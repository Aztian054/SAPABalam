<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 — Loket Penyerahan (Blueprint V2, modul 07).
     *
     * Penyerahan memakai pemegang tiket yang sama (`penanggung_jawab_id`,
     * `diambil_pada`); kolom di bawah menyimpan bukti serah terima saat
     * petugas menekan SELESAI.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            if (! Schema::hasColumn('tikets', 'penerima_nama')) {
                $table->string('penerima_nama', 150)->nullable()->after('alasan_penolakan');
            }

            if (! Schema::hasColumn('tikets', 'penerima_hubungan')) {
                $table->string('penerima_hubungan', 30)->nullable()->after('penerima_nama');
            }

            if (! Schema::hasColumn('tikets', 'penerima_no_identitas')) {
                $table->string('penerima_no_identitas', 50)->nullable()->after('penerima_hubungan');
            }

            if (! Schema::hasColumn('tikets', 'catatan_penyerahan')) {
                $table->text('catatan_penyerahan')->nullable()->after('penerima_no_identitas');
            }

            if (! Schema::hasColumn('tikets', 'diserahkan_pada')) {
                $table->timestamp('diserahkan_pada')->nullable()->after('catatan_penyerahan');
            }

            if (! Schema::hasColumn('tikets', 'diserahkan_oleh_id')) {
                $table->foreignId('diserahkan_oleh_id')->nullable()->after('diserahkan_pada')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropForeign(['diserahkan_oleh_id']);
            $table->dropColumn([
                'penerima_nama',
                'penerima_hubungan',
                'penerima_no_identitas',
                'catatan_penyerahan',
                'diserahkan_pada',
                'diserahkan_oleh_id',
            ]);
        });
    }
};
