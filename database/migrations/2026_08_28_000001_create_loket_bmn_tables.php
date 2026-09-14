<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * V2.0 — 9 peran, 8 tahap, pola Smart Search → Add → Proses → Selesai → Kembali ke DB Admin.
     */
    public function up(): void
    {
        // 1. Jenis Permohonan
        Schema::create('jenis_permohonans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 200);
            $table->enum('kategori', ['umum', 'bmn', 'alih_media'])->default('umum');
            $table->integer('batas_hari_sla')->default(7);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Persyaratan Dokumen
        Schema::create('persyaratan_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_permohonan_id')->constrained('jenis_permohonans')->onDelete('cascade');
            $table->string('nama_dokumen', 200);
            $table->boolean('wajib')->default(true);
            $table->string('keterangan', 255)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // 3. Saran Koreksi (Template Kekurangan)
        Schema::create('saran_koreksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_permohonan_id')->nullable()->constrained('jenis_permohonans')->onDelete('cascade');
            $table->string('nama_dokumen_kurang', 200);
            $table->text('pesan_koreksi');
            $table->string('dasar_hukum', 255)->nullable();
            $table->timestamps();
        });

        // 4. Tikets — Database Tiket Terpadu (milik Admin / DB Admin)
        Schema::create('tikets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket', 50)->unique(); // contoh: K/23/070225/1
            $table->string('nomor_antrian', 50)->nullable();
            $table->integer('nomor_urut_berkas')->nullable();
            $table->enum('status_pembetulan', ['P0', 'P1', 'P2', 'P3', 'P4', 'P5'])->default('P0');
            $table->date('tanggal_masuk');
            $table->foreignId('jenis_permohonan_id')->constrained('jenis_permohonans');
            $table->string('nama_pemohon', 200);
            $table->string('nik_pemohon', 20)->nullable();
            $table->string('no_hp_pemohon', 20);
            $table->string('satuan_kerja', 200)->nullable();
            $table->string('no_hak_sekarang', 100)->nullable();
            $table->text('no_hak_sebelumnya')->nullable();
            $table->string('kelurahan_desa', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->integer('jumlah_bidang')->default(1);
            $table->foreignId('petugas_loket_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_petugas_loket', 100)->nullable();
            $table->string('nomor_telepon', 20)->nullable();
            $table->string('nomor_tiket_ppat', 50)->nullable();
            $table->string('nomor_tiket_non_ppat', 50)->nullable();
            // Status progres BT/SU (paralel hanya pada Validator & Alih Media)
            $table->enum('status_pra_btel', ['menunggu', 'proses', 'selesai'])->default('menunggu');
            $table->enum('status_pra_suel', ['menunggu', 'proses', 'selesai'])->default('menunggu');
            $table->unsignedTinyInteger('revisi_ke')->default(0);
            $table->enum('status', [
                'diterima',
                'verifikasi',
                'warkah',
                'validasi_btel',
                'validasi_suel',
                'alih_media_btel',
                'alih_media_suel',
                'selesai',
                'dikembalikan',
                'batal',
            ])->default('diterima');
            $table->boolean('status_sps')->default(false);
            $table->date('tanggal_sps')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_target_selesai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Bidang Tanah
        Schema::create('bidang_tanahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->string('nib', 50)->nullable();
            $table->string('no_sertifikat_lama', 100)->nullable();
            $table->string('no_sertifikat_elektronik', 100)->nullable();
            $table->enum('jenis_hak', ['HM', 'HGB', 'HGU', 'HP', 'HPL'])->nullable();
            $table->string('nama_pemegang_hak', 200)->nullable();
            $table->decimal('luas_m2', 12, 2)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->enum('status_plotting', ['belum', 'sudah'])->default('belum');
            $table->integer('urutan')->default(1);
            $table->timestamps();
        });

        // 6. Verifikasi Berkas
        Schema::create('verifikasi_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status_pembetulan', 10)->nullable();
            $table->integer('iterasi')->default(1);
            $table->date('tanggal_diterima')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['proses', 'lengkap', 'perbaikan', 'konsul', 'batal'])->default('proses');
            $table->text('catatan')->nullable();
            $table->json('dokumen_kurang')->nullable();
            $table->timestamps();
        });

        // 7. Lembar Kerja Warkah
        Schema::create('lembar_kerja_warkahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('tanggal_eksekusi')->nullable();
            $table->enum('status_berkas_dataset', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_data_sertipikat_bt', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_data_sertipikat_su', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_sosialisasi', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_public_response', ['belum', 'proses', 'selesai'])->default('belum');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 8a. Lembar Kerja Validasi BT (Pra-Buku Tanah Elektronik)
        Schema::create('validasi_btel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang_tanahs')->nullOnDelete();
            $table->foreignId('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('kesesuaian_nama', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('kesesuaian_luas', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('kesesuaian_nib', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('status_validasi', ['proses', 'lulus', 'perlu_koreksi', 'ditolak'])->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 8b. Lembar Kerja Validasi SU (Pra-Surat Ukur Elektronik)
        Schema::create('validasi_suel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang_tanahs')->nullOnDelete();
            $table->foreignId('validator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('kesesuaian_nama', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('kesesuaian_luas', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('cocok_letak', ['sesuai', 'tidak_sesuai'])->nullable();
            $table->enum('status_validasi', ['proses', 'lulus', 'perlu_koreksi', 'ditolak'])->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 9a. Lembar Kerja Alih Media BT (Pra-Buku Tanah Elektronik)
        Schema::create('alih_media_btel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang_tanahs')->nullOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status_scan_buku_tanah', ['belum', 'sudah', 'kualitas_buruk'])->default('belum');
            $table->enum('status_upload_kkp', ['belum', 'sudah'])->default('belum');
            $table->enum('status_ttd_elektronik', ['belum', 'sudah'])->default('belum');
            $table->date('tanggal_terbit_sertifikat_el')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 9b. Lembar Kerja Alih Media SU (Pra-Surat Ukur Elektronik)
        Schema::create('alih_media_suel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('bidang_id')->nullable()->constrained('bidang_tanahs')->nullOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status_scan_surat_ukur', ['belum', 'sudah', 'kualitas_buruk'])->default('belum');
            $table->enum('status_upload_kkp', ['belum', 'sudah'])->default('belum');
            $table->enum('status_ttd_elektronik', ['belum', 'sudah'])->default('belum');
            $table->date('tanggal_terbit_sertifikat_el')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 10. Tiket Penugasan — inti pola Smart Search → Add → Proses → Selesai
        Schema::create('tiket_penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('stage', ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel', 'alih_media_btel', 'alih_media_suel']);
            $table->enum('status', ['proses', 'selesai', 'dikembalikan', 'batal'])->default('proses');
            $table->timestamp('tanggal_add')->useCurrent();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['tiket_id', 'stage']);
        });

        // 11. Catatan Revisi — alur pengembalian antar tahap
        Schema::create('catatan_revisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('pengirim_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('penerima_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('dari_stage', ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel', 'alih_media_btel', 'alih_media_suel', 'admin']);
            $table->enum('ke_stage', ['loket', 'verifikasi', 'warkah', 'admin'])->nullable();
            $table->unsignedTinyInteger('revisi_ke')->default(1);
            $table->text('isi_revisi');
            $table->boolean('sudah_diproses')->default(false);
            $table->timestamp('tanggal_masuk')->useCurrent();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        // 12. Arsip Folder — pengelolaan arsip fisik oleh Admin
        Schema::create('arsip_folder', function (Blueprint $table) {
            $table->id();
            $table->string('nama_folder', 200);
            $table->string('jenis_dokumen', 100)->nullable();
            $table->string('lokasi_fisik', 200)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 13. Arsip Tiket — arsip tiket terselesaikan ke dalam folder
        Schema::create('arsip_tiket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('arsip_folder')->nullOnDelete();
            $table->string('nama_arsip', 200)->nullable();
            $table->string('tipe_dokumen', 100)->nullable();
            $table->date('tanggal_arsip')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // 14. Riwayat Status (Audit Trail)
        Schema::create('riwayat_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onDelete('cascade');
            $table->string('stage_dari', 50);
            $table->string('stage_ke', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_statuses');
        Schema::dropIfExists('arsip_tiket');
        Schema::dropIfExists('arsip_folder');
        Schema::dropIfExists('catatan_revisi');
        Schema::dropIfExists('tiket_penugasan');
        Schema::dropIfExists('alih_media_suel');
        Schema::dropIfExists('alih_media_btel');
        Schema::dropIfExists('validasi_suel');
        Schema::dropIfExists('validasi_btel');
        Schema::dropIfExists('lembar_kerja_warkahs');
        Schema::dropIfExists('verifikasi_berkas');
        Schema::dropIfExists('bidang_tanahs');
        Schema::dropIfExists('tikets');
        Schema::dropIfExists('saran_koreksis');
        Schema::dropIfExists('persyaratan_dokumens');
        Schema::dropIfExists('jenis_permohonans');
    }
};
