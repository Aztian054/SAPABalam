<?php

namespace Tests\Feature;

use App\Http\Middleware\ImpersonateMiddleware;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Lihat/Coba Sebagai Akun" (Super Admin, read-only).
 *
 * Sesi asli Super Admin tidak boleh tertimpa; aksi yang mengubah data
 * diblokir selama mode aktif.
 */
class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ], $attributes));
    }

    public function test_halaman_lihat_coba_hanya_untuk_super_admin(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran', ['name' => 'Petugas Loket Uji']);

        $this->actingAs($admin)->get(route('super-admin.impersonate.index'))->assertForbidden();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.impersonate.index'))
            ->assertOk()
            ->assertSee('Lihat/Coba Sebagai Akun')
            ->assertSee('Petugas Loket Uji')
            ->assertDontSee(route('super-admin.impersonate.start', $superAdmin->id));
    }

    public function test_super_admin_dapat_mulai_dan_mengakhiri_impersonasi(): void
    {
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran', ['name' => 'Rina Loket']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.impersonate.start', $loket->id))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas(ImpersonateMiddleware::KUNCI_TARGET, $loket->id)
            ->assertSessionHas(ImpersonateMiddleware::KUNCI_ASAL, $superAdmin->id);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Anda melihat sebagai Rina Loket')
            ->assertSee('Kembali ke Super Admin');

        $this->get(route('impersonate.stop'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing(ImpersonateMiddleware::KUNCI_TARGET)
            ->assertSessionMissing(ImpersonateMiddleware::KUNCI_ASAL)
            ->assertSessionHas('success');

        // Super Admin bergerak tanpa jejak: aktivitas lihat/coba tidak ditulis.
        $this->assertDatabaseMissing('log_aktivitas', [
            'aksi' => 'MULAI IMPERSONASI',
        ]);
        $this->assertDatabaseMissing('log_aktivitas', [
            'aksi' => 'SELESAI IMPERSONASI',
        ]);
    }

    public function test_mode_lihat_coba_bersifat_baca_saja(): void
    {
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($superAdmin)->post(route('super-admin.impersonate.start', $loket->id));

        $this->get(route('dashboard'))->assertOk();

        // Aksi tulis diblokir walaupun jabatan target mengizinkannya.
        $this->post(route('pendaftaran.tahap1.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('tikets', 0);
    }

    public function test_super_admin_lain_tidak_dapat_di_impersonate(): void
    {
        $superAdmin = $this->user('super_admin');
        $lain = $this->user('super_admin');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.impersonate.start', $lain->id))
            ->assertNotFound();
    }

    public function test_sesi_impersonate_berakhir_bila_akun_target_dinonaktifkan(): void
    {
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($superAdmin)->post(route('super-admin.impersonate.start', $loket->id));

        $loket->update(['is_active' => false]);

        $this->get(route('dashboard'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing(ImpersonateMiddleware::KUNCI_TARGET);
    }

    public function test_stop_tanpa_mode_impersonasi_tidak_mengeluarkan_pengguna(): void
    {
        $superAdmin = $this->user('super_admin');

        // Dulu route ini langsung logout; sekarang harus aman bila diakses
        // tanpa sesi impersonate (mis. bookmark lama / tombol kembali browser).
        $this->actingAs($superAdmin)
            ->get(route('impersonate.stop'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('warning');

        $this->assertAuthenticatedAs($superAdmin);
        $this->assertSame(0, LogAktivitas::where('aksi', 'SELESAI IMPERSONASI')->count());
    }

    public function test_menu_keluar_sistem_diganti_kembali_ke_super_admin(): void
    {
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran', ['name' => 'Rina Loket']);

        $this->actingAs($superAdmin)->post(route('super-admin.impersonate.start', $loket->id));

        // Form logout memakai POST dan diblokir middleware baca-saja, jadi
        // selama mode aktif harus diganti tautan GET kembali ke Super Admin.
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kembali ke Super Admin')
            ->assertDontSee('Keluar Sistem');
    }

    public function test_kembali_ke_super_admin_dapat_diakses_meski_target_tanpa_akses_admin(): void
    {
        $superAdmin = $this->user('super_admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($superAdmin)->post(route('super-admin.impersonate.start', $loket->id));

        // Bukan route super_admin (target tidak punya jabatan itu), tetap boleh.
        $this->get(route('impersonate.stop'))->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Anda melihat sebagai');

        $this->assertSame(0, LogAktivitas::where('aksi', 'SELESAI IMPERSONASI')->count());
    }
}
