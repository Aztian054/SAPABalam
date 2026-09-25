# 05 — Akun Validator (Stage 4A: Pra-BTel & 4B: Pra-SuEl)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Validator** — tahap pertama yang **memisahkan bidang BT dan SU**.

---

## 1. Peran Validator

Validator bertugas memvalidasi kesesuaian data sebelum proses alih media. **Pemisahan BT/SU dimulai di stage ini:**

| Sub-bidang | Role | Yang Divalidasi |
|------------|------|-----------------|
| **Pra-BTel** | `validator_btel` | Kesesuaian data Buku Tanah (BT): **nomor, nama, luas** |
| **Pra-SuEl** | `validator_suel` | Kesesuaian data Surat Ukur (SU): **gambar ukur, luas, batas** |

> **Urutan bebas:** Validator Pra-BTel dan Pra-SuEl dapat dikerjakan **paralel/bebas urutan** — keduanya **mulai** saat Warkah menandai `diserahkan_ke_validator = true`. **Tidak menunggu Verifikator** (perubahan dari alur lama).
>
> Jabatan Validator boleh dipakai **banyak akun**; tiap akun hanya mengerjakan tiket di daftarnya sendiri (Exclusive Claim per jabatan).

---

## 4. Gate/Aturan di Kode (TiketFlowService.php)

```php
'validasi_btel' => ['warkah'],   // TIDAK tunggu Verifikasi - hanya Warkah
'validasi_suel' => ['warkah'],   // TIDAK tunggu Verifikasi - hanya Warkah
```

**Artinya:**
- Validator dapat Add tiket **hanya** jika Warkah selesai + flag `diserahkan_ke_validator = true`
- **Tidak menunggu Verifikasi** — ini perbedaan utama dari alur sebelumnya

```
WARKAH (diserahkan_ke_validator = true)
   ──▶ [Validator Pra-BTel] ──┐
   ──▶ [Validator Pra-SuEl] ──┴──▶ (Kedua selesai, tanpa menunggu Verifikator) ──▶ ALIH MEDIA (semua tahap selesai)
```

---

## 2. Menu pada Sidebar Validator

```
📋 STAGE 4A: VALIDATOR BT   📋 STAGE 4B: VALIDATOR SU
└── 🛡️ Validasi Pra-BTel    └── 🛡️ Validasi Pra-SuEl
    → /validator-bt             → /validator-su
```

Halaman menyajikan seksi **Daftar (Antrian Aktif Saya) / Revisi Menunggu / Riwayat Diproses**.

---

## 3. Alur Kerja Validator

```
1. Validator mencari tiketnya di Daftar Tiket Admin
   (nomor tiket / nomor sertipikat — harus LENGKAP).
   Pencarian hanya menampilkan tiket yang sudah diserahkan Warkah
   (`diserahkan_ke_validator = true`) — tanpa menunggu Verifikator.
2. Klik "Add" → tiket masuk daftar Validator milik akun sendiri.
3. Buka detail tiket → validasi data sesuai sub-bidang.
4. Isi kesimpulan validator & keterangan.
5. Proses Selesai → status tiket selesai di Pra-BTel / Pra-SuEl (kolom BT / SU)
   → tiket kembali ke Daftar Tiket Admin.
```

---

## 4. Aksi & Ceklis Validator

| Aksi | Keterangan |
|------|------------|
| **Proses Selesai** | Tandai validasi sub-bidang selesai |
| **Kembalikan (Revisi)** | Kembalikan ke DB Admin / Loket / Verifikator / Warkah dengan catatan |
| **Cetak Form Perbaikan** | Cetak form perbaikan |

Ceklis yang dikelola validator (sesuai dokumen Excel 04):

- **Diberikan kepada Petugas Alih Media** (`diberikan_ke_alih_media`)
- **Kembali ke Warkah Sementara** (`kembali_ke_warkah` + `catatan_kembali_warkah`)
- **Keterangan Validator** (`keterangan`)

**Kesimpulan Validator (BT & SU), Tgl. BT/SU Selesai, Ceklis, dan Keterangan** dikelola bersama kedua validator.

---

## 5. Kolom Utama Validator

**Pra-BTel (kolom BT):** No · Nomor Sertipikat · Nomor Tiket · Tgl. Diserahkan BT/SU · Petugas Warkah · Jenis Permohonan · **Nama Petugas BT** · **Status BT** · **Tgl. Selesai Validasi BT**

Contoh: `B.895/BERINGIN JAYA · K/28/141024/1 · 14/10/2024 15.07 · Berry · PERALIHAN JUAL BELI · Afriza (BT) · SELESAI · 15/10/2024 16.49`

**Pra-SuEl (kolom SU):** No · Nomor Sertipikat · Nomor Tiket · Tgl. Diserahkan BT/SU · Petugas Warkah · Jenis Permohonan · **Nama Petugas Validator SU** · **Status Validasi SU** · **Tgl. Selesai Validasi SU**

Contoh: `B.895/BERINGIN JAYA · K/28/141024/1 · 14/10/2024 15.07 · Berry · PERALIHAN JUAL BELI · Aprill (SU) · SELESAI · 15/10/2024 17.23`

---

## 6. Gate ke Alih Media

```
Gerbang Validator: tiket muncul di pencarian Validator setelah Warkah menandai
`diserahkan_ke_validator = true` (tanpa menunggu Verifikator).

Gate ke Alih Media (AND penuh) — tombol aksi Alih Media (Stage 5) terbuka hanya jika
SEMUA tahap selesai: verifikasi 'lengkap' + warkah selesai (+ diserahkan)
+ validasi BT selesai + validasi SU selesai.

Jika belum → tiket tetap di DB Admin, tombol aksi Alih Media TERKUNCI.
```

---

Dokumen ini bagian dari panduan alur LOKET 2026 - diselaraskan dengan kode sumber TiketFlowService.php v2.3 (17 September 2026).