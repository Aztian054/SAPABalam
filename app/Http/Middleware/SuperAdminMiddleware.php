<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route tertentu hanya untuk Super Admin.
 *
 * Berbeda dari `role:admin` (yang juga meloloskan Admin), middleware ini
 * menolak Admin biasa agar area developer tetap tertutup.
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isSuperAdmin()) {
            abort(403, 'Halaman ini hanya dapat diakses Super Admin.');
        }

        return $next($request);
    }
}
