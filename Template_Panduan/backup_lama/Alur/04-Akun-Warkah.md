# 04 — Akun Warkah (Stage 3: Siapkan Data Pendukung)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Warkah** — penyedia data pendukung Buku Tanah (BT) & Surat Ukur (SU).

---

## 1. Peran Warkah

Warkah adalah **jabatan** yang menyiapkan data pendukung **Buku Tanah (BT) dan Surat Ukur (SU) sekaligus** untuk setiap tiket. Jabatan ini boleh dipakai oleh **banyak akun**; setiap akun hanya melihat & mengerjakan tiket di daftar pribadinya (Exclusive Claim per jabatan).

```
DB ADMIN ──▶ WARKAH (Stage 3) ║ VERIFIKATOR (Stage 2, paralel) ──▶ VALIDATOR (Stage 4A/4B)
```

> **Paralel:** Warkah bekerja **bersamaan** dengan Verifikator. Setelah Warkah selesai **dan menandai `diserahkan_ke_validator`**, tiket siap di-Add **kedua Validator** (BT & SU) — **tanpa menunggu Verifikator**.

---

## 2. Menu pada Sidebar Warkah

```
📋 STAGE 3: WARKAH
└── 📁 Lembar Kerja Warkah → /warkah
```

Halaman menyajikan seksi **Daftar (Antrian Aktif Saya) / Revisi Menunggu / Riwayat Diproses**.

---

## 3. Alur Kerja Warkah

```
1. Warkah mencari tiketnya di Daftar Tiket Admin → klik "Add"
   → tiket masuk Daftar Tiket Warkah milik akun sendiri.
2. Buka detail tiket → buka form "Lembar Kerja Warkah".
3. Menyiapkan data pendukung BT & SU.
4. Isi status sertipikat, status dokumen BT / SU, tanggal serah/kembali.
5. Aksi "Serahkan ke Validator" → status sertipikat `DISERAHKAN`
   + `diserahkan_ke_validator = true`.
6. Proses Selesai → tiket kembali ke Daftar Tiket Admin
   (siap di-Add kedua Validator — tanpa menunggu Verifikator).
```

---

## 4. Lembar Kerja Warkah

### 4.1 Status Progres

| Kolom | Isi |
|-------|-----|
| Berkas & Dataset | `belum` / `proses` / `selesai` |
| Data Sertipikat Buku Tanah (BT) | `belum` / `proses` / `selesai` |
| Data Sertipikat Surat Ukur (SU) | `belum` / `proses` / `selesai` |
| Sosialisasi / Pemberitahuan | `belum` / `proses` / `selesai` |
| Public Response / Tanggapan | `belum` / `proses` / `selesai` |

### 4.2 Keluaran Warkah (sesuai dokumen FINAL)

| Kolom | Keterangan |
|-------|------------|
| **Status Sertipikat** | `PROSES` (sedang disiapkan) / `DISERAHKAN` (sudah ke Validator) |
| **Status Dokumen BT** | `ada` / `tidak ada` |
| **Status Dokumen SU** | `ada` / `tidak ada` |
| **Tanggal Diserahkan** | Tanggal diserahkan ke Validator |
| **Tanggal Kembali** | Tanggal dokumen dikembalikan |
| **Jumlah Berkas** | Hitung jumlah berkas |
| **Jumlah Halaman** | Hitung jumlah halaman |
| **Gabungan / Combine** | Ya / Tidak — gabungan lebih dari satu bidang |
| **Berkas Dikembalikan** | Jumlah berkas yang dikembalikan |
| **Pengembalian Sementara** | Dokumen dikembalikan dulu, akan diserahkan lagi |
| **Keterangan Status** | Catatan status |

### 4.3 Serahkan ke Validator (`diserahkan_ke_validator`)

Aksi **"Serahkan ke Validator"** menandai status sertipikat `DISERAHKAN` dan flag `diserahkan_ke_validator = true`. Efeknya:

- **Validator Pra-BTel & Pra-SuEl langsung dapat meng-Add tiket dari DB Admin** — **tidak menunggu Verifikator**.
- Alih Media tetap terkunci sampai **SEMUA** tahap selesai.

Flag ini adalah **gerbang (Conditional Gateway)** pembuka Validator. Tanpa flag ini, Validator tidak menemukan tiket pada pencarian Validator.

### 4.4 Pengembalian Warkah Sementara

Mencatat jika berkas **dipinjam/dikembalikan sementara** (misal: dipinjam guna keperluan tahap lain) — ditandai `pengembalian_sementara = true`, lengkap dengan **tanggal kembali** dan **jumlah berkas dikembalikan**.

---

## 5. Kolom Utama Daftar Tiket Warkah

**No · Kode Tiket · Nomor Sertipikat · Nomor Sertipikat Dahulu · Tgl. Input Loket · Jenis Permohonan · Nama Petugas Warkah · Status Sertipikat · Tgl. Diserahkan ke Validator · Tgl. Kembali · Count · Combine**

Contoh: `1 · K/41/251124/1 · - · — · 14/10/2024 · PTPGT · Berry · DISERAHKAN · 26/11/2024 12.03 · — · 0 · DISERAHKAN_0`

---

## 6. Revisi dari Warkah

Jika data pendukung bermasalah, Warkah dapat mengembalikan tiket (ke DB Admin / Loket / Verifikator / Warkah) dengan catatan — kode pembetulan **P1, P2, P3, ...** (revisi internal Warkah).

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + konfigurasi alur paralel (Warkah ║ Verifikator; gate Validator via `diserahkan_ke_validator`).*