<?php

use Database\Seeders\MenuSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Menyelaraskan menu sidebar pada instalasi yang sudah berjalan.
 *
 * Migration awal hanya menanam menu yang ada di config saat itu. Setiap
 * kali menu default ditambah, jalankan seeder idempoten ini lewat migrasi
 * baru agar `php artisan migrate` ikut memperbarui sidebar tanpa
 * menghapus menu yang sudah diubah manual.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new MenuSeeder)->run();
    }

    public function down(): void
    {
        // Menu tidak dihapus: bisa saja sudah diubah manual oleh operator.
    }
};
