<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Akun `super_admin` adalah akun developer yang tidak boleh terlihat
 * maupun tersentuh dari Manajemen Akun Admin.
 */
class HiddenSuperAdminAccountTest extends TestCase
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

    public function test_daftar_akun_tidak_memuat_super_admin(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin', ['name' => 'Pemilik Sistem']);
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee($loket->username)
            ->assertDontSee($superAdmin->username)
            ->assertDontSee('Pemilik Sistem')
            ->assertDontSee('Super Admin');
    }

    public function test_filter_role_super_admin_tidak_menampilkan_akun_tersebut(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin', ['name' => 'Pemilik Sistem']);

        $this->actingAs($admin)
            ->get(route('admin.users', ['role' => 'super_admin']))
            ->assertOk()
            ->assertDontSee($superAdmin->username)
            ->assertDontSee('Pemilik Sistem');
    }

    public function test_halaman_edit_akun_super_admin_tidak_dapat_dibuka(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $superAdmin->id))
            ->assertNotFound();
    }

    public function test_akun_super_admin_tidak_dapat_diubah(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin', [
            'name' => 'Pemilik Sistem',
            'username' => 'pemilik_sistem',
            'email' => 'pemilik@loket.test',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $superAdmin->id), [
                'name' => 'Dibajak',
                'username' => 'dibajak',
                'email' => 'dibajak@loket.test',
                'password' => 'rahasia123',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'name' => 'Pemilik Sistem',
            'username' => 'pemilik_sistem',
            'email' => 'pemilik@loket.test',
            'role' => 'super_admin',
        ]);
    }

    public function test_akun_super_admin_tidak_dapat_dihapus(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin');

        $this->actingAs($admin)
            ->post(route('admin.users.hapus', $superAdmin->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_akun_super_admin_tidak_dapat_dinonaktifkan(): void
    {
        $admin = $this->user('admin');
        $superAdmin = $this->user('super_admin');

        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $superAdmin->id))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'is_active' => true]);
    }

    public function test_role_super_admin_tidak_dapat_dibuat_lewat_form(): void
    {
        $admin = $this->user('admin');
        $jumlahAwal = User::where('role', 'super_admin')->count();

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post(route('admin.users.store'), [
                'name' => 'Super Admin Palsu',
                'username' => 'super_palsu',
                'email' => 'super_palsu@loket.test',
                'password' => 'rahasia123',
                'role' => 'super_admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['username' => 'super_palsu']);
        $this->assertSame($jumlahAwal, User::where('role', 'super_admin')->count());
    }

    public function test_jabatan_tidak_dapat_dinaikkan_menjadi_super_admin(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $loket->id))
            ->put(route('admin.users.update', $loket->id), [
                'name' => $loket->name,
                'username' => $loket->username,
                'email' => $loket->email,
                'role' => 'super_admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $loket->id,
            'role' => 'loket_pendaftaran',
        ]);
    }
}
