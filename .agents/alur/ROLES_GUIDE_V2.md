# Panduan Peran & Fungsi Akun LOKET2026 (V2)

> Sistem: Laravel 13, PHP 8.3, MySQL, Blade + Bootstrap 5.3.  
> Semua role terdefinisi di `users.role`, dikontrol middleware `role:xxx` atau `super_admin`.

## 1. Tabel Peran (12 Role)

| Kode | Nama | Role | Hak Utama | Menu Utama |
|------|------|------|-----------|------------|
| super_admin | Super Admin | `super_admin` | Semua modul, backup, log, impersonate | Dashboard, Daftar Permohonan, Pengaduan, Demo, Impersonate, Akun, Log, Laporan, Backup |
| admin | Admin | `admin` | Monitoring lintas unit (read-only), Akun, Setting | Dashboard, Daftar Berkas, Berkas Selesai, Pengaduan, Akun, Master Data, Setting, Log, Laporan |
| loket_pendaftaran | Loket Pendaftaran | `loket_pendaftaran` | Pendaftaran 4 tahap, ploting, arsip | Pendaftaran Baru, Daftar Berkas, Ploting, Arsip, Laporan |
| loket_ploting | Loket Ploting | `loket_ploting` | Proses ploting, daftar selesai | Daftar Ploting, Arsip, Laporan |
| petugas_warkah | Petugas Warkah | `petugas_warkah` | Warkah fisik (pinjam, kembalikan) | Permintaan Warkah, Pinjaman, Dikembalikan, Arsip, Laporan |
| staf_seksi | Staf Seksi | `staf_seksi` | ADD, Proses, Tiket Saya, Selesai/Ditolak | Cari & ADD, Tiket Saya, Selesai, Ditolak, Laporan |
| kasi_seksi | Kasi Seksi | `kasi_seksi` | Monitoring seksi, pengaduan | Permohonan, Warkah, Selesai, Pengaduan, Laporan |
| kasi_unit | Kasi Unit | `kasi_unit` | Penanganan pengaduan unit | Pengaduan, Selesai, Laporan |
| kasubbag_tu | Kasubbag TU | `kasubbag_tu` | Penanganan pengaduan TU | Pengaduan, Selesai, Laporan |
| loket_penyerahan | Loket Penyerahan | `loket_penyerahan` | ADD + SELESAI, antrian penyerahan | Antrian Penyerahan, Tiket Saya, Selesai, Laporan |
| loket_pengaduan | Loket Pengaduan | `loket_pengaduan` | Buat & kirim pengaduan | Buat Pengaduan, Daftar Pengaduan, Selesai, Laporan |
| kakan | Kakan | `kakan` | Monitoring read‑only | Dashboard, Permohonan, Warkah, Pengaduan, Laporan |

## 2. Cara Menggunakan Setiap Role

### Super Admin (`super_admin`)
- Login: `superadmin` / `superadmin123`
- Akses: semua menu, impersonate, data demo, backup, log
- Impersonate: menu **Lihat/Coba Sebagai Akun** → pilih akun → mode baca‑saja
- Demo: **Data & Skenario Demo** → **Seed** / **Reset**
- Backup: menu **Backup** → **Unduh**

### Admin (`admin`)
- Login: `admin` / `admin123`
- Akses: monitoring lintas unit (read-only), Akun, Setting, Log
- Monitoring: **Daftar Berkas** / **Berkas Selesai** / **Pengaduan** → lihat detail saja
- Akun: **Manajemen Akun** → **+ Tambah Akun** (perubahan staf otomatis memberi notifikasi ke Kasi Seksi)

### Loket Pendaftaran (`loket_pendaftaran`)
- Login: `loketdaftar1` / `loketdaftar123`
- Dashboard: **Pendaftaran Baru**, **Daftar Berkas**, **Ploting**, **Arsip**
- Proses 4 tahap: Biodata → Ploting → Jenis → Selesai/ditolak

### Loket Ploting (`loket_ploting`)
- Login: `loketploting1` / `ploting123`
- Dashboard: **Daftar Ploting**, **Arsip**
- Aksi: ubah status ploting (belum → sudah) + catatan

### Petugas Warkah (`petugas_warkah`)
- Login: `warkah1` / `warkah123`
- Alur: Terima → Pinjam → Siap → Pinjam → Kembalikan → Ubah Batas
- Batas pinjam default 7 hari, diperpanjang via UI

### Staf Seksi (`staf_seksi`)
- Pilih SECTION (1‑5/TU) saat buat akun
- Dashboard: **Cari & ADD**, **Tiket Saya**, **Selesai**, **Ditolak**
- Proses: ADD → Selesai/Tolak → KIRIM → ADD ULANG (jika ditolak)

### Kasi Seksi (`kasi_seksi`)
- Pilih SECTION saat buat akun
- Dashboard: **Permohonan**, **Selesai**, **Ditolak**, **Pengaduan**
- Lihat tiket hanya setelah staf SECTION‑nya ADD

### Kasi Unit (`kasi_unit`)
- Dashboard: **Pengaduan**, **Selesai**, **Laporan**
- Tidak terikat seksi, tanggung jawab penuh

### Kasubbag TU (`kasubbag_tu`)
- Dashboard: **Pengaduan**, **Selesai**, **Laporan**
- Tugas: terima/kirim ulang/kembalikan pengaduan

### Loket Penyerahan (`loket_penyerahan`)
- Dashboard: **Antrian Penyerahan**, **Tiket Saya**, **Selesai**
- Aksi utama: **ADD** → **SELESAI** (tanpa Tolak)

### Loket Pengaduan (`loket_pengaduan`)
- Dashboard: **Buat Pengaduan**, **Daftar Pengaduan**, **Selesai**
- Aksi: **Kirim** → **Kirim Ulang** → **Tutup**

### Kakan (`kakan`)
- Dashboard: **Monitoring & Evaluasi** → ringkasan per SECTION
- Filter: No Tiket, status, rentang tanggal
- Read‑only: tidak boleh mengubah data

## 3. Matriks Akses (Ringkas)

| Akses | super_admin | admin | loket_pend | loket_plot | petugas_w | staf_seksi | kasi_seksi | kasi_unit | kasubbag | loket_py | loket_pa | kakan |
|-------|-------------|-------|-----------|-----------|-----------|----------|----------|----------|----------|----------|----------|-------|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Akun | ✓ | ✓ | | | | | | | | | | |
| Master Data | ✓ | ✓ | | | | | | | | | | |
| Permohonan | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Ploting | | | ✓ | ✓ | | | | | | | | |
| Warkah | | | ✓ | | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Pengaduan | ✓ | ✓ | | | | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ |
| Selesai/Ditolak | ✓ | ✓ | ✓ | ✓ | | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Backup/Log | ✓ | | | | | | | | | | | |
| Lepas Tiket | | *(dihapus)* | | | | | | | | | | |
| Impersonate | ✓ | | | | | | | | | | | |

✓ = Bisa, - = Tidak boleh

## 4. Aturan Keamanan Utama
- **RBAC**: middleware `role:xxx` membuka hanya rute milik jabatan.
- **RBAC seksi**: Staf/Kasi hanya lihat data SECTION‑nya.
- **Super Admin**: hanya impersonate, backup, log, demo; tidak boleh aksi operasional harian.
- **Demo mode**: aktif bila `APP_ENV=local` atau `DEMO_MODE=true`; data demo `is_demo = true` bisa direset.
- **Backup**: hanya Super Admin, dump tidak tercatat di Log Aktivitas.

---

*Catatan: Dokumen ini bersifat implementasi; detail rule/UI ada di file `.md` terkait di repo.*