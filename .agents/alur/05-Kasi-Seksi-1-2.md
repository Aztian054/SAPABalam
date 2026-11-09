# 05 — Kasi Seksi 1 & 2

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** Fase 2–3 — **Daftar Permohonan Seksi** (read-only, hanya tiket yang sudah di-ADD staf seksinya), Daftar Selesai, dan Daftar Ditolak sudah ada; penanganan **pengaduan seksinya** (terima/tutup/kembalikan) juga sudah. Belum: Daftar Warkah dan kartu pengaduan di dashboard.

---

## 1. Peran

Kasi Seksi 1/2 adalah **pengawas operasional seksi**, bukan pelaksana berkas. Tugasnya:

1. Memantau tiket yang **sudah di-ADD staf seksinya**.
2. Menangani **pengaduan** yang ditujukan ke seksinya.
3. Membaca rekap Selesai & Ditolak seksinya.

**Aturan kunci:** Kasi **tidak melihat** tiket yang masih `di_database` — tiket baru muncul setelah staf seksinya menekan ADD.

Kasi **tidak** bisa ADD, memproses, menolak, atau melepas tiket.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu: diproses seksi, ditolak, macet, selesai bulan ini, pengaduan masuk |
| Daftar Permohonan Seksi | Tiket yang di-ADD staf seksinya (E.5) |
| Daftar Warkah | Warkah terkait tiket seksinya (E.7d) |
| Daftar Selesai | Tiket seksinya yang sudah diserahkan (E.8) |
| Daftar Ditolak | Tiket yang ditolak staf seksinya (E.9) |
| Daftar Pengaduan | Pengaduan untuk seksinya (E.10) |
| Pengaduan Selesai | Riwayat pengaduan yang sudah ditutup |
| Laporan | Cetak/export per seksi |

---

## 3. Daftar Permohonan Seksi (E.5)

Tanggal acuan: **Waktu ADD**.

| No | No Tiket | Pemohon | Jenis | Staf | Peran | Waktu ADD | Lama | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | Andi | Alih Media / BT | 26/10 09:00 | 3 jam | 🔵 Diproses | Detail |
| 2 | JP25102026004 | Doni | Roya | Rina | Verifikasi | 25/10 14:20 | 1 hari | 🔴 Ditolak | Detail |
| 3 | JP25102026007 | Wati | Pemecahan | Andi | Validator / SU | 24/10 10:05 | 3 hari | 🟡 Macet | Detail |

**Isi baris**
- Hanya tiket dengan staf dari **seksi yang sama** dengan Kasi.
- Tiket `ditolak` tetap tampil selama belum ADD ULANG.
- Tiket yang sudah selesai dipindah ke Daftar Selesai.

**Filter:** rentang tanggal, staf, peran/bidang, status (Diproses / Ditolak / Macet), pencarian pintar.

**Aksi:** hanya `Detail` — tidak ada tombol yang mengubah status.

---

## 4. Panel Detail

Kasi melihat hal yang sama dengan staf, tanpa tombol aksi:

| Bagian | Isi |
|---|---|
| Header tiket | No Tiket, pemohon, jenis, No Hak, status, posisi |
| Diagram Alur | Riwayat lengkap termasuk penolakan dan ADD ULANG |
| Lembar kerja | Catatan tiap tahap (read-only) |
| Warkah | Status BT/SU + peminjam + batas kembali |
| Penanggung jawab | Nama staf, peran, waktu ADD |

Kasi **tidak** melihat data pemohon selengkap Admin (NIK disamarkan sebagian bila kebijakan kantor menghendaki); nomor HP tetap tampil untuk koordinasi.

---

## 5. Pengaduan Seksi

### 5.1 Alur

```text
Loket Pengaduan membuat pengaduan (LP...) -> pilih Seksi Tujuan = Seksi 1 / Seksi 2
        |
        v
Kasi seksi tujuan: notifikasi in-app -> buka Daftar Pengaduan
        |
        +-- TERIMA  -> status diterima, pengadu mendapat notifikasi
        |       -> selesai ditangani -> TUTUP (status selesai)
        |
        '-- KEMBALIKAN (salah tujuan / bukan kewenangan) + catatan
                -> status dikembalikan -> Loket Pengaduan -> KIRIM ULANG
```

### 5.2 Kolom Daftar Pengaduan (E.10)

| No | No Pengaduan | Pengadu | Jenis | Seksi Tujuan | Status | Catatan | Tgl Buat | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | LP26102026001 | Budi | Pelayanan | Seksi 1 | 🟠 Terkirim | — | 26/10 08:00 | **Terima** |
| 2 | LP26102026002 | Sari | Berkas | Seksi 1 | 🔵 Diterima | Menunggu dokumen | 25/10 13:10 | **Tutup** |

**Aksi:** `Terima` · `Tutup` · `Kembalikan` (wajib catatan).

### 5.3 Aturan

- Kasi hanya melihat pengaduan untuk seksinya.
- Pengaduan `dikembalikan` hilang dari daftar aktif dan muncul lagi setelah dikirim ulang.
- Semua aksi tercatat di riwayat pengaduan.

---

## 6. Penanda Macet

| Kondisi | Tampilan |
|---|---|
| Tiket dipegang staf seksinya melewati batas hari (Setting, default 3 hari) | Baris merah + chip **MACET** |
| Tiket ditolak dan belum ADD ULANG melewati batas | Chip **MENUNGGU PERBAIKAN** |

Kasi menerima notifikasi saat tiket seksinya menjadi macet, dan dapat menegur staf di luar sistem (tidak ada aksi paksa).

---

## 7. Daftar Selesai & Ditolak

### 7.1 Daftar Selesai (E.8) — versi Kasi

Kolom sama dengan versi Staf, ditambah **Staf** dan **Peran**:

| No | No Tiket | Pemohon | Jenis | Staf | Peran | Tgl Daftar | Tgl Diserahkan | Durasi | Aksi |
|---|---|---|---|---|---|---|---|---|---|

### 7.2 Daftar Ditolak (E.9) — versi Kasi

| No | No Tiket | Pemohon | Jenis | Ditolak Oleh | Seksi | Tgl Ditolak | Alasan | Lama Menunggu | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|---|

Tiket yang sudah ADD ULANG tetap tercatat di sini sebagai riwayat, tetapi ditandai **Sudah ditindaklanjuti**.

---

## 8. Aksi yang TIDAK Tersedia

- ADD / ADD ULANG tiket.
- Menyetujui atau menandatangani hasil verifikasi (tidak ada Persetujuan Akhir di V2).
- Mengubah catatan staf.
- *(Dihapus)* Melepas tiket — aksi Admin ini sudah tidak tersedia.
- Menutup tiket yang bukan pengaduan seksinya.
- Menyerahkan berkas ke pemohon (hanya Loket Penyerahan).

---

## 9. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Staf seksi ADD tiket | Kasi seksi yang sama |
| Staf seksi Tolak tiket | Kasi seksi yang sama |
| Staf seksi ADD ULANG | Kasi seksi yang sama |
| Tiket macet | Kasi + Staf + Admin |
| Pengaduan masuk untuk seksinya | Kasi seksi tujuan |
| Tiket selesai/diserahkan | Kasi seksi yang sama |

Detail mekanisme: `16-Notifikasi-In-App.md`.

---

## 10. Laporan

- Rekap beban kerja per staf (jumlah ADD, rata-rata lama, jumlah tolak).
- Rekap tiket macet.
- Rekap pengaduan per jenis dan waktu penyelesaian.
- Semua laporan mengikuti filter tanggal yang aktif dan mencetak rentang tanggal di kop.
