# 00 — Blueprint Utama SAPA Balam (Target V2)

> **Status dokumen:** Blueprint V2 — sudah diterapkan.
> **Status implementasi kode:** sudah (lihat §9). Dokumen ini menggambarkan alur yang berlaku di aplikasi.
> **Kantor:** Pertanahan Kota Bandar Lampung (BALAM) · **Platform:** Laravel 13 · PHP 8.3 · MySQL · Blade + Bootstrap 5.3.

---

## 1. Ringkasan Sistem

SAPA Balam mendigitalkan pelayanan pertanahan dari **pendaftaran di loket** sampai **penyerahan berkas** dan **arsip**, dengan:

- satu basis data terpusat (Daftar Permohonan) sebagai antrean kerja bersama;
- pola kerja **cari → ADD → proses → KIRIM** (Selesai / Tolak);
- **back office di Seksi 1 dan 2** untuk verifikasi, validasi, dan alih media;
- **Warkah** sebagai pinjaman berkas fisik yang dicatat, bukan menu permintaan di tiap akun;
- **Loket Penyerahan** sebagai penutup alur (hanya aksi SELESAI);
- **notifikasi in-app** sebagai pengganti email;
- **pencarian pintar + filter rentang tanggal** yang seragam di semua daftar.

---

## 2. Jabatan & Hak Akses

| # | Jabatan | Kode role (usulan) | Cakupan |
|---|---|---|---|
| 1 | Super Admin | `super_admin` | Seluruh sistem, demo, log, backup |
| 2 | Admin | `admin` | Monitoring lintas unit (read-only) + akun/master/setting |
| 3 | Loket Pendaftaran | `loket_pendaftaran` | Wizard pendaftaran 4 tahap, ploting, selesai/ditolak, arsip |
| 4 | Loket Ploting | `loket_ploting` | Proses ploting berkas |
| 5 | Petugas Warkah | `petugas_warkah` | Permintaan, pinjaman fisik, pengembalian |
| 6 | Staf Seksi 1–2 | `staf_seksi` + `seksi` | ADD, proses, Selesai/Tolak, ADD ULANG |
| 7 | Kasi Seksi 1–2 | `kasi_seksi` + `seksi` | Monitoring seksi + pengaduan seksi |
| 8 | Kasi Seksi 3/4/5 & Kasubbag TU | `kasi_unit` / `kasubbag_tu` | Penanganan pengaduan unit |
| 9 | Loket Penyerahan | `loket_penyerahan` | ADD + SELESAI (tanpa Tolak) |
| 10 | Loket Pengaduan | `loket_pengaduan` | Buat & kirim pengaduan (LP) |
| 11 | Kakan | `kakan` | Monitoring read-only |

**Aturan penentu hak:** `jabatan` + `seksi` pada form akun. Kasi hanya melihat tiket setelah **staf seksinya meng-ADD**.

---

## 3. Alur Utama (B.1)

```text
[1] LOKET PENDAFTARAN  Tahap 1 Biodata + No Tiket otomatis (sistem, tidak bisa diubah) + No. Berkas
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
[5] DATABASE (Daftar Permohonan)  status: Di Database
        |
        v   +--------- BACK OFFICE: HANYA SEKSI 1 DAN SEKSI 2 ---------+
[6]  Staf Seksi 1/2 cari tiket -> ADD -> Selesai+catatan / Tolak+catatan -> KIRIM
        |     (tiket kembali ke Database, lalu staf berikutnya ADD)
        |     Tolak -> kembali ke STAF PENOLAK
        +------------------------------------------------------------+
        |
[7] LOKET PENYERAHAN cari/ketik No Tiket -> ADD -> SELESAI
        |
[8] SELESAI -> Daftar Selesai -> otomatis Daftar Arsip setelah N hari
```

### 3.1 Urutan back office tidak dikunci

Kebiasaan nyata (contoh, **bukan aturan sistem**): Alih Media Seksi 2 (BT) → Alih Media Seksi 1 (SU) → Validator (urutan bebas). Sistem mengikuti **posisi berkas fisik**: siapa pun dari Seksi 1/2 yang memegang berkas boleh ADD.

### 3.2 Alur Warkah (paralel, hanya bila dibutuhkan)

```text
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

Tambah waktu **tidak** lewat website: dibicarakan langsung/WA, lalu Petugas Warkah mengubah **Batas Kembali** + alasan (riwayat tersimpan).

---

## 4. Status & Transisi

### 4.1 Status tiket

| Status | Label UI | Chip | Arti |
|---|---|---|---|
| `di_database` | Di Database | ⚪ abu | Menunggu diambil staf/stasiun berikutnya |
| `diproses` | Sedang Diproses | 🔵 biru | Dipegang satu akun (ADD) |
| `ditolak` | Ditolak — Menunggu Perbaikan | 🔴 merah | Dikembalikan ke staf penolak (`penanggung_jawab`) |
| `selesai` | Selesai | 🟢 hijau | Diserahkan ke pemohon |
| `macet` | Macet | 🟡 kuning | **Penanda**, bukan status: melewati batas hari di stasiun |
| `dilepas_admin` | Dilepas Admin | ⚪ abu | **Legacy**: penanda lama, aksi Lepas Tiket sudah dihapus |

### 4.2 Status warkah

`diminta` → `diterima` → `siap` → `dipinjam` → `dikembalikan`; otomatis `terlambat` bila melewati batas.

### 4.3 Status pengaduan

`terkirim` → `diterima` → `selesai`; atau `dikembalikan` (salah tujuan) → **Kirim Ulang**.

### 4.4 Transisi inti

| Dari | Ke | Aksi | Aturan |
|---|---|---|---|
| — | `di_database` | Tahap 4 selesai | Warkah (bila perlu) dibuat otomatis |
| `di_database` | `diproses` | ADD (Staf/Kasi seksi 1–2, Loket Penyerahan) | Satu tiket satu akun per stasiun |
| `diproses` | `di_database` | KIRIM → Selesai | + catatan; menunggu stasiun berikutnya |
| `diproses` | `ditolak` | KIRIM → Tolak | + catatan; `penanggung_jawab = user` |
| `ditolak` | `diproses` | ADD ULANG | **Hanya** `penanggung_jawab` |
| `diproses`/`ditolak` | `di_database` | *(legacy)* Lepas Tiket Admin sudah dihapus | - |
| `diproses` | `selesai` | SELESAI (Loket Penyerahan) | Satu-satunya aksi penutup |
| `selesai` | arsip | Otomatis setelah N hari | N default 30 (Setting) |

---

## 5. Aturan Kunci (Keputusan Terkunci)

| # | Keputusan |
|---|---|
| 2 | Loket Penyerahan = penutup alur; aksi hanya **SELESAI** (tanpa Tolak) |
| 3 | Tiket Ditolak kembali ke **staf penolak**; staf lain tidak bisa ADD |
| 4 | Urutan antar seksi tidak dipaksa; **Lepas Tiket Admin sudah dihapus** |
| 4b | Back office hanya di **Seksi 1 dan 2**, setelah Loket Pendaftaran & Loket Ploting |
| 4c | Warkah hanya jika dibutuhkan; detail di Staf/Kasi 1–2; **Kakan kartu saja** |
| 4d | Pencarian pintar + filter tanggal di **semua** daftar |
| 4e | Daftar Selesai & Daftar Ditolak ada di Loket Pendaftaran, Staf 1–2, Kasi 1–2 |
| 5 | Kakan monitoring saja; **tanpa Persetujuan Akhir** |
| 6 | Format nomor sama; prefiks **JP** (permohonan) dan **LP** (pengaduan) |
| 7 | No Tiket dibuat otomatis oleh sistem; tidak ada input/edit manual |
| 8 | Kasi 1–2 melihat tiket setelah staf seksinya **ADD** |
| 9 | Notifikasi **in-app** |
| 10 | Warkah dipinjam **fisik**; tambah waktu lewat Petugas Warkah |

---

## 6. Matriks Menu

| Akun | Menu |
|---|---|
| **Super Admin** | Dashboard Sistem · Daftar Permohonan · Daftar Pengaduan · Skenario Demo · Lihat/Coba Sebagai Akun · Manajemen Akun · Master Data & Setting · Data Demo · Log · Laporan · Backup |
| **Admin** | Dashboard · Daftar Berkas (monitoring) · Berkas Selesai · Daftar Pengaduan · Pengaduan Selesai · Manajemen Akun · Master Data · Setting · Arsip Folder · Log · Laporan |
| **Loket Pendaftaran** | Dashboard · Pendaftaran Baru · Daftar Berkas Permohonan · Ploting · Daftar Warkah · Daftar Selesai · Daftar Ditolak · Arsip · Laporan |
| **Loket Ploting** | Dashboard · Daftar Berkas Ploting · Daftar Berkas Selesai Ploting · Arsip · Laporan |
| **Petugas Warkah** | Dashboard · Permintaan Warkah · Berkas Pinjaman · Berkas Dikembalikan · Arsip · Laporan |
| **Staf Seksi 1–2** | Dashboard · Daftar Permohonan (cari + ADD) · Tiket Saya · Daftar Warkah · Daftar Selesai · Daftar Ditolak · Laporan |
| **Kasi Seksi 1–2** | Dashboard · Daftar Permohonan Seksi · Daftar Warkah · Daftar Selesai · Daftar Ditolak · Daftar Pengaduan · Pengaduan Selesai · Laporan |
| **Kasi 3/4/5 & Kasubbag TU** | Dashboard · Daftar Pengaduan · Pengaduan Selesai · Laporan |
| **Loket Penyerahan** | Dashboard · Antrian Penyerahan (cari + ADD) · Tiket Saya · Daftar Selesai · Laporan |
| **Loket Pengaduan** | Dashboard · Buat Pengaduan · Daftar Pengaduan · Pengaduan Selesai · Laporan |
| **Kakan** | Dashboard · Daftar Permohonan · Daftar Warkah (opsional) · Daftar Pengaduan · Laporan |

> Menu **Atur Waktu Peminjaman** dilebur ke aksi *Ubah Batas Kembali*. Menu **Peminjaman Warkah** (ajukan/minta tambah waktu) dihapus dari Staf, Kasi, dan Loket Pendaftaran.

---

## 7. Matriks Daftar per Akun

| Daftar | Super Admin | Admin | Loket Pend. | Loket Ploting | Petugas Warkah | Staf 1–2 | Kasi 1–2 | Kasi 3–5/TU | Loket Penyerahan | Loket Pengaduan | Kakan |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Permohonan (Database) | ✔ | ✔ | | | | | | | | | ✔ |
| Berkas Permohonan | | | ✔ | | | | | | | | |
| Ploting | | | ✔ | ✔ | | | | | | | |
| Cari + ADD | | | | | | ✔ | | | ✔ | | |
| Tiket Saya | | | | | | ✔ | | | ✔ | | |
| Permohonan Seksi | | | | | | | ✔ | | | | |
| Warkah | | | ✔ (ringkas) | | ✔ | ✔ | ✔ | | | | kartu |
| Selesai | | | ✔ | | | ✔ | ✔ | | ✔ | | |
| Ditolak | | | ✔ | | | ✔ | ✔ | | | | |
| Pengaduan | ✔ | | | | | | ✔ | ✔ | | ✔ | ✔ |
| Arsip | | | ✔ | ✔ | ✔ | | | | | | |

---

## 8. Ringkasan Tabel (Main Content)

Aturan umum: kolom pertama **No**, terakhir **Aksi**; Bar Filter di atas tabel; kartu ringkasan di atas filter; pagination 20; klik baris = Detail.

| Daftar | Tanggal acuan | Kolom inti |
|---|---|---|
| Permohonan (Database) | Tgl Daftar | No Tiket · Pemohon · Jenis · No Hak · Status · Posisi · Lama · Warkah |
| Berkas Permohonan | Tgl Daftar | No Tiket · Pemohon · Jenis · No Hak · Status · Posisi · Tgl Daftar |
| Ploting (menunggu) | Waktu Kirim | No Tiket · Pemohon · NIK · No HP · Waktu Kirim · Lama Menunggu · Petugas |
| Ploting (selesai) | Waktu Kirim | No Tiket · Pemohon · Catatan · Waktu Masuk · Waktu Kirim · Lama |
| Cari + ADD | Tgl Daftar | No Tiket · Pemohon · Jenis · No Hak/Sertipikat · Status · Dipegang Oleh |
| Tiket Saya | Waktu ADD | No Tiket · Pemohon · Jenis · Peran/Bidang · Waktu ADD · Lama · Status · Warkah |
| Permohonan Seksi | Waktu ADD | No Tiket · Pemohon · Jenis · Staf · Peran · Waktu ADD · Lama · Status |
| Warkah | Tgl Permintaan/Pinjam/Kembali | No Tiket · Jenis BT/SU · Peminjam · Tgl Pinjam · Batas · Sisa · Status |
| Selesai | Tgl Selesai/Diserahkan | No Tiket · Pemohon · Jenis · Tgl Daftar · Tgl Diserahkan · Durasi · Penerima |
| Ditolak | Tgl Ditolak | No Tiket · Pemohon · Jenis · Ditolak Oleh · Seksi · Alasan · Lama · Status |
| Pengaduan | Tgl Buat/Diterima | No Pengaduan · Pengadu · Jenis · Seksi Tujuan · Status · Catatan |
| Arsip | Tgl Selesai | No Tiket · Pemohon · Jenis · Tgl Selesai · Tgl Diarsipkan |

Detail kolom & chip: lihat `BLUEPRINT_REFISI_V2.md` bagian E.

---

## 9. Status Implementasi Kode

| Modul target | Kondisi kode saat ini |
|---|---|
| Login/RBAC | **12 jabatan V2 + kolom `seksi` sudah ada**; role lama sudah dihapus dari kode & database |
| Wizard 4 tahap | **Sudah (Fase 1)** — draf di `pendaftaran_drafts`, No. Berkas manual (Tahap 1 → tiket), Tahap 2 menunggu ploting, Tahap 4 menerbitkan tiket `di_database` |
| Prefix JP/LP | **Sudah** - JP otomatis (`TiketNomorService`, tanpa input/edit manual), LP otomatis untuk pengaduan (`LP` + ddmmyyyy + urut) |
| Ploting | **Sudah (Fase 1)** — tab menunggu/selesai, proses + catatan, `Lanjut Pendaftaran` |
| Back office Seksi 1/2 | **Sudah (Fase 2)** — stasiun Cari → ADD → KIRIM (Selesai/Tolak), Peran/Bidang, Tiket Saya, Daftar Selesai |
| Ditolak + ADD ULANG | **Sudah (Fase 2)** — Tolak menahan tiket pada pemegangnya (`ditolak_pada`, `alasan_penolakan`), ADD ULANG hanya oleh pemilik. **Lepas Tiket Admin dihapus** (Admin read-only). |
| Warkah pinjam fisik | **Sudah (Fase 4)** — `diminta → diterima → siap → dipinjam → terlambat → dikembalikan`, serah terima + Ubah Batas + riwayat (`warkah_permintaan_riwayats`), pengingat harian. Cetak & lampiran belum |
| Loket Penyerahan | **Sudah (Fase 3)** — Antrian Penyerahan + ADD, Tiket Saya, SELESAI dengan form serah terima (penerima/hubungan/identitas), Daftar Selesai. Tanpa Tolak |
| Pengaduan LP | **Sudah (Fase 3)** — Loket Pengaduan membuat/mengirim/kirim ulang; unit tujuan (Kasi 1–5 & TU) menerima/menutup/mengembalikan; penanda TERLAMBAT. Lampiran & cetak belum |
| Notifikasi in-app | **Sudah (Fase 4)** — tabel `notifikasis`, `NotifikasiService`, lonceng + polling 60 detik, halaman daftar, preferensi per tipe, retensi harian |
| Pencarian + filter tanggal | **Sudah** untuk Daftar Pendaftaran, Ploting, Seksi 1/2, Antrian Penyerahan, Pengaduan, dan Arsip (cari pintar termasuk No. Berkas, nomor tiket lama, NIB, sertipikat; filter tanggal) |
| Daftar Selesai/Ditolak/Arsip otomatis | **Sudah (Fase 4)** — Daftar Selesai Seksi 1/2 + Loket Penyerahan, Daftar Ditolak, Pengaduan Selesai, dan Arsip otomatis (`loket:arsipkan-tiket` + `arsip.*` read-only). Ekspor PDF/CSV belum |

Kode yang sudah dipensiunkan: notifikasi email (`TiketRevisiMail`, `sendRevisiEmail`, form email pemohon, `FormPerbaikanPdf`). Kolom `tikets.email_pemohon` tetap disimpan tanpa UI.

---

## 10. Dokumen Turunan

- Aturan tampilan & pencarian: [`15-Komponen-Umum.md`](15-Komponen-Umum.md)
- Notifikasi: [`16-Notifikasi-In-App.md`](16-Notifikasi-In-App.md)
- PRD lengkap: [`../prd.md`](../prd.md)
- Blueprint mentah: [`BLUEPRINT_REFISI_V2.md`](BLUEPRINT_REFISI_V2.md)
