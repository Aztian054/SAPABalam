# 📚 Panduan Alur Sistem LOKET 2026

Folder ini berisi dokumentasi **alur dan fungsi setiap menu** sistem LOKET 2026, dikelompokkan per role/jabatan — sesuai **ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx** (Evaluasi Alur Final — 9 Role / 8 Stage) + **konfigurasi alur paralel (13 Sep 2026):** Verifikator ║ Warkah; Validator via `diserahkan_ke_validator`; Alih Media gate AND penuh.

---

## Daftar Dokumen

| No | File | Isi |
|----|------|-----|
| 0 | [00-Alur-Lengkap-Sistem.md](00-Alur-Lengkap-Sistem.md) | Alur lengkap 8 stage (paralel), diagram, status, aturan krusial |
| 1 | [01-Landing-Page-dan-Login.md](01-Landing-Page-dan-Login.md) | Flow web → login → dashboard, akun quick-login 9 role |
| 2 | [02-Akun-Loket.md](02-Akun-Loket.md) | Stage 1: Pendaftaran / registrasi tiket |
| 3 | [03-Akun-Verifikator.md](03-Akun-Verifikator.md) | Stage 2: Verifikasi berkas asli |
| 4 | [04-Akun-Warkah.md](04-Akun-Warkah.md) | Stage 3: Siapkan data pendukung BT & SU |
| 5 | [05-Akun-Validator.md](05-Akun-Validator.md) | Stage 4A/4B: Validasi Pra-BTel & Pra-SuEl |
| 6 | [06-Akun-Alih-Media.md](06-Akun-Alih-Media.md) | Stage 5A/5B: Alih media (BTel & SuEl) |
| 7 | [07-Akun-Selesai-dan-Arsip.md](07-Akun-Selesai-dan-Arsip.md) | Menu Selesai & Arsip folder |
| 8 | [08-Akun-Admin.md](08-Akun-Admin.md) | Akses penuh + administrasi |
| 9 | [09-Akun-Pimpinan.md](09-Akun-Pimpinan.md) | Monitoring read-only |
| 10 | [10-Tracking-Publik.md](10-Tracking-Publik.md) | Portal tracking tanpa login |
| 11 | [11-Menu-Proses-Monitor-Global.md](11-Menu-Proses-Monitor-Global.md) | Database Tiket Admin (monitoring global) |

---

## Ringkasan Alur (Sekilas)

```
LOKET ──▶ DB ADMIN ──▶ (VERIFIKATOR ║ WARKAH) ──▶ (VALIDATOR BT ║ VALIDATOR SU) ──▶ (ALIH MEDIA BT ║ ALIH MEDIA SU) ──▶ SELESAI ──▶ ARSIP
```

- **Loket** = titik masuk (entry point) — registrasi tiket (**nomor tiket manual**, format bebas), tiket langsung masuk Database Admin.
- **Verifikator ║ Warkah** berjalan **paralel** sejak tiket di DB Admin.
- **Validator BT & SU** mulai saat Warkah menandai **`diserahkan_ke_validator`** — tanpa menunggu Verifikator.
- **BT/SU** dipisah **hanya** di Validator (Stage 4) dan Alih Media (Stage 5).
- **Gate**: Alih Media terbuka hanya jika **SEMUA tahap selesai** (AND penuh); kedua sub-bidang Alih Media selesai sebelum `status='selesai'`.
- **Banyak akun per jabatan** dengan **Exclusive Claim** (tiket yang sudah di-Add satu akun tidak bisa di-Add akun lain di jabatan yang sama).
- Semua akun stage memakai pola **Search → Add → Process → Selesai → Back to Admin**.
- Menu **Selesai** menampilkan tiket `selesai`; Admin mengarsipkan ke **Arsip Folder** dari sini (*Buat Folder → Tambahkan Tiket*).

---

## Bacaan Per Role

- **Admin**: [08](08-Akun-Admin.md) · [11](11-Menu-Proses-Monitor-Global.md) · [07](07-Akun-Selesai-dan-Arsip.md)
- **Pimpinan**: [09](09-Akun-Pimpinan.md)
- **Loket**: [02](02-Akun-Loket.md)
- **Verifikator**: [03](03-Akun-Verifikator.md)
- **Warkah**: [04](04-Akun-Warkah.md)
- **Validator**: [05](05-Akun-Validator.md)
- **Alih Media**: [06](06-Akun-Alih-Media.md)

---

*Dokumen dibuat berdasarkan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` dan kode sumber LOKET 2026 — diperbarui 13 September 2026 (konfigurasi alur paralel).*