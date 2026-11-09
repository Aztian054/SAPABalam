<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda menu yang hanya boleh tampil saat mode demo aktif.
 *
 * Menu Skenario/Data Demo tidak berguna di instalasi produksi, sehingga
 * disembunyikan tanpa menghapus barisnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('menus', 'butuh_demo')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table): void {
            $table->boolean('butuh_demo')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('menus', 'butuh_demo')) {
            return;
        }

        Schema::table('menus', function (Blueprint $table): void {
            $table->dropColumn('butuh_demo');
        });
    }
};
