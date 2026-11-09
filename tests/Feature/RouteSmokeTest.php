<?php

namespace Tests\Feature;

use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\KategoriPermohonan;
use App\Models\LogAktivitas;
use App\Models\Notifikasi;
use App\Models\PendaftaranDraft;
use App\Models\Pengaduan;
use App\Models\SaranKoreksi;
use App\Models\Tiket;
use App\Models\User;
use App\Models\WarkahPermintaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Smoke test seluruh halaman GET lintas jabatan.
 *
 * Menangkap ketidakcocokan view vs fitur yang membuat halaman error 500,
 * mis. variabel yang dipakai Blade tetapi tidak pernah dikirim controller,
 * atau route yang memanggil view yang sudah tidak ada.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $gagal = [];

    private function user(string $role, ?string $seksi = null): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' Uji',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'seksi' => $seksi,
            'is_active' => true,
        ]);
    }

    private function siapkanData(): void
    {
        $jenis = JenisPermohonan::create([
            'kode' => 'JP01', 'nama' => 'Peralihan Hak', 'kategori' => 'umum', 'is_active' => true,
        ]);
        JenisHak::create(['kode' => 'HM', 'nama' => 'Hak Milik', 'is_active' => true]);
        KategoriPermohonan::create(['kode' => 'UMUM', 'nama' => 'Umum', 'is_active' => true]);

        $loket = $this->user('loket_pendaftaran');
        $staf = $this->user('staf_seksi', '1');
        $kasi = $this->user('kasi_seksi', '1');
        $warkah = $this->user('petugas_warkah');
        $this->user('loket_penyerahan');
        $this->user('loket_pengaduan');
        $this->user('kasi_unit', '3');
        $this->user('kasubbag_tu', 'TU');
        $this->user('kakan');

        $tiket = Tiket::create([
            'kode_tiket' => 'JP28102026001',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Uji',
            'no_hp_pemohon' => '081234567890',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
            'created_by' => $loket->id,
        ]);

        foreach (['diproses', 'ditolak', 'selesai'] as $i => $status) {
            Tiket::create([
                'kode_tiket' => 'JP2810202600'.($i + 2),
                'nomor_antrian' => 'A-00'.($i + 2),
                'tanggal_masuk' => now()->subDays(3)->toDateString(),
                'jenis_permohonan_id' => $jenis->id,
                'nama_pemohon' => 'Pemohon '.$status,
                'no_hp_pemohon' => '081234567890',
                'jumlah_bidang' => 1,
                'status' => $status,
                'penanggung_jawab_id' => $status === 'selesai' ? null : $staf->id,
                'diambil_pada' => $status === 'selesai' ? null : now()->subDays(5),
                'ditolak_pada' => $status === 'ditolak' ? now()->subDays(4) : null,
                'alasan_penolakan' => $status === 'ditolak' ? 'Berkas kurang' : null,
                'tanggal_selesai' => $status === 'selesai' ? now()->subDays(2)->toDateString() : null,
                'diserahkan_pada' => $status === 'selesai' ? now()->subDays(2) : null,
                'created_by' => $loket->id,
            ]);
        }

        $pengaduan = Pengaduan::create([
            'nomor_pengaduan' => 'LP28102026001',
            'tanggal_buat' => now()->toDateString(),
            'pembuat_id' => $loket->id,
            'nama_pengadu' => 'Pengadu Uji',
            'jenis' => 'pelayanan',
            'uraian' => 'Uji coba',
            'seksi_tujuan' => '3',
            'status' => 'terkirim',
        ]);

        Pengaduan::create([
            'nomor_pengaduan' => 'LP28102026002',
            'tanggal_buat' => now()->toDateString(),
            'pembuat_id' => $loket->id,
            'nama_pengadu' => 'Pengadu Selesai',
            'jenis' => 'berkas',
            'uraian' => 'Uji selesai',
            'seksi_tujuan' => '3',
            'status' => 'selesai',
            'selesai_pada' => now(),
        ]);

        $warkah = WarkahPermintaan::create([
            'tiket_id' => $tiket->id,
            'jenis' => 'BT',
            'status' => 'dipinjam',
            'diminta_oleh_id' => $warkah->id,
            'tanggal_permintaan' => now()->subDays(5),
            'peminjam_id' => $staf->id,
            'nama_peminjam' => $staf->name,
            'seksi' => '1',
            'tanggal_pinjam' => now()->subDays(5)->toDateString(),
            'batas_kembali' => now()->addDays(2)->toDateString(),
        ]);

        Notifikasi::create([
            'user_id' => $kasi->id,
            'tipe' => 'uji',
            'judul' => 'Notifikasi Uji',
            'pesan' => 'Pesan uji',
        ]);

        LogAktivitas::create(['nama_pelaku' => 'Uji', 'aksi' => 'LOGIN', 'objek' => 'Sesi']);

        PendaftaranDraft::create([
            'user_id' => $loket->id,
            'kode_tiket' => 'JP28102026998',
            'tahap' => 2,
            'ploting_status' => 'belum',
            'data' => [
                'biodata' => [
                    'nama' => 'Pemohon Draf', 'nik' => '1801010101010001', 'no_hp' => '081234567891',
                    'kelurahan' => 'Kelurahan Uji', 'kecamatan' => 'Kecamatan Uji', 'alamat' => 'Jalan Uji',
                ],
                'permohonan' => [
                    'jenis_permohonan_id' => $jenis->id,
                    'no_hak_sekarang' => 'HM 001',
                    'jumlah_bidang' => 1,
                    'bidang' => [['luas' => '100', 'uraian' => 'Bidang uji']],
                ],
            ],
        ]);

        SaranKoreksi::create(['nama_dokumen_kurang' => 'Dokumen Uji', 'pesan_koreksi' => 'Pesan uji']);
    }

    private function nilaiParam(string $nama, ?string $namaRoute = null): int|string|null
    {
        return match ($nama) {
            'tiket' => Tiket::query()->value('id'),
            'pengaduan' => Pengaduan::query()->value('id'),
            'warkahPermintaan' => WarkahPermintaan::query()->value('id'),
            'notifikasi' => Notifikasi::query()->value('id'),
            'draf' => PendaftaranDraft::query()->value('id'),
            'id' => match (true) {
                str_starts_with((string) $namaRoute, 'admin.users') => User::where('role', '!=', 'super_admin')->value('id'),
                str_starts_with((string) $namaRoute, 'admin.form-pendaftaran.jenis-hak') => JenisHak::query()->value('id'),
                str_starts_with((string) $namaRoute, 'admin.form-pendaftaran.jenis-permohonan') => JenisPermohonan::query()->value('id'),
                str_starts_with((string) $namaRoute, 'admin.form-pendaftaran.kategori') => KategoriPermohonan::query()->value('id'),
                str_starts_with((string) $namaRoute, 'admin.form-pendaftaran.saran-koreksi') => SaranKoreksi::query()->value('id'),
                default => null,
            },
            default => null,
        };
    }

    public function test_crawl_semua_halaman_get(): void
    {
        $this->siapkanData();

        $roles = array_keys(User::ROLES);
        $dilewati = ['impersonate.stop', 'logout', 'login', 'login.submit', 'sanctum.csrf-cookie'];
        $diuji = 0;

        foreach ($roles as $role) {
            $user = User::where('role', $role)->first();

            if ($user === null) {
                continue;
            }

            foreach (Route::getRoutes() as $route) {
                if (! in_array('GET', $route->methods(), true)) {
                    continue;
                }
                if (! str_starts_with($route->uri(), '/') && str_starts_with($route->uri(), 'api')) {
                    continue;
                }
                if ($route->getName() !== null && in_array($route->getName(), $dilewati, true)) {
                    continue;
                }

                $uri = $route->uri();
                $bisa = true;

                foreach ($route->parameterNames() as $nama) {
                    $nilai = $this->nilaiParam($nama, $route->getName());
                    if ($nilai === null) {
                        $bisa = false;
                        break;
                    }
                    $uri = str_replace(['{'.$nama.'}', '{'.$nama.'?}'], (string) $nilai, $uri);
                }

                if (! $bisa || str_contains($uri, '{')) {
                    continue;
                }

                $diuji++;

                try {
                    $respon = $this->actingAs($user)->get('/'.ltrim($uri, '/'));
                    $status = $respon->getStatusCode();
                } catch (\Throwable $e) {
                    $this->gagal[] = "{$role} GET /{$uri} EXCEPTION: ".get_class($e).' — '.$e->getMessage();

                    continue;
                }

                if ($status >= 500) {
                    $this->gagal[] = "{$role} GET /{$uri} -> {$status}";
                }
            }
        }

        fwrite(STDERR, "\nRoute diuji: {$diuji}\n");
        fwrite(STDERR, $this->gagal === [] ? "Tidak ada error 500\n" : implode("\n", $this->gagal)."\n");

        $this->assertSame([], $this->gagal, 'Ada halaman yang error 500.');
    }
}
