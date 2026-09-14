<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlihMediaBtelController;
use App\Http\Controllers\AlihMediaSuelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PemimpinController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\ValidatorBtelController;
use App\Http\Controllers\ValidatorSuelController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\WarkahController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
| Web Routes — Sistem Loket Pertanahan Elektronik BMN BALAM V2.0
| 9 peran: admin, pemimpin, loket, verifikator, warkah,
|          validator_btel, validator_suel, alih_media_btel, alih_media_suel
| Pola pull-based: Smart Search → Add → Proses → Selesai → Kembali ke DB Admin.
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('tracking.index');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public tracking
Route::get('/tracking', [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/tracking/{kode}', [TrackingController::class, 'show'])->name('tracking.show')->where('kode', '.*');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Admin: DB Tiket Terpadu, User, Arsip ──
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/selesai', [AdminController::class, 'selesai'])->name('selesai');
        Route::post('/selesai/arsipkan-massal', [AdminController::class, 'arsipkanMassal'])->name('selesai.arsipkan-massal');
        Route::get('/revisi', [AdminController::class, 'revisi'])->name('revisi');
        Route::post('/revisi/{id}/hapus', [AdminController::class, 'revisiHapus'])->name('revisi.hapus');
        Route::get('/tambah-tiket', [AdminController::class, 'createTiket'])->name('create_tiket');
        Route::post('/tambah-tiket', [AdminController::class, 'storeTiket'])->name('store_tiket');
        Route::get('/tiket/{id}', [AdminController::class, 'show'])->name('show');
        Route::post('/tiket/{id}/arsipkan', [AdminController::class, 'arsipkan'])->name('arsipkan');
        Route::get('/arsip', [AdminController::class, 'arsipIndex'])->name('arsip');
        Route::post('/arsip', [AdminController::class, 'arsipStore'])->name('arsip.store');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'usersStore'])->name('users.store');
        Route::post('/users/{id}/toggle', [AdminController::class, 'usersToggle'])->name('users.toggle');
    });

    // ── Pemimpin: Monitoring ──
    Route::middleware(['role:pemimpin'])->prefix('pemimpin')->name('pemimpin.')->group(function () {
        Route::get('/', [PemimpinController::class, 'index'])->name('index');
        Route::get('/tiket/{id}', [PemimpinController::class, 'show'])->name('show');
    });

    // ── Stage 1: Loket ──
    Route::middleware(['role:loket'])->prefix('loket')->name('loket.')->group(function () {
        Route::get('/', [LoketController::class, 'index'])->name('index');
        Route::get('/create', [LoketController::class, 'create'])->name('create');
        Route::post('/', [LoketController::class, 'store'])->name('store');
        Route::get('/{id}', [LoketController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LoketController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LoketController::class, 'update'])->name('update');
        Route::post('/{id}/resubmit', [LoketController::class, 'resubmit'])->name('resubmit');
        Route::get('/{id}/print-receipt', [LoketController::class, 'printReceipt'])->name('printReceipt');
        Route::get('/{id}/print-checklist', [LoketController::class, 'printChecklist'])->name('printChecklist');
    });

    // ── Stage 2: Verifikator ──
    Route::middleware(['role:verifikator'])->prefix('verifikator')->name('verifikator.')->group(function () {
        Route::get('/', [VerifikatorController::class, 'index'])->name('index');
        Route::get('/search', [VerifikatorController::class, 'search'])->name('search');
        Route::post('/add/{id}', [VerifikatorController::class, 'add'])->name('add');
        Route::get('/{id}', [VerifikatorController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [VerifikatorController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [VerifikatorController::class, 'simpanHasil'])->name('simpan');
        Route::post('/{id}/selesai', [VerifikatorController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [VerifikatorController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [VerifikatorController::class, 'lepas'])->name('lepas');
    });

    // ── Stage 3: Warkah ──
    Route::middleware(['role:warkah'])->prefix('warkah')->name('warkah.')->group(function () {
        Route::get('/', [WarkahController::class, 'index'])->name('index');
        Route::get('/search', [WarkahController::class, 'search'])->name('search');
        Route::post('/add/{id}', [WarkahController::class, 'add'])->name('add');
        Route::get('/{id}', [WarkahController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [WarkahController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [WarkahController::class, 'simpanProgres'])->name('simpan');
        Route::post('/{id}/selesai', [WarkahController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [WarkahController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [WarkahController::class, 'lepas'])->name('lepas');
    });

    // ── Stage 4a: Validator BT ──
    Route::middleware(['role:validator_btel'])->prefix('validator-bt')->name('validator_btel.')->group(function () {
        Route::get('/', [ValidatorBtelController::class, 'index'])->name('index');
        Route::get('/search', [ValidatorBtelController::class, 'search'])->name('search');
        Route::post('/add/{id}', [ValidatorBtelController::class, 'add'])->name('add');
        Route::get('/{id}', [ValidatorBtelController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [ValidatorBtelController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [ValidatorBtelController::class, 'simpanHasil'])->name('simpan');
        Route::post('/{id}/selesai', [ValidatorBtelController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [ValidatorBtelController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [ValidatorBtelController::class, 'lepas'])->name('lepas');
    });

    // ── Stage 4b: Validator SU ──
    Route::middleware(['role:validator_suel'])->prefix('validator-su')->name('validator_suel.')->group(function () {
        Route::get('/', [ValidatorSuelController::class, 'index'])->name('index');
        Route::get('/search', [ValidatorSuelController::class, 'search'])->name('search');
        Route::post('/add/{id}', [ValidatorSuelController::class, 'add'])->name('add');
        Route::get('/{id}', [ValidatorSuelController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [ValidatorSuelController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [ValidatorSuelController::class, 'simpanHasil'])->name('simpan');
        Route::post('/{id}/selesai', [ValidatorSuelController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [ValidatorSuelController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [ValidatorSuelController::class, 'lepas'])->name('lepas');
    });

    // ── Stage 5a: Alih Media BT ──
    Route::middleware(['role:alih_media_btel'])->prefix('alih-media-bt')->name('alih_media_btel.')->group(function () {
        Route::get('/', [AlihMediaBtelController::class, 'index'])->name('index');
        Route::get('/search', [AlihMediaBtelController::class, 'search'])->name('search');
        Route::post('/add/{id}', [AlihMediaBtelController::class, 'add'])->name('add');
        Route::get('/{id}', [AlihMediaBtelController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [AlihMediaBtelController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [AlihMediaBtelController::class, 'simpanHasil'])->name('simpan');
        Route::post('/{id}/selesai', [AlihMediaBtelController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [AlihMediaBtelController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [AlihMediaBtelController::class, 'lepas'])->name('lepas');
    });

    // ── Stage 5b: Alih Media SU ──
    Route::middleware(['role:alih_media_suel'])->prefix('alih-media-su')->name('alih_media_suel.')->group(function () {
        Route::get('/', [AlihMediaSuelController::class, 'index'])->name('index');
        Route::get('/search', [AlihMediaSuelController::class, 'search'])->name('search');
        Route::post('/add/{id}', [AlihMediaSuelController::class, 'add'])->name('add');
        Route::get('/{id}', [AlihMediaSuelController::class, 'show'])->name('show');
        Route::get('/{id}/print-perbaikan', [AlihMediaSuelController::class, 'printPerbaikan'])->name('print-perbaikan');
        Route::post('/{id}/simpan', [AlihMediaSuelController::class, 'simpanHasil'])->name('simpan');
        Route::post('/{id}/selesai', [AlihMediaSuelController::class, 'selesai'])->name('selesai');
        Route::post('/{id}/revisi', [AlihMediaSuelController::class, 'revisi'])->name('revisi');
        Route::post('/{id}/lepas', [AlihMediaSuelController::class, 'lepas'])->name('lepas');
    });

    // ── Laporan ──
    Route::middleware(['role:admin,pemimpin,loket,verifikator,warkah,validator_btel,validator_suel,alih_media_btel,alih_media_suel'])
        ->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/print', [ReportController::class, 'print'])->name('print');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
        });
});
