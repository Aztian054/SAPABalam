<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan warkah BT/SU. Dibuat otomatis oleh wizard Tahap 4.
     * Pengelolaan pinjaman fisik menyusul pada fase warkah.
     */
    public function up(): void
    {
        Schema::create('warkah_permintaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->cascadeOnDelete();
            $table->string('jenis', 2);
            $table->string('status', 20)->default('diminta');
            $table->foreignId('diminta_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_permintaan')->nullable();
            $table->foreignId('peminjam_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_peminjam', 200)->nullable();
            $table->string('seksi', 10)->nullable();
            $table->date('tanggal_pinjam')->nullable();
            $table->date('batas_kembali')->nullable();
            $table->date('tanggal_kembali')->nullable();
            $table->string('pengembali', 200)->nullable();
            $table->string('kondisi', 20)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['tiket_id', 'jenis']);
            $table->index('status');
            $table->index('tanggal_permintaan');
            $table->index('batas_kembali');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warkah_permintaans');
    }
};
