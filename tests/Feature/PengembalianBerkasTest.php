<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\LembarKerjaWarkah;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur pengembalian berkas BT/SU (pinjaman hardcopy) — revisi stage Warkah:
 *   - KIRIM berkas → status_pengembalian = dipinjam + buka gerbang Validator.
 *   - Warkah TIDAK boleh Proses Selesai sebelum pengembalian tercatat.
 *   - Catat Pengembalian hanya sah bila status = dipinjam.
 *   - Setelah dicatat + seluruh Alih Media beres → tiket SELESAI.
 */
class PengembalianBerkasTest extends TestCase
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
            'kode' => 'PG', 'nama' => 'Permohonan Uji Pengembalian', 'kategori' => 'umum', 'is_active' => true,
        ]);

        $tiket = Tiket::create([
            'kode_tiket' => 'PG/'.now()->timestamp,
            'nomor_antrian' => 'A-001',
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Pengembalian',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'diterima',
        ]);

        BidangTanah::create(['tiket_id' => $tiket->id, 'nib' => '001', 'urutan' => 1]);

        return $tiket;
    }

    private function addWarkahKeAntrian(User $warkah, Tiket $tiket): void
    {
        $this->actingAs($warkah);
        $this->post(route('warkah.add', $tiket->id))->assertSessionHasNoErrors();
    }

    /** Serahkan Berkas Warkah via HTTP route dengan payload serah terima lengkap. */
    private function kirimBerkas(User $warkah, User $validator, Tiket $tiket): void
    {
        $this->actingAs($warkah);
        $this->post(route('warkah.kirim', $tiket->id), [
            'penerima_validator_id' => $validator->id,
            'waktu_serah' => now()->format('Y-m-d\TH:i'),
            'status_berkas_bt' => 'lengkap',
            'status_berkas_su' => 'lengkap',
            'catatan_kondisi_berkas' => 'Berkas BT/SU diserahkan lengkap.',
        ])->assertSessionHasNoErrors();
        $tiket->refresh();
    }

    /** Konfirmasi Pengembalian Berkas via HTTP route dengan payload lengkap. */
    private function konfirmasiPengembalian(User $warkah, Tiket $tiket): void
    {
        $this->actingAs($warkah);
        $this->post(route('warkah.pengembalian', $tiket->id), [
            'petugas_pengembali' => 'Petugas Warkah Test',
            'waktu_kembali' => now()->format('Y-m-d\TH:i'),
            'kondisi_berkas_kembali' => 'lengkap',
            'catatan_pengembalian' => 'Berkas BT/SU diterima kembali lengkap.',
            'jumlah_berkas_dikembalikan' => 2,
        ])->assertSessionHasNoErrors();
        $tiket->refresh();
    }

    public function test_warkah_tidak_bisa_proses_selesai_sebelum_berkas_dikembalikan(): void
    {
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);

        // Belum diserahkan, apalagi dikembalikan — Proses Selesai diblokir.
        $this->actingAs($warkah);
        $this->post(route('warkah.selesai', $tiket->id), ['catatan' => 'coba selesaikan'])
            ->assertSessionHas('error', 'Berkas BT/SU belum dikembalikan ke Warkah. Konfirmasi pengembalian lebih dulu melalui tombol "Konfirmasi Pengembalian Berkas".');
        $this->assertSame('warkah', $tiket->fresh()->status);

        // Kirim berkas (menjadi DIPINJAM) — tetap belum boleh selesai.
        $this->kirimBerkas($warkah, $this->user('validator_btel'), $tiket);
        $this->post(route('warkah.selesai', $tiket->id), ['catatan' => 'coba selesaikan'])
            ->assertSessionHas('error');

        $this->assertSame('warkah', $tiket->fresh()->status);
        $this->assertSame('dipinjam', LembarKerjaWarkah::where('tiket_id', $tiket->id)->first()->status_pengembalian);
    }

    public function test_kirim_berkas_membuka_validator_dan_menandai_dipinjam(): void
    {
        $warkah = $this->user('warkah');
        $validator = $this->user('validator_btel');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);

        // Validator masih diblokir sebelum KIRIM.
        $this->actingAs($validator);
        $this->post(route('validator_btel.add', $tiket->id))
            ->assertSessionHas('error');

        // Warkah KIRIM berkas — serah terima lengkap ke Validator BT.
        $this->kirimBerkas($warkah, $validator, $tiket);

        $tiket->refresh();
        $this->assertTrue($tiket->isDiserahkanKeValidator());

        $lembar = LembarKerjaWarkah::where('tiket_id', $tiket->id)->first();
        $this->assertSame('dipinjam', $lembar->status_pengembalian);
        $this->assertSame('diserahkan', $lembar->status_sertipikat);
        $this->assertNotNull($lembar->tanggal_diserahkan);
        $this->assertNotNull($lembar->waktu_serah);
        $this->assertSame($validator->id, $lembar->penerima_validator_id);
        $this->assertSame($validator->name.' ('.$validator->role_label.')', $lembar->nama_penerima_validator);
        $this->assertSame('lengkap', $lembar->status_berkas_bt);
        $this->assertSame('lengkap', $lembar->status_berkas_su);
        $this->assertSame('Berkas BT/SU diserahkan lengkap.', $lembar->catatan_kondisi_berkas);

        // Validator kini dapat meng-Add secara paralel.
        $this->actingAs($validator);
        $this->post(route('validator_btel.add', $tiket->id))->assertSessionHasNoErrors();
    }

    public function test_catat_pengembalian_ditolak_bila_berkas_belum_diserahkan(): void
    {
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);

        $this->actingAs($warkah);
        $this->post(route('warkah.pengembalian', $tiket->id), ['tanggal_kembali' => now()->toDateString()])
            ->assertSessionHas('error', 'Berkas BT/SU belum diserahkan ke Validator; belum ada pinjaman yang perlu dikembalikan.');
    }

    public function test_pengembalian_dicatat_dan_tiket_selesai_setelah_semua_alih_media_beres(): void
    {
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);
        $this->kirimBerkas($warkah, $this->user('validator_btel'), $tiket);

        // Simulasi alih media BT & SU selesai.
        $tiket->penugasans()->create([
            'user_id' => $this->user('alih_media_btel')->id,
            'stage' => 'alih_media_btel', 'status' => 'selesai', 'tanggal_add' => now(), 'tanggal_selesai' => now(),
        ]);
        $tiket->penugasans()->create([
            'user_id' => $this->user('alih_media_suel')->id,
            'stage' => 'alih_media_suel', 'status' => 'selesai', 'tanggal_add' => now(), 'tanggal_selesai' => now(),
        ]);

        // Konfirmasi pengembalian (waktu & kondisi diisi manual oleh Warkah).
        $this->konfirmasiPengembalian($warkah, $tiket);

        $lembar = LembarKerjaWarkah::where('tiket_id', $tiket->id)->first();
        $this->assertSame('dikembalikan', $lembar->status_pengembalian);
        $this->assertSame('lengkap', $lembar->kondisi_berkas_kembali);
        $this->assertSame('Petugas Warkah Test', $lembar->petugas_pengembali);
        $this->assertNotNull($lembar->waktu_kembali);
        $this->assertSame(2, $lembar->jumlah_berkas_dikembalikan);

        // Proses Selesai Warkah — seluruh tiket SELESAI.
        $this->post(route('warkah.selesai', $tiket->id), ['catatan' => 'Berkas BT/SU sudah kembali ke Warkah.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('selesai', $tiket->fresh()->status);
        $this->assertNotNull($tiket->fresh()->tanggal_selesai);
    }

    public function test_konfirmasi_pengembalian_diblokir_sebelum_alih_media_selesai(): void
    {
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);
        $this->kirimBerkas($warkah, $this->user('validator_btel'), $tiket);

        // Alih Media BT/SU belum selesai — Konfirmasi Pengembalian diblokir.
        $this->actingAs($warkah);
        $this->post(route('warkah.pengembalian', $tiket->id), [
            'petugas_pengembali' => 'Petugas Warkah Test',
            'waktu_kembali' => now()->format('Y-m-d\TH:i'),
            'kondisi_berkas_kembali' => 'lengkap',
        ])->assertSessionHas('error', 'Konfirmasi Pengembalian Berkas hanya dapat dilakukan setelah Alih Media BT & SU selesai.');

        $this->assertSame('dipinjam', LembarKerjaWarkah::where('tiket_id', $tiket->id)->first()->status_pengembalian);
    }

    public function test_serah_berkas_wajib_input_lengkap(): void
    {
        $warkah = $this->user('warkah');
        $tiket = $this->tiket();

        $this->addWarkahKeAntrian($warkah, $tiket);

        $this->actingAs($warkah);
        $this->post(route('warkah.kirim', $tiket->id), [])
            ->assertSessionHasErrors(['penerima_validator_id', 'waktu_serah', 'status_berkas_bt', 'status_berkas_su']);

        $this->assertFalse($tiket->fresh()->isDiserahkanKeValidator());
    }
}
