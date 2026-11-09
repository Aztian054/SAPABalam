<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Pengaduan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Pengaduan LP (Blueprint V2, modul 08–09):
 * Loket Pengaduan membuat & mengirim, unit tujuan menerima/menutup/mengembalikan.
 */
class PengaduanTest extends TestCase
{
    use RefreshDatabase;

    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 09:00:00');

        JenisPermohonan::create([
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

    private function pengaduan(string $seksi = '3', array $atribut = []): Pengaduan
    {
        $this->urut++;

        return Pengaduan::create([
            'nomor_pengaduan' => 'LP26102026'.str_pad((string) $this->urut, 3, '0', STR_PAD_LEFT),
            'tanggal_buat' => now()->toDateString(),
            'nama_pengadu' => 'Pengadu '.$this->urut,
            'no_hp_pengadu' => '08120000000'.$this->urut,
            'jenis' => 'buruknya_pelayanan',
            'uraian' => 'Uraian pengaduan yang cukup panjang untuk diuji pada sistem.',
            'seksi_tujuan' => $seksi,
            'status' => 'terkirim',
            ...$atribut,
        ]);
    }

    private function tiket(string $kode): Tiket
    {
        return Tiket::create([
            'kode_tiket' => $kode,
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => JenisPermohonan::firstOrFail()->id,
            'nama_pemohon' => 'Pemohon '.$kode,
            'nik_pemohon' => '1871010101010001',
            'no_hp_pemohon' => '081234567890',
            'no_hak_sekarang' => 'HM.123/Kedaton',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);
    }

    public function test_loket_membuat_pengaduan_dengan_nomor_lp_dan_riwayat(): void
    {
        $loket = $this->user('loket_pengaduan');

        $this->actingAs($loket)
            ->post(route('pengaduan.store'), [
                'nama_pengadu' => 'Budi',
                'no_hp_pengadu' => '08123456789',
                'jenis' => 'buruknya_pelayanan',
                'uraian' => 'Pelayanan loket lambat saat pengambilan berkas.',
                'seksi_tujuan' => '1',
            ])
            ->assertRedirect();

        $pengaduan = Pengaduan::firstOrFail();

        $this->assertSame('LP26102026001', $pengaduan->nomor_pengaduan);
        $this->assertSame('terkirim', $pengaduan->status);
        $this->assertSame($loket->id, $pengaduan->pembuat_id);
        $this->assertSame('Seksi 1', $pengaduan->seksi_tujuan_label);

        $this->assertDatabaseHas('pengaduan_riwayats', [
            'pengaduan_id' => $pengaduan->id,
            'ke_status' => 'terkirim',
            'oleh_id' => $loket->id,
        ]);

        $this->actingAs($loket)
            ->get(route('pengaduan.index'))
            ->assertOk()
            ->assertSee('LP26102026001');

        $this->actingAs($loket)
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Pelayanan loket lambat saat pengambilan berkas.');
    }

    public function test_validasi_uraian_minimal_dan_hp_wajib(): void
    {
        $loket = $this->user('loket_pengaduan');

        $this->actingAs($loket)
            ->post(route('pengaduan.store'), [
                'jenis' => 'buruknya_pelayanan',
                'uraian' => 'pendek',
                'seksi_tujuan' => '1',
            ])
            ->assertSessionHasErrors(['nama_pengadu', 'no_hp_pengadu', 'uraian']);

        $this->assertSame(0, Pengaduan::count());
    }

    public function test_anonim_boleh_tanpa_nama_dan_hp(): void
    {
        $loket = $this->user('loket_pengaduan');

        $this->actingAs($loket)
            ->post(route('pengaduan.store'), [
                'is_anonim' => '1',
                'jenis' => 'kritik_saran_aspirasi',
                'uraian' => 'Saran perbaikan ruang tunggu agar lebih nyaman.',
                'seksi_tujuan' => 'TU',
            ])
            ->assertRedirect();

        $pengaduan = Pengaduan::firstOrFail();

        $this->assertTrue($pengaduan->is_anonim);
        $this->assertNull($pengaduan->nama_pengadu);
        $this->assertNull($pengaduan->no_hp_pengadu);
        $this->assertSame('Anonim', $pengaduan->pengadu_label);

        $this->actingAs($loket)
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Anonim');
    }

    public function test_kasi_asli_minta_bantuan_ke_kasi_lain_dan_balasan(): void
    {
        $kasi1 = $this->user('kasi_seksi', ['seksi' => '1']);
        $kasi2 = $this->user('kasi_seksi', ['seksi' => '2']);
        $pengaduan = $this->pengaduan('1', ['nomor_pengaduan' => 'LP26102026099']);

        // Kasi 1 terima dulu
        $this->actingAs($kasi1)
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertRedirect();

        // Kasi 1 minta bantuan ke Kasi 2
        $this->actingAs($kasi1)
            ->post(route('pengaduan-unit.minta-bantuan', $pengaduan), [
                'ke_kasi' => '2',
                'catatan' => 'Mohon bantuan verifikasi berkas tanah ini.',
            ])
            ->assertRedirect();

        $pengaduan->refresh();
        $this->assertDatabaseHas('pengaduan_bantuans', [
            'pengaduan_id' => $pengaduan->id,
            'dari_kasi' => '1',
            'ke_kasi' => '2',
            'status' => 'diajukan',
        ]);
        $this->assertDatabaseHas('pengaduan_catatans', [
            'pengaduan_id' => $pengaduan->id,
            'jenis' => 'minta_bantuan',
        ]);

        // Kasi 2 balas bantuan
        $this->actingAs($kasi2)
            ->post(route('pengaduan-unit.balas-bantuan', $pengaduan), [
                'catatan' => 'Sudah diperiksa, berkas lengkap.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pengaduan_bantuans', [
            'pengaduan_id' => $pengaduan->id,
            'status' => 'ditanggapi',
        ]);
        $this->assertDatabaseHas('pengaduan_catatans', [
            'pengaduan_id' => $pengaduan->id,
            'jenis' => 'balasan_bantuan',
        ]);

        // Status masih proses (belum selesai)
        $this->assertSame('diterima', $pengaduan->fresh()->status);
    }

    public function test_kasi_bukan_asli_tidak_bisa_minta_bantuan(): void
    {
        $kasi2 = $this->user('kasi_seksi', ['seksi' => '2']);
        $pengaduan = $this->pengaduan('1', ['nomor_pengaduan' => 'LP26102026098']);

        $this->actingAs($kasi1 = $this->user('kasi_seksi', ['seksi' => '1']))
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertRedirect();

        // Kasi 2 (bukan asli) coba minta bantuan — harus ditolak
        $this->actingAs($kasi2)
            ->post(route('pengaduan-unit.minta-bantuan', $pengaduan), [
                'ke_kasi' => '3',
                'catatan' => 'Coba minta bantuan.',
            ])
            ->assertForbidden();
    }

    public function test_hanya_kasi_asli_yang_bisa_tutup(): void
    {
        $kasi1 = $this->user('kasi_seksi', ['seksi' => '1']);
        $kasi2 = $this->user('kasi_seksi', ['seksi' => '2']);
        $pengaduan = $this->pengaduan('1', ['nomor_pengaduan' => 'LP26102026097']);

        $this->actingAs($kasi1)
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertRedirect();

        // Kasi 2 (bukan asli) coba tutup — harus ditolak
        $this->actingAs($kasi2)
            ->post(route('pengaduan-unit.tutup', $pengaduan), [
                'catatan' => 'coba tutup dari kasi 2',
            ])
            ->assertForbidden();

        // Kasi 1 (asli) tutup — berhasil
        $this->actingAs($kasi1)
            ->post(route('pengaduan-unit.tutup', $pengaduan), [
                'catatan' => 'Sudah selesai ditangani oleh Kasi 1.',
            ])
            ->assertRedirect();

        $this->assertSame('selesai', $pengaduan->fresh()->status);
    }

    public function test_loket_hanya_melihat_pengaduan_yang_dibuatnya(): void
    {
        $loketA = $this->user('loket_pengaduan');
        $loketB = $this->user('loket_pengaduan');

        $milikA = $this->pengaduan('1', ['pembuat_id' => $loketA->id, 'nomor_pengaduan' => 'LP26102026010']);
        $milikB = $this->pengaduan('1', ['pembuat_id' => $loketB->id, 'nomor_pengaduan' => 'LP26102026011']);

        $this->actingAs($loketA)
            ->get(route('pengaduan.index'))
            ->assertOk()
            ->assertSee($milikA->nomor_pengaduan)
            ->assertDontSee($milikB->nomor_pengaduan);

        $this->actingAs($loketA)
            ->get(route('pengaduan.show', $milikB))
            ->assertForbidden();
    }

    public function test_unit_menerima_dan_menutup_pengaduan(): void
    {
        $kasi = $this->user('kasi_unit', ['seksi' => '3']);
        $pengaduan = $this->pengaduan('3', ['nomor_pengaduan' => 'LP26102026020']);

        $this->actingAs($kasi)
            ->get(route('pengaduan-unit.index'))
            ->assertOk()
            ->assertSee($pengaduan->nomor_pengaduan);

        $this->actingAs($kasi)
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertRedirect();

        $pengaduan->refresh();

        $this->assertSame('diterima', $pengaduan->status);
        $this->assertSame($kasi->id, $pengaduan->diterima_oleh_id);
        $this->assertNotNull($pengaduan->diterima_pada);

        // Catatan hasil wajib minimal 10 karakter.
        $this->actingAs($kasi)
            ->post(route('pengaduan-unit.tutup', $pengaduan), ['catatan' => 'pendek'])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($kasi)
            ->post(route('pengaduan-unit.tutup', $pengaduan), [
                'catatan' => 'Sudah dijelaskan kepada pengadu melalui telepon.',
            ])
            ->assertRedirect();

        $pengaduan->refresh();

        $this->assertSame('selesai', $pengaduan->status);
        $this->assertSame($kasi->id, $pengaduan->selesai_oleh_id);
        $this->assertNotNull($pengaduan->selesai_pada);
        $this->assertSame('Sudah dijelaskan kepada pengadu melalui telepon.', $pengaduan->catatan_terakhir);

        $this->actingAs($kasi)
            ->get(route('pengaduan-unit.selesai'))
            ->assertOk()
            ->assertSee($pengaduan->nomor_pengaduan);
    }

    public function test_unit_tidak_bisa_menangani_pengaduan_unit_lain(): void
    {
        $kasi4 = $this->user('kasi_unit', ['seksi' => '4']);
        $pengaduan = $this->pengaduan('3', ['nomor_pengaduan' => 'LP26102026030']);

        $this->actingAs($kasi4)
            ->get(route('pengaduan-unit.index'))
            ->assertOk()
            ->assertDontSee($pengaduan->nomor_pengaduan);

        $this->actingAs($kasi4)
            ->get(route('pengaduan-unit.show', $pengaduan))
            ->assertForbidden();

        $this->actingAs($kasi4)
            ->post(route('pengaduan-unit.terima', $pengaduan))
            ->assertForbidden();

        $this->assertSame('terkirim', $pengaduan->fresh()->status);
    }

    public function test_kasubbag_tu_menangani_pengaduan_tu(): void
    {
        $kasubbag = $this->user('kasubbag_tu');
        $pengaduanTu = $this->pengaduan('TU', ['nomor_pengaduan' => 'LP26102026040']);
        $pengaduanSeksi = $this->pengaduan('1', ['nomor_pengaduan' => 'LP26102026041']);

        $this->actingAs($kasubbag)
            ->get(route('pengaduan-unit.index'))
            ->assertOk()
            ->assertSee($pengaduanTu->nomor_pengaduan)
            ->assertDontSee($pengaduanSeksi->nomor_pengaduan);
    }

    public function test_kembalikan_lalu_kirim_ulang_mempertahankan_nomor(): void
    {
        $kasi3 = $this->user('kasi_unit', ['seksi' => '3']);
        $kasi4 = $this->user('kasi_unit', ['seksi' => '4']);
        $loket = $this->user('loket_pengaduan');

        $pengaduan = $this->pengaduan('3', [
            'nomor_pengaduan' => 'LP26102026050',
            'pembuat_id' => $loket->id,
        ]);

        $this->actingAs($kasi3)
            ->post(route('pengaduan-unit.kembalikan', $pengaduan), [
                'catatan' => 'Bukan kewenangan Seksi 3.',
            ])
            ->assertRedirect();

        $pengaduan->refresh();

        $this->assertSame('dikembalikan', $pengaduan->status);
        $this->assertNotNull($pengaduan->dikembalikan_pada);

        $this->actingAs($loket)
            ->get(route('pengaduan.show', $pengaduan))
            ->assertOk()
            ->assertSee('Bukan kewenangan Seksi 3.');

        $this->actingAs($loket)
            ->post(route('pengaduan.kirim-ulang', $pengaduan), [
                'seksi_tujuan' => '4',
                'catatan' => 'Sudah diarahkan ulang ke Seksi 4.',
            ])
            ->assertRedirect();

        $pengaduan->refresh();

        $this->assertSame('terkirim', $pengaduan->status);
        $this->assertSame('4', $pengaduan->seksi_tujuan);
        $this->assertSame('LP26102026050', $pengaduan->nomor_pengaduan);
        $this->assertSame(2, $pengaduan->riwayats()->count());

        $this->actingAs($kasi4)
            ->get(route('pengaduan-unit.index'))
            ->assertOk()
            ->assertSee('LP26102026050');
    }

    public function test_kasi_unit_tidak_bisa_membuat_pengaduan(): void
    {
        $kasi = $this->user('kasi_unit', ['seksi' => '3']);

        $this->actingAs($kasi)
            ->post(route('pengaduan.store'), [
                'nama_pengadu' => 'Budi',
                'no_hp_pengadu' => '08123456789',
                'jenis' => 'pelayanan',
                'uraian' => 'Mencoba membuat pengaduan dari akun unit.',
                'seksi_tujuan' => '3',
            ])
            ->assertForbidden();
    }

    public function test_penanda_terlambat_hanya_untuk_pengaduan_terkirim(): void
    {
        $pengaduan = $this->pengaduan('3', ['status' => 'terkirim']);

        $this->assertFalse($pengaduan->is_terlambat);

        $pengaduan->forceFill(['created_at' => now()->subDays(5)])->save();
        $this->assertTrue($pengaduan->fresh()->is_terlambat);

        $pengaduan->forceFill(['status' => 'selesai', 'selesai_pada' => now()])->save();
        $this->assertFalse($pengaduan->fresh()->is_terlambat);
    }
}
