<?php

use Database\Seeders\MenuSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Menanam menu Demo pada instalasi yang sudah berjalan.
 *
 * Seeder idempoten sehingga aman dijalankan berkali-kali dan tidak
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
