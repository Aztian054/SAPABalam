<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot jabatan pelaku pada log audit admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->string('jabatan_pelaku', 100)->nullable()->after('role_pelaku');
        });

        DB::statement('
            UPDATE log_aktivitas
            SET jabatan_pelaku = (SELECT jabatan FROM users WHERE users.id = log_aktivitas.user_id)
            WHERE user_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropColumn('jabatan_pelaku');
        });
    }
};
