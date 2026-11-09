# 02 — Loket Pendaftaran

> **Status dokumen:** Blueprint V2 · **Implementasi kode:** sudah (wizard 4 tahap + **No. Berkas manual**, No Tiket otomatis tanpa input/edit manual, ploting, permintaan warkah otomatis, daftar & pencarian berkas).

---

## 1. Peran

Loket Pendaftaran adalah **titik masuk permohonan**. Tugasnya:

1. Mendaftarkan permohonan lewat **wizard 4 tahap**.
2. Menjalankan/memantau **Ploting** bersama Loket Ploting.
3. Menerima **No Tiket otomatis** dari sistem (tidak dapat diisi/diubah manual).
4. Memantau tiket yang ia daftarkan sampai selesai/ditolak.
5. Membuat **permintaan warkah** otomatis pada Tahap 4.

Loket Pendaftaran **tidak** memproses berkas dan **tidak** menyerahkan berkas ke pemohon.

---

## 2. Menu

| Menu                     | Isi                                                                                   |
| ------------------------ | ------------------------------------------------------------------------------------- |
| Dashboard                | Kartu: pendaftaran hari ini, menunggu ploting, siap lanjut, ditolak, selesai hari ini |
| Pendaftaran Baru         | Wizard Tahap 1–4                                                                     |
| Daftar Berkas Permohonan | Semua tiket hasil pendaftarannya (E.2)                                                |
| Ploting                  | Tab Menunggu Ploting & Sudah Ploting (E.3a)                                           |
| Daftar Warkah            | Read-only ringkas (E.7e)                                                              |
| Daftar Selesai           | Tiket yang ia daftarkan dan sudah diserahkan (E.8)                                    |
| Daftar Ditolak           | Tiket yang ia daftarkan dan sedang ditolak (E.9)                                      |
| Arsip                    | Read-only (E.11)                                                                      |
| Laporan                  | Cetak/export                                                                          |

---

## 3. Wizard Pendaftaran

### Tahap 1 — No Tiket, No. Berkas & Biodata

Urutan tampilan: **No Tiket → No. Berkas → Biodata Pemohon**. Form **Tambah Berkas** Admin/Super Admin memakai urutan dan field yang sama.

| Field                          | Aturan                                                                                                               |
| ------------------------------ | -------------------------------------------------------------------------------------------------------------------- |
| **No Tiket**             | Paling atas; otomatis `JP` + `ddmmyyyy` + urut 3 digit (mis. `JP26102026001`); tidak dapat diisi/diubah manual |
| **No. Berkas**           | Tepat di bawah No Tiket; manual oleh Loket Pendaftaran; format bebas, tidak unik, tanpa batas panjang                |
| Nama pemohon                   | Wajib                                                                                                                |
| NIK                            | Opsional, 16 digit                                                                                                   |
| No. HP                         | Wajib                                                                                                                |
| Alamat / kelurahan / kecamatan | Wajib minimal kelurahan                                                                                              |

**Aturan No Tiket**

- Dibuat otomatis oleh sistem saat Tahap 1 disimpan (server-side).
- Unik; tidak ada input manual di form maupun di daftar.
- Nomor tidak dapat diubah setelah terbit.

**Aturan No. Berkas**

- Nomor resmi pusat BPN, diletakkan tepat setelah No Tiket.
- Boleh angka, huruf, simbol, dan spasi; tidak memengaruhi alur (kunci tetap `kode_tiket`).
- Boleh kosong (disimpan `null`), boleh duplikat, dan boleh diisi/diubah kapan saja oleh pembuat berkas atau Admin.
- Disimpan di `pendaftaran_drafts.no_berkas`, disalin ke `tikets.no_berkas` saat Tahap 4.

### Tahap 2 — Ploting

| Kondisi                   | Aksi                                                                                                                                                                                        |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Sudah** diploting | Lanjut ke Tahap 3                                                                                                                                                                           |
| **Belum**           | Sistem mencetak **antrian** → berkas diserahkan ke **Loket Ploting** → setelah ploting selesai, petugas membuka menu **Ploting → Sudah Ploting → Lanjut Pendaftaran** |

Tiket berhenti di Tahap 2 selama ploting belum selesai. Waktu kirim & lama menunggu tampil di daftar ploting.

### Tahap 3 — Data Permohonan

| Field                             | Aturan                             |
| --------------------------------- | ---------------------------------- |
| Jenis permohonan                  | Wajib (JP01–JP15)                 |
| No Hak sekarang                   | Format `JenisHak.No/Kelurahan`   |
| No Hak sebelumnya                 | Wajib bila ada penanda `-DAHULU` |
| Letak tanah (kelurahan/kecamatan) | Wajib                              |
| Jumlah bidang                     | ≥ 1                               |

### Tahap 4 — Kebutuhan Warkah

| Pilihan            | Efek                                                          |
| ------------------ | ------------------------------------------------------------- |
| **Tidak**    | Pendaftaran selesai → tiket masuk Database (`di_database`) |
| **BT**       | Permintaan warkah BT dibuat otomatis → Petugas Warkah        |
| **SU**       | Permintaan warkah SU dibuat otomatis                          |
| **Keduanya** | Dua permintaan warkah                                         |

Setelah Tahap 4 selesai, status tiket **Di Database** dan siap di-ADD staf Seksi 1/2.

dan bisa batalkan walau berhenti di tahap mana pun selagi masih pendaftaran

---

## 4. Setelah Pendaftaran

```text
status = di_database
-> muncul di Daftar Berkas Permohonan (milik akun ini)
-> masuk antrean Database untuk Staf Seksi 1/2
-> bila butuh warkah: permintaan tampil di Petugas Warkah
```

Loket Pendaftaran **tidak** mengirim tiket ke stasiun berikutnya; stasiun mengambil sendiri lewat ADD.

---

## 5. No Tiket Otomatis

No Tiket **hanya** dibuat oleh sistem (`TiketNomorService`): prefix JP + tanggal + urut 3 digit, memeriksa bentrok dengan tiket dan draf lain.

| Aturan        | Detail                                                             |
| ------------- | ------------------------------------------------------------------ |
| Input manual  | Tidak ada — field di Tahap 1 hanya tampilan read-only             |
| Edit nomor    | Tidak tersedia (route, controller, dan halaman edit sudah dihapus) |
| Riwayat nomor | Tidak ada; nomor tidak pernah berubah                              |
| Admin         | Form Tambah Berkas juga memakai nomor otomatis                     |

---

## 6. Daftar & Aksi

### 6.1 Daftar Berkas Permohonan (E.2)

Kolom: **No · No Tiket · No. Berkas · Pemohon · Jenis · No Hak · Status · Posisi Saat Ini · Tgl Daftar · Aksi**.

Aksi: `Detail` · `Cetak Bukti`.

Sub-tab: **Semua · Berjalan · Selesai · Ditolak**.

### 6.2 Ploting (E.3a)

- Tab **Menunggu Ploting**: No Tiket, Pemohon, NIK, No HP, Waktu Kirim, Lama Menunggu, Petugas Pendaftar. Aksi: Detail, Cetak Ulang Antrian.
- Tab **Sudah Ploting**: No Tiket, Pemohon, Catatan Ploting, Waktu Selesai, Diploting Oleh. Aksi: **Lanjut Pendaftaran**.

### 6.3 Warkah (E.7e)

Read-only ringkas: No Tiket, Jenis BT/SU, Status, Peminjam/Seksi, Batas Kembali, Sisa Waktu. Untuk menjawab pemohon ("warkah saya sudah siap?").

---

## 7. Aksi yang TIDAK Tersedia

- Menolak tiket (itu hak staf back office).
- Memproses verifikasi/validasi/alih media.
- Mengubah status setelah tiket dipegang stasiun.
- Menghapus tiket.

---

## 8. Cetak

| Dokumen           | Kapan                        |
| ----------------- | ---------------------------- |
| Bukti Pendaftaran | Setelah Tahap 4 selesai      |
| Rekap/Laporan     | Kapan saja, mengikuti filter |

---

## 9. Notifikasi yang Diterima

| Peristiwa                | Isi                                         |
| ------------------------ | ------------------------------------------- |
| Tiket ditolak staf       | "Tiket X ditolak: {alasan}" + tautan detail |
| Tiket selesai/diserahkan | "Tiket X telah diserahkan"                  |
| Warkah siap              | "Warkah tiket X siap"                       |
| Ploting selesai          | "Tiket X siap dilanjutkan"                  |

Detail: `16-Notifikasi-In-App.md`.
