<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Setting;
use App\Models\Tiket;
use App\Models\User;
use App\Services\TiketNomorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Setting operasional (Admin): batas hari, penomoran, retensi log.
 * Perubahan harus langsung dipakai kode (bukan hanya tersimpan).
 */
class SettingTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'batas_macet_hari' => 7,
            'batas_warkah_hari' => 10,
            'hari_arsip' => 45,
            'batas_tanggap_pengaduan_hari' => 4,
            'retensi_log_hari' => 120,
            'prefix_tiket' => 'lk',
            'prefix_pengaduan' => 'lp',
            'padding_tiket' => 4,
        ], $overrides);
    }

    private function tiketSelesai(int $hariLalu): Tiket
    {
        $jenis = JenisPermohonan::create([
            'kode' => 'JP01',
            'nama' => 'Peralihan Hak',
            'kategori' => 'umum',
            'is_active' => true,
        ]);

        return Tiket::create([
            'kode_tiket' => 'JP28102026001',
            'nomor_antrian' => 'A-001',
            'tanggal_masuk' => now()->subDays($hariLalu + 1)->toDateString(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Setting',
            'no_hp_pemohon' => '081234567890',
            'jumlah_bidang' => 1,
            'status' => 'selesai',
            'tanggal_selesai' => now()->subDays($hariLalu)->toDateString(),
        ]);
    }

    public function test_admin_dapat_membuka_halaman_setting_dengan_nilai_default(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)
            ->get(route('admin.setting'))
            ->assertOk()
            ->assertSee('Batas hari penanda MACET')
            ->assertSee('Batas kembali warkah')
            ->assertSee('Hari menuju arsip otomatis')
            ->assertSee('Batas tanggap pengaduan')
            ->assertSee('Prefix No. Tiket')
            ->assertSee('Retensi log');
    }

    public function test_pengguna_biasa_tidak_dapat_membuka_atau_mengubah_setting(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($loket)->get(route('admin.setting'))->assertForbidden();
        $this->actingAs($loket)->put(route('admin.setting.update'), $this->payload())->assertForbidden();
    }

    public function test_admin_dapat_menyimpan_setting_dan_nilainya_langsung_dipakai(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)
            ->put(route('admin.setting.update'), $this->payload())
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(7, Setting::angka('batas_macet_hari'));
        $this->assertSame(45, Setting::angka('hari_arsip'));
        $this->assertSame('LK', Setting::teks('prefix_tiket'));

        // Prefix & padding baru langsung dipakai generator nomor tiket.
        $nomor = app(TiketNomorService::class)->prefix(Carbon::create(2026, 10, 28));
        $this->assertSame('LK28102026', $nomor);

        $kode = app(TiketNomorService::class)->generate(Carbon::create(2026, 10, 28));
        $this->assertMatchesRegularExpression('/^LK28102026\d{4}$/', $kode);
    }

    public function test_setting_hari_arsip_mempengaruhi_kandidat_arsip_otomatis(): void
    {
        $admin = $this->user();
        $this->tiketSelesai(5);

        // Default 30 hari: tiket 5 hari belum masuk kandidat arsip.
        $this->assertSame(0, Tiket::siapDiarsipkan()->count());

        $this->actingAs($admin)
            ->put(route('admin.setting.update'), $this->payload(['hari_arsip' => 3]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Tiket::siapDiarsipkan()->count());
    }

    public function test_nilai_setting_tidak_valid_ditolak(): void
    {
        $admin = $this->user();
        $nilaiAwal = Setting::angka('batas_macet_hari');

        $this->actingAs($admin)
            ->from(route('admin.setting'))
            ->put(route('admin.setting.update'), $this->payload([
                'batas_macet_hari' => 0,
                'prefix_tiket' => 'ABC!',
                'padding_tiket' => 9,
            ]))
            ->assertSessionHasErrors(['batas_macet_hari', 'prefix_tiket', 'padding_tiket']);

        $this->assertSame($nilaiAwal, Setting::angka('batas_macet_hari'));
    }

    public function test_prefix_disimpan_dalam_huruf_besar(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)
            ->put(route('admin.setting.update'), $this->payload([
                'prefix_tiket' => 'ab',
                'prefix_pengaduan' => 'xy',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'prefix_tiket', 'value' => 'AB']);
        $this->assertDatabaseHas('settings', ['key' => 'prefix_pengaduan', 'value' => 'XY']);
    }
}
