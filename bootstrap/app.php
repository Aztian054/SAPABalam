<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aktifkan mode stateful Sanctum: SPA React (cookie session) mengakses
        // route /api/v1 dan divalidasi lewat session, bukan bearer token.
        $middleware->statefulApi();

        // Akses via ngrok/tunnel https: percayai header proxy (X-Forwarded-Proto dll.)
        // agar Laravel membangkitkan URL https untuk form & redirect (mistmatch
        // http/https saat login melalui tunnel). Skope ketat untuk production.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 419 (CSRF token mismatch): jangan tampilkan layar mati — arahkan kembali
        // dengan pesan agar pengguna cukup mengulang, CSRF tetap aktif.
        //
        // Catatan: prepareException() mengubah TokenMismatchException → HttpException(419),
        // jadi renderViaCallbacks menerima HttpException, bukan TokenMismatchException.
        // Kita filter berdasarkan status code 419.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $pesan = 'Sesi Anda telah berakhir atau token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $pesan], 419);
            }

            return redirect()->back()
                ->withInput($request->except('_token', '_method', 'password', 'password_confirmation', 'password_lama', 'password_baru'))
                ->with('error', $pesan);
        });
    })->create();
