# 📊 RINGKASAN EKSEKUTIF — LOKET 2026 v2.3

**Tanggal:** 23 September 2026  
**Versi:** 2.3 (Alur Sekuensial + Paralel Final)  
**Didasarkan pada:** Kode sumber `app/Services/TiketFlowService.php` (source of truth)

---

## 🎯 Alur Sistem (Flowchart)

```
LOKET (1) → DB ADMIN → VERIFIKASI (2) → WARKAH FASE 1 (3a)
                                        Gate: Verifikasi Selesai
                                        ↓
                               (Val BT ║ Val SU) - 4a/4b
                               Gate: Warkah + flag
                                        ↓
                               (AM BT ║ AM SU) - 5a/5b
                               Gate: SEMUA tahap selesai
                                        ↓
                               WARKAH FASE 2 (3b)
                               Gate: AM BT & SU Selesai
                               ↓
                               SELESAI → ARSIP
```

**Ringkasan:**
- Loket → Input manual nomor tiket
- Verifikasi → Periksa berkas (LENGKAP/PERBAIKAN)
- **Warkah (2 fase):** Diserahkn ke Validator → Dikembalikan dari Alih Media → Trigger Selesai
- Validator BT/SU → Paralel, bebas urutan (gate: Warkah selesai + flag)
- Alih Media BT/SU → Paralel (gate AND: semua tahap selesai)

---

## 🔑 Poin Kunci vs Alur Lama

| Aspek | Alur Lama | Alur Baru v2.3 |
|-------|-----------|----------------|
| Verifikasi ║ Warkah | Paralel | **Sekuensial** |
| Validator Gate | Tunggu Verif + Warkah | **Warkah selesai + flag** |
| Warkah | 1 fase | **2 fase** |
| Alih Media Gate | Tunggu Validator | **Gate AND penuh** |
| Trigger Selesai | Alih Media selesai | **Alih Media + Warkah Dikembalikan** |

---

## 📋 STAGE_GATES (Kode Sumber)

```php
'verifikasi'       => [],           // Dari Loket
'warkah'           => ['verifikasi'],  // HARUS tunggu Verifikasi
'validasi_btel'    => ['warkah'],      // HARUS tunggu Warkah
'validasi_suel'    => ['warkah'],      // HARUS tunggu Warkah
'alih_media_btel'  => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
'alih_media_suel'  => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
```

---

## 9 Role

| No | Role | Jabatan |
|----|------|---------|
| 1 | admin | Admin |
| 2 | pemimpin | Pemimpin |
| 3 | loket | Loket Penerimaan |
| 4 | verifikator | Verifikator |
| 5 | warkah | Warkah |
| 6 | validator_btel | Validator BT |
| 7 | validator_suel | Validator SU |
| 8 | alih_media_btel | Alih Media BT |
| 9 | alih_media_suel | Alih Media SU |

---

## 🔍 Pola Universal

**Search → Add → Process → Selesai → Back to Admin**

- Search: Cari di DB Admin (nomor tiket/sertipikat, harus lengkap)
- Add: Klik "Add" → masuk daftar pribadi (Exclusive Claim per jabatan)
- Process: Buka detail → isi status/catatan sesuai bidang
- Done: Klik "Selesai" → kembali ke DB Admin
- Print: Cetak Form Perbaikan jika diperlukan

---

## 📊 Dashboard & Reporting

Tersedia di semua 9 role: Dashboard (statistik + grafik 6 bulan), Tracking Publik (tanpa login), Laporan (Index/Print/Export/Print Rapi)

---

## 🧪 Testing

```bash
php artisan test
php artisan test --filter=DemoAlurRealtimeTest
vendor/bin/pint --dirty --format agent
```

---

## 📁 File Utama

| File | Tujuan |
|------|--------|
| `app/Services/TiketFlowService.php` | Inti alur - STAGE_GATES |
| `app/Models/Tiket.php` | Model + helper method |
| `app/Models/LembarKerjaWarkah.php` | 2 fase Warkah |
| `app/Http/Controllers/StageWorkflow.php` | Base class pola universal |
| `routes/web.php` | Routes 9 role |

**Status:** ✅ Production Ready v2.3

*Document ini adalah sumber kebenaran - didasarkan pada kode sumber.*