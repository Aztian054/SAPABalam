<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Monitoring berkas selesai oleh Admin (read-only, tanpa arsip manual). */
class AdminSelesaiMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function jenisPermohonan(string $kode = 'JP1'): JenisPermohonan
    {
        return JenisPermohonan::create([
            'kode' => $kode,
            'nama' => "Permohonan {$kode}",
            'kategori' => 'umum',
            'is_active' => true,
        ]);
    }

    private function tiketSelesai(array $overrides = []): Tiket
    {
        $jp = JenisPermohonan::first() ?? $this->jenisPermohonan();

        return Tiket::create(array_merge([
            'kode_tiket' => 'K/'.fake()->unique()->numberBetween(100, 9999).'/260913/1',
            'nomor_antrian' => 'A-'.fake()->numberBetween(1, 999),
            'tanggal_masuk' => now()->subDays(10)->toDateString(),
            'jenis_permohonan_id' => $jp->id,
            'nama_pemohon' => fake()->name(),
            'no_hp_pemohon' => '08'.fake()->numberBetween(1000000000, 9999999999),
            'jumlah_bidang' => 1,
            'status' => 'selesai',
            'tanggal_selesai' => now()->subDay()->toDateString(),
        ], $overrides));
    }

    public function test_admin_melihat_halaman_selesai_tanpa_aksi_arsip(): void
    {
        $this->actingAs($this->user('admin'));

        $tiket = $this->tiketSelesai(['nama_pemohon' => 'Pemohon Filter Test']);

        $this->get(route('admin.selesai'))
            ->assertOk()
            ->assertSee($tiket->kode_tiket)
            ->assertSee('Tahun Selesai')
            ->assertSee('Mode Monitoring')
            ->assertDontSee('openBulkModal')
            ->assertDontSee('Arsipkan Semua Hasil Filter')
            ->assertDontSee('id="bulkForm"', false);
    }

    public function test_filter_tahun_dan_jenis_permohonan_membatasi_daftar(): void
    {
        $this->actingAs($this->user('admin'));

        $jpA = $this->jenisPermohonan('JPA');
        $jpB = $this->jenisPermohonan('JPB');

        $lama = $this->tiketSelesai([
            'jenis_permohonan_id' => $jpA->id,
            'tanggal_selesai' => now()->subYears(2)->toDateString(),
        ]);
        $baru = $this->tiketSelesai([
            'jenis_permohonan_id' => $jpB->id,
            'tanggal_selesai' => now()->toDateString(),
        ]);

        $this->get(route('admin.selesai', [
            'tahun' => now()->year,
            'jenis_permohonan_id' => $jpB->id,
        ]))
            ->assertOk()
            ->assertSee($baru->kode_tiket)
            ->assertDontSee($lama->kode_tiket);
    }

    public function test_filter_jenis_hak_membatasi_daftar(): void
    {
        $this->actingAs($this->user('admin'));

        $withHm = $this->tiketSelesai();
        BidangTanah::create([
            'tiket_id' => $withHm->id,
            'jenis_hak' => 'HM',
            'urutan' => 1,
        ]);
        $withoutHm = $this->tiketSelesai();

        $this->get(route('admin.selesai', ['jenis_hak' => 'HM']))
            ->assertOk()
            ->assertSee($withHm->kode_tiket)
            ->assertDontSee($withoutHm->kode_tiket);
    }
}
