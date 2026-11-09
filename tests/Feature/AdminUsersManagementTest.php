<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Manajemen akun: edit data & password (role tidak diubah),
 * hapus akun non-admin, dan proteksi akun admin tunggal.
 */
class AdminUsersManagementTest extends TestCase
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

    public function test_akun_dengan_role_petugas_tidak_bisa_mengelola_akun(): void
    {
        $petugas = $this->user('loket_pendaftaran');

        $this->actingAs($petugas)
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_admin_dapat_membuka_halaman_edit_akun(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $loket->id))
            ->assertOk()
            ->assertSee($loket->name)
            ->assertSee($loket->username)
            ->assertSee('Role tidak dapat diubah');
    }

    public function test_admin_dapat_mengubah_data_dan_password_akun(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)->put(route('admin.users.update', $loket->id), [
            'name' => 'Petugas Loket Baru',
            'username' => 'loket_baru',
            'email' => 'loket_baru@test.dev',
            'nip' => '198001012000001001',
            'no_hp' => '081234567890',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.users'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $loket->id,
            'name' => 'Petugas Loket Baru',
            'username' => 'loket_baru',
            'email' => 'loket_baru@test.dev',
            'nip' => '198001012000001001',
            'no_hp' => '081234567890',
        ]);

        // Salinan plaintext ikut diperbarui agar tampak di Manajemen Akun.
        $this->assertDatabaseHas('users', [
            'id' => $loket->id,
            'password_text' => 'rahasia123',
        ]);

        // Password lama tidak berlaku lagi; yang baru berhasil login.
        $this->post(route('login.submit'), [
            'username' => 'loket_baru',
            'password' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($loket->fresh());
    }

    public function test_role_tidak_dapat_diubah_ketika_mengedit_akun(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)->put(route('admin.users.update', $loket->id), [
            'name' => 'Nama Baru',
            'username' => 'loket_nama_baru',
            'email' => 'loket_nama_baru@test.dev',
            'password' => '',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $loket->id,
            'role' => 'loket_pendaftaran',
        ]);
    }

    public function test_username_atau_email_duplikat_ditolak_saat_edit(): void
    {
        $admin = $this->user('admin');
        $a = $this->user('loket_pendaftaran');
        $b = $this->user('loket_ploting');

        $this->actingAs($admin)
            ->from(route('admin.users.edit', $a->id))
            ->put(route('admin.users.update', $a->id), [
                'name' => 'Nama A',
                'username' => $b->username,
                'email' => $b->email,
            ])
            ->assertSessionHasErrors(['username', 'email']);

        $this->assertDatabaseHas('users', ['id' => $a->id, 'username' => $a->username]);
    }

    public function test_tambah_akun_tidak_menerima_role_admin(): void
    {
        $admin = $this->user('admin');
        $jumlah = User::count();

        $this->actingAs($admin)
            ->from(route('admin.users'))
            ->post(route('admin.users.store'), [
                'name' => 'Admin Kedua',
                'username' => 'admin2',
                'email' => 'admin2@test.dev',
                'password' => 'rahasia123',
                'role' => 'admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame($jumlah, User::count());
    }

    public function test_akun_baru_menyimpan_password_plaintext_untuk_pandangan_admin(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Petugas Baru',
                'username' => 'petugas_baru',
                'email' => 'petugas_baru@test.dev',
                'password' => 'rahasia123',
                'role' => 'loket_pendaftaran',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $baru = User::where('username', 'petugas_baru')->first();
        $this->assertNotNull($baru);
        $this->assertSame('rahasia123', $baru->password_text);
        // Kolom password tetap hash bcrypt yang tidak sama dengan plaintext agar login aman.
        $this->assertNotSame('rahasia123', $baru->password);
        $this->assertTrue(Hash::check('rahasia123', $baru->password));

        // Daftar akun menampilkan password saat ini untuk admin.
        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertSee('rahasia123', false);
    }

    public function test_halaman_edit_akun_menampilkan_password_saat_ini(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');
        $loket->update(['password_text' => 'loket1234']);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $loket->id))
            ->assertOk()
            ->assertSee('Password saat ini')
            ->assertSee('loket1234');
    }

    public function test_akun_non_admin_dapat_dihapus(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($admin)
            ->post(route('admin.users.hapus', $loket->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $loket->id]);
    }

    public function test_akun_admin_tidak_dapat_dihapus(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.hapus', $admin->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_akun_admin_tidak_dapat_dinonaktifkan(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $admin->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }

    public function test_nip_dan_no_hp_tersimpan_saat_admin_membuat_akun(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Petugas Baru',
                'username' => 'petugas1',
                'email' => 'petugas1@loket.test',
                'password' => 'rahasia123',
                'role' => 'loket_pendaftaran',
                'nip' => '198501012025011001',
                'no_hp' => '081234567890',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'petugas1',
            'nip' => '198501012025011001',
            'no_hp' => '081234567890',
        ]);
    }

    public function test_jabatan_manual_tersimpan_saat_admin_membuat_akun(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Petugas Baru',
                'username' => 'petugas_jabatan',
                'email' => 'petugas_jabatan@loket.test',
                'password' => 'rahasia123',
                'role' => 'staf_seksi',
                'seksi' => '1',
                'jabatan' => 'Validator',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'petugas_jabatan',
            'jabatan' => 'Validator',
        ]);
    }

    public function test_admin_dapat_mengubah_jabatan_tanpa_mengubah_role(): void
    {
        $admin = $this->user('admin');
        $staf = $this->user('staf_seksi');
        $staf->update(['seksi' => '1']);

        $this->actingAs($admin)->put(route('admin.users.update', $staf->id), [
            'name' => $staf->name,
            'username' => $staf->username,
            'email' => $staf->email,
            'seksi' => '1',
            'jabatan' => 'Verifikator Berkas',
        ])->assertSessionHasNoErrors();

        $staf->refresh();
        $this->assertSame('Verifikator Berkas', $staf->jabatan);
        $this->assertSame('staf_seksi', $staf->role);
    }

    public function test_daftar_akun_menampilkan_kolom_role_dan_jabatan(): void
    {
        $admin = $this->user('admin');
        $loket = $this->user('loket_pendaftaran');
        $loket->update(['jabatan' => 'Petugas Depan']);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Semua Role')
            ->assertSee('<th>Role</th>', false)
            ->assertSee('<th>Jabatan</th>', false)
            ->assertSee('Petugas Depan');
    }
}
