<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomor tiket kini dibuat otomatis oleh sistem dan tidak dapat diubah,
     * sehingga tabel riwayat perubahan nomor tidak dipakai lagi.
     */
    public function up(): void
    {
        Schema::dropIfExists('tiket_nomor_riwayat');
    }

    public function down(): void
    {
        // Riwayat perubahan nomor manual sudah tidak didukung.
    }
};
