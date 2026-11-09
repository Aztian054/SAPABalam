<?php

namespace App\Services;

use App\Models\JenisPermohonan;
use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\User;
use App\Models\WarkahPermintaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Data & Skenario Demo (Blueprint V2 — Super Admin).
 *
 * Semua baris yang dibuat ditandai `is_demo = true` dan nama pemohon/pengadu
 * berprefiks "[DEMO]" agar mudah dikenali di seluruh daftar. Reset hanya
 * menghapus baris bertanda tersebut sehingga data operasional asli aman.
 */
class DemoService
{
    /** Prefiks nama agar data demo langsung terlihat di daftar mana pun. */
    public const PREFIX = '[DEMO]';

    /** Frasa konfirmasi yang harus diketik saat menghapus data demo. */
    public const KONFIRMASI = 'HAPUS DEMO';

    /** Username akun demo (dipakai untuk mencegah bentrok akun asli). */
    private const USERNAME_DEMO = ['demo_loket', 'demo_staf', 'demo_warkah'];

    /** Mode demo aktif bila APP_ENV=local atau flag DEMO_MODE=true. */
    public function aktif(): bool
    {
        return (bool) config('loket.demo_mode', false) || app()->environment('local');
    }

    /**
     * Jumlah baris demo saat ini.
     *
     * @return array{tiket: int, pengaduan: int, warkah: int, pengguna: int, total: int}
     */
    public function ringkasan(): array
    {
        $tiket = Tiket::where('is_demo', true)->count();
        $pengaduan = Pengaduan::where('is_demo', true)->count();
        $warkah = WarkahPermintaan::where('is_demo', true)->count();
        $pengguna = User::where('is_demo', true)->count();

        return [
            'tiket' => $tiket,
            'pengaduan' => $pengaduan,
            'warkah' => $warkah,
            'pengguna' => $pengguna,
            'total' => $tiket + $pengaduan + $warkah + $pengguna,
        ];
    }

    public function punyaJenisPermohonan(): bool
    {
        return JenisPermohonan::where('is_active', true)->exists();
    }

    /**
     * Buat paket data demo: akun, tiket (normal/ditolak/selesai/macet/antrean
     * warkah), warkah, dan pengaduan.
     *
     * @return array{tiket: int, pengaduan: int, warkah: int, pengguna: int}
     */
    public function seed(): array
    {
        if (! $this->punyaJenisPermohonan()) {
            throw new RuntimeException('Master Jenis Permohonan masih kosong. Tambahkan satu jenis permohonan aktif terlebih dahulu, lalu jalankan seed demo.');
        }

        $this->pastikanUsernameDemoBebas();

        if ($this->ringkasan()['total'] > 0) {
            throw new RuntimeException('Data demo sudah ada. Hapus data demo terlebih dahulu sebelum membuat ulang.');
        }

        $jenis = JenisPermohonan::where('is_active', true)->orderBy('id')->firstOrFail();

        return DB::transaction(function () use ($jenis): array {
            $loket = $this->pengguna('Rina Loket', 'demo_loket', 'loket_pendaftaran');
            $staf = $this->pengguna('Andi Staf', 'demo_staf', 'staf_seksi', '1');
            $petugasWarkah = $this->pengguna('Bayu Warkah', 'demo_warkah', 'petugas_warkah');

            // 1) Alur normal — tiket sedang diproses Staf Seksi 1.
            $normal = $this->tiket($jenis, $loket, [
                'nama_pemohon' => self::PREFIX.' Budi Santoso',
                'status' => 'diproses',
                'penanggung_jawab_id' => $staf->id,
                'peran_bidang' => 'verifikasi',
                'diambil_pada' => now()->subDay(),
            ]);

            // 2) Alur ditolak — menunggu berkas diperbaiki pemohon.
            $this->tiket($jenis, $loket, [
                'nama_pemohon' => self::PREFIX.' Siti Aminah',
                'status' => 'ditolak',
                'penanggung_jawab_id' => $staf->id,
                'diambil_pada' => now()->subDays(2),
                'ditolak_pada' => now()->subDays(2),
                'alasan_penolakan' => 'Fotokopi KTP tidak terbaca. Mohon unggah ulang.',
            ]);

            // 3) Alur selesai — siap diserahkan/diarsipkan.
            $this->tiket($jenis, $loket, [
                'nama_pemohon' => self::PREFIX.' Doni Pratama',
                'status' => 'selesai',
                'tanggal_selesai' => now()->subDays(2)->toDateString(),
                'diserahkan_pada' => now()->subDays(2),
                'penerima_nama' => self::PREFIX.' Doni Pratama',
                'penerima_hubungan' => 'pemohon',
            ]);

            // 4) Alur macet — dipegang melewati batas hari penanda MACET.
            $this->tiket($jenis, $loket, [
                'nama_pemohon' => self::PREFIX.' Kartini',
                'status' => 'diproses',
                'penanggung_jawab_id' => $staf->id,
                'peran_bidang' => 'validator_bt',
                'diambil_pada' => now()->subDays(10),
            ]);

            // 5) Alur warkah — tiket baru di database dengan permintaan warkah
            //    yang belum diproses (sesuai alur Tahap 4).
            $tiketWarkah = $this->tiket($jenis, $loket, [
                'nama_pemohon' => self::PREFIX.' Joko Warkah',
                'status' => 'di_database',
            ]);

            $this->warkah($normal, 'BT', 'dipinjam', $petugasWarkah, $staf);
            $this->warkah($tiketWarkah, 'SU', 'diminta', $petugasWarkah, null);

            // 6) Alur pengaduan — satu terlambat ditanggapi, satu sudah selesai.
            $this->pengaduan($loket, [
                'nama_pengadu' => self::PREFIX.' Warga Demo',
                'jenis' => 'pelayanan',
                'seksi_tujuan' => '3',
                'status' => 'terkirim',
                'uraian' => 'Mohon informasi kelengkapan berkas peralihan hak.',
                'dibuat' => now()->subDays(5),
            ]);

            $this->pengaduan($loket, [
                'nama_pengadu' => self::PREFIX.' Warga Selesai',
                'jenis' => 'berkas',
                'seksi_tujuan' => '1',
                'status' => 'selesai',
                'uraian' => 'Berkas sudah diterima dan ditindaklanjuti.',
                'catatan_terakhir' => 'Sudah ditindaklanjuti unit terkait.',
                'diterima_pada' => now()->subDays(5),
                'selesai_pada' => now()->subDays(4),
                'dibuat' => now()->subDays(6),
            ]);

            return $this->ringkasanTanpaTotal();
        });
    }

    /**
     * Hapus hanya baris bertanda demo.
     *
     * @return array{tiket: int, pengaduan: int, warkah: int, pengguna: int}
     */
    public function reset(): array
    {
        return DB::transaction(function (): array {
            $warkah = WarkahPermintaan::where('is_demo', true)->delete();
            $pengaduan = Pengaduan::where('is_demo', true)->delete();
            $tiket = Tiket::where('is_demo', true)->delete();
            $pengguna = User::where('is_demo', true)->delete();

            return [
                'tiket' => $tiket,
                'pengaduan' => $pengaduan,
                'warkah' => $warkah,
                'pengguna' => $pengguna,
            ];
        });
    }

    /** @param  array<string, mixed>  $atribut */
    private function tiket(JenisPermohonan $jenis, User $loket, array $atribut): Tiket
    {
        return Tiket::create(array_merge([
            'kode_tiket' => app(TiketNomorService::class)->generate(),
            'nomor_antrian' => 'D-'.fake()->unique()->numberBetween(1, 9999),
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => self::PREFIX.' Pemohon Demo',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
            'petugas_loket_id' => $loket->id,
            'nama_petugas_loket' => $loket->name,
            'created_by' => $loket->id,
            'is_demo' => true,
        ], $atribut));
    }

    private function warkah(Tiket $tiket, string $jenis, string $status, User $petugas, ?User $peminjam): WarkahPermintaan
    {
        return WarkahPermintaan::create([
            'tiket_id' => $tiket->id,
            'jenis' => $jenis,
            'status' => $status,
            'diminta_oleh_id' => $petugas->id,
            'tanggal_permintaan' => now()->subDays(12),
            'peminjam_id' => $peminjam?->id,
            'nama_peminjam' => $peminjam?->name,
            'seksi' => $peminjam?->seksi,
            'tanggal_pinjam' => $peminjam === null ? null : now()->subDays(12)->toDateString(),
            'batas_kembali' => $peminjam === null ? null : now()->subDays(5)->toDateString(),
            'catatan' => $jenis === 'BT'
                ? 'Contoh pinjaman Buku Tanah untuk pelatihan.'
                : 'Contoh permintaan Surat Ukur untuk pelatihan.',
            'is_demo' => true,
        ]);
    }

    /** @param  array<string, mixed>  $atribut */
    private function pengaduan(User $loket, array $atribut): Pengaduan
    {
        $dibuat = $atribut['dibuat'] ?? now();
        unset($atribut['dibuat']);

        $pengaduan = Pengaduan::create(array_merge([
            'nomor_pengaduan' => 'DEMO-LP-'.Str::upper(Str::random(6)),
            'tanggal_buat' => $dibuat->toDateString(),
            'pembuat_id' => $loket->id,
            'nama_pengadu' => self::PREFIX.' Warga Demo',
            'no_hp_pengadu' => '081200000001',
            'jenis' => 'pelayanan',
            'uraian' => 'Contoh pengaduan demo.',
            'seksi_tujuan' => '3',
            'status' => 'terkirim',
            'is_demo' => true,
        ], $atribut));

        // Backdate agar chip TERLAMBAT/riwayat terlihat realistis.
        $pengaduan->forceFill(['created_at' => $dibuat, 'updated_at' => $dibuat])->save();

        return $pengaduan;
    }

    private function pengguna(string $nama, string $username, string $role, ?string $seksi = null): User
    {
        return User::create([
            'name' => self::PREFIX.' '.$nama,
            'username' => $username,
            'email' => $username.'@demo.loket.test',
            'password' => 'demo12345',
            'password_text' => 'demo12345',
            'role' => $role,
            'seksi' => $seksi,
            'is_active' => true,
            'is_demo' => true,
        ]);
    }

    private function pastikanUsernameDemoBebas(): void
    {
        $bentrok = User::whereIn('username', self::USERNAME_DEMO)
            ->where('is_demo', false)
            ->exists();

        if ($bentrok) {
            throw new RuntimeException('Username akun demo (demo_loket/demo_staf/demo_warkah) sudah dipakai akun asli. Ganti username tersebut lalu coba lagi.');
        }
    }

    /** @return array{tiket: int, pengaduan: int, warkah: int, pengguna: int} */
    private function ringkasanTanpaTotal(): array
    {
        $ringkasan = $this->ringkasan();
        unset($ringkasan['total']);

        return $ringkasan;
    }
}
