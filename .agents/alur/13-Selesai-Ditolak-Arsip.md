# 13 — Selesai, Ditolak & Arsip

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sudah (Fase 4). Status `di_database`/`diproses`/`ditolak`/`selesai` berjalan; halaman **Daftar Arsip read-only** dan **arsip otomatis** setelah N hari sudah aktif. Rekap/ekspor PDF-CSV dan kartu ringkas Kakan belum dibuat.

---

## 1. Tiga Keadaan Akhir

| Keadaan | Status | Siapa yang menentukan | Sifat |
|---|---|---|---|
| Selesai | `selesai` | Loket Penyerahan (SELESAI) | Final |
| Ditolak | `ditolak` | Staf Seksi 1/2 (TOLAK) | Sementara — menunggu ADD ULANG |
| Arsip | turunan dari `selesai` | Sistem setelah N hari | Final, read-only |

---

## 2. Selesai

### 2.1 Syarat
- Berkas sudah melewati seluruh pekerjaan back office yang diperlukan.
- Berkas fisik tiba di Loket Penyerahan.
- Pemohon/kuasa hadir dengan bukti pendaftaran.

### 2.2 Proses
```text
Loket Penyerahan: ADD -> cocokkan identitas -> Serahkan -> SELESAI
   -> status selesai, tanggal_selesai terisi
   -> masuk Daftar Selesai (Loket Pendaftaran, Staf, Kasi, Loket Penyerahan)
   -> hitungan N hari menuju Arsip dimulai
```

### 2.3 Kolom Daftar Selesai (E.8)

Tanggal acuan: **Tgl Diserahkan**.

| No | No Tiket | Pemohon | Jenis | Tgl Daftar | Tgl Diserahkan | Durasi | Penerima | Diserahkan Oleh | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | 24/10 09:00 | 26/10 13:20 | 2h 4j | Budi S. (Pemohon) | Dedi | Detail |

Versi Staf menambah kolom **Peran Saya** dan **Hasil Saya**; versi Kasi menambah **Staf**.

### 2.4 Pembatalan
Selesai bersifat final. Bila terjadi kekeliruan, Admin mencatat koreksi di log dan (bila perlu) membuat tiket baru. Tidak ada "batalkan selesai" di UI.

---

## 3. Ditolak

### 3.1 Arti
Tiket **belum gagal** — hanya dikembalikan untuk perbaikan oleh staf yang menolak.

### 3.2 Aturan
| Aspek | Aturan |
|---|---|
| Penanggung jawab | Staf yang menolak (`penanggung_jawab_id`) |
| Staf lain | Tombol ADD **nonaktif** |
| Kembali ke database | Ya, tetapi terkunci untuk akun lain |
| ADD ULANG | Hanya penanggung jawab |
| Riwayat | Penolakan tetap tampil setelah ADD ULANG |
| Lepas Tiket | *(dihapus)* Admin tidak lagi dapat melepas tiket |

### 3.3 Kolom Daftar Ditolak (E.9)

Tanggal acuan: **Tgl Ditolak**.

| No | No Tiket | Pemohon | Jenis | Ditolak Oleh | Seksi | Tgl Ditolak | Alasan | Lama Menunggu | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | JP25102026004 | Doni | Roya | Rina Sari | 1 | 25/10 14:20 | Berkas tidak lengkap | 1 hari | 🔴 Menunggu Perbaikan | Detail · **ADD ULANG** |

Sub-tab: **Aktif** · **Sudah ditindaklanjuti**.

### 3.4 Siapa yang melihat
- Staf penolak (dengan ADD ULANG)
- Kasi seksi terkait
- Loket Pendaftaran (pemilik tiket)
- Admin & Super Admin
- Kakan (kartu ringkas, read-only)

---

## 4. Arsip

### 4.1 Aturan Otomatis
| Aspek | Aturan |
|---|---|
| Pemicu | `selesai` + N hari (Setting, default 30) |
| Proses | Terjadwal harian (scheduler) |
| Efek | Tiket masuk daftar Arsip; tetap bisa dibuka read-only |
| Warkah | Ikut diarsipkan; tidak bisa diubah |
| Pengaduan | Tidak diarsipkan (punya daftar Selesai sendiri) |

Implementasi: tiket **tetap** berstatus `selesai`; arsip adalah keadaan turunan dari `tikets.diarsipkan_pada`. Saat scheduler berjalan, sistem juga membuat baris `arsip_tiket` (folder "Arsip Otomatis") dan `RiwayatStatus` (`stage_ke = Arsip`) agar jejak audit tetap ada.

### 4.2 Kolom Arsip (E.11)

Tanggal acuan: **Tgl Selesai**.

| No | No Tiket | Pemohon | Jenis | Tgl Selesai | Tgl Diarsipkan | Lama Simpan | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP24092026001 | Sari | Pemecahan | 24/09 10:00 | 24/10 00:05 | 30 hari | Detail |

Filter: rentang tanggal selesai, jenis, pencarian pintar.

### 4.3 Siapa yang melihat
Loket Pendaftaran, Loket Ploting, Petugas Warkah, Admin, Super Admin. Staf/Kasi tidak punya menu Arsip (data tetap dapat dicari lewat pencarian pintar bila diperlukan).

### 4.4 Larangan
- Mengubah status tiket arsip.
- Menghapus arsip dari UI.
- Meminjam warkah yang sudah diarsipkan.

---

## 5. Diagram Keadaan

```text
                 +--> [selesai] --(N hari)--> [arsip]
                 |
[di_database] --+--> [diproses] --+--> [di_database] (KIRIM Selesai)
                 |                |
                 |                '--> [ditolak] (KIRIM Tolak)
                 |                        |
                 |                        '--> [diproses] (ADD ULANG)
                 |
                 '-----------------------> [di_database] (Lepas Admin)
```

---

## 6. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Tiket selesai | Loket Pendaftaran, Staf & Kasi terkait, Kakan (kartu) |
| Tiket ditolak | Loket Pendaftaran, Kasi seksi |
| Tiket ADD ULANG | Kasi seksi |
| Tiket diarsipkan | Admin (rekap harian) |

---

## 7. Laporan

- Rekap selesai per periode/seksi/jenis.
- Rekap ditolak: jumlah, alasan, staf, waktu tunggu perbaikan.
- Rekap arsip: jumlah per bulan, lama simpan.
- Export PDF/CSV dengan rentang tanggal tercetak.

> Export PDF/CSV **belum diimplementasikan** (deferred Fase 4). Rekap arsip bulan berjalan tampil sebagai kartu ringkasan di `arsip.index`.

---

## 8. Implementasi (as-built)

| Bagian | Lokasi kode |
|---|---|
| Arsip otomatis | `App\Console\Commands\ArsipkanTiketSelesai` (`loket:arsipkan-tiket`, harian 00:05) |
| Scheduler | `routes/console.php` |
| Penanda arsip | `tikets.diarsipkan_pada` + scope `Tiket::arsip()` / `siapDiarsipkan()` |
| Folder fisik | `arsip_tiket` (dibuat lewat `firstOrCreate` folder "Arsip Otomatis") |
| Daftar & detail | `ArsipController@index/show`, `resources/views/arsip/**` (tanpa tombol aksi) |
| Akses | Loket Pendaftaran, Loket Ploting, Petugas Warkah (+ Admin/Super Admin bypass middleware) |
| Notifikasi rekap | `tiket.diarsipkan` ke Admin setiap scheduler menemukan kandidat |
| Retensi | `config('loket.hari_arsip', 30)`; opsi `--hari=` untuk override manual |

**Belum (deferred):** ekspor PDF/CSV, kartu ringkas Kakan, pengarsipan manual per folder lewat UI baru (alur lama `admin.arsipkan` masih ada).
