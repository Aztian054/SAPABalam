<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Draf wizard pendaftaran 4 tahap. Nomor tiket sudah dipesan di sini agar
     * tidak bentrok saat pendaftaran selesai.
     */
    public function up(): void
    {
        Schema::create('pendaftaran_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kode_tiket', 50)->unique();
            $table->unsignedTinyInteger('tahap')->default(1);
            $table->string('ploting_status', 10)->default('belum');
            $table->timestamp('dikirim_pada')->nullable();
            $table->text('ploting_catatan')->nullable();
            $table->foreignId('ploting_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ploting_selesai_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tahap']);
            $table->index('ploting_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_drafts');
    }
};
