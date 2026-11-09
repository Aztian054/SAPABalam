<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 — Arsip otomatis (Blueprint V2, modul 13).
     *
     * Tiket `selesai` yang melewati `config('loket.hari_arsip')` ditandai
     * di sini oleh scheduler harian. Penanda dipisah dari `status` agar
     * tiket arsip tetap terbaca sebagai "selesai" (read-only).
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            if (! Schema::hasColumn('tikets', 'diarsipkan_pada')) {
                $table->timestamp('diarsipkan_pada')->nullable()->after('tanggal_selesai');
                $table->index('diarsipkan_pada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropIndex(['diarsipkan_pada']);
            $table->dropColumn('diarsipkan_pada');
        });
    }
};
