<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use Illuminate\Console\Command;

/**
 * Pembersihan notifikasi lama (Blueprint V2, modul 16 §7).
 *
 * Hanya notifikasi yang sudah dibaca dan melewati masa retensi yang dihapus;
 * notifikasi belum dibaca selalu dipertahankan.
 */
class BersihkanNotifikasi extends Command
{
    protected $signature = 'loket:bersihkan-notifikasi {--hari= : Retensi hari, menimpa config}';

    protected $description = 'Hapus notifikasi in-app yang sudah dibaca dan melewati masa retensi';

    public function handle(): int
    {
        $hari = $this->option('hari') !== null
            ? (int) $this->option('hari')
            : (int) config('loket.retensi_notifikasi_hari', 90);

        $jumlah = Notifikasi::whereNotNull('dibaca_pada')
            ->where('dibaca_pada', '<', now()->subDays($hari))
            ->delete();

        $this->info("{$jumlah} notifikasi lama dihapus.");

        return self::SUCCESS;
    }
}
