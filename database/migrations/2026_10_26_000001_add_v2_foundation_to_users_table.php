<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fondasi V2 — jabatan baru + seksi pada akun.
     *
     * Kolom `role` diubah dari enum menjadi string agar jabatan V2 dapat
     * ditambahkan tanpa mengubah tipe kolom di kemudian hari.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('loket_pendaftaran')->change();
            $table->string('seksi', 10)->nullable()->after('role');
            $table->index('seksi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['seksi']);
            $table->dropColumn('seksi');
        });
    }
};
