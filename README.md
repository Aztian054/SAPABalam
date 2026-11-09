# LOKET 2026

Sistem pelayanan berkas permohonan & pengaduan pertanahan — Kantah BPN Kota Bandar Lampung.

Aplikasi Laravel untuk mencatat perjalanan berkas dari **pendaftaran di loket** sampai **penyerahan ke pemohon**, termasuk warkah (berkas fisik BT/SU) dan pengaduan masyarakat.

---

## Dokumentasi

Dokumen utama ada di folder [`.agents/`](.agents/README.md).

| Dokumen | Isi |
|---|---|
| [`.agents/prd.md`](.agents/prd.md) | PRD 7 bagian: masalah, tujuan, pengguna, user story, kebutuhan fungsional & non-fungsional, ruang lingkup |
| [`.agents/alur/README.md`](.agents/alur/README.md) | Indeks 16 dokumen alur + jalur baca per peran |
| [`.agents/alur/00-Blueprint-Utama.md`](.agents/alur/00-Blueprint-Utama.md) | Ringkasan alur target, status, matriks menu/daftar |
| [`.agents/alur/BLUEPRINT_REFISI_V2.md`](.agents/alur/BLUEPRINT_REFISI_V2.md) | Blueprint desain mentah |

> Dokumen alur menggambarkan **Blueprint V2** yang sudah diterapkan penuh: 12 jabatan V2 + `seksi`, status tiket V2, wizard 4 tahap dengan **No Tiket otomatis** dan **No. Berkas manual**, back office Seksi 1/2 (Cari → ADD → KIRIM, ADD ULANG, pantauan Kasi), Loket Penyerahan (ADD → SELESAI), Pengaduan LP, warkah pinjam fisik, notifikasi in-app, arsip otomatis, dan laporan. **Admin bersifat read-only untuk berkas** — hanya monitoring daftar berkas/selesai/pengaduan plus manajemen akun, form, setting, folder arsip, dan log. Landing page tracking publik sudah dihapus — halaman awal adalah **login**. Sisa yang belum dibuat hanya bersifat pelengkap: unggah lampiran, cetak bukti/rekap, ekspor PDF/CSV, dan push real-time. Status per modul dirangkum di `00-Blueprint-Utama.md` §9.

---

## Alur Ringkas (Target)

```text
LOKET PENDAFTARAN (wizard 4 tahap)
   -> [ploting bila belum] LOKET PLOTING
   -> Database (di_database)
   -> STAF SEKSI 1/2: cari -> ADD -> proses -> KIRIM (Selesai / Tolak)
        Tolak -> kembali ke staf penolak -> ADD ULANG
   -> LOKET PENYERAHAN: ADD -> SELESAI
   -> Daftar Selesai -> Arsip otomatis setelah N hari

Paralel: WARKAH (BT/SU) — permintaan otomatis dari Tahap 4,
         pinjam fisik, batas kembali, penanda terlambat.

Terpisah: PENGADUAN (LP) — Loket Pengaduan -> Kasi unit -> selesai.
```

---

## Kebutuhan Sistem

- PHP 8.3
- Composer
- Node.js & npm
- MySQL 8

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`, lalu:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Untuk pengembangan:

```bash
composer run dev
```

## Pengujian

```bash
php artisan test --compact
```

## Format Kode

```bash
vendor/bin/pint --dirty --format agent
```
