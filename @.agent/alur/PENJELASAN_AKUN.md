# Penjelasan Akun & Role Operasional

> **Dokumen Resmi Berdasarkan Kode Implementasi Aktual**  
> Sistem: LOKET2026 (Laravel 13 · PHP 8.3 · MySQL)  
> Satuan Kerja: Kantor Pertanahan Kota Bandar Lampung (BALAM)  
> Status: Revisi saat ini (Super Admin dikecualikan)

---

## Daftar Role Akun Operasional (11 Role)

| # | Kode Role | Nama Tampilan | Middleware | Fungsi Utama | Lihat Tiket Permohonan | Lihat Pengaduan |
|---|-----------|---------------|------------|--------------|------------------------|-----------------|
| 1 | `admin` | Admin | `role:admin` | Operasional lintas unit, lepas tiket, kelola user & master data | ✅ | ✅ |
| 2 | `loket_pendaftaran` | Loket Pendaftaran | `role:loket_pendaftaran` | Wizard pendaftaran berkas 4 tahap, cetak antrian, arsip | ✅ | ❌ |
| 3 | `loket_ploting` | Loket Ploting | `role:loket_ploting` | Pemetaan bidang / ploting berkas pendaftaran | ❌ | ❌ |
| 4 | `petugas_warkah` | Petugas Warkah | `role:petugas_warkah` | Sirkulasi warkah fisik (permintaan, pinjam, kembali, ubah batas) | ❌ | ❌ |
| 5 | `staf_seksi` | Staf Seksi | `role:staf_seksi` | Back office: Cari & ADD berkas, proses tiket, selesai / tolak | ❌ | ❌ |
| 6 | `kasi_seksi` | Kasi Seksi | `role:kasi_seksi` | Monitoring permohonan seksi & penanganan pengaduan seksi | ✅ (Seksi 1,2 only) | ✅ |
| 7 | `kasi_unit` | Kasi Unit | `role:kasi_unit` | Penanganan pengaduan unit kerja (Seksi 3-5) | ❌ | ✅ |
| 8 | `kasubbag_tu` | Kasubbag TU | `role:kasubbag_tu` | Penanganan pengaduan Tata Usaha (TU) | ❌ | ✅ |
| 9 | `loket_penyerahan` | Loket Penyerahan | `role:loket_penyerahan` | Penutup alur: ADD antrian penyerahan & aksi SELESAI berkas | ❌ | ❌ |
| 10 | `loket_pengaduan` | Loket Pengaduan | `role:loket_pengaduan` | Penerimaan, registrasi tiket LP, dan kirim ulang pengaduan | ❌ | ✅ (milik sendiri) |
| 11 | `kakan` | Kakan | `role:kakan` | Monitoring & Evaluasi eksekutif seluruh unit (read-only) | ✅ (semua) | ✅ (monitoring) |

---

## 1. Admin (`admin`)

### Dasar Kode
- **Middleware**: `role:admin`
- **Model**: `User::isAdmin()`
- **Controller**: `AdminController`, `AccountController`, `SettingController`, `FormPendaftaranController`
- **Route Prefix**: `/admin/*`

### Fungsi Utama
- **Database Berkas Terpadu**: Memantau seluruh tiket dari seluruh tahapan dan loket secara terpusat
- **Lepas Tiket**: Mengintervensi tiket yang terkunci di tangan staf Seksi 1 atau Seksi 2 (misal staf berhalangan/cuti) agar tiket kembali ke antrean umum (`di_database`)
- **Manajemen Akun Pengguna**: Menambah, mengedit data identitas (NIP, No HP, Seksi, Jabatan), mengaktifkan/menonaktifkan akun staf
- **Pengaturan Master Data & Form**: Mengatur jenis hak, persyaratan berkas permohonan, saran koreksi kekurangan dokumen, dan template pendaftaran
- **Setting Sistem**: Mengatur parameter retensi berkas arsip, batas waktu macet tiket, dan batas SLA pengaduan

### Sidebar
Dashboard, Daftar Berkas, Berkas Selesai, Arsip Folder, Manajemen Akun, Kelola Form Pendaftaran, Setting, Log Aktivitas, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `admin` | `admin123` | `1234567890` |

---

## 2. Loket Pendaftaran (`loket_pendaftaran`)

### Dasar Kode
- **Middleware**: `role:loket_pendaftaran`
- **Model**: `User::isLoketPendaftaran()`
- **Controller**: `PendaftaranController`, `PlotingController`
- **Route Prefix**: `/pendaftaran/*`

### Fungsi Utama
- **Wizard Pendaftaran 4 Tahap**:
  - Tahap 1: Identitas & Berkas (generate Kode Tiket `JP`)
  - Tahap 2: Ploting Bidang
  - Tahap 3: Data Yuridis
  - Tahap 4: Kebutuhan Warkah & Finalisasi
- **Cetak Antrian & Formulir**: Mencetak tanda terima bukti pendaftaran pemohon
- **Monitoring Ploting**: Memantau tab Draf Menunggu Ploting & Draf Selesai Ploting

### Sidebar
Pendaftaran Baru, Daftar Berkas Permohonan, Ploting, Daftar Arsip, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `loket1` | `loket123` | `199501012020012001` |

---

## 3. Loket Ploting (`loket_ploting`)

### Dasar Kode
- **Middleware**: `role:loket_ploting`
- **Model**: `User::isLoketPloting()`
- **Controller**: `PlotingController`
- **Route Prefix**: `/ploting/*`

### Fungsi Utama
- Menerima kiriman draf pendaftaran dari Loket Pendaftaran yang membutuhkan pemetaan/ploting bidang tanah
- Meninjau posisi koordinat/peta, memverifikasi spasial, membubuhkan catatan hasil ploting
- Mengubah status draf dari "belum" menjadi "sudah ploting"

### Sidebar
Daftar Berkas Ploting, Daftar Arsip, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `ploting` | `ploting123` | `199602022021011002` |

---

## 4. Petugas Warkah (`petugas_warkah`)

### Dasar Kode
- **Middleware**: `role:petugas_warkah`
- **Model**: `User::isPetugasWarkah()`
- **Controller**: `WarkahPermintaanController`
- **Route Prefix**: `/warkah-permintaan/*`

### Fungsi Utama
- Mengatur sirkulasi peminjaman warkah (Buku Tanah & Surat Ukur) di ruang arsip
- Siklus status: `diminta` → `diterima` → `siap` → `dipinjam` → `dikembalikan`
- Ubah Batas Waktu Pinjaman, Monitoring Keterlambatan

### Sidebar
Permintaan Warkah, Berkas Pinjaman, Berkas Dikembalikan, Arsip, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `warkah` | `warkah123` | `199206102019032024` |

---

## 5. Staf Seksi (`staf_seksi`)

### Dasar Kode
- **Middleware**: `role:staf_seksi`
- **Model**: `User::isStafSeksi()`, `User::requiresSeksi()`
- **Controller**: `SeksiController`
- **Route Prefix**: `/seksi/*`

### Fungsi Utama
- Setiap akun staf terikat pada unit seksi tertentu (Seksi 1, 2, 3, 4, 5, atau TU)
- Khusus back office pendaftaran dilayani oleh **Seksi 1 dan Seksi 2**
- Pola kerja **Cari → ADD**: mencari berkas pada antrean pusat (`di_database`), lalu menekan tombol **ADD** untuk mengambil tanggung jawab
- **Tiket Saya**: Area kerja pribadi tempat staf memproses tiket yang dipegangnya
- Keputusan: **SELESAI** (kembali ke database), **TOLAK** (catatan revisi, terkunci pada staf penolak), **ADD ULANG** (staf penolak yang sama)

### Sidebar
Cari & ADD Berkas, Tiket Saya, Daftar Selesai, Daftar Ditolak, Laporan

### Akun Default
| Username | Password | Seksi | NIP |
|----------|----------|-------|-----|
| `validatorsu` | `validator123` | 1 | `196508151990031004` |
| `alihmediasu` | `alihmedia123` | 1 | `196508151990031004` |
| `validatorbt` | `validator123` | 2 | `199703032022012003` |
| `alihmediabt` | `alihmedia123` | 2 | `199804042023011004` |

---

## 6. Kasi Seksi (`kasi_seksi`)

### Dasar Kode
- **Middleware**: `role:kasi_seksi`
- **Model**: `User::isKasiSeksi()`, `User::requiresSeksi()`
- **Controller**: `SeksiController`, `PengaduanUnitController`
- **Route Prefix**: `/seksi/*`, `/pengaduan-unit/*`

### Fungsi Utama
- **Monitoring Permohonan Seksi**: Mengawasi seluruh berkas tiket yang sedang dipegang oleh staf-staf di bawah seksinya. Kasi baru melihat tiket setelah staf seksinya melakukan **ADD**
- **Daftar Selesai & Ditolak Seksi**: Melihat riwayat kinerja penyelesaian dan penolakan berkas oleh seluruh staf di seksinya
- **Penanganan Pengaduan Seksi**: Menerima disposisi pengaduan masyarakat (`LP`) yang ditujukan ke seksinya, memproses jawaban klarifikasi, atau mengembalikan berkas pengaduan jika bukan kewenangan seksinya
- **Kasi Seksi 1 dan 2**: Bisa melihat daftar permohonan/tiket. **Kasi Seksi 3, 4, 5**: Hanya pengaduan

### Sidebar (Kasi 1 & 2)
Daftar Permohonan Seksi, Daftar Selesai, Daftar Ditolak, Daftar Pengaduan, Pengaduan Selesai, Laporan

### Sidebar (Kasi 3, 4, 5)
Daftar Pengaduan, Pengaduan Selesai, Laporan

### Akun Default
| Username | Password | Seksi | NIP |
|----------|----------|-------|-----|
| `kasi1` | `kasi123` | 1 | `198504042010011004` |
| `kasi2` | `kasi123` | 2 | `198605052011012005` |
| `kasi3` | `kasi123` | 3 | `198706062012011006` |
| `kasi4` | `kasi123` | 4 | `198908082014011008` |
| `kasi5` | `kasi123` | 5 | `199009092015011009` |

---

## 7. Kasi Unit (`kasi_unit`)

### Dasar Kode
- **Middleware**: `role:kasi_unit`
- **Model**: `User::isKasiUnit()`
- **Controller**: `PengaduanUnitController`
- **Route Prefix**: `/pengaduan-unit/*`

### Fungsi Utama
- Fokus penanganan pengaduan untuk Kepala Seksi di luar back office sertipikat (Seksi 3, 4, 5)
- Menerima pengaduan masyarakat (`LP`), memberikan tindak lanjut/solusi, menutup kasus (`status: selesai`), atau mengembalikan berkas pengaduan ke Loket Pengaduan bila salah kamar/salah unit tujuan

### Sidebar
Daftar Pengaduan, Pengaduan Selesai, Laporan

### Akun Default
Tidak ada akun default khusus (gunakan akun Kasi Seksi 3-5 dengan seksi yang sesuai)

---

## 8. Kasubbag TU (`kasubbag_tu`)

### Dasar Kode
- **Middleware**: `role:kasubbag_tu`
- **Model**: `User::isKasiUnit()` (termasuk `kasubbag_tu`)
- **Controller**: `PengaduanUnitController`
- **Route Prefix**: `/pengaduan-unit/*`

### Fungsi Utama
- Mengelola keluhan, aduan pelayanan administrasi, persuratan, dan kepegawaian internal kantor yang ditujukan ke Subbagian Tata Usaha
- Menerima aduan (`LP`), menelaah, memberikan jawaban resmi, dan menutup pengaduan

### Sidebar
Daftar Pengaduan, Pengaduan Selesai, Laporan

### Akun Default
| Username | Password | Seksi | NIP |
|----------|----------|-------|-----|
| `kasubbag` | `kasubbag123` | TU | `198807072013012007` |

---

## 9. Loket Penyerahan (`loket_penyerahan`)

### Dasar Kode
- **Middleware**: `role:loket_penyerahan`
- **Model**: `User::isLoketPenyerahan()`
- **Controller**: `PenyerahanController`
- **Route Prefix**: `/penyerahan/*`

### Fungsi Utama
- Berada di akhir rantai operasional pendaftaran berkas tanah
- Pola **Antrian Penyerahan → ADD**: mencari atau memindai nomor tiket pemohon yang datang mengambil produk layanan
- Satu-satunya tombol aksi akhir adalah **SELESAI** (tidak bisa menolak)
- Form Serah Terima Produk: nama penerima, hubungan dengan pemohon, NIK penerima, no HP, catatan penyerahan
- Setelah diserahkan, tiket berstatus `selesai` dan siap diarsipkan otomatis

### Sidebar
Antrian Penyerahan, Tiket Saya, Daftar Selesai, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `serah1` | `loket123` | `199805052023012005` |

---

## 10. Loket Pengaduan (`loket_pengaduan`)

### Dasar Kode
- **Middleware**: `role:loket_pengaduan`
- **Model**: `User::isLoketPengaduan()`
- **Controller**: `PengaduanController`
- **Route Prefix**: `/pengaduan/*`

### Fungsi Utama
- Menerima laporan/keluhan masyarakat baik yang datang langsung ke loket maupun secara daring
- Registrasi Nomor LP Otomatis: format `LP` + tanggal (ddmmyyyy) + nomor urut 4 digit (contoh: `LP051120260001`)
- Disposisi ke Unit Terkait: memilih unit/seksi tujuan pengaduan (Seksi 1 s.d. 5 atau Bagian TU) — **hanya 1 tujuan**
- Pengiriman Ulang (Re-Submit): bila pengaduan dikembalikan, dapat memperbaiki data dan mengirim ulang

### Sidebar
Buat Pengaduan, Daftar Pengaduan, Pengaduan Selesai, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `pengaduan1` | `loket123` | `199906062024011006` |

---

## 11. Kakan (`kakan`)

### Dasar Kode
- **Middleware**: `role:kakan`
- **Model**: `User::isKakan()`
- **Controller**: `KakanController`
- **Route Prefix**: `/kakan/*`

### Fungsi Utama
- **Executive Monitoring & Evaluasi (Read-Only)**: Kepala Kantor memantau performa seluruh alur kerja pelayanan pertanahan tanpa kewenangan mengubah status secara langsung
- **Statistik Beban Kerja Seksi**: Mengetahui jumlah berkas yang sedang berproses per seksi (Seksi 1, 2, 3, 4, 5, TU)
- **Ringkasan Indikator Kinerja**: Memantau volume tiket harian, tiket berstatus `di_database`, `diproses`, `ditolak`, `selesai`, dan `batal`
- **Audit Trail & Timeline Berkas**: Menelaah jejak riwayat status setiap berkas secara menyeluruh

### Sidebar
Monitoring & Evaluasi, Laporan

### Akun Default
| Username | Password | NIP |
|----------|----------|-----|
| `kakan` | `kakan123` | `196508151990031004` |

---

## Matriks Hak Akses (Excluding Super Admin)

| Fitur | Admin | Loket Pendaftaran | Loket Ploting | Petugas Warkah | Staf Seksi | Kasi Seksi | Kasi Unit | Kasubbag TU | Loket Penyerahan | Loket Pengaduan | Kakan |
|-------|-------|-------------------|---------------|----------------|------------|------------|-----------|-------------|------------------|-----------------|-------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (RO) |
| Pendaftaran Baru | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Ploting | ❌ | ⭮ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Cari & ADD Berkas | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Monitoring Berkas Seksi | ❌ | ❌ | ❌ | ❌ | (sendiri) | (seksinya) | ❌ | ❌ | ❌ | ❌ | ✅ (semua) |
| Lepas Tiket | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Sirkulasi Warkah | ❌ | ❌ | ❌ | ✅ | (peminjam) | ❌ | ❌ | ❌ | ❌ | ❌ | ⭮ |
| Penyerahan Produk | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Buat Pengaduan LP | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Tindak Lanjut & Tutup Pengaduan | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Manajemen Akun | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Master Data & Setting | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Audit Trail & Log | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Laporan | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

**Keterangan:**
- `✅` = Memiliki Hak Akses & Eksekusi
- `❌` = Tidak Memiliki Akses
- `⭮` = Akses Terbatas / Monitor only
- `(RO)` = Read-Only

---

## Catatan Penting

- **Super Admin tidak termasuk** dalam dokumentasi ini (akses developer & darurat saja)
- **Kasi Seksi 1 dan 2** dapat melihat daftar permohonan/tiket (modul Seksi)
- **Kasi Seksi 3, 4, 5 dan Kasubbag TU** hanya melihat modul pengaduan, tidak ada tiket permohonan
- Semua role operasional memiliki akses ke **Laporan** dan **Profil Saya**
