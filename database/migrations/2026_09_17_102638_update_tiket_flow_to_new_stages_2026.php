<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update alur flow LOKET 2026 - versi 17 September 2026
     * Perubahan: Verifikasi HARUS selesai sebelum Warkah mulai (tidak paralel lagi)
     * Tambah status "perbaikan" untuk workflow revisi yang lebih jelas
     */
    public function up(): void
    {
        // Untuk MySQL: tambah status 'perbaikan' ke enum status tikets
        // Untuk SQLite: skip karena SQLite tidak mendukung ALTER TABLE MODIFY COLUMN dengan ENUM
        // Status 'perbaikan' akan tetap digunakan oleh aplikasi, hanya validasi database yang berbeda
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE tikets MODIFY COLUMN status ENUM(
                'diterima',
                'verifikasi',
                'warkah',
                'validasi_btel',
                'validasi_suel',
                'alih_media_btel',
                'alih_media_suel',
                'selesai',
                'dikembalikan',
                'perbaikan',
                'batal'
            ) DEFAULT 'diterima'");
        } else {
            // Untuk SQLite dan driver lain, kita hanya mencatat bahwa status 'perbaikan' valid
            // Validasi akan dilakukan di level aplikasi (Tiket model dan TiketFlowService)
        }

        // 2. Migrasi data: ubah status 'dikembalikan' yang lama jadi 'perbaikan'
        // (opsional - hanya jika ada data existing)
        // DB::table('tikets')
        //     ->where('status', 'dikembalikan')
        //     ->update(['status' => 'perbaikan']);

        // 3. Tambah kolom untuk tracking fase Warkah (opsional - sudah ada di LembarKerjaWarkah)
        // Kita gunakan status_pengembalian yang sudah ada di lembar_kerja_warkahs
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum lama (tanpa 'perbaikan')
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE tikets MODIFY COLUMN status ENUM(
                'diterima',
                'verifikasi',
                'warkah',
                'validasi_btel',
                'validasi_suel',
                'alih_media_btel',
                'alih_media_suel',
                'selesai',
                'dikembalikan',
                'batal'
            ) DEFAULT 'diterima'");
        }
    }
};
