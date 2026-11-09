<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mode "Lihat/Coba Sebagai Akun" (impersonate read-only).
 *
 * Sesi asli Super Admin tidak pernah ditimpa: middleware ini hanya
 * mengganti user pada guard untuk request berjalan. Setiap request
 * berikutnya membaca `impersonate_id` dari sesi dan mengganti lagi.
 */
class ImpersonateMiddleware
{
    public const KUNCI_TARGET = 'impersonate_id';

    public const KUNCI_ASAL = 'impersonate_original';

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        if (! $session->has(self::KUNCI_TARGET)) {
            return $next($request);
        }

        $target = User::find($session->get(self::KUNCI_TARGET));
        $asal = User::find($session->get(self::KUNCI_ASAL));

        // Sesi impersonate tidak valid lagi: bersihkan tanpa mengganggu sesi asli bila masih sah.
        if (! $target || ! $target->is_active || $target->isSuperAdmin() || ! $asal || ! $asal->isSuperAdmin()) {
            $session->forget([self::KUNCI_TARGET, self::KUNCI_ASAL]);

            if (! $asal || ! $asal->isSuperAdmin()) {
                Auth::logout();
                $session->invalidate();
                $session->regenerateToken();
            }

            return redirect()->route('dashboard')
                ->with('error', 'Sesi lihat/coba sebagai akun telah berakhir.');
        }

        // Jabatan Super Admin tidak boleh di-impersonate.
        Auth::setUser($target);

        // Mode baca-saja: semua aksi yang mengubah data diblokir.
        if (! $request->isMethodSafe()) {
            abort(403, 'Mode lihat/coba sebagai akun bersifat baca-saja. Kembali ke Super Admin untuk melakukan perubahan.');
        }

        return $next($request);
    }
}
