<?php

namespace App\Console\Commands;

use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\WarkahPermintaan;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

/**
 * Pengingat harian (Blueprint V2, modul 06/13/16).
 *
 * - Tiket macet: dipegang melewati `loket.batas_macet_hari`.
 * - Pengaduan belum ditanggapi melewati `loket.batas_tanggap_pengaduan_hari`.
 * - Warkah mendekati batas (H-1) dan warkah terlambat.
 */
class KirimPengingatHarian extends Command
{
    protected $signature = 'loket:pengingat-harian';

    protected $description = 'Kirim notifikasi in-app untuk tiket macet, pengaduan terlambat, dan warkah jatuh tempo';

    public function handle(NotifikasiService $notifikasi): int
    {
        $this->ingatkanTiketMacet($notifikasi);
        $this->ingatkanPengaduanTerlambat($notifikasi);
        $this->ingatkanWarkah($notifikasi);

        return self::SUCCESS;
    }

    private function ingatkanTiketMacet(NotifikasiService $notifikasi): void
    {
        $batas = (int) config('loket.batas_macet_hari', 3);

        $tikets = Tiket::with('penanggungJawab')
            ->where('status', 'diproses')
            ->whereNotNull('diambil_pada')
            ->where('diambil_pada', '<', now()->subDays($batas))
            ->get();

        foreach ($tikets as $tiket) {
            $pemegang = $tiket->penanggungJawab;
            $seksi = $pemegang?->seksi;

            $notifikasi->kirim(
                tipe: 'tiket.macet',
                penerimaIds: array_merge(
                    [$pemegang?->id],
                    $notifikasi->idKasiSeksi($seksi),
                ),
                judul: "Tiket {$tiket->kode_tiket} macet",
                pesan: "Berkas dipegang lebih dari {$batas} hari. Segera diselesaikan atau dilepas Admin.",
                url: route('seksi.tiket-saya'),
                objekTipe: 'tiket',
                objekId: $tiket->id,
            );
        }

        if ($tikets->isNotEmpty()) {
            $this->info("Pengingat macet: {$tikets->count()} tiket.");
        }
    }

    private function ingatkanPengaduanTerlambat(NotifikasiService $notifikasi): void
    {
        $batas = (int) config('loket.batas_tanggap_pengaduan_hari', 2);

        $pengaduans = Pengaduan::where('status', 'terkirim')
            ->where('created_at', '<', now()->subDays($batas))
            ->get();

        foreach ($pengaduans as $pengaduan) {
            $notifikasi->kirim(
                tipe: 'pengaduan.terlambat',
                penerimaIds: $notifikasi->idUnitPengaduan((string) $pengaduan->seksi_tujuan),
                judul: "Pengaduan {$pengaduan->nomor_pengaduan} belum ditangani",
                pesan: 'Pengaduan melewati batas tanggap '.$batas.' hari. Segera TERIMA dan tindak lanjuti.',
                url: route('pengaduan-unit.index'),
                objekTipe: 'pengaduan',
                objekId: $pengaduan->id,
            );
        }

        if ($pengaduans->isNotEmpty()) {
            $this->info("Pengingat pengaduan: {$pengaduans->count()} pengaduan.");
        }
    }

    private function ingatkanWarkah(NotifikasiService $notifikasi): void
    {
        $besok = now()->addDay()->toDateString();
        $hariIni = now()->toDateString();

        $mendekati = WarkahPermintaan::with('tiket')
            ->where('status', 'dipinjam')
            ->whereDate('batas_kembali', $besok)
            ->get();

        foreach ($mendekati as $warkah) {
            $notifikasi->kirim(
                tipe: 'warkah.mendekati_batas',
                penerimaIds: array_merge(
                    [$warkah->peminjam_id],
                    $notifikasi->idPetugasWarkah(),
                ),
                judul: 'Warkah harus dikembalikan besok',
                pesan: "Warkah {$warkah->jenis} untuk tiket {$warkah->tiket?->kode_tiket} jatuh tempo besok.",
                url: route('warkah-permintaan.pinjaman'),
                objekTipe: 'warkah',
                objekId: $warkah->id,
            );
        }

        $terlambat = WarkahPermintaan::with('tiket')
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->whereDate('batas_kembali', '<', $hariIni)
            ->get();

        foreach ($terlambat as $warkah) {
            $warkah->update(['status' => 'terlambat']);

            $seksiPeminjam = $warkah->seksi;

            $penerima = array_merge(
                [$warkah->peminjam_id],
                $notifikasi->idKasiSeksi($seksiPeminjam),
                $notifikasi->idPetugasWarkah(),
                $notifikasi->idAdmin(),
            );

            $notifikasi->kirim(
                tipe: 'warkah.terlambat',
                penerimaIds: $penerima,
                judul: 'Warkah terlambat dikembalikan',
                pesan: "Warkah {$warkah->jenis} untuk tiket {$warkah->tiket?->kode_tiket} melewati batas kembali.",
                url: route('warkah-permintaan.pinjaman'),
                objekTipe: 'warkah',
                objekId: $warkah->id,
            );
        }

        if ($mendekati->isNotEmpty() || $terlambat->isNotEmpty()) {
            $this->info("Pengingat warkah: {$mendekati->count()} mendekati batas, {$terlambat->count()} terlambat.");
        }
    }
}
