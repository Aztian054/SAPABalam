<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus kolom SLA/Target & Status SPS dari tikets + batas_hari_sla dari jenis_permohonans.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_target_selesai',
                'status_sps',
                'tanggal_sps',
            ]);
        });

        Schema::table('jenis_permohonans', function (Blueprint $table) {
            $table->dropColumn('batas_hari_sla');
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->date('tanggal_target_selesai')->nullable()->after('keterangan');
            $table->boolean('status_sps')->default(false)->after('tanggal_target_selesai');
            $table->date('tanggal_sps')->nullable()->after('status_sps');
        });

        Schema::table('jenis_permohonans', function (Blueprint $table) {
            $table->integer('batas_hari_sla')->default(30)->after('kategori');
        });
    }
};
