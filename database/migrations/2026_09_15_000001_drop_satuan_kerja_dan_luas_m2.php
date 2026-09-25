<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom "Satuan Kerja" (tikets) dan "Luas (m²)" (bidang_tanahs)
     * karena tidak dibutuhkan pada website. Data dummy disesuaikan pula.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropColumn('satuan_kerja');
        });

        Schema::table('bidang_tanahs', function (Blueprint $table) {
            $table->dropColumn('luas_m2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->string('satuan_kerja', 200)->nullable();
        });

        Schema::table('bidang_tanahs', function (Blueprint $table) {
            $table->decimal('luas_m2', 12, 2)->nullable();
        });
    }
};
