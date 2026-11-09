# 09 — Kasi Seksi 3/4/5 & Kasubbag TU

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** Fase 3 — penanganan pengaduan unit sudah ada (`pengaduan-unit.*`): Daftar Pengaduan ter-scope `seksi_tujuan`, **TERIMA**, **TUTUP** (catatan hasil min. 10 karakter), **KEMBALIKAN** + alasan, penanda TERLAMBAT, dan Pengaduan Selesai. Berlaku juga untuk Kasi Seksi 1/2. Belum: pengaturan batas tanggap di UI, cetak rekap per unit.

---

## 1. Peran

Unit Seksi 3, 4, 5, dan TU **tidak** mengerjakan berkas permohonan (back office hanya Seksi 1 dan 2). Perannya terbatas pada **penanganan pengaduan**.

| Unit         | Kode role                   | Cakupan                  |
| ------------ | --------------------------- | ------------------------ |
| Kasi Seksi 3 | `kasi_unit` + `seksi=3` | Pengaduan tujuan Seksi 3 |
| Kasi Seksi 4 | `kasi_unit` + `seksi=4` | Pengaduan tujuan Seksi 4 |
| Kasi Seksi 5 | `kasi_unit` + `seksi=5` | Pengaduan tujuan Seksi 5 |
| Kasubbag TU  | `kasubbag_tu`             | Pengaduan tujuan TU      |

---

## 2. Menu

| Menu              | Isi                                                            |
| ----------------- | -------------------------------------------------------------- |
| Dashboard         | Kartu: pengaduan masuk, diterima, selesai bulan ini, terlambat |
| Daftar Pengaduan  | Pengaduan untuk unitnya (E.10)                                 |
| Pengaduan Selesai | Riwayat pengaduan yang sudah ditutup                           |
| Laporan           | Cetak/export per unit                                          |

Tidak ada menu permohonan, warkah, selesai, atau ditolak.

---

## 3. Alur Penanganan

```text
Loket Pengaduan KIRIM -> status terkirim, unit tujuan = Seksi 3/4/5/TU
        |
        v
Kasi/Kasubbag buka Daftar Pengaduan
        |
        +-- TERIMA  -> status diterima
        |      -> tindak lanjut (di luar sistem bila perlu)
        |      -> TUTUP + catatan hasil -> status selesai
        |
        '-- KEMBALIKAN + catatan -> status dikembalikan
               -> Loket Pengaduan KIRIM ULANG
```

---

## 4. Daftar Pengaduan

| No | No Pengaduan  | Pengadu | Jenis     | Status      | Catatan             | Tgl Buat    | Lama   | Aksi                                    |
| -- | ------------- | ------- | --------- | ----------- | ------------------- | ----------- | ------ | --------------------------------------- |
| 1  | LP26102026001 | Budi    | Pelayanan | 🟠 Terkirim | —                  | 26/10 08:00 | 4 jam  | **Terima**                        |
| 2  | LP25102026002 | Sari    | Lainnya   | 🔵 Diterima | Menunggu koordinasi | 25/10 09:30 | 1 hari | **Tutup** · **Kembalikan** |
| 3  | LP24102026003 | Doni    | Berkas    | 🟢 Selesai  | Sudah dijelaskan    | 24/10 10:00 | —     | Detail                                  |

**Filter:** rentang tanggal, jenis, status, pencarian pintar.

**Aksi per status**

| Status           | Aksi                          |
| ---------------- | ----------------------------- |
| `terkirim`     | Terima · Kembalikan          |
| `diterima`     | Tutup · Kembalikan           |
| `dikembalikan` | — (menunggu Loket Pengaduan) |
| `selesai`      | Detail saja                   |

---

## 5. Aturan Aksi

### 5.1 Terima

- Hanya untuk `terkirim`.
- Mencatat pelaku + waktu terima.
- Notifikasi ke pembuat pengaduan.

### 5.2 Tutup (Selesai)

- Hanya untuk `diterima`.
- **Catatan hasil wajib** (min. 10 karakter).
- Status final; tidak bisa dibuka kembali. Bila pengadu kembali, buat pengaduan baru.

### 5.3 Kembalikan

- Untuk `terkirim` atau `diterima`.
- **Alasan wajib**.
- Dipakai bila pengaduan salah tujuan atau bukan kewenangan unit.
- Pengaduan kembali ke Loket Pengaduan untuk **Kirim Ulang**.

---

## 6. Batasan Data

- Hanya melihat pengaduan dengan `seksi_tujuan` = unitnya.
- Tidak bisa melihat pengaduan unit lain, kecuali Admin/Super Admin.
- Data pengadu anonim tetap anonim.
- Tidak ada akses ke modul permohonan/warkah.

---

## 7. Penanda Terlambat

- Batas tanggap di Setting (default **2 hari kerja** sejak `terkirim`).
- Bila terlewat: baris merah + chip **TERLAMBAT**.
- Notifikasi ke Kasi/Kasubbag unit + Admin.
- Tidak mengubah status.

---

## 8. Aksi yang TIDAK Tersedia

- Membuat pengaduan baru (hanya Loket Pengaduan).
- Mengubah isi pengaduan atau nomor LP.
- Menghapus pengaduan.
- Mengakses data permohonan/warkah.
- Menutup pengaduan tanpa catatan hasil.

---

## 9. Notifikasi

| Peristiwa                     | Penerima                   |
| ----------------------------- | -------------------------- |
| Pengaduan masuk untuk unitnya | Kasi/Kasubbag unit         |
| Pengaduan diterima            | Pembuat (Loket Pengaduan)  |
| Pengaduan ditutup             | Pembuat (Loket Pengaduan)  |
| Pengaduan dikembalikan        | Pembuat (Loket Pengaduan)  |
| Pengaduan terlambat           | Kasi/Kasubbag unit + Admin |

---

## 10. Laporan

- Rekap pengaduan per jenis dan status.
- Rata-rata waktu terima dan waktu selesai.
- Daftar pengaduan terlambat.
- Semua mengikuti filter tanggal aktif dan mencetak rentangnya.
