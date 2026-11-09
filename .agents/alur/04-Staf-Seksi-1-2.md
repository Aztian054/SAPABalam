# 04 — Staf Seksi 1 & 2 (Back Office)

> **Status dokumen:** Blueprint V2 · **Implementasi kode:** sudah — stasiun **Cari → ADD → KIRIM (Selesai/Tolak)** di `seksi.*`, termasuk Peran/Bidang (`tikets.peran_bidang`), Tiket Saya, Daftar Ditolak + ADD ULANG, panel warkah pinjam fisik, dan notifikasi in-app (lihat `06-Petugas-Warkah.md` dan `16-Notifikasi-In-App.md`). Modul lama (`verifikator`, `validator_btel/suel`, `alih_media_btel/suel`) sudah dihapus dari kode dan database.

---

## 1. Peran

Staf Seksi 1/2 adalah **pelaksana back office** untuk verifikasi, validasi, dan alih media. Semua pekerjaan dilakukan di **satu stasiun** dengan pola:

```text
Cari -> ADD -> proses -> KIRIM (Selesai / Tolak) -> kembali ke Database
```

Yang membedakan antar-pekerjaan bukan role, melainkan **Peran/Bidang** yang dicatat pada tiket:

| Peran/Bidang | Contoh pekerjaan |
|---|---|
| Verifikasi | Kelengkapan & keaslian berkas |
| Validator BT | Kesesuaian data Buku Tanah |
| Validator SU | Kesesuaian data Surat Ukur |
| Alih Media BT | Scan/upload/TTD/cetak tim BT |
| Alih Media SU | Scan/upload/TTD/cetak tim SU |

**Berkas mengikuti posisi fisik.** Siapa pun dari Seksi 1/2 yang memegang berkas boleh ADD. Sistem **tidak memaksa urutan** antar seksi.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu: di database, tiket saya, ditolak, macet, selesai bulan ini |
| Daftar Permohonan | Halaman **cari + ADD** (kosong sampai ada pencarian) |
| Tiket Saya | Tiket yang saya ADD dan belum saya KIRIM |
| Daftar Warkah | Read-only (E.7d) |
| Daftar Selesai | Tiket yang pernah saya proses dan sudah selesai |
| Daftar Ditolak | Tiket yang **saya** tolak + ADD ULANG |
| Laporan | Cetak/export |

---

## 3. Halaman Cari + ADD

**Aturan penting:** daftar **kosong sampai ada pencarian** (No Tiket / No Hak / No Sertipikat). Filter tanggal tetap tersedia.

### 3.1 Kolom (E.4)

| No | No Tiket | Pemohon | Jenis | No Hak / Sertipikat | Status | Dipegang Oleh | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | HM.123/Kedaton | ⚪ Di Database | - | **ADD** |
| 2 | JP26102026005 | Sari | Pemecahan | HGB.55/Rajabasa | 🔵 Diproses | Andi / Validator / Seksi 2 | ADD (nonaktif) |
| 3 | JP25102026004 | Doni | Roya | HM.77/Tanjung Karang | 🔴 Ditolak | Rina (penanggung jawab) | ADD (nonaktif, kecuali Rina: **ADD ULANG**) |

Keterangan nonaktif ditampilkan sebagai teks di bawah tombol:
- "Sudah diambil oleh …"
- "Menjadi tanggung jawab …"

### 3.2 Aturan ADD

| Kondisi tiket | Tombol | Pesan bila diklik |
|---|---|---|
| `di_database` | **ADD** | — |
| `diproses` akun lain | nonaktif | "Sudah diambil oleh {nama} ({peran}/{seksi})" |
| `ditolak` akun lain | nonaktif | "Ditolak, menjadi tanggung jawab {nama}" |
| `ditolak` akun **saya** | **ADD ULANG** | — |
| `selesai` | nonaktif | "Tiket sudah selesai" |

Validasi juga di **server** (`user_id = penanggung_jawab_id` untuk ADD ULANG).

---

## 4. Tiket Saya & Aksi

Tanggal acuan: **Waktu ADD**. Hanya tiket yang saya ADD dan belum saya KIRIM.

| No | No Tiket | Pemohon | Jenis | Peran/Bidang | Waktu ADD | Lama di Saya | Status | Warkah | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | Alih Media / BT | 26/10 09:00 | 3 jam | 🔵 Diproses | BT: Dipinjam saya | **Proses** |

### 4.1 Panel Proses

| Aksi | Efek |
|---|---|
| **SELESAI + catatan** | Status kembali `di_database`; siap di-ADD stasiun berikutnya |
| **TOLAK + catatan** | Status `ditolak`; `penanggung_jawab = saya`; staf lain tidak bisa ADD |
| Simpan progres | Menyimpan catatan tanpa mengubah status |

**Catatan wajib** pada Selesai dan Tolak (minimal 3 karakter).

### 4.2 Perilaku setelah KIRIM

- Tiket hilang dari **Tiket Saya**.
- Bila Selesai → masuk **Daftar Selesai** hanya jika seluruh alur tuntas; bila belum, tiket menunggu di Database.
- Bila Tolak → masuk **Daftar Ditolak** saya dan Kasi seksi saya.
- Riwayat penolakan tetap tampil di Diagram Alur, termasuk setelah ADD ULANG.

---

## 5. Tiket Ditolak & ADD ULANG

```text
Saya klik TOLAK + catatan -> KIRIM
   -> status ditolak, penanggung_jawab = saya
   -> kembali ke Database, tetapi tombol ADD staf lain NONAKTIF
   -> muncul di: Daftar Ditolak saya, Daftar Ditolak Kasi, Daftar Ditolak Loket Pendaftaran, kartu Kakan
   -> notifikasi ke Loket Pendaftaran + Kasi seksi saya
Masalah fisik/administrasi diperbaiki
   -> saya buka Daftar Ditolak -> ADD ULANG
   -> status diproses, riwayat penolakan tetap tampil
   -> saya pilih Selesai / Tolak lagi
```

Bila saya cuti/pindah → berkas tetap tercatat atas nama saya sampai dikembalikan lewat alur normal (Admin tidak lagi punya aksi **Lepas Tiket**).

### 5.1 Kolom Daftar Ditolak (E.9)

| No | No Tiket | Pemohon | Jenis | Ditolak Oleh | Seksi | Tgl Ditolak | Alasan | Lama Menunggu | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | JP25102026004 | Doni | Roya | Rina Sari | Seksi 1 | 25/10 14:20 | Kurang lengkap | 1 hari | 🔴 Menunggu Perbaikan | Detail · **ADD ULANG** |

---

## 6. Daftar Warkah (Read-Only)

Tanggal acuan: **Tanggal Pinjam** (atau Tgl Permintaan bila belum dipinjam).

| No | No Tiket | Jenis | Status | Peminjam | Jabatan/Seksi | Tgl Pinjam | Batas Kembali | Sisa Waktu | Pengembali | Tgl Kembali |
|---|---|---|---|---|---|---|---|---|---|---|

- Hanya warkah untuk tiket yang **saya ADD** atau yang **dipinjam atas nama saya**.
- **Tidak ada tombol aksi** — perubahan hanya oleh Petugas Warkah.
- Filter: Status, BT/SU, Peminjam, **Terlambat saja**.

---

## 7. Daftar Selesai

| No | No Tiket | Pemohon | Jenis | Tgl Daftar | Tgl Diserahkan | Durasi | Diserahkan Oleh | Penerima | Aksi |
|---|---|---|---|---|---|---|---|---|---|

Isi: tiket yang pernah **saya proses** dan kini selesai. Tambahan kolom **Peran Saya** dan **Hasil Saya** (Selesai/Tolak terakhir).

---

## 8. Penanda Macet

- Batas hari di Setting (default **3 hari kerja**).
- Bila "Lama di Saya" melewati batas → baris merah + chip **MACET**.
- Notifikasi ke Staf, Kasi seksi, dan Admin.
- Tidak mengubah status tiket.

---

## 9. Aksi yang TIDAK Tersedia

- Mengubah data pemohon atau No Tiket.
- Mengubah status warkah (hanya baca).
- *(Dihapus)* Melepas tiket sendiri — dulu khusus Admin, kini tidak ada.
- Melakukan ADD untuk tiket yang ditolak akun lain.
- Melihat tiket di luar lingkup seksi.

---

## 10. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Saya ADD tiket | Kasi seksi saya |
| Saya Tolak | Loket Pendaftaran + Kasi seksi saya |
| Saya ADD ULANG | Kasi seksi saya |
| Tiket macet | Saya + Kasi + Admin |
| Tiket selesai/diserahkan | Saya + Kasi |
| Warkah siap / terlambat | Saya (bila tiket saya) |

---

## 11. Cetak

- Detail tiket + Diagram Alur.
- Form permohonan perbaikan (bila menolak) — format kantor.
- Rekap pekerjaan pribadi (mengikuti filter).
