# 12 — Kakan (Monitoring)

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sebagian (role `kakan` sudah ada dan dapat membuka monitoring read-only; menu & kartu dashboard V2 penuh belum).

---

## 1. Peran

Kakan (Kepala Kantor) adalah **pengawas puncak**. Aksesnya **read-only** untuk seluruh data operasional.

Keputusan #5: **tidak ada Persetujuan Akhir** di V2 — Kakan tidak menandatangani/menyetujui tiket. Bila perlu persetujuan formal, dilakukan di luar sistem.

---

## 2. Menu

| Menu | Isi |
|---|---|
| Dashboard | Kartu ringkas: total berjalan, selesai, ditolak, macet, pengaduan |
| Daftar Permohonan | Semua tiket (read-only) |
| Daftar Warkah | Ringkasan warkah (opsional, read-only) |
| Daftar Pengaduan | Semua pengaduan (read-only) |
| Laporan | Cetak/export lintas unit |

---

## 3. Dashboard

| Kartu | Isi |
|---|---|
| Berjalan | Tiket `di_database` + `diproses` |
| Selesai | Tiket `selesai` pada periode |
| Ditolak | Tiket `ditolak` aktif |
| Macet | Tiket melewati batas hari |
| Pengaduan | Terkirim/diterima vs selesai |
| Durasi rata-rata | Rata-rata hari dari daftar ke serah |

Periode default: bulan berjalan, dapat diubah (hari ini / 7 hari / 30 hari / rentang).

---

## 4. Daftar Permohonan (Read-Only)

| No | No Tiket | Pemohon | Jenis | Status | Posisi | Lama | Warkah |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | B*** S. | Peralihan Hak | 🔵 Diproses | Seksi 2 | 5 hari | BT: Dipinjam |
| 2 | JP26102026002 | S*** | Pemecahan | 🟢 Selesai | — | 2 hari | — |

Aturan privasi:
- **NIK tidak ditampilkan**; nama disamarkan (huruf awal + ***).
- Nomor HP tidak ditampilkan.
- Nama petugas **ditampilkan** (untuk akuntabilitas), tetapi tanpa detail pribadi.

**Aksi:** hanya `Detail` (tanpa tombol perubahan).

---

## 5. Daftar Warkah (Opsional)

Ringkasan: jumlah diminta, siap, dipinjam, terlambat, dikembalikan. Bila kantor menghendaki, Kakan dapat membuka daftar detail read-only.

---

## 6. Daftar Pengaduan (Read-Only)

| No | No Pengaduan | Pengadu | Jenis | Seksi Tujuan | Status | Tgl Buat |
|---|---|---|---|---|---|---|
| 1 | LP26102026001 | B*** | Pelayanan | Seksi 1 | 🔵 Diterima | 26/10 |

Nama pengadu disamarkan; isi pengaduan dapat dibaca lengkap.

---

## 7. Laporan

| Laporan | Isi |
|---|---|
| Kinerja Layanan | Jumlah masuk/selesai/ditolak per periode |
| Durasi | Rata-rata & terlama per seksi |
| Penolakan | Jumlah & alasan terbanyak |
| Macet | Daftar tiket melewati batas |
| Warkah | Pinjaman & keterlambatan |
| Pengaduan | Jumlah per jenis & waktu tanggap |

Export: PDF (kop kantor + rentang tanggal) dan CSV.

---

## 8. Aksi yang TIDAK Tersedia

- ADD / ADD ULANG / KIRIM / Tolak / Selesai.
- Menyetujui tiket (tidak ada Persetujuan Akhir).
- *(Dihapus)* Lepas Tiket.
- Mengubah setting/master data.
- Mengubah status warkah atau pengaduan.

---

## 9. Notifikasi

Kakan hanya menerima notifikasi **ringkasan** (opsional, bisa dimatikan di Setting):

- Rekap harian/mingguan.
- Peringatan bila tiket macet melewati ambang tertentu.
- Peringatan bila pengaduan terlambat melewati ambang.

Detail mekanisme: `16-Notifikasi-In-App.md`.

---

## 10. Catatan Implementasi

- Middleware `role:kakan` dengan policy read-only.
- Samarkan NIK/nama di level query/resource, bukan hanya di view, agar tidak bocor lewat export.
- Dashboard sebaiknya memakai cache singkat (1–5 menit) untuk mengurangi beban.
