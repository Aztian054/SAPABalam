<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use App\Models\WarkahPermintaan;
use App\Models\WarkahPermintaanRiwayat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Pinjaman warkah fisik (Blueprint V2, modul 06):
 * diminta → diterima → siap → dipinjam → dikembalikan, plus UBAH BATAS.
 */
class WarkahPinjamanTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    private User $staf;

    private Tiket $tiket;

    private WarkahPermintaan $warkah;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 09:00:00');

        $jenis = JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);

        $this->petugas = $this->user('petugas_warkah');
        $this->staf = $this->user('staf_seksi', ['seksi' => '1']);

        $this->tiket = Tiket::create([
            'kode_tiket' => 'JP26102026001',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Warkah',
            'no_hp_pemohon' => '081234567890',
            'jumlah_bidang' => 1,
            'status' => 'diproses',
            'penanggung_jawab_id' => $this->staf->id,
            'diambil_pada' => now(),
        ]);

        $this->warkah = WarkahPermintaan::create([
            'tiket_id' => $this->tiket->id,
            'jenis' => 'BT',
            'status' => 'diminta',
            'diminta_oleh_id' => $this->staf->id,
            'tanggal_permintaan' => now(),
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

    public function test_alur_lengkap_terima_siap_serahkan_kembalikan(): void
    {
        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.terima', $this->warkah))
            ->assertRedirect();

        $this->assertSame('diterima', $this->warkah->fresh()->status);

        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.siap', $this->warkah))
            ->assertRedirect();

        $this->assertSame('siap', $this->warkah->fresh()->status);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->staf->id,
            'tipe' => 'warkah.siap',
        ]);

        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.serahkan', $this->warkah), [
                'peminjam_id' => $this->staf->id,
                'batas_kembali' => now()->addDays(7)->toDateString(),
                'catatan' => 'Berkas diserahkan di ruang warkah.',
            ])
            ->assertRedirect();

        $this->warkah->refresh();
        $this->assertSame('dipinjam', $this->warkah->status);
        $this->assertSame($this->staf->id, $this->warkah->peminjam_id);
        $this->assertSame($this->staf->name, $this->warkah->nama_peminjam);
        $this->assertSame('1', $this->warkah->seksi);
        $this->assertSame(now()->addDays(7)->toDateString(), $this->warkah->batas_kembali->toDateString());

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->staf->id,
            'tipe' => 'warkah.dipinjam',
        ]);

        $this->actingAs($this->petugas)
            ->get(route('warkah-permintaan.pinjaman'))
            ->assertOk()
            ->assertSee($this->tiket->kode_tiket)
            ->assertSee('7 hari');

        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.kembalikan', $this->warkah), [
                'pengembali' => $this->staf->name,
                'kondisi' => 'baik',
            ])
            ->assertRedirect();

        $this->warkah->refresh();
        $this->assertSame('dikembalikan', $this->warkah->status);
        $this->assertSame('baik', $this->warkah->kondisi);
        $this->assertNotNull($this->warkah->tanggal_kembali);

        $this->actingAs($this->petugas)
            ->get(route('warkah-permintaan.dikembalikan'))
            ->assertOk()
            ->assertSee($this->tiket->kode_tiket);

        $this->assertSame(
            ['TERIMA', 'TANDAI SIAP', 'SERAHKAN', 'KEMBALIKAN'],
            WarkahPermintaanRiwayat::where('warkah_permintaan_id', $this->warkah->id)
                ->orderBy('id')
                ->pluck('aksi')
                ->all()
        );
    }

    public function test_ubah_batas_mencatat_riwayat_dan_notifikasi(): void
    {
        $this->warkah->update([
            'status' => 'dipinjam',
            'peminjam_id' => $this->staf->id,
            'nama_peminjam' => $this->staf->name,
            'seksi' => '1',
            'tanggal_pinjam' => now()->toDateString(),
            'batas_kembali' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.ubah-batas', $this->warkah), [
                'batas_kembali' => now()->addDays(10)->toDateString(),
                'alasan' => 'Permintaan lisan peminjam, pekerjaan belum selesai.',
            ])
            ->assertRedirect();

        $this->warkah->refresh();
        $this->assertSame(now()->addDays(10)->toDateString(), $this->warkah->batas_kembali->toDateString());
        $this->assertSame('dipinjam', $this->warkah->status);

        $riwayat = WarkahPermintaanRiwayat::where('warkah_permintaan_id', $this->warkah->id)->firstOrFail();
        $this->assertSame('UBAH BATAS', $riwayat->aksi);
        $this->assertSame(now()->addDays(3)->toDateString(), $riwayat->batas_lama->toDateString());
        $this->assertSame(now()->addDays(10)->toDateString(), $riwayat->batas_baru->toDateString());

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->staf->id,
            'tipe' => 'warkah.batas_diubah',
        ]);
    }

    public function test_peminjam_wajib_staf_seksi_aktif(): void
    {
        $this->warkah->update(['status' => 'siap']);

        $bukanStaf = $this->user('loket_pendaftaran');

        $this->actingAs($this->petugas)
            ->post(route('warkah-permintaan.serahkan', $this->warkah), [
                'peminjam_id' => $bukanStaf->id,
                'batas_kembali' => now()->addDays(7)->toDateString(),
            ])
            ->assertSessionHasErrors('peminjam_id');

        $this->assertSame('siap', $this->warkah->fresh()->status);
    }

    public function test_petugas_lain_tidak_boleh_mengakses_halaman_warkah(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($loket)
            ->get(route('warkah-permintaan.index'))
            ->assertForbidden();
    }

    public function test_halaman_permintaan_dan_detail_menampilkan_aksi_sesuai_status(): void
    {
        $this->actingAs($this->petugas)
            ->get(route('warkah-permintaan.index'))
            ->assertOk()
            ->assertSee($this->tiket->kode_tiket)
            ->assertSee('Terima');

        $this->actingAs($this->petugas)
            ->get(route('warkah-permintaan.show', $this->warkah))
            ->assertOk()
            ->assertSee('TERIMA PERMINTAAN')
            ->assertDontSee('SERAHKAN');
    }
}
