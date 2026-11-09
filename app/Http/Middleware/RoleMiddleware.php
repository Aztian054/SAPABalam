<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        // Super Admin (akun bayangan/developer) tetap memiliki akses penuh.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        /*
         * Admin hanya monitoring + manajemen.
         * Admin boleh membuka halaman (GET) dan seluruh aksi di modul admin,
         * notifikasi, serta profil. Aksi operasional (POST/PUT/DELETE) di modul
         * pendaftaran, ploting, seksi, penyerahan, pengaduan, warkah, dan
         * pengaduan-unit ditolak agar Admin tidak dapat memproses berkas.
         */
        if ($user->role === 'admin') {
            $boleh = $request->isMethod('GET')
                || $request->routeIs('admin.*')
                || $request->routeIs('notifikasi.*')
                || $request->routeIs('profil.*')
                || $request->routeIs('logout');

            abort_unless($boleh, 403, 'Admin hanya memiliki akses monitoring dan manajemen.');

            return $next($request);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
