# LAPORAN DEMO ALUR & KONDISI REALTIME — LOKET 2026

> **Topik:** Uji end-to-end seluruh alur tiket LOKET 2026 melalui **HTTP route asli** (bukan panggilan service langsung)
> **File test:** `tests/Feature/DemoAlurRealtimeTest.php`
> **Tanggal:** 13 September 2026 — **Hasil: 5/5 test lulus (160 asersi)**
> **Konfigurasi yang diuji:** Alur paralel (Verifikator ║ Warkah), gate Validator via `diserahkan_ke_validator`, gate Alih Media AND penuh, nomor tiket manual, banyak akun per jabatan.

---

## 1. Ringkasan Demo

Demo ini menjalankan skenario **realtime** persis seperti yang dilakukan petugas lewat browser: mengirim HTTP request ke route yang sama, sehingga middleware otorisasi role, validasi form, controller, view, dan transaksi database benar-benar dieksekusi.

| # | Skenario | Tiket | Akun | Status Akhir | Hasil |
|---|---|---|---|---|---|
| 1 | Happy path akun #1 — 6 tahap paralel selesai | `DEMO/G1/T1` | Set `akun #1` | SELESAI (Sertifikat El. Terbit) | ✅ |
| 2 | Happy path akun #2 — bukti banyak akun per jabatan | `DEMO/G2/T2` | Set `akun #2` | SELESAI | ✅ |
| 3 | Revisi eksternal P1 — Verifikator → Loket → Resubmit | `DEMO/G3/T3` | akun #2 | SELESAI, `revisi_ke=1`, `P1` | ✅ |
| 4 | Revisi internal — Validator BT → Warkah + **3 kondisi error** | `DEMO/G4/T4` | campuran | SELESAI, `revisi_ke=1`, `P0` | ✅ |
| 5 | Visibilitas catatan, privasi antrian, timeline, tracking publik | `DEMO/V6/T6`, `DEMO/V7/T7` | 2 Loket + semua jabatan + Admin + publik | SELESAI (T6) | ✅ |

---

## 2. Matriks FSM — Status Tiket & Transisi

### 2.1 Status Tiket

| Status | Badge | Makna |
|---|---|---|
| `diterima` | warning | Baru diregistrasi di Loket; menunggu sortir dari DB Admin |
| `verifikasi` | info | Sedang diperiksa Verifikator (penugasan aktif) |
| `warkah` | secondary | Sedang disiapkan data warkah |
| `validasi_btel` | primary | Sedang divalidasi Validator Pra-BTel |
| `validasi_suel` | purple | Sedang divalidasi Validator Pra-SuEl |
| `alih_media_btel` | dark | Sedang alih media Pra-BTel |
| `alih_media_suel` | indigo | Sedang alih media Pra-SuEl |
| `dikembalikan` | danger | Dikembalikan untuk perbaikan (revisi) |
| `batal` | danger | Dibatalkan |
| `selesai` | success | Semua tahap selesai; sertifikat elektronik terbit |

### 2.2 Matriks Transisi

| Dari | Ke | Pemicu | Rule |
|---|---|---|---|
| `diterima` | `verifikasi` | Verifikator **Add** dari DB Admin | Gate: `diterima` (belum pernah diverifikasi) |
| `diterima` | `warkah` | Warkah **Add** dari DB Admin | Paralel dengan Verifikator — tidak saling menunggu |
| `verifikasi` | `dikembalikan` | Verifikator **Revisi** (`isi_revisi ≥ 3`, `ke_stage=loket`) | Catatan revisi tercatat; `revisi_ke +1`; tiket ke Loket |
| `dikembalikan` | `verifikasi` | Loket **Resubmit** (perbaikan diterima) | `status_pembetulan` naik `P0→P1`; catatan revisi ditandai `sudah_diproses` |
| `verifikasi` | `diterima` | Verifikator **Selesai** | Kembali ke DB Admin |
| `warkah` | `dikembalikan` | Warkah / Validator BT **Revisi internal** (`ke_stage=warkah`) | Revisi internal TIDAK menaikkan P-code (hanya `revisi_ke`) |
| `warkah` | `diterima` | Warkah **Selesai** | Kembali ke DB Admin |
| `diterima` | `validasi_btel` | Validator BT **Add** | Gate: `diserahkan_ke_validator = true` (dari Warkah) |
| `diterima` | `validasi_suel` | Validator SU **Add** | Gate: `diserahkan_ke_validator = true` |
| `validasi_btel` | `diterima` | Validator BT **Selesai** (`status_pra_btel = selesai`) | Kembali ke DB Admin |
| `validasi_suel` | `diterima` | Validator SU **Selesai** (`status_pra_suel = selesai`) | Kembali ke DB Admin |
| `diterima` | `alih_media_btel` | Alih Media BT **Add** | Gate AND penuh: verifikasi ║ warkah ║ validasi BT ║ validasi SU semuanya `selesai` |
| `diterima` | `alih_media_suel` | Alih Media SU **Add** | Gate AND penuh (sama) |
| `alih_media_btel` | `selesai` | Alih Media BT **Selesai** + Alih Media SU sudah selesai | Yang terakhir selesai memicu `status = selesai`, `tanggal_selesai` terisi |
| `alih_media_suel` | `selesai` | Alih Media SU **Selesai** + Alih Media BT sudah selesai | Sama |
| `verifikasi` | `batal` | Admin menandai batal | Tiket tidak muncul di tahap selanjutnya |

> **Perhatikan:** status tiket mengikuti **tahap aktif terakhir**. Saat beberapa tahap paralel aktif, penyelesaian satu cabang **tidak menimpa** status cabang yang masih dikerjakan (logika `activeStageStatus`).
---

## 3. Walkthrough per Tiket

### 3.1 `DEMO/G1/T1` — Happy Path akun #1

Urutan HTTP route yang dieksekusi (persis aksi petugas):

| No | Role | Aksi | HTTP Request |
|---|---|---|---|
| 1 | Loket #1 | Registrasi tiket | `POST /loket` (`loket.store`) |
| 2 | Verifikator #1 | Ambil tiket | `POST /verifikator/add/1` |
| 3 | Warkah #1 | Ambil tiket (paralel) | `POST /warkah/add/1` |
| 4 | Warkah #1 | Simpan lembar kerja + tandai **DISERAHKAN** | `POST /warkah/1/simpan` |
| 5 | Warkah #1 | Selesai tahap | `POST /warkah/1/selesai` |
| 6 | Verifikator #1 | Simpan hasil verifikasi `lengkap` | `POST /verifikator/1/simpan` |
| 7 | Verifikator #1 | Selesai tahap | `POST /verifikator/1/selesai` |
| 8 | Validator BT #1 | Ambil tiket | `POST /validator-bt/add/1` |
| 9 | Validator SU #1 | Ambil tiket (paralel) | `POST /validator-su/add/1` |
| 10 | Validator BT #1 | Simpan `lulus` → Selesai | `POST /validator-bt/1/simpan`, `POST /validator-bt/1/selesai` |
| 11 | Validator SU #1 | Simpan `lulus` → Selesai | `POST /validator-su/1/simpan`, `POST /validator-su/1/selesai` |
| 12 | Alih Media BT #1 | Ambil tiket (gate AND penuh terbuka) | `POST /alih-media-bt/add/1` |
| 13 | Alih Media SU #1 | Ambil tiket | `POST /alih-media-su/add/1` |
| 14 | Alih Media BT #1 | Simpan hasil → Selesai | `POST /alih-media-bt/1/simpan`, `POST /alih-media-bt/1/selesai` |
| 15 | Alih Media SU #1 | Simpan hasil → Selesai → **status SELESAI** | `POST /alih-media-su/1/simpan`, `POST /alih-media-su/1/selesai` |

**Asersi DB:**
- 6 penugasan `status=selesai` (verifikasi, warkah, validasi BT/SU, alih media BT/SU)
- `status_pembetulan = P0`, `revisi_ke = 0`
- `tanggal_selesai` terisi
- **14 baris `riwayat_statuses`** (1 registrasi + 6 Add + 6 Selesai + 1 serah Warkah→Validator)

### 3.2 `DEMO/G2/T2` — Happy Path akun #2

Skenario yang **sama persis** dijalankan dengan set akun kedua (`Loket 2`, `Verifikator 2`, …, `Alih Media 2`) untuk membuktikan:
- Setiap jabatan mendukung banyak akun;
- Satu tiket dikerjakan utuh oleh satu set akun tanpa tabrakan antar akun pada jabatan yang sama (Exclusive Claim);
- Asersi memeriksa `penugasan.user_id` = akun #2 pada Verifikator & Alih Media SU.

### 3.3 `DEMO/G3/T3` — Revisi Eksternal P1 (Verifikator → Loket → Resubmit)

| No | Role | Aksi | HTTP Request |
|---|---|---|---|
| 1 | Loket #2 | Registrasi | `POST /loket` |
| 2 | Verifikator #1 | Add → simpan `status_verifikasi=perbaikan` | `POST /verifikator/add/{id}`, `POST /verifikator/{id}/simpan` |
| 3 | Verifikator #1 | Kembalikan ke Loket | `POST /verifikator/{id}/revisi` (`isi_revisi`, `ke_stage=loket`) |
| 4 | Loket #2 | Lihat alert revisi + isi catatan | `GET /loket/{id}` → `assertSee('Berkas Dikembalikan untuk Perbaikan Revisi')` |
| 5 | Admin | Lihat menu Revisi | `GET /admin/revisi` → `assertSee('DEMO/G3/T3')` |
| 6 | Loket #2 | Terima perbaikan pemohon (resubmit) | `POST /loket/{id}/resubmit` |
| 7 | Verifikator #1 | Add ulang → simpan `lengkap` → Selesai | routes verifikator |
| 8–14 | Warkah, Validator BT/SU, Alih Media BT/SU | Lanjut hingga SELESAI | routes tahap |

**Asersi kunci:**
- `catatan_revisi`: `dari_stage=verifikasi`, `ke_stage=loket`, `revisi_ke=1`, `sudah_diproses=false` saat kembali
- Setelah resubmit: `status=verifikasi`, `status_pembetulan=P1`, `sudah_diproses=true`
- Akhir: `status=selesai`, `revisi_ke=1`, `P1`
### 3.4 `DEMO/G4/T4` — Revisi Internal + 3 Kondisi Error

**Sebelum pekerjaan mulai** diverifikasi 3 kondisi error (lihat §6 untuk pesan persis):

| Kode | Error | Waktu | Aturan yang Dilanggar |
|---|---|---|---|
| **a** | Validator BT claim prematur | Sebelum Warkah menandai diserahkan | Gate Validator (`diserahkan_ke_validator`) |
| **b** | Alih Media BT Add terkunci | Belum ada tahap selesai | Gate Alih Media AND penuh |
| **c** | Verifikator #2 Add diblokir | Verifikator #1 sudah Add | Anti-duplikat (Exclusive Claim) |

Kemudian alur normal berjalan sampai Validator BT, lalu **revisi internal**:

| No | Role | Aksi | HTTP |
|---|---|---|---|
| 1 | Validator BT #1 | Simpan `perlu_koreksi` + kembalikan ke Warkah | `POST /validator-bt/{id}/simpan`, `POST /validator-bt/{id}/revisi` (`ke_stage=warkah`) |
| 2 | Warkah #1 | Lihat menu **Revisi Menunggu Saya** | `GET /warkah` → `assertSee('Revisi Menunggu Saya')` |
| 3 | Warkah #1 | Add ulang (dibuka revisi pending) → perbaiki → Selesai | routes warkah |
| 4 | Validator BT #1 | Add ulang → `lulus` → Selesai | routes validator BT |
| 5 | Validator SU, Alih Media BT/SU | Lanjut hingga SELESAI | routes tahap |

**Asersi kunci:** status `selesai`, `revisi_ke=1`, **`status_pembetulan=P0`** — P-code **tidak naik** pada revisi internal, hanya naik pada resubmit Loket (kode combine publik P0..Pn).

### 3.5 `DEMO/V6/T6` & `DEMO/V7/T7` — Visibilitas & Tracking

T6 didaftarkan **Loket #1**, T7 oleh **Loket #2**:

| No | Aksi | HTTP | Asersi |
|---|---|---|---|
| 1 | `GET /loket` sebagai Loket #1 | — | lihat T6, **tidak** lihat T7 |
| 2 | `GET /loket` sebagai Loket #2 | — | lihat T7, **tidak** lihat T6 |
| 3 | Warkah #1 simpan lembar kerja + catatan rahasia `CATATAN_RAHASIA_WARKAH_12345` | `POST /warkah/{id}/simpan` | — |
| 4 | Warkah #1 lihat detail | `GET /warkah/{id}` | **melihat** catatan rahasia |
| 5 | Verifikator #1 lihat detail | `GET /verifikator/{id}` | **tidak** melihat catatan rahasia |
| 6 | Verifikator #1 lihat timeline | `GET /verifikator/{id}` | melihat riwayat **global** `Warkah menyerahkan sertipikat ke Validator` |
| 7 | Verifikator #1 selesai dengan `CATATAN_FINAL_VERIFIKATOR_999` | `POST /verifikator/{id}/selesai` | — |
| 8 | Admin lihat detail | `GET /admin/{id}` | **melihat** catatan final penugasan |
| 9 | T6 diteruskan sampai SELESAI | routes tahap | `status=selesai` |
| 10 | Publik (tanpa login) buka tracking | `GET /tracking/{kode}` | melihat kode, `Selesai (Sertifikat El. Terbit)`, riwayat `Berkas terdaftar di Loket` & `Warkah menyerahkan sertipikat ke Validator` |

---

## 4. Matriks Visibilitas Catatan & Informasi per Role

| Informasi | Loket | Verifikator | Warkah | Validator BT/SU | Alih Media BT/SU | Admin | Pemimpin | Publik (Tracking) |
|---|---|---|---|---|---|---|---|---|
| Tiket milik akun sendiri (antrian Loket) | ✅ | — | — | — | — | semua | semua | — |
| Catatan lembar kerja tahap (mis. `catatan` lembar Warkah) | ❌ | ❌ | ✅ (hanya tahap sendiri) | ❌ | ❌ | ✅ (lembar kerja) | view | ❌ |
| Catatan riskan suatu tahap lihat akun tahap lain | ❌ | ❌ (tidak lihat catatan warkah) | ❌ | ❌ | ❌ | ✅ | view | ❌ |
| **Timeline `riwayat_statuses` (global)** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (riwayat perjalanan) |
| Catatan final penugasan (`tiket_penugasan.catatan`) | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ (Detail Admin) | view | ❌ |
| Isi catatan revisi (`catatan_revisi.isi_revisi`) | ✅ (alert merah) | ✅ (akun pengirim) | ✅ (Revisi Menunggu Saya) | ✅ (akun pengirim) | ✅ | ✅ (menu Revisi) | view | ❌ |
| Kode revisi P1..Pn | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (badge pembetulan) |
| Nama penanggung jawab tahap | pada penugasan miliknya | pada penugasan miliknya | pada penugasan miliknya | pada penugasan miliknya | pada penugasan miliknya | ✅ semua | view | ❌ |

> **Aturan tabur:** catatan kerja tahap bersifat **privat per akun/tahap**; timeline dan status **global**; catatan final terlihat **hanya di Detail Admin**; isi revisi terlihat oleh **pengirim, penerima, dan Admin**.
---

## 5. Output Timeline (`riwayat_statuses`) — Happy Path `DEMO/G1/T1`

Urutan riwayat yang dihasilkan (14 baris, persis urutan eksekusi):

| # | stage_dari | stage_ke | Keterangan (ringkas) |
|---|---|---|---|
| 1 | Pendaftaran | DB Admin | `Berkas terdaftar di Loket. Tiket masuk Database Admin.` |
| 2 | DB Admin | verifikasi | `Tiket di-Add oleh {Verifikator} (tahap verifikasi).` |
| 3 | DB Admin | warkah | `Tiket di-Add oleh {Warkah} (tahap warkah).` |
| 4 | Warkah | Validator (diserahkan_ke_validator) | `Warkah menyerahkan sertipikat ke Validator (status DISERAHKAN). Validator BT/SU kini dapat mulai secara paralel.` |
| 5 | warkah | DB Admin | `Tiket … selesai pada tahap warkah.` |
| 6 | verifikasi | DB Admin | `Tiket … selesai pada tahap verifikasi.` |
| 7 | DB Admin | validasi_btel | `Tiket di-Add oleh {Validator BT}` |
| 8 | DB Admin | validasi_suel | `Tiket di-Add oleh {Validator SU}` |
| 9 | validasi_btel | DB Admin | `Tiket … selesai pada tahap validasi_btel.` |
| 10 | validasi_suel | DB Admin | `Tiket … selesai pada tahap validasi_suel.` |
| 11 | DB Admin | alih_media_btel | `Tiket di-Add oleh {Alih Media BT}` |
| 12 | DB Admin | alih_media_suel | `Tiket di-Add oleh {Alih Media SU}` |
| 13 | alih_media_btel | DB Admin | `Tiket … selesai pada tahap alih_media_btel.` |
| 14 | alih_media_suel | selesai | `Tiket … selesai pada tahap alih_media_suel.` (yang terakhir memicu status **selesai**) |

> Public Tracking menampilkan daftar ini (dengan nama pelaku versi ringkas di tiap baris) di kartu **"Riwayat Perjalanan Berkas"**.

---

## 6. Kondisi Error & Pesan Yang Diuji (dari controller/route asli)

Semua pesan berikut di-assert **persis** di test (`assertSessionHas('error', …)`) pada `DemoAlurRealtimeTest#test_gelombang_4_…`:

| Kode | Skenario | Pesan error persis |
|---|---|---|
| a | Validator BT claim sebelum `diserahkan_ke_validator` | `Tiket DEMO/G4/T4 belum diserahkan oleh Warkah ke Validator (diserahkan_ke_validator belum aktif); belum dapat diproses di tahap validasi_btel.` |
| b | Alih Media Add saat gate belum penuh | `Tiket DEMO/G4/T4 belum selesai pada tahap verifikasi; belum dapat diproses di tahap alih_media_btel.` |
| c | Anti-duplikat — Add kedua pada jabatan sama | `Tiket DEMO/G4/T4 sedang diproses akun lain pada tahap ini.` |

Catatan penting yang tervalidasi oleh demo:
- Pesan (a) hanya muncul untuk tahap Validator **karena Warkah belum menandai** diserahkan.
- Pesan (b) selalu menyebut **tahap prasyarat pertama yang belum selesai** (urutan gate: verifikasi → warkah → validasi_btel → validasi_suel).
- Pesan (c) membuktikan **Exclusive Ticket Claiming** per jabatan berjalan di level service (bukan hanya di tampilan penetapan).

---

## 7. Verifikasi Database

Jalankan untuk memverifikasi hasil secara manual (SQLite lokal):

```sql
-- 1) Semua tiket demo + status akhir
SELECT kode_tiket, status, status_pembetulan, revisi_ke, tanggal_selesai
FROM tikets
WHERE kode_tiket LIKE 'DEMO/%'
ORDER BY id;

-- 2) Penugasan selesai per tiket (harus 6 untuk tiket happy path / selesai)
SELECT t.kode_tiket, COUNT(*) AS penugasan_selesai
FROM tiket_penugasan tp
JOIN tikets t ON t.id = tp.tiket_id
WHERE tp.status = 'selesai'
GROUP BY t.kode_tiket
ORDER BY t.kode_tiket;

-- 3) Riwayat timeline per tiket (urutan perjalanan)
SELECT t.kode_tiket, r.stage_dari, r.stage_ke, r.keterangan, r.created_at
FROM riwayat_statuses r
JOIN tikets t ON t.id = r.tiket_id
WHERE t.kode_tiket IN ('DEMO/G1/T1','DEMO/V6/T6')
ORDER BY t.id, r.id;

-- 4) Catatan revisi (T3 eksternal → loket; T4 internal → warkah)
SELECT t.kode_tiket, cr.dari_stage, cr.ke_stage, cr.revisi_ke, cr.sudah_diproses, cr.isi_revisi
FROM catatan_revisi cr
JOIN tikets t ON t.id = cr.tiket_id
WHERE t.kode_tiket IN ('DEMO/G3/T3','DEMO/G4/T4')
ORDER BY t.id, cr.id;

-- 5) Privasi antrian Loket (created_by per tiket)
SELECT kode_tiket, created_by, petugas_loket_id FROM tikets WHERE kode_tiket LIKE 'DEMO/V%';
```

> **Catatan hasil demo:** seluruh tiket demo dibuat dengan `jenis_permohonan_id` dari helper test (`jenis`), bukan melalui seeder — jadi isolasi antar gelombang dijamin oleh `RefreshDatabase`.
---

## 8. Cara Menjalankan Demo

```bash
# Dari root proyek LOKET2026
php artisan test --filter=DemoAlurRealtimeTest
```

Dibutuhkan: database test SQLite in-memory (otomatis di-set oleh `phpunit.xml`), jadi tidak perlu mengubah data database produksi.

Hasil terakhir yang diverifikasi:

```
Tests: 5 passed (160 assertions)
```

---

## 9. Temuan yang Diperbaiki Selama Penyusunan Demo

Penyusunan demo end-to-end ini menemukan dan memperbaiki **1 kerentanan kecil di controller**:

- **`LoketController@store` (temuan):** membaca kolom opsional (`nik_pemohon`, `satuan_kerja`, `no_hak_sekarang`, `no_hak_sebelumnya`, `kelurahan_desa`, `kecamatan`, `keterangan`) langsung dari `$validated[...]` tanpa fallback `?? null`. Bila petugas mengirim form yang mengosongkan kolom opsional (input tidak terkirim), controller melempar `Undefined array key` → HTTP 500 meskipun validasi lulus.
- **Perbaikan:** semua pembacaan kolom opsional kini memakai `$validated[...] ?? null` (lihat `app/Http/Controllers/LoketController.php`).

Fix ini **tidak mengubah perilaku** bila seluruh field dikirim (kondisi normal dari form browser), tetapi membuat route abadi (idempoten) terhadap payload yang tidak lengkap — penting untuk integrasi API/ekspor.

---

*Laporan demo alur & kondisi realtime — diselaraskan dengan `00-Alur-Lengkap-Sistem.md` (alur paralel) dan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx`.*