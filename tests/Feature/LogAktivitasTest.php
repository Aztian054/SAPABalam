<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Log audit & aktivitas (Admin/Super Admin).
 *
 * Mencatat login/logout, perubahan akun, perubahan setting, dan lepas
 * tiket; halaman Log dapat difilter berdasarkan aksi/tanggal/pelaku.
 * Aktivitas Super Admin tidak pernah dicatat maupun ditampilkan.
 */
class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_admin_dapat_membuka_halaman_log(): void
    {
        $admin = $this->user();

        LogAktivitas::create([
            'user_id' => $admin->id,
            'nama_pelaku' => $admin->name,
            'role_pelaku' => $admin->role,
            'aksi' => 'UJI COBA',
            'objek' => 'Contoh Objek',
            'detail' => 'Baris log contoh',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.log'))
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Contoh Objek')
            ->assertSee('Baris log contoh');
    }

    public function test_pengguna_biasa_tidak_dapat_membuka_log(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($loket)
            ->get(route('admin.log'))
            ->assertForbidden();
    }

    public function test_login_dan_logout_tercatat(): void
    {
        $user = $this->user('loket_pendaftaran');

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $user->id,
            'aksi' => 'LOGIN',
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $user->id,
            'aksi' => 'LOGOUT',
        ]);
    }

    public function test_aktivitas_super_admin_tidak_tercatat_dan_tidak_tampil(): void
    {
        $superAdmin = $this->user('super_admin');
        $admin = $this->user();

        $this->post(route('login.submit'), [
            'username' => $superAdmin->username,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        // Masuk/keluar Super Admin tidak boleh meninggalkan baris apa pun.
        $this->assertSame(0, LogAktivitas::count());

        // Baris lama (dari sebelum aturan berlaku) juga disembunyikan.
        LogAktivitas::create([
            'user_id' => $superAdmin->id,
            'nama_pelaku' => $superAdmin->name,
            'role_pelaku' => 'super_admin',
            'aksi' => 'LOGIN',
            'objek' => 'Jejak Lama Super Admin',
        ]);
        LogAktivitas::create([
            'nama_pelaku' => 'Admin Uji',
            'role_pelaku' => 'admin',
            'aksi' => 'UBAH SETTING',
            'objek' => 'Setting Terlihat',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.log'))
            ->assertOk()
            ->assertSee('Setting Terlihat')
            ->assertDontSee('Jejak Lama Super Admin');
    }

    public function test_pembuatan_akun_tercatat_tanpa_password(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Petugas Log',
                'username' => 'petugas_log',
                'email' => 'petugas_log@loket.test',
                'password' => 'rahasia123',
                'role' => 'loket_pendaftaran',
            ])
            ->assertSessionHasNoErrors();

        $log = LogAktivitas::where('aksi', 'BUAT AKUN')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('petugas_log', $log->objek);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringNotContainsString('rahasia123', (string) $log->detail);
    }

    public function test_perubahan_setting_tercatat_dengan_nilai_lama_dan_baru(): void
    {
        $admin = $this->user();

        $this->actingAs($admin)
            ->put(route('admin.setting.update'), [
                'batas_macet_hari' => 9,
                'batas_warkah_hari' => 7,
                'hari_arsip' => 30,
                'batas_tanggap_pengaduan_hari' => 2,
                'retensi_log_hari' => 90,
                'prefix_tiket' => 'JP',
                'prefix_pengaduan' => 'LP',
                'padding_tiket' => 3,
            ])
            ->assertSessionHasNoErrors();

        $log = LogAktivitas::where('aksi', 'UBAH SETTING')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Batas hari penanda MACET: 3 → 9', (string) $log->detail);
    }

    public function test_log_dapat_difilter_berdasarkan_aksi(): void
    {
        $admin = $this->user();

        LogAktivitas::create([
            'nama_pelaku' => 'Sistem', 'aksi' => 'LOGIN', 'objek' => 'Masuk A',
        ]);
        LogAktivitas::create([
            'nama_pelaku' => 'Sistem', 'aksi' => 'UBAH SETTING', 'objek' => 'Pengaturan Rahasia',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.log', ['aksi' => 'LOGIN']))
            ->assertOk()
            ->assertSee('Masuk A')
            ->assertDontSee('Pengaturan Rahasia');
    }

    public function test_log_lama_dibersihkan_mengikuti_retensi_setting(): void
    {
        $lama = LogAktivitas::create(['nama_pelaku' => 'Sistem', 'aksi' => 'LOGIN']);
        $lama->forceFill(['created_at' => now()->subDays(100)])->save();

        $baru = LogAktivitas::create(['nama_pelaku' => 'Sistem', 'aksi' => 'LOGIN']);

        $this->artisan('loket:bersihkan-log')->assertSuccessful();

        $this->assertDatabaseMissing('log_aktivitas', ['id' => $lama->id]);
        $this->assertDatabaseHas('log_aktivitas', ['id' => $baru->id]);
    }
}
