<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah flag penyerahan Warkah → Validator pada tabel tikets.
 *
 * Selaras dengan "Konfigurasi Alur Paralel" (dokumen Alur_00 + PRD 2.2):
 * Warkah menandai status sertipikat = DISERAHKAN → mengaktifkan
 * `diserahkan_ke_validator = true` → membuka tahap Validator BT/SU
 * (paralel, TANPA menunggu Verifikator).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->boolean('diserahkan_ke_validator')->default(false)->after('status_pra_suel');
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropColumn('diserahkan_ke_validator');
        });
    }
};
