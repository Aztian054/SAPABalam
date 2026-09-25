<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrade fitur Warkah — Serah Terima & Konfirmasi Pengembalian Berkas BT/SU.
 *
 * Kolom lama (tanggal_diserahkan, tanggal_kembali, jumlah_berkas_dikembalikan)
 * dipertahankan untuk kompatibilitas; kolom baru melengkapi detail serah terima:
 * penerima validator, waktu serah, kondisi berkas BT/SU, catatan kondisi,
 * serta petugas/waktu/kondisi/catatan pengembalian berkas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->foreignId('penerima_validator_id')
                ->nullable()
                ->after('status_pengembalian')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('nama_penerima_validator', 100)->nullable()->after('penerima_validator_id');
            $table->dateTime('waktu_serah')->nullable()->after('nama_penerima_validator');
            $table->string('status_berkas_bt', 20)->nullable()->after('waktu_serah');
            $table->string('status_berkas_su', 20)->nullable()->after('status_berkas_bt');
            $table->text('catatan_kondisi_berkas')->nullable()->after('status_berkas_su');
            $table->string('petugas_pengembali', 100)->nullable()->after('catatan_kondisi_berkas');
            $table->dateTime('waktu_kembali')->nullable()->after('petugas_pengembali');
            $table->string('kondisi_berkas_kembali', 20)->nullable()->after('waktu_kembali');
            $table->text('catatan_pengembalian')->nullable()->after('kondisi_berkas_kembali');
        });
    }

    public function down(): void
    {
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penerima_validator_id');

            $table->dropColumn([
                'nama_penerima_validator',
                'waktu_serah',
                'status_berkas_bt',
                'status_berkas_su',
                'catatan_kondisi_berkas',
                'petugas_pengembali',
                'waktu_kembali',
                'kondisi_berkas_kembali',
                'catatan_pengembalian',
            ]);
        });
    }
};
