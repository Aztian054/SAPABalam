# 11 — Database Tiket Admin (Monitoring Global)

Dokumen ini menjelaskan **Daftar Tiket Admin (Database)** — basis data terpusat sekaligus pusat pengawasan seluruh tiket untuk **Admin**.

---

## 1. Daftar Tiket (Database)

Menu **Daftar Tiket Admin** (`/admin`) adalah **basis data terpusat seluruh tiket** dari semua akun Loket. Setiap tahapan terhubung ke database ini melalui **mesin pencarian nomor tiket**.

| Fungsi | Deskripsi |
|--------|-----------|
| **Lihat semua tiket** | Seluruh database tiket dari semua akun Loket |
| **Edit / Hapus** | Perbaiki data atau hapus tiket |
| **Tambahkan ke Arsip** | Arsipkan tiket (bulanan/mingguan/tahunan) |
| **Buka detail** | Lihat posisi tiket & penanggung jawab tiap tahap |

---

## 2. Fitur Tambah Tiket

Admin dapat membuat tiket baru **langsung dari akun Admin** (`/admin/tambah-tiket`), selain tiket yang dibuat oleh akun Loket.

---

## 3. Monitoring & Navigasi Terkait

| Menu | Isi |
|------|-----|
| **Tiket Selesai** (`/admin/selesai`) | Seluruh tiket selesai di semua stage; Admin dapat pindahkan ke Arsip |
| **Revisi Perbaikan** (`/admin/revisi`) | Semua revisi dari semua akun; Admin dapat melihat, mengedit, menghapus |
| **Arsip Folder** (`/admin/arsip`) | Buat folder → tambahkan tiket selesai → export PDF/Excel |

---

## 4. Akses

| Role | Akses Database Tiket Admin |
|------|----------------------------|
| `admin` | ✅ Ya (penuh) |
| `pemimpin` | ✅ Ya (view-only, print) |
| Akun stage | ❌ Hanya pencarian (Search) + Add tiket terkait |

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx`.*