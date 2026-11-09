# 15 — Komponen Umum (Tampilan, Pencarian, Filter)

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sebagian (komponen tabel & pencarian dasar ada; filter rentang tanggal sudah di Daftar Pendaftaran & Ploting; chip status V2 dan pagination seragam untuk modul lain belum).

---

## 1. Prinsip

Semua daftar di seluruh akun memakai **satu pola tampilan** agar pengguna berpindah menu tanpa belajar ulang.

```text
Judul halaman + breadcrumb
[ Kartu ringkasan ]   (angka kunci, bisa diklik = filter)
[ Bar filter ]        (pencarian pintar + rentang tanggal + filter khusus)
[ Tab ]               (bila daftar punya sub-keadaan)
[ Tabel ]             (No ... Aksi)
[ Pagination ]        20 baris/halaman
```

---

## 2. Kartu Ringkasan

| Aturan | Detail |
|---|---|
| Posisi | Di atas bar filter |
| Jumlah | 3–5 kartu per halaman |
| Isi | Angka + label + ikon |
| Interaksi | Klik kartu = menerapkan filter terkait |
| Keadaan aktif | Kartu terpilih diberi border/aksen |

Contoh (Dashboard Staf): Di Database · Tiket Saya · Ditolak · Macet · Selesai Bulan Ini.

---

## 3. Pencarian Pintar

Satu kolom input yang mencocokkan **beberapa field sekaligus**.

| Daftar | Field yang dicari |
|---|---|
| Permohonan | No Tiket, nama pemohon, NIK, No Hak, No Sertipikat |
| Ploting | No Tiket, nama pemohon, NIK, No HP |
| Warkah | No Tiket, nama pemohon, nama peminjam |
| Pengaduan | No Pengaduan, nama pengadu, No Tiket, isi ringkas |
| Arsip | No Tiket, nama pemohon, No Hak |

Aturan:
- Debounce 400 ms, minimum 2 karakter.
- Pencarian **case-insensitive** dan toleran spasi/tanda baca pada nomor.
- Menekan Enter langsung mencari tanpa menunggu debounce.
- Halaman **Cari + ADD** kosong sampai ada kata kunci.
- Kata kunci aktif ditampilkan sebagai chip yang bisa dihapus.

---

## 4. Filter Rentang Tanggal

**Wajib ada di semua daftar** (Keputusan #4d).

| Preset | Arti |
|---|---|
| Hari ini | 00:00–23:59 hari ini |
| 7 hari | 7 hari terakhir |
| 30 hari | 30 hari terakhir |
| Bulan ini | Tanggal 1 s/d akhir bulan |
| Bulan lalu | Bulan sebelumnya penuh |
| Kustom | Dua input tanggal (from–to) |

Aturan:
- Default per halaman ditentukan oleh **tanggal acuan** daftar tersebut (lihat `00` §8).
- Filter ikut tercetak di kop laporan.
- Filter tersimpan selama sesi; tombol **Reset** mengembalikan default.
- Tanggal memakai format tampilan `dd/mm/yyyy`, penyimpanan `Y-m-d`.

| Daftar | Tanggal acuan |
|---|---|
| Permohonan / Berkas | Tgl Daftar |
| Ploting menunggu | Waktu Kirim |
| Ploting selesai | Waktu Kirim |
| Tiket Saya | Waktu ADD |
| Permohonan Seksi | Waktu ADD |
| Warkah | Tgl Permintaan / Tgl Pinjam |
| Selesai | Tgl Diserahkan |
| Ditolak | Tgl Ditolak |
| Pengaduan | Tgl Buat |
| Arsip | Tgl Selesai |

---

## 5. Tabel

| Aturan | Detail |
|---|---|
| Kolom pertama | **No** (nomor urut halaman) |
| Kolom terakhir | **Aksi** |
| Klik baris | Membuka Detail (kecuali ada tombol di baris) |
| Header | Tetap terlihat saat scroll (sticky) pada layar besar |
| Kepadatan | Baris 44–52 px; teks 13–14 px |
| Kosong | Ilustrasi ringan + pesan + saran tindakan |
| Loading | Skeleton baris, bukan spinner penuh |

---

## 6. Chip Status

| Status tiket | Chip | Warna |
|---|---|---|
| `di_database` | Di Database | Abu netral |
| `diproses` | Diproses | Biru |
| `ditolak` | Ditolak | Merah |
| `selesai` | Selesai | Hijau |
| `macet` | Macet | Kuning (penanda) |
| `dilepas_admin` | Dilepas Admin | Abu bergaris |

| Status warkah | Chip |
|---|---|
| `diminta` | Oranye |
| `diterima` | Biru |
| `siap` | Hijau |
| `dipinjam` | Ungu |
| `terlambat` | Merah |
| `dikembalikan` | Abu |

| Status pengaduan | Chip |
|---|---|
| `terkirim` | Oranye |
| `diterima` | Biru |
| `dikembalikan` | Merah |
| `selesai` | Hijau |

Chip selalu memuat **teks**, bukan hanya warna (aksesibilitas).

---

## 7. Tombol & Aksi

| Jenis | Gaya | Contoh |
|---|---|---|
| Utama | Solid | ADD, KIRIM, SELESAI |
| Sekunder | Outline | Detail, Cetak |
| Berbahaya | Merah | Tolak, Kembalikan |
| Nonaktif | Abu + tooltip alasan | ADD saat dipegang akun lain |

Aturan:
- Maksimal **satu** tombol utama per baris.
- Aksi yang mengubah status wajib konfirmasi berisi ringkasan akibat.
- Aksi yang butuh catatan membuka modal, bukan halaman baru.
- Setelah aksi sukses: redirect + toast hijau; gagal: toast merah + pesan spesifik.

---

## 8. Pagination

- 20 baris/halaman (bisa diubah 20/50/100).
- Menampilkan "Menampilkan 1–20 dari 137".
- Nomor halaman dipertahankan saat kembali dari Detail.
- Filter & halaman disimpan di query string agar bisa dibagikan/di-refresh.

---

## 9. Detail Tiket (Halaman Bersama)

Struktur standar untuk semua akun:

| Bagian | Isi |
|---|---|
| Header | No Tiket, chip status, pemohon, jenis, No Hak |
| Aksi tahap | Tombol sesuai peran (ADD/Selesai/Tolak/Lepas/SELESAI) |
| Data permohonan | Biodata ringkas, letak tanah, jumlah bidang |
| Tabel bidang | NIB, No Sertipikat lama, luas, keterangan |
| Lembar kerja | Catatan tiap tahap (read-only di luar tahap aktif) |
| Warkah | Status BT/SU, peminjam, batas kembali |
| Diagram Alur | Timeline kejadian + pelaku + waktu |

Privasi: NIK disamarkan untuk Kakan; nama petugas hanya untuk peran yang berhak.

---

## 10. Notifikasi Toast

| Kejadian | Warna | Isi |
|---|---|---|
| Berhasil | Hijau | Ringkasan aksi + No Tiket |
| Peringatan | Kuning | Aksi berhasil tetapi ada catatan |
| Gagal | Merah | Alasan spesifik + langkah perbaikan |
| Info | Biru | Perubahan sistem/penugasan |

Durasi 4–6 detik, bisa ditutup, maksimal 3 tampil bersamaan.

---

## 11. Aksesibilitas

- Kontras teks minimal 4.5:1.
- Semua tombol/ikon punya label atau `aria-label`.
- Fokus keyboard terlihat; modal menangkap fokus dan bisa ditutup `Esc`.
- Tabel punya `<caption>`/`scope` yang layak.
- Status tidak disampaikan hanya lewat warna.

---

## 12. Cetak

| Aturan | Detail |
|---|---|
| Kop | Nama kantor + judul laporan + rentang tanggal + waktu cetak |
| Tabel | Tanpa tombol aksi, kolom penting saja |
| Warna | Hitam-putih ramah (chip jadi teks) |
| Footer | Nama pencetak + halaman |
| Format | A4 portrait; landscape untuk tabel lebar |
