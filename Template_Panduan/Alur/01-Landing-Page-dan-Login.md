# 01 — Landing Page & Login

Dokumen ini menjelaskan alur dari pengguna membuka website hingga masuk ke dashboard sesuai role — **sesuai perilaku aplikasi LOKET 2026 saat ini** (`routes/web.php`, `AuthController`).

---

## 1. Alur Masuk Sistem

```
Buka Website (/) → Redirect instan:
   • Belum login → Portal Tracking Publik (/tracking)
   • Sudah login → Dashboard (/dashboard)
→ Halaman Login (/login) → isi username ATAU email + password
→ Validasi (rate-limit 6×/menit; akun harus aktif)
→ Redirect ke /dashboard
```

---

## 2. Landing Page (`/`)

**URL:** `GET /` — tidak memiliki halaman sendiri (redirect langsung):

- Jika **belum login** → redirect ke **Portal Tracking Publik** (`/tracking`).
- Jika **sudah login** → redirect ke **Dashboard** (`/dashboard`).

---

## 3. Halaman Login (`/login`)

**URL:** `GET /login` (tampil form) dan `POST /login` (proses login).

Halaman login berisi:
- **Form login** (satu kolom input identitas) — menerima **Username** **atau Email** + **Password** + checkbox *Remember me*.
- **Kartu ringkasan 9 role** beserta label warna status.
- **Akun default seeder** (Quick Login) untuk uji coba cepat.

### ⚙️ Proses & Aturan Login (`AuthController@login`)

1. **Identitas:** field berlabel login menerima **username atau email** — sistem mendeteksi otomatis: jika berisi `@`, dicocokkan ke kolom `email`; selain itu dicocokkan ke kolom `username`.
2. **Akun aktif:** akun wajib berstatus **aktif** (`is_active = true`). Akun non-aktif ditolak walau username/password benar.
3. **Rate Limit:** route `POST /login` memakai middleware **`throttle:6,1`** — maksimal **6 percobaan login per menit** per klien. Setelah itu percobaan berikutnya ditolak (HTTP 429 *Too Many Attempts*). Tunggu 1 menit sebelum mencoba lagi.
4. **Gagal** → kembali ke `/login` dengan pesan: *"Username atau password yang Anda masukkan salah, atau akun tidak aktif."*
5. **Berhasil** → sesi di-regenarate → redirect ke `/dashboard` dengan flash *"Selamat datang kembali, {nama}"*.

### Akun Seeder untuk Uji Coba (2 akun per jabatan)

| Jabatan | Username #1 | Username #2 | Password | Role |
|---|---|---|---|---|
| 🔴 Admin | `admin` | — | `admin123` | `admin` |
| 🟡 Pemimpin | `pemimpin` | `pemimpin2` | `pemimpin123` | `pemimpin` |
| 🟠 Loket | `loket1` | `loket2` | `loket123` | `loket` |
| 🔵 Verifikator | `verifikator1` | `verifikator2` | `verif123` | `verifikator` |
| 🟣 Warkah | `warkah1` | `warkah2` | `warkah123` | `warkah` |
| 🟢 Validator BT | `vbtel1` | `vbtel2` | `vbtel123` | `validator_btel` |
| 🟢 Validator SU | `vsuel1` | `vsuel2` | `vsuel123` | `validator_suel` |
| ⚫ Alih Media BT | `ambt1` | `ambt2` | `ambt123` | `alih_media_btel` |
| ⚫ Alih Media SU | `amsu1` | `amsu2` | `amsu123` | `alih_media_suel` |

> Sumber: `database/seeders/UserSeeder.php` — **total 17 akun aktif** (1 admin + 2 per jabatan non-admin).

---

## 4. Redirect Setelah Login

Sistem mengarahkan pengguna ke **`/dashboard`**. Dashboard (`DashboardController@index`) menampilkan:
- **Kartu statistik:** total tiket, tiket hari ini, tiket selesai, tiket dikembalikan, tiket overdue.
- **Grafik tiket masuk 6 bulan terakhir** dan **rincian overdue per stage**.
- **Tiket terbaru** (8 data sesuai lingkup akun) dan **aktivitas terakhir** (6 peristiwa).

Dari sini pengguna mengakses menu sesuai role (sidebar) dan **Portal Tracking Publik**.

---

## 5. Akses Per Role (Sidebar)

| Role | Menu yang Muncul |
|------|------------------|
| `admin` | Daftar Tiket (Database), Tambah Tiket, Tiket Selesai, Revisi, Arsip Folder, Manajemen Akun |
| `pemimpin` | Dashboard Pemimpin + Monitoring Tiket + Print Monitoring |
| `loket` | Pendaftaran Baru, Daftar Tiket Loket |
| `verifikator` | Verifikasi Berkas |
| `warkah` | Lembar Kerja Warkah |
| `validator_btel` | Validasi Pra-BTel |
| `validator_suel` | Validasi Pra-SuEl |
| `alih_media_btel` | Alih Media BT |
| `alih_media_suel` | Alih Media SU |

> **Dashboard**, **Portal Tracking Publik**, dan **Laporan & Rekap SLA** (index / print / export / print-rapi) tersedia di **semua 9 role**.

---

## 6. Logout

Tombol logout (menu profil di kanan atas) melakukan `POST /logout`: sesi di-invalidate + token di-regenerate → redirect ke `/login` dengan pesan sukses.

---

## 7. Aplikasi SPA `/app`

Selain halaman Blade di atas, tersedia **React SPA** di prefix `/app` (`GET /app/{any?}`) sebagai fallback untuk halaman yang sudah dimigrasikan. Autentikasi tetap berlaku.

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + kesesuaian website riil (rate-limit login, username/email, 2 akun per jabatan).*