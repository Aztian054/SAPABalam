# 11 — Super Admin

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sudah (Skenario Demo, Lihat/Coba Sebagai Akun, Data Demo, Backup, Log). Rincian pembaruan: [`17-Pembaruan-P1-dan-Audit-View.md`](17-Pembaruan-P1-dan-Audit-View.md).

---

## 1. Peran

Super Admin adalah **pemilik sistem**. Semua kemampuan Admin ditambah:

- menjalankan **Skenario Demo** untuk pelatihan/uji alur;
- **Lihat/Coba Sebagai Akun** (impersonate, read-only);
- mengelola **Data Demo** (reset/seed);
- mengelola **Log** seluruh sistem dan **Backup**.

Super Admin **tidak** memproses berkas operasional.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard Sistem | Kesehatan sistem, jumlah akun aktif, aktivitas terbaru |
| Daftar Permohonan | Semua tiket (read-only monitoring) |
| Daftar Pengaduan | Semua pengaduan (read-only) |
| Skenario Demo | Jalankan alur otomatis 1 klik |
| Lihat/Coba Sebagai Akun | Impersonate read-only |
| Manajemen Akun | Sama dengan Admin |
| Master Data & Setting | Sama dengan Admin |
| Data Demo | Reset/seed data contoh |
| Log | Log sistem + audit |
| Laporan | Lintas unit |
| Backup | Unduh/arsipkan basis data |

---

## 3. Skenario Demo

Tujuan: memperagakan alur lengkap tanpa input manual.

| Skenario | Isi |
|---|---|
| Alur Normal | Daftar → Ploting → ADD staf → Selesai → Serah → Arsip |
| Alur Ditolak | ADD → Tolak → ADD ULANG → Selesai |
| Alur Warkah | Tahap 4 butuh warkah → Terima → Siap → Pinjam → Kembalikan |
| Alur Macet | Tiket dibiarkan melewati batas → chip MACET |
| Alur Pengaduan | Buat LP → Terima → Tutup |

Aturan:
- Hanya di lingkungan demo (`APP_ENV=local` atau flag `DEMO_MODE`).
- Data demo diberi prefiks yang jelas dan bisa dihapus 1 klik.
- Setiap langkah menampilkan pelaku & waktu agar mudah dijelaskan.

---

## 4. Lihat/Coba Sebagai Akun

| Aspek | Aturan |
|---|---|
| Mode | **Read-only** — tidak bisa menekan aksi yang mengubah data |
| Penanda | Banner kuning "Anda melihat sebagai {nama} ({jabatan})" |
| Keluar | Tombol "Kembali ke Super Admin" |
| Audit | Setiap penggunaan dicatat di Log |
| Larangan | Tidak bisa impersonate Super Admin lain |

Tujuan: memverifikasi tampilan & hak akses tanpa mengubah data.

---

## 5. Data Demo

| Aksi | Efek |
|---|---|
| Seed Demo | Membuat akun + tiket + warkah + pengaduan contoh |
| Reset Demo | Menghapus **hanya** data demo (bertanda) |
| Hapus Semua Demo | Bersihkan total data demo termasuk riwayat |

Pengamanan: konfirmasi ganda + hanya untuk Super Admin. Data non-demo tidak tersentuh.

---

## 6. Log

| Jenis | Isi |
|---|---|
| Audit | Perubahan data penting (akun, setting, ubah nomor) |
| Aktivitas | Login, ADD, KIRIM, Tolak, Selesai |
| Sistem | Error & kegagalan (ringkas) |

Filter: rentang tanggal, pelaku, jenis, objek. Retensi mengikuti Setting (default 90 hari).

---

## 7. Backup

| Aspek | Aturan |
|---|---|
| Isi | Dump basis data (tanpa file upload besar) |
| Akses | Super Admin saja |
| Penamaan | `loket-{yyyyMMdd-HHmm}.sql` |
| Audit | Setiap unduhan dicatat |
| Pemulihan | Manual oleh operator (di luar aplikasi) |

---

## 8. Aksi yang TIDAK Tersedia

- Memproses verifikasi/validasi/alih media.
- Menyerahkan berkas.
- Menerima/menutup pengaduan.
- Menghapus data non-demo.
- Mengubah riwayat/audit log.

---

## 9. Notifikasi

Super Admin menerima notifikasi **ringkas** untuk kejadian kritis: kegagalan backup, error sistem berulang, dan (opsional) tiket macet berkepanjangan.

---

## 10. Catatan Implementasi

- Middleware `role:super_admin` + flag demo untuk menu Skenario Demo/Data Demo.
- Impersonate memakai `impersonate_id` / `impersonate_original` di sesi; guard diganti per request dan seluruh method non-GET diblokir (baca-saja).
- Backup dibuat `BackupService` (SQL portabel) tanpa `mysqldump`; setiap unduhan dicatat di `log_aktivitas`.
- Data demo ditandai `is_demo` + prefiks `[DEMO]`; reset hanya menghapus data demo.
- Rincian lengkap dan daftar bug view vs fitur yang diperbaiki: [`17-Pembaruan-P1-dan-Audit-View.md`](17-Pembaruan-P1-dan-Audit-View.md).
