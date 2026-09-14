<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use App\Services\TiketFlowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifikasi konfigurasi ALUR PARALEL (dokumen Alur_00 + PRD 2.2):
 *   - Verifikator ║ Warkah tidak saling menunggu;
 *   - Validator BT/SU dibuka oleh flag diserahkan_ke_validator (tanpa menunggu Verifikator);
 *   - Alih Media hanya terbuka untuk tiket dengan SEMUA tahap selesai (gate AND penuh).
 */
class AlurParalelFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'name' => $role,
            'username' => $role.'_'.str()->random(6),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function tiket(): Tiket
    {
        $jenis = JenisPermohonan::create([
            'kode' => 'TEST',
            'nama' => 'Permohonan Uji Alur',
            'kategori' => 'umum',
            'batas_hari_sla' => 7,
            'is_active' => true,
        ]);

        $tiket = Tiket::create([
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

        BidangTanah::create([
            'tiket_id' => $tiket->id,
            'nib' => '001',
            'urutan' => 1,
        ]);

        return $tiket;
    }

    public function test_warkah_dapat_meng_add_tiket_sementara_verifikator_masih_bekerja(): void
    {
        $verifikator = $this->user('verifikator');
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        // Verifikator meng-Add lebih dulu — tiket belum selesai diverifikasi.
        $flow->claim($verifikator, $tiket);

        // Warkah TETAP bisa meng-Add tiket yang sama (paralel, tanpa menunggu Verifikator).
        $flow->claim($warkah, $tiket);

        $this->assertNotNull($tiket->penugasanAktif('verifikasi'));
        $this->assertNotNull($tiket->penugasanAktif('warkah'));
    }

    public function test_validator_diblokir_sebelum_diserahkan_ke_validator_oleh_warkah(): void
    {
        $warkah = $this->user('warkah');
        $validator = $this->user('validator_btel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        // Warkah sudah selesai mengerjakan tetapi BELUM menandai DISERAHKAN.
        $flow->claim($warkah, $tiket);
        $flow->done($warkah, $tiket, 'data warkah lengkap');

        $this->expectException(\RuntimeException::class);
        $flow->claim($validator, $tiket);
    }

    public function test_validator_bt_dan_su_paralel_setelah_flag_diserahkan_diaktifkan(): void
    {
        $validatorBtel = $this->user('validator_btel');
        $validatorSuel = $this->user('validator_suel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        // Simulasi Warkah menandai status sertipikat DISERAHKAN.
        $tiket->update(['diserahkan_ke_validator' => true]);

        $flow->claim($validatorBtel, $tiket);
        $flow->claim($validatorSuel, $tiket);

        $this->assertNotNull($tiket->penugasanAktif('validasi_btel'));
        $this->assertNotNull($tiket->penugasanAktif('validasi_suel'));
    }

    public function test_alih_media_diblokir_sampai_semua_tahap_selesai(): void
    {
        $alihBtel = $this->user('alih_media_btel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        $tiket->update(['diserahkan_ke_validator' => true]);

        $this->expectException(\RuntimeException::class);
        $flow->claim($alihBtel, $tiket);
    }

    public function test_alih_media_terbuka_setelah_semua_tahap_selesai_dan_berujung_status_selesai(): void
    {
        $verifikator = $this->user('verifikator');
        $warkah = $this->user('warkah');
        $validatorBtel = $this->user('validator_btel');
        $validatorSuel = $this->user('validator_suel');
        $alihBtel = $this->user('alih_media_btel');
        $alihSuel = $this->user('alih_media_suel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        $tiket->update(['diserahkan_ke_validator' => true]);

        $flow->claim($verifikator, $tiket);
        $flow->done($verifikator, $tiket);

        $flow->claim($warkah, $tiket);
        $flow->done($warkah, $tiket);

        $flow->claim($validatorBtel, $tiket);
        $flow->done($validatorBtel, $tiket);

        $flow->claim($validatorSuel, $tiket);
        $flow->done($validatorSuel, $tiket);

        $this->assertTrue($tiket->isSemuaTahapSelesai());

        $flow->claim($alihBtel, $tiket);
        $flow->done($alihBtel, $tiket);

        $flow->claim($alihSuel, $tiket);
        $flow->done($alihSuel, $tiket);

        $tiket->refresh();

        $this->assertSame('selesai', $tiket->status);
        $this->assertTrue($tiket->isAlihMediaSelesai());
    }

    public function test_nip_dan_no_hp_tersimpan_saat_admin_membuat_akun(): void
    {
        $this->actingAs($this->user('admin'));

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Petugas Baru',
            'username' => 'petugas1',
            'email' => 'petugas1@loket.test',
            'password' => 'rahasia123',
            'role' => 'verifikator',
            'nip' => '198501012025011001',
            'no_hp' => '081234567890',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'username' => 'petugas1',
            'nip' => '198501012025011001',
            'no_hp' => '081234567890',
        ]);
    }
}
