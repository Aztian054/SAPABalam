# 01 — Landing Page & Login

Dokumen ini menjelaskan alur dari pengguna membuka website hingga masuk ke dashboard sesuai role.

---

## 1. Alur Masuk Sistem

```
Buka Website (/) → Redirect ke Tracking Publik (belum login) / Dashboard (sudah login)
→ Halaman Login (/login) → Masukkan Username/Password → Redirect ke /dashboard
```

---

## 2. Landing Page (`/`)

**URL:** `GET /`

Landing page adalah halaman awal sistem:
- Jika **belum login** → redirect ke **Portal Tracking Publik** (`/tracking`).
- Jika **sudah login** → redirect ke **Dashboard** (`/dashboard`).

### Navigasi yang Tersedia

| Elemen | Tujuan | Deskripsi |
|--------|--------|-----------|
| **Log in** | `/login` | Halaman login |
| **Dashboard** | `/dashboard` | Hanya muncul jika sudah login |
| **Tracking Publik** | `/tracking` | Portal tracking tanpa login |

---

## 3. Halaman Login (`/login`)

**URL:** `GET /login` dan `POST /login`

Halaman login berisi:
- **Form login** — Username/Email + Password
- **Kartu ringkasan role** beserta label warna status
- **Akun default seeder** (Quick Login) untuk uji coba 9 role

### Akun Default (Seeder) untuk Uji Coba

| Username | Password | Role | Keterangan |
|---|---|---|---|
| `admin` | `admin123` | admin | Akun admin permanen — akses penuh semua modul |
| `pemimpin` | `pemimpin123` | pemimpin | View-only monitoring (aksi terkunci) |
| `loket1` | `loket123` | loket | Petugas loket #1 |
| `verifikator1` | `verif123` | verifikator | Petugas verifikator #1 |
| `warkah1` | `warkah123` | warkah | Petugas warkah #1 |
| `vbtel1` | `vbtel123` | validator_btel | Validator Buku Tanah #1 |
| `vsuel1` | `vsuel123` | validator_suel | Validator Surat Ukur #1 |
| `ambt1` | `ambt123` | alih_media_btel | Alih Media Buku Tanah #1 |
| `amsu1` | `amsu123` | alih_media_suel | Alih Media Surat Ukur #1 |

### Validasi Login

- Username/password **salah** → tampilkan pesan error.
- **Berhasil** → redirect ke `/dashboard`.

---

## 4. Redirect Setelah Login

Sistem mengarahkan pengguna ke **`/dashboard`**. Dari dashboard, pengguna bisa melihat statistik, mengakses menu sesuai role (sidebar), dan membuka **Portal Tracking Publik**.

---

## 5. Akses Per Role (Sidebar)

| Role | Menu yang Muncul |
|------|------------------|
| `admin` | Daftar Tiket (Database), Tambah Tiket, Tiket Selesai, Revisi, Arsip Folder, Manajemen Akun |
| `loket` | Pendaftaran Baru, Daftar Tiket Loket |
| `verifikator` | Verifikasi Berkas |
| `warkah` | Lembar Kerja Warkah |
| `validator_btel` | Validasi Pra-BTel |
| `validator_suel` | Validasi Pra-SuEl |
| `alih_media_btel` | Alih Media BT |
| `alih_media_suel` | Alih Media SU |
| `pemimpin` | Dashboard Pemimpin (monitoring, view-only) |

> Dashboard, Portal Tracking Publik, dan Laporan & Rekap SLA tersedia di semua akun.

---

## 6. Logout

User dapat logout melalui menu profil/avatar di kanan atas. Logout menghapus sesi dan mengembalikan ke halaman login.

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx`.*