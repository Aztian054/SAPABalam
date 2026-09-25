# DAFTAR AKUN LOGIN — LOKET 2026

> **Sumber akun login:** `database/seeders/UserSeeder.php` — di-seed ke database pengembangan (`loket2026`).
> **Sumber akun test:** `tests/Feature/DemoAlurRealtimeTest.php` — hanya dibuat **di memory test**, TIDAK bisa dipakai login manual (lihat perbedaan di §3).
> **Verifikasi:** seluruh password di bawah diuji `Hash::check()` terhadap DB pengembangan — **semua bisa login**.

---

## 1. Cara Cepat Login (Akun Database Pengembangan)

Gunakan username + password di bawah ini untuk **login di aplikasi yang berjalan** (laragon). Semua akun berstatus aktif.

### 1.1 Akun Utama (satu per jabatan)

| Jabatan | Username | Password | Role |
|---|---|---|---|
| 🔴 Admin | `admin` | `admin123` | `admin` |
| 🟡 Pemimpin | `pemimpin` | `pemimpin123` | `pemimpin` |
| 🟠 Loket | `loket1` | `loket123` | `loket` |
| 🔵 Verifikator | `verifikator1` | `verif123` | `verifikator` |
| 🟣 Warkah | `warkah1` | `warkah123` | `warkah` |
| 🟢 Validator BT | `vbtel1` | `vbtel123` | `validator_btel` |
| 🟢 Validator SU | `vsuel1` | `vsuel123` | `validator_suel` |
| ⚫ Alih Media BT | `ambt1` | `ambt123` | `alih_media_btel` |
| ⚫ Alih Media SU | `amsu1` | `amsu123` | `alih_media_suel` |

### 1.2 Akun Kedua per Jabatan (bukti banyak akun per tahap)

| Jabatan | Username | Password |
|---|---|---|
| Pemimpin 2 | `pemimpin2` | `pemimpin123` |
| Loket 2 | `loket2` | `loket123` |
| Verifikator 2 | `verifikator2` | `verif123` |
| Warkah 2 | `warkah2` | `warkah123` |
| Validator BT 2 | `vbtel2` | `vbtel123` |
| Validator SU 2 | `vsuel2` | `vsuel123` |
| Alih Media BT 2 | `ambt2` | `ambt123` |
| Alih Media SU 2 | `amsu2` | `amsu123` |

Jika tabel di atas kosong/tidak bisa login, jalankan seeder:

```bash
php artisan db:seed --class=UserSeeder
```

---

## 2. Akun Test Demo (Referensi Pengujian — BUKAN untuk login)

Dibuat oleh helper `user()` di `DemoAlurRealtimeTest` per eksekusi test di **database test in-memory**
(`RefreshDatabase`). Username-nya mengikuti pola `{role}{nomor}` dan password default factory `password`.

| No | Nama (di test) | Username | Email (test) | Role |
|---|---|---|---|---|
| 1 | Admin 1 | `admin1` | `admin1@loket.test` | `admin` |
| 2 | Loket 1 | `loket1` | `loket1@loket.test` | `loket` |
| 3 | Loket 2 | `loket2` | `loket2@loket.test` | `loket` |
| 4 | Verifikator 1 | `verifikator1` | `verifikator1@loket.test` | `verifikator` |
| 5 | Verifikator 2 | `verifikator2` | `verifikator2@loket.test` | `verifikator` |
| 6 | Warkah 1 | `warkah1` | `warkah1@loket.test` | `warkah` |
| 7 | Warkah 2 | `warkah2` | `warkah2@loket.test` | `warkah` |
| 8 | Validator Btel 1 | `validator_btel1` | `validator_btel1@loket.test` | `validator_btel` |
| 9 | Validator Btel 2 | `validator_btel2` | `validator_btel2@loket.test` | `validator_btel` |
| 10 | Validator Suel 1 | `validator_suel1` | `validator_suel1@loket.test` | `validator_suel` |
| 11 | Validator Suel 2 | `validator_suel2` | `validator_suel2@loket.test` | `validator_suel` |
| 12 | Alih Media Btel 1 | `alih_media_btel1` | `alih_media_btel1@loket.test` | `alih_media_btel` |
| 13 | Alih Media Btel 2 | `alih_media_btel2` | `alih_media_btel2@loket.test` | `alih_media_btel` |
| 14 | Alih Media Suel 1 | `alih_media_suel1` | `alih_media_suel1@loket.test` | `alih_media_suel` |
| 15 | Alih Media Suel 2 | `alih_media_suel2` | `alih_media_suel2@loket.test` | `alih_media_suel` |

### Pemakaian Akun Test per Gelombang

| Gelombang | Test | Akun Test yang Dipakai |
|---|---|---|
| 1 (T1) | `test_gelombang_1_t1_happy_path_akun_1_sampai_selesai` | Loket 1, Verifikator 1, Warkah 1, Validator Btel 1, Validator Suel 1, Alih Media Btel 1, Alih Media Suel 1 |
| 2 (T2) | `test_gelombang_2_t2_happy_path_akun_2_bukti_banyak_akun_per_jabatan` | Loket 2, Verifikator 2, Warkah 2, Validator Btel 2, Validator Suel 2, Alih Media Btel 2, Alih Media Suel 2 |
| 3 (T3) | `test_gelombang_3_t3_revisi_eksternal_verifikator_ke_loket_lalu_resubmit` | Loket 2, Verifikator 1, Warkah 1, Validator Btel 1, Validator Suel 1, Alih Media Btel 1, Alih Media Suel 1, Admin 1 |
| 4 (T4) | `test_gelombang_4_t4_revisi_internal_dan_tiga_kondisi_error` | Loket 1, Verifikator 1 + 2, Warkah 1, Validator Btel 1 + 2, Validator Suel 1, Alih Media Btel 1, Alih Media Suel 1 |
| 5 (T6/T7) | `test_visibilitas_catatan_privasi_antrian_dan_tracking` | Loket 1 + 2, Verifikator 1, Warkah 1, Validator Btel 1, Validator Suel 1, Alih Media Btel 1, Alih Media Suel 1, Admin 1 |

---

## 3. Perbedaan Akun Login vs Akun Test

| | Akun Login (UserSeeder) | Akun Test (DemoAlurRealtimeTest) |
|---|---|---|
| Ada di database pengembangan? | ✅ Ya (tabel `users`) | ❌ Tidak — memory test saja |
| Bisa dipakai login manual? | ✅ Ya | ❌ Tidak (menyebabkan error "Username atau password salah") |
| Password | `admin123` / `loket123` / dst | `password` (semua) |
| Pola username | `admin`, `loket1`, `vbtel1`, `ambt1`, … | `{role}{nomor}` mis. `validator_btel1`, `alih_media_suel1` |
| Cara dibuat | `php artisan db:seed --class=UserSeeder` | `php artisan test --filter=DemoAlurRealtimeTest` |

> ⚠️ **Jangan gunakan akun test untuk login aplikasi** — akun tersebut (mis. `admin1`, `validator_btel1`)
> memang **tidak ada / beda password** di database pengembangan, sehingga login akan gagal.

---

## 4. Cara Login Aplikasi

Sistem menerima **username** maupun **email** saat login (`AuthController@login`, route `POST /login`):

- Contoh: username `admin` + password `admin123`, atau `loket1` + `loket123`.
- Akun harus berstatus **aktif** (`is_active = true`).
- Setelah login, akun diarahkan ke `dashboard` masing-masing role.
- **Rate-limit:** `POST /login` memakai `throttle:6,1` — maksimal **6 percobaan login per menit**. Setelah 6× gagal dalam 1 menit, percobaan berikutnya ditolak sementara (HTTP 429 *Too Many Attempts*); tunggu 1 menit lalu coba lagi.
- Akun non-aktif **tidak dapat login** meskipun username/password benar.

---

## 5. Verifikasi Akun di Database

```sql
-- Cek daftar akun aktif di database pengembangan (MySQL loket2026)
SELECT id, username, email, role, is_active FROM users ORDER BY id;
```

Jalankan melalui:

```php
// php artisan tinker
App\Models\User::orderBy('id')->get()->each(fn ($u) => print($u->id.' | '.$u->username.' | '.$u->role.' | '.($u->is_active ? 'aktif' : 'nonaktif').PHP_EOL));
```