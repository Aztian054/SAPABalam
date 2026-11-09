<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preferensi notifikasi in-app per pengguna (Blueprint V2, modul 16).
     *
     * Tidak adanya baris berarti tipe tersebut aktif (default). Baris hanya
     * dibuat saat pengguna menyimpan preferensi, sehingga akun lama tidak
     * perlu di-backfill.
     */
    public function up(): void
    {
        Schema::create('notifikasi_preferensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipe', 50);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_preferensis');
    }
};
