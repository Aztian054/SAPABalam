# Alur LOKET 2026 — Daftar Dokumen

Kumpulan dokumen alur **target** sesuai `BLUEPRINT_REFISI_V2.md`. Dokumen ini menggambarkan keadaan yang **dituju**, bukan seluruhnya keadaan kode hari ini. Status implementasi per modul dicatat di setiap dokumen dan dirangkum di [`00-Blueprint-Utama.md`](00-Blueprint-Utama.md) §9.

---

## 1. Daftar Dokumen

| No | Dokumen | Isi |
|---|---|---|
| 00 | [Blueprint Utama](00-Blueprint-Utama.md) | Ringkasan sistem, jabatan, alur utama, status, aturan kunci, matriks menu & daftar, status implementasi |
| 01 | [Landing, Login & Akses](01-Landing-Login-Akses.md) | Halaman masuk, RBAC jabatan/seksi, akun demo, logout |
| 02 | [Loket Pendaftaran](02-Loket-Pendaftaran.md) | Wizard 4 tahap, No Tiket otomatis, ploting, warkah otomatis |
| 03 | [Loket Ploting](03-Loket-Ploting.md) | Antrean ploting, proses, kirim, daftar selesai ploting |
| 04 | [Staf Seksi 1 & 2](04-Staf-Seksi-1-2.md) | Cari + ADD, Tiket Saya, Selesai/Tolak, ADD ULANG, warkah read-only |
| 05 | [Kasi Seksi 1 & 2](05-Kasi-Seksi-1-2.md) | Monitoring seksi, daftar ditolak, pengaduan seksi |
| 06 | [Petugas Warkah](06-Petugas-Warkah.md) | Permintaan, siap, pinjam fisik, batas kembali, terlambat |
| 07 | [Loket Penyerahan](07-Loket-Penyerahan.md) | Antrean penyerahan, serah terima, SELESAI |
| 08 | [Loket Pengaduan](08-Loket-Pengaduan.md) | Buat LP, kirim, kirim ulang |
| 09 | [Kasi 3/4/5 & TU](09-Kasi-3-5-dan-TU.md) | Penanganan pengaduan unit non-back-office |
| 10 | [Admin](10-Admin.md) | Monitoring berkas & pengaduan (read-only), akun, master, setting, log |
| 11 | [Super Admin](11-Super-Admin.md) | Demo, impersonate, data demo, backup |
| 12 | [Kakan](12-Kakan.md) | Monitoring read-only, laporan |
| 13 | [Selesai, Ditolak & Arsip](13-Selesai-Ditolak-Arsip.md) | Tiga keadaan akhir, aturan arsip otomatis |
| 15 | [Komponen Umum](15-Komponen-Umum.md) | Pola tabel, pencarian pintar, filter tanggal, chip, cetak |
| 16 | [Notifikasi In-App](16-Notifikasi-In-App.md) | Pengganti email, model data, matriks peristiwa |
| 17 | [Pembaruan P1 & Audit View](17-Pembaruan-P1-dan-Audit-View.md) | Implementasi P1 (setting, log, impersonate, backup, demo), audit bug view vs fitur, RWD + sidebar sticky scroll |
| — | [Blueprint V2 (mentah)](BLUEPRINT_REFISI_V2.md) | Sumber acuan desain |
| — | [PRD](../prd.md) | Dokumen kebutuhan produk 7 bagian |

---

## 2. Jalur Baca per Peran

### 2.1 Pengembang baru
1. `00` — pahami istilah & matriks
2. `15` — pola tampilan & filter
3. `04` → `05` → `07` — inti back office & penutup alur
4. `16` — notifikasi
5. `13` — keadaan akhir & arsip

### 2.2 Loket Pendaftaran
`01` → `02` → `03` → `13` → `15` → `16`

### 2.3 Loket Ploting
`01` → `03` → `02` (Tahap 2–3) → `15`

### 2.4 Petugas Warkah
`01` → `06` → `04` (bagian warkah) → `16`

### 2.5 Staf Seksi 1/2
`01` → `04` → `13` → `06` (read-only) → `16`

### 2.6 Kasi Seksi 1/2
`01` → `05` → `04` (konteks staf) → `08` (pengaduan) → `13`

### 2.7 Kasi 3/4/5 & TU
`01` → `09` → `08` → `16`

### 2.8 Loket Penyerahan
`01` → `07` → `13` → `15`

### 2.9 Loket Pengaduan
`01` → `08` → `09` → `16`

### 2.10 Admin / Super Admin
`01` → `10` → `11` → `00` → `15`

### 2.11 Kakan
`01` → `12` → `13` → `15`

### 2.12 Auditor / penerima serah terima
`00` → `10` (log) → `PRD`

---

## 3. Istilah Kunci

| Istilah | Arti |
|---|---|
| **Tiket** | Satu permohonan (nomor `JP...`) |
| **No. Berkas** | Nomor resmi pusat BPN, diisi manual oleh Loket Pendaftaran (bebas format, tidak unik) |
| **Database / Daftar Permohonan** | Antrean bersama tiket berstatus `di_database` |
| **ADD** | Mengambil tiket dari Database ke antrian pribadi |
| **ADD ULANG** | Mengambil kembali tiket yang **saya** tolak |
| **KIRIM** | Menutup pekerjaan tahap: Selesai (kembali ke Database) atau Tolak |
| **SELESAI** | Penutup alur oleh Loket Penyerahan |
| **Lepas Tiket** | *(dihapus)* Dulu: Admin mengembalikan tiket ke Database dari Seksi 1/2 |
| **Macet** | Penanda tiket melewati batas hari di satu stasiun |
| **Warkah** | Berkas fisik pendukung (BT/SU) yang dipinjam |
| **Ploting** | Penentuan bidang/letak tanah sebelum pendaftaran dilanjutkan |
| **LP** | Nomor pengaduan |

---

## 4. Konvensi Dokumen

| Konvensi | Arti |
|---|---|
| `[BELUM DIIMPLEMENTASI]` / "Implementasi kode: belum" | Fitur belum ada di kode |
| "sebagian" | Sebagian perilaku ada, belum sesuai target |
| Kode `inline` | Nama status, role, route, atau field nyata |
| Tabel E.x | Merujuk tabel pada `BLUEPRINT_REFISI_V2.md` bagian E |
| `->` | Langkah/alur berikutnya |
