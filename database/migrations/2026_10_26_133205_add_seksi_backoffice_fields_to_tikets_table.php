<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 — back office Seksi 1/2.
     *
     * `peran_bidang` mencatat pekerjaan yang dipilih staf saat ADD
     * (Verifikasi / Validator BT / Validator SU / Alih Media BT / SU).
     * `ditolak_pada` + `alasan_penolakan` menyimpan jejak penolakan agar
     * Daftar Ditolak tetap tampil walau tiket sudah ADD ULANG.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->string('peran_bidang', 30)->nullable()->after('penanggung_jawab_id');
            $table->timestamp('ditolak_pada')->nullable()->after('diambil_pada');
            $table->text('alasan_penolakan')->nullable()->after('ditolak_pada');

            $table->index('penanggung_jawab_id');
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropIndex(['penanggung_jawab_id']);
            $table->dropColumn(['peran_bidang', 'ditolak_pada', 'alasan_penolakan']);
        });
    }
};
