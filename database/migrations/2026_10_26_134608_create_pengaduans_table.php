<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 — modul pengaduan (Blueprint V2, modul 08–09).
     *
     * Nomor `LP` + ddmmyyyy + urut 3 digit. Status: terkirim → diterima →
     * selesai, dengan cabang dikembalikan (salah tujuan).
     */
    public function up(): void
    {
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengaduan', 30)->unique();
            $table->date('tanggal_buat');
            $table->foreignId('pembuat_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nama_pengadu', 150)->nullable();
            $table->string('nik_pengadu', 20)->nullable();
            $table->string('no_hp_pengadu', 30)->nullable();
            $table->string('alamat_pengadu', 255)->nullable();
            $table->boolean('is_anonim')->default(false);

            $table->string('jenis', 30);
            $table->foreignId('tiket_id')->nullable()->constrained('tikets')->nullOnDelete();
            $table->text('uraian');
            $table->string('seksi_tujuan', 5);
            $table->string('status', 20)->default('terkirim');
            $table->text('catatan_terakhir')->nullable();

            $table->foreignId('diterima_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diterima_pada')->nullable();
            $table->foreignId('selesai_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamp('dikembalikan_pada')->nullable();

            $table->timestamps();

            $table->index(['seksi_tujuan', 'status']);
            $table->index('tanggal_buat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};
