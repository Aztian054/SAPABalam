<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\User;
use Throwable;

/**
 * Pencatat log audit & aktivitas penting.
 *
 * Kegagalan menulis log tidak boleh menggagalkan aksi bisnis, sehingga
 * exception dilaporkan lalu diabaikan.
 *
 * Aktivitas Super Admin sengaja tidak pernah ditulis: keberadaannya tidak
 * boleh terdeteksi dari halaman Log, termasuk saat memakai mode lihat/coba.
 */
class LogAktivitasService
{
    public function catat(
        string $aksi,
        ?string $objek = null,
        ?string $detail = null,
        ?User $pelaku = null,
    ): void {
        $pelaku ??= auth()->user();

        if ($pelaku?->isSuperAdmin()) {
            return;
        }

        try {
            LogAktivitas::create([
                'user_id' => $pelaku?->id,
                'nama_pelaku' => $pelaku?->name ?? 'Sistem',
                'role_pelaku' => $pelaku?->role,
                'jabatan_pelaku' => $pelaku?->jabatan,
                'aksi' => $aksi,
                'objek' => $objek,
                'detail' => $detail,
                'ip' => request()->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
