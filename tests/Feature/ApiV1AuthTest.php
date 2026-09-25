<?php

namespace Tests\Feature;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * SPA React (Fase 1) — shell /app + API auth Sanctum stateful:
 *   shell SPA, login, me, logout, dashboard, dan tracking publik (kode berslash).
 */
class ApiV1AuthTest extends TestCase
{
    use RefreshDatabase;

    private const SPA_ORIGIN = 'http://loket2026.test';

    private function user(string $role = 'admin'): User
    {
        return User::factory()->create([
            'name' => 'Petugas '.ucfirst($role),
            'username' => $role,
            'email' => $role.'@loket.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
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

    public function test_shell_spa_terlayani_di_prefix_app(): void
    {
        $this->get('/app/dashboard')
            ->assertOk()
            ->assertSee('id="root"', false);
    }

    public function test_login_berhasil_mengembalikan_user(): void
    {
        $this->user('loket');

        $response = $this->apiCall('postJson', '/api/v1/login', [
            'username' => 'loket',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'loket')
            ->assertJsonPath('data.user.role', 'loket')
            ->assertJsonPath('data.user.role_label', 'Loket Penerimaan');

        // Simulasi request baru (guard dimuat ulang) — session tetap dikenali.
        $this->app['auth']->forgetGuards();

        $this->apiCall('getJson', '/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.user.username', 'loket');
    }

    public function test_login_salah_password_mengembalikan_401(): void
    {
        $this->user('loket');

        $this->apiCall('postJson', '/api/v1/login', [
            'username' => 'loket',
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
        $user = $this->user('verifikator');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.user.role', 'verifikator')
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

        // Konsolidasi dibatalkan: GET /dashboard kembali menghidangkan Blade
        // view('dashboard.index') penuh, bukan redirect ke shell React SPA.
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Pelayanan', false)
            ->assertViewHas('totalTiket')
            ->assertViewHas('tiketHariIni')
            ->assertViewHas('tiketSelesai')
            ->assertViewHas('tiketDikembalikan')
            ->assertViewHas('stageCounts')
            ->assertViewHas('monthlyData')
            ->assertViewHas('recentTikets')
            ->assertViewHas('recentActivities');
    }

    public function test_tracking_publik_menangani_kode_berslash(): void
    {
        $jenis = JenisPermohonan::create([
            'kode' => 'TRK',
            'nama' => 'Tracking API Test',
            'kategori' => 'umum',
            'is_active' => true,
        ]);

        $tiket = Tiket::create([
            'kode_tiket' => 'DEMO/V6/T6',
            'nomor_antrian' => 1,
            'tanggal_masuk' => now(),
            'jenis_permohonan_id' => $jenis->id,
            'nama_pemohon' => 'Pemohon Tracking',
            'no_hp_pemohon' => '08130000123',
            'jumlah_bidang' => 1,
            'status' => 'diterima',
        ]);

        $this->get('/api/v1/tracking/DEMO%2FV6%2FT6')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.no_tiket', $tiket->kode_tiket)
            ->assertJsonPath('data.nama_pemohon', 'Pemohon Tracking');
    }
}
