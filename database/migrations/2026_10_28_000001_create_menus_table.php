<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menu sidebar berbasis database.
 *
 * Data awal (semua jabatan) ikut ditanam di sini agar sidebar langsung
 * terisi setelah `php artisan migrate` — termasuk pada database uji
 * (SQLite :memory: dengan RefreshDatabase). Untuk menanam ulang tanpa
 * migrasi, gunakan `php artisan db:seed --class=MenuSeeder`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->string('route', 150);
            $table->string('icon', 80)->nullable();
            $table->string('role_owner', 50);
            $table->unsignedSmallInteger('order_position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('active_patterns')->nullable();
            $table->timestamps();

            $table->unique(['role_owner', 'route']);
            $table->index(['role_owner', 'order_position']);
        });

        $this->tanamMenuDefault();
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }

    /** Tanam menu awal dari config/sidebar.php. */
    private function tanamMenuDefault(): void
    {
        $defaults = config('sidebar.defaults', []);
        $now = now();
        $baris = [];

        foreach ($defaults as $roleOwner => $menus) {
            foreach ($menus as $urutan => $menu) {
                $baris[] = [
                    'label' => $menu['label'],
                    'route' => $menu['route'],
                    'icon' => $menu['icon'] ?? null,
                    'role_owner' => $roleOwner,
                    'order_position' => $urutan + 1,
                    'is_active' => true,
                    'active_patterns' => isset($menu['active']) ? json_encode($menu['active']) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($baris !== []) {
            DB::table('menus')->insert($baris);
        }
    }
};
