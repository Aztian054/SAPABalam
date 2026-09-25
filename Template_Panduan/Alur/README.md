# 📚 Panduan Alur Sistem LOKET 2026

Folder ini berisi dokumentasi **alur dan fungsi setiap menu** sistem LOKET 2026, dikelompokkan per role/jabatan — sesuai **ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx** (Evaluasi Alur Final — 9 Role / 8 Stage) + **konfigurasi alur sekuensial + paralel (17 Sep 2026):** Verifikasi → Warkah Diserahkan (sekuensial); Validator BT/SU (paralel, gate via `diserahkan_ke_validator`); Alih Media (paralel, gate AND penuh); Warkah Dikembalikan (trigger Selesai).

---

## Daftar Dokumen

| No | File | Isi |
|----|------|-----|
| 0 | [00-Alur-Lengkap-Sistem.md](00-Alur-Lengkap-Sistem.md) | Alur lengkap 8 stage (sekuensial + paralel), diagram, status, aturan krusial |
| 1 | [01-Landing-Page-dan-Login.md](01-Landing-Page-dan-Login.md) | Flow web → login (username/email, rate-limit 6/menit) → dashboard, akun quick-login 17 akun / 9 role |
| 2 | [02-Akun-Loket.md](02-Akun-Loket.md) | Stage 1: Pendaftaran / registrasi tiket baru (nomor manual) |
| 3 | [03-Akun-Verifikator.md](03-Akun-Verifikator.md) | Stage 2: Verifikasi berkas asli (GATE: Loket → Verifikasi → Warkah sekuensial) |
| 4 | [04-Akun-Warkah.md](04-Akun-Warkah.md) | Stage 3: 2 fase - Diserahkan (siapkan BT/SU, serahkan ke Validator) & Dikembalikan (terima kembali setelah Alih Media) |
| 5 | [05-Akun-Validator.md](05-Akun-Validator.md) | Stage 4A/4B: Validasi Pra-BTel & Pra-SuEl (GATE: Warkah diserahkan + flag `diserahkan_ke_validator`) |
| 6 | [06-Akun-Alih-Media.md](06-Akun-Alih-Media.md) | Stage 5A/5B: Alih media (BTel & SuEl) (GATE AND: semua tahap selesai + berkas dikirim) |
| 7 | [07-Akun-Selesai-dan-Arsip.md](07-Akun-Selesai-dan-Arsip.md) | Menu Selesai & Arsip folder (TRIGGER: AM BT & AM SU selesai + Warkah dikembalikan) |
| 8 | [08-Akun-Admin.md](08-Akun-Admin.md) | Akses penuh + administrasi tiket, akun, laporan |
| 9 | [09-Akun-Pimpinan.md](09-Akun-Pimpinan.md) | Monitoring read-only (semua aksi form terkunci) |
| 10 | [10-Tracking-Publik.md](10-Tracking-Publik.md) | Portal tracking tanpa login (by tiket atau telepon) |
| 11 | [11-Menu-Proses-Monitor-Global.md](11-Menu-Proses-Monitor-Global.md) | Database Tiket Admin (monitoring global + filter) |

---

## Ringkasan Alur (Sekilas)

```
LOKET → DB ADMIN → VERIFIKASI → WARKAH (Diserahkan)
                         ↓
                    (VALIDATOR BT ║ VALIDATOR SU) ║
                         ↓
                    (ALIH MEDIA BT ║ ALIH MEDIA SU) → WARKAH (Dikembalikan) → SELESAI → ARSIP
```

- **Loket** = titik masuk (entry point) — registrasi tiket (**nomor tiket manual**, format bebas), tiket langsung masuk Database Admin.
- **Verifikasi → Warkah** berjalan **sekuensial** (Verifikasi HARUS selesai dulu sebelum Warkah mulai).
- **Warkah memiliki 2 fase:**
  - Fase 1: Penyerahan — siapkan & serahkan BT/SU ke Validator (gate: Verifikasi selesai)
  - Fase 2: Pengembalian — terima kembali dari Alih Media (trigger: Selesai total)
- **Validator BT & SU** berjalan **paralel** saat Warkah fase 1 selesai + flag `diserahkan_ke_validator` aktif — **tanpa menunggu Verifikasi**.
- **BT/SU** dipisah **hanya** di Validator (Stage 4) dan Alih Media (Stage 5).
- **Gate Alih Media**: terbuka hanya jika **SEMUA tahap selesai** (Verifikasi + Warkah fase 1 + Val BT + Val SU) dan berkas dikirim; kedua sub-bidang (BT & SU) selesai sebelum Selesai total.
- **Banyak akun per jabatan** dengan **Exclusive Claim** (tiket yang sudah di-Add satu akun tidak bisa di-Add akun lain di jabatan yang sama).
- Semua akun stage memakai pola **Search → Add → Process → Selesai → Back to Admin**.
- Menu **Selesai** menampilkan tiket `selesai`; Admin mengarsipkan ke **Arsip Folder** dari sini (Buat Folder → Tambahkan Tiket, single/massal).
- **Dashboard**, **Portal Tracking Publik**, dan **Laporan & Rekap SLA** (index/print/export/print-rapi) tersedia di semua 9 role.

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

*Dokumen dibuat berdasarkan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` dan kode sumber LOKET 2026 — diperbarui 22 September 2026 (konfigurasi alur sekuensial + paralel v2.3).*