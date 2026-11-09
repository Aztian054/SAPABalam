# 16 — Notifikasi In-App

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sudah (Fase 4). Tabel `notifikasis`, `App\Services\NotifikasiService`, lonceng + polling 60 detik di layout, halaman Notifikasi, preferensi per tipe (`/notifikasi/preferensi`), dan scheduler pembersihan berjalan. Notifikasi email sudah dihapus dari kode; kolom `tikets.email_pemohon` dipertahankan tanpa UI.

---

## 1. Prinsip

Keputusan #9: seluruh pemberitahuan memakai **notifikasi in-app**. Tidak ada pengiriman email, tidak ada PDF perbaikan yang dilampirkan.

| Aspek | Aturan |
|---|---|
| Kanal | Lonceng di header + daftar notifikasi |
| Penerima | Akun internal (bukan pemohon) |
| Pemohon | Hanya lewat komunikasi langsung di loket |
| Real-time | Opsional; minimal polling ringan (60 detik) |
| Riwayat | Disimpan, tidak dihapus otomatis kecuali retensi Setting |

---

## 2. Model Data (Usulan)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `user_id` | bigint | Penerima |
| `tipe` | string | Kode peristiwa (lihat §4) |
| `judul` | string | Ringkas, maks. 80 karakter |
| `pesan` | text | Detail 1–2 baris |
| `url` | string | Tautan tujuan (route internal) |
| `objek_tipe` | string | `tiket` / `warkah` / `pengaduan` |
| `objek_id` | bigint | ID objek |
| `dibaca_pada` | timestamp null | Null = belum dibaca |
| `created_at` | timestamp | Waktu |

Indeks: `(user_id, dibaca_pada)`, `(objek_tipe, objek_id)`.

Tabel: `notifikasi` (bukan bawaan Laravel `notifications`, agar tidak mencampur kanal).

---

## 3. Antarmuka

| Bagian | Aturan |
|---|---|
| Lonceng | Badge angka notifikasi belum dibaca (maks. tampil `99+`) |
| Dropdown | 5 notifikasi terbaru + "Lihat semua" |
| Halaman daftar | Tab **Belum Dibaca** / **Semua**, pagination 20 |
| Baris | Ikon tipe, judul, pesan, waktu relatif ("3 menit lalu") |
| Klik | Tandai dibaca + buka `url` |
| Aksi massal | "Tandai semua dibaca" |
| Kosong | "Tidak ada notifikasi baru" |

Waktu relatif berubah ke tanggal absolut setelah 7 hari. Notifikasi belum dibaca diberi latar berbeda, bukan hanya titik warna.

---

## 4. Matriks Peristiwa

Format kode: `{objek}.{peristiwa}`.

| Kode | Peristiwa | Penerima | Judul | Tautan |
|---|---|---|---|---|
| `tiket.dibuat` | Tahap 4 selesai | Staf Seksi 1 & 2 | "Tiket baru di Database" | Detail tiket |
| `tiket.ploting_selesai` | Ploting KIRIM | Pembuat tiket (Loket Pendaftaran) | "Ploting selesai — lanjutkan pendaftaran" | Wizard Tahap 3 |
| `tiket.diambil` | ADD | Kasi seksi terkait | "Staf Anda mengambil tiket" | Detail tiket |
| `tiket.ditolak` | Tolak | Pembuat tiket + Kasi seksi + semua Staf Seksi yang pernah menangani | "Tiket ditolak" | Detail tiket |
| `tiket.ditolak_ulang` | ADD ULANG | Kasi seksi | "Tiket diproses kembali" | Detail tiket |
| `tiket.diteruskan` | Berkas berpindah akun (ADD ulang / ADD di Loket Penyerahan) | Semua Staf Seksi yang pernah menangani, kecuali pelaku | "Berkas ... kini di akun lain" | Detail tiket |
| `tiket.dilepas` | Lepas Admin (tidak lagi tersedia di UI) | Pemegang sebelumnya + Kasi | "Tiket dilepas Admin" | Detail tiket |
| `tiket.macet` | Melewati batas hari | Staf pemegang + Kasi + Admin | "Tiket macet" | Tiket Saya |
| `tiket.selesai` | SELESAI | Pembuat tiket + Staf terkait + Kasi | "Tiket selesai & diserahkan" | Detail tiket |
| `tiket.diarsipkan` | Arsip otomatis | Admin | "Rekap arsip harian" | Daftar Arsip |
| `warkah.diminta` | Permintaan dibuat | Petugas Warkah | "Permintaan warkah baru" | Permintaan Warkah |
| `warkah.siap` | Tandai Siap | Staf pemegang tiket + Kasi | "Warkah siap diambil" | Detail tiket |
| `warkah.dipinjam` | Serah terima | Peminjam | "Warkah dipinjam" | Berkas Pinjaman |
| `warkah.mendekati_batas` | H-1 | Peminjam + Petugas Warkah | "Warkah harus dikembalikan besok" | Berkas Pinjaman |
| `warkah.terlambat` | Melewati batas | Peminjam + Kasi + Petugas Warkah + Admin | "Warkah terlambat" | Berkas Pinjaman |
| `warkah.batas_diubah` | Ubah Batas Kembali | Peminjam | "Batas kembali diperpanjang" | Berkas Pinjaman |
| `pengaduan.masuk` | Kirim | Kasi unit tujuan | "Pengaduan baru" | Daftar Pengaduan |
| `pengaduan.diterima` | Terima | Pembuat pengaduan | "Pengaduan diterima" | Detail pengaduan |
| `pengaduan.dikembalikan` | Kembalikan | Pembuat pengaduan | "Pengaduan dikembalikan" | Detail pengaduan |
| `pengaduan.selesai` | Tutup | Pembuat pengaduan | "Pengaduan selesai" | Detail pengaduan |
| `pengaduan.terlambat` | Melewati batas tanggap | Kasi unit + Admin | "Pengaduan belum ditangani" | Daftar Pengaduan |
| `akun.staf_baru` | Staf Seksi baru ditambahkan | Kasi Seksi terkait | "Staf baru di seksi Anda" | Halaman Notifikasi |
| `akun.staf_nonaktif` | Staf Seksi dinonaktifkan | Kasi Seksi terkait | "Staf dinonaktifkan" | Halaman Notifikasi |
| `akun.staf_aktif` | Staf Seksi diaktifkan kembali | Kasi Seksi terkait | "Staf diaktifkan kembali" | Halaman Notifikasi |
| `akun.staf_pindah_seksi` | Staf Seksi pindah seksi | Kasi seksi lama & baru | "Staf pindah seksi" | Halaman Notifikasi |
| `akun.staf_dihapus` | Staf Seksi dihapus | Kasi Seksi terkait | "Staf dihapus dari seksi Anda" | Halaman Notifikasi |

---

## 5. Aturan Pengiriman

1. **Satu penerima satu baris** — penerima banyak dibuat sebagai banyak baris.
2. **Tanpa duplikat** — peristiwa yang sama untuk objek & penerima yang sama dalam 60 detik diabaikan.
3. **Jangan blokir transaksi** — pembuatan notifikasi setelah commit; kegagalan tidak membatalkan aksi utama.
4. **Hormati preferensi** — pengguna dapat mematikan tipe tertentu (mis. `tiket.macet` untuk Kakan).
5. **Hormati peran** — penerima hanya akun aktif dengan hak melihat objek tersebut.
6. **Privasi** — pesan tidak memuat NIK, alamat, atau isi berkas; hanya nomor tiket & ringkasan.

---

## 6. Preferensi Pengguna

| Pengaturan | Default |
|---|---|
| Notifikasi in-app aktif | Ya |
| Tipe yang bisa dimatikan | Macet, pengaduan terlambat, rekap arsip |
| Tipe wajib (tidak bisa dimatikan) | `tiket.ditolak`, `tiket.dilepas`, `tiket.diteruskan`, `tiket.selesai`, `akun.*`, `pengaduan.masuk` |
| Ringkasan harian | Nonaktif |

Preferensi disimpan per akun; perubahan berlaku pada notifikasi berikutnya.

Implementasi: tabel `notifikasi_preferensis` (`user_id`, `tipe`, `aktif`, unik per pengguna+tipe). Tipe tanpa baris dianggap aktif. Tipe wajib (`tiket.ditolak`, `tiket.dilepas`, `tiket.diteruskan`, `tiket.selesai`, seluruh `akun.*`, `pengaduan.masuk`) ditampilkan terkunci dan selalu dikirim. Halaman: `GET /notifikasi/preferensi`, simpan: `POST /notifikasi/preferensi`.

---

## 7. Retensi & Pembersihan

| Aspek | Aturan |
|---|---|
| Retensi default | 90 hari (Setting) |
| Pembersihan | Scheduler harian menghapus notifikasi lama yang sudah dibaca |
| Notifikasi belum dibaca | Tidak dihapus otomatis |
| Audit | Kejadian asli tetap ada di Log meski notifikasi dihapus |

---

## 8. Implementasi Teknis (as-built)

| Bagian | Pendekatan |
|---|---|
| Tabel | `notifikasis` (`user_id`, `tipe`, `judul`, `pesan`, `url`, `objek_tipe`, `objek_id`, `dibaca_pada`) |
| Pembuatan | `App\Services\NotifikasiService::kirim(tipe, penerimaIds[], judul, pesan, url, objekTipe, objekId)` |
| Pemicu | Controller aksi (pendaftaran, seksi, penyerahan, warkah, pengaduan, akun, scheduler) |
| Transaksi | Dipanggil **setelah** `DB::transaction` selesai; kegagalan notifikasi tidak membatalkan aksi utama |
| Endpoint | `GET /notifikasi`, `GET /notifikasi/jumlah`, `POST /notifikasi/{id}/baca`, `POST /notifikasi/baca-semua` |
| Badge | Lonceng di layout + polling `GET /notifikasi/jumlah` tiap 60 detik |
| Deduplikasi | Tipe + objek + penerima yang sama dalam 60 detik diabaikan |
| Preferensi | `notifikasi_preferensis` per pengguna+tipe; default aktif, tipe wajib tidak dapat dimatikan |
| Penerima | Hanya akun `is_active = true`; id kosong/ditolak diabaikan |
| Staf terkait | `NotifikasiService::idStafTerkait($tiket, $kecuali)` — semua Staf Seksi aktif yang pernah muncul di `riwayat_statuses.changed_by` untuk tiket tersebut, kecuali pelaku saat ini |
| Retensi | `loket:bersihkan-notifikasi` (harian 01:00) menghapus notifikasi **sudah dibaca** lebih dari `config('loket.retensi_notifikasi_hari', 90)` |

Contoh pemanggilan:

```php
app(NotifikasiService::class)->kirim(
    tipe: 'tiket.ditolak',
    penerimaIds: array_merge([$tiket->created_by], $notifikasi->idKasiSeksi($user->seksi)),
    judul: "Tiket {$tiket->kode_tiket} ditolak",
    pesan: 'Berkas dikembalikan untuk perbaikan.',
    url: route('seksi.show', $tiket),
    objekTipe: 'tiket',
    objekId: $tiket->id,
);
```

> Catatan: `afterCommit` **tidak** dipakai karena pada `RefreshDatabase` transaksi pengujian tidak pernah commit sehingga notifikasi tidak akan pernah terkirim. Pemanggilan dilakukan eksplisit setelah closure transaksi.

---

## 9. Penggantian Email (Catatan Migrasi)

| Yang dihapus | Status |
|---|---|
| `App\Mail\TiketRevisiMail` | Dihapus |
| `TiketFlowService::sendRevisiEmail()` | Dihapus |
| `App\Services\FormPerbaikanPdf` | Dihapus |
| `resources/views/emails/` | Dihapus |
| Field email di form Loket/Admin | Dihapus dari UI & validasi |
| Kolom `tikets.email_pemohon` | **Dipertahankan** di DB, tanpa UI |
| `config/mail.php` | Tetap ada (bawaan Laravel), tidak dipakai |
| `config/services.php` | Dihapus (tidak ada layanan pihak ketiga) |
| `MAIL_*` & `AWS_*` di `.env` / `.env.example` | Dihapus |

Rute `verifikator.revisi-terkirim` sudah dihapus bersama alur lama; pemulihan berkas dilakukan lewat ADD ULANG (lihat `04-Staf-Seksi-1-2.md`). Lepas Tiket oleh Admin juga sudah dihapus.

---

## 10. Kriteria Selesai

- [x] Tabel `notifikasis` + model + relasi `User::notifikasis()`.
- [x] `NotifikasiService` dengan deduplikasi 60 detik (pemanggilan setelah transaksi).
- [x] Lonceng + halaman daftar + tandai dibaca (per item & semua).
- [x] Peristiwa di §4 terpicu: `tiket.dibuat`, `tiket.ploting_selesai`, `tiket.diambil`, `tiket.ditolak`, `tiket.ditolak_ulang`, `tiket.diteruskan`, `tiket.macet`, `tiket.selesai`, `tiket.diarsipkan`, `akun.staf_baru/nonaktif/aktif/pindah_seksi/dihapus`, `warkah.diminta/siap/dipinjam/mendekati_batas/terlambat/batas_diubah/dikembalikan`, `pengaduan.masuk/diterima/dikembalikan/selesai/terlambat`.
- [x] Preferensi pengguna tersimpan (`notifikasi_preferensis`, halaman `/notifikasi/preferensi`).
- [x] Scheduler retensi berjalan (`loket:bersihkan-notifikasi`, harian 01:00).
- [x] Tidak ada kode email tersisa (grep bersih).

**Belum (deferred):** ringkasan harian dan push real-time (WebSocket/SSE).
