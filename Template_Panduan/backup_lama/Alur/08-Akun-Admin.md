# 08 — Akun Admin (Akses Penuh)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Admin** — role dengan akses paling penuh di sistem.

---

## 1. Peran Admin

Admin adalah **jabatan permanen** (minimal 1 akun bootstrap `admin`; boleh ditambah akun Admin lain) yang dapat:
1. **Melihat seluruh tiket dari semua tahap secara penuh** (monitoring database).
2. Membuat tiket baru langsung dari akun Admin (**Tambah Tiket**).
3. Mengelola **Revisi** seluruh akun dan **Selesai/Arsip**.
4. **Menambah/mengedit/menghapus akun pengguna** (9 jabatan).
5. **Export** data (PDF/Excel).

---

## 2. Menu pada Sidebar Admin

```
ADMIN (DB Tiket Terpadu)
├── 🗄️ Daftar Tiket (Database)  → /admin
├── ➕ Tambah Tiket              → /admin/tambah-tiket
├── ✅ Tiket Selesai             → /admin/selesai
├── 🔄 Revisi (Perbaikan)        → /admin/revisi
├── 📦 Arsip Folder              → /admin/arsip
└── 👥 Manajemen Akun            → /admin/users
```

---

## 3. Fungsi Admin per Area

### 3.1 Dashboard & Daftar Tiket (Database)

- **Dashboard (Monitoring Database)** — ringkasan seluruh tiket dari semua akun Loket dalam format kartu/grafik; memuat tabel monitoring kolom **No · Kode Tiket · Warkah · Status Sertipikat · Tgl. Diserahkan Validator · Jenis Permohonan**.
- **Daftar Tiket (Database Seluruh Tiket)** — seluruh database tiket dapat dilihat, diedit, dihapus, atau diarsipkan.
- **Tambah Tiket** — fitur khusus membuat tiket baru langsung dari akun Admin (selain dari akun Loket).

### 3.2 Revisi

Berisi **semua revisi dari semua akun**. Admin dapat melihat, mengedit, dan **menghapus** revisi (`/admin/revisi/{id}/hapus`).

### 3.3 Selesai

Berisi semua tiket yang sudah selesai di semua stage. Tombol **"Pindahkan ke Arsip"** — tiket yang dipindahkan tidak lagi muncul di menu Selesai.

### 3.4 Arsip

- Menyimpan tiket yang telah diarsipkan Admin.
- **Alur:** Buat Folder → Tambahkan Tiket Selesai ke dalam Folder.
- Tombol **Export** tersedia (PDF & Excel).

### 3.5 Manajemen Akun

Menambah, mengedit, dan menghapus akun pengguna. Formulir penambahan akun memuat:

| Field | Keterangan |
|-------|------------|
| Nama | Nama lengkap |
| Username | Login |
| Email | Login alternatif |
| **NIP** | NIP petugas (rekap monitoring) |
| **No. HP** | Nomor HP petugas |
| Password | Kata sandi |
| **Jabatan (Role)** | 9 jabatan (admin, pemimpin, loket, verifikator, warkah, validator_btel, validator_suel, alih_media_btel, alih_media_suel) |

> **Catatan Manajemen Akun:**
> - Form penambahan akun menampilkan label **"Jabatan"** untuk pilihan role akun.
> - **Admin dapat mengubah jabatan akun kapan saja; perubahan berlaku real-time.**
> - Setiap jabatan boleh memiliki **banyak akun** (termasuk Loket, Verifikator, Warkah, Validator, dan Alih Media).
> - **NIP** dan **No. HP** tampil pada form, tetapi pada implementasi saat ini kolomnya **belum tersimpan** dari form ini (tersedia di skema database sebagai kolom `null`).

---

## 4. Admin sebagai "Super User"

Admin bisa mengakses detail tiket di stage mana pun dan membantu menyelesaikan tiket bermasalah.

> ⚠️ **Catatan:** Akses ini digunakan untuk **monitoring & intervensi**, bukan menggantikan tugas operasional tiap stage.

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + ketentuan banyak akun per jabatan & label "Jabatan" pada form Manajemen Akun.*