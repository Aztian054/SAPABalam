# Alur Permohonan Berkas (Sertipikat Tanah)

> **Dokumen Resmi Berdasarkan Kode Implementasi Aktual**  
> Sistem: LOKET2026 (Laravel 13 · PHP 8.3 · MySQL · Blade)  
> Satuan Kerja: Kantor Pertanahan Kota Bandar Lampung (BALAM)  
> Status: Revisi saat ini (tidak mencakup Super Admin)

---

## 1. Ringkasan Alur Singkat

```
Loket Pendaftaran (Wizard 4 Tahap)
    ↓ (status: di_database)
Staf Seksi 1/2 (ADD → diproses)
    ↓ (Selesai / Tolak / ADD ULANG)
Loket Penyerahan (ADD → diproses → SELESAI)
    ↓
Arsip Otomatis (setelah rentang waktu)
```

---

## 2. Detail Alur Permohonan Berkas

### Tahap A: Loket Pendaftaran — Pendaftaran Baru (Wizard 4 Tahap)

| Langkah | Aksi | Detail Kode |
|---------|------|-------------|
| 1 | Buka menu **Pendaftaran Baru** | `pendaftaran.create` |
| 2 | **Tahap 1**: Input identitas pemohon, kuasa, No. Berkas fisik | `PendaftaranController@storeTahap1` |
| 3 | **Tahap 2**: Ploting bidang (jika perlu) | `PendaftaranController@tahap2` / `PlotingController` |
| 4 | **Tahap 3**: Data yuridis (jenis permohonan, kategori hak, no hak, lokasi) | `PendaftaranController@tahap3` |
| 5 | **Tahap 4**: Kebutuhan warkah & finalisasi | `PendaftaranController@tahap4` |
| 6 | Klik **Selesaikan Pendaftaran** → tiket berstatus `di_database` | `PendaftaranController@simpanTahap4` |

- **Kode tiket**: Otomatis prefix `JP` + tanggal + urutan (contoh: `JP26102026001`)
- **Riwayat**: `stage_dari` → `stage_ke` disimpan di `riwayat_statuses` dengan snapshot `nama_pelaku`, `jabatan_pelaku`, `role_pelaku`

---

### Tahap B: Ploting (Opsional, untuk bidang yang belum terpetakan)

| Aktor | Menu | Aksi |
|-------|------|------|
| Loket Ploting | `ploting.index` | Terima draf dari Loket Pendaftaran yang butuh ploting |
| | | Proses pemetaan/koordinat → **Selesai Ploting** |
| Loket Pendaftaran | `ploting.index` (tab Sudah Ploting) | Klik **Lanjut Pendaftaran** |

---

### Tahap C: Staf Seksi 1/2 — Back Office (Cari → ADD → KIRIM)

| Langkah | Aksi | Role yang boleh | Route |
|---------|------|-----------------|-------|
| 1 | Cari tiket di antrean pusat (`di_database`) | Staf Seksi | `seksi.index` |
| 2 | Klik **ADD Tiket** → pilih Peran Bidang | Staf Seksi | `seksi.add` (POST) |
| 3 | Tiket berstatus `diproses`, jadi `penanggung_jawab` staf tersebut | Staf Seksi | `seksi.tiket-saya` |
| 4 | Proses berkas (verifikasi, validasi, analisis) | Staf Seksi | `seksi.show` |
| 5 | Klik **Selesai & Kirim** → kembali ke `di_database` | Staf Seksi | `seksi.selesis.store` (POST) |
| 5b | Klik **Tolak** → berstatus `ditolak`, terkunci pada staf penolak | Staf Seksi | `seksi.tolak` (POST) |
| 6 | **ADD ULANG** (staf penolak yang sama) setelah diperbaiki | Staf Seksi | `seksi.add` (POST) |

- **Peran Bidang**: Verifikasi, Validasi, dan lainnya (dari `Tiket::PERAN_BIDANG`)
- **Kasi Seksi 1/2**: Hanya **monitoring (read-only)** tiket yang di-ADD staf seksinya (`seksi.index_kasi`)
- **Admin**: Bisa **Lepas Tiket** jika staf berhalangan/cuti (`admin.lepasTiket`)

---

### Tahap D: Loket Penyerahan — Penutup Alur

| Langkah | Aksi | Route |
|---------|------|-------|
| 1 | Pemohon datang mengambil berkas | `penyerahan.index` |
| 2 | Cari nomor tiket (`JP`) → klik **ADD** | `penyerahan.add` (POST) |
| 3 | Tiket masuk ke **Tiket Saya** | `penyerahan.tiket-saya` |
| 4 | Klik **SELESAI & SERAHKAN PRODUK** | `penyerahan.selesis.store` (POST) |
| 5 | Isi formulir serah terima (nama penerima, hubungan, NIK penerima) | `penyerahan.show` |
| 6 | Tiket berstatus `selesai` → siap arsip otomatis | `PenyerahanController@selesaiProses` |

- Loket Penyerahan **tidak bisa menolak** (hanya SELESAI)
- Tanda tangan digital: Nama + Jabatan (snapshot) disimpan di `riwayat_statuses`

---

### Tahap E: Admin — Intervensi & Arsip

| Aksi | Detail | Route |
|------|--------|-------|
| Lepas Tiket | Intervensi tiket macet di tangan staf Seksi 1/2 → kembali ke `di_database` | `admin.lepasTiket` |
| Arsip Massal | Arsipkan tiket `selesai` yang sudah cukup umur | `admin.arsipkanMassal` |
| Cetak Arsip | Cetak dokumen arsip | `admin.arsipStore` |

---

### Tahap F: Arsip Otomatis

- Tiket berstatus `selesai` akan diarsipkan otomatis oleh sistem setelah rentang waktu tertentu (konfigurasi `hari_arsip`, default 30 hari)
- Perintah: `ArsipkanTiketSelesai` (console command)
- Riwayat: `stage_dari='selesai'` → `stage_ke='Arsip'`

---

### Tahap G: Petugas Warkah — Sirkulasi Warkah Fisik

| Status | Aksi |
|--------|------|
| `diminta` | Permintaan otomatis dari Loket Pendaftaran atau staf |
| `diterima` | Petugas warkah memvalidasi dan mencari di rak |
| `siap` | Dokumen siap diambil staf seksi |
| `dipinjam` | Diserahkan ke staf peminjam (catat nama, seksi, batas waktu 7 hari) |
| `dikembalikan` | Berkas kembali ke ruang warkah |

- Menu: `warkah-permintaan.index`, `pinjaman`, `dikembalikan`

---

## 3. Matriks Role dalam Alur Permohonan

| Role | Terdapat dalam Alur | Aksi Utama |
|------|---------------------|------------|
| Loket Pendaftaran | ✅ Awal | Daftar berkas, generate kode tiket JP |
| Loket Ploting | ✅ Opsional | Pemetaan bidang |
| Staf Seksi 1/2 | ✅ Intip | ADD, proses, selesai/tolak/add ulang |
| Kasi Seksi 1/2 | ✅ Monitoring | Pantau staf seksinya (read-only) |
| Petugas Warkah | ✅ Pendukung | Sirkulasi warkah fisik |
| Loket Penyerahan | ✅ Akhir | ADD, serahkan produk, selesai |
| Admin | ✅ Intervensi | Lepas tiket, arsip massal |
| Kakan | ✅ Evaluasi | Monitoring read-only seluruh alur |

---

## 4. Status Tiket (Permohonan)

| Status | Keterangan | Badge |
|--------|------------|-------|
| `di_database` | Tiket di database, belum dipegang staf | `warning` |
| `diproses` | Sedang diproses staf Seksi | `primary` |
| `ditolak` | Ditolak staf (perlu revisi) | `danger` |
| `di_database` (kembali) | Kembali dari staf setelah selesai | `success` |
| `selesai` | Diserahkan Loket Penyerahan | `success` |
| `batal` | Dibatalkan | `danger` |

---

## 5. Riwayat Alur (Snapshot)

Setiap perubahan status mencatat:
- `nama_pelaku` — Nama asli pegawai
- `jabatan_pelaku` — Jabatan pegawai
- `role_pelaku` — Role akses (audit-only, tidak ditampilkan)

Format tampilan: **Nama (Jabatan)** — contoh: `ValidatorSU (Validator SU)`

---

## Catatan Penting

- **Super Admin tidak termasuk** dalam dokumentasi alur operasional ini (akses developer & darurat saja)
- **Kasi Seksi 3, 4, 5 dan Kasubbag TU** tidak melihat daftar permohonan/tiket — hanya pengaduan
- **Staf Seksi 1 dan 2** hanya yang bisa ADD dan memproses tiket permohonan
- **Loket Penyerahan** hanya bisa SELESAI, tidak bisa TOLAK
