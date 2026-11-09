<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Setiap menu sidebar harus benar-benar dapat dibuka pemiliknya.
 *
 * Menangkap ketidakcocokan view vs fitur berupa menu yang tampil tetapi
 * route-nya tidak ada, atau route-nya menolak jabatan pemilik menu (403).
 */
class MenuNavigationTest extends TestCase
{
    use RefreshDatabase;

    /** Menu yang memang sengaja tertutup selama mode demo tidak aktif. */
    private const MENU_KHUSUS_DEMO = ['super-admin.demo.index'];

    private array $gagal = [];

    private function user(string $role, ?string $seksi = null): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' Uji',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'seksi' => $seksi,
            'is_active' => true,
        ]);
    }

    public function test_menu_sidebar_dapat_dibuka_pemiliknya(): void
    {
        foreach (array_keys(User::ROLES) as $role) {
            $seksi = match (true) {
                in_array($role, ['staf_seksi', 'kasi_seksi', 'kasi_unit'], true) => '1',
                $role === 'kasubbag_tu' => 'TU',
                default => null,
            };

            $user = $this->user($role, $seksi);

            $rute = Menu::where('role_owner', $role)->pluck('route')->all();
            $rute[] = 'dashboard';
            $rute[] = 'reports.index';

            foreach (array_unique($rute) as $nama) {
                if (! Route::has($nama)) {
                    $this->gagal[] = "{$role} menu -> route {$nama} TIDAK ADA";

                    continue;
                }

                $url = route($nama, absolute: false);

                if (str_contains($url, '{')) {
                    continue;
                }

                $status = $this->actingAs($user)->get($url)->getStatusCode();

                // Menu demo hanya boleh 403 saat mode demo mati (APP_ENV=testing).
                if (in_array($nama, self::MENU_KHUSUS_DEMO, true)) {
                    $this->assertSame(403, $status, "{$role} GET {$url} seharusnya 403 saat demo mati.");

                    continue;
                }

                if ($status >= 400) {
                    $this->gagal[] = "{$role} GET {$url} -> {$status}";
                }
            }
        }

        fwrite(STDERR, $this->gagal === [] ? "\nSemua menu terbuka\n" : "\n".implode("\n", $this->gagal)."\n");

        $this->assertSame([], $this->gagal);
    }
}
