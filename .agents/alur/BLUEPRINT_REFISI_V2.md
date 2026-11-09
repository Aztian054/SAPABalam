# BLUEPRINT REFISI V2 (PATCH)
## Sistem Pelayanan Berkas Permohonan & Pengaduan: Kantah BPN Kota Bandar Lampung

> Dokumen ini **melengkapi dan menimpa** bagian yang bertabrakan di `BLUEPRINT_REFISI_FINAL.md`.
> Bagian yang tidak disebut di sini (Super Admin pusat demo, Mode Terang/Gelap, Notifikasi, Wizard Tahap 1-4, Diagram Alur, Cetak) **tetap berlaku**.
> Isi: (A) keputusan yang dikunci, (B) alur terstruktur, (C) aturan per modul, (D) fitur pencarian & kalender, (E) **desain tabel semua daftar**, (F) matriks menu, (G) perubahan data, (H) pertanyaan tersisa.

---

## A. KEPUTUSAN YANG DIKUNCI (dari jawaban poin 2-10)

| # | Keputusan | Menimpa bagian lama |
|---|---|---|
| 2 | **Loket Penyerahan = penutup alur.** Petugas ketik/cari No Tiket di antrian, **ADD**, lalu satu-satunya aksi adalah tombol **SELESAI** (tanpa Tolak). Tiket berstatus SELESAI. | 5.9 (opsi Tolak dihapus) |
| 3 | **Tiket DITOLAK kembali ke staf yang menolak.** Staf lain tidak bisa Add. Perbaikan/refisi menjadi tanggung jawab staf penolak. | 8.3 |
| 4 | **Lepas Tiket hanya untuk tiket di Seksi 1 dan 2.** Urutan antar seksi **tidak dipaksa sistem** (lihat B.2). | 8.4 |
| 4b | **Back office berkas hanya di Seksi 1 dan 2**, setelah Loket Pendaftaran dan Loket Ploting. | 5.7, 5.8 |
| 4c | **Warkah:** alur berjalan hanya jika dibutuhkan; **detail** di Staf 1-2 dan Kasi 1-2; **Kakan hanya kartu dashboard.** Tidak ada ajukan permintaan / minta tambah waktu lewat website. | 6, 5.6, 5.7, 5.11 |
| 4d | **Pencarian pintar + filter rentang tanggal (kalender)** di semua daftar di semua akun. | baru (D) |
| 4e | **Daftar Selesai dan Daftar Ditolak** ada di Loket Pendaftaran, Staf 1-2, Kasi 1-2. | baru |
| 5 | **Kakan monitoring saja**: dashboard, daftar permohonan, daftar warkah, daftar pengaduan. Opsi "Persetujuan Akhir" **dihapus**. | 5.11 |
| 6 | Format sama; beda hanya prefiks **JP** (permohonan) dan **LP** (pengaduan), di menu berbeda. | 5.3, 5.10 |
| 7 | **No Tiket dibuat otomatis oleh sistem**; tidak ada input/edit manual, termasuk bila terbit nomor resmi. | 5.3 Tahap 1 |
| 8 | **Kasi 1-2 baru melihat tiket di Daftar Permohonan setelah staf seksinya meng-Add.** Jabatan dan seksi di form akun yang menentukan hak ini. | 5.7 |
| 9 | Notifikasi: sesuai blueprint sebelumnya. | - |
| 10 | **Warkah dipinjam secara fisik**: staf datang ke ruang warkah, dicocokkan dengan No Tiket, Petugas Warkah mencatat peminjam + seksi + batas waktu. Tambah waktu dibicarakan langsung / WhatsApp, Petugas Warkah yang mengubah di sistem. | 6.3, 6.4 |

---

## B. ALUR TERSTRUKTUR

### B.1 Alur utama

```
[1] LOKET PENDAFTARAN  Tahap 1 Biodata + No Tiket otomatis (sistem)
        |
[2]     Tahap 2 Ploting ---- Sudah --------------------------------+
        |                                                           |
        '-- Belum -> Cetak Antrian -> LOKET PLOTING (Sudah + catatan -> Kirim)
                       -> menu Ploting (Loket Pendaftaran) -> Lanjut Pendaftaran
        |                                                           |
[3]     Tahap 3 Jenis Permohonan, No Hak, Letak Tanah <-------------+
        |
[4]     Tahap 4 Butuh warkah?  Tidak -> Selesai
                               Ya (BT/SU/Keduanya) -> Permintaan Warkah otomatis
                                    ke PETUGAS WARKAH -> Selesai
        |
        v
[5] DATABASE (Daftar Permohonan Super Admin)  status: Di Database
        |
        v   ┌──────── BACK OFFICE: HANYA SEKSI 1 DAN SEKSI 2 ────────┐
[6]  Staf Seksi 1/2 cari tiket -> ADD -> Selesai+catatan / Tolak+catatan -> KIRIM
        |     (tiket kembali ke Database, lalu staf berikutnya ADD)      |
        |     Tolak -> kembali ke STAF PENOLAK (lihat C.2)               |
        └───────────────────────────────────────────────────────────────┘
        |
[7] LOKET PENYERAHAN cari/ketik No Tiket -> ADD -> SELESAI
        |
[8] SELESAI -> Daftar Selesai -> otomatis Daftar Arsip setelah N hari
```

### B.2 Urutan di Back Office (tidak dikunci)

Kebiasaan saat ini, **hanya sebagai contoh, bukan aturan sistem**:

1. Alih Media Seksi 2 (BT)
2. Alih Media Seksi 1 (SU)
3. Validator, Seksi 1 atau Seksi 2 dulu (bebas)

Karena sistem mengikuti **posisi berkas fisik**, siapa pun dari Seksi 1/2 yang memegang berkas boleh Add. Urutan dapat berubah tanpa mengubah sistem. Diagram Alur tetap mencatat urutan nyata.

### B.3 Alur Warkah (paralel, hanya bila dibutuhkan)

```
Loket Pendaftaran Tahap 4 (Butuh warkah: BT / SU / Keduanya)
        |
        v
PETUGAS WARKAH: Permintaan masuk -> TERIMA -> berkas pendukung SIAP
        |
        |   (di dunia nyata) Staf Seksi 1/2 memeriksa berkas, lalu
        |   datang ke ruang warkah, menyebut No Tiket
        v
PETUGAS WARKAH mencocokkan No Tiket -> cocok -> berkas diberikan
   mencatat: Nama Peminjam, Seksi, Tgl Pinjam, Batas Kembali -> DIPINJAM
        |
        v
Peminjam mengembalikan -> Petugas Warkah catat Pengembali -> DIKEMBALIKAN
        |
 Jika lewat batas -> otomatis TERLAMBAT (merah)
```

Tambah waktu: dibicarakan langsung / WA. Petugas Warkah mengubah **Batas Kembali** + alasan (riwayat tersimpan).

---

## C. ATURAN PER MODUL (yang berubah)

### C.1 Loket Penyerahan
- Menu: Dashboard · **Antrian Penyerahan** (cari/ketik No Tiket + Add) · **Tiket Saya** · Daftar Selesai · Laporan.
- Panel aksi hanya **[ SELESAI ]** + kolom catatan opsional (nama penerima/hubungan). Tidak ada Tolak.
- Klik Selesai: status **SELESAI**, alur tertutup, opsi cetak Tanda Terima.
- Syarat Add (usulan): tiket sudah pernah dikerjakan Seksi 1/2 dan tidak sedang dipegang atau berstatus Ditolak. Sistem hanya memberi peringatan bila belum ada riwayat seksi, tidak memblokir.

### C.2 Tiket Ditolak (menggantikan 8.3)

```
Staf A klik TOLAK + catatan -> KIRIM
   -> status DITOLAK, "Penanggung jawab" = Staf A (tetap)
   -> kembali ke Database, tetapi tombol ADD untuk staf lain NONAKTIF
      ("Ditolak, menjadi tanggung jawab Staf A")
   -> muncul di: Daftar Ditolak Staf A, Daftar Ditolak Kasi seksi A,
      Daftar Ditolak Loket Pendaftaran, dashboard Kakan (kartu)
   -> notifikasi ke Loket Pendaftaran + Kasi seksi A
Masalah fisik/administrasi diperbaiki
   -> Staf A buka Daftar Ditolak -> klik ADD ULANG
   -> status Sedang Diproses, riwayat penolakan tetap tampil di Diagram Alur
   -> Staf A pilih Selesai / Tolak lagi
```

Jika Staf A cuti/pindah: gunakan Lepas Tiket (C.3).

### C.3 Lepas Tiket (menggantikan 8.4)
- Hanya untuk tiket yang dipegang **Staf Seksi 1 atau 2** (termasuk tiket Ditolak yang dipegang penanggung jawab).
- Dilakukan **Admin / Super Admin** + catatan alasan. Tiket kembali ke Database, stasiun ditandai *Dilepas admin*.
- Penanda **MACET** otomatis tetap berlaku untuk tiket di staf Seksi 1/2 (batas hari di Setting).

### C.4 Warkah (menggantikan 6.3 - 6.5 yang bertabrakan)
- **Tidak ada** "Ajukan Permintaan Warkah" dan "Minta Tambah Waktu" di akun Staf, Kasi, maupun Loket Pendaftaran.
- Permintaan warkah hanya lahir dari **Loket Pendaftaran Tahap 4**.
- Yang tersisa di Staf/Kasi: **Daftar Warkah (read only)**.
- Petugas Warkah satu-satunya yang mengubah status dan batas kembali.
- Nama peminjam dipilih dari akun Staf/Kasi Seksi 1-2 (atau Loket Pendaftaran bila memang meminjam).

| Akun | Tampilan warkah |
|---|---|
| Petugas Warkah | Penuh (kelola) |
| Staf Seksi 1/2 | **Daftar detail** (pinjaman atas nama sendiri + warkah untuk tiket yang ia Add) |
| Kasi Seksi 1/2 | **Daftar detail** seluruh warkah seksinya |
| Loket Pendaftaran | Daftar read only untuk tiket yang ia daftarkan (usulan, lihat H.1) |
| Kakan | **Kartu dashboard saja**, tanpa daftar |

**Kartu Warkah Kakan:** *Diproses* · *Dipinjam* · *Lewat Waktu (Terlambat)* · *Sudah Dikembalikan*.

### C.5 Kasi Seksi 1 dan 2
- **Daftar Permohonan Seksi** hanya memuat tiket yang **sudah di-Add staf seksinya** (read only + Diagram Alur).
- Menu: Dashboard · Daftar Permohonan · Daftar Selesai · Daftar Ditolak · Daftar Warkah · Daftar Pengaduan · Pengaduan Selesai · Laporan.

### C.6 Kakan (menggantikan 5.11)
- Menu: Dashboard · Daftar Permohonan · Daftar Warkah *(lihat catatan H.2)* · Daftar Pengaduan · Laporan. **Semua read only.**
- Tidak ada Persetujuan Akhir.

### C.7 No Tiket otomatis (tidak bisa diedit)
- Dibuat oleh sistem saat Tahap 1 disimpan (prefix JP + tanggal + urut 3 digit).
- Tidak ada input manual, alasan perubahan, maupun riwayat nomor.
- Tidak ada route/controller/halaman edit nomor; Admin pun memakai nomor otomatis.

---

## D. PENCARIAN PINTAR DAN FILTER TANGGAL (SEMUA DAFTAR)

Satu komponen **Bar Filter** dipakai identik di setiap daftar di setiap akun:

```
┌────────────────────────────────────────────────────────────────────────┐
│ 🔍 [ Cari: no tiket, nama, no hak, NIK, no sertipikat...        ] [Cari]│
│ 📅 Dari [ 01/10/2026 ▾ ]  Sampai [ 26/10/2026 ▾ ]   Cepat: [Hari ini]   │
│    [7 hari] [Bulan ini] [Bulan lalu] [Reset]                            │
│ Status [ Semua ▾ ]  Jenis [ Semua ▾ ]  Seksi [ Semua ▾ ]  [Urutkan ▾] │
└────────────────────────────────────────────────────────────────────────┘
 Menampilkan 1-20 dari 134 data    Per halaman [20 ▾]   [🖨 Cetak] 
```

**Pencarian pintar:**
- Satu kotak mencari ke banyak kolom sekaligus (No Tiket, nama pemohon, NIK, No Hak, No Sertipikat, nama petugas).
- Toleran huruf besar/kecil, spasi, dan tanda baca (`jp 26102026 001` tetap ketemu `JP26102026001`).
- Saran otomatis (autocomplete) saat mengetik.
- Hasil menandai potongan yang cocok (highlight).
- Pengecualian: halaman **Daftar Permohonan Staf dan Loket Penyerahan** tetap **kosong sampai ada pencarian** (aturan lama poin 8). Filter tanggal tetap tersedia.

**Filter tanggal:**
- Kalender rentang **Dari - Sampai**, plus tombol cepat.
- Tanggal yang dipakai mengikuti jenis daftar (lihat kolom "Tanggal acuan" di E).
- Filter dan urutan ikut di **Cetak** (periode tercetak di kop laporan).
- Disimpan sementara selama sesi (pindah halaman lalu kembali, filter tidak hilang).

**Sortir:** klik judul kolom (naik/turun); bawaan: terbaru di atas, kecuali disebut lain.

---

## E. DESAIN TABEL MAIN CONTENT AREA

### E.0 Aturan umum semua tabel
- Bar Filter (D) di atas tabel, **kartu ringkasan** di atas Bar Filter.
- Kolom pertama selalu **No** (urut), kolom terakhir selalu **Aksi**.
- **Chip status** memakai warna seragam (abu = menunggu, biru = diproses, hijau = selesai, merah = ditolak/terlambat, kuning = hampir jatuh tempo).
- **Lama** ditampilkan relatif (`2 hari 3 jam`), merah bila melewati batas.
- Pagination 20 data bawaan; di layar kecil tabel menjadi **kartu**.
- Klik baris = buka **Detail** (dengan Diagram Alur untuk tiket).
- Data demo diberi lencana **DEMO**.

### E.1 Daftar Permohonan (Database) , Super Admin / Admin / Kakan

Tanggal acuan: **Tanggal Daftar**.

| No | No Tiket | Pemohon | Jenis Permohonan | No Hak | Status | Posisi Saat Ini | Lama | Warkah | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | HM.123/Kedaton | 🔵 Sedang Diproses | Andi / Validator / Seksi 2 | 2h 3j | BT: Dipinjam | Detail |
| 2 | JP26102026002 | Sari | Pemecahan | HGB.55/Rajabasa | ⚪ Di Database | - | 5j | - | Detail |
| 3 | JP25102026004 | Doni | Roya | HM.77/Tanjung Karang | 🔴 Ditolak | Rina (penanggung jawab) | 1h | SU: Siap | Detail |

- Admin/Super Admin: tambah aksi **Lepas Tiket** (hanya bila posisi di Seksi 1/2) dan **Koreksi**.
- Kakan: tanpa aksi selain Detail; NIK tidak ditampilkan, data pribadi disamarkan.
- Kartu: *Total aktif · Di Database · Sedang Diproses · Ditolak · Macet · Selesai bulan ini*.
- Filter khusus: Status, Jenis, Posisi (Seksi 1/Seksi 2/Penyerahan), Ada Warkah, Macet, DEMO/ASLI (Super Admin).

### E.2 Daftar Berkas Permohonan , Loket Pendaftaran

Tanggal acuan: **Tanggal Daftar**. Isi: tiket yang pendaftarannya sudah lewat Tahap 3.

| No | No Tiket | Pemohon | Jenis | No Hak | Status | Posisi Saat Ini | Tgl Daftar | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | HM.123/Kedaton | 🔵 Diproses | Andi / Seksi 2 | 26/10 08:10 | Detail · Cetak Bukti |

- Kartu: *Pendaftaran hari ini · Menunggu ploting · Siap lanjut · Ditolak · Selesai hari ini*.
- Sub-tab di atas tabel: **Semua · Berjalan · Selesai · Ditolak** (Selesai dan Ditolak juga tersedia sebagai menu sendiri, E.8 dan E.9).

### E.3 Daftar Ploting

**a. Loket Pendaftaran, menu Ploting** (dua tab). Tanggal acuan: **Waktu Kirim**.

Tab **Menunggu Ploting**

| No | No Tiket | Pemohon | NIK | No HP | Waktu Kirim | Lama Menunggu | Petugas Pendaftar | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026003 | Wati | 1871xxxx | 0812xxxx | 26/10 09:15 | 3 jam | Petugas A | Detail · Cetak Ulang Antrian |

Tab **Sudah Ploting (Siap Lanjut)**

| No | No Tiket | Pemohon | Catatan Ploting | Waktu Selesai Ploting | Diploting Oleh | Aksi |
|---|---|---|---|---|---|---|
| 1 | JP26102026002 | Sari | Bidang sesuai peta | 26/10 11:00 | Sari / Loket Ploting | **Lanjut Pendaftaran** |

**b. Loket Ploting, Daftar Berkas Ploting.** Tanggal acuan: **Waktu Masuk**.

| No | No Tiket | Pemohon | Waktu Masuk | Lama Menunggu | Status | Aksi |
|---|---|---|---|---|---|---|
| 1 | JP26102026003 | Wati | 26/10 09:15 | 3 jam | ⚪ Menunggu | **Proses** |

**c. Loket Ploting, Daftar Berkas Selesai Ploting.** Tanggal acuan: **Waktu Kirim**.

| No | No Tiket | Pemohon | Catatan | Waktu Masuk | Waktu Kirim | Lama Proses | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026002 | Sari | Bidang sesuai peta | 26/10 08:40 | 26/10 11:00 | 2j 20m | Detail |

### E.4 Daftar Permohonan , Staf Seksi 1/2 dan Loket Penyerahan (halaman cari + Add)

Kosong sampai ada pencarian (No Tiket / No Hak / No Sertipikat). Tanggal acuan: **Tanggal Daftar**.

| No | No Tiket | Pemohon | Jenis | No Hak / Sertipikat | Status | Dipegang Oleh | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | HM.123/Kedaton | ⚪ Di Database | - | **ADD** |
| 2 | JP26102026005 | Sari | Pemecahan | HGB.55/Rajabasa | 🔵 Diproses | Andi / Validator / Seksi 2 | ADD (nonaktif) |
| 3 | JP25102026004 | Doni | Roya | HM.77/Tanjung Karang | 🔴 Ditolak | Rina (penanggung jawab) | ADD (nonaktif, kecuali Rina: **ADD ULANG**) |

Keterangan nonaktif ditampilkan sebagai teks di bawah tombol ("Sudah diambil oleh ...", "Menjadi tanggung jawab ...").

### E.5 Tiket Saya , Staf Seksi 1/2 dan Loket Penyerahan

Tanggal acuan: **Waktu Add**. Hanya tiket yang **saya Add** dan belum saya Kirim.

| No | No Tiket | Pemohon | Jenis | Peran/Bidang | Waktu Add | Lama di Saya | Status | Warkah | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | Alih Media / BT | 26/10 09:00 | 3 jam | 🔵 Diproses | BT: Dipinjam saya | **Proses** |

(Untuk Loket Penyerahan kolom *Peran/Bidang* dan *Warkah* tidak ditampilkan; Aksi = **Proses → Selesai**.)

### E.6 Daftar Permohonan Seksi , Kasi Seksi 1/2 (read only)

Tanggal acuan: **Waktu Add**. Hanya tiket yang **sudah di-Add staf seksi ini**.

| No | No Tiket | Pemohon | Jenis | Dipegang Oleh (Staf) | Peran/Bidang | Waktu Add | Lama | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | Budi S. | Peralihan Hak | Andi Pratama | Validator / SU | 26/10 09:00 | 3 jam | 🔵 Diproses | Detail (Diagram Alur) |

- Kartu: *Sedang diproses · Macet · Ditolak · Selesai bulan ini · Beban per staf*.
- Filter khusus: **Staf**, Peran, Macet.

### E.7 Daftar Warkah

**a. Petugas Warkah, Daftar Permintaan Warkah.** Tanggal acuan: **Tanggal Permintaan**.

| No | No Tiket | Jenis (BT/SU) | Pemohon | Jenis Permohonan | Tgl Permintaan | Diminta Oleh | Status | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | BT | Budi S. | Peralihan Hak | 26/10 08:10 | Petugas Loket A | ⚪ Diminta | **Terima** |

**b. Petugas Warkah, Daftar Berkas Pinjaman.** Tanggal acuan: **Tanggal Pinjam**.

| No | No Tiket | Jenis | Peminjam | Jabatan / Seksi | Tgl Pinjam | Batas Kembali | Sisa Waktu | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | BT | Andi Pratama | Staf / Seksi 2 | 26/10 | 02/11 | 6 hari | 🔵 Dipinjam | Detail · **Ubah Batas Kembali** · **Dikembalikan** |
| 2 | JP20102026007 | SU | Rina Sari | Staf / Seksi 1 | 20/10 | 24/10 | **Terlambat 2 hari** | 🔴 Terlambat | Detail · Ubah Batas · Dikembalikan |

**c. Petugas Warkah, Daftar Berkas Dikembalikan.** Tanggal acuan: **Tanggal Kembali**.

| No | No Tiket | Jenis | Peminjam | Seksi | Tgl Pinjam | Tgl Kembali | Pengembali | Lama Pinjam | Catatan Kondisi | Aksi |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | JP15102026002 | BT | Dedi | Seksi 2 | 16/10 | 22/10 | Dedi | 6 hari | Lengkap | Detail |

**d. Staf Seksi 1/2 dan Kasi Seksi 1/2, Daftar Warkah (read only).** Tanggal acuan: **Tanggal Pinjam** (atau Tgl Permintaan bila belum dipinjam).

| No | No Tiket | Jenis | Status | Peminjam | Jabatan / Seksi | Tgl Pinjam | Batas Kembali | Sisa Waktu | Pengembali | Tgl Kembali |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | JP26102026001 | BT | 🔵 Dipinjam | Andi Pratama | Staf / Seksi 2 | 26/10 | 02/11 | 6 hari | - | - |
| 2 | JP26102026001 | SU | ⚪ Siap | - | - | - | - | - | - | - |

- **Staf:** hanya warkah milik tiket yang ia Add atau yang dipinjam atas namanya.
- **Kasi:** semua warkah seksinya (peminjam dari seksinya).
- Filter khusus: Status, Jenis (BT/SU), Peminjam, **Terlambat saja**.
- Chip status: Diminta · Diterima · Siap · Dipinjam · Dikembalikan · Terlambat.
- **Tidak ada tombol aksi** di akun ini.

**e. Loket Pendaftaran, Daftar Warkah (read only, ringkas).**

| No | No Tiket | Jenis | Status | Peminjam / Seksi | Batas Kembali | Sisa Waktu |
|---|---|---|---|---|---|---|

### E.8 Daftar Selesai

Tersedia di **Loket Pendaftaran, Staf Seksi 1/2, Kasi Seksi 1/2** (juga Loket Penyerahan). Tanggal acuan: **Tanggal Selesai**.

| No | No Tiket | Pemohon | Jenis | Tgl Daftar | **Tgl Diserahkan** | Total Durasi | Diserahkan Oleh | Penerima | Aksi |
|---|---|---|---|---|---|---|---|---|---|
| 1 | JP20102026003 | Eka | Peralihan Hak | 20/10 | 25/10 | 5 hari | Loket Penyerahan / Maya | Eka (pemohon) | Detail · Cetak |

Isi per akun:
- **Loket Pendaftaran:** tiket yang ia daftarkan, sudah diserahkan.
- **Staf:** tiket yang pernah **saya proses** dan kini selesai (tambah kolom *Peran Saya* dan *Hasil Saya*).
- **Kasi 1/2:** tiket yang pernah diproses staf seksinya dan kini selesai.

### E.9 Daftar Ditolak

Tersedia di **Loket Pendaftaran, Staf Seksi 1/2, Kasi Seksi 1/2**. Tanggal acuan: **Tanggal Ditolak**.

| No | No Tiket | Pemohon | Jenis | Ditolak Oleh (Penanggung Jawab) | Seksi | Tgl Ditolak | **Alasan (Catatan)** | Lama Menunggu Perbaikan | Status | Aksi |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | JP25102026004 | Doni | Roya | Rina Sari | Seksi 1 | 25/10 14:20 | Kurang lengkap | 1 hari | 🔴 Menunggu Perbaikan | Detail · (Staf penolak) **ADD ULANG** |

Isi per akun:
- **Loket Pendaftaran:** semua tiket ditolak yang ia daftarkan (untuk menghubungi pemohon). Tanpa aksi Add Ulang.
- **Staf:** hanya yang **saya tolak** (aksi **Add Ulang** aktif).
- **Kasi 1/2:** yang ditolak staf seksinya (read only).
- Setelah Add Ulang: baris hilang dari Daftar Ditolak, muncul di Tiket Saya. Bila akhirnya Selesai, tampil di Daftar Selesai.

### E.10 Daftar Pengaduan

**a. Loket Pengaduan.** Tanggal acuan: **Tanggal Buat**.

| No | No Pengaduan | Pengadu | Jenis Pengaduan | Seksi Tujuan | Tgl Kirim | Status | Catatan Terakhir | Aksi |
|---|---|---|---|---|---|---|---|---|
| 1 | LP26102026001 | Hasan | Permasalahan Tanah | Seksi 2 | 26/10 | ⚪ Terkirim | - | Detail · Cetak Bukti |
| 2 | LP25102026002 | Ina | Pelayanan & Sikap | Subbag TU | 25/10 | 🔴 Dikembalikan | "Salah tujuan, kirim ke Seksi 5" | Detail · **Kirim Ulang** |

**b. Kasi Seksi 1-5 / Kasubbag TU (hanya pengaduan tujuan unit sendiri).** Tanggal acuan: **Tanggal Diterima**.

| No | No Pengaduan | Pengadu | Jenis | Ringkasan Uraian | Tgl Masuk | Status | Aksi |
|---|---|---|---|---|---|---|---|
| 1 | LP26102026001 | Hasan | Permasalahan Tanah | Batas tanah... | 26/10 | ⚪ Terkirim | Detail · **Terima** · **Dikembalikan** |
| 2 | LP24102026003 | Joko | Fasilitas | AC rusak... | 24/10 | 🔵 Diterima | Detail · **Selesai** |

**c. Pengaduan Selesai (Loket Pengaduan dan Kasi).** Tanggal acuan: **Tanggal Selesai**.

| No | No Pengaduan | Pengadu | Jenis | Seksi Penanganan | Tgl Kirim | Tgl Selesai | Durasi | Catatan Penyelesaian | Aksi |
|---|---|---|---|---|---|---|---|---|---|

**d. Kakan dan Super Admin (semua unit, read only).** Kolom tambahan: **Seksi Tujuan**, **Ditangani Oleh**; filter Seksi dan Status.

### E.11 Daftar Arsip

Hanya baca. Tanggal acuan: **Tanggal Selesai**. Muncul di Loket Pendaftaran, Loket Ploting, Petugas Warkah.

| No | No Tiket | Pemohon / Peminjam | Jenis | Tgl Selesai | Tgl Diarsipkan | Aksi |
|---|---|---|---|---|---|---|

### E.12 Kakan, Dashboard Warkah (kartu, bukan tabel)

```
┌─ Warkah ─────────────────────────────────────────────────────────┐
│ ┌─────────┐ ┌─────────┐ ┌──────────────┐ ┌───────────────┐        │
│ │ DIPROSES│ │ DIPINJAM│ │ LEWAT WAKTU  │ │ DIKEMBALIKAN  │        │
│ │   6     │ │   11    │ │  2 (merah)   │ │ 48 bln ini    │        │
│ └─────────┘ └─────────┘ └──────────────┘ └───────────────┘        │
└──────────────────────────────────────────────────────────────────┘
```

Rentang kartu mengikuti filter tanggal dashboard.

### E.13 Ringkasan: daftar apa ada di akun mana

| Daftar | Super Admin | Admin | Loket Pend. | Loket Ploting | Petugas Warkah | Staf 1-2 | Kasi 1-2 | Kasi 3-5/TU | Loket Penyerahan | Loket Pengaduan | Kakan |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Daftar Permohonan (Database) E.1 | ✔ | ✔ | | | | | | | | | ✔ |
| Daftar Berkas Permohonan E.2 | | | ✔ | | | | | | | | |
| Daftar Ploting E.3 | | | ✔ | ✔ | | | | | | | |
| Cari + Add E.4 | | | | | | ✔ | | | ✔ | | |
| Tiket Saya E.5 | | | | | | ✔ | | | ✔ | | |
| Daftar Permohonan Seksi E.6 | | | | | | | ✔ | | | | |
| Daftar Warkah E.7 | | | ✔ (e) | | ✔ (a-c) | ✔ (d) | ✔ (d) | | | | kartu saja |
| Daftar Selesai E.8 | | | ✔ | | | ✔ | ✔ | | ✔ | | |
| Daftar Ditolak E.9 | | | ✔ | | | ✔ | ✔ | | | | |
| Daftar Pengaduan E.10 | ✔ | | | | | | ✔ | ✔ | | ✔ | ✔ |
| Daftar Arsip E.11 | | | ✔ | ✔ | ✔ | | | | | | |

---

## F. MATRIKS MENU TERBARU (menggantikan bagian 11)

| Akun | Menu |
|---|---|
| **Super Admin** | (tetap) Dashboard Sistem, Daftar Permohonan, Daftar Pengaduan, Skenario Demo, Lihat/Coba Sebagai Akun, Manajemen Akun, Master Data & Setting, Data Demo, Log, Laporan, Backup |
| **Admin** | Dashboard, Manajemen Akun, Manajemen Loket Pendaftaran, Manajemen Loket Pengaduan, Master Data, Setting, Daftar Permohonan + **Lepas Tiket** (Seksi 1-2), Log, Laporan |
| **Loket Pendaftaran** | Dashboard, Pendaftaran Baru, Daftar Berkas Permohonan, Ploting, Daftar Warkah, Daftar Selesai, Daftar Ditolak, Arsip, Laporan |
| **Loket Ploting** | Dashboard, Daftar Berkas Ploting, Daftar Berkas Selesai Ploting, Arsip, Laporan |
| **Petugas Warkah** | Dashboard, Permintaan Warkah, Berkas Pinjaman, Berkas Dikembalikan, Arsip, Laporan |
| **Staf Seksi 1-2** | Dashboard, Daftar Permohonan (cari + Add), Tiket Saya, Daftar Warkah, Daftar Selesai, Daftar Ditolak, Laporan |
| **Kasi Seksi 1-2** | Dashboard, Daftar Permohonan Seksi, Daftar Warkah, Daftar Selesai, Daftar Ditolak, Daftar Pengaduan, Pengaduan Selesai, Laporan |
| **Kasi 3/4/5, Kasubbag TU** | Dashboard, Daftar Pengaduan, Pengaduan Selesai, Laporan |
| **Loket Penyerahan** | Dashboard, Antrian Penyerahan (cari + Add), Tiket Saya, Daftar Selesai, Laporan |
| **Loket Pengaduan** | Dashboard, Buat Pengaduan, Daftar Pengaduan, Pengaduan Selesai, Laporan |
| **Kakan** | Dashboard, Daftar Permohonan, Daftar Warkah *(lihat H.2)*, Daftar Pengaduan, Laporan |

Catatan: menu **Atur Waktu Peminjaman** dilebur ke aksi *Ubah Batas Kembali* di Berkas Pinjaman; menu **Peminjaman Warkah** (ajukan/minta tambah waktu) dihapus dari Staf, Kasi, Loket Pendaftaran.

---

## G. PERUBAHAN RANCANGAN DATA

| Tabel | Perubahan |
|---|---|
| `tiket` | tambah `penanggung_jawab_id` (staf penolak, isi saat Ditolak, kosong saat selesai/Add ulang); tambah `tgl_selesai`; `status` tambah nilai *Ditolak-Menunggu Perbaikan* |
| `tiket_alur` | `hasil` untuk Loket Penyerahan hanya *selesai*; tambah penanda `add_ulang` (bool) |
| `warkah_pinjam` | hapus alur permintaan dari Staf/Kasi; `diminta_oleh` selalu Petugas Loket Pendaftaran; peminjam diisi **Petugas Warkah** |
| `warkah_perpanjangan` | tetap, `oleh_user_id` selalu Petugas Warkah |
| `setting` | hapus opsi *persetujuan_akhir_kakan* |
| Semua tabel daftar | indeks pada kolom tanggal acuan (E) dan kolom pencarian (D) agar filter rentang tanggal cepat |

Aturan teknis baru:
- Add pada tiket Ditolak hanya lolos bila `user_id = penanggung_jawab_id` (validasi server).
- Lepas Tiket hanya lolos bila posisi tiket di user dengan `seksi IN (1, 2)`.
- Kasi Seksi 1/2 melihat tiket bila ada `tiket_alur.seksi = seksi Kasi` (staf sudah Add).

---

## H. YANG MASIH PERLU KAMU KONFIRMASI

1. **Loket Pendaftaran masih perlu Daftar Warkah (read only)?** Di jawabanmu, daftar warkah hanya disebut untuk Staf 1-2, Kasi 1-2, dan kartu Kakan. Saya tetap menyediakan versi ringkas di Loket Pendaftaran supaya bisa menjawab warga ("warkah saya sudah siap?"). Hapus saja bila tidak perlu.
2. **Kakan: Daftar Warkah detail atau hanya kartu?** Pesanmu: Kakan hanya kartu dashboard untuk warkah, tetapi di poin 5 Kakan memantau "daftar warkah". Saya pakai **kartu saja** (E.12) dan menu *Daftar Warkah* Kakan opsional (read only). Pilih salah satu.
3. **Lepas Tiket untuk tiket Ditolak:** bila staf penanggung jawab cuti/pindah, apakah Admin boleh melepasnya dan menunjuk staf lain? (Default di dokumen ini: boleh, lewat Lepas Tiket + catatan.)
4. **Syarat Add di Loket Penyerahan:** hanya peringatan (default) atau diblokir bila tiket belum pernah dikerjakan Seksi 1/2?
5. **Angka bawaan:** batas *Macet* (3 hari kerja), durasi pinjam warkah (7 hari), hari ke Arsip (30 hari). Sesuaikan bila ada aturan kantor.
