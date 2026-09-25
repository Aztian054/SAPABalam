<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Form Pendaftaran — admin mengelola isi dropdown/form
     * tanpa perlu ubahan kode (mandiri tanpa developer).
     *
     *  - Tabel baru: kategori_permohonans, jenis_haks
     *  - Ubah enum kategori/jenis_hak → string agar kode bisa ditambah dari UI
     */
    public function up(): void
    {
        // 1. Kategori Permohonan (umum / bmn / alih_media — bisa ditambah)
        Schema::create('kategori_permohonans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Jenis Hak (HM / HGB / HGU / HP / HPL — bisa ditambah)
        Schema::create('jenis_haks', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Ubah enum kategori → string (data umum/bmn/alih_media tetap utuh)
        Schema::table('jenis_permohonans', function (Blueprint $table) {
            $table->string('kategori', 20)->default('umum')->change();
        });

        // 4. Ubah enum jenis_hak → string nullable (data HM/HGB/HGU/HP/HPL tetap utuh)
        Schema::table('bidang_tanahs', function (Blueprint $table) {
            $table->string('jenis_hak', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bidang_tanahs', function (Blueprint $table) {
            $table->enum('jenis_hak', ['HM', 'HGB', 'HGU', 'HP', 'HPL'])->nullable()->change();
        });

        Schema::table('jenis_permohonans', function (Blueprint $table) {
            $table->enum('kategori', ['umum', 'bmn', 'alih_media'])->default('umum')->change();
        });

        Schema::dropIfExists('jenis_haks');
        Schema::dropIfExists('kategori_permohonans');
    }
};
