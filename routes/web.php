<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\FormPendaftaranController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\KakanController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PengaduanUnitController;
use App\Http\Controllers\PenyerahanController;
use App\Http\Controllers\PlotingController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SeksiController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WarkahPermintaanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
| Web Routes — Sistem Loket Pertanahan Elektronik BMN BALAM V2.0
| 12 role V2: super_admin, admin, loket_pendaftaran, loket_ploting,
| petugas_warkah, staf_seksi, kasi_seksi, kasi_unit, kasubbag_tu,
| loket_penyerahan, loket_pengaduan, kakan.
| Alur: Loket Pendaftaran → Seksi 1/2 → Loket Penyerahan → Selesai.
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profil mandiri: biodata, username, password ──
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::patch('/profil/username', [ProfilController::class, 'updateUsername'])->name('profil.username.update');
    Route::patch('/profil/password', [ProfilController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('profil.password.update');

    Route::post('/profil/foto', [ProfilController::class, 'uploadFoto'])->name('profil.foto.update');

    // ── Admin: DB Tiket Terpadu, User, Arsip ──
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/selesai', [AdminController::class, 'selesai'])->name('selesai');
        Route::get('/tiket/{id}', [AdminController::class, 'show'])->name('show');
        Route::get('/arsip', [AdminController::class, 'arsipIndex'])->name('arsip');
        Route::post('/arsip', [AdminController::class, 'arsipStore'])->name('arsip.store');
        Route::get('/users', [AccountController::class, 'index'])->name('users');
        Route::post('/users', [AccountController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/toggle', [AccountController::class, 'toggle'])->name('users.toggle');
        Route::get('/users/{id}/edit', [AccountController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [AccountController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/hapus', [AccountController::class, 'destroy'])->name('users.hapus');

        // Setting operasional (batas hari, penomoran, retensi log)
        Route::get('/setting', [SettingController::class, 'edit'])->name('setting');
        Route::put('/setting', [SettingController::class, 'update'])->name('setting.update');

        // Log audit & aktivitas
        Route::get('/log', [LogAktivitasController::class, 'index'])->name('log');

        // ── Kelola Form Pendaftaran (master konten form — mandiri tanpa developer) ──
        Route::get('/form-pendaftaran', [FormPendaftaranController::class, 'index'])->name('form-pendaftaran');

        // Jenis Permohonan
        Route::post('/form-pendaftaran/jenis-permohonan', [FormPendaftaranController::class, 'jenisPermohonanStore'])->name('form-pendaftaran.jenis-permohonan.store');
        Route::get('/form-pendaftaran/jenis-permohonan/{id}/edit', [FormPendaftaranController::class, 'jenisPermohonanEdit'])->name('form-pendaftaran.jenis-permohonan.edit');
        Route::put('/form-pendaftaran/jenis-permohonan/{id}', [FormPendaftaranController::class, 'jenisPermohonanUpdate'])->name('form-pendaftaran.jenis-permohonan.update');
        Route::post('/form-pendaftaran/jenis-permohonan/{id}/toggle', [FormPendaftaranController::class, 'jenisPermohonanToggle'])->name('form-pendaftaran.jenis-permohonan.toggle');
        Route::post('/form-pendaftaran/jenis-permohonan/{id}/hapus', [FormPendaftaranController::class, 'jenisPermohonanDestroy'])->name('form-pendaftaran.jenis-permohonan.hapus');

        // Persyaratan Dokumen
        Route::post('/form-pendaftaran/persyaratan', [FormPendaftaranController::class, 'persyaratanStore'])->name('form-pendaftaran.persyaratan.store');
        Route::post('/form-pendaftaran/persyaratan/{id}/hapus', [FormPendaftaranController::class, 'persyaratanDestroy'])->name('form-pendaftaran.persyaratan.hapus');

        // Saran Koreksi
        Route::post('/form-pendaftaran/saran-koreksi', [FormPendaftaranController::class, 'saranKoreksiStore'])->name('form-pendaftaran.saran-koreksi.store');
        Route::get('/form-pendaftaran/saran-koreksi/{id}/edit', [FormPendaftaranController::class, 'saranKoreksiEdit'])->name('form-pendaftaran.saran-koreksi.edit');
        Route::put('/form-pendaftaran/saran-koreksi/{id}', [FormPendaftaranController::class, 'saranKoreksiUpdate'])->name('form-pendaftaran.saran-koreksi.update');
        Route::post('/form-pendaftaran/saran-koreksi/{id}/hapus', [FormPendaftaranController::class, 'saranKoreksiDestroy'])->name('form-pendaftaran.saran-koreksi.hapus');

        // Jenis Hak
        Route::post('/form-pendaftaran/jenis-hak', [FormPendaftaranController::class, 'jenisHakStore'])->name('form-pendaftaran.jenis-hak.store');
        Route::get('/form-pendaftaran/jenis-hak/{id}/edit', [FormPendaftaranController::class, 'jenisHakEdit'])->name('form-pendaftaran.jenis-hak.edit');
        Route::put('/form-pendaftaran/jenis-hak/{id}', [FormPendaftaranController::class, 'jenisHakUpdate'])->name('form-pendaftaran.jenis-hak.update');
        Route::post('/form-pendaftaran/jenis-hak/{id}/toggle', [FormPendaftaranController::class, 'jenisHakToggle'])->name('form-pendaftaran.jenis-hak.toggle');
        Route::post('/form-pendaftaran/jenis-hak/{id}/hapus', [FormPendaftaranController::class, 'jenisHakDestroy'])->name('form-pendaftaran.jenis-hak.hapus');

        // Kategori Permohonan
        Route::post('/form-pendaftaran/kategori', [FormPendaftaranController::class, 'kategoriStore'])->name('form-pendaftaran.kategori.store');
        Route::get('/form-pendaftaran/kategori/{id}/edit', [FormPendaftaranController::class, 'kategoriEdit'])->name('form-pendaftaran.kategori.edit');
        Route::put('/form-pendaftaran/kategori/{id}', [FormPendaftaranController::class, 'kategoriUpdate'])->name('form-pendaftaran.kategori.update');
        Route::post('/form-pendaftaran/kategori/{id}/toggle', [FormPendaftaranController::class, 'kategoriToggle'])->name('form-pendaftaran.kategori.toggle');
        Route::post('/form-pendaftaran/kategori/{id}/hapus', [FormPendaftaranController::class, 'kategoriDestroy'])->name('form-pendaftaran.kategori.hapus');
    });

    // ── Kakan: monitoring seluruh alur (read-only) ──
    Route::middleware(['role:kakan'])->prefix('kakan')->name('kakan.')->group(function () {
        Route::get('/', [KakanController::class, 'index'])->name('index');
        Route::get('/tiket/{tiket}', [KakanController::class, 'show'])->name('show');
    });

    // ── V2: Loket Pendaftaran — wizard 4 tahap + daftar berkas ──
    Route::middleware(['role:loket_pendaftaran'])->prefix('pendaftaran')->name('pendaftaran.')->group(function () {
        Route::get('/', [PendaftaranController::class, 'index'])->name('index');
        Route::get('/baru', [PendaftaranController::class, 'create'])->name('create');
        Route::post('/tahap-1', [PendaftaranController::class, 'storeTahap1'])->name('tahap1.store');
        Route::get('/{draf}/tahap-2', [PendaftaranController::class, 'tahap2'])->name('tahap2');
        Route::post('/{draf}/tahap-2', [PendaftaranController::class, 'simpanTahap2'])->name('tahap2.store');
        Route::get('/{draf}/antrian', [PendaftaranController::class, 'antrian'])->name('antrian');
        Route::get('/{draf}/tahap-3', [PendaftaranController::class, 'tahap3'])->name('tahap3');
        Route::post('/{draf}/tahap-3', [PendaftaranController::class, 'simpanTahap3'])->name('tahap3.store');
        Route::get('/{draf}/tahap-4', [PendaftaranController::class, 'tahap4'])->name('tahap4');
        Route::post('/{draf}/tahap-4', [PendaftaranController::class, 'simpanTahap4'])->name('tahap4.store');
        Route::delete('/{draf}', [PendaftaranController::class, 'batal'])->name('batal');
        Route::get('/tiket/{tiket}/cetak', [PendaftaranController::class, 'cetak'])->name('cetak');
        Route::put('/tiket/{tiket}/no-berkas', [PendaftaranController::class, 'updateNoBerkas'])->name('no-berkas.update');
        Route::get('/tiket/{tiket}', [PendaftaranController::class, 'show'])->name('show');
        Route::get('/tiket/{tiket}/edit', [PendaftaranController::class, 'edit'])->name('edit');
        Route::put('/tiket/{tiket}', [PendaftaranController::class, 'update'])->name('update');
        Route::delete('/tiket/{tiket}', [PendaftaranController::class, 'destroy'])->name('destroy');
    });

    // ── V2: Ploting — Loket Ploting memproses, Loket Pendaftaran memantau ──
    Route::middleware(['role:loket_pendaftaran,loket_ploting'])->prefix('ploting')->name('ploting.')->group(function () {
        Route::get('/', [PlotingController::class, 'index'])->name('index');
        Route::post('/{draf}/selesai', [PlotingController::class, 'selesai'])
            ->middleware('role:loket_ploting')->name('selesai');
    });

    // ── V2: Back office Seksi 1 & 2 — Cari → ADD → KIRIM (Selesai/Tolak) ──
    Route::middleware(['role:staf_seksi,kasi_seksi'])->prefix('seksi')->name('seksi.')->group(function () {
        Route::get('/', [SeksiController::class, 'index'])->name('index');
        Route::get('/tiket-saya', [SeksiController::class, 'tiketSaya'])->name('tiket-saya');
        Route::get('/ditolak', [SeksiController::class, 'ditolak'])->name('ditolak');
        Route::get('/selesai', [SeksiController::class, 'selesai'])->name('selesai');
        Route::post('/{tiket}/add', [SeksiController::class, 'add'])->name('add');
        Route::post('/{tiket}/selesai', [SeksiController::class, 'selesaiProses'])->name('selesai.store');
        Route::post('/{tiket}/tolak', [SeksiController::class, 'tolak'])->name('tolak');
        Route::get('/{tiket}', [SeksiController::class, 'show'])->name('show');
    });

    // ── V2: Loket Penyerahan — Antrian → ADD → SELESAI ──
    Route::middleware(['role:loket_penyerahan'])->prefix('penyerahan')->name('penyerahan.')->group(function () {
        Route::get('/', [PenyerahanController::class, 'index'])->name('index');
        Route::get('/tiket-saya', [PenyerahanController::class, 'tiketSaya'])->name('tiket-saya');
        Route::get('/selesai', [PenyerahanController::class, 'selesai'])->name('selesai');
        Route::post('/{tiket}/add', [PenyerahanController::class, 'add'])->name('add');
        Route::post('/{tiket}/selesai', [PenyerahanController::class, 'selesaiProses'])->name('selesai.store');
        Route::get('/{tiket}', [PenyerahanController::class, 'show'])->name('show');
    });

    // ── V2: Loket Pengaduan — buat, kirim, kirim ulang ──
    Route::middleware(['role:loket_pengaduan'])->prefix('pengaduan')->name('pengaduan.')->group(function () {
        Route::get('/', [PengaduanController::class, 'index'])->name('index');
        Route::get('/baru', [PengaduanController::class, 'create'])->name('create');
        Route::post('/', [PengaduanController::class, 'store'])->name('store');
        Route::get('/selesai', [PengaduanController::class, 'selesai'])->name('selesai');
        Route::post('/{pengaduan}/kirim-ulang', [PengaduanController::class, 'kirimUlang'])->name('kirim-ulang');
        Route::get('/{pengaduan}/cetak-preview', [PengaduanController::class, 'cetakPreview'])->name('cetak-preview');
        Route::get('/{pengaduan}/cetak-pdf', [PengaduanController::class, 'cetakPdf'])->name('cetak-pdf');
        Route::get('/{pengaduan}', [PengaduanController::class, 'show'])->name('show');
    });

    // ── V2: Unit tujuan pengaduan — TERIMA / TUTUP / KEMBALIKAN ──
    Route::middleware(['role:kasi_unit,kasubbag_tu,kasi_seksi'])->prefix('pengaduan-unit')->name('pengaduan-unit.')->group(function () {
        Route::get('/', [PengaduanUnitController::class, 'index'])->name('index');
        Route::get('/selesai', [PengaduanUnitController::class, 'selesai'])->name('selesai');
        Route::post('/{pengaduan}/terima', [PengaduanUnitController::class, 'terima'])->name('terima');
        Route::post('/{pengaduan}/tutup', [PengaduanUnitController::class, 'tutup'])->name('tutup');
        Route::post('/{pengaduan}/kembalikan', [PengaduanUnitController::class, 'kembalikan'])->name('kembalikan');
        Route::post('/{pengaduan}/minta-bantuan', [PengaduanUnitController::class, 'mintaBantuan'])->name('minta-bantuan');
        Route::post('/{pengaduan}/balas-bantuan', [PengaduanUnitController::class, 'balasBantuan'])->name('balas-bantuan');
        Route::get('/{pengaduan}', [PengaduanUnitController::class, 'show'])->name('show');
    });

    // ── V2: Notifikasi in-app — lonceng, daftar, tandai dibaca ──
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/jumlah', [NotifikasiController::class, 'jumlah'])->name('notifikasi.jumlah');
    Route::get('/notifikasi/preferensi', [NotifikasiController::class, 'preferensi'])->name('notifikasi.preferensi');
    Route::post('/notifikasi/preferensi', [NotifikasiController::class, 'simpanPreferensi'])->name('notifikasi.preferensi.simpan');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    // ── V2: Daftar Arsip (read-only, hasil arsip otomatis) ──
    Route::middleware(['role:loket_pendaftaran,loket_ploting,petugas_warkah'])->prefix('arsip')->name('arsip.')->group(function () {
        Route::get('/', [ArsipController::class, 'index'])->name('index');
        Route::get('/{tiket}', [ArsipController::class, 'show'])->name('show');
    });

    // ── V2: Pengelolaan pinjaman warkah fisik ──
    Route::middleware(['role:petugas_warkah'])->prefix('warkah-permintaan')->name('warkah-permintaan.')->group(function () {
        Route::get('/', [WarkahPermintaanController::class, 'index'])->name('index');
        Route::get('/pinjaman', [WarkahPermintaanController::class, 'pinjaman'])->name('pinjaman');
        Route::get('/dikembalikan', [WarkahPermintaanController::class, 'dikembalikan'])->name('dikembalikan');
        Route::post('/{warkahPermintaan}/terima', [WarkahPermintaanController::class, 'terima'])->name('terima');
        Route::post('/{warkahPermintaan}/siap', [WarkahPermintaanController::class, 'siap'])->name('siap');
        Route::post('/{warkahPermintaan}/serahkan', [WarkahPermintaanController::class, 'serahkan'])->name('serahkan');
        Route::post('/{warkahPermintaan}/kembalikan', [WarkahPermintaanController::class, 'kembalikan'])->name('kembalikan');
        Route::post('/{warkahPermintaan}/ubah-batas', [WarkahPermintaanController::class, 'ubahBatas'])->name('ubah-batas');
        Route::get('/{warkahPermintaan}', [WarkahPermintaanController::class, 'show'])->name('show');
    });

    // ── Laporan ──
    // ── V2: Area Super Admin (impersonate, backup, demo) ──
    Route::middleware(['super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/impersonate', [ImpersonateController::class, 'index'])->name('impersonate.index');
        Route::post('/impersonate/{id}', [ImpersonateController::class, 'start'])->name('impersonate.start');

        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup', [BackupController::class, 'unduh'])->name('backup.unduh');

        Route::get('/demo', [DemoController::class, 'index'])->name('demo.index');
        Route::post('/demo/seed', [DemoController::class, 'seed'])->name('demo.seed');
        Route::post('/demo/reset', [DemoController::class, 'reset'])->name('demo.reset');
    });

    // Kembali dari mode lihat/coba — harus tetap dapat diakses saat memakai akun lain.
    Route::get('/impersonate/stop', [ImpersonateController::class, 'stop'])->name('impersonate.stop');

    Route::middleware(['role:kakan,loket_pendaftaran,loket_ploting,petugas_warkah,staf_seksi,kasi_seksi,loket_penyerahan,loket_pengaduan,kasi_unit,kasubbag_tu'])
        ->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/print', [ReportController::class, 'print'])->name('print');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/print-rapi', [ReportController::class, 'printRapi'])->name('print_rapi');
        });
});
