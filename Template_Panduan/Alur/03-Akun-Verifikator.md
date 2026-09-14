# 03 — Akun Verifikator (Stage 2: Verifikasi Berkas)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Verifikator** — pemeriksa kelengkapan berkas asli pemohon.

---

## 1. Peran Verifikator

Verifikator adalah **jabatan** (tidak dibedakan BT/SU) yang memeriksa kelengkapan dan keaslian berkas pemohon untuk **SEMUA jenis permohonan**. Jabatan ini boleh dipakai oleh **banyak akun**; setiap akun hanya melihat & mengerjakan tiket di daftar pribadinya (Exclusive Claim per jabatan).

```
DB ADMIN ──▶ VERIFIKATOR (Stage 2) ║ WARKAH (Stage 3, paralel) ──▶ VALIDATOR (Stage 4A/4B) ──▶ ...
```

> **Paralel:** Verifikator bekerja **bersamaan** dengan Warkah sejak tiket ada di DB Admin. Menyelesaikan verifikasi **tidak mengunci jalur Warkah** — Warkah tetap bisa menyiapkan data BT & SU. Validator baru terbuka setelah Warkah menandai `diserahkan_ke_validator` (tanpa menunggu Verifikator).

---

## 2. Menu pada Sidebar Verifikator

```
📋 STAGE 2: VERIFIKATOR
└── ✅ Verifikasi Berkas → /verifikator
```

Halaman Verifikasi Berkas menyajikan statistik & seksi **Daftar (Antrian Aktif Saya) / Revisi Menunggu / Riwayat Diproses**:

| Seksi | Isi |
|-------|-----|
| **Tersedia di DB Admin** | Jumlah tiket siap di-Add dari Database Admin |
| **Antrian Aktif Saya** | Tiket yang sedang dikerjakan akun ini |
| **Revisi Menunggu Saya** | Tiket yang perlu diperbaiki di Verifikator |
| **Riwayat Diproses** | Tiket yang pernah diproses (terakhir) |

---

## 3. Alur Kerja Verifikator

```
1. Verifikator mencari tiketnya di Daftar Tiket Admin
   (berdasarkan nomor tiket / nomor sertipikat — harus LENGKAP).
2. Klik "Add" → tiket masuk ke Daftar Tiket Verifikator milik akun tersebut.
   Exclusive Claim: tiket yang sudah di-Add akun Verifikator lain ditolak
   (anti duplikat per jabatan).
3. Buka detail tiket → periksa berkas fisik vs data input.
4. Pilih aksi:
   ├── ✅ Lengkap   → status verifikasi 'lengkap' → tiket kembali ke DB Admin
   │                  (salah satu syarat gate Alih Media; jalur Warkah/Validator tidak terblokir)
   ├── 🔄 Perbaikan → tiket kembali ke Loket dengan catatan
   │                  + Cetak Form Perbaikan
   ├── 🚫 Batal     → status = 'batal'
   └── 🧩 Konsul    → perlu konsultasi lebih lanjut
```

---

## 4. Aksi di Detail Tiket Verifikator

| Aksi | Deskripsi / Status yang Diubah |
|------|--------------------------------|
| **Simpan Progres** | Menyimpan hasil verifikasi sementara (belum final) |
| **Proses Selesai** | `verifikasi.status='lengkap'` → tiket kembali ke DB Admin |
| **Kembalikan (Revisi)** | Pilih tujuan: DB Admin / Loket / Verifikator / Warkah + tulis catatan revisi |
| **Cetak Form Perbaikan** | `/verifikator/{id}/print-perbaikan` — form perbaikan cetak |
| **Lepas** | Lepas tiket kembali ke DB Admin tanpa menyelesaikan |

### Keterangan Status Verifikasi

| Status | Keterangan |
|--------|------------|
| belum | Belum diperiksa |
| lengkap | Berkas lengkap — syarat tiket lanjut ke tahap berikutnya (Warkah paralel, tidak menunggu) |
| perbaikan | Berkas kurang, perlu diperbaiki (kembali ke Loket) |
| batal | Permohonan dibatalkan |
| konsul | Perlu konsultasi lebih lanjut |

---

## 5. Deteksi Double (Anti Duplikat per Jabatan)

Jika nomor tiket **sudah pernah di-Add/diverifikasi oleh akun Verifikator mana pun**, sistem **menolak input** dari akun Verifikator lain — tiket tidak dapat di-Add/diproses ulang secara duplikat di **jabatan yang sama** (Exclusive Claim).

---

## 6. Kolom Utama Daftar Tiket Verifikator

**No · Nomor Tiket · Tgl. Permohonan Masuk · Nama Pemohon · No. Telepon · Jenis Permohonan · Nomor Hak · Kelurahan · Nama Petugas Loket · Nama Verifikator · Tgl. Terima Berkas · Catatan 1-10 · Status Berkas · Tgl. Selesai Verifikasi · Cetak Form Perbaikan**

Contoh: `K/41/251124/1 · 14/10/2024 · GURUH SAMODRA · 08xx · PTPGT · M.1088/... · WAY LAGA · Baynur · ILA NOFRI · 14/10/2024 17.05 · — · PERBAIKAN · 15/10/2024 13.59 · Cetak`

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + konfigurasi alur paralel (Verifikator ║ Warkah).*