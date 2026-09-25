<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\LembarKerjaWarkah;
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

        // Warkah sudah meng-Add tetapi BELUM menandai DISERAHKAN (tombol Kirim).
        $flow->claim($warkah, $tiket);

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

    public function test_alih_media_diblokir_sampai_verifikasi_dan_validator_selesai_dan_berkas_dikirim(): void
    {
        $alihBtel = $this->user('alih_media_btel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        $tiket->update(['diserahkan_ke_validator' => true]);

        $this->expectException(\RuntimeException::class);
        $flow->claim($alihBtel, $tiket);
    }

    public function test_alih_media_terbuka_setelah_kirim_berkas_dan_berujung_status_selesai_setelah_pengembalian(): void
    {
        $verifikator = $this->user('verifikator');
        $warkah = $this->user('warkah');
        $validatorBtel = $this->user('validator_btel');
        $validatorSuel = $this->user('validator_suel');
        $alihBtel = $this->user('alih_media_btel');
        $alihSuel = $this->user('alih_media_suel');
        $tiket = $this->tiket();
        $flow = app(TiketFlowService::class);

        // Warkah meng-Add dan KIRIM berkas (DIPINJAM) — tanpa perlu "selesai".
        $flow->claim($warkah, $tiket);
        $tiket->update(['diserahkan_ke_validator' => true]);
        LembarKerjaWarkah::updateOrCreate(
            ['tiket_id' => $tiket->id],
            ['status_pengembalian' => 'dipinjam', 'tanggal_diserahkan' => now()->toDateString()]
        );

        $flow->claim($verifikator, $tiket);
        $flow->done($verifikator, $tiket);

        $flow->claim($validatorBtel, $tiket);
        $flow->done($validatorBtel, $tiket);

        $flow->claim($validatorSuel, $tiket);
        $flow->done($validatorSuel, $tiket);

        // Alih Media terbuka meskipun Warkah belum selesai (berkas sudah KIRIM).
        $flow->claim($alihBtel, $tiket);
        $flow->done($alihBtel, $tiket);

        $flow->claim($alihSuel, $tiket);
        $flow->done($alihSuel, $tiket);

        $tiket->refresh();
        $this->assertNotSame('selesai', $tiket->status);
        $this->assertTrue($tiket->isAlihMediaSelesai());

        // Warkah mencatat pengembalian lalu Proses Selesai → SELESAI.
        LembarKerjaWarkah::where('tiket_id', $tiket->id)
            ->update(['status_pengembalian' => 'dikembalikan', 'tanggal_kembali' => now()->toDateString()]);
        $flow->done($warkah, $tiket, 'Berkas BT/SU dikembalikan ke Warkah.');

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
