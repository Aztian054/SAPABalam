<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan kronologis pada pengaduan (urutan 1, 2, 3...).
 * Transparan bagi semua Kasi yang menjadi target dalam rantai bantuan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan_catatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->cascadeOnDelete();
            $table->string('dari_sisi', 20); // 'loket', 'kasi_1', dst.
            $table->string('seksi', 10)->nullable();
            $table->string('jenis', 20); // 'kirim_awal', 'minta_bantuan', 'balasan_bantuan', 'penyelesaian_akhir'
            $table->text('catatan');
            $table->integer('urutan');
            $table->timestamps();

            $table->index(['pengaduan_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_catatans');
    }
};
