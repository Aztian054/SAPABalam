# LOKET 2026 — Sistem Loket Pelayanan Pertanahan Elektronik

Aplikasi web digitalisasi alur permohonan pelayanan pertanahan untuk **Kantor Pertanahan Kota Bandar Lampung (BALAM)**. Menggantikan pengelolaan 6 file Excel/Google Sheets terpisah dengan satu basis data terpusat, autentikasi 9 role, alur kerja 8 stage paralel, cetak formulir, tracking publik, dan arsip digital.

- **Versi:** 2.2 (Alur Paralel — 9 Role / 8 Stage)
- **Stack:** Laravel 13 · PHP 8.3 · MySQL · Bootstrap 5.3 (CDN)
- **Basis dokumen:** `Template_Panduan/PRD_LOKET_BMN_BALAM.md`

---

## Alur Sistem

```
LOKET ─▶ DB ADMIN ─▶ (VERIFIKATOR ║ WARKAH) ─▶ (VALIDATOR BT ║ VALIDATOR SU)
      ─▶ (ALIH MEDIA BT ║ ALIH MEDIA SU) ─▶ SELESAI ─▶ ARSIP
```

| Stage | Role | Keterangan |
|---|---|---|
| 1 | `loket` | Registrasi tiket (nomor tiket manual, format bebas) |
| 2 | `verifikator` | Pemeriksaan kelengkapan berkas — **paralel** dengan Warkah |
| 3 | `warkah` | Penyiapan data pendukung BT & SU → `diserahkan_ke_validator` |
| 4A / 4B | `validator_btel` / `validator_suel` | Validasi BT / SU |
| 5A / 5B | `alih_media_btel` / `alih_media_suel` | Alih media BT / SU (gate AND penuh) |
| — | `admin` | Basis data terpusat, tambah tiket, revisi, selesai, arsip, kelola akun |
| — | `pemimpin` | Monitoring read-only (aksi terkunci) |

Pola universal setiap tahap: **Smart Search → Add → Process → Selesai → Kembali ke DB Admin**.

---

## Kebutuhan Sistem

- PHP **8.3+** dengan ekstensi standar Laravel
- MySQL 8 / MariaDB (Laragon, XAMPP, dsb.)
- Composer 2
- Node.js & NPM (opsional — UI memakai Bootstrap CDN, hanya untuk Vite tooling)

---

## Instalasi

```bash
# 1. Install dependency PHP
composer install

# 2. Siapkan environment
copy .env.example .env      # Windows
php artisan key:generate

# 3. Buat database MySQL bernama "loket2026", lalu isi kredensial di .env
#    DB_DATABASE=loket2026 · DB_USERNAME=root · DB_PASSWORD=

# 4. Migrasi & seeder
php artisan migrate --seed

# 5. Jalankan aplikasi
php artisan serve            # http://localhost:8000
# atau via Laragon: http://LOKET2026.test
```

---

## Akun Demo (Seeder)

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Pemimpin | `pemimpin` | `pemimpin123` |
| Loket | `loket1` | `loket123` |
| Verifikator | `verifikator1` | `verif123` |
| Warkah | `warkah1` | `warkah123` |
| Validator BT | `vbtel1` | `vbtel123` |
| Validator SU | `vsuel1` | `vsuel123` |
| Alih Media BT | `ambt1` | `ambt123` |
| Alih Media SU | `amsu1` | `amsu123` |

> Login menerima **username** maupun **email**. Setiap jabatan mendukung banyak akun.
> Daftar lengkap akun: `Template_Panduan/AKUN_DEMO_REALTIME.md`.

---

## Pengujian

```bash
# Seluruh suite (SQLite in-memory, aman — tidak menyentuh DB MySQL)
php artisan test

# Hanya demo alur end-to-end via HTTP route asli
php artisan test --filter=DemoAlurRealtimeTest

# Perbaiki format kode
vendor/bin/pint --dirty
```

Suite utama: `DemoAlurRealtimeTest` (5 skenario / 160 asersi) menyimulasikan seluruh perjalanan tiket lewat route HTTP sungguhan, termasuk middleware role dan transisi basis data.

---

## Struktur Direktori Utama

```
app/
├── Http/Controllers/    # Controller per role + AdminController + Report/Tracking/Api
├── Http/Middleware/     # RoleMiddleware (otorisasi 9 role)
├── Models/              # Tiket, TiketPenugasan, CatatanRevisi, Arsip*, User, dll.
└── Services/
    ├── TiketFlowService.php      # Inti alur: gate antar-tahap, anti-duplikat, claim
    └── SpreadsheetMlBuilder.php  # Export .xls (SpreadsheetML 2003, tanpa dependency)

resources/views/         # Blade per role (admin, loket, verifikator, warkah, ...)
routes/web.php           # Rute 9 role · routes/api.php — API v1 (tracking/stats)
database/migrations/     # Skema lengkap + migrasi penyelarasan alur final
Template_Panduan/        # PRD, panduan alur per role, laporan demo, akun demo
```

---

## Dokumen Pendamping

| Dokumen | Isi |
|---|---|
| `Template_Panduan/PRD_LOKET_BMN_BALAM.md` | PRD v2.2 — ruang lingkup, alur, skema, kriteria |
| `Template_Panduan/Alur/README.md` | Indeks 12 dokumen panduan alur per role |
| `Template_Panduan/PANDUAN_DEMO_MANUAL_BROWSER.md` | Langkah demo alur via browser |
| `Template_Panduan/LAPORAN_DEMO_ALUR_KONDISI_REALTIME.md` | Hasil uji end-to-end + matriks transisi |

---

*Kantor Pertanahan Kota Bandar Lampung · LOKET 2026 v2.2*
