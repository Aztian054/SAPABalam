<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistem Loket Pertanahan Elektronik BMN BALAM
|--------------------------------------------------------------------------
| Versioned v1. SPA React (Fase 1+) memakai mode stateful Sanctum:
| cookie session + CSRF (XSRF-TOKEN) untuk login/logout/me/dashboard.
*/

Route::prefix('v1')->group(function () {
    // Tracking publik & QR Validation (kode tiket bisa mengandung `/`)
    Route::get('/tracking/{no_tiket}', [ApiController::class, 'tracking'])->where('no_tiket', '.*');

    // Live Dashboard Stats (publik)
    Route::get('/stats', [ApiController::class, 'stats']);

    // Master Services
    Route::get('/jenis-permohonan', [ApiController::class, 'jenisPermohonan']);

    // Auth (SPA Sanctum: kirim header X-XSRF-TOKEN via cookie session)
    Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:6,1');

    // Endpoint terproteksi — guard sanctum (session cookie utk SPA / bearer utk API client)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/dashboard', [ApiAuthController::class, 'dashboard']);
    });
});
