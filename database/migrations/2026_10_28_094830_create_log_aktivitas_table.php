<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log audit & aktivitas penting (Blueprint V2 — Admin/Super Admin).
 *
 * Nama pelaku & jabatan disalin saat kejadian agar log tetap terbaca
 * meskipun akun berubah atau dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_pelaku', 200);
            $table->string('role_pelaku', 50)->nullable();
            $table->string('aksi', 50);
            $table->string('objek', 150)->nullable();
            $table->text('detail')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['aksi', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
