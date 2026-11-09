<?php

namespace App\Console\Commands;

use App\Models\ArsipFolder;
use App\Models\ArsipTiket;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Arsip otomatis tiket selesai (Blueprint V2, modul 13).
 *
 * Tiket `selesai` yang sudah lewat `config('loket.hari_arsip')` ditandai
 * `diarsipkan_pada` dan dicatat ke `arsip_tiket`. Dijadwalkan harian.
 */
class ArsipkanTiketSelesai extends Command
{
    protected $signature = 'loket:arsipkan-tiket {--hari= : Batas hari, menimpa config loket.hari_arsip}';

    protected $description = 'Arsipkan otomatis tiket selesai yang melewati batas hari arsip';

    public function handle(NotifikasiService $notifikasi): int
    {
        $hari = $this->option('hari') !== null
            ? (int) $this->option('hari')
            : (int) config('loket.hari_arsip', 30);

        $kandidat = Tiket::siapDiarsipkan($hari)->get();

        if ($kandidat->isEmpty()) {
            $this->info('Tidak ada tiket selesai yang perlu diarsipkan.');

            return self::SUCCESS;
        }

        $folder = ArsipFolder::firstOrCreate(
            ['nama_folder' => 'Arsip Otomatis'],
            [
                'jenis_dokumen' => 'Berkas Permohonan',
                'keterangan' => 'Dibuat otomatis oleh scheduler arsip.',
            ]
        );

        DB::transaction(function () use ($kandidat, $folder, $hari) {
            foreach ($kandidat as $tiket) {
                $tiket->update(['diarsipkan_pada' => now()]);

                ArsipTiket::firstOrCreate(
                    ['tiket_id' => $tiket->id],
                    [
                        'folder_id' => $folder->id,
                        'nama_arsip' => $tiket->kode_tiket,
                        'tipe_dokumen' => 'Berkas Permohonan',
                        'tanggal_arsip' => now()->toDateString(),
                        'keterangan' => 'Arsip otomatis setelah '.$hari.' hari selesai.',
                    ]
                );

                RiwayatStatus::create([
                    'tiket_id' => $tiket->id,
                    'stage_dari' => 'selesai',
                    'stage_ke' => 'Arsip',
                    'changed_by' => null,
                    'keterangan' => "Diarsipkan otomatis oleh sistem ({$hari} hari setelah selesai).",
                ]);
            }
        });

        $notifikasi->kirim(
            tipe: 'tiket.diarsipkan',
            penerimaIds: $notifikasi->idAdmin(),
            judul: 'Rekap arsip harian: '.$kandidat->count().' berkas',
            pesan: 'Berkas selesai yang melewati '.$hari.' hari telah dipindahkan ke Arsip.',
            url: route('arsip.index'),
            objekTipe: null,
            objekId: null,
        );

        $this->info("{$kandidat->count()} tiket diarsipkan ke folder {$folder->nama_folder}.");

        return self::SUCCESS;
    }
}
