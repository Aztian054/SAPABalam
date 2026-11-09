<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Menanam ulang menu sidebar default dari config/sidebar.php.
 *
 * Idempoten: entri dicocokkan berdasarkan (role_owner, route) sehingga
 * aman dijalankan berkali-kali tanpa membuat duplikat.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('sidebar.defaults', []) as $roleOwner => $menus) {
            foreach ($menus as $urutan => $menu) {
                $atribut = [
                    'label' => $menu['label'],
                    'icon' => $menu['icon'] ?? null,
                    'order_position' => $urutan + 1,
                    'is_active' => true,
                    'active_patterns' => $menu['active'] ?? null,
                ];

                // Kolom `butuh_demo` baru ada pada migrasi setelah menu awal,
                // sehingga migrasi lama tetap aman saat `migrate` dari nol.
                if (Schema::hasColumn('menus', 'butuh_demo')) {
                    $atribut['butuh_demo'] = $menu['butuh_demo'] ?? false;
                }

                Menu::updateOrCreate(
                    [
                        'role_owner' => $roleOwner,
                        'route' => $menu['route'],
                    ],
                    $atribut,
                );
            }
        }
    }
}
