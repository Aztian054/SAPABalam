<?php

namespace Tests\Feature;

use App\Models\ArsipFolder;
use App\Models\ArsipTiket;
use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur bulk archive: halaman Selesai (filter + checkbox) dan
 * aksi arsip massal (POST /admin/selesai/arsipkan-massal).
 */
class AdminSelesaiArsipTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

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

    private function folder(): ArsipFolder
    {
        return ArsipFolder::create([
            'nama_folder' => 'Arsip 2026',
            'lokasi_fisik' => 'Rak A',
        ]);
    }

    private function tiketSelesai(array $overrides = []): Tiket
    {
        $jp = JenisPermohonan::first() ?? $this->jenisPermohonan();

        return Tiket::create(array_merge([
            'kode_tiket' => 'K/'.fake()->unique()->numberBetween(100, 9999).'/260913/1',
            'nomor_antrian' => 'A-'.fake()->numberBetween(1, 999),
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => now()->subDays(10)->toDateString(),
            'jenis_permohonan_id' => $jp->id,
            'nama_pemohon' => fake()->name(),
            'no_hp_pemohon' => '08'.fake()->numberBetween(1000000000, 9999999999),
            'jumlah_bidang' => 1,
            'status' => 'selesai',
            'tanggal_selesai' => now()->subDay()->toDateString(),
        ], $overrides));
    }

    // ------------------------------------------------------------------
    // Halaman Selesai
    // ------------------------------------------------------------------

    public function test_admin_dapat_melihat_halaman_selesai_dengan_filter(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $tiket = $this->tiketSelesai(['nama_pemohon' => 'Pemohon Filter Test']);
        $folder = $this->folder();

        $this->get(route('admin.selesai'))
            ->assertOk()
            ->assertSee($tiket->kode_tiket)
            ->assertSee('Tahun Selesai')
            ->assertSee($folder->nama_folder)
            // Script bulk-archive harus benar-benar ter-render via @yield('scripts')
            ->assertSee('openBulkModal')
            ->assertSee('Arsipkan Semua Hasil Filter')
            ->assertSee('id="bulkForm"', false);
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

    // ------------------------------------------------------------------
    // Arsip Massal — mode ids (terpilih)
    // ------------------------------------------------------------------

    public function test_arsip_massal_dengan_ids_membuat_arsip_dan_riwayat(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $folder = $this->folder();
        $a = $this->tiketSelesai();
        $b = $this->tiketSelesai();

        $response = $this->post(route('admin.selesai.arsipkan-massal'), [
            'folder_id' => $folder->id,
            'nama_arsip' => 'Arsip Massal Agustus',
            'tipe' => 'Sertipikat',
            'keterangan' => 'Bulk dari admin',
            'ids' => "{$a->id},{$b->id}",
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseCount('arsip_tiket', 2);
        $this->assertDatabaseHas('arsip_tiket', [
            'tiket_id' => $a->id,
            'folder_id' => $folder->id,
            'nama_arsip' => 'Arsip Massal Agustus',
            'tipe_dokumen' => 'Sertipikat',
        ]);
        $this->assertDatabaseHas('arsip_tiket', ['tiket_id' => $b->id]);

        $this->assertDatabaseCount('riwayat_statuses', 2);
        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $a->id,
            'stage_dari' => 'DB Admin',
            'stage_ke' => 'Arsip',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_arsip_massal_melewati_tiket_yang_sudah_diarsip(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $folder = $this->folder();
        $sudah = $this->tiketSelesai();
        $belum = $this->tiketSelesai();

        ArsipTiket::create([
            'tiket_id' => $sudah->id,
            'folder_id' => $folder->id,
            'nama_arsip' => 'Sudah Diarsip',
            'tanggal_arsip' => now()->toDateString(),
        ]);

        $this->post(route('admin.selesai.arsipkan-massal'), [
            'folder_id' => $folder->id,
            'nama_arsip' => 'Batch Baru',
            'ids' => "{$sudah->id},{$belum->id}",
        ])->assertSessionHas('success');

        // Hanya tiket baru yang mendapat ArsipTiket tambahan.
        $this->assertDatabaseCount('arsip_tiket', 2);
        $this->assertDatabaseHas('arsip_tiket', ['tiket_id' => $belum->id]);
    }

    public function test_tiket_belum_selesai_tidak_diikutkan_arsip_massal(): void
    {
        $this->actingAs($this->user('admin'));

        $folder = $this->folder();
        $selesai = $this->tiketSelesai();
        $belumSelesai = $this->tiketSelesai(['status' => 'verifikasi']);

        $this->post(route('admin.selesai.arsipkan-massal'), [
            'folder_id' => $folder->id,
            'nama_arsip' => 'Batch',
            'ids' => "{$selesai->id},{$belumSelesai->id}",
        ])->assertSessionHas('success');

        $this->assertDatabaseCount('arsip_tiket', 1);
        $this->assertDatabaseHas('arsip_tiket', ['tiket_id' => $selesai->id]);
    }

    // ------------------------------------------------------------------
    // Arsip Massal — mode semua (sesuai filter)
    // ------------------------------------------------------------------

    public function test_arsip_massal_semua_menggunakan_filter_aktif(): void
    {
        $this->actingAs($this->user('admin'));

        $folder = $this->folder();

        $masukFilter = $this->tiketSelesai(['nama_pemohon' => 'Pemohon Target Massal']);
        $luarFilter = $this->tiketSelesai(['nama_pemohon' => 'Pemohon Lain']);

        $this->post(route('admin.selesai.arsipkan-massal'), [
            'folder_id' => $folder->id,
            'nama_arsip' => 'Semua Hasil Filter',
            'semua' => 1,
            'q' => 'Pemohon Target Massal',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('arsip_tiket', [
            'tiket_id' => $masukFilter->id,
            'nama_arsip' => 'Semua Hasil Filter',
        ]);
        $this->assertDatabaseMissing('arsip_tiket', ['tiket_id' => $luarFilter->id]);
    }

    // ------------------------------------------------------------------
    // Otorisasi & Validasi
    // ------------------------------------------------------------------

    public function test_non_admin_ditahan_dari_rute_arsip_massal(): void
    {
        $loket = $this->user('loket');
        $this->actingAs($loket);

        $folder = $this->folder();
        $tiket = $this->tiketSelesai();

        $this->post(route('admin.selesai.arsipkan-massal'), [
            'folder_id' => $folder->id,
            'nama_arsip' => 'Tidak Boleh',
            'ids' => (string) $tiket->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('arsip_tiket', 0);
    }

    public function test_arsip_massal_memvalidasi_field_wajib(): void
    {
        $this->actingAs($this->user('admin'));

        $tiket = $this->tiketSelesai();

        $this->post(route('admin.selesai.arsipkan-massal'), [
            'ids' => (string) $tiket->id,
        ])->assertSessionHasErrors(['folder_id', 'nama_arsip']);

        $this->assertDatabaseCount('arsip_tiket', 0);
    }
}
