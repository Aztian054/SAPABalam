<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fondasi V2 — status tiket baru + kolom pendukung alur.
     *
     * `status` diubah dari enum ke string agar nilai `di_database`, `diproses`,
     * `ditolak`, dan `selesai` dapat dipakai tanpa mengubah tipe kolom.
     */
    public function up(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->string('status', 40)->default('di_database')->change();

            if (! Schema::hasColumn('tikets', 'penanggung_jawab_id')) {
                $table->foreignId('penanggung_jawab_id')->nullable()->after('status')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('tikets', 'diambil_pada')) {
                $table->timestamp('diambil_pada')->nullable()->after('penanggung_jawab_id');
            }

            if (! Schema::hasColumn('tikets', 'dilepas_admin_at')) {
                $table->timestamp('dilepas_admin_at')->nullable()->after('diambil_pada');
            }

            if (! Schema::hasColumn('tikets', 'ploting_status')) {
                $table->string('ploting_status', 10)->default('sudah')->after('dilepas_admin_at');
                $table->text('ploting_catatan')->nullable()->after('ploting_status');
                $table->timestamp('ploting_selesai_at')->nullable()->after('ploting_catatan');
                $table->foreignId('ploting_oleh_id')->nullable()->after('ploting_selesai_at')
                    ->constrained('users')->nullOnDelete();
            }

            $table->index('status');
            $table->index('tanggal_masuk');
            $table->index(['status', 'tanggal_masuk']);
        });
    }

    public function down(): void
    {
        Schema::table('tikets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_masuk']);
            $table->dropIndex(['status', 'tanggal_masuk']);
            $table->dropConstrainedForeignId('ploting_oleh_id');
            $table->dropColumn(['ploting_selesai_at', 'ploting_catatan', 'ploting_status']);
            $table->dropConstrainedForeignId('penanggung_jawab_id');
            $table->dropColumn(['dilepas_admin_at', 'diambil_pada']);
        });
    }
};
