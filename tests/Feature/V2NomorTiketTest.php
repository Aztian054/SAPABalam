<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\PendaftaranDraft;
use App\Models\Tiket;
use App\Models\User;
use App\Services\TiketNomorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Fondasi V2: penomoran JP otomatis (tanpa input manual), dan jabatan V2 (seksi).
 */
class V2NomorTiketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function user(string $role = 'loket_pendaftaran', array $atribut = []): User
    {
        return User::factory()->create([
            'name' => $role,
            'username' => $role.'_'.str()->random(6),
            'role' => $role,
            'is_active' => true,
            ...$atribut,
        ]);
    }

    private function jenis(): JenisPermohonan
    {
        return JenisPermohonan::firstOrCreate(
            ['kode' => 'JP01'],
            ['nama' => 'Peralihan Hak', 'kategori' => 'umum', 'is_active' => true]
        );
    }

    private function tiket(string $kode, User $pembuat): Tiket
    {
        return Tiket::create([
            'kode_tiket' => $kode,
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $this->jenis()->id,
            'nama_pemohon' => 'Pemohon Uji',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
            'created_by' => $pembuat->id,
        ]);
    }

    public function test_nomor_tiket_dibuat_berurutan_dan_tidak_bentrok_dengan_draf(): void
    {
        $service = app(TiketNomorService::class);
        $pembuat = $this->user();

        $this->assertSame('JP26102026001', $service->generate());

        PendaftaranDraft::create([
            'user_id' => $pembuat->id,
            'kode_tiket' => 'JP26102026001',
            'tahap' => 1,
            'ploting_status' => 'belum',
        ]);

        $this->assertSame('JP26102026002', $service->generate());

        $this->tiket('JP26102026002', $pembuat);

        $this->assertSame('JP26102026003', $service->generate());
        $this->assertFalse($service->tersedia('JP26102026001'));
    }

    public function test_nomor_tiket_manual_dari_loket_diabaikan(): void
    {
        $petugas = $this->user();

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap1.store'), [
                'nama_pemohon' => 'Pemohon Uji',
                'no_hp_pemohon' => '081200000000',
                'kelurahan_desa' => 'Kedaton',
                'kode_tiket' => 'MANUAL-LOKET-01',
            ])
            ->assertRedirect();

        $draf = PendaftaranDraft::sole();

        $this->assertSame('JP26102026001', $draf->kode_tiket);
    }

    public function test_admin_tidak_memiliki_rute_tambah_berkas(): void
    {
        $this->actingAs($this->user('admin'))
            ->post('/admin/tambah-tiket', [])
            ->assertNotFound();

        $this->assertSame(0, Tiket::count());
    }

    public function test_jabatan_v2_menyimpan_seksi_dan_label(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '2']);
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);
        $superAdmin = $this->user('super_admin');

        $this->assertSame('2', $staf->fresh()->seksi);
        $this->assertSame('Seksi 2', $staf->seksi_label);
        $this->assertSame('Seksi 1', $kasi->seksi_label);
        $this->assertTrue($staf->isStafSeksi());
        $this->assertTrue($kasi->isKasiSeksi());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    public function test_dashboard_aman_untuk_jabatan_v2(): void
    {
        foreach (['loket_pendaftaran', 'loket_ploting', 'staf_seksi', 'kasi_seksi', 'loket_penyerahan', 'loket_pengaduan', 'kakan', 'super_admin'] as $role) {
            $user = $this->user($role);

            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        }
    }
}
