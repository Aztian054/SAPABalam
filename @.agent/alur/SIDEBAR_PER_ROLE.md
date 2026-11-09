# Sidebar Menu per Role Akun

> **Dokumen Resmi Berdasarkan Kode Implementasi Aktual**  
> Sistem: LOKET2026  
> Sidebar di-render dari tabel `menus` melalui `SidebarService::groupsFor($user)`  
> File: `resources/views/partials/sidebar.blade.php`  
> Status: Revisi saat ini (Super Admin dikecualikan)

---

## Struktur Sidebar Umum

Semua role (kecuali Super Admin) memiliki:

| Item | Route | Keterangan |
|------|-------|------------|
| Dashboard | `dashboard` | Halaman utama |
| Monitoring Laporan | `reports.index` | Laporan dan monitoring |
| Profil Saya | `profil.edit` | Pengaturan profil mandiri |

---

## 1. Admin (`admin`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Daftar Berkas (Database) | `admin.index` | `AdminController@index` |
| Berkas Selesai | `admin.selesai` | `AdminController@selesai` |
| Arsip Folder | `admin.arsip` | `AdminController@arsipIndex` |
| Manajemen Akun | `admin.users` | `AccountController@index` |
| Kelola Form Pendaftaran | `admin.form-pendaftaran` | `FormPendaftaranController@index` |
| Setting | `admin.setting` | `SettingController@edit` |
| Log Aktivitas | `admin.log` | `LogAktivitasController@index` |

---

## 2. Kakan (`kakan`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Monitoring & Evaluasi | `kakan.index` | `KakanController@index` |

> Kakan hanya memiliki menu monitoring (read-only), tidak ada aksi ubah data.

---

## 3. Loket Pendaftaran (`loket_pendaftaran`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Pendaftaran Baru | `pendaftaran.create` | `PendaftaranController@create` |
| Daftar Berkas Permohonan | `pendaftaran.index` | `PendaftaranController@index` |
| Ploting | `ploting.index` | `PlotingController@index` |
| Daftar Arsip | `arsip.index` | `ArsipController@index` |

---

## 4. Loket Ploting (`loket_ploting`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Daftar Berkas Ploting | `ploting.index` | `PlotingController@index` |
| Daftar Arsip | `arsip.index` | `ArsipController@index` |

---

## 5. Petugas Warkah (`petugas_warkah`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Permintaan Warkah | `warkah-permintaan.index` | `WarkahPermintaanController@index` |
| Berkas Pinjaman | `warkah-permintaan.pinjaman` | `WarkahPermintaanController@pinjaman` |
| Berkas Dikembalikan | `warkah-permintaan.dikembalikan` | `WarkahPermintaanController@dikembalikan` |
| Arsip | `arsip.index` | `ArsipController@index` |

---

## 6. Staf Seksi (`staf_seksi`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Cari & ADD Berkas | `seksi.index` | `SeksiController@index` |
| Tiket Saya | `seksi.tiket-saya` | `SeksiController@tiketSaya` |
| Daftar Selesai | `seksi.selesai` | `SeksiController@selesai` |
| Daftar Ditolak | `seksi.ditolak` | `SeksiController@ditolak` |

---

## 7. Kasi Seksi (`kasi_seksi`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Daftar Permohonan Seksi | `seksi.index` | `SeksiController@indexKasi` |
| Daftar Selesai | `seksi.selesai` | `SeksiController@selesai` |
| Daftar Ditolak | `seksi.ditolak` | `SeksiController@ditolak` |
| Daftar Pengaduan | `pengaduan-unit.index` | `PengaduanUnitController@index` |
| Pengaduan Selesai | `pengaduan-unit.selesai` | `PengaduanUnitController@selesai` |

> Kasi Seksi 1 dan 2 juga melihat modul permohonan (tiket). Kasi Seksi 3, 4, 5 hanya melihat modul pengaduan.

---

## 8. Kasi Unit (`kasi_unit`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Daftar Pengaduan | `pengaduan-unit.index` | `PengaduanUnitController@index` |
| Pengaduan Selesai | `pengaduan-unit.selesai` | `PengaduanUnitController@selesai` |

---

## 9. Kasubbag TU (`kasubbag_tu`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Daftar Pengaduan | `pengaduan-unit.index` | `PengaduanUnitController@index` |
| Pengaduan Selesai | `pengaduan-unit.selesai` | `PengaduanUnitController@selesai` |

---

## 10. Loket Penyerahan (`loket_penyerahan`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Antrian Penyerahan | `penyerahan.index` | `PenyerahanController@index` |
| Tiket Saya | `penyerahan.tiket-saya` | `PenyerahanController@tiketSaya` |
| Daftar Selesai | `penyerahan.selesai` | `PenyerahanController@selesai` |

---

## 11. Loket Pengaduan (`loket_pengaduan`)

| Menu Item | Route | Controller |
|-----------|-------|------------|
| Buat Pengaduan | `pengaduan.create` | `PengaduanController@create` |
| Daftar Pengaduan | `pengaduan.index` | `PengaduanController@index` |
| Pengaduan Selesai | `pengaduan.selesai` | `PengaduanController@selesai` |

---

## Catatan

- **Super Admin dikecualikan** dari dokumentasi sidebar ini (akses developer & darurat melalui `/super-admin/*`)
- Menu "Monitoring Laporan" (`reports.index`) tersedia untuk semua role operasional
- Menu "Profil Saya" tersedia untuk semua role (kecuali saat mode impersonate)
- Sidebar dinamis: menu ditampilkan berdasarkan role pengguna yang login, bukan hardcoded
