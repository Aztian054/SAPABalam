# 03 — Akun Verifikator (Stage 2: Verifikasi Berkas)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Verifikator** — pemeriksa kelengkapan berkas asli pemohon.

---

## 1. Peran Verifikator

Verifikator adalah **jabatan** (tidak dibedakan BT/SU) yang memeriksa kelengkapan dan keaslian berkas pemohon untuk **SEMUA jenis permohonan**. Jabatan ini boleh dipakai oleh **banyak akun**; setiap akun hanya melihat & mengerjakan tiket di daftar pribadinya (Exclusive Claim per jabatan).

```
DB ADMIN ──▶ VERIFIKASI (Stage 2) ──▶ WARKAH (Stage 3)
                ↑                         ↑
                └──── SEKUENSIAL ─────────┘
```

> **Sekuensial:** Verifikator **DIDAHARUS selesai** dulu sebelum Warkah dapat memulai. Ini adalah perubahan dari alur paralel sebelumnya — sekarang Verifikasi dan Warkah berjalan secara berurutan sesuai kode di `TiketFlowService.php`:
> ```php
> 'warkah' => ['verifikasi']  // Warkah HARUS tunggu Verifikasi selesai
> ```

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

## 3. Alur Kerja Verifikator (SEKUENSIAL)

```
1. Verifikator mencari tiketnya di Daftar Tiket Admin
   (berdasarkan nomor tiket / nomor sertipikat — harus LENGKAP).
2. Klik "Add" → tiket masuk ke Daftar Tiket Verifikator milik akun tersebut.
   Exclusive Claim: tiket yang sudah di-Add akun Verifikator lain ditolak
   (anti duplikat per jabatan).
3. Buka detail tiket → periksa berkas fisik vs data input.
4. Pilih aksi:
   ├── ✅ Lengkap   → status verifikasi 'lengkaps' → tiket kembali ke DB Admin
   │                  → **BERHASIL → Warkah baru bisa mulai (GATE TERBUKA)**
   ├── 🔄 Perbaikan → tiket kembali ke Loket dengan catatan
   │                  + Cetak Form Perbaikan
   ├── 🚫 Batal     → status = 'batal'
   └── 🧩 Konsul    → perlu konsultasi lebih lanjut
```

**Catatan Penting (SEKUENSIAL):**
- Tiket **TIDAK bisa** di-Add oleh Warkah sebelum Verifikator selesai
- Ini berbeda dari alur paralel sebelumnya — sekarang ada keterlibatan ketat antar-tahap

---

## 4. Gate/Aturan di Kode (TiketFlowService.php)

```php
public const STAGE_GATES = [
    'verifikasi' => [],           // Dari Loket - tidak ada gate
    'warkah' => ['verifikasi'],  // Warkah WAIT Verifikasi - SEKUENSIAL!
    // ...
];
```

**Artinya:**
- ✅ Verifikator dapat Add tiket **sepenuhnya bebas** (dari Loket/diterima)
- ⚠️ Warkah **TIDAK bisa** Add tiket jika Verifikasi belum selesai

---

## 5. Aksi di Detail Tiket Verifikator

| Aksi | Deskripsi / Status yang Diubah |
|------|--------------------------------|
| **Simpan Progres** | Menyimpan hasil verifikasi sementara (belum final) |
| **Proses Selesai** | `verifikasi.status='lengkaps'` → tiket kembali ke DB Admin → Warkah dapat mulai |
| **Kembalikan (Revisi)** | Pilih tujuan: DB Admin / Loket / Verifikator / Warkah + tulis catatan revisi |
| **Cetak Form Perbaikan** | `/verifikator/{id}/print-perbaikan` — form perbaikan cetak |
| **Lepas** | Lepas tiket kembali ke DB Admin tanpa menyelesaikan |

### Keterangan Status Verifikasi

| Status | Keterangan |
|--------|------------|
| belum | Belum diperiksa |
| lengkap | Berkas lengkap — **Warkah baru bisa dimulai** (gate terbuka) |
| perbaikan | Berkas kurang, perlu diperbaiki (kembali ke Loket) |
| batal | Permohonan dibatalkan |
| konsul | Perlu konsultasi lebih lanjut |

---

## 6. Deteksi Double (Anti Duplikat per Jabatan)

Jika nomor tiket **sudah pernah di-Add/diverifikasi oleh akun Verifikator mana pun**, sistem **menolak input** dari akun Verifikator lain — tiket tidak dapat di-Add/diproses ulang secara duplikat di **jabatan yang sama** (Exclusive Claim).

---

## 7. Kolom Utama Daftar Tiket Verifikator

**No · Nomor Tiket · Tgl. Permohonan Masuk · Nama Pemohon · No. Telepon · Jenis Permohonan · Nomor Hak · Kelurahan · Nama Petugas Loket · Nama Verifikator · Tgl. Terima Berkas · Catatan · Status Berkas · Tgl. Selesai Verifikasi · Cetak Form Perbaikan**

---

## 8. Perbedaan dari Alur Paralel Lama

| Aspek | Alur Paralel (Lama) | Alur Sekuensial (Baru - KODE v2.3) |
|-------|---------------------|-------------------------------------|
| Verifikasi ↔ Warkah | Paralel (bisa bersamaan) | **Sekuensial** (Verif harus selesai dulu) |
| Gate Warkah | Bebas, tanpa tunggu Verifikasi | **HARUS tunggu Verifikasi selesai** |
| Alasan | Efisiensi waktu | **Konsistensi data** - berkas diverifikasi dulu sebelum diproses Warkah |

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan kode sumber `TiketFlowService.php` v2.3 (alur sekuensial 17 September 2026).*