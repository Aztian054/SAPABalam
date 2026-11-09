# 08 — Loket Pengaduan

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** Fase 3 — Buat Pengaduan (nomor `LP` + ddmmyyyy + urut), Daftar Pengaduan dengan filter + sub-tab status, Detail + riwayat, dan **Kirim Ulang** untuk status `dikembalikan` sudah ada (`pengaduan.*`). Belum: lampiran berkas, cetak bukti/rekap.

---

## 1. Peran

Loket Pengaduan menerima keluhan/saran dari masyarakat, membuat **Nomor Pengaduan** (`LP` + `ddmmyyyy` + urut 3 digit), dan mengirimkannya ke unit tujuan (Seksi 1–5 atau TU).

Loket Pengaduan **tidak** menangani/menutup pengaduan — itu tugas Kasi unit tujuan.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu: pengaduan hari ini, terkirim, diterima, dikembalikan, selesai |
| Buat Pengaduan | Form pengaduan baru |
| Daftar Pengaduan | Semua pengaduan yang ia buat (E.10) |
| Pengaduan Selesai | Riwayat pengaduan berstatus selesai |
| Laporan | Cetak/export |

---

## 3. Alur Pengaduan

```text
Masyarakat datang/telepon/menulis
   -> Loket Pengaduan buka Buat Pengaduan
   -> isi data pengadu + isi pengaduan + Seksi Tujuan
   -> KIRIM  -> nomor LP... dibuat, status terkirim
        |
        v
Kasi unit tujuan: TERIMA  -> status diterima
        |                        |
        |                        '-> TUTUP -> status selesai
        |
        '-> KEMBALIKAN + catatan (salah tujuan) -> status dikembalikan
                 |
                 v
        Loket Pengaduan: KIRIM ULANG (ubah tujuan/catatan) -> status terkirim
```

---

## 4. Form Buat Pengaduan

### 4.1 Identitas Pengadu

| Field | Aturan |
|---|---|
| Nama pengadu | Wajib |
| NIK | Opsional, 16 digit |
| No HP | Wajib (untuk tindak lanjut) |
| Alamat | Opsional |
| Anonim? | Checkbox — bila dicentang, nama/HP boleh dikosongkan |

### 4.2 Isi Pengaduan

| Field | Aturan |
|---|---|
| Jenis | Pelayanan / Berkas / Warkah / Lainnya |
| No Tiket terkait | Opsional; bila diisi harus valid |
| Uraian | Wajib, min. 20 karakter |
| Seksi tujuan | Wajib — Seksi 1, 2, 3, 4, 5, atau TU |
| Lampiran | Opsional (foto/dokumen) |

### 4.3 Aturan Nomor

- Format `LP` + `ddmmyyyy` + urut 3 digit, mis. `LP26102026001`.
- Unik dan tidak bisa diedit setelah KIRIM.
- Nomor tercetak pada bukti pengaduan.

---

## 5. Daftar Pengaduan (E.10)

Tanggal acuan: **Tgl Buat**.

| No | No Pengaduan | Pengadu | Jenis | Seksi Tujuan | Status | Catatan | Tgl Buat | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | LP26102026001 | Budi | Pelayanan | Seksi 1 | 🟠 Terkirim | — | 26/10 08:00 | Detail |
| 2 | LP25102026003 | Sari | Berkas | Seksi 2 | 🔵 Diterima | Sedang ditelaah | 25/10 11:20 | Detail |
| 3 | LP24102026002 | Doni | Warkah | TU | 🔴 Dikembalikan | Bukan kewenangan TU | 24/10 09:40 | Detail · **Kirim Ulang** |
| 4 | LP23102026001 | Wati | Pelayanan | Seksi 1 | 🟢 Selesai | Sudah dijelaskan | 23/10 10:10 | Detail |

**Filter:** rentang tanggal, jenis, seksi tujuan, status, pencarian pintar (No Pengaduan / nama pengadu / No Tiket).

**Sub-tab:** Semua · Terkirim · Diterima · Dikembalikan · Selesai.

---

## 6. Kirim Ulang

Hanya untuk status `dikembalikan`.

| Field | Aturan |
|---|---|
| Seksi tujuan baru | Wajib (boleh sama bila catatan diperbaiki) |
| Catatan perbaikan | Wajib |
| Nomor pengaduan | **Tetap** (tidak membuat nomor baru) |

Efek: status kembali `terkirim`, riwayat mencatat pengembalian dan pengiriman ulang.

---

## 7. Status Pengaduan

| Status | Label UI | Chip | Arti |
|---|---|---|---|
| `terkirim` | Terkirim | 🟠 oranye | Menunggu unit tujuan |
| `diterima` | Diterima | 🔵 biru | Sedang ditangani Kasi unit |
| `dikembalikan` | Dikembalikan | 🔴 merah | Salah tujuan/kewenangan |
| `selesai` | Selesai | 🟢 hijau | Ditutup Kasi unit |

---

## 8. Detail Pengaduan

| Bagian | Isi |
|---|---|
| Header | No Pengaduan, status, tanggal buat, seksi tujuan |
| Pengadu | Nama/NIK/HP (kecuali anonim) |
| Uraian | Teks lengkap + lampiran |
| Tiket terkait | Tautan ke detail tiket bila ada |
| Riwayat | Terkirim → Diterima → (Dikembalikan → Kirim Ulang)* → Selesai |

Loket Pengaduan hanya bisa melihat pengaduan **yang ia buat**; Super Admin & Admin melihat semua.

---

## 9. Aksi yang TIDAK Tersedia

- Menerima/menutup pengaduan.
- Mengubah seksi tujuan setelah pengaduan diterima (harus lewat pengembalian).
- Menghapus pengaduan.
- Mengubah nomor pengaduan.
- Membuat pengaduan atas nama unit lain tanpa data pengadu.

---

## 10. Notifikasi

| Peristiwa | Penerima |
|---|---|
| Pengaduan baru dikirim | Kasi unit tujuan |
| Pengaduan diterima | Loket Pengaduan (pembuat) |
| Pengaduan dikembalikan | Loket Pengaduan (pembuat) + pengadu (opsional) |
| Pengaduan selesai | Loket Pengaduan (pembuat) |
| Pengaduan belum ditangani > batas | Kasi unit + Admin |

---

## 11. Cetak

- Bukti pengaduan (dengan nomor LP).
- Rekap pengaduan per periode, jenis, dan unit.
- Rekap waktu tanggap (Tgl Buat → Diterima → Selesai).
