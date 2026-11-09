
# 10 — Admin

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sudah (Admin **read-only** untuk berkas; monitoring daftar berkas, berkas selesai, dan pengaduan; manajemen akun, master data, setting, arsip folder, log). Aksi operasional berkas milik loket/seksi, bukan Admin.

---

## 1. Peran

Admin adalah **operator monitoring & manajemen lintas unit**: mengelola akun & master data, mengatur setting, memantau seluruh daftar berkas dan pengaduan, serta menata folder arsip.

Admin **bukan** pelaksana operasional: tidak mendaftarkan berkas, tidak memproses (ADD/KIRIM/Tolak), tidak menyerahkan ke pemohon, tidak menangani pengaduan, dan tidak mengarsipkan tiket secara manual. Semua aksi tulis di modul operasional ditolak middleware.

Aturan penegakan:

| Lapisan | Aturan |
| ------- | ------ |
| `RoleMiddleware` | Role `admin` hanya boleh `GET`, ditambah aksi di `admin.*`, `notifikasi.*`, `profil.*`, dan `logout`. Selain itu `403`. |
| Controller | Gate `isAdmin()` dihapus dari aksi operasional (Pendaftaran, Ploting, Seksi, Penyerahan, Pengaduan, Pengaduan Unit). |
| View | Tombol aksi operasional disembunyikan untuk Admin. |
| Super Admin | Tetap akses penuh (akun developer/darurat), dikecualikan dari dokumen ini. |

---

## 2. Menu

| Menu                        | Isi                                                                           |
| --------------------------- | ----------------------------------------------------------------------------- |
| Dashboard                   | Kartu: tiket macet, pengaduan terlambat, akun non-aktif, ringkasan status      |
| Daftar Berkas (Database)    | Monitoring seluruh tiket (filter status & pencarian) — tanpa aksi ubah         |
| Berkas Selesai              | Monitoring tiket `selesai` (filter tahun, jenis permohonan, jenis hak, petugas) |
| Daftar Pengaduan            | Monitoring pengaduan per unit — tanpa aksi terima/tolak/tutup                  |
| Pengaduan Selesai           | Monitoring pengaduan `selesai`                                                |
| Manajemen Akun              | CRUD akun, jabatan, seksi, reset password, aktif/non-aktif                    |
| Manajemen Loket Pendaftaran | Penugasan petugas loket & data pendaftaran                                    |
| Manajemen Loket Pengaduan   | Penugasan petugas pengaduan                                                   |
| Master Data                 | Jenis permohonan, seksi, pengaturan nomor                                     |
| Setting                     | Batas hari macet, batas kembali warkah, N hari arsip, batas tanggap pengaduan |
| Arsip Folder                | Kelola folder arsip fisik (bukan mengarsipkan tiket)                          |
| Log                         | Riwayat aktivitas & perubahan status                                          |
| Laporan                     | Cetak/export lintas unit                                                      |

---

## 3. Monitoring Berkas

### 3.1 Daftar Berkas (Database)

| No | No Tiket      | Pemohon | Jenis         | Status      | Posisi  | Dipegang Oleh | Seksi | Lama   | Aksi   |
| -- | ------------- | ------- | ------------- | ----------- | ------- | ------------- | ----- | ------ | ------ |
| 1  | JP26102026001 | Budi S. | Peralihan Hak | 🔵 Diproses | Seksi 2 | Andi          | 2     | 5 hari | Detail |
| 2  | JP25102026004 | Doni    | Roya          | 🔴 Ditolak  | Seksi 1 | Rina          | 1     | 2 hari | Detail |

Admin hanya dapat membuka **Detail**. Tidak ada tombol **Tambah Berkas**, **Lepas Tiket**, atau **Arsipkan**.

### 3.2 Berkas Selesai

Halaman `admin.selesai` menampilkan tiket berstatus `selesai` dengan filter:

- kata kunci (kode tiket / no. berkas / pemohon / NIK);
- tahun selesai;
- jenis permohonan;
- jenis hak;
- petugas.

Kolom **Arsip** hanya menampilkan status sudah/belum memiliki arsip. Tidak ada arsip manual maupun arsip massal dari halaman ini; arsip otomatis dijalankan scheduler.

### 3.3 Monitoring Pengaduan

- `pengaduan-unit.index` — daftar pengaduan yang masuk ke unit.
- `pengaduan-unit.selesai` — daftar pengaduan selesai.

Admin hanya melihat; tombol **Terima**, **Tutup**, **Kembalikan**, dan **Bantuan** disembunyikan dan ditolak controller. Penanganan pengaduan tetap milik Kasi/Kasubbag unit.

---

## 4. Manajemen Akun

### 4.1 Field Akun

| Field    | Aturan                                                                |
| -------- | --------------------------------------------------------------------- |
| Nama     | Wajib                                                                 |
| Username | Wajib, unik                                                           |
| Email    | Wajib, unik                                                           |
| Password | Wajib saat buat; opsional saat ubah                                   |
| Jabatan  | Wajib - salah satu dari 12 jabatan                                    |
| Seksi    | Wajib untuk `staf_seksi`, `kasi_seksi`, `kasi_unit` (1–5 / TU) |
| Aktif    | Default aktif                                                         |

### 4.2 Aturan

- Akun **tidak dihapus** bila sudah punya riwayat — cukup dinonaktifkan.
- Reset password memaksa pengguna mengganti saat login berikutnya (opsional).
- Admin tidak bisa menaikkan dirinya menjadi Super Admin.
- Perubahan jabatan/seksi berlaku pada login berikutnya (sesi aktif dipertahankan sampai logout).
- Setiap perubahan akun **Staf Seksi** mengirim notifikasi in-app ke **Kasi Seksi** terkait:
  - `akun.staf_baru` — staf baru ditambahkan;
  - `akun.staf_nonaktif` — staf dinonaktifkan;
  - `akun.staf_aktif` — staf diaktifkan kembali;
  - `akun.staf_pindah_seksi` — staf pindah seksi (Kasi lama & baru);
  - `akun.staf_dihapus` — staf dihapus.

---

## 5. Setting

| Setting                 | Default               | Dipakai untuk             |
| ----------------------- | --------------------- | ------------------------- |
| Batas hari macet        | 3 hari kerja          | Chip MACET                |
| Batas kembali warkah    | 7 hari                | Batas pinjaman baru       |
| N hari arsip            | 30 hari               | Selesai → Arsip otomatis |
| Batas tanggap pengaduan | 2 hari kerja          | Chip TERLAMBAT            |
| Format No Tiket         | `JP{ddmmyyyy}{NNN}` | Pembuatan nomor           |
| Format No Pengaduan     | `LP{ddmmyyyy}{NNN}` | Pembuatan nomor           |

Perubahan setting berlaku untuk data **baru**; data lama tidak dihitung ulang kecuali dijalankan lewat aksi khusus.

---

## 6. Master Data

| Data             | Keterangan                             |
| ---------------- | -------------------------------------- |
| Jenis Permohonan | Kode JP01–JP15, nama, aktif/non-aktif |
| Daftar Seksi     | 1–5 + TU, nama unit                   |
| Penomoran        | Prefix & padding                       |

Menonaktifkan jenis permohonan tidak menghapus data lama; jenis tersebut tidak muncul di wizard.

---

## 7. Log

| Kolom  | Isi                                                      |
| ------ | -------------------------------------------------------- |
| Waktu  | Timestamp                                                |
| Pelaku | Nama + role                                              |
| Aksi   | Seluruh aktivitas operasional & manajemen                |
| Objek  | No Tiket / No Pengaduan / akun                           |
| Detail | Nilai lama → nilai baru                                 |

Filter: rentang tanggal, pelaku, aksi, objek.

---

## 8. Aksi yang TIDAK Tersedia

- Menambah/mendaftarkan berkas (`Tambah Berkas` sudah dihapus).
- Melepas tiket dari staf (`Lepas Tiket` sudah dihapus).
- Memverifikasi/memvalidasi/mengalihmediakan berkas.
- Menyerahkan berkas ke pemohon.
- Menerima/menutup/mengembalikan pengaduan (itu Kasi unit).
- Mengarsipkan tiket secara manual maupun massal.
- Mengubah status menjadi `selesai` selain lewat Loket Penyerahan.
- Menghapus tiket atau riwayat.
- Mengelola demo/skenario (khusus Super Admin).

---

## 9. Notifikasi

| Peristiwa                          | Penerima                          |
| ---------------------------------- | --------------------------------- |
| Tiket macet melewati batas         | Admin                             |
| Warkah terlambat                   | Admin                             |
| Pengaduan terlambat                | Admin                             |
| Rekap arsip harian                 | Admin                             |
| Staf baru / nonaktif / aktif       | Kasi Seksi terkait                |
| Staf pindah seksi                  | Kasi seksi lama & baru            |
| Staf dihapus                       | Kasi Seksi terkait                |
| Akun dinonaktifkan                 | Pemilik akun tidak bisa login (tanpa notifikasi) |

---

## 10. Laporan

- Rekap seluruh tiket per status, seksi, dan periode.
- Rekap penolakan per staf/seksi.
- Rekap tiket macet.
- Rekap pengaduan lintas unit.
- Export CSV/PDF.
