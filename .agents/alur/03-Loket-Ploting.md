# 03 — Loket Ploting

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sebagian (Fase 1: daftar menunggu/selesai, proses + catatan, status tampil di sisi Loket Pendaftaran). Daftar arsip khusus dan laporan ploting belum.

---

## 1. Peran

Loket Ploting menangani **penentuan bidang/letak tanah** untuk permohonan yang belum diploting. Berkas masuk dari Loket Pendaftaran (Tahap 2), diproses, lalu dikembalikan agar pendaftaran bisa dilanjutkan.

Loket Ploting **tidak** memproses verifikasi/validasi dan **tidak** menyerahkan berkas ke pemohon.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu: menunggu, sedang diproses, selesai hari ini, lama menunggu |
| Daftar Berkas Ploting | Berkas masuk yang belum diproses (E.3b) |
| Daftar Berkas Selesai Ploting | Riwayat ploting selesai (E.3c) |
| Arsip | Read-only (E.11) |
| Laporan | Cetak/export |

---

## 3. Alur Kerja

```text
Loket Pendaftaran Tahap 2 "Belum" -> cetak antrian -> berkas fisik ke Loket Ploting
        |
        v
Loket Ploting buka Daftar Berkas Ploting
   -> cari/ketik No Tiket -> baris muncul -> klik PROSES
   -> tentukan bidang/letak sesuai peta -> isi catatan ploting
   -> klik KIRIM
        |
        v
Berkas pindah ke Daftar Berkas Selesai Ploting
        |
        v
Loket Pendaftaran: menu Ploting -> tab "Sudah Ploting" -> Lanjut Pendaftaran
```

**Aturan**
- Proses hanya bisa untuk berkas yang sudah masuk daftar.
- Catatan ploting wajib bila ada penyimpangan dari peta.
- Setelah KIRIM, Loket Ploting tidak bisa mengubah lagi; koreksi dilakukan lewat Admin (dengan catatan) atau pengembalian oleh Loket Pendaftaran.
- Tidak ada aksi Tolak; bila berkas tidak layak, gunakan catatan + koordinasi langsung.

---

## 4. Daftar

### 4.1 Daftar Berkas Ploting (E.3b)

Tanggal acuan: **Waktu Masuk**.

| No | No Tiket | Pemohon | Waktu Masuk | Lama Menunggu | Status | Aksi |
|---|---|---|---|---|---|---|
| 1 | JP26102026003 | Wati | 26/10 09:15 | 3 jam | ⚪ Menunggu | **Proses** |

Filter: tanggal masuk, status, pencarian pintar. Baris berubah merah/kuning bila lama menunggu melewati batas.

### 4.2 Daftar Berkas Selesai Ploting (E.3c)

Tanggal acuan: **Waktu Kirim**.

| No | No Tiket | Pemohon | Catatan | Waktu Masuk | Waktu Kirim | Lama Proses | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026002 | Sari | Bidang sesuai peta | 26/10 08:40 | 26/10 11:00 | 2j 20m | Detail |

---

## 5. Detail & Cetak

- Detail menampilkan data pemohon, bidang tanah, catatan ploting, dan riwayat.
- Cetak: bukti ploting (opsional) dan rekap harian/bulanan.
- Filter tanggal ikut tercetak pada kop laporan.

---

## 6. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Berkas ploting masuk | Loket Ploting |
| Ploting selesai (KIRIM) | Loket Pendaftaran (pemilik tiket) |
| Menunggu melewati batas | Loket Ploting + Admin |

---

## 7. Batasan

- Tidak melihat tiket yang tidak terkait ploting.
- Tidak mengubah data pemohon atau No Tiket.
- Tidak memproses warkah.
