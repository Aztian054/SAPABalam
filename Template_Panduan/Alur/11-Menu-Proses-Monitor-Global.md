# 11 — Database Tiket Admin (Monitoring Global)

Dokumen ini menjelaskan **Daftar Tiket Admin (Database)** — basis data terpusat sekaligus pusat pengawasan seluruh tiket untuk **Admin** (dan monitoring view-only untuk **Pemimpin**).

> ⚠️ **Koreksi dari versi sebelumnya:** dalam aplikasi saat ini Admin **TIDAK dapat meng-edit atau menghapus tiket secara langsung** — route `edit/delete` tiket memang tidak tersedia untuk Admin (`routes/web.php` → grup `admin.*`). Admin hanya dapat **melihat, membuka detail, membuat tiket baru, dan mengarsipkan**.

---

## 1. Daftar Tiket (Database)

Menu **Daftar Tiket Admin** (`GET /admin`) adalah **basis data terpusat seluruh tiket** dari semua akun Loket. Setiap tahapan terhubung ke database ini melalui **mesin pencarian nomor tiket** (`search` di 6 grup stage).

| Fungsi | Route | Deskripsi |
|--------|-------|-----------|
| **Lihat semua tiket** | `GET /admin` | Seluruh database tiket dari semua akun Loket |
| **Buka detail** | `GET /admin/tiket/{id}` | Posisi tiket, penanggung jawab tiap tahap, catatan final |
| **Tambah Tiket** | `GET/POST /admin/tambah-tiket` | Membuat tiket baru langsung dari akun Admin |
| **Arsipkan (single)** | `POST /admin/tiket/{id}/arsipkan` | Pindahkan satu tiket ke Arsip Folder |
| **Arsipkan (massal)** | `POST /admin/selesai/arsipkan-massal` | Pindahkan banyak tiket sekaligus ke Arsip |
| ~~Edit / Hapus tiket~~ | ❌ tidak ada | Admin **tidak** punya tombol Edit/Hapus tiket |

---

## 2. Fitur Tambah Tiket

Admin dapat membuat tiket baru **langsung dari akun Admin** (`/admin/tambah-tiket`), selain tiket yang dibuat oleh akun Loket.

---

## 3. Monitoring & Navigasi Terkait

| Menu | Route | Isi |
|------|-------|-----|
| **Tiket Selesai** | `GET /admin/selesai` | Seluruh tiket `selesai`; Admin bisa **pindah ke Arsip** satu per satu atau **massal** |
| **Revisi Perbaikan** | `GET /admin/revisi` + `POST /admin/revisi/{id}/hapus` | Semua revisi dari semua akun. Admin dapat **melihat dan menghapus** entri revisi; **tidak ada edit revisi** |
| **Arsip Folder** | `GET/POST /admin/arsip` | Buat folder → tambahkan tiket selesai → export |
| **Manajemen Akun** | `GET/POST /admin/users` + `POST /admin/users/{id}/toggle` | Tambah akun (label **"Jabatan"** untuk role), ubah jabatan, **aktif/nonaktifkan akun** (toggle, real-time) |
| **Export / Cetak** | `/reports`, `/reports/print`, `/reports/export`, `/reports/print-rapi` | Laporan & Rekap SLA untuk semua role |

---

## 4. Akses

| Role | Akses Database Tiket Admin |
|------|----------------------------|
| `admin` | ✅ Penuh (lihat, detail, tambah, arsip; tanpa edit/hapus tiket) |
| `pemimpin` | ✅ View-only + print monitoring (`/pemimpin`) |
| Akun stage | ❌ Hanya pencarian (Search) + Add tiket terkait |

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + kesesuaian website riil (Admin tanpa edit/hapus tiket; arsip massal; revisi hapus; toggle akun).*