<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Back office Seksi 1/2 (Blueprint V2, modul 04–05):
 * Cari → ADD → KIRIM (Selesai/Tolak) → ADD ULANG, pantauan Kasi,
 * dan Lepas Tiket oleh Admin.
 */
class SeksiBackOfficeTest extends TestCase
{
    use RefreshDatabase;

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

    private function tiket(string $kode, array $atribut = []): Tiket
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
            ...$atribut,
        ]);
    }

    public function test_staf_add_mencatat_penanggung_peran_dan_riwayat(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026001');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $tiket->refresh();

        $this->assertSame('diproses', $tiket->status);
        $this->assertSame($staf->id, $tiket->penanggung_jawab_id);
        $this->assertSame('verifikasi', $tiket->peran_bidang);
        $this->assertSame('Verifikasi', $tiket->peran_bidang_label);
        $this->assertNotNull($tiket->diambil_pada);

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'diproses',
            'changed_by' => $staf->id,
        ]);

        $this->actingAs($staf)
            ->get(route('seksi.tiket-saya'))
            ->assertOk()
            ->assertSee($tiket->kode_tiket)
            ->assertSee('Verifikasi');
    }

    public function test_peran_bidang_wajib_dipilih_saat_add(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026002');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), [])
            ->assertSessionHasErrors('peran_bidang');

        $this->assertSame('di_database', $tiket->fresh()->status);
    }

    public function test_staf_lain_tidak_bisa_add_atau_kirim_tiket_yang_sedang_dipegang(): void
    {
        $pemegang = $this->user('staf_seksi', ['seksi' => '1']);
        $lain = $this->user('staf_seksi', ['seksi' => '2']);
        $tiket = $this->tiket('JP26102026003');

        $this->actingAs($pemegang)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'validator_bt']);

        $this->actingAs($lain)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertSessionHas('error');

        $this->actingAs($lain)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Coba selesaikan'])
            ->assertSessionHas('error');

        $this->assertSame('diproses', $tiket->fresh()->status);
        $this->assertSame($pemegang->id, $tiket->fresh()->penanggung_jawab_id);
    }

    public function test_kirim_selesai_mengembalikan_tiket_ke_database(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $stafLain = $this->user('staf_seksi', ['seksi' => '2']);
        $tiket = $this->tiket('JP26102026004');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'alih_media_bt']);

        $this->actingAs($staf)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => ''])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($staf)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Berkas lengkap dan sesuai'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $tiket->refresh();

        $this->assertSame('di_database', $tiket->status);
        $this->assertNull($tiket->penanggung_jawab_id);
        $this->assertNull($tiket->diambil_pada);
        $this->assertSame('Alih Media BT', $tiket->peran_bidang_label);

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'di_database',
            'changed_by' => $staf->id,
        ]);

        // Staf lain kini boleh ADD.
        $this->actingAs($stafLain)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'validator_su'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $this->assertSame('diproses', $tiket->fresh()->status);
        $this->assertSame($stafLain->id, $tiket->fresh()->penanggung_jawab_id);
    }

    public function test_tolak_menahan_tiket_untuk_pemilik_dan_bisa_add_ulang(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $lain = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026005');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi']);

        $this->actingAs($staf)
            ->post(route('seksi.tolak', $tiket), ['catatan' => 'Berkas kurang lengkap'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $tiket->refresh();

        $this->assertSame('ditolak', $tiket->status);
        $this->assertSame($staf->id, $tiket->penanggung_jawab_id);
        $this->assertSame('Berkas kurang lengkap', $tiket->alasan_penolakan);
        $this->assertNotNull($tiket->ditolak_pada);

        // Staf lain tidak boleh ADD tiket yang ditolak akun lain.
        $this->actingAs($lain)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertSessionHas('error');

        // Pemilik boleh ADD ULANG.
        $this->actingAs($staf)
            ->get(route('seksi.ditolak'))
            ->assertOk()
            ->assertSee('Menunggu Perbaikan');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $tiket->refresh();

        $this->assertSame('diproses', $tiket->status);
        $this->assertNotNull($tiket->ditolak_pada);

        $this->actingAs($staf)
            ->get(route('seksi.ditolak'))
            ->assertOk()
            ->assertSee('Sudah ditindaklanjuti');

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'diproses',
            'changed_by' => $staf->id,
            'keterangan' => 'ADD ULANG — Verifikasi.',
        ]);
    }

    public function test_kasi_hanya_melihat_tiket_staf_seksinya(): void
    {
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);
        $staf1 = $this->user('staf_seksi', ['seksi' => '1']);
        $staf2 = $this->user('staf_seksi', ['seksi' => '2']);

        $tiketSeksi1 = $this->tiket('JP26102026006');
        $tiketSeksi2 = $this->tiket('JP26102026007');
        $tiketDatabase = $this->tiket('JP26102026008');

        $this->actingAs($staf1)->post(route('seksi.add', $tiketSeksi1), ['peran_bidang' => 'verifikasi']);
        $this->actingAs($staf2)->post(route('seksi.add', $tiketSeksi2), ['peran_bidang' => 'verifikasi']);

        // Flash dari POST staf di atas memuat kode tiket; bersihkan agar
        // assertion di bawah benar-benar menguji isi tabel Kasi.
        session()->forget(['success', 'error', 'warning']);

        $this->actingAs($kasi)
            ->get(route('seksi.index'))
            ->assertOk()
            ->assertSee($tiketSeksi1->kode_tiket)
            ->assertDontSee($tiketSeksi2->nama_pemohon)
            ->assertDontSee($tiketSeksi2->kode_tiket)
            ->assertDontSee($tiketDatabase->kode_tiket);

        $this->actingAs($kasi)->get(route('seksi.show', $tiketSeksi1))->assertOk();
        $this->actingAs($kasi)->get(route('seksi.show', $tiketSeksi2))->assertForbidden();

        // Kasi tidak boleh ADD.
        $this->actingAs($kasi)
            ->post(route('seksi.add', $tiketDatabase), ['peran_bidang' => 'verifikasi'])
            ->assertForbidden();
    }

    public function test_admin_tidak_bisa_lepas_tiket(): void
    {
        $admin = $this->user('admin');
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026009');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi']);

        $this->assertFalse(Route::has('admin.tiket.lepas'));

        $this->actingAs($admin)
            ->post('/admin/tiket/'.$tiket->id.'/lepas', ['alasan' => 'Staf cuti'])
            ->assertNotFound();

        $tiket->refresh();

        // Berkas tetap dipegang staf; Admin tidak dapat melepas.
        $this->assertSame('diproses', $tiket->status);
        $this->assertSame($staf->id, $tiket->penanggung_jawab_id);
    }

    public function test_daftar_kosong_sampai_pencarian_ditemukan(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026010', ['no_hak_sekarang' => 'HM.777/Kedaton']);

        $this->actingAs($staf)
            ->get(route('seksi.index'))
            ->assertOk()
            ->assertSee('Ketik No Tiket')
            ->assertDontSee($tiket->kode_tiket);

        $this->actingAs($staf)
            ->get(route('seksi.index', ['q' => 'JP 26102026 010']))
            ->assertOk()
            ->assertSee($tiket->kode_tiket);

        $this->actingAs($staf)
            ->get(route('seksi.index', ['q' => 'HM.777']))
            ->assertOk()
            ->assertSee($tiket->kode_tiket);
    }
}
