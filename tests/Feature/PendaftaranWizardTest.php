<?php

namespace Tests\Feature;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\PendaftaranDraft;
use App\Models\Tiket;
use App\Models\User;
use App\Models\WarkahPermintaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Wizard pendaftaran 4 tahap (Blueprint V2, modul 02):
 * Tahap 1 draft + nomor JP, Tahap 2 ploting, Tahap 3 permohonan,
 * Tahap 4 warkah → tiket `di_database`.
 */
class PendaftaranWizardTest extends TestCase
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
            'name' => $role,
            'username' => $role.'_'.str()->random(6),
            'role' => $role,
            'is_active' => true,
            ...$atribut,
        ]);
    }

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

    private function permohonan(): array
    {
        return [
            'jenis_permohonan_id' => JenisPermohonan::firstOrFail()->id,
            'no_hak_sekarang' => 'HM.123/Kedaton',
            'no_hak_sebelumnya' => null,
            'kelurahan_desa' => 'Kedaton',
            'kecamatan' => 'Kedaton',
            'keterangan' => 'Uji wizard',
            'bidang' => [
                [
                    'nib' => '0123456789',
                    'no_sertifikat_lama' => 'HM-123',
                    'jenis_hak' => 'HM',
                    'nama_pemegang_hak' => 'Budi Santoso',
                    'desa_kelurahan' => 'Kedaton',
                    'kecamatan' => 'Kedaton',
                ],
            ],
        ];
    }

    public function test_wizard_empat_tahap_menghasilkan_tiket_di_database_dan_warkah(): void
    {
        $petugas = $this->user('loket_pendaftaran');
        $ploting = $this->user('loket_ploting');

        $this->actingAs($petugas)->get(route('pendaftaran.create'))->assertOk();

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap1.store'), $this->biodata())
            ->assertRedirect();

        $draf = PendaftaranDraft::firstOrFail();

        $this->assertMatchesRegularExpression('/^JP\d{8}\d{3}$/', $draf->kode_tiket);
        $this->assertSame('belum', $draf->ploting_status);
        $this->assertSame('Budi Santoso', $draf->biodata()['nama_pemohon']);

        // Draf yang belum dikirim ke ploting belum masuk antrian Loket Ploting.
        $this->actingAs($ploting)
            ->get(route('ploting.index'))
            ->assertOk()
            ->assertSee('Tidak ada berkas yang menunggu ploting.');

        // Tahap 3 masih terkunci selama ploting belum selesai.
        $this->actingAs($petugas)
            ->get(route('pendaftaran.tahap3', $draf))
            ->assertRedirect(route('pendaftaran.tahap2', $draf));

        // Tahap 2 "belum" → cetak antrian.
        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap2.store', $draf), ['ploting' => 'belum'])
            ->assertRedirect(route('pendaftaran.antrian', $draf));

        $this->actingAs($petugas)->get(route('pendaftaran.tahap2', $draf))->assertOk();

        $this->assertNotNull($draf->fresh()->dikirim_pada);
        $this->actingAs($petugas)->get(route('pendaftaran.antrian', $draf))->assertOk();

        $this->actingAs($ploting)
            ->get(route('ploting.index'))
            ->assertOk()
            ->assertSee($draf->kode_tiket);

        // Loket Ploting menyatakan sudah + catatan.
        $this->actingAs($ploting)
            ->post(route('ploting.selesai', $draf), ['catatan' => 'Bidang sesuai peta'])
            ->assertRedirect(route('ploting.index', ['tab' => 'selesai']));

        $draf->refresh();
        $this->assertSame('sudah', $draf->ploting_status);
        $this->assertSame($ploting->id, $draf->ploting_oleh_id);
        $this->assertNotNull($draf->ploting_selesai_at);

        $this->actingAs($ploting)
            ->get(route('ploting.index', ['tab' => 'selesai']))
            ->assertOk()
            ->assertSee('Bidang sesuai peta');

        // Tahap 3 tersimpan.
        $this->actingAs($petugas)->get(route('pendaftaran.tahap3', $draf))->assertOk();

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap3.store', $draf), $this->permohonan())
            ->assertRedirect(route('pendaftaran.tahap4', $draf));

        $this->assertSame('Kedaton', $draf->fresh()->permohonan()['kelurahan_desa']);
        $this->actingAs($petugas)->get(route('pendaftaran.tahap4', $draf))->assertOk();

        // Tahap 4 → tiket di_database + dua permintaan warkah.
        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap4.store', $draf), ['warkah' => 'keduanya'])
            ->assertRedirect(route('pendaftaran.index'));

        $this->assertDatabaseMissing('pendaftaran_drafts', ['id' => $draf->id]);

        $tiket = Tiket::where('nama_pemohon', 'Budi Santoso')->firstOrFail();

        $this->assertSame('di_database', $tiket->status);
        $this->assertSame('sudah', $tiket->ploting_status);
        $this->assertSame($ploting->id, $tiket->ploting_oleh_id);
        $this->assertSame($petugas->id, $tiket->created_by);
        $this->assertSame(1, $tiket->bidangTanahs()->count());
        $this->assertSame('HM.123/Kedaton', $tiket->no_hak_sekarang);

        $this->assertEqualsCanonicalizing(
            ['BT', 'SU'],
            WarkahPermintaan::where('tiket_id', $tiket->id)->pluck('jenis')->all()
        );
        $this->assertSame('diminta', WarkahPermintaan::where('tiket_id', $tiket->id)->first()->status);

        $this->actingAs($petugas)
            ->get(route('pendaftaran.index'))
            ->assertOk()
            ->assertSee($tiket->kode_tiket);
    }

    public function test_tahap_4_tanpa_warkah_tidak_membuat_permintaan(): void
    {
        $petugas = $this->user('loket_pendaftaran');

        $draf = PendaftaranDraft::create([
            'user_id' => $petugas->id,
            'kode_tiket' => 'JP26102026999',
            'tahap' => 3,
            'ploting_status' => 'sudah',
            'ploting_selesai_at' => now(),
            'data' => [
                'biodata' => $this->biodata(),
                'permohonan' => [
                    ...$this->permohonan(),
                    'jumlah_bidang' => 1,
                    'bidang' => [$this->permohonan()['bidang'][0]],
                ],
            ],
        ]);

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap4.store', $draf), ['warkah' => 'none'])
            ->assertRedirect(route('pendaftaran.index'));

        $tiket = Tiket::where('kode_tiket', 'JP26102026999')->firstOrFail();

        $this->assertSame('di_database', $tiket->status);
        $this->assertSame(0, $tiket->warkahPermintaans()->count());
        $this->assertSame(1, BidangTanah::where('tiket_id', $tiket->id)->count());
    }

    public function test_loket_pendaftaran_tidak_boleh_memproses_ploting(): void
    {
        $petugas = $this->user('loket_pendaftaran');

        $draf = PendaftaranDraft::create([
            'user_id' => $petugas->id,
            'kode_tiket' => 'JP26102026050',
            'tahap' => 2,
            'ploting_status' => 'belum',
            'dikirim_pada' => now(),
        ]);

        $this->actingAs($petugas)
            ->post(route('ploting.selesai', $draf), ['catatan' => 'Coba sendiri'])
            ->assertForbidden();

        $this->assertSame('belum', $draf->fresh()->ploting_status);
    }

    public function test_nomor_tiket_manual_dari_form_diabaikan(): void
    {
        $petugas = $this->user('loket_pendaftaran');

        Tiket::create([
            'kode_tiket' => 'JP26102026100',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => JenisPermohonan::firstOrFail()->id,
            'nama_pemohon' => 'Pemohon Lama',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);

        $this->actingAs($petugas)
            ->post(route('pendaftaran.tahap1.store'), $this->biodata(['kode_tiket' => 'JP26102026100']))
            ->assertRedirect();

        $draf = PendaftaranDraft::sole();

        $this->assertNotSame('JP26102026100', $draf->kode_tiket);
        $this->assertMatchesRegularExpression('/^JP26102026\d{3}$/', $draf->kode_tiket);
    }
}
