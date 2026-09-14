# Checklist Kepatuhan PRD — LOKET 2026 (v2.2)

> **Dokumen kerja Fase 1 (Audit Kepatuhan PRD).** Status diverifikasi terhadap kode sumber pada **14 September 2026**.
> Acuan: `PRD_LOKET_BMN_BALAM.md` §12 (Kriteria Keberhasilan) dan §5.5 (Aturan & Ketentuan Alur Krusial).
> Legenda: ✅ Terpenuhi · ⚠️ Sebagian/perlu perbaikan · ❌ Belum ada.

---

## A. Kriteria Keberhasilan (PRD §12)

| # | Kriteria | Status | Catatan / Bukti |
|---|---|---|---|
| 1 | Admin: Login → Daftar Tiket (Database) → Tambah Tiket → Revisi → Selesai → Arsip → Manajemen Akun → Export | ✅ | Merujuk `AdminController` (index, createTiket, revisi, selesai, arsip*, users*) + `ReportController@export` |
| 2 | Pemimpin: tampilan sama Admin, aksi terkunci, monitoring + print | ✅ | `PemimpinController` + middleware `role:pemimpin`; perlu sentuhan UI pada Fase 2 |
| 3 | Loket: registrasi tiket (nomor manual) → `diterima` di DB Admin → revisi → resubmit | ✅ | `LoketController@store` (kode manual), `resubmit` |
| 4 | Verifikator: smart search → Add → verifikasi; Lengkap (paralel) / Perbaikan → Loket | ✅ | Validasi pada `DemoAlurRealtimeTest` gelombang 1–4 |
| 5 | Warkah: smart search → Add → data pendukung BT & SU → `diserahkan_ke_validator` → selesai (tanpa menunggu Verifikator) | ✅ | Gate validator bertumpu flag `diserahkan_ke_validator` |
| 6 | Validator BTEL/SUEL: search hanya tiket `diserahkan_ke_validator` → Add → validasi → selesai, urutan bebas | ✅ | `TiketFlowService::availableTikets` filter flag |
| 7 | Alih Media: aksi terkunci sampai SEMUA tahap selesai (verifikasi, warkah, validasi BT & SU) | ✅ | Gate AND penuh di `STAGE_GATES`; error message tervalidasi demo |
| 8 | Revisi: kode pembetulan P1..Pn tak terbatas; tiket kembali ke tahap sebelumnya | ✅ | `status_pembetulan` bertipe string; `revisi_ke` increment; revisi eksternal & internal teruji |
| 9 | Export Excel/PDF laporan berfungsi (`/reports/export`, `/reports/print`) | ⚠️ | **Excel ✅** (SpreadsheetMlBuilder). **PDF ❌ belum ada** — hanya print HTML browser. Butuh keputusan solusi (lihat item Fase 3) |
| 10 | Arsip folder berfungsi (buat folder → tambahkan tiket selesai → hilang dari Selesai) | ✅ | `AdminController@arsipIndex/arsipStore`; diuji `AdminSelesaiArsipTest` |
| 11 | Cetak Form Perbaikan di seluruh 6 group stage | ✅ | Route `print-perbaikan` tersedia di 6 grup controller |
| 12 | (PRD §5.5) Export tersedia di semua akun (Daftar, Dashboard, Revisi, Selesai) | ⚠️ | Export laporan global tersedia via `reports/export`; menu shortcut di belum semua halaman daftar/revisi/selesai |

## B. Aturan & Ketentuan Alur Krusial (PRD §5.5)

| # | Aturan | Status | Catatan |
|---|---|---|---|
| 1 | Basis data terpusat — semua tiket masuk Daftar Tiket Admin | ✅ | Semua tiket Loket & Admin tersimpan di tabel `tikets` |
| 2 | Nomor tiket manual, format bebas | ✅ | Form Loket field `kode_tiket` bebas (tidak auto-generate) |
| 3 | Saling terhubung via mesin pencarian nomor tiket | ✅ | Smart Search di 6 group stage |
| 4 | Banyak akun per jabatan; tiap akun hanya lihat tiketnya sendiri | ✅ | Akun #1 & #2 diuji pada gelombang demo 2 |
| 5 | Anti-duplikat (Exclusive Claim per jabatan) | ✅ | Service-level, diuji gelombang 4 (error c) |
| 6 | Paralel Verifikator ║ Warkah | ✅ | Tidak saling menunggu; diuji gelombang 1–5 |
| 7 | Gerbang Validator via `diserahkan_ke_validator` | ✅ | Diuji gelombang 4 (error a) |
| 8 | Gate Alih Media AND penuh | ✅ | Diuji gelombang 4 (error b) |
| 9 | Nama penanggung jawab otomatis & terkunci | ⚠️ | Perlu verifikasi di semua form stage (audit menu Fase 1) |
| 10 | Status batal tidak tampil di pencarian tahapan | ✅ | `whereNotIn('status', ['selesai','batal','dikembalikan'])` |
| 11 | Tiket selesai → Daftar Admin → Arsip | ✅ | AdminSelesaiArsipTest |
| 12 | Export (PDF/Excel) di semua akun | ⚠️ | Sama seperti A.9/A.12 |
| 13 | Detail tiket — nama penanggung jawab hanya di akun penanggung jawabnya | ⚠️ | Perlu audit per view (privasi detail) |
| 14 | Kode revisi P1..Pn tak terbatas | ✅ | String, increment per resubmit |

## C. Ringkasan Status

| Status | Jumlah | Butir |
|---|---|---|
| ✅ Terpenuhi | 19 | A1–A8, A10, A11, B1–B8, B10, B11, B14 |
| ⚠️ Sebagian/perlu audit | 5 | A9, A12, B9, B12, B13 |
| ❌ Belum ada | 0 | — |

## D. Prioritas Perbaikan (untuk Fase 1 & 3)

1. **Export PDF** (A9) — solusi: browser-print vs library (butuh keputusan stakeholder).
2. **Audit privasi detail** (B13) + penanggung jawab terkunci (B9) — periksa semua view stage.
3. **Shortcut export** dari halaman daftar/revisi/selesai (A12/B12).
4. **Print monitoring pimpinan** — konfirmasi halaman cetak dedicated.

---

*Disusun 14 September 2026 — diverifikasi dari kode sumber; detail teknis di `Template_Panduan/LAPORAN_DEMO_ALUR_KONDISI_REALTIME.md`.*