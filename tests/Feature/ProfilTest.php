<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Profil mandiri: biodata, username, dan password.
 *
 * Role & seksi bersifat tetap dan tidak boleh berubah dari halaman ini.
 */
class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'loket_pendaftaran', array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ], $attributes));
    }

    private function jsonHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('profil.edit'))->assertRedirect(route('login'));
    }

    public function test_halaman_profil_menampilkan_role_yang_tidak_dapat_diubah(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($user)
            ->get(route('profil.edit'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->username)
            ->assertSee('Staf Seksi')
            ->assertSee('Role bersifat tetap')
            ->assertSee('Biodata')
            ->assertSee('Keamanan Akun')
            ->assertSee('Password Saat Ini')
            ->assertSee('Konfirmasi Password Baru');
    }

    public function test_pengguna_dapat_mengubah_biodata_dan_jabatan_sendiri(): void
    {
        $user = $this->user('loket_pendaftaran');

        $this->actingAs($user)->put(route('profil.update'), [
            'name' => 'Nama Baru Pengguna',
            'nip' => '198501012025011001',
            'no_hp' => '081234567890',
            'jabatan' => 'Validator',
        ], $this->jsonHeaders())
            ->assertOk()
            ->assertJson(['message' => 'Biodata berhasil diperbarui.'])
            ->assertJsonMissingValidationErrors();

        $user->refresh();
        $this->assertSame('Nama Baru Pengguna', $user->name);
        $this->assertSame('198501012025011001', $user->nip);
        $this->assertSame('081234567890', $user->no_hp);
        $this->assertSame('Validator', $user->jabatan);
        $this->assertSame('loket_pendaftaran', $user->role);
    }

    public function test_role_dan_seksi_tidak_dapat_diubah_lewat_profil(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->put(route('profil.update'), [
                'name' => $user->name,
                'no_hp' => $user->no_hp,
                'jabatan' => $user->jabatan,
                'role' => 'admin',
                'seksi' => '2',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors(['role', 'seksi']);

        $user->refresh();
        $this->assertSame('staf_seksi', $user->role);
        $this->assertSame('1', $user->seksi);
    }

    public function test_pengguna_dapat_mengubah_username_sendiri(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profil.username.update'), [
            'username' => 'username_baru',
            'current_password' => 'password',
        ], $this->jsonHeaders())
            ->assertOk()
            ->assertJson(['message' => 'Username berhasil diperbarui.']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'username_baru',
        ]);
    }

    public function test_username_duplikat_ditolak(): void
    {
        $user = $this->user();
        $lain = $this->user('loket_ploting');

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->patch(route('profil.username.update'), [
                'username' => $lain->username,
                'current_password' => 'password',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors('username');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => $user->username,
        ]);
    }

    public function test_username_berbentuk_email_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->patch(route('profil.username.update'), [
                'username' => 'nama@contoh.test',
                'current_password' => 'password',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors('username');
    }

    public function test_username_membutuhkan_password_saat_ini(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->patch(route('profil.username.update'), [
                'username' => 'username_baru',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors('current_password');
    }

    public function test_password_saat_ini_salah_ditolak(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->patch(route('profil.password.update'), [
                'current_password' => 'password_salah',
                'password' => 'passwordbaru',
                'password_confirmation' => 'passwordbaru',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_konfirmasi_password_baru_harus_cocok(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->from(route('profil.edit'))
            ->patch(route('profil.password.update'), [
                'current_password' => 'password',
                'password' => 'passwordbaru',
                'password_confirmation' => 'passwordlain',
            ], $this->jsonHeaders())
            ->assertJsonValidationErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_pengguna_dapat_mengubah_password_dan_salinan_plaintext_ikut_sinkron(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profil.password.update'), [
            'current_password' => 'password',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ], $this->jsonHeaders())
            ->assertOk()
            ->assertJson(['message' => 'Password berhasil diperbarui.']);

        $user->refresh();
        $this->assertTrue(Hash::check('passwordbaru', $user->password));
        $this->assertSame('passwordbaru', $user->password_text);

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'passwordbaru',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_saat_ini_ditampilkan_untuk_eye_icon(): void
    {
        $user = $this->user();
        $user->update(['password_text' => 'password']);

        $this->actingAs($user)
            ->get(route('profil.edit'))
            ->assertOk()
            ->assertSee('data-toggle-password="inputCurrentPassword"', false)
            ->assertSee('data-toggle-password="inputCurrentPasswordUsername"', false);
    }
}
