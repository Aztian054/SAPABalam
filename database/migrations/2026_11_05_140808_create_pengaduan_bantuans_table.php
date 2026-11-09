<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rantai bantuan antar Kasi pada satu pengaduan.
 * Kasi asli meminta bantuan ke 1 Kasi lain, secara berurutan, tanpa batas pengulangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan_bantuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->cascadeOnDelete();
            $table->string('dari_kasi', 10); // Kasi yang meminta bantuan
            $table->string('ke_kasi', 10); // Kasi yang diminta bantuan
            $table->text('catatan_dari'); // Catatan permintaan bantuan
            $table->text('catatan_ke')->nullable(); // Balasan catatan dari Kasi tujuan
            $table->string('status', 20)->default('diajukan'); // 'diajukan', 'ditanggapi'
            $table->integer('urutan');
            $table->timestamps();

            $table->index(['pengaduan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_bantuans');
    }
};
