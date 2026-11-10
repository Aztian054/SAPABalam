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

Halaman login memakai tata letak **split-screen** (45% panel identitas, 55% panel form)
dengan latar `asset/background/BGloginpage.png` pada panel kiri.

| Elemen | Keterangan |
|---|---|
| Identitas | Satu kolom: **username atau email** (mengandung `@` → email) |
| Password | Wajib, dilengkapi tombol tampil/sembunyikan |
| Remember me | Tidak tersedia |
| Tombol masuk | Status loading + pencegahan klik ganda saat submit |
| Pesan gagal | "Username atau password salah, atau akun tidak aktif." |
| Pembatasan | `throttle:6,1` — 6 percobaan/menit per klien (HTTP 429) |
| Syarat akun | `is_active = true` |
| Setelah sukses | Sesi diregenerasi → `/dashboard` + salam |

Panel kiri memuat identitas sistem **SAPA Balam**, tagline *Melayani, Profesional,
Terpercaya*, tombol **Tentang / Layanan / Kontak**, tombol **Website Resmi**, serta
kontak langsung (alamat, WhatsApp, email, media sosial).

Tombol Tentang/Layanan/Kontak membuka panel **offcanvas** (sisi kanan pada desktop,
bottom sheet pada layar < 768px) berisi tiga tab: Tentang (visi, misi, moto),
Layanan (Permohonan Online, Layanan Pengaduan), dan Kontak. Form login tetap utuh
selama panel terbuka.

Ukuran tipografi memakai skala fluid `clamp(16px, 0.6vw + 8px, 28px)`; seluruh
komponen memakai satuan `rem` sehingga proporsional pada zoom 50%–200%.

Tidak ada lagi kartu bantuan berisi daftar akun demo pada halaman login.

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

## 5. Akun Bawaan (Seeder)

Total **18 akun aktif** dibuat oleh `UserSeeder`. Kredensial tidak dicatat di
dokumentasi ini; pengelolaan dan penggantian password dilakukan oleh Admin
melalui menu Manajemen Akun.

| Peran (`role`) | Jumlah | Cakupan |
|---|---|---|
| `super_admin` | 1 | Kendali penuh, demo, impersonate, backup |
| `admin` | 1 | Monitoring read-only, akun, master data, log |
| `kakan` | 1 | Monitoring pimpinan (read-only) |
| `kasi_seksi` | 5 | Seksi 1–5, termasuk pengaduan unit |
| `kasubbag_tu` | 1 | Tata Usaha |
| `loket_pendaftaran` | 1 | Pendaftaran & ploting |
| `loket_ploting` | 1 | Ploting bidang |
| `loket_penyerahan` | 1 | Serah terima berkas |
| `loket_pengaduan` | 1 | Registrasi & penyaluran pengaduan |
| `petugas_warkah` | 1 | Warkah fisik |
| `staf_seksi` | 4 | Staf Seksi 1/2 (termasuk alih media & validasi) |

> Seeder bersifat idempoten: akun dicocokkan berdasarkan `username`, sehingga
> menjalankan ulang seeder tidak menggandakan data dan tidak menghapus akun lain.

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
