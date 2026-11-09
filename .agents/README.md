# .agents — Dokumentasi Proyek LOKET 2026

Folder ini menyimpan dokumen acuan dan aset kerja agen untuk aplikasi LOKET 2026 (Pertanahan Kota Bandar Lampung).

---

## 1. Isi Folder

| Path | Isi |
|---|---|
| [`prd.md`](prd.md) | PRD 7 bagian: masalah, tujuan, pengguna, user story, kebutuhan fungsional, non-fungsional, ruang lingkup |
| [`alur/`](alur/README.md) | 16 dokumen alur V2 (00–16, tanpa 14) + blueprint mentah |
| [`alur/BLUEPRINT_REFISI_V2.md`](alur/BLUEPRINT_REFISI_V2.md) | Sumber acuan desain (mentah) |
| [`skills/`](skills/) | Skill/panduan kerja agen |

---

## 2. Mulai dari Mana

| Kebutuhan | Baca |
|---|---|
| Memahami produk & ruang lingkup | [`prd.md`](prd.md) |
| Memahami alur end-to-end | [`alur/00-Blueprint-Utama.md`](alur/00-Blueprint-Utama.md) |
| Mengerjakan satu peran | [`alur/README.md`](alur/README.md) §2 (jalur baca per peran) |
| Tahu apa yang sudah ada di kode | [`alur/00-Blueprint-Utama.md`](alur/00-Blueprint-Utama.md) §9 |

---

## 3. Status Dokumen

- Dokumen `alur/00`–`16` menggambarkan **Blueprint V2** yang sudah diterapkan; halaman tracking publik sudah dihapus.
- Setiap dokumen mencantumkan status implementasi di bagian atas.
- Bila dokumen dan kode berbeda, **kode adalah kenyataan**; dokumen adalah arah yang dituju.
- Notifikasi email sudah dihapus dari kode dan dokumen; penggantinya notifikasi in-app (`alur/16`).

---

## 4. Aturan Penulisan

- Bahasa Indonesia, istilah teknis memakai kode `inline` (mis. `di_database`, `loket_penyerahan`).
- Rujuk tabel Blueprint sebagai `E.x`, bukan menyalin ulang seluruh isi.
- Jangan menuliskan fitur sebagai "sudah ada" tanpa memeriksa kode.
- Dokumen baru harus ditambahkan ke indeks [`alur/README.md`](alur/README.md).

---

## 5. Struktur Aplikasi (Ringkas)

| Bagian | Lokasi |
|---|---|
| Controller | `app/Http/Controllers` |
| Service | `app/Services` (notifikasi, nomor tiket) |
| Model | `app/Models` |
| View | `resources/views` |
| Route | `routes/web.php` |
| Migrasi | `database/migrations` |
| Test | `tests/Feature` |
