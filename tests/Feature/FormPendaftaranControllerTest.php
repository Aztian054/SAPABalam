<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\KategoriPermohonan;
use App\Models\PersyaratanDokumen;
use App\Models\SaranKoreksi;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menu "Kelola Form Pendaftaran": CRUD master konten form
 * (Jenis Permohonan + persyaratan, Kategori, Jenis Hak, Saran Koreksi).
 */
class FormPendaftaranControllerTest extends TestCase
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

    private function kategori(string $kode = 'umum'): KategoriPermohonan
    {
        return KategoriPermohonan::create([
            'kode' => $kode,
            'nama' => ucfirst($kode),
            'urutan' => 1,
            'is_active' => true,
        ]);
    }

    private function jenisHak(string $kode = 'HM'): JenisHak
    {
        return JenisHak::create([
            'kode' => $kode,
            'nama' => 'Hak '.$kode,
            'urutan' => 1,
            'is_active' => true,
        ]);
    }

    private function jenisPermohonan(?KategoriPermohonan $kategori = null): JenisPermohonan
    {
        if ($kategori === null) {
            $kategori = $this->kategori();
        }

        return JenisPermohonan::create([
            'kode' => 'JP'.fake()->unique()->numberBetween(100, 999),
            'nama' => 'Permohonan Uji',
            'kategori' => $kategori->kode,
            'is_active' => true,
        ]);
    }

    private function tiket(JenisPermohonan $jenis): Tiket
    {
        return Tiket::create([
            'kode_tiket' => 'TEST/'.now()->timestamp,
            'nomor_antrian' => 'A-001',
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Uji',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'diterima',
        ]);
    }

    public function test_halaman_kelola_form_pendaftaran_hanya_untuk_admin(): void
    {
        $petugas = $this->user('loket');

        $this->actingAs($petugas)
            ->get(route('admin.form-pendaftaran'))
            ->assertForbidden();
    }

    public function test_admin_dapat_membuka_halaman_kelola_form_pendaftaran(): void
    {
        $kategori = $this->kategori();
        $jenis = $this->jenisPermohonan($kategori);
        $this->jenisHak();

        $this->actingAs($this->user('admin'))
            ->get(route('admin.form-pendaftaran'))
            ->assertOk()
            ->assertSee('Jenis Permohonan')
            ->assertSee('Saran Koreksi')
            ->assertSee('Jenis Hak')
            ->assertSee($jenis->kode);
    }

    public function test_admin_dapat_menambah_kategori(): void
    {
        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.kategori.store'), [
                'kode' => 'bmn',
                'nama' => 'Barang Milik Negara',
                'urutan' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_permohonans', [
            'kode' => 'bmn',
            'nama' => 'Barang Milik Negara',
            'urutan' => 2,
            'is_active' => true,
        ]);
    }

    public function test_kategori_masih_dipakai_jenis_permohonan_tidak_dapat_dihapus(): void
    {
        $kategori = $this->kategori();
        $this->jenisPermohonan($kategori);

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.kategori.hapus', $kategori->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_permohonans', ['id' => $kategori->id]);
    }

    public function test_admin_dapat_menambah_jenis_hak(): void
    {
        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.jenis-hak.store'), [
                'kode' => 'HPL',
                'nama' => 'Hak Pengelolaan Lahan',
                'urutan' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('jenis_haks', [
            'kode' => 'HPL',
            'nama' => 'Hak Pengelolaan Lahan',
            'is_active' => true,
        ]);
    }

    public function test_admin_dapat_menambah_jenis_permohonan_dengan_persyaratan(): void
    {
        $this->kategori();

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.jenis-permohonan.store'), [
                'kode' => 'JP01',
                'nama' => 'Pendaftaran Pertama Kali',
                'kategori' => 'umum',
                'deskripsi' => 'Lorem ipsum dolor',
                'is_active' => 1,
                'persyaratan' => [
                    ['nama_dokumen' => 'Fotokopi KTP', 'wajib' => 1],
                    ['nama_dokumen' => 'SPPT PBB', 'wajib' => 1, 'keterangan' => 'Tahun berjalan'],
                ],
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('jenis_permohonans', [
            'kode' => 'JP01',
            'kategori' => 'umum',
            'is_active' => true,
        ]);

        $jp = JenisPermohonan::where('kode', 'JP01')->firstOrFail();
        $this->assertSame(2, $jp->persyaratanDokumens()->count());
        $this->assertDatabaseHas('persyaratan_dokumens', [
            'jenis_permohonan_id' => $jp->id,
            'nama_dokumen' => 'SPPT PBB',
            'wajib' => true,
        ]);
    }

    public function test_baris_persyaratan_kosong_dilewati_saat_menambah_jenis_permohonan(): void
    {
        $this->kategori();

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.jenis-permohonan.store'), [
                'kode' => 'JP02',
                'nama' => 'Tanpa Persyaratan Khusus',
                'kategori' => 'umum',
                'is_active' => 1,
                'persyaratan' => [
                    ['nama_dokumen' => '', 'wajib' => 1],
                    ['nama_dokumen' => '', 'wajib' => 1],
                ],
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHasNoErrors();

        $jp = JenisPermohonan::where('kode', 'JP02')->firstOrFail();
        $this->assertSame(0, $jp->persyaratanDokumens()->count());
    }

    public function test_jenis_permohonan_dengan_kode_duplikat_ditolak(): void
    {
        $kategori = $this->kategori();
        JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Satu',
            'kategori' => $kategori->kode,
            'is_active' => true,
        ]);

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.jenis-permohonan.store'), [
                'kode' => 'JP01',
                'nama' => 'Dua',
                'kategori' => $kategori->kode,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('kode');

        $this->assertSame(1, JenisPermohonan::where('kode', 'JP01')->count());
    }

    public function test_admin_dapat_mengubah_jenis_permohonan_dan_mengganti_persyaratan(): void
    {
        $kategori = $this->kategori();
        $jp = $this->jenisPermohonan($kategori);
        PersyaratanDokumen::create([
            'jenis_permohonan_id' => $jp->id,
            'nama_dokumen' => 'Dokumen Lama',
            'wajib' => false,
            'urutan' => 1,
        ]);

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran.jenis-permohonan.edit', $jp->id))
            ->put(route('admin.form-pendaftaran.jenis-permohonan.update', $jp->id), [
                'kode' => $jp->kode,
                'nama' => 'Nama Baru',
                'kategori' => $kategori->kode,
                'is_active' => 1,
                'persyaratan' => [
                    ['nama_dokumen' => 'Dokumen Baru', 'wajib' => 1],
                ],
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('jenis_permohonans', ['id' => $jp->id, 'nama' => 'Nama Baru']);

        $rows = PersyaratanDokumen::where('jenis_permohonan_id', $jp->id)->get();
        $this->assertSame(1, $rows->count());
        $this->assertSame('Dokumen Baru', $rows->first()->nama_dokumen);
    }

    public function test_jenis_permohonan_yang_sudah_dipakai_tiket_tidak_dapat_dihapus(): void
    {
        $jenis = $this->jenisPermohonan();
        $this->tiket($jenis);

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.jenis-permohonan.hapus', $jenis->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('jenis_permohonans', ['id' => $jenis->id]);
    }

    public function test_jenis_permohonan_tanpa_tiket_dapat_dihapus(): void
    {
        $jenis = $this->jenisPermohonan();

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.jenis-permohonan.hapus', $jenis->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('jenis_permohonans', ['id' => $jenis->id]);
    }

    public function test_admin_dapat_menambah_saran_koreksi(): void
    {
        $jenis = $this->jenisPermohonan();

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran'))
            ->post(route('admin.form-pendaftaran.saran-koreksi.store'), [
                'jenis_permohonan_id' => $jenis->id,
                'nama_dokumen_kurang' => 'Sertifikat Asli',
                'pesan_koreksi' => 'Mohon melengkapi sertifikat asli.',
                'dasar_hukum' => 'PP 24/1997',
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('saran_koreksis', [
            'jenis_permohonan_id' => $jenis->id,
            'nama_dokumen_kurang' => 'Sertifikat Asli',
            'dasar_hukum' => 'PP 24/1997',
        ]);
    }

    public function test_admin_dapat_mengubah_saran_koreksi(): void
    {
        $jenis = $this->jenisPermohonan();
        $saran = SaranKoreksi::create([
            'jenis_permohonan_id' => $jenis->id,
            'nama_dokumen_kurang' => 'Dokumen Lama',
            'pesan_koreksi' => 'Pesan lama.',
        ]);

        $this->actingAs($this->user('admin'))
            ->from(route('admin.form-pendaftaran.saran-koreksi.edit', $saran->id))
            ->put(route('admin.form-pendaftaran.saran-koreksi.update', $saran->id), [
                'jenis_permohonan_id' => $jenis->id,
                'nama_dokumen_kurang' => 'Dokumen Baru',
                'pesan_koreksi' => 'Pesan baru.',
                'dasar_hukum' => '',
            ])
            ->assertRedirect(route('admin.form-pendaftaran'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('saran_koreksis', [
            'id' => $saran->id,
            'nama_dokumen_kurang' => 'Dokumen Baru',
            'pesan_koreksi' => 'Pesan baru.',
        ]);
    }

    public function test_jenis_hak_yang_sudah_dipakai_bidang_tanah_tidak_dapat_dihapus(): void
    {
        $jenis = $this->jenisPermohonan();
        $hak = $this->jenisHak('HGB');
        $tiket = $this->tiket($jenis);
        BidangTanah::create([
            'tiket_id' => $tiket->id,
            'jenis_hak' => 'HGB',
            'urutan' => 1,
        ]);

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.jenis-hak.hapus', $hak->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('jenis_haks', ['id' => $hak->id]);
    }

    public function test_admin_dapat_menonaktifkan_dan_mengaktifkan_jenis_hak(): void
    {
        $hak = $this->jenisHak();

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.jenis-hak.toggle', $hak->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($hak->fresh()->is_active);

        $this->actingAs($this->user('admin'))
            ->post(route('admin.form-pendaftaran.jenis-hak.toggle', $hak->id))
            ->assertSessionHas('success');

        $this->assertTrue($hak->fresh()->is_active);
    }

    public function test_bidang_tanah_dengan_jenis_hak_yang_tidak_terdaftar_ditolak(): void
    {
        $jenis = $this->jenisPermohonan();
        $this->jenisHak('HM');

        $this->actingAs($this->user('loket'))
            ->from(route('loket.create'))
            ->post(route('loket.store'), [
                'kode_tiket' => 'BLM/2026/0001',
                'jenis_permohonan_id' => $jenis->id,
                'nama_pemohon' => 'Pemohon Uji',
                'no_hp_pemohon' => '081200000000',
                'jumlah_bidang' => 1,
                'bidang' => [
                    ['jenis_hak' => 'XZY'],
                ],
            ])
            ->assertSessionHasErrors('bidang.0.jenis_hak');

        $this->assertDatabaseMissing('tikets', ['kode_tiket' => 'BLM/2026/0001']);
    }
}
