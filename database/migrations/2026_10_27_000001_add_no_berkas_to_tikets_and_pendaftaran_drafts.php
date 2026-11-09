<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No. Berkas — nomor resmi dari pusat BPN yang diisi manual oleh Loket
     * Pendaftaran.
     *
     * Sengaja memakai `text` tanpa unique index dan tanpa batas panjang:
     * format bebas (huruf, angka, simbol, spasi) dan nomor yang sama boleh
     * muncul lebih dari sekali. Alur tetap memakai `kode_tiket`.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            if (! Schema::hasColumn('tikets', 'no_berkas')) {
                $table->text('no_berkas')->nullable()->after('kode_tiket');
            }
        });

        Schema::table('pendaftaran_drafts', function (Blueprint $table) {
            if (! Schema::hasColumn('pendaftaran_drafts', 'no_berkas')) {
                $table->text('no_berkas')->nullable()->after('kode_tiket');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropColumn('no_berkas');
        });

        Schema::table('pendaftaran_drafts', function (Blueprint $table) {
            $table->dropColumn('no_berkas');
        });
    }
};
