<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Admin = monitoring + manajemen.
 * Admin tidak boleh mendaftarkan, memproses, atau mengubah berkas.
 */
class AdminReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => $role,
            'username' => $role.'_'.str()->random(6),
            'role' => $role,
            'is_active' => true,
        ], $atribut));
    }

    private function tiket(): Tiket
    {
        $jp = JenisPermohonan::firstOrCreate(
            ['kode' => 'JP01'],
            ['nama' => 'Peralihan Hak', 'kategori' => 'umum', 'is_active' => true],
        );

        return Tiket::create([
            'kode_tiket' => 'K/001/260913/1',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jp->id,
            'nama_pemohon' => 'Pemohon Uji',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);
    }

    private function pengaduan(User $pembuat): Pengaduan
    {
        return Pengaduan::create([
            'nomor_pengaduan' => 'LP001',
            'tanggal_buat' => now()->toDateString(),
            'pembuat_id' => $pembuat->id,
            'nama_pengadu' => 'Pengadu Uji',
            'jenis' => 'pungli',
            'uraian' => 'Uji pengaduan untuk monitoring Admin.',
            'seksi_tujuan' => '1',
            'status' => 'terkirim',
        ]);
    }

    public function test_rute_operasional_admin_sudah_dihapus(): void
    {
        $this->assertFalse(Route::has('admin.create_tiket'));
        $this->assertFalse(Route::has('admin.store_tiket'));
        $this->assertFalse(Route::has('admin.tiket.lepas'));
        $this->assertFalse(Route::has('admin.arsipkan'));
        $this->assertFalse(Route::has('admin.selesai.arsipkan-massal'));
    }

    public function test_admin_melihat_daftar_berkas_tanpa_tombol_tambah(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Mode Monitoring')
            ->assertDontSee('Tambah Berkas')
            ->assertDontSee('Lepas Tiket');
    }

    public function test_admin_melihat_pengaduan_tanpa_tombol_aksi(): void
    {
        $admin = $this->user('admin');
        $pengadu = $this->user('loket_pengaduan');
        $pengaduan = $this->pengaduan($pengadu);

        $this->actingAs($admin)
            ->get(route('pengaduan-unit.index'))
            ->assertOk()
            ->assertSee($pengaduan->nomor_pengaduan)
            ->assertDontSee('>Terima</button>', false);
    }

    public function test_admin_tidak_dapat_memproses_berkas(): void
    {
        $admin = $this->user('admin');
        $tiket = $this->tiket();

        $this->actingAs($admin)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('penyerahan.add', $tiket))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('pendaftaran.tahap1.store'), [
                'nama_pemohon' => 'Pemohon Admin',
                'no_hp_pemohon' => '081200000001',
                'kelurahan_desa' => 'Kedaton',
            ])
            ->assertForbidden();
    }

    public function test_admin_tidak_dapat_mengubah_status_pengaduan(): void
    {
        $admin = $this->user('admin');
        $pengadu = $this->user('loket_pengaduan');
        $pengaduan = $this->pengaduan($pengadu);

        $this->actingAs($admin)
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertForbidden();

        $this->assertSame('terkirim', $pengaduan->fresh()->status);
    }
}
