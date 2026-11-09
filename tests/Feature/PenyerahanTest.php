<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Loket Penyerahan (Blueprint V2, modul 07):
 * Antrian → ADD → SELESAI (tanpa Tolak).
 */
class PenyerahanTest extends TestCase
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

    public function test_antrian_kosong_sampai_pencarian_lalu_add_menahan_tiket(): void
    {
        $loket = $this->user('loket_penyerahan');
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $tiket = $this->tiket('JP26102026101');

        $this->actingAs($loket)
            ->get(route('penyerahan.index'))
            ->assertOk()
            ->assertSee('Ketik No Tiket')
            ->assertDontSee($tiket->kode_tiket);

        $this->actingAs($loket)
            ->get(route('penyerahan.index', ['q' => 'JP26102026101']))
            ->assertOk()
            ->assertSee($tiket->kode_tiket);

        $this->actingAs($loket)
            ->post(route('penyerahan.add', $tiket))
            ->assertRedirect(route('penyerahan.tiket-saya'))
            ->assertSessionHas('success')
            ->assertSessionHas('warning');

        $tiket->refresh();

        $this->assertSame('diproses', $tiket->status);
        $this->assertSame($loket->id, $tiket->penanggung_jawab_id);
        $this->assertNotNull($tiket->diambil_pada);

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'diproses',
            'changed_by' => $loket->id,
        ]);

        $this->actingAs($loket)
            ->get(route('penyerahan.tiket-saya'))
            ->assertOk()
            ->assertSee($tiket->kode_tiket);

        // Staf Seksi tidak boleh ADD tiket yang sedang dipegang penyerahan.
        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertSessionHas('error');

        $this->assertSame('diproses', $tiket->fresh()->status);
    }

    public function test_selesai_wajib_penerima_dan_identitas_bila_kuasa(): void
    {
        $loket = $this->user('loket_penyerahan');
        $tiket = $this->tiket('JP26102026102');

        $this->actingAs($loket)->post(route('penyerahan.add', $tiket));

        $this->actingAs($loket)
            ->post(route('penyerahan.selesai.store', $tiket), [])
            ->assertSessionHasErrors(['penerima_nama', 'penerima_hubungan']);

        $this->actingAs($loket)
            ->post(route('penyerahan.selesai.store', $tiket), [
                'penerima_nama' => 'Budi Santoso',
                'penerima_hubungan' => 'kuasa',
            ])
            ->assertSessionHasErrors('penerima_no_identitas');

        $this->actingAs($loket)
            ->post(route('penyerahan.selesai.store', $tiket), [
                'penerima_nama' => 'Budi Santoso',
                'penerima_hubungan' => 'kuasa',
                'penerima_no_identitas' => '3201010101010002',
                'catatan' => 'Diterima kuasa dengan surat kuasa.',
            ])
            ->assertRedirect(route('penyerahan.selesai'));

        $tiket->refresh();

        $this->assertSame('selesai', $tiket->status);
        $this->assertSame('Budi Santoso', $tiket->penerima_nama);
        $this->assertSame('kuasa', $tiket->penerima_hubungan);
        $this->assertSame('Kuasa', $tiket->penerima_hubungan_label);
        $this->assertSame($loket->id, $tiket->diserahkan_oleh_id);
        $this->assertNotNull($tiket->diserahkan_pada);
        $this->assertNotNull($tiket->tanggal_selesai);
        $this->assertNull($tiket->penanggung_jawab_id);
        $this->assertNull($tiket->diambil_pada);

        $this->assertDatabaseHas('riwayat_statuses', [
            'tiket_id' => $tiket->id,
            'stage_ke' => 'selesai',
            'changed_by' => $loket->id,
        ]);
    }

    public function test_petugas_lain_tidak_bisa_add_atau_menyelesaikan_tiket_yang_dipegang(): void
    {
        $pemegang = $this->user('loket_penyerahan');
        $lain = $this->user('loket_penyerahan');
        $tiket = $this->tiket('JP26102026103');

        $this->actingAs($pemegang)->post(route('penyerahan.add', $tiket));

        $this->actingAs($lain)
            ->post(route('penyerahan.add', $tiket))
            ->assertSessionHas('error');

        $this->actingAs($lain)
            ->post(route('penyerahan.selesai.store', $tiket), [
                'penerima_nama' => 'Orang Lain',
                'penerima_hubungan' => 'pemohon',
            ])
            ->assertSessionHas('error');

        $tiket->refresh();

        $this->assertSame('diproses', $tiket->status);
        $this->assertSame($pemegang->id, $tiket->penanggung_jawab_id);
    }

    public function test_daftar_selesai_menampilkan_penerima_dan_disaring_per_petugas(): void
    {
        $loket = $this->user('loket_penyerahan');
        $loketLain = $this->user('loket_penyerahan');

        $diserahkan = $this->tiket('JP26102026104');
        $milikLain = $this->tiket('JP26102026105');
        $masihDatabase = $this->tiket('JP26102026106');

        $this->actingAs($loket)->post(route('penyerahan.add', $diserahkan));
        $this->actingAs($loket)->post(route('penyerahan.selesai.store', $diserahkan), [
            'penerima_nama' => 'Sari Dewi',
            'penerima_hubungan' => 'pemohon',
        ]);

        $this->actingAs($loketLain)->post(route('penyerahan.add', $milikLain));
        $this->actingAs($loketLain)->post(route('penyerahan.selesai.store', $milikLain), [
            'penerima_nama' => 'Andi',
            'penerima_hubungan' => 'keluarga',
        ]);

        // Flash dari POST petugas lain memuat kode tiketnya; bersihkan agar
        // assertion di bawah menguji isi tabel, bukan pesan flash.
        session()->forget(['success', 'error', 'warning']);

        $this->actingAs($loket)
            ->get(route('penyerahan.selesai'))
            ->assertOk()
            ->assertSee($diserahkan->kode_tiket)
            ->assertSee('Sari Dewi')
            ->assertSee('Pemohon')
            ->assertDontSee($milikLain->kode_tiket)
            ->assertDontSee($masihDatabase->kode_tiket);
    }

    public function test_peringatan_hilang_bila_tiket_sudah_pernah_diproses_seksi(): void
    {
        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $loket = $this->user('loket_penyerahan');
        $tiket = $this->tiket('JP26102026107');

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi']);

        $this->actingAs($staf)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Berkas tuntas diverifikasi']);

        $this->actingAs($loket)
            ->post(route('penyerahan.add', $tiket))
            ->assertSessionHas('success')
            ->assertSessionMissing('warning');

        $this->assertSame($loket->id, $tiket->fresh()->penanggung_jawab_id);
    }
}
