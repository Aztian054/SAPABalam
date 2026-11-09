<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket_nomor_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->cascadeOnDelete();
            $table->string('no_lama', 50);
            $table->string('no_baru', 50);
            $table->text('alasan');
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waktu');
            $table->timestamps();

            $table->index(['tiket_id', 'waktu']);
            $table->index('no_lama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_nomor_riwayat');
    }
};
