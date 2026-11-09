# 06 — Petugas Warkah

> **Status dokumen:** Target Blueprint V2 · **Implementasi kode:** sudah (Fase 4). Permintaan warkah otomatis dari Tahap 4, pengelolaan pinjam fisik (`diminta → diterima → siap → dipinjam → terlambat → dikembalikan`), Ubah Batas + riwayat, dan pengingat harian sudah berjalan di `WarkahPermintaanController` + `WarkahPermintaanRiwayat`. Cetak bukti/rekap dan lampiran dokumen **belum** dibuat.

---

## 1. Peran

Petugas Warkah mengelola **berkas fisik pendukung** (warkah BT / SU) yang dipinjam oleh staf Seksi 1/2. Semua transaksi warkah dicatat di sini — staf **tidak** mengajukan permintaan lewat menu sendiri.

Prinsip:

- Warkah = **pinjaman fisik**, bukan berkas digital.
- Permintaan dibuat **otomatis** oleh Loket Pendaftaran Tahap 4.
- Perpanjangan waktu dilakukan **di luar sistem** (lisan/WA), lalu Petugas Warkah mengubah **Batas Kembali** + alasan.

---

## 2. Menu

| Menu                | Isi                                                                      |
| ------------------- | ------------------------------------------------------------------------ |
| Dashboard           | Kartu: permintaan baru, siap, dipinjam, terlambat, dikembalikan hari ini |
| Permintaan Warkah   | Permintaan yang belum diterima/diproses (E.7a)                           |
| Berkas Pinjaman     | Warkah yang sedang dipinjam (E.7b)                                       |
| Berkas Dikembalikan | Riwayat pengembalian (E.7c)                                              |
| Arsip               | Warkah dari tiket yang sudah diarsipkan                                  |
| Laporan             | Cetak/export                                                             |

---

## 3. Alur Warkah

```text
Loket Pendaftaran Tahap 4 (Butuh warkah: BT / SU / Keduanya)
        |
        v
[diminta]  -> muncul di Permintaan Warkah
        |
        v  TERIMA (Petugas Warkah menyiapkan berkas fisik)
[diterima]
        |
        v  TANDAI SIAP
[siap]     -> notifikasi ke staf pemegang tiket
        |
        |   Staf Seksi 1/2 datang ke ruang warkah, menyebut No Tiket
        v
Petugas Warkah: cocokkan No Tiket -> catat peminjam -> SERAHKAN
[dipinjam]  nama peminjam, seksi, tgl pinjam, batas kembali
        |
        +-- lewat batas -> otomatis [terlambat] (chip merah)
        |
        v  staf mengembalikan berkas -> catat pengembali
[dikembalikan]
```

**Perpanjangan**

1. Peminjam menghubungi Petugas Warkah (langsung/WA) — tidak lewat website.
2. Petugas Warkah membuka warkah → **Ubah Batas Kembali**.
3. Isi batas baru + **alasan** (wajib).
4. Sistem menyimpan riwayat perubahan; status `terlambat` hilang bila batas baru masih berlaku.

---

## 4. Status

| Status           | Label UI     | Chip      | Arti                               |
| ---------------- | ------------ | --------- | ---------------------------------- |
| `diminta`      | Diminta      | 🟠 oranye | Permintaan dibuat, belum ditangani |
| `diterima`     | Diterima     | 🔵 biru   | Sedang disiapkan                   |
| `siap`         | Siap Diambil | 🟢 hijau  | Berkas siap diserahkan             |
| `dipinjam`     | Dipinjam     | 🟣 ungu   | Berada di peminjam                 |
| `terlambat`    | Terlambat    | 🔴 merah  | Melewati batas kembali             |
| `dikembalikan` | Dikembalikan | ⚪ abu    | Sudah kembali ke warkah            |

`terlambat` adalah **penanda otomatis** dari `dipinjam` + batas kembali terlewat.

---

## 5. Daftar

### 5.1 Permintaan Warkah (E.7a)

Tanggal acuan: **Tgl Permintaan**.

| No | No Tiket      | Pemohon | Jenis | Tgl Permintaan | Lama Menunggu | Status      | Aksi                  |
| -- | ------------- | ------- | ----- | -------------- | ------------- | ----------- | --------------------- |
| 1  | JP26102026001 | Budi S. | BT    | 26/10 09:10    | 2 jam         | 🟠 Diminta  | **Terima**      |
| 2  | JP26102026001 | Budi S. | SU    | 26/10 09:10    | 2 jam         | 🔵 Diterima | **Tandai Siap** |

Satu tiket bisa punya **dua baris** (BT dan SU).

**Aksi:** `Terima` · `Tandai Siap` · `Detail`.

### 5.2 Berkas Pinjaman (E.7b)

Tanggal acuan: **Tgl Pinjam**.

| No | No Tiket      | Jenis | Peminjam | Jabatan/Seksi  | Tgl Pinjam  | Batas Kembali | Sisa Waktu | Status       | Aksi                                                   |
| -- | ------------- | ----- | -------- | -------------- | ----------- | ------------- | ---------- | ------------ | ------------------------------------------------------ |
| 1  | JP25102026004 | BT    | Andi     | Staf / Seksi 2 | 25/10 10:00 | 28/10 10:00   | 2 hari     | 🟣 Dipinjam  | Detail · **Kembalikan** · **Ubah Batas** |
| 2  | JP24102026002 | SU    | Rina     | Staf / Seksi 1 | 20/10 09:00 | 23/10 09:00   | −3 hari   | 🔴 Terlambat | Detail · **Kembalikan** · **Ubah Batas** |

**Filter:** status, BT/SU, peminjam, seksi, **Terlambat saja**.

### 5.3 Berkas Dikembalikan (E.7c)

Tanggal acuan: **Tgl Kembali**.

| No | No Tiket      | Jenis | Peminjam | Tgl Pinjam  | Tgl Kembali | Lama Pinjam  | Terlambat? | Aksi   |
| -- | ------------- | ----- | -------- | ----------- | ----------- | ------------ | ---------- | ------ |
| 1  | JP24102026001 | BT    | Andi     | 20/10 09:00 | 22/10 14:30 | 2 hari 5 jam | Tidak      | Detail |

### 5.4 Ringkasan untuk Loket Pendaftaran (E.7e)

Loket Pendaftaran melihat versi ringkas **read-only** untuk menjawab pertanyaan pemohon. Petugas Warkah adalah satu-satunya yang boleh mengubah status.

---

## 6. Form Serah Terima

Saat menekan **Serahkan** pada warkah `siap`:

| Field          | Aturan                                                     |
| -------------- | ---------------------------------------------------------- |
| Peminjam       | Dipilih dari staf Seksi 1/2 (atau isi manual nama + seksi) |
| Seksi          | 1 / 2                                                      |
| Tanggal pinjam | Otomatis = sekarang                                        |
| Batas kembali  | Default dari Setting (7 hari), bisa diubah                 |
| Catatan        | Opsional                                                   |

Validasi: warkah harus berstatus `siap` dan belum dipinjam.

Saat **Kembalikan**:

| Field           | Aturan                          |
| --------------- | ------------------------------- |
| Pengembali      | Nama staf yang mengembalikan    |
| Tanggal kembali | Otomatis = sekarang             |
| Kondisi berkas  | Baik / Rusak / Tidak lengkap    |
| Catatan         | Wajib bila kondisi bukan "Baik" |

Bila warkah rusak/hilang, tercatat di riwayat dan menjadi bahan laporan; tiket **tidak** otomatis ditolak.

---

## 7. Ubah Batas Kembali

| Field      | Aturan                                               |
| ---------- | ---------------------------------------------------- |
| Batas baru | Wajib, harus setelah tanggal pinjam                  |
| Alasan     | Wajib, min. 3 karakter                               |
| Riwayat    | Simpan batas lama, batas baru, alasan, pelaku, waktu |

Efek: chip `terlambat` hilang bila batas baru belum terlewat; notifikasi ke peminjam.

---

## 8. Aksi yang TIDAK Tersedia

- Memproses verifikasi/validasi tiket.
- Mengubah data pemohon atau No Tiket.
- Menolak/menyelesaikan tiket.
- Menghapus riwayat pinjaman.
- Mengubah status warkah milik tiket yang sudah diarsipkan (hanya baca).

---

## 9. Notifikasi

| Peristiwa                    | Penerima                                       |
| ---------------------------- | ---------------------------------------------- |
| Permintaan warkah baru       | Petugas Warkah                                 |
| Warkah siap diambil          | Staf pemegang tiket + Kasi seksinya            |
| Warkah dipinjam              | Staf peminjam                                  |
| Warkah mendekati batas (H-1) | Peminjam + Petugas Warkah                      |
| Warkah terlambat             | Peminjam + Kasi seksi + Petugas Warkah + Admin |
| Batas kembali diubah         | Peminjam                                       |
| Warkah dikembalikan          | Petugas Warkah (riwayat) + Staf terkait        |

---

## 10. Cetak

- Bukti serah terima warkah.
- Bukti pengembalian.
- Rekap pinjaman aktif (termasuk yang terlambat).
- Rekap pengembalian per periode.

> Bagian ini **belum diimplementasikan** (deferred Fase 4): belum ada halaman/tombol cetak untuk warkah.

---

## 11. Implementasi (as-built)

| Bagian            | Lokasi kode                                                                                                               |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------- |
| Menu & daftar     | `WarkahPermintaanController@index/pinjaman/dikembalikan/show` + `resources/views/warkah_permintaan/**`                |
| Aksi status       | `terima`, `siap`, `serahkan`, `kembalikan`, `ubahBatas` (semua POST + CSRF)                                     |
| Riwayat & audit   | tabel `warkah_permintaan_riwayats` via `WarkahPermintaanRiwayat`                                                      |
| Notifikasi        | `App\Services\NotifikasiService` (`warkah.diminta/siap/dipinjam/dikembalikan/batas_diubah/mendekati_batas/terlambat`) |
| Penanda terlambat | `loket:pengingat-harian` (07:00) mengubah `dipinjam` → `terlambat` dan mengirim pengingat H-1                      |
| Batas default     | `config('loket.batas_warkah_hari', 7)`                                                                                  |
| Role              | `petugas_warkah`                                                                                                        |

**Belum (deferred):** cetak bukti/rekap dan lampiran dokumen.
