# PRD — LOKET 2026

**Produk:** Sistem Pelayanan Berkas Permohonan & Pengaduan
**Kantor:** Pertanahan Kota Bandar Lampung (BALAM) — BPN
**Versi dokumen:** 3.0 (V2 diterapkan)
**Tanggal:** 27 Oktober 2026
**Status:** Acuan desain — sudah diimplementasikan
**Sumber:** `alur/BLUEPRINT_REFISI_V2.md`, `alur/00`–`16`

> Dokumen ini menjelaskan produk V2 yang sudah diterapkan. Status implementasi kode dirangkum di `alur/00-Blueprint-Utama.md` §9 dan pada bagian atas setiap dokumen alur.

---

## 1. Problem Statement

### 1.1 Kondisi saat ini

Pelayanan berkas pertanahan di kantor masih bergantung pada **berkas fisik yang berpindah antar-meja** dan pencatatan yang tersebar. Aplikasi versi sebelumnya mendigitalkan sebagian langkah, tetapi belum mengikuti kebiasaan kerja nyata:

1. **Pemohon tidak tahu posisi berkasnya.** Pertanyaan "berkas saya sudah sampai mana?" hanya bisa dijawab dengan menelusuri meja atau bertanya ke petugas. Belum ada satu sumber kebenaran internal yang dipakai semua petugas.
2. **Berkas menumpuk tanpa penanda.** Tidak ada cara cepat mengetahui tiket mana yang sudah terlalu lama diam di satu stasiun, sehingga keterlambatan baru diketahui setelah pemohon menanyakan.
3. **Alur dipaksa berurutan.** Versi lama mengunci urutan Verifikator → Validator → Alih Media, padahal di lapangan berkas mengikuti **posisi fisik** dan siapa pun di Seksi 1/2 yang memegang berkas dapat mengerjakannya.
4. **Penolakan berkas tidak jelas pemiliknya.** Mekanisme revisi P0..Pn mengembalikan berkas ke loket tanpa penanggung jawab yang tegas, sehingga berkas bisa berpindah-pindah tanpa ada yang menyelesaikan.
5. **Warkah (berkas pendukung BT/SU) tidak terlacak.** Permintaan warkah diajukan dari banyak akun berbeda, pinjaman fisik tidak memiliki batas kembali, dan tidak ada penanda terlambat.
6. **Data pemohon tidak konsisten.** Nomor tiket diisi bebas dengan format berbeda-beda, sulit dicari, dan tidak dapat dikoreksi saat nomor resmi dari aplikasi pemerintah keluar.
7. **Tidak ada kanal pengaduan terstruktur.** Keluhan masyarakat masuk lewat berbagai cara tanpa nomor, tanpa status, dan tanpa bukti penyelesaian.
8. **Pencarian dan filter tidak seragam.** Setiap halaman punya perilaku berbeda; tidak ada filter rentang tanggal, sehingga rekap bulanan dikerjakan manual.
9. **Pemberitahuan ke petugas tidak konsisten.** Notifikasi berbasis surel bergantung pada konfigurasi server dan alamat penerima yang sering kosong/tidak valid, sehingga pemberitahuan gagal tepat saat dibutuhkan. Kanal ini sudah dipensiunkan dan digantikan notifikasi in-app.

### 1.2 Dampak

| Dampak | Akibat |
|---|---|
| Ketidakpastian bagi pemohon | Kunjungan berulang, keluhan lisan, kepercayaan layanan menurun |
| Berkas tertahan | Durasi layanan memanjang tanpa terukur |
| Riwayat tidak lengkap | Sulit diaudit saat ada sengketa atau pemeriksaan |
| Beban administratif | Rekap manual, duplikasi entri, kesalahan penomoran |
| Ketergantungan pada satu petugas | Cuti/pindah tugas membuat berkas berhenti |

### 1.3 Peluang

Satu basis data terpusat dengan pola kerja **cari → ADD → proses → KIRIM**, penanda macet otomatis, dan **No. Berkas** resmi dapat menjawab sebagian besar masalah di atas tanpa menambah beban administratif baru.

---

## 2. Goals

### 2.1 Tujuan produk

| # | Tujuan | Ukuran keberhasilan |
|---|---|---|
| G1 | Berkas mudah dicari oleh petugas | ≥ 80% pencarian No Tiket / No. Berkas berhasil menemukan tiket; penurunan pertanyaan posisi berkas |
| G2 | Setiap berkas selalu punya pemegang yang jelas | 100% tiket `diproses` memiliki tepat satu pemegang |
| G3 | Keterlambatan terdeteksi otomatis | Tiket melewati batas langsung berchip MACET tanpa intervensi manual |
| G4 | Penolakan berkas kembali ke penanggung jawab | 0 tiket `ditolak` tanpa `penanggung_jawab` |
| G5 | Warkah terlacak dari permintaan sampai pengembalian | 100% pinjaman memiliki batas kembali & penanda terlambat |
| G6 | Pengaduan punya nomor, status, dan bukti selesai | 100% pengaduan berstatus akhir |
| G7 | Rekap dapat dihasilkan tanpa kerja manual | Laporan per periode tersedia ≤ 3 klik dari dashboard |
| G8 | Notifikasi tidak bergantung pada email | 0 alur bisnis yang gagal karena kegagalan notifikasi |

### 2.2 Tujuan organisasi

- Menyeragamkan praktik kerja Seksi 1 dan 2 tanpa memaksa urutan yang tidak ada di lapangan.
- Menyediakan data untuk pengawasan pimpinan (Kakan) tanpa menambah beban persetujuan.
- Menyiapkan dasar audit: siapa, kapan, melakukan apa, pada berkas mana.

### 2.3 Non-goals

- Bukan pengganti aplikasi nasional (KKP/ Sentuh Tanahku); nomor resmi tetap berasal dari sana.
- Bukan sistem kearsipan elektronik penuh (scan dokumen tetap terbatas pada alih media kerja).
- Bukan kanal komunikasi langsung ke pemohon (tidak ada SMS/email/chat).
- Bukan sistem kepegawaian atau penilaian kinerja formal.

---

## 3. Target Users

### 3.1 Pengguna internal

| # | Persona | Kode role | Kebutuhan utama | Frekuensi |
|---|---|---|---|---|
| U1 | **Super Admin** (operator TI) | `super_admin` | Menjaga sistem, demo, audit, backup | Harian |
| U2 | **Admin** (operator kantor) | `admin` | Kelola akun/master/setting, lepas tiket tertahan | Harian |
| U3 | **Petugas Loket Pendaftaran** | `loket_pendaftaran` | Daftarkan permohonan cepat & benar | Sangat sering |
| U4 | **Petugas Loket Ploting** | `loket_ploting` | Selesaikan ploting agar pendaftaran lanjut | Sering |
| U5 | **Petugas Warkah** | `petugas_warkah` | Catat permintaan, pinjaman, pengembalian | Sering |
| U6 | **Staf Seksi 1/2** | `staf_seksi` + `seksi` | Ambil & selesaikan berkas di mejanya | Sangat sering |
| U7 | **Kasi Seksi 1/2** | `kasi_seksi` + `seksi` | Pantau beban & penolakan seksinya | Harian |
| U8 | **Kasi Seksi 3/4/5 & Kasubbag TU** | `kasi_unit` / `kasubbag_tu` | Tangani pengaduan unitnya | Sesuai masuk |
| U9 | **Petugas Loket Penyerahan** | `loket_penyerahan` | Serahkan berkas & tutup tiket | Sangat sering |
| U10 | **Petugas Loket Pengaduan** | `loket_pengaduan` | Catat & teruskan keluhan | Sesuai masuk |
| U11 | **Kakan** | `kakan` | Pantau kinerja layanan | Mingguan |

### 3.2 Pengguna eksternal

| # | Persona | Akses | Kebutuhan |
|---|---|---|---|
| U12 | **Pengadu** | Lewat Loket Pengaduan (tatap muka) | Keluhan tercatat & ditindaklanjuti |
| U13 | **Auditor internal** | Via Admin/Super Admin | Riwayat lengkap & log |

### 3.3 Karakteristik pengguna

- Sebagian pengguna **kurang terbiasa aplikasi kompleks** — antarmuka harus konsisten, label berbahasa Indonesia, dan aksi utama selalu terlihat.
- Pengguna loket bekerja **cepat dengan antrean** — butuh pencarian instan dan sedikit klik.
- Pengguna back office bekerja **per berkas fisik** — butuh penanda "dipegang siapa" yang jelas.
- Pimpinan butuh **ringkasan**, bukan detail teknis.

---

## 4. User Stories

Format: `US-xx` — *Sebagai {persona}, saya ingin {tujuan} agar {manfaat}.*
Prioritas: **M** (Must), **S** (Should), **C** (Could).

### 4.1 Akses & akun

| ID | Cerita | Prio |
|---|---|---|
| US-01 | Sebagai petugas, saya ingin login dengan username **atau** email agar tidak lupa kredensial. | M |
| US-02 | Sebagai admin, saya ingin membuat akun dengan jabatan + seksi agar hak akses tepat sejak awal. | M |
| US-03 | Sebagai admin, saya ingin menonaktifkan akun tanpa menghapus riwayatnya agar audit tetap utuh. | M |
| US-04 | Sebagai super admin, saya ingin melihat sistem sebagai akun lain (read-only) agar dapat memverifikasi tampilan. | S |

### 4.2 Pendaftaran & ploting

| ID | Cerita | Prio |
|---|---|---|
| US-05 | Sebagai petugas loket, saya ingin mendaftarkan permohonan lewat wizard 4 tahap agar tidak ada data yang terlewat. | M |
| US-06 | Sebagai petugas loket, saya ingin nomor tiket dibuat otomatis (JP+tanggal+urut) agar konsisten. | M |
| US-07 | Sebagai petugas loket, saya ingin mengedit nomor tiket dengan alasan agar sesuai nomor resmi tanpa kehilangan riwayat. | M |
| US-08 | Sebagai petugas loket, saya ingin mencetak antrean ploting agar berkas fisik bisa dibawa ke loket ploting. | M |
| US-09 | Sebagai petugas ploting, saya ingin memproses daftar ploting dan mengirim hasilnya agar pendaftaran bisa dilanjutkan. | M |
| US-10 | Sebagai petugas loket, saya ingin menandai kebutuhan warkah (BT/SU/keduanya) agar permintaan dibuat otomatis. | M |

### 4.3 Back office (Staf & Kasi)

| ID | Cerita | Prio |
|---|---|---|
| US-11 | Sebagai staf seksi, saya ingin mencari tiket berdasarkan No Tiket/No Hak lalu ADD agar hanya saya yang mengerjakannya. | M |
| US-12 | Sebagai staf seksi, saya ingin melihat siapa yang memegang tiket agar tidak terjadi bentrok. | M |
| US-13 | Sebagai staf seksi, saya ingin menyelesaikan tiket dengan catatan agar berkas lanjut ke stasiun berikutnya. | M |
| US-14 | Sebagai staf seksi, saya ingin menolak tiket dengan alasan agar berkas diperbaiki dulu. | M |
| US-15 | Sebagai staf penolak, saya ingin ADD ULANG tiket yang saya tolak agar saya yang menyelesaikannya. | M |
| US-16 | Sebagai staf seksi, saya ingin melihat tiket yang saya pegang melewati batas (MACET) agar saya prioritaskan. | M |
| US-17 | Sebagai kasi, saya ingin melihat semua tiket yang di-ADD staf seksi saya agar dapat mengawasi beban kerja. | M |
| US-18 | Sebagai kasi, saya ingin melihat daftar tiket yang ditolak seksinya agar dapat menindaklanjuti. | S |

### 4.4 Warkah

| ID | Cerita | Prio |
|---|---|---|
| US-19 | Sebagai petugas warkah, saya ingin menerima permintaan & menandai siap agar staf tahu warkah bisa diambil. | M |
| US-20 | Sebagai petugas warkah, saya ingin mencatat serah terima pinjaman (peminjam, seksi, batas kembali) agar terlacak. | M |
| US-21 | Sebagai petugas warkah, saya ingin sistem menandai TERLAMBAT otomatis agar keterlambatan terlihat. | M |
| US-22 | Sebagai petugas warkah, saya ingin mengubah batas kembali dengan alasan agar perpanjangan tercatat. | M |
| US-23 | Sebagai staf, saya ingin melihat status warkah tiket saya (read-only) agar tahu kapan bisa mengambil. | M |

### 4.5 Penyerahan & akhir alur

| ID | Cerita | Prio |
|---|---|---|
| US-24 | Sebagai petugas penyerahan, saya ingin mencari tiket & ADD agar saya yang menyerahkannya. | M |
| US-25 | Sebagai petugas penyerahan, saya ingin mencatat nama penerima lalu SELESAI agar tiket tertutup dengan bukti. | M |
| US-26 | Sebagai petugas penyerahan, saya tidak ingin bisa menolak tiket agar alur tidak berputar di ujung. | M |
| US-27 | Sebagai admin, saya ingin melepas tiket yang tertahan di Seksi 1/2 dengan alasan agar berkas tidak berhenti saat staf berhalangan. | M |
| US-28 | Sebagai sistem, saya ingin memindahkan tiket selesai ke arsip setelah N hari agar daftar aktif tetap bersih. | S |

### 4.6 Pengaduan

| ID | Cerita | Prio |
|---|---|---|
| US-29 | Sebagai petugas pengaduan, saya ingin membuat pengaduan dengan nomor LP agar keluhan tercatat. | M |
| US-30 | Sebagai kasi unit, saya ingin menerima/menutup pengaduan agar ada bukti penyelesaian. | M |
| US-31 | Sebagai kasi unit, saya ingin mengembalikan pengaduan yang salah tujuan agar diteruskan ke unit tepat. | M |
| US-32 | Sebagai petugas pengaduan, saya ingin mengirim ulang pengaduan yang dikembalikan agar tidak membuat nomor baru. | S |

### 4.7 Notifikasi & tampilan

| ID | Cerita | Prio |
|---|---|---|
| US-35 | Sebagai petugas, saya ingin notifikasi in-app agar tahu perubahan tanpa memeriksa email. | M |
| US-36 | Sebagai pengguna, saya ingin pencarian pintar & filter tanggal seragam di semua daftar agar cepat menemukan data. | M |
| US-37 | Sebagai pimpinan, saya ingin dashboard ringkas read-only agar dapat memantau tanpa mengubah data. | S |
| US-38 | Sebagai pengguna, saya ingin semua laporan tercetak dengan kop & rentang tanggal agar dapat diarsipkan. | S |

---

## 5. Functional Requirements

Prioritas: **M** wajib untuk rilis target; **S** penting, dapat menyusul; **C** opsional.

### 5.1 Autentikasi & Otorisasi (`FR-A`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-A1 | Login menerima **username atau email** + password; akun harus `is_active`. | M | `01` |
| FR-A2 | Rate-limit login 6 percobaan/menit per klien; pesan gagal tidak membocorkan apakah akun ada. | M | `01` |
| FR-A3 | Setelah login, pengguna diarahkan ke dashboard sesuai jabatan. | M | `01` |
| FR-A4 | Middleware `role` menjaga setiap rute; rute tanpa hak → 403. | M | `01` |
| FR-A5 | Setiap akun memiliki **jabatan** dan (bila berlaku) **seksi**; data difilter berdasarkan seksi. | M | `01` |
| FR-A6 | Kakan bersifat read-only; tidak ada aksi pengubah status. | M | `12` |
| FR-A7 | Akun non-aktif kehilangan akses tanpa menghapus riwayat. | M | `01` |
| FR-A8 | Super Admin dapat impersonate **read-only** dengan banner & audit log. | S | `11` |
| FR-A9 | Logout menginvalidasi sesi & meregenerasi token. | M | `01` |

### 5.2 Pendaftaran (`FR-R`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-R1 | Pendaftaran memakai **wizard 4 tahap**: biodata+nomor, ploting, data permohonan, kebutuhan warkah. | M | `02` |
| FR-R2 | Nomor tiket otomatis `JP{ddmmyyyy}{NNN}`, unik, dapat diubah manual saat input. | M | `02` |
| FR-R3 | Nomor tiket dapat diedit kemudian dengan **alasan wajib**; riwayat nomor tersimpan dan nomor lama tetap dapat dicari. | M | `02` |
| FR-R4 | Tahap 2: bila ploting **belum**, sistem mencetak antrean dan pendaftaran tertahan sampai ploting selesai. | M | `02` |
| FR-R5 | Tahap 3: jenis permohonan, No Hak sekarang/sebelumnya, letak tanah, jumlah bidang (≥1). | M | `02` |
| FR-R6 | Tahap 4 membuat permintaan warkah otomatis sesuai pilihan (BT / SU / keduanya / tidak). | M | `02` |
| FR-R7 | Setelah Tahap 4, tiket berstatus `di_database` dan tampil di antrean Staf Seksi 1/2. | M | `02` |
| FR-R8 | Loket Pendaftaran dapat mencetak bukti pendaftaran & antrean ploting. | M | `02` |
| FR-R9 | Loket Pendaftaran melihat daftar berkas miliknya dengan filter status & tanggal. | M | `02` |
| FR-R10 | **No. Berkas** diisi manual di Tahap 1, disalin ke tiket saat Tahap 4, tampil di semua tabel terkait. Format bebas, tidak unik, boleh diubah pembuat/Admin kapan saja. | M | `02` |

### 5.3 Ploting (`FR-P`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-P1 | Daftar ploting menampung berkas dengan status menunggu, diurutkan waktu masuk. | M | `03` |
| FR-P2 | Petugas ploting memproses satu berkas: isi catatan, lalu KIRIM. | M | `03` |
| FR-P3 | Setelah KIRIM, berkas pindah ke daftar "Selesai Ploting" dan tidak dapat diubah petugas ploting. | M | `03` |
| FR-P4 | Loket Pendaftaran dapat melanjutkan pendaftaran dari tab "Sudah Ploting". | M | `03` |
| FR-P5 | Lama menunggu dihitung dan ditampilkan; melewati batas → penanda visual. | S | `03` |

### 5.4 Staf Seksi 1/2 (`FR-B`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-B1 | Halaman Cari + ADD **kosong sampai ada pencarian** (No Tiket / No Hak / No Sertipikat). | M | `04` |
| FR-B2 | ADD hanya untuk tiket `di_database`; server menolak bila tiket sudah dipegang akun lain. | M | `04` |
| FR-B3 | Setelah ADD: status `diproses`, pemegang = user, tiket muncul di **Tiket Saya**. | M | `04` |
| FR-B4 | Selesai + catatan → status kembali `di_database`, siap di-ADD stasiun berikutnya. | M | `04` |
| FR-B5 | Tolak + catatan → status `ditolak`, `penanggung_jawab_id` = user penolak. | M | `04` |
| FR-B6 | Tombol ADD untuk tiket `ditolak` **nonaktif** bagi akun selain penanggung jawab; server memvalidasi. | M | `04` |
| FR-B7 | Penanggung jawab dapat **ADD ULANG** tiketnya; riwayat penolakan tetap tampil. | M | `04` |
| FR-B8 | Catatan wajib (min. 3 karakter) pada Selesai dan Tolak. | M | `04` |
| FR-B9 | Tiket yang dipegang melewati batas hari ditandai **MACET** (tanpa mengubah status). | M | `04` |
| FR-B10 | Staf melihat daftar warkah tiketnya **read-only**. | M | `04` |
| FR-B11 | Staf melihat Daftar Selesai & Daftar Ditolak pribadi. | M | `04` |

### 5.5 Kasi Seksi 1/2 (`FR-K`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-K1 | Kasi hanya melihat tiket yang **sudah di-ADD** staf seksinya. | M | `05` |
| FR-K2 | Daftar Permohonan Seksi menampilkan staf, peran, waktu ADD, lama, status. | M | `05` |
| FR-K3 | Kasi tidak memiliki aksi ADD/KIRIM/Tolak/Lepas/Selesai. | M | `05` |
| FR-K4 | Kasi melihat Daftar Ditolak seksinya, termasuk penanda "sudah ditindaklanjuti". | S | `05` |
| FR-K5 | Kasi melihat Daftar Warkah seksinya (read-only, filter terlambat). | S | `05` |
| FR-K6 | Kasi menerima & menutup pengaduan untuk seksinya. | M | `05` |

### 5.6 Warkah (`FR-W`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-W1 | Permintaan warkah dibuat otomatis oleh sistem dari Tahap 4 (`diminta`). | M | `06` |
| FR-W2 | Petugas warkah menerima (`diterima`) lalu menandai siap (`siap`). | M | `06` |
| FR-W3 | Serah terima mencatat peminjam, seksi, tanggal pinjam, batas kembali, kondisi → `dipinjam`. | M | `06` |
| FR-W4 | Warkah melewati batas kembali otomatis berstatus/penanda `terlambat`. | M | `06` |
| FR-W5 | Pengembalian mencatat pengembali, tanggal, kondisi → `dikembalikan`. | M | `06` |
| FR-W6 | Perpanjangan dilakukan Petugas Warkah: batas baru + alasan wajib; riwayat perubahan tersimpan. | M | `06` |
| FR-W7 | Staf/Kasi/Loket Pendaftaran **tidak** dapat mengubah status warkah. | M | `06` |
| FR-W8 | Satu tiket dapat memiliki dua warkah (BT dan SU) dengan status terpisah. | M | `06` |

### 5.7 Loket Penyerahan (`FR-S`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-S1 | Loket Penyerahan memakai pola cari + ADD yang sama. | M | `07` |
| FR-S2 | Satu-satunya aksi penutup adalah **SELESAI**; tidak ada Tolak. | M | `07` |
| FR-S3 | SELESAI wajib mencatat nama penerima (dan identitas bila kuasa). | M | `07` |
| FR-S4 | SELESAI mengisi `tanggal_selesai` dan mengubah status menjadi `selesai` (final). | M | `07` |
| FR-S5 | Tiket selesai tampil di Daftar Selesai semua akun terkait. | M | `07` |

### 5.8 Pengaduan (`FR-G`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-G1 | Loket Pengaduan membuat pengaduan dengan nomor `LP{ddmmyyyy}{NNN}` yang unik. | M | `08` |
| FR-G2 | Pengaduan memiliki jenis, uraian (min. 20 karakter), seksi tujuan, dan lampiran opsional. | M | `08` |
| FR-G3 | KIRIM mengubah status `terkirim` dan memberi notifikasi ke unit tujuan. | M | `08` |
| FR-G4 | Kasi unit dapat `Terima`, `Tutup` (catatan hasil wajib), atau `Kembalikan` (alasan wajib). | M | `09` |
| FR-G5 | Pengaduan `dikembalikan` dapat **dikirim ulang** tanpa membuat nomor baru. | S | `08` |
| FR-G6 | Pengaduan melewati batas tanggap ditandai TERLAMBAT. | S | `09` |
| FR-G7 | Pengaduan `selesai` bersifat final. | M | `09` |

### 5.9 Admin & Super Admin (`FR-M`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-M1 | Admin mengelola akun: buat/ubah/nonaktifkan/reset password. | M | `10` |
| FR-M2 | Admin mengelola master data (jenis permohonan, seksi, penomoran). | M | `10` |
| FR-M3 | Admin mengubah Setting: batas macet, batas warkah, N arsip, batas tanggap. | M | `10` |
| FR-M4 | Admin dapat **Lepas Tiket** hanya untuk tiket `diproses`/`ditolak` di Seksi 1/2, dengan alasan wajib. | M | `10` |
| FR-M5 | Lepas Tiket mengosongkan penanggung jawab, menandai `dilepas_admin`, dan mencatat riwayat. | M | `10` |
| FR-M6 | Admin melihat log aktivitas dengan filter pelaku/aksi/tanggal. | S | `10` |
| FR-M7 | Super Admin menjalankan Skenario Demo (normal, ditolak, warkah, macet, pengaduan). | C | `11` |
| FR-M8 | Super Admin mengelola Data Demo (seed/reset) tanpa menyentuh data non-demo. | C | `11` |
| FR-M9 | Super Admin mengunduh backup basis data; setiap unduhan tercatat. | S | `11` |

### 5.10 Kakan (`FR-N`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-N1 | Dashboard Kakan menampilkan kartu berjalan, selesai, ditolak, macet, pengaduan, durasi rata-rata. | M | `12` |
| FR-N2 | Kakan melihat daftar permohonan/pengaduan read-only dengan NIK & nama disamarkan. | M | `12` |
| FR-N3 | Tidak ada Persetujuan Akhir; Kakan tidak mengubah status. | M | `12` |
| FR-N4 | Kakan dapat mengunduh laporan periode. | S | `12` |

### 5.11 Akhir alur & arsip (`FR-E`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-E1 | Daftar Selesai tersedia di Loket Pendaftaran, Staf, Kasi, Loket Penyerahan. | M | `13` |
| FR-E2 | Daftar Ditolak tersedia di Loket Pendaftaran, Staf, Kasi (+ kartu Kakan). | M | `13` |
| FR-E3 | Tiket `selesai` berpindah ke Arsip otomatis setelah N hari (default 30) via scheduler. | S | `13` |
| FR-E4 | Arsip bersifat read-only; warkah terkait ikut terkunci. | M | `13` |
| FR-E5 | Selesai tidak dapat dibatalkan dari UI; koreksi lewat catatan Admin. | M | `13` |

### 5.12 Komponen umum (`FR-U`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-U1 | Semua daftar memakai pola: kartu ringkasan → filter → tab → tabel → pagination. | M | `15` |
| FR-U2 | **Pencarian pintar** multi-field dengan debounce 400 ms, min. 2 karakter. | M | `15` |
| FR-U3 | **Filter rentang tanggal** tersedia di semua daftar dengan 5 preset + kustom. | M | `15` |
| FR-U4 | Kolom pertama `No`, terakhir `Aksi`; pagination 20 baris; filter tersimpan di query string. | M | `15` |
| FR-U5 | Chip status memakai teks + warna (tidak warna saja). | M | `15` |
| FR-U6 | Aksi pengubah status wajib konfirmasi berisi akibat. | M | `15` |
| FR-U7 | Cetak laporan memuat kop kantor, rentang tanggal, dan waktu cetak. | S | `15` |

### 5.13 Notifikasi (`FR-F`)

| ID | Kebutuhan | Prio | Sumber |
|---|---|---|---|
| FR-F1 | Notifikasi in-app disimpan per penerima dengan status baca. | M | `16` |
| FR-F2 | Lonceng menampilkan jumlah belum dibaca; halaman daftar punya tab Belum Dibaca/Semua. | M | `16` |
| FR-F3 | Seluruh peristiwa pada matriks `16` §4 memicu notifikasi ke penerima yang berhak. | M | `16` |
| FR-F4 | Pembuatan notifikasi setelah commit; kegagalan tidak membatalkan aksi utama. | M | `16` |
| FR-F5 | Deduplikasi peristiwa sama dalam 60 detik per penerima. | S | `16` |
| FR-F6 | Pengguna dapat mematikan tipe tertentu (kecuali tipe wajib). | S | `16` |
| FR-F7 | Notifikasi > 90 hari yang sudah dibaca dibersihkan scheduler. | C | `16` |
| FR-F8 | **Tidak ada** pengiriman email/PDF perbaikan dalam alur bisnis. | M | `16` |

---

## 6. Non-Functional Requirements

### 6.1 Performa

| ID | Kebutuhan | Target |
|---|---|---|
| NFR-P1 | Waktu muat daftar (20 baris) | ≤ 1,5 detik (p95) pada data 100 ribu tiket |
| NFR-P2 | Respons pencarian pintar | ≤ 800 ms setelah debounce |
| NFR-P3 | Aksi ADD/KIRIM/SELESAI | ≤ 1 detik |
| NFR-P4 | Dashboard ringkasan | ≤ 2 detik; boleh memakai cache 1–5 menit |
| NFR-P5 | Cetak/export 1 bulan data | ≤ 10 detik |

**Pendukung:** indeks pada kolom tanggal acuan & kolom pencarian (`BLUEPRINT` G), pagination server-side, hindari N+1.

### 6.2 Keamanan

| ID | Kebutuhan |
|---|---|
| NFR-S1 | Password di-hash (bcrypt/argon2); tidak pernah dicatat di log. |
| NFR-S2 | Seluruh rute internal memerlukan autentikasi + otorisasi peran/seksi. |
| NFR-S3 | CSRF protection aktif pada semua form; validasi di server untuk setiap aksi status. |
| NFR-S4 | Rate-limit login 6 percobaan/menit per klien. |
| NFR-S5 | Escaping output Blade; tidak ada `{!! !!}` untuk data pengguna. |
| NFR-S6 | Upload lampiran dibatasi tipe & ukuran, disimpan di luar web root atau dengan validasi ketat. |
| NFR-S7 | Audit log tidak dapat diubah/dihapus dari UI. |

### 6.3 Privasi data

| ID | Kebutuhan |
|---|---|
| NFR-D1 | NIK tidak ditampilkan pada Kakan dan halaman yang tidak memerlukannya. |
| NFR-D2 | Penyamaran dilakukan di level query/resource, bukan hanya di view. |
| NFR-D3 | Data pribadi tidak masuk ke pesan notifikasi. |
| NFR-D5 | Retensi log & notifikasi mengikuti Setting (default 90 hari). |

### 6.4 Keandalan & pemulihan

| ID | Kebutuhan |
|---|---|
| NFR-R1 | Transisi status memakai transaksi DB; gagal = tidak ada perubahan separuh. |
| NFR-R2 | Notifikasi gagal tidak menggagalkan aksi bisnis (setelah commit, error ditelan + dilaporkan). |
| NFR-R3 | Backup basis data harian; unduhan manual oleh Super Admin. |
| NFR-R4 | Target ketersediaan jam kerja 99% (single server, tanpa HA). |
| NFR-R5 | Scheduler arsip & pembersihan notifikasi harus idempoten (aman dijalankan ulang). |

### 6.5 Usability & aksesibilitas

| ID | Kebutuhan |
|---|---|
| NFR-U1 | Konsisten: satu pola daftar, satu gaya tombol, satu bahasa (Indonesia). |
| NFR-U2 | Kontras teks ≥ 4.5:1; status tidak hanya warna. |
| NFR-U3 | Dapat dioperasikan dengan keyboard; modal bisa ditutup `Esc`. |
| NFR-U4 | Responsif: layar 1366×768 (kantor) dan 375 px (mobile untuk petugas lapangan). |
| NFR-U5 | Pesan error spesifik & memberi langkah perbaikan. |

### 6.6 Kompatibilitas

| ID | Kebutuhan |
|---|---|
| NFR-C1 | Browser: Chrome/Edge/Firefox versi 2 tahun terakhir. |
| NFR-C2 | PHP 8.3, Laravel 13, MySQL 8, Blade + Bootstrap 5.3. |
| NFR-C3 | Cetak A4 portrait; landscape untuk tabel lebar. |

### 6.7 Maintainability & observability

| ID | Kebutuhan |
|---|---|
| NFR-M1 | Logika transisi terpusat di model/service (`Tiket`, `NotifikasiService`) — controller tidak menulis status langsung. |
| NFR-M2 | Setiap fitur utama memiliki feature test (alur normal, ditolak, add ulang, lepas, selesai, pengaduan). |
| NFR-M3 | Kode mengikuti Pint & konvensi proyek. |
| NFR-M4 | Error produksi tercatat (log) tanpa membocorkan data pribadi. |

### 6.8 Lokalisasi

| ID | Kebutuhan |
|---|---|
| NFR-L1 | Seluruh teks UI berbahasa Indonesia. |
| NFR-L2 | Tanggal tampil `dd/mm/yyyy`, simpan `Y-m-d`; waktu `HH:mm`. |
| NFR-L3 | Zona waktu `Asia/Jakarta`. |

---

## 7. Scope

### 7.1 In scope (target V2)

| Modul | Cakupan |
|---|---|
| Autentikasi & RBAC | Login, 12 jabatan + seksi, middleware, impersonate read-only |
| Pendaftaran | Wizard 4 tahap, No. Berkas manual, nomor JP otomatis/editable + riwayat, bukti & antrean |
| Ploting | Antrean, proses, selesai ploting |
| Back office | Staf Seksi 1/2 (ADD, Selesai, Tolak, ADD ULANG), Kasi monitoring |
| Warkah | Permintaan otomatis, siap, pinjam fisik, batas kembali, terlambat, pengembalian |
| Penyerahan | Antrean, serah terima, SELESAI |
| Pengaduan | Buat LP, kirim, terima, kembalikan, kirim ulang, tutup |
| Akhir alur | Daftar Selesai, Daftar Ditolak, Arsip otomatis |
| Admin | Akun, master, setting, Lepas Tiket, log, laporan |
| Super Admin | Demo, data demo, backup, log sistem |
| Kakan | Dashboard & laporan read-only |
| Notifikasi | In-app (lonceng, daftar, preferensi, retensi) |
| Komponen umum | Pencarian pintar, filter tanggal, chip, cetak |

### 7.2 Out of scope

| Tidak termasuk | Alasan |
|---|---|
| Notifikasi email/SMS/WhatsApp | Diganti in-app (Keputusan #9) |
| Persetujuan akhir Kakan | Dihapus (Keputusan #5) |
| Permintaan warkah dari akun Staf/Kasi | Dilebur ke Petugas Warkah |
| Integrasi langsung aplikasi nasional | Nomor resmi diinput manual |
| Tanda tangan digital / sertifikat elektronik | Belum ada kebutuhan |
| Mobile app native | Web responsif cukup |
| Multi-kantor / multi-tenant | Satu kantor |
| Akuntansi/keuangan | Di luar domain |
| Manajemen arsip fisik jangka panjang (retensi nasional) | Hanya penanda arsip aplikasi |
| Penggantian mesin antrean fisik | Di luar lingkup |

### 7.3 Tahapan rilis (usulan)

| Fase | Isi | Ketergantungan |
|---|---|---|
| **Fase 1 — Fondasi** | RBAC 12 jabatan + seksi, wizard 4 tahap + No. Berkas, nomor JP + riwayat, Daftar Permohonan, pencarian pintar + filter tanggal | Migrasi `users.seksi`, `tiket_nomor_riwayat` |
| **Fase 2 — Back office** | ADD/KIRIM, Selesai/Tolak, ADD ULANG, Lepas Tiket, Kasi monitoring, Daftar Ditolak | Fase 1 |
| **Fase 3 — Warkah & Penyerahan** | Warkah penuh (batas/terlambat), Loket Penyerahan, arsip otomatis, Daftar Selesai | Fase 2 |
| **Fase 4 — Pengaduan, notifikasi, laporan** | Modul LP, notifikasi in-app, laporan lengkap, demo | Fase 3 |

### 7.4 Asumsi

1. Kantor menggunakan satu basis data terpusat dan satu server aplikasi.
2. Petugas loket memiliki akses komputer/printer untuk cetak antrean & bukti.
3. Berkas fisik tetap menjadi acuan; aplikasi mencatat, bukan menggantikan.
4. Nomor resmi dari aplikasi pemerintah diinput manual oleh Loket Pendaftaran.
5. Kebijakan batas hari (macet, warkah, arsip, pengaduan) disepakati kantor dan dapat diubah di Setting.

### 7.5 Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Perubahan kebiasaan kerja | Petugas kembali ke cara lama | Demo, pelatihan, UI yang meniru alur nyata |
| Data lama tidak konsisten | Migrasi status gagal | Peta status lama→baru + verifikasi bertahap |
| Beban server saat laporan | Halaman lambat | Indeks, pagination, cache ringkasan, export async bila perlu |
| Ketergantungan satu petugas | Berkas berhenti | Penanda macet + Lepas Tiket |
| Penyamaran data bocor lewat export | Pelanggaran privasi | Samarkan di query/resource, uji export Kakan |

### 7.6 Metrik keberhasilan

| Metrik | Baseline (kondisi awal) | Target |
|---|---|---|
| Rata-rata durasi daftar→serah | Tidak terukur | Terukur & turun ≥ 20% dalam 3 bulan |
| Tiket macet aktif | Tidak diketahui | < 5% tiket berjalan |
| Tiket ditolak tanpa penanggung jawab | Ada | 0 |
| Pengaduan tanpa status akhir > 7 hari | Tidak terukur | < 10% |
| Pencarian berkas (No Tiket/No. Berkas) berhasil | — | ≥ 80% |
| Rekap bulanan manual | Ada | Tidak ada (otomatis) |

---

## Lampiran A — Glosarium

| Istilah | Definisi |
|---|---|
| **Tiket** | Satu permohonan dengan nomor `JP...` |
| **Database** | Antrean bersama tiket `di_database` |
| **ADD** | Mengambil tiket ke antrian pribadi |
| **ADD ULANG** | Mengambil kembali tiket yang saya tolak |
| **KIRIM** | Menutup pekerjaan tahap (Selesai/Tolak) |
| **SELESAI** | Penutup alur oleh Loket Penyerahan |
| **Lepas Tiket** | Admin mengembalikan tiket ke Database |
| **Macet** | Penanda melewati batas hari |
| **Warkah** | Berkas fisik pendukung (BT/SU) |
| **Ploting** | Penentuan bidang/letak tanah |
| **LP** | Nomor pengaduan |
| **P0..Pn** | Mekanisme revisi **lama** — tidak dipakai lagi |

---

## Lampiran B — Status & Transisi

### B.1 Tiket

| Status | Arti |
|---|---|
| `di_database` | Menunggu diambil |
| `diproses` | Dipegang satu akun |
| `ditolak` | Dikembalikan ke staf penolak |
| `selesai` | Diserahkan ke pemohon |
| Penanda `macet` | Melewati batas hari (bukan status) |
| Penanda `dilepas_admin` | Dilepas paksa oleh Admin |

| Dari | Ke | Aksi | Syarat |
|---|---|---|---|
| — | `di_database` | Tahap 4 selesai | Warkah dibuat bila perlu |
| `di_database` | `diproses` | ADD | Belum dipegang akun lain |
| `diproses` | `di_database` | KIRIM Selesai | Catatan ≥ 3 karakter |
| `diproses` | `ditolak` | KIRIM Tolak | Catatan ≥ 3 karakter |
| `ditolak` | `diproses` | ADD ULANG | `user = penanggung_jawab` |
| `diproses`/`ditolak` | `di_database` | Lepas Tiket | Posisi di Seksi 1/2 + alasan |
| `diproses` | `selesai` | SELESAI | Penerima dicatat |

### B.2 Warkah

`diminta` → `diterima` → `siap` → `dipinjam` → `dikembalikan`; penanda `terlambat` otomatis.

### B.3 Pengaduan

`terkirim` → `diterima` → `selesai`; atau `dikembalikan` → `terkirim` (kirim ulang).

---

## Lampiran C — Matriks Daftar per Akun

| Daftar | Super Admin | Admin | Loket Pend. | Loket Ploting | Petugas Warkah | Staf 1–2 | Kasi 1–2 | Kasi 3–5/TU | Loket Penyerahan | Loket Pengaduan | Kakan |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Permohonan (Database) | ✔ | ✔ | | | | ✔ | | | ✔ | | ✔ |
| Berkas Permohonan | | | ✔ | | | | | | | | |
| Ploting | | | ✔ | ✔ | | | | | | | |
| Tiket Saya | | | | | | ✔ | | | ✔ | | |
| Permohonan Seksi | | | | | | | ✔ | | | | |
| Warkah | | | ✔ (ringkas) | | ✔ | ✔ | ✔ | | | | kartu |
| Selesai | | | ✔ | | | ✔ | ✔ | | ✔ | | |
| Ditolak | | | ✔ | | | ✔ | ✔ | | | | |
| Pengaduan | ✔ | ✔ | | | | | ✔ | ✔ | | ✔ | ✔ |
| Arsip | | ✔ | ✔ | ✔ | ✔ | | | | | | |

Menu lengkap: `alur/00-Blueprint-Utama.md` §6.

---

## Lampiran D — Traceability User Story → Functional Requirement

| User Story | FR |
|---|---|
| US-01 | FR-A1, FR-A2 |
| US-02 | FR-A5, FR-M1 |
| US-03 | FR-A7, FR-M1 |
| US-04 | FR-A8 |
| US-05 | FR-R1, FR-R4, FR-R5 |
| US-06 | FR-R2 |
| US-07 | FR-R3 |
| US-08 | FR-R4, FR-R8 |
| US-09 | FR-P1, FR-P2, FR-P3 |
| US-10 | FR-R6, FR-W1 |
| US-11 | FR-B1, FR-B2, FR-B3 |
| US-12 | FR-B2, FR-B6 |
| US-13 | FR-B4, FR-B8 |
| US-14 | FR-B5, FR-B8 |
| US-15 | FR-B6, FR-B7 |
| US-16 | FR-B9 |
| US-17 | FR-K1, FR-K2 |
| US-18 | FR-K4 |
| US-19 | FR-W2 |
| US-20 | FR-W3, FR-W8 |
| US-21 | FR-W4 |
| US-22 | FR-W6 |
| US-23 | FR-B10, FR-W7 |
| US-24 | FR-S1 |
| US-25 | FR-S2, FR-S3, FR-S4 |
| US-26 | FR-S2 |
| US-27 | FR-M4, FR-M5 |
| US-28 | FR-E3, FR-E4 |
| US-29 | FR-G1, FR-G2 |
| US-30 | FR-G3, FR-G4, FR-G7 |
| US-31 | FR-G4, FR-G5 |
| US-32 | FR-G5 |
| US-33 | FR-T1 |
| US-34 | FR-T2, FR-T3, FR-T4 |
| US-35 | FR-F1, FR-F2, FR-F3 |
| US-36 | FR-U2, FR-U3 |
| US-37 | FR-N1, FR-N2, FR-N3 |
| US-38 | FR-U7, FR-N4 |

---

## Lampiran E — Rancangan Tabel Main Content

Ringkasan; detail kolom ada di `BLUEPRINT_REFISI_V2.md` bagian E dan `alur/15`.

| Kode | Daftar | Pemilik | Tanggal acuan |
|---|---|---|---|
| E.1 | Permohonan (Database) | Super Admin, Admin, Kakan | Tgl Daftar |
| E.2 | Berkas Permohonan | Loket Pendaftaran | Tgl Daftar |
| E.3a/b/c | Ploting (menunggu/selesai) | Loket Pendaftaran, Loket Ploting | Waktu Kirim |
| E.4 | Cari + ADD | Staf 1–2, Loket Penyerahan | Tgl Daftar |
| E.5 | Tiket Saya | Staf 1–2, Loket Penyerahan | Waktu ADD |
| E.6 | Permohonan Seksi | Kasi 1–2 | Waktu ADD |
| E.7a–e | Warkah | Petugas Warkah, Staf, Kasi, Loket Pend. | Permintaan/Pinjam/Kembali |
| E.8 | Selesai | Loket Pend., Staf, Kasi, Loket Penyerahan | Tgl Diserahkan |
| E.9 | Ditolak | Loket Pend., Staf, Kasi | Tgl Ditolak |
| E.10 | Pengaduan | Loket Pengaduan, Kasi 1–5/TU, Admin, Kakan | Tgl Buat |
| E.11 | Arsip | Loket Pend., Loket Ploting, Petugas Warkah, Admin | Tgl Selesai |
| E.12 | Dashboard Warkah (kartu) | Kakan | — |

Aturan umum (E.0): kolom pertama `No`, terakhir `Aksi`, bar filter di atas tabel, kartu ringkasan di atas filter, pagination 20, klik baris = Detail.

---

## Lampiran F — Matriks Menu

Lihat `alur/00-Blueprint-Utama.md` §6. Ringkas:

| Akun | Menu inti |
|---|---|
| Super Admin | Dashboard Sistem · Permohonan · Pengaduan · Demo · Impersonate · Akun · Master · Data Demo · Log · Laporan · Backup |
| Admin | Dashboard · Akun · Loket Pendaftaran · Loket Pengaduan · Master · Setting · Permohonan (+ Lepas) · Log · Laporan |
| Loket Pendaftaran | Dashboard · Pendaftaran Baru · Berkas · Ploting · Warkah · Selesai · Ditolak · Arsip · Laporan |
| Loket Ploting | Dashboard · Berkas Ploting · Selesai Ploting · Arsip · Laporan |
| Petugas Warkah | Dashboard · Permintaan · Pinjaman · Dikembalikan · Arsip · Laporan |
| Staf 1–2 | Dashboard · Permohonan (cari+ADD) · Tiket Saya · Warkah · Selesai · Ditolak · Laporan |
| Kasi 1–2 | Dashboard · Permohonan Seksi · Warkah · Selesai · Ditolak · Pengaduan · Pengaduan Selesai · Laporan |
| Kasi 3–5/TU | Dashboard · Pengaduan · Pengaduan Selesai · Laporan |
| Loket Penyerahan | Dashboard · Antrian Penyerahan · Tiket Saya · Selesai · Laporan |
| Loket Pengaduan | Dashboard · Buat Pengaduan · Daftar Pengaduan · Pengaduan Selesai · Laporan |
| Kakan | Dashboard · Permohonan · Warkah (opsional) · Pengaduan · Laporan |

---

## Lampiran G — Perubahan Rancangan Data

| Tabel | Perubahan |
|---|---|
| `tikets` | Tambah `no_berkas` (teks bebas, non-unik), `penanggung_jawab_id` (staf penolak), `tanggal_selesai` (bila belum ada); status baru `ditolak`; penanda `macet`/`dilepas_admin` |
| `pendaftaran_drafts` | Tambah `no_berkas` (disalin ke `tikets` saat Tahap 4) |
| `tiket_nomor_riwayat` (baru) | `tiket_id`, `no_lama`, `no_baru`, `alasan`, `diubah_oleh`, `waktu` |
| `tiket_alur` | `hasil` untuk Loket Penyerahan hanya `selesai`; tambah penanda `add_ulang` (bool) |
| `warkah_pinjam` | Hapus alur permintaan dari Staf/Kasi; `diminta_oleh` = Petugas Loket Pendaftaran; peminjam diisi Petugas Warkah; tambah `batas_kembali` |
| `warkah_perpanjangan` | Tetap; `oleh_user_id` selalu Petugas Warkah; simpan alasan |
| `pengaduans` (baru/belum) | Nomor LP, pengadu, jenis, uraian, seksi tujuan, status, riwayat |
| `notifikasi` (baru) | `user_id`, `tipe`, `judul`, `pesan`, `url`, `objek_tipe`, `objek_id`, `dibaca_pada` |
| `users` | Tambah `seksi` (1–5/TU); role baru 12 jabatan |
| `settings` | Hapus opsi `persetujuan_akhir_kakan`; tambah batas macet/warkah/arsip/tanggap |
| Semua tabel daftar | Indeks pada kolom tanggal acuan & kolom pencarian |

**Aturan teknis**
- ADD pada tiket `ditolak` hanya lolos bila `user_id = penanggung_jawab_id` (validasi server).
- Lepas Tiket hanya lolos bila posisi tiket di user dengan `seksi IN (1,2)`.
- Kasi 1/2 melihat tiket bila ada `tiket_alur.seksi` = seksi Kasi (staf sudah ADD).
- `email_pemohon` **dipertahankan** di DB tanpa UI (warisan; keputusan pembersihan email).

---

## Lampiran H — Pertanyaan Terbuka

| # | Pertanyaan | Default dokumen ini | Dampak bila berubah |
|---|---|---|---|
| H.1 | Apakah Loket Pendaftaran tetap perlu **Daftar Warkah read-only**? | Ya, versi ringkas | Hapus menu & query terkait |
| H.2 | Kakan: **kartu saja** atau **daftar warkah detail**? | Kartu (E.12) + menu opsional read-only | Menambah/menghapus menu & kolom |
| H.3 | Lepas Tiket untuk tiket **Ditolak** bila penanggung jawab cuti/pindah? | Boleh, dengan alasan + catatan | Bila tidak, perlu mekanisme pengalihan lain |
| H.4 | ADD di Loket Penyerahan: **hanya peringatan** atau **diblokir** bila tiket belum pernah dikerjakan Seksi 1/2? | Peringatan (tidak memblokir) | Bila diblokir, perlu aturan “pernah dikerjakan” |
| H.5 | Angka bawaan: macet **3 hari kerja**, pinjam warkah **7 hari**, arsip **30 hari**, tanggap pengaduan **2 hari kerja**? | Sesuai nilai tersebut di Setting | Semua chip penanda & laporan ikut berubah |

---

## Lampiran I — Referensi Dokumen

| Dokumen | Isi |
|---|---|
| `alur/BLUEPRINT_REFISI_V2.md` | Blueprint mentah A–H |
| `alur/00-Blueprint-Utama.md` | Ringkasan target, matriks, status implementasi |
| `alur/01`–`12` | Alur per peran |
| `alur/13` | Selesai, Ditolak, Arsip |
| `alur/15` | Komponen umum |
| `alur/16` | Notifikasi in-app |
