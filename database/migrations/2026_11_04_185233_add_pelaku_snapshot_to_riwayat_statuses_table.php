<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot pelaku pada riwayat alur berkas.
 *
 * Nama & jabatan disalin saat kejadian agar riwayat tetap utuh meskipun
 * akun diubah namanya, jabatannya diganti, atau akunnya dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_statuses', function (Blueprint $table) {
            $table->string('nama_pelaku', 200)->nullable()->after('changed_by');
            $table->string('jabatan_pelaku', 100)->nullable()->after('nama_pelaku');
            $table->string('role_pelaku', 50)->nullable()->after('jabatan_pelaku');
        });

        DB::statement('
            UPDATE riwayat_statuses
            SET nama_pelaku = (SELECT name FROM users WHERE users.id = riwayat_statuses.changed_by),
                jabatan_pelaku = (SELECT jabatan FROM users WHERE users.id = riwayat_statuses.changed_by),
                role_pelaku = (SELECT role FROM users WHERE users.id = riwayat_statuses.changed_by)
            WHERE changed_by IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('riwayat_statuses', function (Blueprint $table) {
            $table->dropColumn(['nama_pelaku', 'jabatan_pelaku', 'role_pelaku']);
        });
    }
};
