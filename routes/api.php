<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistem Loket Pertanahan Elektronik BMN BALAM V2
|--------------------------------------------------------------------------
| Versioned v1. Tidak ada lagi endpoint tracking publik.
*/

Route::prefix('v1')->group(function () {
    // Master Services
    Route::get('/jenis-permohonan', [ApiController::class, 'jenisPermohonan']);

    // Auth (mode stateful Sanctum: kirim header X-XSRF-TOKEN via cookie session)
    Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:6,1');

    // Endpoint terproteksi — guard sanctum (session cookie / bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/dashboard', [ApiAuthController::class, 'dashboard']);
    });
});
