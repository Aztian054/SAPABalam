# 17 — Pembaruan P1 & Audit View vs Fitur

> **Status dokumen:** Catatan pembaruan **implementasi nyata** (bukan target Blueprint).
> **Tanggal:** 28 Oktober 2026
> **Cakupan:** Penyelesaian P1 (Setting, Log Aktivitas, Lihat/Coba Sebagai Akun, Backup, Data Demo), audit ketidakcocokan view vs fitur, serta RWD + sidebar sticky scroll.
> **Rujukan:** [`00-Blueprint-Utama.md`](00-Blueprint-Utama.md), [`11-Super-Admin.md`](11-Super-Admin.md), [`16-Notifikasi-In-App.md`](16-Notifikasi-In-App.md)

---

## 1. Ringkasan

P1 menambahkan lima kemampuan operasional yang sebelumnya hanya ada di dokumen target. Setelah itu dilakukan audit menyeluruh untuk mencari **view yang memakai data/route/fitur yang tidak benar-benar dikirim atau tidak tersedia**, lalu bug yang ditemukan diperbaiki. Terakhir, seluruh tampilan diseragamkan agar responsif dan sidebar dapat digulir.

Hasil akhir:

| Aspek | Status |
|---|---|
| Test suite | **170 test / 840 assertion — lulus semua** |
| Crawl halaman GET | **522 route lintas 12 jabatan, tanpa error 500** |
| Menu → route | **Semua menu dapat dibuka pemiliknya** (kecuali menu Demo saat mode demo mati — sesuai desain) |
| Route yang dipakai Blade | **Tidak ada nama route fiktif** di kode aktif |

---

## 2. Fitur P1 yang Diselesaikan

### 2.1 Setting Aplikasi

- Tabel `settings` (kunci–nilai) + model `App\Models\Setting` dengan fallback ke `config/loket.php` lalu nilai default.
- Halaman Admin → **Setting** (`SettingController`) untuk mengubah batas hari macet, batas arsip, batas tanggapan pengaduan, retensi log, dan identitas instansi.
- Nilai setting kini benar-benar dipakai fitur, bukan angka mati di view:
  - `Tiket` → penanda **MACET** dan batas arsip.
  - `Pengaduan` → penanda **terlambat ditanggapi**.
  - `TiketNomorService` → format/segmen nomor tiket.
  - `WarkahPermintaanController` → batas pengembalian.
  - `admin/arsip` → teks "berkas selesai yang sudah melewati N hari".

### 2.2 Log Aktivitas

- Tabel `log_aktivitas` menyimpan **salinan nama & jabatan pelaku** (tetap terbaca walau akun dihapus/diubah).
- `LogAktivitasService` mencatat: `LOGIN`, `LOGOUT`, CRUD akun, `UBAH SETTING`, `LEPAS TIKET`.
- **Aktivitas Super Admin tidak pernah dicatat** (dan baris lamanya disembunyikan), sehingga keberadaannya tidak dapat dideteksi dari halaman Log — termasuk login, impersonate, seed/reset demo, dan unduh backup.
- Pencatatan **tidak boleh menggagalkan aksi bisnis** — kegagalan log ditelan, aksi utama tetap jalan.
- Halaman Admin → **Log Aktivitas** dengan filter kata kunci, aksi, dan rentang tanggal; query memakai scope `tanpaSuperAdmin()`.
- Command `loket:bersihkan-log` dijadwalkan 01:30, memakai `retensi_log_hari`.

### 2.3 Lihat/Coba Sebagai Akun (Impersonate)

- Sesi asli disimpan di `impersonate_original`, target di `impersonate_id`; guard diganti **per request** oleh `ImpersonateMiddleware` sehingga sesi Super Admin tidak tertimpa.
- **Read-only**: seluruh method non-GET diblokir middleware (403) selama mode aktif.
- Banner kuning "Anda melihat sebagai …" + tombol **Kembali ke Super Admin**.
- Super Admin lain **tidak bisa** di-impersonate (404).
- Sesi otomatis berakhir bila akun target dinonaktifkan.
- Aktivitas mulai/selesai impersonate **tidak tercatat** di Log (aturan Super Admin tanpa jejak).
- Area developer dijaga alias middleware `super_admin` (`SuperAdminMiddleware`).

### 2.4 Backup Database

- `BackupService` membuat dump `.sql` **portabel** (MySQL/MariaDB/SQLite) tanpa bergantung `mysqldump`.
- Nama file: `loket-{yyyyMMdd-HHmm}.sql`.
- Hanya Super Admin (`super-admin.backup.index`, `super-admin.backup.unduh`).
- Unduhan **tidak muncul di Log Aktivitas** karena pelakunya Super Admin.
- Folder `backup/` tidak dihapus dan tidak dipakai sebagai sumber kode.

### 2.5 Data & Skenario Demo

- Hanya aktif bila `APP_ENV=local` **atau** `loket.demo_mode` / `DEMO_MODE=true`.
- Satu halaman **Data & Skenario Demo** berisi ringkasan + tombol Seed dan Reset.
- Data demo ditandai `is_demo = true` pada `users`, `tikets`, `pengaduans`, `warkah_permintaans` dan nama berprefiks `[DEMO]`.
- **Reset hanya menghapus data demo**, data asli tetap utuh; konfirmasi ganda: ketik `HAPUS DEMO` + centang pernyataan.
- Menu Demo memakai kolom `menus.butuh_demo` dan disembunyikan saat mode demo mati (route-nya 403).
- Isi paket demo (setelah perbaikan audit):
  - 3 akun: `demo_loket`, `demo_staf`, `demo_warkah`
  - **5 tiket**: normal, ditolak, selesai, macet, dan **antrean warkah** (`di_database`)
  - 2 warkah: pinjaman berjalan + permintaan baru
  - 2 pengaduan: terlambat ditanggapi + selesai (lengkap dengan `diterima_pada`, `selesai_pada`, `catatan_terakhir`)

---

## 3. Metode Audit View vs Fitur

Audit dilakukan tiga lapis agar tidak hanya mengandalkan pembacaan manual:

1. **Pemindaian statis route** — semua `route('…')` di `resources/views`, `app/Http/Controllers`, dan `app/Console` dibandingkan dengan daftar `php artisan route:list --json`.
2. **Pemindaian statis view** — semua `view('…')` di controller/command dicek keberadaan file Blade-nya.
3. **Crawl runtime** — `RouteSmokeTest` membuka **522 route GET** sebagai 12 jabatan, memastikan tidak ada 500 (variabel view hilang, view tidak ada, relasi null).
4. **Audit menu** — `MenuNavigationTest` membuka setiap `menus.route` milik tiap `role_owner`, memastikan bukan 403/404.

Temuan pemindaian statis yang tersisa **hanya berada di `backup/2026-10-27_v2-start/`** (salinan kode lama), bukan kode yang berjalan.

---

## 4. Bug View vs Fitur yang Diperbaiki

| # | Gejala | Penyebab | Perbaikan |
|---|---|---|---|
| 1 | Membuka `/impersonate/stop` tanpa sedang impersonate **mengeluarkan pengguna** dari sesi sendiri | `stop()` langsung `Auth::logout()` tanpa memeriksa kunci sesi | `stop()` memeriksa `impersonate_id`; bila tidak ada → redirect dashboard + `warning`, sesi tetap |
| 2 | Saat impersonate, menu **"Keluar Sistem"** tampil tetapi selalu 403 | Form logout memakai POST, diblokir middleware read-only | Item diganti **"Kembali ke Super Admin"** (GET `impersonate.stop`) selama mode aktif |
| 3 | Angka **badge notifikasi** hanya benar setelah polling JS pertama; saat muat awal tampak 0/tersembunyi | Variabel `$jumlahNotifikasi` dipakai layout tetapi tidak pernah dikirim controller | `AppServiceProvider` menambahkan view composer `layouts.app` yang menghitung notifikasi belum dibaca |
| 4 | Skenario demo warkah menunjuk tiket yang sama sehingga "permintaan baru" tidak terlihat di alur Tahap 4 | Seed memakai tiket normal untuk permintaan `SU` | Ditambah tiket demo `di_database` khusus antrean warkah (total 5 tiket) |
| 5 | Pengaduan demo berstatus `selesai` tampil tanpa tanggal terima/selesai/catatan | Seed tidak mengisi kolom tersebut | Seed mengisi `diterima_pada`, `selesai_pada`, `catatan_terakhir` |
| 6 | Halaman demo menulis "4 tiket" dan skenario warkah tidak sesuai data | Teks statis tidak ikut berubah | Teks diperbarui: 5 tiket + baris skenario warkah diperjelas |
| 7 | Label backup "Nama file" terbaca seolah file sudah ada | Label ambigu | Menjadi **"Contoh nama file"** |
| 8 | Login, impersonate, dan aksi lain Super Admin **terlihat di Log Aktivitas** | Service mencatat semua pelaku apa adanya | Service melewati pelaku `super_admin`; halaman Log memakai scope `tanpaSuperAdmin()` sehingga baris lama pun tersembunyi |
| 9 | Sidebar panjang terpotong (tidak bisa digulir) dan tampilan sempit berantakan | Sidebar `fixed` tanpa area gulir; topbar 4 elemen berjajar tanpa grup | Sidebar `100dvh` + area menu bergulir sendiri; topbar dikelompokkan; CSS RWD & scrollbar kustom global |

---

## 4b. Responsive Web Design & Scrollbar

Berlaku untuk **semua halaman ber-layout** (`layouts/app.blade.php`, 50 view) dan halaman **login**, termasuk halaman akun yang ditambahkan kemudian.

### Sidebar (sticky scroll)

- `#sidebar` memakai `height: 100dvh` + `display: flex` + `overflow: hidden`.
- Blok brand tetap di atas; daftar menu (`.sidebar-scroll`) bergulir sendiri dengan `overflow-y: auto`, `overscroll-behavior: contain`, dan `-webkit-overflow-scrolling: touch`.
- Saat drawer mobile terbuka diberi bayangan agar terpisah dari konten.

### Scrollbar kustom

- Global: `scrollbar-width: thin` (Firefox) + `::-webkit-scrollbar` 10px, thumb membulat, track transparan, hover menjadi emas.
- Sidebar: thumb putih transparan 6px agar menyatu dengan latar navy.
- Halaman login: thumb terang di atas latar gradient.

### RWD

- `body`/`#main-content` diberi `overflow-x: hidden` + `min-width: 0` agar tabel lebar tidak merusak layout.
- Media query 1199 / 991 / 767 / 575 px: padding kontainer, topbar, footer, kartu, modal, tombol, badge, dan chart menyesuaikan.
- Topbar dikelompokkan menjadi **kiri** (toggle + tanggal) dan **kanan** (tema, notifikasi, pengguna) dengan `margin-left: auto`, sehingga tidak menyebar saat sempit.
- Judul + tombol aksi halaman turun baris (`flex-wrap`) di layar kecil.
- `img/svg/video/canvas` dibatasi `max-width: 100%`.
- `prefers-reduced-motion` menonaktifkan animasi bagi pengguna yang memintanya.
- Tabel sudah memakai `.table-responsive` (kecuali halaman cetak), dengan scroll sentuh yang halus.

---

## 5. Berkas Terdampak (Utama)

| Area | Berkas |
|---|---|
| Setting | `app/Models/Setting.php`, `app/Http/Controllers/SettingController.php`, `database/migrations/2026_10_28_094434_create_settings_table.php`, `config/loket.php`, `resources/views/admin/setting.blade.php` |
| Log | `app/Models/LogAktivitas.php`, `app/Services/LogAktivitasService.php`, `app/Http/Controllers/LogAktivitasController.php`, `app/Console/Commands/BersihkanLogAktivitas.php`, `resources/views/admin/log.blade.php` |
| Impersonate | `app/Http/Middleware/ImpersonateMiddleware.php`, `app/Http/Middleware/SuperAdminMiddleware.php`, `app/Http/Controllers/ImpersonateController.php`, `resources/views/super_admin/impersonate.blade.php`, `resources/views/layouts/app.blade.php` |
| Backup | `app/Services/BackupService.php`, `app/Http/Controllers/BackupController.php`, `resources/views/super_admin/backup.blade.php` |
| Demo | `app/Services/DemoService.php`, `app/Http/Controllers/DemoController.php`, `resources/views/super_admin/demo.blade.php`, `database/migrations/2026_10_28_100000_add_is_demo_columns.php`, `database/migrations/2026_10_28_100100_add_butuh_demo_to_menus_table.php` |
| Menu | `app/Models/Menu.php`, `app/Services/SidebarService.php`, `database/seeders/MenuSeeder.php`, `resources/views/partials/sidebar.blade.php` |
| Notifikasi | `app/Providers/AppServiceProvider.php`, `resources/views/layouts/app.blade.php` |
| RWD & Scrollbar | `resources/views/layouts/app.blade.php` (CSS global + topbar), `resources/views/partials/sidebar.blade.php` (`.sidebar-scroll`), `resources/views/auth/login.blade.php` |
| Route | `routes/web.php` (grup `super-admin`, `impersonate.stop` di luar grup), `bootstrap/app.php` (alias `super_admin`) |

---

## 6. Test

Test baru/terbaru yang menjaga pembaruan ini:

| Test | Menjaga |
|---|---|
| `SettingTest` | Simpan/baca setting, fallback config, validasi |
| `LogAktivitasTest` | Pencatatan aksi, filter, retensi, **aktivitas Super Admin tidak tercatat & tidak tampil** |
| `ImpersonateTest` | Mulai/stop, read-only, Super Admin lain ditolak, target nonaktif, **stop tanpa sesi tidak logout**, **menu logout diganti**, **impersonate tidak tercatat** |
| `BackupTest` | Hanya Super Admin, nama file, isi dump, **unduhan tidak tercatat di Log** |
| `DemoTest` | Mode aktif/mati, seed 5 tiket bertanda, konfirmasi ganda, reset hanya data demo |
| `NotifikasiTest` | **Badge jumlah belum dibaca dirender server-side** |
| `RouteSmokeTest` | Crawl 522 GET lintas jabatan — tidak ada 500 |
| `MenuNavigationTest` | Setiap menu dapat dibuka pemiliknya (Demo 403 saat mode mati) |

Perintah verifikasi:

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
php artisan view:cache
```

---

## 7. Catatan Operasional

- **Mode demo** ditentukan `APP_ENV=local` atau `DEMO_MODE=true`. Di produksi biarkan `DEMO_MODE=false`; menu dan route Demo otomatis tertutup.
- **Backup** dibuat aplikasi (bukan `mysqldump`), jadi aman di lingkungan tanpa akses shell database. Simpan hasil unduhan di luar folder publik.
- **Impersonate bersifat baca-saja**: jangan menambah aksi tulis ke area ini tanpa mencabut pemblokiran middleware, karena seluruh non-GET akan 403.
- **Setting** memakai cache config; setelah mengubah `.env` jalankan `php artisan config:clear`.
- **Retensi log** dijalankan scheduler; pastikan cron `schedule:run` aktif di server.
- **Log Super Admin** sengaja kosong. Bila suatu saat audit jejak Super Admin dibutuhkan, ubah `LogAktivitasService` + scope `tanpaSuperAdmin()` secara sadar — jangan menambah pengecualian diam-diam.
- **RWD** terpusat di `layouts/app.blade.php`; halaman baru otomatis mengikuti. Jangan menaruh lebar/tinggi px keras pada sidebar atau topbar.
- Folder `backup/` **tidak dihapus** dan bukan bagian kode aktif — jangan dijadikan acuan audit.

---

## 8. Checklist

- [x] Menu dari database, terfilter per jabatan, `super_admin` melihat semua + penanda MODE DEVELOPER
- [x] Akun `super_admin` tidak tampil & tidak dapat dikelola dari Manajemen Akun
- [x] Setting aplikasi terhubung ke fitur
- [x] Log aktivitas + pembersihan terjadwal; aktivitas Super Admin tidak tercatat
- [x] Impersonate read-only + banner + jalan kembali (tanpa jejak log)
- [x] Backup `.sql` Super Admin (tanpa jejak log)
- [x] Data demo bertanda, reset selektif, konfirmasi ganda
- [x] RWD + sidebar sticky scroll + scrollbar kustom di semua tampilan
- [x] Audit view vs fitur & perbaikan bug
- [x] Dokumentasi pembaruan (dokumen ini)

---

## 9. Revisi November 2026

### 9.1 Rebranding SAPA Balam

- Nama sistem berubah dari LOKET 2026 menjadi **SAPA Balam** pada seluruh antarmuka, judul halaman, sidebar, footer, ekspor laporan, dan dokumentasi.
- APP_NAME menjadi `SAPA Balam`; database produksi berganti nama menjadi `sapa` (32 tabel, 18 akun, dipindahkan utuh; DB lama diarsipkan).
- Nama cookie sesi ikut berubah karena diturunkan dari `APP_NAME`, sehingga semua pengguna perlu login ulang satu kali.

### 9.2 Halaman Login Baru

- Tata letak split-screen: panel identitas 45% dengan `BGloginpage.png`, panel form 55%.
- Animasi masuk bertahap, gradient bergerak, ornamen CSS, dan glow yang mengikuti kursor (tanpa tilt).
- Tombol tampil/sembunyikan password dan status loading anti-submit ganda.
- Panel kiri: tombol **Tentang / Layanan / Kontak**, tautan **Website Resmi**, kontak langsung (alamat, WhatsApp, email, media sosial), tagline *Melayani, Profesional, Terpercaya* (Caveat Brush).
- Offcanvas **Tentang & Kontak** tiga tab: Tentang (visi/misi/moto), Layanan (Permohonan Online, Layanan Pengaduan), Kontak.
- Skala tipografi fluid `clamp(16px, 0.6vw + 8px, 28px)`; seluruh komponen memakai `rem`; tetap muat pada 1366×768 dan bebas scroll horizontal pada zoom 50%–200%.
- Tidak ada lagi tautan "Ingat saya" maupun kartu akun demo di halaman login.

### 9.3 Halaman Utama

- Latar konten memakai `RIWAYAT.png` dengan lapisan gelap; kontras teks disesuaikan untuk mode terang dan gelap.
- Bootstrap JS bundle dimuat di halaman login untuk mendukung offcanvas dan tab; aset tetap dari CDN tanpa proses build.

### 9.4 Verifikasi

- `php artisan view:cache` berhasil.
- Test login: 4 test / 17 assertion lulus.
- Route smoke test: 558 rute, tidak ada error 500.
