<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * API v1 auth Sanctum stateful + dashboard: login, me, logout, dashboard.
 * Tidak ada lagi endpoint tracking publik maupun shell SPA React.
 */
class ApiV1AuthTest extends TestCase
{
    use RefreshDatabase;

    private const SPA_ORIGIN = 'http://loket2026.test';

    private function user(string $role = 'admin', array $atribut = []): User
    {
        return User::factory()->create([
            'name' => 'Petugas '.ucfirst($role),
            'username' => $role,
            'email' => $role.'@loket.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
            ...$atribut,
        ]);
    }

    /**
     * Request API ala browser SPA — Sanctum memutuskan mode stateful dari
     * header Origin/Referer, sama seperti axios dengan kredensial cookie.
     */
    private function apiCall(string $method, string $uri, array $data = [], array $headers = [])
    {
        return $this->withHeaders(array_merge(['Origin' => self::SPA_ORIGIN], $headers))
            ->{$method}($uri, $data);
    }

    public function test_login_berhasil_mengembalikan_user(): void
    {
        $this->user('loket_pendaftaran');

        $response = $this->apiCall('postJson', '/api/v1/login', [
            'username' => 'loket_pendaftaran',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'loket_pendaftaran')
            ->assertJsonPath('data.user.role', 'loket_pendaftaran')
            ->assertJsonPath('data.user.role_label', 'Loket Pendaftaran');

        // Simulasi request baru (guard dimuat ulang) — session tetap dikenali.
        $this->app['auth']->forgetGuards();

        $this->apiCall('getJson', '/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.user.username', 'loket_pendaftaran');
    }

    public function test_login_salah_password_mengembalikan_401(): void
    {
        $this->user('loket_pendaftaran');

        $this->apiCall('postJson', '/api/v1/login', [
            'username' => 'loket_pendaftaran',
            'password' => 'salah',
        ])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_me_memerlukan_autentikasi(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_me_mengembalikan_user_terautentikasi(): void
    {
        $user = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'staf_seksi')
            ->assertJsonPath('data.user.is_active', true);
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->user('admin');

        // Login dulu lewat alur nyata (cookie session), lalu keluar.
        $this->apiCall('postJson', '/api/v1/login', [
            'username' => 'admin',
            'password' => 'password',
        ])->assertOk();

        $this->apiCall('postJson', '/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        // Setelah logout, /me tanpa sesi harus ditolak
        // (guard dimuat ulang agar teruji lewat session, bukan cache memori).
        $this->app['auth']->forgetGuards();

        $this->apiCall('getJson', '/api/v1/me')->assertUnauthorized();
    }

    public function test_dashboard_memerlukan_autentikasi(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_mengembalikan_struktur_lengkap(): void
    {
        $user = $this->user('admin');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'total_tiket',
                    'tiket_hari_ini',
                    'tiket_selesai',
                    'tiket_ditolak',
                    'stage_counts',
                    'monthly_data',
                    'recent_tikets',
                    'recent_activities',
                    'user' => ['id', 'name', 'username', 'role', 'role_label'],
                ],
            ]);
    }

    public function test_dashboard_blade_menampilkan_view(): void
    {
        $user = $this->user('admin');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Pelayanan', false)
            ->assertViewHas('totalTiket')
            ->assertViewHas('tiketHariIni')
            ->assertViewHas('tiketSelesai')
            ->assertViewHas('tiketDitolak')
            ->assertViewHas('statusCounts')
            ->assertViewHas('monthlyData')
            ->assertViewHas('recentTikets')
            ->assertViewHas('recentActivities');
    }
}
