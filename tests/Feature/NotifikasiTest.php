<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Notifikasi;
use App\Models\NotifikasiPreferensi;
use App\Models\PendaftaranDraft;
use App\Models\Tiket;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Notifikasi in-app (Blueprint V2, modul 16).
 */
class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-26 09:00:00');
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

    private function notifikasi(User $user, array $atribut = []): Notifikasi
    {
        return Notifikasi::create([
            'user_id' => $user->id,
            'tipe' => 'tiket.dibuat',
            'judul' => 'Notifikasi uji',
            'pesan' => 'Pesan uji',
            'url' => route('dashboard'),
            'objek_tipe' => 'tiket',
            'objek_id' => 1,
            ...$atribut,
        ]);
    }

    public function test_service_membuat_satu_baris_per_penerima_aktif(): void
    {
        $aktif = $this->user('kasi_seksi', ['seksi' => '1']);
        $nonaktif = $this->user('kasi_seksi', ['seksi' => '1', 'is_active' => false]);

        $jumlah = app(NotifikasiService::class)->kirim(
            tipe: 'tiket.macet',
            penerimaIds: [$aktif->id, $nonaktif->id, null],
            judul: 'Tiket macet',
            pesan: 'Uji',
            url: route('dashboard'),
            objekTipe: 'tiket',
            objekId: 7,
        );

        $this->assertSame(1, $jumlah);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $aktif->id, 'tipe' => 'tiket.macet']);
        $this->assertDatabaseMissing('notifikasis', ['user_id' => $nonaktif->id]);
    }

    public function test_service_tidak_menduplikasi_peristiwa_sama_dalam_60_detik(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);
        $service = app(NotifikasiService::class);

        $service->kirim('warkah.siap', [$user->id], 'Warkah siap', 'Uji', null, 'warkah', 3);
        $service->kirim('warkah.siap', [$user->id], 'Warkah siap', 'Uji', null, 'warkah', 3);

        $this->assertSame(1, Notifikasi::count());

        Carbon::setTestNow(now()->addSeconds(61));
        $service->kirim('warkah.siap', [$user->id], 'Warkah siap', 'Uji', null, 'warkah', 3);

        $this->assertSame(2, Notifikasi::count());
    }

    public function test_halaman_notifikasi_hanya_menampilkan_milik_pengguna_dan_tab_belum_dibaca(): void
    {
        $user = $this->user('loket_pendaftaran');
        $lain = $this->user('loket_pendaftaran');

        $belum = $this->notifikasi($user, ['judul' => 'Belum dibaca saya']);
        $this->notifikasi($user, ['judul' => 'Sudah dibaca saya', 'dibaca_pada' => now()]);
        $this->notifikasi($lain, ['judul' => 'Milik pengguna lain']);

        $this->actingAs($user)
            ->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Belum dibaca saya')
            ->assertDontSee('Sudah dibaca saya')
            ->assertDontSee('Milik pengguna lain');

        $this->actingAs($user)
            ->get(route('notifikasi.index', ['tab' => 'semua']))
            ->assertOk()
            ->assertSee('Belum dibaca saya')
            ->assertSee('Sudah dibaca saya')
            ->assertDontSee('Milik pengguna lain');

        $this->assertNull($belum->fresh()->dibaca_pada);
    }

    public function test_membuka_notifikasi_menandai_dibaca_dan_mengarahkan_ke_tautan(): void
    {
        $user = $this->user('loket_pendaftaran');
        $notifikasi = $this->notifikasi($user, ['url' => route('pengaduan.index')]);

        $this->actingAs($user)
            ->post(route('notifikasi.baca', $notifikasi))
            ->assertRedirect(route('pengaduan.index'));

        $this->assertNotNull($notifikasi->fresh()->dibaca_pada);
    }

    public function test_notifikasi_milik_pengguna_lain_tidak_dapat_dibuka(): void
    {
        $user = $this->user('loket_pendaftaran');
        $notifikasi = $this->notifikasi($this->user('loket_pendaftaran'));

        $this->actingAs($user)
            ->post(route('notifikasi.baca', $notifikasi))
            ->assertForbidden();

        $this->assertNull($notifikasi->fresh()->dibaca_pada);
    }

    public function test_tandai_semua_dibaca_dan_endpoint_jumlah(): void
    {
        $user = $this->user('kasi_seksi', ['seksi' => '2']);
        $this->notifikasi($user);
        $this->notifikasi($user, ['objek_id' => 2]);

        $this->actingAs($user)
            ->getJson(route('notifikasi.jumlah'))
            ->assertOk()
            ->assertJsonPath('belum_dibaca', 2);

        $this->actingAs($user)->post(route('notifikasi.baca-semua'))->assertRedirect();

        $this->assertSame(0, Notifikasi::whereNull('dibaca_pada')->count());

        $this->actingAs($user)
            ->getJson(route('notifikasi.jumlah'))
            ->assertOk()
            ->assertJsonPath('belum_dibaca', 0);
    }

    public function test_lonceng_menampilkan_jumlah_belum_dibaca_langsung_dari_server(): void
    {
        $user = $this->user('kasi_seksi', ['seksi' => '2']);
        $this->notifikasi($user);
        $this->notifikasi($user, ['objek_id' => 2]);
        $this->notifikasi($user, ['objek_id' => 3, 'dibaca_pada' => now()]);

        // Angka badge dirakit server agar benar sebelum polling JS pertama,
        // bukan bergantung pada satu-satunya sumber dari JavaScript.
        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="notifBadge"[^>]*>\s*2\s*<\/span>/',
            $html,
            'Badge notifikasi tidak menampilkan jumlah belum dibaca dari server.',
        );

        $this->assertDoesNotMatchRegularExpression('/id="notifBadge"[^>]*\bd-none\b/', $html);
    }

    public function test_ploting_selesai_mengirim_notifikasi_ke_pembuat_draf(): void
    {
        $loket = $this->user('loket_pendaftaran');
        $ploting = $this->user('loket_ploting');

        $draf = PendaftaranDraft::create([
            'user_id' => $loket->id,
            'kode_tiket' => 'JP26102026001',
            'tahap' => 2,
            'ploting_status' => 'belum',
            'dikirim_pada' => now(),
        ]);

        $this->actingAs($ploting)
            ->post(route('ploting.selesai', $draf), ['catatan' => 'Bidang sesuai peta'])
            ->assertRedirect(route('ploting.index', ['tab' => 'selesai']));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $loket->id,
            'tipe' => 'tiket.ploting_selesai',
            'objek_id' => $draf->id,
        ]);
    }

    public function test_halaman_preferensi_menampilkan_semua_tipe_dan_default_aktif(): void
    {
        $user = $this->user('loket_pendaftaran');

        $response = $this->actingAs($user)
            ->get(route('notifikasi.preferensi'))
            ->assertOk()
            ->assertSee('Preferensi Notifikasi');

        foreach (NotifikasiService::semuaTipe() as $tipe) {
            $response->assertSee($tipe);
        }

        $this->assertTrue($response->viewData('pilihan')->isEmpty());
        $this->assertSame([], NotifikasiPreferensi::where('user_id', $user->id)->pluck('tipe')->all());
    }

    public function test_mematikan_tipe_menyembunyikan_notifikasi_tipe_itu_saja(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);
        $service = app(NotifikasiService::class);

        $this->actingAs($user)
            ->post(route('notifikasi.preferensi.simpan'), [
                'tipe' => ['tiket.dibuat', 'warkah.siap'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifikasi_preferensis', [
            'user_id' => $user->id,
            'tipe' => 'tiket.macet',
            'aktif' => false,
        ]);

        $this->assertSame(0, $service->kirim('tiket.macet', [$user->id], 'Macet', 'Uji', null, 'tiket', 91));

        $this->assertSame(1, $service->kirim('tiket.dibuat', [$user->id], 'Baru', 'Uji', null, 'tiket', 92));

        $this->assertSame(1, Notifikasi::count());
    }

    public function test_preferensi_tidak_mempengaruhi_pengguna_lain(): void
    {
        $pemilih = $this->user('staf_seksi', ['seksi' => '1']);
        $lain = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($pemilih)
            ->post(route('notifikasi.preferensi.simpan'), ['tipe' => ['tiket.dibuat']])
            ->assertRedirect();

        app(NotifikasiService::class)->kirim(
            tipe: 'tiket.macet',
            penerimaIds: [$pemilih->id, $lain->id],
            judul: 'Macet',
            pesan: 'Uji',
            objekTipe: 'tiket',
            objekId: 93,
        );

        $this->assertDatabaseMissing('notifikasis', ['user_id' => $pemilih->id, 'tipe' => 'tiket.macet']);
        $this->assertDatabaseHas('notifikasis', ['user_id' => $lain->id, 'tipe' => 'tiket.macet']);
    }

    public function test_tipe_wajib_tetap_dikirim_walau_preferensi_dimatikan(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);

        NotifikasiPreferensi::create([
            'user_id' => $user->id,
            'tipe' => 'tiket.ditolak',
            'aktif' => false,
        ]);

        app(NotifikasiService::class)->kirim('tiket.ditolak', [$user->id], 'Ditolak', 'Uji', null, 'tiket', 94);

        $this->assertDatabaseHas('notifikasis', ['user_id' => $user->id, 'tipe' => 'tiket.ditolak']);
    }

    public function test_tipe_preferensi_tidak_dikenal_ditolak(): void
    {
        $user = $this->user('loket_pendaftaran');

        $this->actingAs($user)
            ->post(route('notifikasi.preferensi.simpan'), ['tipe' => ['tiket.tidak_ada']])
            ->assertSessionHasErrors('tipe.0');

        $this->assertSame(0, NotifikasiPreferensi::count());
    }

    public function test_staf_add_tiket_mengirim_notifikasi_ke_kasi_seksi(): void
    {
        $jenis = JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);

        $staf = $this->user('staf_seksi', ['seksi' => '1']);
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);
        $kasiLain = $this->user('kasi_seksi', ['seksi' => '2']);

        $tiket = Tiket::create([
            'kode_tiket' => 'K/1/261026/1',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Notifikasi',
            'no_hp_pemohon' => '081234567890',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);

        $this->actingAs($staf)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertRedirect(route('seksi.tiket-saya'));

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasi->id,
            'tipe' => 'tiket.diambil',
            'objek_id' => $tiket->id,
        ]);

        $this->assertDatabaseMissing('notifikasis', ['user_id' => $kasiLain->id]);
    }
}
