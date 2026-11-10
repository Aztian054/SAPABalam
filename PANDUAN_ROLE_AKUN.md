# Panduan Peran & Fungsi Akun SAPA Balam (V2)

> **Dokumen Resmi Berdasarkan Kode Implementasi Aktual Website SAPA Balam**  
> Sistem: Laravel 13 · PHP 8.3 · MySQL · Blade + Bootstrap 5.3  
> Satuan Kerja: Kantor Pertanahan Kota Bandar Lampung (BALAM)

---

## Daftar Role Akun (11 Role Operasional & Sistem)

| # | Kode Role | Nama Tampilan | Middleware Penjaga | Fungsi Utama |
|---|-----------|---------------|-------------------|--------------|
| 1 | `super_admin` | Super Admin | `super_admin` | Pemilik sistem, impersonate, backup, log, kelola demo |
| 2 | `admin` | Admin | `role:admin` | Operasional lintas unit, lepas tiket, manajemen user & master data |
| 3 | `loket_pendaftaran` | Loket Pendaftaran | `role:loket_pendaftaran` | Wizard pendaftaran berkas 4 tahap, cetak antrian, arsip |
| 4 | `loket_ploting` | Loket Ploting | `role:loket_ploting` | Pemetaan bidang / ploting berkas pendaftaran |
| 5 | `petugas_warkah` | Petugas Warkah | `role:petugas_warkah` | Sirkulasi warkah fisik (permintaan, pinjam, kembali, ubah batas) |
| 6 | `staf_seksi` | Staf Seksi | `role:staf_seksi` | Back office: Cari & ADD berkas, proses tiket, selesai / tolak |
| 7 | `kasi_seksi` | Kasi Seksi | `role:kasi_seksi` | Monitoring permohonan seksi & penanganan pengaduan seksi |
| 8 | `kasubbag_tu` | Kasubbag TU | `role:kasubbag_tu` | Penanganan pengaduan Tata Usaha (TU) |
| 9 | `loket_penyerahan` | Loket Penyerahan | `role:loket_penyerahan` | Penutup alur: ADD antrian penyerahan & aksi SELESAI berkas |
| 10 | `loket_pengaduan` | Loket Pengaduan | `role:loket_pengaduan` | Penerimaan, registrasi tiket LP, dan kirim ulang pengaduan |
| 11 | `kakan` | Kakan | `role:kakan` | Monitoring & Evaluasi eksekutif seluruh unit (read-only) |

---

## 1. Super Admin (`super_admin`)

### Dasar Kode
- **Middleware**: `SuperAdminMiddleware` (`app/Http/Middleware/SuperAdminMiddleware.php`)
- **Model**: `User::isSuperAdmin()` (`app/Models/User.php:102`)
- **Controller**: `ImpersonateController`, `BackupController`, `DemoController`
- **Route Prefix**: `/super-admin/*` (`routes/web.php:223`)

### Fungsi & Wewenang
- **Pemilik Sistem**: Memiliki akses tertinggi untuk pemeliharaan teknis sistem aplikasi.
- **Lihat/Coba Sebagai Akun (Impersonate)**: Masuk dan melihat antarmuka sebagai akun pengguna lain dengan mode **baca-saja (read-only)** tanpa mengetahui kata sandi mereka.
- **Backup Basis Data**: Membuat dan mengunduh berkas cadangan SQL sistem secara instan dan mandiri tanpa bergantung pada shell server.
- **Kelola Data & Skenario Demo**: Menjalankan pembuatan data demo terisolasi (`is_demo = true`) dan pembersihan 1-klik (hanya aktif pada mode demo / `APP_ENV=local`).
- **Tanpa Jejak Log**: Aktivitas login, impersonasi, dan backup oleh Super Admin sengaja tidak direkam di log publik agar tidak meninggalkan residu audit.

### Menu Sidebar
- Dashboard Sistem (`dashboard`)
- Daftar Permohonan (`admin.index`)
- Daftar Pengaduan (`admin.index`)
- Skenario Demo (`super-admin.demo.index`)
- Lihat/Coba Sebagai Akun (`super-admin.impersonate.index`)
- Manajemen Akun (`admin.users`)
- Master Data & Setting (`admin.form-pendaftaran`, `admin.setting`)
- Data Demo (`super-admin.demo.index`)
- Log Aktivitas (`admin.log`)
- Laporan (`reports.index`)
- Backup Basis Data (`super-admin.backup.index`)

### Cara Menggunakan
1. Login dengan username `superadmin` dan password `superadmin123`.
2. Untuk uji coba tampilan peran lain: Klik menu **Lihat/Coba Sebagai Akun**, pilih pengguna tujuan, lalu klik **Coba Sebagai Akun Ini**.
3. Sistem beralih ke sesi pengguna tujuan dengan banner informasi kuning di atas layar. apaSeluruh tombol aksi POST/PUT/DELETE akan diblokir demi keamanan data.
4. Klik **Kembali ke Super Admin** di topbar untuk keluar dari mode impersonasi.

---

## 2. Admin (`admin`)

### Dasar Kode
- **Middleware**: `role:admin` (`app/Http/Middleware/RoleMiddleware.php`)
- **Model**: `User::isAdmin()` (`app/Models/User.php:97`)
- **Controller**: `AdminController`, `AccountController`, `SettingController`, `FormPendaftaranController`
- **Route Prefix**: `/admin/*` (`routes/web.php:58`)

### Fungsi & Wewenang
- **Database Berkas Terpadu**: Memantau seluruh tiket dari seluruh tahapan dan loket secara terpusat.
- **Lepas Tiket**: Mengintervensi tiket yang terkunci di tangan staf Seksi 1 atau Seksi 2 (misal staf berhalangan/cuti) agar tiket kembali ke antrean umum (`di_database`).
- **Manajemen Akun Pengguna**: Menambah, mengedit data identitas (NIP, No HP, Seksi, Jabatan), mengaktifkan/menonaktifkan akun staf.
- **Pengaturan Master Data & Form**: Mengatur jenis hak, persyaratan berkas permohonan, saran koreksi kekurangan dokumen, dan template pendaftaran.
- **Setting Sistem**: Mengatur parameter retensi berkas arsip, batas waktu macet tiket, dan batas SLA pengaduan.

### Menu Sidebar
- Dashboard (`dashboard`)
- Daftar Berkas (Database) (`admin.index`)
- Berkas Selesai (`admin.selesai`)
- Arsip Folder (`admin.arsip`)
- Manajemen Akun (`admin.users`)
- Kelola Form Pendaftaran (`admin.form-pendaftaran`)
- Setting (`admin.setting`)
- Log Aktivitas (`admin.log`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `admin` dan password `admin123`.
2. Untuk melepas tiket macet: Buka **Daftar Berkas**, cari nomor tiket, buka detail, lalu klik tombol **Lepas Tiket** dan masukkan alasan.
3. Untuk kelola akun: Buka **Manajemen Akun**, buat akun baru staf dengan memilih role operasional yang sesuai (role Super Admin & Admin dikunci).


---

## 3. Loket Pendaftaran (`loket_pendaftaran`)

### Dasar Kode
- **Middleware**: `role:loket_pendaftaran`
- **Model**: `User::isLoketPendaftaran()` (`app/Models/User.php:113`)
- **Controller**: `PendaftaranController`, `PlotingController`
- **Route Prefix**: `/pendaftaran/*` (`routes/web.php:125`)

### Fungsi & Wewenang
- **Wizard Pendaftaran 4 Tahap**:
  - **Tahap 1 (Identitas & Berkas)**: Input data pemohon, kuasa/badan hukum, No. Berkas fisik, otomatis generate Kode Tiket (prefix `JP`).
  - **Tahap 2 (Ploting Bidang)**: Menentukan apakah bidang butuh ploting. Jika perlu ploting, tiket masuk ke antrean Loket Ploting dan dapat dicetak nomor antrian ploting.
  - **Tahap 3 (Data Yuridis)**: Input jenis permohonan, kategori hak, nomor hak, dan lokasi letak tanah.
  - **Tahap 4 (Kebutuhan Warkah & Finalisasi)**: Memilih opsi butuh pinjam warkah (Buku Tanah / Surat Ukur / Keduanya). Jika butuh, otomatis membuat tiket permintaan warkah ke Petugas Warkah. Tiket resmi terbit berstatus `di_database`.
- **Cetak Antrian & Formulir**: Mencetak tanda terima bukti pendaftaran pemohon.
- **Monitoring Ploting**: Memantau tab Draf Menunggu Ploting & Draf Selesai Ploting untuk melanjutkan proses wizard.

### Menu Sidebar
- Pendaftaran Baru (`pendaftaran.create`)
- Daftar Berkas Permohonan (`pendaftaran.index`)
- Ploting (`ploting.index`)
- Daftar Arsip (`arsip.index`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `loketdaftar1` dan password `loketdaftar123`.
2. Klik **Pendaftaran Baru**, lengkapi identitas pemohon di Tahap 1.
3. Di Tahap 2, bila sertipikat belum terpetakan, tandai "Belum Ploting" dan kirim ke antrean ploting.
4. Setelah ploting diselesaikan petugas ploting, buka menu **Ploting** → tab **Sudah Ploting**, klik tombol **Lanjut Pendaftaran**.
5. Di Tahap 4, klik **Selesaikan Pendaftaran** untuk menerbitkan tiket ke database kantor.

---

## 4. Loket Ploting (`loket_ploting`)

### Dasar Kode
- **Middleware**: `role:loket_ploting`
- **Model**: `User::isLoketPloting()` (`app/Models/User.php:118`)
- **Controller**: `PlotingController`
- **Route Prefix**: `/ploting/*` (`routes/web.php:146`)

### Fungsi & Wewenang
- **Antrean Pemetaan Spasial**: Menerima kiriman draf pendaftaran dari Loket Pendaftaran yang membutuhkan pemetaan/ploting bidang tanah.
- **Penyelesaian Ploting**: Meninjau posisi koordinat/peta, memverifikasi spasial, membubuhkan catatan hasil ploting, dan mengubah status draf dari "belum" menjadi "sudah ploting".
- **Notifikasi Balik**: Sistem secara otomatis mengirim notifikasi in-app kepada petugas pendaftaran bahwa ploting telah selesai.

### Menu Sidebar
- Daftar Berkas Ploting (`ploting.index`)
- Daftar Arsip (`arsip.index`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `loketploting1` dan password `ploting123`.
2. Buka menu **Daftar Berkas Ploting** (tab default: **Menunggu Ploting**).
3. Cari berkas berdasarkan kode tiket atau nomor berkas.
4. Klik tombol aksi **Proses Ploting**, masukkan catatan teknis hasil pemetaan, lalu klik **Simpan & Selesaikan Ploting**.
5. Berkas berpindah ke tab **Selesai Ploting** dan siap dilanjutkan oleh Loket Pendaftaran.

---

## 5. Petugas Warkah (`petugas_warkah`)

### Dasar Kode
- **Middleware**: `role:petugas_warkah`
- **Model**: `User::isPetugasWarkah()` (`app/Models/User.php:123`)
- **Controller**: `WarkahPermintaanController`
- **Route Prefix**: `/warkah-permintaan/*` (`routes/web.php:209`)

### Fungsi & Wewenang
- **Pengelolaan Berkas Fisik Warkah**: Mengatur sirkulasi peminjaman warkah (Buku Tanah & Surat Ukur) di ruang arsip.
- **Siklus Status Pinjaman**:
  - `diminta`: Permintaan otomatis dari Loket Pendaftaran atau staf.
  - `diterima`: Petugas warkah memvalidasi permintaan dan mencari warkah di rak penyimpanan.
  - `siap`: Dokumen warkah sudah ditemukan di rak dan siap diambil staf seksi.
  - `dipinjam`: Berkas fisik diserahkan ke staf peminjam (mencatat nama peminjam, seksi, batas waktu kembali default 7 hari).
  - `dikembalikan`: Berkas fisik telah kembali ke ruang warkah dalam kondisi lengkap.
- **Ubah Batas Waktu Pinjaman**: Memperpanjang tenggat waktu pinjaman atas kesepakatan langsung/komunikasi lapangan.
- **Monitoring Keterlambatan**: Berkas yang melewati batas waktu otomatis mendapatkan penanda keterlambatan.

### Menu Sidebar
- Permintaan Warkah (`warkah-permintaan.index`)
- Berkas Pinjaman (`warkah-permintaan.pinjaman`)
- Berkas Dikembalikan (`warkah-permintaan.dikembalikan`)
- Arsip (`arsip.index`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `warkah1` dan password `warkah123`.
2. Buka menu **Permintaan Warkah**, klik **Terima Permintaan** untuk berkas yang masuk.
3. Setelah mencari dokumen fisik di rak arsip, klik **Tandai Siap Diambil**.
4. Saat staf datang mengambil berkas, klik **Serahkan / Catat Pinjaman**, isi data peminjam dan batas waktu.
5. Saat berkas dikembalikan staf, buka menu **Berkas Pinjaman** dan klik **Konfirmasi Pengembalian**.

---

## 6. Staf Seksi (`staf_seksi`)

### Dasar Kode
- **Middleware**: `role:staf_seksi`
- **Model**: `User::isStafSeksi()`, `User::requiresSeksi()` (`app/Models/User.php:128, 170`)
- **Controller**: `SeksiController`
- **Route Prefix**: `/seksi/*` (`routes/web.php:153`)

### Fungsi & Wewenang
- **Keterikatan Seksi Kerja**: Setiap akun staf terikat pada unit seksi tertentu (Seksi 1, Seksi 2, Seksi 3, Seksi 4, Seksi 5, atau TU). Khusus back office pendaftaran dilayani oleh Seksi 1 dan Seksi 2.
- **Pola Kerja Cari → ADD**: Staf mencari berkas pada antrean pusat (`di_database`), lalu menekan tombol **ADD** untuk mengambil tanggung jawab pemrosesan berkas tersebut.
- **Tiket Saya**: Area kerja pribadi tempat staf memproses tiket yang dipegangnya (analisis berkas, verifikasi berkas, validasi teknis).
- **Keputusan Pemrosesan**:
  - **SELESAI**: Berkas selesai diproses pada seksi bersangkutan, tiket dikirim kembali ke database kantor untuk tahapan seksi berikutnya atau ke penyerahan.
  - **TOLAK**: Bila terdapat cacat berkas/kekurangan yuridis, staf menolak tiket dengan catatan revisi. Tiket berstatus `ditolak` dan **terkunci pada staf yang menolak**.
- **ADD ULANG**: Tiket yang ditolak hanya dapat diambil dan diperbaiki kembali oleh staf penolak yang sama melalui tombol ADD ULANG setelah pemohon melengkapi berkas.

### Menu Sidebar
- Cari & ADD Berkas (`seksi.index`)
- Tiket Saya (`seksi.tiket-saya`)
- Daftar Selesai (`seksi.selesai`)
- Daftar Ditolak (`seksi.ditolak`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username staf (contoh: `staf1a`, password `staf123` untuk Seksi 1).
2. Buka menu **Cari & ADD Berkas**, cari tiket yang tersedia, lalu klik tombol **ADD Tiket**.
3. Buka menu **Tiket Saya**, klik tiket yang sedang diproses.
4. Teliti berkas:
   - Jika berkas memenuhi syarat: Klik tombol **Selesai & Kirim**, berikan catatan perbaikan/hasil verifikasi.
   - Jika berkas tidak lengkap: Klik tombol **Tolak Berkas**, pilih template kekurangan dokumen dan isi alasan penolakan.
5. Bila pemohon telah memperbaiki dokumen berkas ditolak: Buka **Daftar Ditolak**, lalu klik **ADD ULANG** untuk memprosesnya kembali.

---

## 7. Kasi Seksi (`kasi_seksi`)

### Dasar Kode
- **Middleware**: `role:kasi_seksi`
- **Model**: `User::isKasiSeksi()`, `User::requiresSeksi()` (`app/Models/User.php:133, 170`)
- **Controller**: `SeksiController`, `PengaduanUnitController`
- **Route Prefix**: `/seksi/*`, `/pengaduan-unit/*` (`routes/web.php:153, 185`)

### Fungsi & Wewenang
- **Monitoring Permohonan Seksi**: Mengawasi seluruh berkas tiket yang sedang dipegang oleh staf-staf di bawah seksinya. Kasi baru melihat tiket setelah staf seksinya melakukan **ADD**.
- **Daftar Selesai & Ditolak Seksi**: Melihat riwayat kinerja penyelesaian dan penolakan berkas oleh seluruh staf di seksinya.
- **Penanganan Pengaduan Seksi**: Menerima disposisi pengaduan masyarakat (tiket `LP`) yang ditujukan ke seksinya dari Loket Pengaduan, memproses jawaban klarifikasi, atau mengembalikan berkas pengaduan jika bukan kewenangan seksinya.

### Menu Sidebar
- Daftar Permohonan Seksi (`seksi.index`)
- Daftar Selesai (`seksi.selesai`)
- Daftar Ditolak (`seksi.ditolak`)
- Daftar Pengaduan (`pengaduan-unit.index`)
- Pengaduan Selesai (`pengaduan-unit.selesai`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username kasi (contoh: `kasi1`, password `kasi123`).
2. Buka menu **Daftar Permohonan Seksi** untuk memantau beban kerja staf, durasi pengerjaan, dan status kendala berkas.
3. Untuk keluhan warga: Buka menu **Daftar Pengaduan**, telaah uraian pengaduan yang masuk.
4. Klik **Terima Pengaduan** → proses penyelesaian → klik **Tutup & Selesaikan Pengaduan** beserta keterangan solusi perbaikan.


---

## 8. Kasi Unit (`kasi_unit`)

### Dasar Kode
- **Middleware**: `role:kasi_unit`
- **Model**: `User::isKasiUnit()` (`app/Models/User.php:138`)
- **Controller**: `PengaduanUnitController`
- **Route Prefix**: `/pengaduan-unit/*` (`routes/web.php:185`)

### Fungsi & Wewenang
- **Fokus Penanganan Pengaduan Unit**: Dikhususkan bagi Kepala Seksi di luar back office sertipikat (Seksi 3, 4, 5) untuk menangani keluhan pelayanan yang masuk ke unitnya.
- **Tindakan Penyelesaian**: Menerima pengaduan masyarakat (`LP`), memberikan tindak lanjut/solusi, menutup kasus (`status: selesai`), atau mengembalikan berkas pengaduan ke Loket Pengaduan bila salah kamar/salah unit tujuan.

### Menu Sidebar
- Daftar Pengaduan (`pengaduan-unit.index`)
- Pengaduan Selesai (`pengaduan-unit.selesai`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan akun Kasi Unit kerja terkait.
2. Buka menu **Daftar Pengaduan**, lihat aduan masyarakat yang ditujukan ke unitnya.
3. Klik **Terima Pengaduan** untuk memulai penanganan.
4. Berikan tanggapan solusi resmi, lalu klik **Tutup Pengaduan**.

---

## 9. Kasubbag TU (`kasubbag_tu`)

### Dasar Kode
- **Middleware**: `role:kasubbag_tu`
- **Model**: `User::isKasubbagTu()` (`app/Models/User.php:143`)
- **Controller**: `PengaduanUnitController`
- **Route Prefix**: `/pengaduan-unit/*` (`routes/web.php:185`)

### Fungsi & Wewenang
- **Penanganan Pengaduan Bagian Tata Usaha**: Khusus mengelola keluhan, aduan pelayanan administrasi, persuratan, dan kepegawaian internal kantor yang ditujukan ke Subbagian Tata Usaha.
- **Tindakan**: Menerima aduan (`LP`), menelaah, memberikan jawaban resmi, dan menutup pengaduan.

### Menu Sidebar
- Daftar Pengaduan (`pengaduan-unit.index`)
- Pengaduan Selesai (`pengaduan-unit.selesai`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan akun Kasubbag TU.
2. Buka menu **Daftar Pengaduan**, telaah tiket aduan tata usaha.
3. Klik **Terima Pengaduan**, tindak lanjuti secara administratif, lalu klik **Tutup Pengaduan**.

---

## 10. Loket Penyerahan (`loket_penyerahan`)

### Dasar Kode
- **Middleware**: `role:loket_penyerahan`
- **Model**: `User::isLoketPenyerahan()` (`app/Models/User.php:148`)
- **Controller**: `PenyerahanController`
- **Route Prefix**: `/penyerahan/*` (`routes/web.php:165`)

### Fungsi & Wewenang
- **Penutup Alur Pelayanan**: Berada di akhir rantai operasional pendaftaran berkas tanah.
- **Pola Antrian Penyerahan → ADD**: Petugas mencari atau memindai nomor tiket pemohon yang datang mengambil produk layanan di loket, lalu melakukan **ADD**.
- **Aksi Tunggal (Hanya SELESAI, Tanpa Tolak)**: Loket Penyerahan tidak memiliki kewenangan menolak berkas teknis. Satu-satunya tombol aksi akhir adalah **SELESAI**.
- **Form Serah Terima Produk**: Mencatat data penerima riil (nama penerima, hubungan dengan pemohon: Sendiri/Kuasa/Keluarga, NIK penerima, no handphone, dan catatan penyerahan).
- **Finalisasi Tiket**: Setelah diserahkan, tiket berstatus `selesai` dan siap diarsipkan secara otomatis oleh sistem setelah rentang waktu tertentu.

### Menu Sidebar
- Antrian Penyerahan (`penyerahan.index`)
- Tiket Saya (`penyerahan.tiket-saya`)
- Daftar Selesai (`penyerahan.selesai`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `serah1` dan password `serah123`.
2. Saat pemohon datang di loket penyerahan, buka menu **Antrian Penyerahan**, cari nomor tiket (`JP`), lalu klik **ADD**.
3. Tiket masuk ke tab **Tiket Saya**, klik buka tiket.
4. Klik tombol **SELESAI & SERAHKAN PRODUK**.
5. Lengkapi formulir bukti serah terima (nama penerima fisik, hubungan, NIK penerima), lalu klik **Simpan & Selesaikan**.


---

## 11. Loket Pengaduan (`loket_pengaduan`)

### Dasar Kode
- **Middleware**: `role:loket_pengaduan`
- **Model**: `User::isLoketPengaduan()` (`app/Models/User.php:153`)
- **Controller**: `PengaduanController`
- **Route Prefix**: `/pengaduan/*` (`routes/web.php:175`)

### Fungsi & Wewenang
- **Penerimaan Aduan Masyarakat**: Menerima laporan/keluhan masyarakat baik yang datang langsung ke loket maupun secara daring.
- **Registrasi Nomor LP Otomatis**: Membuat tiket aduan dengan format kode otomatis `LP` + tanggal (ddmmyyyy) + nomor urut 3 digit (contoh: `LP28102026001`).
- **Disposisi ke Unit Terkait**: Memilih unit/seksi tujuan pengaduan (Seksi 1 s.d. 5 atau Bagian TU).
- **Pengiriman Ulang (Re-Submit)**: Bila pengaduan dikembalikan oleh unit tujuan karena berkas kurang lengkap atau salah disposisi, Loket Pengaduan dapat memperbaiki data dan mengirim ulang.

### Menu Sidebar
- Buat Pengaduan (`pengaduan.create`)
- Daftar Pengaduan (`pengaduan.index`)
- Pengaduan Selesai (`pengaduan.selesai`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `pengaduan1` dan password `pengaduan123`.
2. Klik menu **Buat Pengaduan**, isi identitas pengadu (atau tandai jika bersifat anonim).
3. Hubungkan dengan nomor tiket pendaftaran bila aduan menyangkut berkas tertentu.
4. Pilih **Seksi Tujuan** dan uraikan keluhan secara jelas, lalu klik **Kirim Pengaduan**.
5. Pantau status penanganan aduan di menu **Daftar Pengaduan**.

---

## 12. Kakan (`kakan`)

### Dasar Kode
- **Middleware**: `role:kakan`
- **Model**: `User::isKakan()` (`app/Models/User.php:108`)
- **Controller**: `KakanController`
- **Route Prefix**: `/kakan/*` (`routes/web.php:238`)

### Fungsi & Wewenang
- **Executive Monitoring & Evaluasi (Read-Only)**: Kepala Kantor memantau performa seluruh alur kerja pelayanan pertanahan tanpa kewenangan mengubah status secara langsung (tidak ada intervensi teknis/persetujuan manual di aplikasi).
- **Statistik Beban Kerja Seksi**: Mengetahui jumlah berkas yang sedang berproses per seksi (Seksi 1, Seksi 2, Seksi 3, Seksi 4, Seksi 5, TU).
- **Ringkasan Indikator Kinerja**: Memantau volume tiket harian, tiket berstatus `di_database`, `diproses`, `ditolak`, `selesai`, dan `batal`.
- **Audit Trail & Timeline Berkas**: Menelaah jejak riwayat status setiap berkas secara menyeluruh dari awal masuk hingga diserahkan.

### Menu Sidebar
- Monitoring & Evaluasi (`kakan.index`)
- Daftar Permohonan (`kakan.index`)
- Daftar Warkah (`kakan.index`)
- Daftar Pengaduan (`kakan.index`)
- Laporan (`reports.index`)

### Cara Menggunakan
1. Login dengan username `kakan` dan password `kakan123`.
2. Halaman dashboard utama langsung menyajikan ringkasan statistik dan distribusi beban kerja per unit/seksi.
3. Gunakan filter pencarian pintar untuk mengecek riwayat detail tiket permohonan atau pengaduan tertentu.


---

## Matriks Hak Akses Fitur Antar-Peran

| Fitur / Modul | super_admin | admin | loket_pendaftaran | loket_ploting | petugas_warkah | staf_seksi | kasi_seksi | kasi_unit | kasubbag_tu | loket_penyerahan | loket_pengaduan | kakan |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **Dashboard Sistem / Kantor** | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ *(read-only)* |
| **Pendaftaran Baru (Wizard 4 Tahap)** | - | - | ✓ | - | - | - | - | - | - | - | - | - |
| **Proses Pemetaan / Ploting** | - | - | *(pantau)* | ✓ | - | - | - | - | - | - | - | - |
| **Cari & ADD Berkas Seksi (Back Office)** | - | - | - | - | - | ✓ | - | - | - | - | - | - |
| **Monitoring Berkas Seksi** | - | - | - | - | - | *(milik sendiri)* | ✓ *(seksinya)* | - | - | - | - | ✓ *(semua)* |
| **Tolak Berkas & ADD Ulang** | - | - | - | - | - | ✓ | - | - | - | - | - | - |
| **Lepas Tiket Seksi (Intervensi)** | - | ✓ *(Seksi 1-2)* | - | - | - | - | - | - | - | - | - | - |
| **Sirkulasi Pinjam Warkah Fisik** | - | - | *(minta otomatis)* | - | ✓ | *(peminjam)* | - | - | - | - | - | *(pantau)* |
| **Penyerahan Produk Akhir (SELESAI)** | - | - | - | - | - | - | - | - | - | ✓ | - | - |
| **Buat Tiket Pengaduan (LP)** | - | - | - | - | - | - | - | - | - | - | ✓ | - |
| **Tindak Lanjut & Tutup Pengaduan** | - | - | - | - | - | - | ✓ | ✓ | ✓ | - | - | - |
| **Manajemen Akun Pengguna** | ✓ | ✓ | - | - | - | - | - | - | - | - | - | - |
| **Master Data & Setting Sistem** | ✓ | ✓ | - | - | - | - | - | - | - | - | - | - |
| **Audit Trail & Log Aktivitas** | ✓ | ✓ | - | - | - | - | - | - | - | - | - | - |
| **Backup Basis Data** | ✓ | - | - | - | - | - | - | - | - | - | - | - |
| **Lihat/Coba Sebagai Akun (Impersonate)**| ✓ | - | - | - | - | - | - | - | - | - | - | - |
| **Kelola Data & Skenario Demo** | ✓ | - | - | - | - | - | - | - | - | - | - | - |

*(Keterangan: `✓` = Memiliki Hak Akses & Eksekusi; `-` = Tidak Memiliki Akses; Tanda kurung menunjukkan lingkup terbatas)*

---

## Arsitektur Keamanan & RBAC (Role-Based Access Control)

1. **Proteksi Lapisan Rute (Route Middleware)**:
   - Setiap rute dijaga oleh alias middleware `role:nama_role` atau `super_admin`.
   - Percobaan akses ke URL di luar wewenang otomatis menghasilkan respon HTTP **403 Forbidden**.
2. **Isolasi Berdasarkan Seksi (RBAC Seksi)**:
   - Staf Seksi dan Kasi Seksi hanya dapat melihat dan memproses data permohonan yang sesuai dengan nomor seksinya (`users.seksi`).
   - Tiket yang ditolak dikunci (`penanggung_jawab_id`) agar tidak bisa di-ADD oleh staf lain, menghindari benturan revisi.
3. **Impersonasi Aman (Read-Only Guard)**:
   - Fitur Impersonate pada Super Admin diatur oleh `ImpersonateMiddleware`.
   - Menggunakan verifikasi metode HTTP: jika request bukan metode aman (`GET`, `HEAD`), sistem otomatis membatalkan eksekusi dengan pesan bahwa mode lihat/coba bersifat baca-saja.
4. **Isolasi Data Demo**:
   - Data demo diberi flag eksplisit `is_demo = true`. Operasi reset demo tidak akan pernah menyentuh atau menghapus data asli pemohon.
5. **Kerahasiaan Kredensial**:
   - Kolom `password_text` disembunyikan (`$hidden`) dari seluruh serialisasi JSON / API, dan hanya dirender pada halaman Manajemen Akun Admin.

---

## Rangkuman Akun Default untuk Pengujian

| Jabatan / Peran | Username | Password Default | NIP | Keterangan Khusus |
|---|---|---|---|---|
| Super Admin | `superadmin` | `superadmin123` | `100000000000000001` | Akses developer & darurat |
| Admin | `admin` | `admin123` | `1234567890` | Administrator operasional kantor |
| Loket Pendaftaran | `loketdaftar1` | `loketdaftar123` | `199501012020012001` | Petugas front office loket |
| Loket Ploting | `loketploting1` | `ploting123` | `199602022021011002` | Petugas pemetaan bidang |
| Petugas Warkah | `warkah1` | `warkah123` | `199206102019032024` | Pengelola ruang arsip warkah |
| Staf Seksi 1 | `staf1a` | `staf123` | `199703032022012003` | Terikat Seksi 1 |
| Kasi Seksi 1 | `kasi1` | `kasi123` | `198504042010011004` | Kepala Seksi 1 |
| Loket Penyerahan | `serah1` | `serah123` | `199805052023012005` | Petugas penyerahan produk |
| Loket Pengaduan | `pengaduan1` | `pengaduan123` | `199906062024011006` | Petugas loket pengaduan |
| Kakan | `kakan` | `kakan123` | `196508151990031004` | Kepala Kantor Pertanahan |

---

*Dokumen ini disusun dan disinkronkan secara langsung dari kode sumber sistem SAPA Balam (per 4 November 2026).*

