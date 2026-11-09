<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat status pengaduan: terkirim → diterima → selesai, termasuk
     * pengembalian dan kirim ulang. Dipakai pada halaman Detail Pengaduan.
     */
    public function up(): void
    {
        Schema::create('pengaduan_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->cascadeOnDelete();
            $table->string('dari_status', 20)->nullable();
            $table->string('ke_status', 20);
            $table->foreignId('oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('pengaduan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan_riwayats');
    }
};
