# 04 — Akun Warkah (Stage 3: Siapkan Data Pendukung)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Warkah** — penyedia data pendukung Buku Tanah (BT) & Surat Ukur (SU).

---

## 1. Peran Warkah

Warkah adalah **jabatan** yang menyiapkan data pendukung **Buku Tanah (BT) dan Surat Ukur (SU) sekaligus** untuk setiap tiket. Jabatan ini boleh dipakai oleh **banyak akun**; setiap akun hanya melihat & mengerjakan tiket di daftar pribadinya (Exclusive Claim per jabatan).

```
DB ADMIN ──▶ WARKAH FASE 1 ──▶ VALIDATOR ║ SU ──▶ ALIH MEDIA ║ SU ──▶ WARKAH FASE 2 ──▶ SELESAI
                  ↑                                            |
                  └──────────── SEKUENSIAL ────────────────────┘
```

> **2 FASE SEKUENSIAL:**
> - **Fase 1:** Siapkan data → serahkan ke Validator (`diserahkan_ke_validator = true`)
> - **Fase 2:** Terima pengembalian dari Alih Media → **TRIGGER SELESAI TOTAL**

---

## 2. Menu pada Sidebar Warkah

```
📋 STAGE 3: WARKAH
└── 📁 Lembar Kerja Warkah → /warkah
```

Halaman menyajikan seksi **Daftar (Antrian Aktif Saya) / Revisi Menunggu / Riwayat Diproses**.

---

## 4. Gate/Aturan di Kode (TiketFlowService.php)

```php
'warkah' => ['verifikasi'],  // SEKUENSIAL: Warkah HARUS tunggu Verifikasi selesai

STATUS_PENGEMBALIAN = [
    'belum_diserahkan' => 'Belum Diserahkn',
    'dipinjam'         => 'Dipinjam (di Validator/Alih Media)',
    'dikembalikan'     => 'Berkas Telah Dikembalikan',  // ← TRIGGER SELESAI
];
```

---

## 5. Alur Kerja Warkah (2 FASE)

### Fase 1: Serahkan ke Validator
```
1. Cari tiket di DB Admin (gate: Verifikasi HARUS selesai)
2. Klik "Add" → masuk Daftar Tiket Warkah
3. Buka detail → Form Lembar Kerja Warkah
4. Siapkan data BT & SU → Isi status, tanggal, dsb
5. Aksi "Serahkan ke Validator" → status = 'DISERAHKAN', flag `diserahkan_ke_validator = true`
6. Klik "Selesai" → tiket ke DB Admin → Validator dapat Add
```

### Fase 2: Terima Pengembalian (TRIGGER SELESAI)
```
1. Setelah Alih Media BT & SU selesai, berkas dikembalikan ke Warkah
2. Warkah Add lagi → terima pengembalian → catat status_pengembalian = 'dikembalikan'
3. Klik "Selesai Fase 2" → TRIGGER: Tiket = SELESAI (100%)
```

> **PENTING:** Tanpa Fase 2, tiket TIDAK akan 100% meskipun Alih Media sudah selesai!

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

Jika data pendukung bermasalah, Warkah dapat mengembalikan tiket dengan catatan.

---

## 7. Perbedaan dari Alur Paralel Lama

| Aspek | Alur Paralel (Lama) | Alur 2 Fase (Baru v2.3) |
|-------|---------------------|-------------------------|
| Verif ↔ Warkah | Paralel | Sekuensial |
| Jumlah Fase | 1 fase | 2 fase |
| Trigger Selesai | Alih Media | Warkah Fase 2 + dikembalikan |
| Gate Validator | Via flag saja | Warkah selesai + flag |

---

Dokumen ini bagian dari panduan alur LOKET 2026 - diselaraskan dengan kode sumber TiketFlowService.php v2.3 (2 fase, 17 September 2026).