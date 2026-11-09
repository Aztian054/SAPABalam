<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 — Riwayat perubahan batas kembali warkah (Blueprint V2, modul 06).
     *
     * Perpanjangan dilakukan di luar sistem; Petugas Warkah mencatat batas
     * baru + alasan di sini agar perubahan dapat diaudit.
     */
    public function up(): void
    {
        Schema::create('warkah_permintaan_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warkah_permintaan_id')
                ->constrained('warkah_permintaans')
                ->cascadeOnDelete();
            $table->string('aksi', 30);
            $table->string('status_dari', 20)->nullable();
            $table->string('status_ke', 20)->nullable();
            $table->date('batas_lama')->nullable();
            $table->date('batas_baru')->nullable();
            $table->foreignId('oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('warkah_permintaan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warkah_permintaan_riwayats');
    }
};
