# 06 — Akun Alih Media (Stage 5A: Pra-BTel & 5B: Pra-SuEl)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Alih Media** — pemrosesan akhir berkas menjadi sertifikat elektronik.

---

## 1. Peran Alih Media

Alih Media memproses berkas menjadi **sertifikat elektronik** (alih media). Terbagi dua sub-bidang **KHUSUS bidang masing-masing:**

| Sub-bidang | Role | Proses |
|------------|------|--------|
| **Pra-BTel** | `alih_media_btel` | Scan BT, upload KKP, TTD elektronik, cetak tim |
| **Pra-SuEl** | `alih_media_suel` | Scan SU, upload KKP, TTD elektronik, cetak tim |

> ⚠️ **Gate AND penuh:** Mesin pencarian tetap menampilkan tiket, tetapi tombol aksi Alih Media **terbuka hanya jika SEMUA tahap selesai** — verifikasi `lengkap`, warkah selesai (+ `diserahkan_ke_validator`), validasi BT selesai, dan validasi SU selesai. Tidak cukup hanya kedua Validator selesai.
>
> Jabatan Alih Media boleh dipakai **banyak akun**; tiap akun hanya mengerjakan tiket di daftarnya sendiri (Exclusive Claim per jabatan).

---

## 4. Gate/Aturan di Kode (TiketFlowService.php)

```php
'alih_media_btel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
'alih_media_suel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
```

**Artinya:**
- Alih Media dapat Add tiket **HANYA** jika SEMUA tahap sebelumnya selesai (AND gate penuh)
- Tidak cukup hanya Validator BT & SU selesai — harus tunggu Verifikasi + Warkah juga

---

## 2. Menu pada Sidebar Alih Media

```
📋 STAGE 5A: ALIH MEDIA BT   📋 STAGE 5B: ALIH MEDIA SU
└── 📄 Alih Media BT         └── 📄 Alih Media SU
    → /alih-media-bt             → /alih-media-su
```

Halaman menyajikan seksi **Daftar (Antrian Aktif Saya) / Revisi Menunggu / Riwayat Diproses**.

---

## 3. Alur Kerja Alih Media

```
1. SEMUA tahap selesai (verifikasi, warkah, validasi BT & SU) → tombol aksi Alih Media terbuka.
2. Petugas Alih Media mencari tiket di Daftar Tiket Admin → klik "Add".
3. Buka detail tiket → proses alih media sub-bidangnya:
   ├── Scan BT / SU
   ├── Upload KKP
   ├── TTD Elektronik
   └── Cetak Tim (PILIH KODE CETAK)
4. Proses Selesai → tiket kembali ke Daftar Tiket Admin.
5. Setelah kedua sub-bidang (BT & SU) selesai → tiket berstatus SELESAI.
```

---

## 4. Aksi di Detail Tiket Alih Media

| Aksi | Keterangan |
|------|------------|
| **Proses Selesai** | Tandai alih media sub-bidang selesai |
| **Kembalikan (Revisi)** | Kembalikan ke DB Admin / Loket / Verifikator / Warkah dengan catatan |
| **Cetak Form Perbaikan** | Cetak form perbaikan |
| **Lepas** | Lepas tiket kembali ke DB Admin |

### Opsi lanjutan

- **Pengembalian BT/SU ke Ruang Warkah** (`pengembalian_bt_su`) — jika diperlukan.
- **Ceklis Ke Loket untuk SPS** (`ke_loket_sps`) + **Tanggal Kirim ke Loket U/SPS** (`tanggal_kirim_loket_sps`).
- **PILIH KODE CETAK** — pemilihan kode pencetakan dokumen (**Tim Hady** / **Tim Rendy**) beserta count list dan tanggal tim.

---

## 5. Kolom Utama Alih Media

**Pra-BTel (kolom BT):** No · Nomor Tiket · Nomor Sertipikat · Jenis Permohonan · Tgl. Selesai Validasi · **Nama Petugas Alih Media BT** · **Status Alih Media BT** · **Tgl. Selesai Alih Media BT** · **Kesimpulan Alih Media** · **Tgl. BT/SU Selesai**

Contoh: `K/28/141024/1 · B.895/BERINGIN JAYA · PERALIHAN JUAL BELI · 16/10/2024 10.04 · Olla (PHP) · SELESAI · 31/10/2024 11.45 · SELESAI · 29/10/2024 16.58`

Kolom cetak BT: **Cetak Tim Hady · Count List Tim Hady · Tgl. Tim Hady**.

**Pra-SuEl (kolom SU):** No · Nomor Tiket · Nomor Sertipikat · Jenis Permohonan · Tgl. Selesai Validasi · **Nama Petugas Alih Media SU** · **Status Alih Media SU** · **Tgl. Selesai Alih Media SU** · **Kesimpulan Alih Media** · **Tgl. BT/SU Selesai**

Contoh: `K/28/141024/1 · B.895/BERINGIN JAYA · PERALIHAN JUAL BELI · 16/10/2024 10.04 · Sri Sekar · SELESAI · 29/10/2024 16.58 · SELESAI · 29/10/2024 16.58`

Kolom cetak SU: **Cetak Tim Rendy · Count List Tim Rendy · Tgl. Tim Rendy**.

---

## 6. Gate-AND Selesai

```
Pra-BTel selesai? ─┐
                   ├──▶ KEDUANYA SELESAI ──▶ status = 'selesai' ──▶ DB Admin (dapat diarsipkan)
Pra-SuEl selesai? ─┘

Jika hanya satu yang selesai → tiket tetap menunggu, status bukan 'selesai'.

Gerbang masuk Alih Media (cerminan dari gate selesai): tombol aksi Alih Media
baru ada setelah SEMUA tahap sebelumnya selesai — verifikasi 'lengkap', warkah selesai
(+ diserahkan_ke_validator), validasi BT & SU selesai.
```

---

Dokumen ini bagian dari panduan alur LOKET 2026 - diselaraskan dengan kode sumber TiketFlowService.php v2.3 (17 September 2026).