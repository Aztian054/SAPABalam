<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 — Notifikasi in-app (Blueprint V2, modul 16).
     *
     * Tabel terpisah dari `notifications` bawaan Laravel agar tidak
     * mencampur kanal (email/SMS sudah tidak dipakai).
     */
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipe', 50);
            $table->string('judul', 120);
            $table->text('pesan')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('objek_tipe', 30)->nullable();
            $table->unsignedBigInteger('objek_id')->nullable();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dibaca_pada']);
            $table->index(['objek_tipe', 'objek_id']);
            $table->index('tipe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
