<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pembersihan total sisa alur 9 role lama.
     *
     * Semua tabel dan kolom di bawah hanya dipakai oleh alur lama yang sudah
     * diganti alur V2 (kode_tiket + seksi + penyerahan). Data lama ikut
     * dihapus agar tidak ada lagi baris dengan status/enum lama.
     */
    private const TABEL_LEGACY = [
        'tiket_penugasan',
        'catatan_revisi',
        'verifikasi_berkas',
        'lembar_kerja_warkahs',
        'validasi_btel',
        'validasi_suel',
        'alih_media_btel',
        'alih_media_suel',
        'tracking_akses',
    ];

    private const KOLOM_LEGACY_TIKETS = [
        'diserahkan_ke_validator',
        'status_pra_btel',
        'status_pra_suel',
        'status_pembetulan',
        'revisi_ke',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::TABEL_LEGACY as $tabel) {
            Schema::dropIfExists($tabel);
        }

        Schema::table('tikets', function (Blueprint $table) {
            $kolom = array_values(array_filter(
                self::KOLOM_LEGACY_TIKETS,
                fn (string $kolom): bool => Schema::hasColumn('tikets', $kolom)
            ));

            if ($kolom !== []) {
                $table->dropColumn($kolom);
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            if (! Schema::hasColumn('tikets', 'status_pembetulan')) {
                $table->string('status_pembetulan', 10)->default('P0');
            }

            if (! Schema::hasColumn('tikets', 'status_pra_btel')) {
                $table->string('status_pra_btel', 10)->default('menunggu');
            }

            if (! Schema::hasColumn('tikets', 'status_pra_suel')) {
                $table->string('status_pra_suel', 10)->default('menunggu');
            }

            if (! Schema::hasColumn('tikets', 'diserahkan_ke_validator')) {
                $table->boolean('diserahkan_ke_validator')->default(false);
            }

            if (! Schema::hasColumn('tikets', 'revisi_ke')) {
                $table->unsignedTinyInteger('revisi_ke')->default(0);
            }
        });
    }
};
