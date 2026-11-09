<?php

namespace App\Console\Commands;

use App\Models\LogAktivitas;
use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Pembersihan log aktivitas lama mengikuti retensi pada halaman Setting
 * (default 90 hari).
 */
class BersihkanLogAktivitas extends Command
{
    protected $signature = 'loket:bersihkan-log {--hari= : Retensi hari, menimpa Setting}';

    protected $description = 'Hapus log aktivitas yang melewati masa retensi';

    public function handle(): int
    {
        $hari = $this->option('hari') !== null
            ? (int) $this->option('hari')
            : Setting::angka('retensi_log_hari', 90);

        $jumlah = LogAktivitas::where('created_at', '<', now()->subDays($hari))->delete();

        $this->info("{$jumlah} log aktivitas lama dihapus (retensi {$hari} hari).");

        return self::SUCCESS;
    }
}
