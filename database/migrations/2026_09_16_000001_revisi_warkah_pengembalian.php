<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi stage Warkah + fitur pengembalian berkas BT/SU (pinjaman hardcopy).
 *
 *  - Hapus kolom yang tidak dipakai lagi pada Lembar Kerja Warkah
 *    (status_berkas_dataset, status_public_response) → form di-reduksi ke 3 field.
 *  - Tambah status_pengembalian untuk melacak siklus pinjam berkas BT/SU:
 *      belum_diserahkan → dipinjam (di Validator/Alih Media) → dikembalikan (ke Warkah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->dropColumn(['status_berkas_dataset', 'status_public_response']);
            $table->string('status_pengembalian', 20)
                ->default('belum_diserahkan')
                ->after('keterangan_status');
        });
    }

    public function down(): void
    {
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->dropColumn('status_pengembalian');

            // Kembalikan kolom yang di-drop (posisi mendekati skema awal).
            $table->enum('status_berkas_dataset', ['belum', 'proses', 'selesai'])
                ->default('belum')
                ->after('tanggal_eksekusi');
            $table->enum('status_public_response', ['belum', 'proses', 'selesai'])
                ->default('belum')
                ->after('status_sosialisasi');
        });
    }
};
