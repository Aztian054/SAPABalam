<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Notifikasi;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Staf mendapat notifikasi saat berkas berpindah akun dan selesai di penyerahan. */
class NotifikasiSerahTerimaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-09 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => $role.' '.str()->random(5),
            'username' => $role.'_'.str()->random(8),
            'email' => $role.'_'.str()->random(8).'@example.test',
            'role' => $role,
            'is_active' => true,
        ], $atribut));
    }

    private function tiket(): Tiket
    {
        $jp = JenisPermohonan::firstOrCreate(
            ['kode' => 'JP01'],
            ['nama' => 'Peralihan Hak', 'kategori' => 'umum', 'is_active' => true],
        );

        return Tiket::create([
            'kode_tiket' => 'K/001/261109/1',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->toDateString(),
            'jenis_permohonan_id' => $jp->id,
            'nama_pemohon' => 'Pemohon Serah Terima',
            'no_hp_pemohon' => '081200000000',
            'jumlah_bidang' => 1,
            'status' => 'di_database',
        ]);
    }

    public function test_staf_terkait_mendapat_notifikasi_saat_berkas_pindah_dan_selesai(): void
    {
        $stafSatu = $this->user('staf_seksi', ['seksi' => '1']);
        $stafDua = $this->user('staf_seksi', ['seksi' => '2']);
        $petugas = $this->user('loket_penyerahan');
        $tiket = $this->tiket();

        // Staf 1 mengambil lalu menyelesaikan berkas -> kembali ke Database.
        $this->actingAs($stafSatu)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi'])
            ->assertRedirect();

        $this->actingAs($stafSatu)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Berkas sudah diverifikasi.'])
            ->assertRedirect();

        // Staf 2 mengambil alih: Staf 1 harus diberi tahu.
        Carbon::setTestNow('2026-11-09 09:05:00');

        $this->actingAs($stafDua)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'validator_bt'])
            ->assertRedirect();

        $this->assertTrue(
            Notifikasi::where('user_id', $stafSatu->id)
                ->where('tipe', 'tiket.diteruskan')
                ->where('objek_id', $tiket->id)
                ->exists(),
            'Staf 1 seharusnya menerima notifikasi saat berkas pindah ke Staf 2.'
        );

        $this->actingAs($stafDua)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Validasi BT selesai.'])
            ->assertRedirect();

        // Loket Penyerahan mengambil berkas: semua staf terkait diberi tahu.
        Carbon::setTestNow('2026-11-09 09:10:00');

        $this->actingAs($petugas)
            ->post(route('penyerahan.add', $tiket))
            ->assertRedirect();

        foreach ([$stafSatu, $stafDua] as $staf) {
            $this->assertTrue(
                Notifikasi::where('user_id', $staf->id)
                    ->where('tipe', 'tiket.diteruskan')
                    ->where('objek_id', $tiket->id)
                    ->where('created_at', '>=', now()->subMinute())
                    ->exists(),
                "Staf {$staf->name} seharusnya menerima notifikasi berkas sampai di Loket Penyerahan."
            );
        }

        // Berkas diserahkan: semua staf terkait menerima notifikasi selesai.
        Carbon::setTestNow('2026-11-09 09:15:00');

        $this->actingAs($petugas)
            ->post(route('penyerahan.selesai.store', $tiket), [
                'penerima_nama' => 'Pemohon Serah Terima',
                'penerima_hubungan' => 'pemohon',
            ])
            ->assertRedirect();

        foreach ([$stafSatu, $stafDua] as $staf) {
            $this->assertDatabaseHas('notifikasis', [
                'user_id' => $staf->id,
                'tipe' => 'tiket.selesai',
                'objek_id' => $tiket->id,
            ]);
        }
    }

    public function test_staf_terkait_mendapat_notifikasi_saat_berkas_ditolak(): void
    {
        $stafSatu = $this->user('staf_seksi', ['seksi' => '1']);
        $stafDua = $this->user('staf_seksi', ['seksi' => '2']);
        $tiket = $this->tiket();

        $this->actingAs($stafSatu)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'verifikasi']);

        $this->actingAs($stafSatu)
            ->post(route('seksi.selesai.store', $tiket), ['catatan' => 'Selesai.']);

        Carbon::setTestNow('2026-11-09 09:05:00');

        $this->actingAs($stafDua)
            ->post(route('seksi.add', $tiket), ['peran_bidang' => 'validator_bt']);

        Carbon::setTestNow('2026-11-09 09:10:00');

        $this->actingAs($stafDua)
            ->post(route('seksi.tolak', $tiket), ['catatan' => 'Berkas perlu perbaikan.'])
            ->assertRedirect();

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $stafSatu->id,
            'tipe' => 'tiket.ditolak',
            'objek_id' => $tiket->id,
        ]);
    }
}
