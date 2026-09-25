# ALUR BARU LOKET 2026 (17 September 2026)

## 📝 Revisi dari Alur Sebelumnya

### Alur Lama (Paralel Awal):
```
Diterima → (Verifikasi ∥ Warkah) → (Val BT ∥ Val SU) → (AM BT ∥ AM SU) → Selesai
```

### Alur Baru (Sekuensial + Paralel - Final):
```
Diterima → Verifikasi → Warkah (Diserahkan) → (Val BT ∥ Val SU) → (AM BT ∥ AM SU) → Warkah (Dikembalikan) → Selesai
```

---

## 📋 Detail Perubahan

### 1. Verifikasi → Warkah (Sekuensial)
- ❌ **SEBELUM:** Verifikasi dan Warkah berjalan paralel
- ✅ **SESUDAH:** Warkah HARUS menunggu Verifikasi selesai

**Alasan:** Memperjelas alur dan memastikan berkas sudah terverifikasi sebelum masuk ke Warkah. Dokumen `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL)` menentukan alur ini.

### 2. Warkah Memiliki 2 Fase

**Fase 1: Warkah Diserahka** - Setelah Verifikasi selesai
- Warkah siapkan data BT & SU
- Serahkan berkas ke Validator via route `warkah.kirim`
- Set `diserahkan_ke_validator = true` di tabel tikets
- Status: `status_pengembalian = 'dipinjam'` di LembarKerjaWarkah

**Fase 2: Warkah Dikembalikan** - Setelah AM BT & AM SU selesai
- Warkah terima pengembalian berkas fisik dari Alih Media
- Catat kondisi pengembalian di LembarKerjaWarkah
- Status: `status_pengembalian = 'dikembalikan'`
- **TRIGGER:** Tiket baru bisa berstatus `selesai` setelah fase ini

### 3. Validator BT/SU Gate
- ✅ Validator BT/SU hanya bisa Add tiket setelah:
  1. Verifikasi selesai (melalui STAGE_GATES)
  2. Warkah selesai diserahkan + `diserahkan_ke_validator = true` (melalui assertCanClaim)

### 4. Status Baru: `perbaikan`
- Ditambahkan ke enum status tikets
- `dikembalikan` = berkas dikembalikan untuk perbaikan eksternal (via Loket)
- `perbaikan` = berkas dalam proses perbaikan/revisi internal

---

## 🔧 Detail Teknis

### File yang Diubah:
1. ✅ `app/Services/TiketFlowService.php`
   - Update `STAGE_GATES`: `'warkah' => ['verifikasi']`
   - Update `STAGE_GATES_VISIBLE`: Validator & Alih Media visible gates
   - Update `assertCanClaim()`: validasi gate + flag `diserahkan_ke_validator`
   - Update `resolveStatusAfterDone()`: logika Selesai setelah pengembalian warkah

2. ✅ `app/Models/Tiket.php`
   - Tambah status `'perbaikan'` di `getStatusBadgeAttribute()` → `'warning'`
   - Tambah status `'perbaikan'` di `getStatusLabelAttribute()` → `'Perbaikan (Dalam Proses Revisi)'`
   - Method `isDiserahkanKeValidator()`: cek flag `$this->diserahkan_ke_validator`

3. ✅ `app/Models/LembarKerjaWarkah.php`
   - Konstanta `STATUS_PENGEMBALIAN`: `'belum_diserahkan'`, `'dipinjam'`, `'dikembalikan'`
   - Konstanta `KONDISI_BERKAS`: `'lengkap'`, `'rusak'`, `'kurang'`
   - Fillable baru: `penerima_validator_id`, `nama_penerima_validator`, `waktu_serah`, dll.

4. ✅ `database/migrations/2026_09_17_102638_update_tiket_flow_to_new_stages_2026.php`
   - Tambah enum status `'perbaikan'` ke tabel `tikets`

5. ✅ `database/migrations/2026_09_17_000001_upgrade_warkah_serah_terima_berkas.php`
   - Kolom detail serah terima: `penerima_validator_id`, `waktu_serah`, `status_berkas_bt`, dll.
   - Kolom detail pengembalian: `petugas_pengembali`, `waktu_kembali`, `kondisi_berkas_kembali`, dll.

6. ✅ `README.md`
   - Update diagram alur ke v2.3
   - Update versi ke 2.3

## 📊 Matriks Transisi Status (FSM)

| Dari | Ke | Pemicu | Kondisi |
|------|-----|--------|---------|
| `diterima` | `verifikasi` | Verifikator Add | Tiket baru dari Loket |
| `verifikasi` | `diterima` | Verifikator Selesai | Kembali ke DB Admin, siap Warkah Add |
| `diterima` | `warkah` | Warkah Add | **GATE: Verifikasi harus selesai** |
| `warkah` | `diterima` | Warkah Selesai Diserahkan | Berkas diserahkan ke Validator, kembali ke DB Admin |
| `diterima` | `validasi_btel` | Validator BT Add | **GATE: Warkah harus selesai + diserahkan_ke_validator = true** |
| `diterima` | `validasi_suel` | Validator SU Add | **GATE: Warkah harus selesai + diserahkan_ke_validator = true** |
| `validasi_btel` | `diterima` | Validator BT Selesai | status_pra_btel = 'selesai', kembali ke DB Admin |
| `validasi_suel` | `diterima` | Validator SU Selesai | status_pra_suel = 'selesai', kembali ke DB Admin |
| `diterima` | `alih_media_btel` | Alih Media BT Add | **GATE AND: Verifikasi, Warkah, Val BT, Val SU selesai** |
| `diterima` | `alih_media_suel` | Alih Media SU Add | **GATE AND: Verifikasi, Warkah, Val BT, Val SU selesai** |
| `alih_media_btel` | `diterima` | Alih Media BT Selesai | Kembali ke DB Admin (menunggu AM SU & Warkah Dikembalikan) |
| `alih_media_suel` | `diterima` | Alih Media SU Selesai | Kembali ke DB Admin (menunggu AM BT & Warkah Dikembalikan) |
| `diterima` | `selesai` | Warkah Catat Pengembalian | **TRIGGER: AM BT & AM SU selesai + status_pengembalian = 'dikembalikan'** |

## 🎯 Gate Logic Baru

### STAGE_GATES (Syarat Add):
```php
'verifikasi' => [],                                    // Dari Loket
'warkah' => ['verifikasi'],                           // Tunggu Verifikasi selesai
'validasi_btel' => ['warkah'],                        // Tunggu Warkah selesai
'validasi_suel' => ['warkah'],                        // Tunggu Warkah selesai
'alih_media_btel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
'alih_media_suel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
```

## 🧪 Testing

### Test Case yang Perlu Diupdate:
1. Test Verifikasi selesai → Warkah baru bisa Add
2. Test Warkah belum selesai → Validator tidak bisa Add
3. Test AM BT & AM SU selesai → Warkah harus catat pengembalian
4. Test status `perbaikan` workflow

### Command Testing:
```bash
# Run migration
php artisan migrate

# Test full suite
php artisan test

# Test specific flow
php artisan test --filter=DemoAlurRealtimeTest
```

## 📝 Catatan Penting

1. **Backward Compatibility**: 
   - Flag `diserahkan_ke_validator` tetap dipertahankan untuk kompatibilitas
   - `status_pengembalian` di `LembarKerjaWarkah` sudah ada dan siap digunakan

2. **Data Existing**:
   - Data lama dengan alur paralel akan tetap work
   - Tiket baru akan mengikuti alur sekuensial

3. **UI Update**:
   - Progress bar perlu update untuk show 2 fase Warkah
   - Dashboard perlu update untuk show alur baru

---

**Tanggal Implementasi:** 17 September 2026  
**Status:** ✅ Core Logic & Database Updated  
**Next Steps:** Update UI & Documentation
