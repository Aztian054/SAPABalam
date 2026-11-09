# 07 — Loket Penyerahan

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** Fase 3 — **Antrian Penyerahan** (cari → ADD), **Tiket Saya**, dan **SELESAI** dengan form serah terima (`penerima_nama`, `penerima_hubungan`, `penerima_no_identitas`, `diserahkan_pada`) sudah ada; tiket menjadi `selesai` + `tanggal_selesai`. Belum: cetak tanda terima, rekap durasi layanan, arsip otomatis.

---

## 1. Peran

Loket Penyerahan adalah **penutup alur**. Tugasnya menyerahkan berkas yang sudah selesai diproses back office kepada pemohon, lalu menandai tiket **SELESAI**.

Aturan kunci (Keputusan #2): Loket Penyerahan hanya punya satu aksi penutup — **SELESAI**. **Tidak ada Tolak.**

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu: antrean siap serah, sedang saya pegang, diserahkan hari ini |
| Antrian Penyerahan | Cari + ADD (E.4 versi penyerahan) |
| Tiket Saya | Tiket yang saya ADD, belum saya SELESAI-kan |
| Daftar Selesai | Riwayat penyerahan (E.8) |
| Laporan | Cetak/export |

---

## 3. Alur Kerja

```text
Back office menyelesaikan seluruh pekerjaan
   -> tiket kembali ke Database (di_database)
   -> berkas fisik tiba di Loket Penyerahan

Pemohon datang membawa bukti pendaftaran
   -> petugas buka Antrian Penyerahan
   -> ketik/cari No Tiket -> baris muncul
   -> ADD  (tiket dipegang saya)
   -> cocokkan identitas pemohon (KTP vs data tiket)
   -> serahkan berkas
   -> klik SELESAI  (wajib: nama penerima + catatan opsional)
        |
        v
status = selesai -> Daftar Selesai -> otomatis Arsip setelah N hari
```

**Aturan penting**
- ADD hanya untuk tiket `di_database` yang **sudah dinyatakan tuntas** oleh back office.
- Bila berkas belum lengkap/tidak ditemukan, petugas **tidak menolak** — gunakan catatan internal dan koordinasi dengan Seksi 1/2 (Lepas Tiket Admin sudah dihapus).
- SELESAI mengubah status final dan tidak bisa dibatalkan; koreksi hanya lewat Admin dengan catatan.

---

## 4. Antrian Penyerahan (E.4 versi penyerahan)

Kosong sampai ada pencarian (No Tiket / No Hak / NIK).

| No | No Tiket | Pemohon | Jenis | No Hak/Sertipikat | Status | Dipegang Oleh | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | HM.123/Kedaton | ⚪ Di Database | — | **ADD** |
| 2 | JP26102026002 | Sari | Pemecahan | HGB.55/Rajabasa | 🔵 Diproses | Dedi (Loket Penyerahan) | ADD (nonaktif) |

Filter: rentang tanggal daftar, jenis permohonan, pencarian pintar.

Keterangan tombol nonaktif: "Sedang dipegang {nama}".

---

## 5. Tiket Saya

Tanggal acuan: **Waktu ADD**.

| No | No Tiket | Pemohon | Jenis | Waktu ADD | Lama | Status | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | 26/10 13:05 | 15 menit | 🔵 Diproses | **Serahkan** |

### 5.1 Form Serah Terima (wajib sebelum SELESAI)

| Field | Aturan |
|---|---|
| Nama penerima | Wajib — bisa pemohon sendiri atau kuasa |
| Hubungan | Pemohon / Kuasa / Keluarga |
| No identitas penerima | Wajib bila kuasa |
| Catatan | Opsional |
| Tanggal serah | Otomatis = sekarang |

Setelah submit: status `selesai`, `tanggal_selesai` terisi, riwayat mencatat pelaku.

### 5.2 Bila pemohon tidak datang

- Tiket tetap di **Tiket Saya** selama dipegang.
- Bila petugas berhalangan (cuti/pindah), berkas tetap tercatat sampai dikembalikan lewat alur normal; **Lepas Tiket Admin sudah dihapus**.
- Tidak ada status "tidak diambil" di V2 — gunakan catatan.

---

## 6. Daftar Selesai (E.8)

Tanggal acuan: **Tgl Diserahkan**.

| No | No Tiket | Pemohon | Jenis | Tgl Daftar | Tgl Diserahkan | Durasi Total | Penerima | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | 24/10 09:00 | 26/10 13:20 | 2 hari 4 jam | Budi S. (Pemohon) | Detail |

- Muncul juga di Daftar Selesai Loket Pendaftaran, Staf 1–2, dan Kasi 1–2.
- Setelah N hari (Setting, default 30) tiket pindah otomatis ke **Arsip**.

---

## 7. Aksi yang TIDAK Tersedia

- **Tolak** tiket.
- Mengubah data pemohon/No Tiket.
- Memproses verifikasi, validasi, alih media.
- Mengubah status warkah.
- Membatalkan SELESAI.
- *(Dihapus)* Melepas tiket sendiri — aksi Admin ini sudah tidak tersedia.

---

## 8. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Tiket tuntas dari back office & menunggu penyerahan | Loket Penyerahan |
| ADD oleh petugas penyerahan | Admin (opsional) |
| SELESAI | Loket Pendaftaran, Staf terkait, Kasi seksi, Kakan (kartu) |

---

## 9. Cetak

- Tanda terima penyerahan berkas (dengan nama penerima & tanggal).
- Rekap penyerahan harian/bulanan.
- Rekap durasi layanan (dari Tgl Daftar ke Tgl Diserahkan).
