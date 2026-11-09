# 01 — Landing Page, Login & Hak Akses

> **Status dokumen:** Blueprint V2 · **Implementasi kode:** sudah (12 jabatan V2 + `seksi`, tanpa role lama).

---

## 1. Alur Masuk

```text
Buka Website (/)
   ├─ Belum login -> Halaman Login (/login)
   └─ Sudah login -> Dashboard (/dashboard)

Halaman Login (/login)
   -> isi Username ATAU Email + Password
   -> validasi (akun aktif, rate-limit 6 percobaan/menit)
   -> redirect /dashboard sesuai jabatan
```

`GET /` tidak punya halaman sendiri — hanya pengalihan (login atau dashboard).

---

## 2. Halaman Login

| Elemen | Keterangan |
|---|---|
| Identitas | Satu kolom: **username atau email** (mengandung `@` → email) |
| Password | Wajib |
| Remember me | Opsional |
| Pesan gagal | "Username atau password salah, atau akun tidak aktif." |
| Pembatasan | `throttle:6,1` — 6 percobaan/menit per klien (HTTP 429) |
| Syarat akun | `is_active = true` |
| Setelah sukses | Sesi diregenerasi → `/dashboard` + salam |

Kartu bantuan di halaman login memuat daftar jabatan dan akun demo (hanya di lingkungan demo).

---

## 3. Jabatan & Menu Setelah Login

| Jabatan | Halaman awal | Menu (ringkas) |
|---|---|---|
| Super Admin | Dashboard Sistem | Demo, lihat-sebagai, akun, master, log, backup |
| Admin | Dashboard | Daftar Berkas & Berkas Selesai (monitoring), Pengaduan (monitoring), akun, master, setting, log |
| Loket Pendaftaran | Dashboard | Pendaftaran Baru, Berkas, Ploting, Warkah, Selesai, Ditolak, Arsip |
| Loket Ploting | Dashboard | Berkas Ploting, Selesai Ploting, Arsip |
| Petugas Warkah | Dashboard | Permintaan, Pinjaman, Pengembalian, Arsip |
| Staf Seksi 1–2 | Dashboard | Permohonan (cari+ADD), Tiket Saya, Warkah, Selesai, Ditolak |
| Kasi Seksi 1–2 | Dashboard | Permohonan Seksi, Warkah, Selesai, Ditolak, Pengaduan |
| Kasi 3/4/5 & TU | Dashboard | Pengaduan, Pengaduan Selesai |
| Loket Penyerahan | Dashboard | Antrian Penyerahan (cari+ADD), Tiket Saya, Selesai |
| Loket Pengaduan | Dashboard | Buat Pengaduan, Daftar Pengaduan, Pengaduan Selesai |
| Kakan | Dashboard | Permohonan, Warkah (opsional), Pengaduan |

Semua jabatan juga mendapat **Laporan** sesuai kewenangannya.

---

## 4. Aturan Otorisasi

1. **RBAC jabatan** — middleware `role` membuka hanya rute milik jabatan.
2. **RBAC seksi** — `Staf` dan `Kasi` hanya melihat data `seksi`-nya (1 atau 2; 3–5/TU untuk pengaduan).
3. **Kakan & Super Admin** — read-only pada data operasional (Super Admin tetap bisa demo/konfigurasi).
4. **Privasi** — nama penanggung jawab tahap hanya tampil di akun yang bersangkutan, Kasi seksinya, Admin, dan Super Admin. NIK disamarkan untuk Kakan.
5. **Akun non-aktif** langsung kehilangan akses tanpa menghapus riwayat.

---

## 5. Akun Demo

| Jabatan | Username | Password |
|---|---|---|
| Super Admin | `superadmin` | `superadmin123` |
| Admin | `admin` | `admin123` |
| Loket Pendaftaran | `loketdaftar1` | `loketdaftar123` |
| Loket Ploting | `loketploting1` | `ploting123` |
| Petugas Warkah | `warkah1` | `warkah123` |
| Staf Seksi 1 | `staf1a` | `staf123` |
| Staf Seksi 2 | `staf2a` | `staf123` |
| Kasi Seksi 1 | `kasi1` | `kasi123` |
| Kasi Seksi 2 | `kasi2` | `kasi123` |
| Kasi Unit 3 | `kasiunit3` | `kasiunit123` |
| Kasubbag TU | `kasubbagtu` | `kasubbagtu123` |
| Loket Penyerahan | `serah1` | `serah123` |
| Loket Pengaduan | `pengaduan1` | `pengaduan123` |
| Kakan | `kakan` | `kakan123` |

> Seeder `UserSeeder` sudah berisi 14 akun V2 di atas dan menghapus akun di luar daftar.

---

## 6. Logout & Sesi

- Logout: `POST /logout` → sesi di-invalidate + token diregenerasi → `/login`.
- Sesi berakhir otomatis sesuai konfigurasi Laravel.
- Filter daftar disimpan sementara selama sesi (lihat `15-Komponen-Umum.md`).

---

## 7. Catatan Implementasi

- Ganti middleware `role:...` menjadi pasangan `role` + `seksi`.
- Tambah kolom `seksi` pada `users` (1–5, atau `TU`).
- Perbarui seeder & tampilan quick-login agar sesuai tabel §5.
- Referensi role lama pada UI/label sudah dihapus.
