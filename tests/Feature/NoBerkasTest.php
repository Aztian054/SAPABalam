<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\PendaftaranDraft;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * No. Berkas manual (V2): diisi di Tahap 1 wizard, disalin ke tiket pada
 * Tahap 4, format bebas, tidak unik, dan dapat diubah pembuat/Admin.
 */
class NoBerkasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);
    }

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create([
            'name' => $role,
            'username' => $role.'_'.str()->random(6),
            'role' => $role,
            'is_active' => true,
            ...$atribut,
        ]);
    }

    private function tiket(array $atribut = []): Tiket
    {
        return Tiket::create([
            'kode_tiket' => 'NB/'.str()->random(8),
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => JenisPermohonan::firstOrFail()->id,
            'nama_pemohon' => 'Pemohon No Berkas',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
            ...$atribut,
        ]);
    }

    /** @return array<string, mixed> */
    private function biodata(array $ganti = []): array
    {
        return [
            'nama_pemohon' => 'Budi Santoso',
            'nik_pemohon' => '1871010101010001',
            'no_hp_pemohon' => '081234567890',
            'kelurahan_desa' => 'Kedaton',
            'kecamatan' => 'Kedaton',
            ...$ganti,
        ];
    }

    /** @return array<string, mixed> */
    private function permohonan(): array
    {
        return [
            'jenis_permohonan_id' => JenisPermohonan::firstOrFail()->id,
            'no_hak_sekarang' => 'HM.123/Kedaton',
            'kelurahan_desa' => 'Kedaton',
            'kecamatan' => 'Kedaton',
            'bidang' => [
                [
                    'nib' => '0123456789',
                    'jenis_hak' => 'HM',
                ],
            ],
        ];
    }

    public function test_no_berkas_dari_tahap_1_tersalin_ke_tiket_saat_tahap_4(): void
    {
        $petugas = $this->user('loket_pendaftaran');
        $noBerkas = 'BPN-2026/001.02 - A';

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap1.store'), $this->biodata(['no_berkas' => $noBerkas]))
            ->assertRedirect();

        $draf = PendaftaranDraft::firstOrFail();
        $this->assertSame($noBerkas, $draf->no_berkas);

        $draf->update(['ploting_status' => 'sudah', 'ploting_selesai_at' => now()]);

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap3.store', $draf), $this->permohonan())
            ->assertRedirect(route('pendaftaran.tahap4', $draf));

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap4.store', $draf), ['warkah' => 'none'])
            ->assertRedirect(route('pendaftaran.index'));

        $tiket = Tiket::where('nama_pemohon', 'Budi Santoso')->firstOrFail();
        $this->assertSame($noBerkas, $tiket->no_berkas);
    }

    public function test_no_berkas_kosong_disimpan_null(): void
    {
        $petugas = $this->user('loket_pendaftaran');

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap1.store'), $this->biodata(['no_berkas' => '   ']))
            ->assertRedirect();

        $this->assertNull(PendaftaranDraft::firstOrFail()->no_berkas);
    }

    public function test_no_berkas_format_bebas_dan_boleh_duplikat(): void
    {
        $petugas = $this->user('loket_pendaftaran');
        $sama = 'Nomor Resmi BPN #2026/ABC';

        $tiketA = $this->tiket(['created_by' => $petugas->id]);
        $tiketB = $this->tiket(['created_by' => $petugas->id]);

        foreach ([$tiketA, $tiketB] as $tiket) {
            $this->actingAs($petugas)
                ->put(route('pendaftaran.no-berkas.update', $tiket), ['no_berkas' => $sama])
                ->assertRedirect()
                ->assertSessionHas('success');
        }

        $this->assertSame($sama, $tiketA->fresh()->no_berkas);
        $this->assertSame($sama, $tiketB->fresh()->no_berkas);
    }

    public function test_pembuat_dapat_mengubah_no_berkas_tetapi_admin_tidak(): void
    {
        $petugas = $this->user('loket_pendaftaran');
        $admin = $this->user('admin');
        $tiket = $this->tiket(['created_by' => $petugas->id]);

        $this->actingAs($petugas)
            ->put(route('pendaftaran.no-berkas.update', $tiket), ['no_berkas' => 'DARI-PEMBUAT'])
            ->assertRedirect();

        $this->assertSame('DARI-PEMBUAT', $tiket->fresh()->no_berkas);

        $this->actingAs($admin)
            ->put(route('pendaftaran.no-berkas.update', $tiket), ['no_berkas' => 'DARI-ADMIN'])
            ->assertForbidden();

        $this->assertSame('DARI-PEMBUAT', $tiket->fresh()->no_berkas);
    }

    public function test_pengguna_lain_tidak_dapat_mengubah_no_berkas(): void
    {
        $pembuat = $this->user('loket_pendaftaran');
        $lain = $this->user('loket_pendaftaran');
        $tiket = $this->tiket(['created_by' => $pembuat->id, 'no_berkas' => 'ASLI']);

        $this->actingAs($lain)
            ->put(route('pendaftaran.no-berkas.update', $tiket), ['no_berkas' => 'DIUBAH'])
            ->assertForbidden();

        $this->assertSame('ASLI', $tiket->fresh()->no_berkas);
    }

    public function test_daftar_pendaftaran_dapat_dicari_dengan_no_berkas(): void
    {
        $petugas = $this->user('loket_pendaftaran');
        $this->tiket([
            'kode_tiket' => 'CARI/001',
            'nama_pemohon' => 'Pemohon Dicari',
            'no_berkas' => 'KHUSUS-999',
            'created_by' => $petugas->id,
            'petugas_loket_id' => $petugas->id,
        ]);

        $this->actingAs($petugas)
            ->get(route('pendaftaran.index', ['q' => 'KHUSUS-999']))
            ->assertOk()
            ->assertSee('Pemohon Dicari');
    }
}
