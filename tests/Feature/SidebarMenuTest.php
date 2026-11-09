<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sidebar dinamis (Blueprint V2): menu dibaca dari tabel `menus`.
 *
 * - Pengguna biasa hanya melihat menu jabatannya sendiri.
 * - Super Admin melihat seluruh menu, dikelompokkan per jabatan pemilik,
 *   dengan penanda "MODE DEVELOPER".
 * - Jabatan baru otomatis muncul tanpa mengubah Blade.
 */
class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_pengguna_biasa_hanya_melihat_menu_jabatannya_sendiri(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($loket)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pendaftaran Baru')
            ->assertSee('Daftar Berkas Permohonan')
            ->assertDontSee('Manajemen Akun')
            ->assertDontSee('MODE DEVELOPER');
    }

    public function test_super_admin_melihat_seluruh_menu_dikelompokkan_per_jabatan(): void
    {
        $superAdmin = $this->user('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('MODE DEVELOPER')
            // Menu milik Admin.
            ->assertSee('Manajemen Akun')
            // Menu milik Loket Pendaftaran.
            ->assertSee('Pendaftaran Baru')
            // Menu milik Kakan.
            ->assertSee('Monitoring & Evaluasi')
            // Badge jabatan pemilik menu tampil di setiap grup.
            ->assertSee('loket_pendaftaran');
    }

    public function test_jabatan_baru_otomatis_muncul_tanpa_mengubah_blade(): void
    {
        Menu::create([
            'label' => 'Audit Internal',
            'route' => 'reports.index',
            'icon' => 'bi-clipboard-check',
            'role_owner' => 'auditor',
            'order_position' => 1,
            'is_active' => true,
        ]);

        $superAdmin = $this->user('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Auditor')
            ->assertSee('Audit Internal');
    }

    public function test_pengguna_dengan_jabatan_baru_melihat_menunya_sendiri(): void
    {
        Menu::create([
            'label' => 'Audit Internal',
            'route' => 'reports.index',
            'icon' => 'bi-clipboard-check',
            'role_owner' => 'auditor',
            'order_position' => 1,
            'is_active' => true,
        ]);

        $auditor = $this->user('auditor');

        $this->actingAs($auditor)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Audit Internal')
            ->assertDontSee('Manajemen Akun');
    }

    public function test_menu_dengan_route_tidak_tersedia_tidak_ditampilkan(): void
    {
        Menu::create([
            'label' => 'Menu Tanpa Route',
            'route' => 'route.tidak.ada',
            'icon' => 'bi-question-circle',
            'role_owner' => 'admin',
            'order_position' => 99,
            'is_active' => true,
        ]);

        $superAdmin = $this->user('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Menu Tanpa Route');
    }

    public function test_menu_nonaktif_tidak_ditampilkan(): void
    {
        Menu::create([
            'label' => 'Menu Nonaktif',
            'route' => 'reports.index',
            'icon' => 'bi-eye-slash',
            'role_owner' => 'admin',
            'order_position' => 98,
            'is_active' => false,
        ]);

        $superAdmin = $this->user('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Menu Nonaktif');
    }
}
