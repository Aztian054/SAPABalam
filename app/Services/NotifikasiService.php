<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\NotifikasiPreferensi;
use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pengirim notifikasi in-app (Blueprint V2, modul 16).
 *
 * Aturan:
 * - satu penerima = satu baris;
 * - deduplikasi 60 detik untuk tipe + objek + penerima yang sama;
 * - penerima difilter ke akun aktif dan preferensi pengguna;
 * - penerima yang tidak ada diabaikan;
 * - dipanggil SETELAH transaksi utama selesai (jangan di dalam closure).
 */
class NotifikasiService
{
    private const DEDUPLIKASI_DETIK = 60;

    /** Katalog tipe notifikasi untuk halaman preferensi. */
    public const TIPE = [
        'Tiket' => [
            'tiket.dibuat' => 'Tiket baru masuk Database',
            'tiket.ploting_selesai' => 'Ploting selesai',
            'tiket.diambil' => 'Tiket diambil staf',
            'tiket.ditolak' => 'Tiket ditolak',
            'tiket.ditolak_ulang' => 'Tiket diproses kembali',
            'tiket.dilepas' => 'Tiket dilepas Admin',
            'tiket.diteruskan' => 'Berkas diteruskan ke akun lain',
            'tiket.macet' => 'Tiket macet',
            'tiket.selesai' => 'Tiket selesai & diserahkan',
            'tiket.diarsipkan' => 'Rekap arsip harian',
        ],
        'Akun' => [
            'akun.staf_baru' => 'Staf baru ditambahkan',
            'akun.staf_nonaktif' => 'Staf dinonaktifkan',
            'akun.staf_aktif' => 'Staf diaktifkan kembali',
            'akun.staf_pindah_seksi' => 'Staf pindah seksi',
            'akun.staf_dihapus' => 'Staf dihapus',
        ],
        'Warkah' => [
            'warkah.diminta' => 'Permintaan warkah baru',
            'warkah.siap' => 'Warkah siap diambil',
            'warkah.dipinjam' => 'Warkah dipinjam',
            'warkah.mendekati_batas' => 'Warkah mendekati batas kembali',
            'warkah.terlambat' => 'Warkah terlambat',
            'warkah.batas_diubah' => 'Batas kembali diubah',
            'warkah.dikembalikan' => 'Warkah dikembalikan',
        ],
        'Pengaduan' => [
            'pengaduan.masuk' => 'Pengaduan baru masuk',
            'pengaduan.diterima' => 'Pengaduan diterima unit',
            'pengaduan.dikembalikan' => 'Pengaduan dikembalikan',
            'pengaduan.selesai' => 'Pengaduan selesai',
            'pengaduan.terlambat' => 'Pengaduan terlambat',
        ],
    ];

    /** Tipe yang selalu dikirim dan tidak dapat dimatikan pengguna. */
    public const WAJIB = [
        'tiket.ditolak',
        'tiket.dilepas',
        'tiket.diteruskan',
        'tiket.selesai',
        'akun.staf_baru',
        'akun.staf_nonaktif',
        'akun.staf_aktif',
        'akun.staf_pindah_seksi',
        'akun.staf_dihapus',
        'pengaduan.masuk',
    ];

    /** @return list<string> */
    public static function semuaTipe(): array
    {
        return array_merge(...array_values(array_map(
            fn (array $grup) => array_keys($grup),
            self::TIPE
        )));
    }

    public static function isWajib(string $tipe): bool
    {
        return in_array($tipe, self::WAJIB, true);
    }

    /**
     * @param  array<int, int|null>  $penerimaIds
     * @return int jumlah notifikasi yang benar-benar dibuat
     */
    public function kirim(
        string $tipe,
        array $penerimaIds,
        string $judul,
        ?string $pesan = null,
        ?string $url = null,
        ?string $objekTipe = null,
        ?int $objekId = null,
    ): int {
        $penerimaIds = array_values(array_unique(array_filter(
            $penerimaIds,
            fn ($id) => $id !== null && $id > 0
        )));

        if ($penerimaIds === []) {
            return 0;
        }

        $penerimaAktif = User::whereIn('id', $penerimaIds)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if ($penerimaAktif === []) {
            return 0;
        }

        $nonaktif = [];

        if (! self::isWajib($tipe)) {
            $nonaktif = NotifikasiPreferensi::whereIn('user_id', $penerimaAktif)
                ->where('tipe', $tipe)
                ->where('aktif', false)
                ->pluck('user_id')
                ->all();
        }

        if ($nonaktif !== []) {
            $penerimaAktif = array_values(array_diff($penerimaAktif, $nonaktif));
        }

        $jumlah = 0;

        foreach ($penerimaAktif as $userId) {
            if ($this->sudahAdaBaruBaruIni($userId, $tipe, $objekTipe, $objekId)) {
                continue;
            }

            Notifikasi::create([
                'user_id' => $userId,
                'tipe' => $tipe,
                'judul' => mb_substr($judul, 0, 120),
                'pesan' => $pesan,
                'url' => $url,
                'objek_tipe' => $objekTipe,
                'objek_id' => $objekId,
            ]);

            $jumlah++;
        }

        return $jumlah;
    }

    /** Kasi Seksi aktif; bila `$seksi` null berarti semua seksi. @return list<int> */
    public function idKasiSeksi(?string $seksi = null): array
    {
        return User::where('role', 'kasi_seksi')
            ->where('is_active', true)
            ->when($seksi !== null, fn (Builder $q) => $q->where('seksi', $seksi))
            ->pluck('id')
            ->all();
    }

    /** @return list<int> */
    public function idAdmin(): array
    {
        return User::whereIn('role', ['admin', 'super_admin'])
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /** @return list<int> */
    public function idPetugasWarkah(): array
    {
        return User::where('role', 'petugas_warkah')
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /** Seluruh Staf Seksi 1/2 yang aktif (penerima tiket baru). @return list<int> */
    public function idStafSeksi(): array
    {
        return User::where('role', 'staf_seksi')
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /**
     * Penerima pengaduan untuk satu unit tujuan: Kasi unit (role sesuai seksi),
     * Kasubbag TU untuk TU, dan Kasi Seksi 1/2 bila tujuannya seksi tersebut.
     *
     * @return list<int>
     */
    public function idUnitPengaduan(string $seksi): array
    {
        if ($seksi === 'TU') {
            return User::where('role', 'kasubbag_tu')
                ->where('is_active', true)
                ->pluck('id')
                ->all();
        }

        if (in_array($seksi, ['1', '2'], true)) {
            return $this->idKasiSeksi($seksi);
        }

        return User::where('role', 'kasi_unit')
            ->where('seksi', $seksi)
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }

    /**
     * Seksi tempat tiket terakhir dikerjakan (dipakai untuk menentukan Kasi
     * yang perlu diberi tahu). Null bila tiket belum pernah disentuh staf.
     */
    public function seksiTiket(Tiket $tiket): ?string
    {
        $pemegang = $tiket->penanggungJawab;

        if ($pemegang !== null && $pemegang->seksi !== null) {
            return $pemegang->seksi;
        }

        return $tiket->riwayatStatuses()
            ->whereIn('changed_by', User::where('role', 'staf_seksi')->select('id'))
            ->latest('id')
            ->first()
            ?->user
            ?->seksi;
    }

    /**
     * Semua Staf Seksi aktif yang pernah menangani tiket (dari riwayat status).
     * Dipakai untuk memberi tahu staf saat berkas berpindah akun atau selesai.
     *
     * @return list<int>
     */
    public function idStafTerkait(Tiket $tiket, ?int $kecuali = null): array
    {
        $stafIds = $tiket->riwayatStatuses()
            ->whereNotNull('changed_by')
            ->distinct()
            ->pluck('changed_by');

        if ($stafIds->isEmpty()) {
            return [];
        }

        return User::where('role', 'staf_seksi')
            ->where('is_active', true)
            ->whereIn('id', $stafIds)
            ->when($kecuali !== null, fn (Builder $q) => $q->where('id', '!=', $kecuali))
            ->pluck('id')
            ->all();
    }

    public function urlTiket(Tiket $tiket, string $rute = 'seksi.show'): string
    {
        return route($rute, $tiket);
    }

    public function urlPengaduan(Pengaduan $pengaduan, string $rute = 'pengaduan.show'): string
    {
        return route($rute, $pengaduan);
    }

    private function sudahAdaBaruBaruIni(
        int $userId,
        string $tipe,
        ?string $objekTipe,
        ?int $objekId,
    ): bool {
        return Notifikasi::query()
            ->where('user_id', $userId)
            ->where('tipe', $tipe)
            ->when(
                $objekTipe === null,
                fn (Builder $q) => $q->whereNull('objek_tipe'),
                fn (Builder $q) => $q->where('objek_tipe', $objekTipe)
            )
            ->when(
                $objekId === null,
                fn (Builder $q) => $q->whereNull('objek_id'),
                fn (Builder $q) => $q->where('objek_id', $objekId)
            )
            ->where('created_at', '>=', now()->subSeconds(self::DEDUPLIKASI_DETIK))
            ->exists();
    }
}
