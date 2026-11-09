<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Log (Admin & Super Admin): audit aktivitas penting
 * dengan filter tanggal, pelaku, aksi, dan objek.
 */
class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'aksi' => ['nullable', 'string', 'max:50'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $logs = LogAktivitas::query()
            ->tanpaSuperAdmin()
            ->when($filter['cari'] ?? null, function ($query, string $cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('nama_pelaku', 'like', "%{$cari}%")
                        ->orWhere('objek', 'like', "%{$cari}%")
                        ->orWhere('detail', 'like', "%{$cari}%");
                });
            })
            ->when($filter['aksi'] ?? null, fn ($query, string $aksi) => $query->where('aksi', $aksi))
            ->when($filter['dari'] ?? null, fn ($query, string $dari) => $query->whereDate('created_at', '>=', $dari))
            ->when($filter['sampai'] ?? null, fn ($query, string $sampai) => $query->whereDate('created_at', '<=', $sampai))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.log', [
            'logs' => $logs,
            'daftarAksi' => LogAktivitas::query()
                ->tanpaSuperAdmin()
                ->distinct()
                ->orderBy('aksi')
                ->pluck('aksi'),
            'retensiHari' => Setting::angka('retensi_log_hari', 90),
        ]);
    }
}
