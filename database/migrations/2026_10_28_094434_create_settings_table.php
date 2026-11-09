<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan operasional (Setting) — batas hari & format penomoran.
 *
 * Nilai awal diambil dari config/loket.php agar perilaku aplikasi tidak
 * berubah sampai Admin menyimpan perubahan di halaman Setting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $this->tanamNilaiDefault();
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }

    private function tanamNilaiDefault(): void
    {
        $now = now();
        $baris = [];

        foreach (Setting::DEFNISI as $key => $definisi) {
            $baris[] = [
                'key' => $key,
                'value' => (string) config('loket.'.$key, $definisi['default']),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($baris !== []) {
            DB::table('settings')->insert($baris);
        }
    }
};
