<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penyesuaian non-destruktif terhadap skema agar selaras dengan
 * "ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL)":
 *  - status_pembetulan pada tikets menjadi string agar revisi P1..Pn TIDAK terbatas;
 *  - users mendapatkan nip & no_hp (daftar kolom monitoring rekap per dokumen);
 *  - lembar_kerja_warkahs mendapatkan kolom keluaran Warkah sesuai dokumen.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Revisi P1..Pn tanpa batas → pindah dari enum ['P0'..'P5'] ke string.
        Schema::table('tikets', function (Blueprint $table) {
            $table->string('status_pembetulan', 10)->default('P0')->change();
        });

        // NIP & No HP petugas (kolom rekap monitoring).
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('username');
            $table->string('no_hp', 20)->nullable()->after('email');
        });

        // Keluaran Lembar Kerja Warkah sesuai dokumen (status sertipikat,
        // dokumen BT/SU, tanggal serah/kembali, perhitungan gabungan).
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->string('status_sertipikat', 30)->nullable()->after('status_public_response');
            $table->string('status_dokumen_bt', 30)->nullable()->after('status_sertipikat');
            $table->string('status_dokumen_su', 30)->nullable()->after('status_dokumen_bt');
            $table->date('tanggal_diserahkan')->nullable()->after('status_dokumen_su');
            $table->date('tanggal_kembali')->nullable()->after('tanggal_diserahkan');
            $table->integer('jumlah_berkas')->nullable()->after('tanggal_kembali');
            $table->integer('jumlah_halaman')->nullable()->after('jumlah_berkas');
            $table->boolean('gabungan')->default(false)->after('jumlah_halaman');
            $table->integer('jumlah_berkas_dikembalikan')->nullable()->after('gabungan');
            $table->boolean('pengembalian_sementara')->default(false)->after('jumlah_berkas_dikembalikan');
            $table->text('keterangan_status')->nullable()->after('pengembalian_sementara');
        });
    }

    public function down(): void
    {
        Schema::table('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->dropColumn([
                'status_sertipikat',
                'status_dokumen_bt',
                'status_dokumen_su',
                'tanggal_diserahkan',
                'tanggal_kembali',
                'jumlah_berkas',
                'jumlah_halaman',
                'gabungan',
                'jumlah_berkas_dikembalikan',
                'pengembalian_sementara',
                'keterangan_status',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'no_hp']);
        });

        // Kembalikan enum P0..P5 (catatan: data P>5 akan dipotong bila di-revert).
        Schema::table('tikets', function (Blueprint $table) {
            $table->string('status_pembetulan', 10)->default('P0')->change();
        });
    }
};
