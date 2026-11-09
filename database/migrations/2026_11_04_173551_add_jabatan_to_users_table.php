<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jabatan manual (free text) terpisah dari role akses.
     *
     * `role` tetap menjadi penentu hak akses; `jabatan` hanya informasi
     * kedudukan yang dapat diisi sendiri oleh pengguna atau oleh Admin.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable()->after('seksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jabatan');
        });
    }
};
