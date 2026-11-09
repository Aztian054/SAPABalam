<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda data demo (Blueprint V2 — Super Admin).
 *
 * Reset demo hanya menghapus baris dengan `is_demo = true`, sehingga data
 * operasional asli tidak pernah tersentuh.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tabel = ['users', 'tikets', 'pengaduans', 'warkah_permintaans'];

    public function up(): void
    {
        foreach ($this->tabel as $tabel) {
            if (Schema::hasColumn($tabel, 'is_demo')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table): void {
                $table->boolean('is_demo')->default(false)->after('id');
                $table->index('is_demo');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $tabel) {
            if (! Schema::hasColumn($tabel, 'is_demo')) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table): void {
                $table->dropIndex(['is_demo']);
                $table->dropColumn('is_demo');
            });
        }
    }
};
