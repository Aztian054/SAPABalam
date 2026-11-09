<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Services\LogAktivitasService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Backup basis data — hanya Super Admin.
 *
 * Dump dibuat aplikasi (bukan binary eksternal) agar portabel, lalu
 * diunduh sebagai `loket-{yyyyMMdd-HHmm}.sql`. Setiap unduhan dicatat
 * di Log; pemulihan dilakukan manual oleh operator.
 */
class BackupController extends Controller
{
    public function __construct(
        private readonly BackupService $backup,
        private readonly LogAktivitasService $log,
    ) {}

    public function index(): View
    {
        return view('super_admin.backup', [
            'driver' => $this->backup->driver(),
            'jumlahTabel' => count($this->backup->daftarTabel()),
            'namaFile' => $this->backup->namaFile(),
        ]);
    }

    public function unduh(): StreamedResponse
    {
        $namaFile = $this->backup->namaFile();

        $this->log->catat('UNDUH BACKUP', $namaFile, 'Driver: '.$this->backup->driver());

        return response()->streamDownload(function (): void {
            $this->backup->tulisKe(fn (string $baris) => print $baris);
        }, $namaFile, [
            'Content-Type' => 'application/sql',
        ]);
    }
}
