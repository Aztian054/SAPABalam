<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\User;
use App\Models\WarkahPermintaan;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Data & Skenario Demo (Super Admin, hanya saat mode demo aktif).
 *
 * Seed menandai seluruh baris dengan `is_demo`; reset hanya menghapus baris
 * bertanda demo dan tidak menyentuh data asli.
 */
class DemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mode demo default mati di lingkungan testing; nyalakan per test.
        config(['loket.demo_mode' => false]);
    }

    private function user(string $role = 'super_admin'): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function jenisPermohonan(): JenisPermohonan
    {
        return JenisPermohonan::create([
            'kode' => 'DEMO-JP',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);
    }

    private function aktifkanModeDemo(): void
    {
        config(['loket.demo_mode' => true]);
    }

    private function tiketAsli(): Tiket
    {
        return Tiket::create([
            'kode_tiket' => 'JP28102026999',
            'nomor_antrian' => 'A-999',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => (JenisPermohonan::first() ?? $this->jenisPermohonan())->id,
            'nama_pemohon' => 'Pemohon Asli',
            'no_hp_pemohon' => '081234567890',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);
    }

    public function test_halaman_demo_tidak_tersedia_saat_mode_demo_mati(): void
    {
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)->get(route('super-admin.demo.index'))->assertForbidden();
        $this->actingAs($superAdmin)->post(route('super-admin.demo.seed'))->assertForbidden();
        $this->actingAs($superAdmin)->post(route('super-admin.demo.reset'))->assertForbidden();
    }

    public function test_admin_biasa_tidak_dapat_mengakses_area_demo(): void
    {
        $this->aktifkanModeDemo();
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('super-admin.demo.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('super-admin.demo.seed'))->assertForbidden();
    }

    public function test_super_admin_dapat_membuka_halaman_demo_saat_mode_aktif(): void
    {
        $this->aktifkanModeDemo();
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.demo.index'))
            ->assertOk()
            ->assertSee('Data & Skenario Demo')
            ->assertSee('Seed Data Demo')
            ->assertSee(DemoService::KONFIRMASI);
    }

    public function test_seed_membuat_data_demo_bertanda_dan_tercatat_di_log(): void
    {
        $this->aktifkanModeDemo();
        $this->jenisPermohonan();
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.demo.seed'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(5, Tiket::where('is_demo', true)->count());
        $this->assertSame(2, WarkahPermintaan::where('is_demo', true)->count());
        $this->assertSame(2, Pengaduan::where('is_demo', true)->count());
        $this->assertSame(3, User::where('is_demo', true)->count());

        // Nama selalu berprefiks jelas agar mudah dikenali di daftar mana pun.
        $this->assertSame(
            5,
            Tiket::where('is_demo', true)->where('nama_pemohon', 'like', DemoService::PREFIX.'%')->count(),
        );

        // Skenario macet dihitung dari batas hari, bukan status.
        $macet = Tiket::where('is_demo', true)->where('status', 'diproses')
            ->where('diambil_pada', '<', now()->subDays(3))
            ->first();

        $this->assertNotNull($macet);
        $this->assertTrue($macet->is_macet);

        // Super Admin tidak meninggalkan jejak di Log Aktivitas.
        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'SEED DEMO']);
    }

    public function test_seed_kedua_ditolak_tanpa_menggandakan_data(): void
    {
        $this->aktifkanModeDemo();
        $this->jenisPermohonan();
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)->post(route('super-admin.demo.seed'));
        $this->actingAs($superAdmin)->post(route('super-admin.demo.seed'))
            ->assertSessionHas('error');

        $this->assertSame(5, Tiket::where('is_demo', true)->count());
    }

    public function test_seed_ditolak_bila_master_jenis_permohonan_kosong(): void
    {
        $this->aktifkanModeDemo();
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)
            ->post(route('super-admin.demo.seed'))
            ->assertSessionHas('error');

        $this->assertSame(0, Tiket::where('is_demo', true)->count());
    }

    public function test_reset_memerlukan_konfirmasi_ganda(): void
    {
        $this->aktifkanModeDemo();
        $this->jenisPermohonan();
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)->post(route('super-admin.demo.seed'));

        $this->actingAs($superAdmin)
            ->post(route('super-admin.demo.reset'), ['konfirmasi' => 'hapus'])
            ->assertSessionHasErrors(['konfirmasi', 'paham']);

        $this->assertSame(5, Tiket::where('is_demo', true)->count());
    }

    public function test_reset_hanya_menghapus_data_demo_dan_tercatat_di_log(): void
    {
        $this->aktifkanModeDemo();
        $this->jenisPermohonan();
        $superAdmin = $this->user();

        $asli = $this->tiketAsli();
        $pengaduanAsli = Pengaduan::create([
            'nomor_pengaduan' => 'LP-ASLI-001',
            'tanggal_buat' => now()->toDateString(),
            'jenis' => 'pelayanan',
            'uraian' => 'Pengaduan asli',
            'seksi_tujuan' => '3',
            'status' => 'terkirim',
        ]);

        $this->actingAs($superAdmin)->post(route('super-admin.demo.seed'));

        $this->actingAs($superAdmin)
            ->post(route('super-admin.demo.reset'), [
                'konfirmasi' => DemoService::KONFIRMASI,
                'paham' => '1',
            ])
            ->assertSessionHas('success');

        $this->assertSame(0, Tiket::where('is_demo', true)->count());
        $this->assertSame(0, WarkahPermintaan::where('is_demo', true)->count());
        $this->assertSame(0, Pengaduan::where('is_demo', true)->count());
        $this->assertSame(0, User::where('is_demo', true)->count());

        // Data asli tetap utuh.
        $this->assertDatabaseHas('tikets', ['id' => $asli->id]);
        $this->assertDatabaseHas('pengaduans', ['id' => $pengaduanAsli->id]);

        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'RESET DEMO']);
    }

    public function test_menu_demo_hanya_tampil_saat_mode_demo_aktif(): void
    {
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Data & Skenario Demo');

        $this->aktifkanModeDemo();

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Data & Skenario Demo');
    }
}
