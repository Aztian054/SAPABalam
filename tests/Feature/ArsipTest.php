<?php

namespace Tests\Feature;

use App\Models\ArsipTiket;
use App\Models\JenisPermohonan;
use App\Models\Notifikasi;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Arsip otomatis & Daftar Arsip (Blueprint V2, modul 13).
 */
class ArsipTest extends TestCase
{
    use RefreshDatabase;

    private JenisPermohonan $jenis;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 09:00:00');

        $this->jenis = JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create([
            'name' => $role.' '.str()->random(4),
            'username' => $role.'_'.str()->random(8),
            'role' => $role,
            'is_active' => true,
            ...$atribut,
        ]);
    }

    private function tiketSelesai(array $atribut = []): Tiket
    {
        return Tiket::create([
            'kode_tiket' => 'K/1/261026/1',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->subDays(40)->toDateString(),
            'jenis_permohonan_id' => $this->jenis->id,
            'nama_pemohon' => 'Pemohon Arsip',
            'nik_pemohon' => '1871010101010001',
            'no_hp_pemohon' => '081234567890',
            'no_hak_sekarang' => 'HM.123/Kedaton',
            'kelurahan_desa' => 'Kedaton',
            'kecamatan' => 'Kedaton',
            'jumlah_bidang' => 1,
            'status' => 'selesai',
            'tanggal_selesai' => now()->subDays(31)->toDateString(),
            'diserahkan_pada' => now()->subDays(31),
            ...$atribut,
        ]);
    }

    public function test_perintah_arsip_menandai_tiket_selesai_dan_membuat_catatan(): void
    {
        $admin = $this->user('admin');
        $tiket = $this->tiketSelesai();

        $this->artisan('loket:arsipkan-tiket')->assertExitCode(0);

        $tiket->refresh();
        $this->assertNotNull($tiket->diarsipkan_pada);
        $this->assertTrue($tiket->is_arsip);
        $this->assertSame('selesai', $tiket->status);

        $this->assertDatabaseHas('arsip_tiket', [
            'tiket_id' => $tiket->id,
            'nama_arsip' => $tiket->kode_tiket,
        ]);

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'Arsip',
        ]);

        $this->assertSame(1, Notifikasi::where('user_id', $admin->id)
            ->where('tipe', 'tiket.diarsipkan')
            ->count());
    }

    public function test_perintah_arsip_tidak_menyentuh_tiket_belum_cukup_umur(): void
    {
        $this->tiketSelesai([
            'kode_tiket' => 'K/2/261026/2',
            'diserahkan_pada' => now()->subDays(3),
            'tanggal_selesai' => now()->subDays(3)->toDateString(),
        ]);

        $this->artisan('loket:arsipkan-tiket')->assertExitCode(0);

        $this->assertSame(0, Tiket::arsip()->count());
        $this->assertSame(0, ArsipTiket::count());
        $this->assertSame(0, RiwayatStatus::where('stage_ke', 'Arsip')->count());
    }

    public function test_daftar_arsip_hanya_menampilkan_tiket_terarsip(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $terarsip = $this->tiketSelesai([
            'diarsipkan_pada' => now(),
        ]);

        $this->tiketSelesai([
            'kode_tiket' => 'K/3/261026/3',
            'nama_pemohon' => 'Pemohon Belum Arsip',
        ]);

        $this->actingAs($loket)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertSee($terarsip->kode_tiket)
            ->assertDontSee('Pemohon Belum Arsip');
    }

    public function test_daftar_arsip_menolak_role_tanpa_akses(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($staf)->get(route('arsip.index'))->assertForbidden();
    }

    public function test_detail_arsip_read_only_dan_menolak_tiket_belum_arsip(): void
    {
        $petugas = $this->user('petugas_warkah');

        $terarsip = $this->tiketSelesai(['diarsipkan_pada' => now()]);
        $belum = $this->tiketSelesai([
            'kode_tiket' => 'K/4/261026/4',
            'nama_pemohon' => 'Pemohon Belum Arsip',
        ]);

        $this->actingAs($petugas)
            ->get(route('arsip.show', $terarsip))
            ->assertOk()
            ->assertSee('Arsip — read-only')
            ->assertDontSee('SERAHKAN');

        $this->actingAs($petugas)
            ->get(route('arsip.show', $belum))
            ->assertNotFound();
    }

    public function test_lama_simpan_arsip_dihitung_dari_penyerahan(): void
    {
        $tiket = $this->tiketSelesai([
            'diserahkan_pada' => now()->subDays(31),
            'diarsipkan_pada' => now(),
        ]);

        $this->assertSame('31 hari', $tiket->lama_simpan_arsip);
    }
}
