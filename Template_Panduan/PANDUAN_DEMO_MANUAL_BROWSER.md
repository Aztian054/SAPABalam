# PANDUAN DEMO MANUAL (BROWSER) — ALUR REALTIME LOKET 2026

> **Tujuan:** memperagakan seluruh alur tiket (registrasi → 6 tahap → SELESAI → Arsip) di **browser**
> memakai **akun login asli** database pengembangan — bukan akun test.
> Langkah di bawah identik dengan yang diuji otomatis `DemoAlurRealtimeTest` (5/5 lulus, 160 asersi),
> tetapi dijalankan lewat antarmuka web sungguhan.

---

## 0. Prasyarat

1. Aplikasi berjalan: Laragon aktif, atau `php artisan serve` di folder `c:\laragon\www\LOKET2026`.
2. Akun seeder sudah ada di database:

   ```bash
   php artisan db:seed --class=UserSeeder
   ```

3. Buka aplikasi di browser, mis. `http://loket2026.test` atau `http://localhost:8000`.
4. **Rate-limit login:** `POST /login` dibatasi `throttle:6,1` — maks. **6 percobaan per menit**. Setelah 6× gagal dalam satu menit, sistem menolak sementara (HTTP 429). Jika terjebak, tunggu 1 menit lalu ulangi.
5. **Kolom login** menerima **username ATAU email**; akun wajib aktif (`is_active = true`).

### Akun yang dipakai demo ini (semua aktif)

| Tahap | Username | Password |
|---|---|---|
| Loket | `loket1` | `loket123` |
| Verifikator | `verifikator1` | `verif123` |
| Warkah | `warkah1` | `warkah123` |
| Validator BT | `vbtel1` | `vbtel123` |
| Validator SU | `vsuel1` | `vsuel123` |
| Alih Media BT | `ambt1` | `ambt123` |
| Alih Media SU | `amsu1` | `amsu123` |
| Admin (verifikasi akhir) | `admin` | `admin123` |

> **Urutan kerja:** setiap selesai di satu tahap, **logout** lalu login akun tahap berikutnya.

---

## 1. Tahap Loket — Registrasi Tiket

1. Halaman login `GET /login` → isi **username** `loket1`, **password** `loket123` → login.
2. Buka menu **Loket** (`/loket`) → klik **Tambah Tiket** (`/loket/create`).
3. Isi form registrasi:
   | Field | Contoh Isi |
   |---|---|
   | Kode Tiket (manual) | `MANUAL/0001` |
   | Jenis Permohonan | pilih salah satu (mis. "Permohonan Demo") |
   | Nama Pemohon | `Demo Manual` |
   | NIK Pemohon | `1871010101010001` |
   | No. HP Pemohon | `081300001234` |
   | Satuan Kerja | `Kementerian PUPR / Demo` (opsional) |
   | No. Hak Sekarang | `M.1088/NEGERI OLOK` |
   | No. Hak Sebelumnya | (opsional) |
   | Kelurahan/Desa | `Way Laga` |
   | Kecamatan | `Panjang` |
   | Jumlah Bidang | `1` |
   | Keterangan | `Demo manual browser.` |
   | Bidang → NIB | `08.01.01.01.000001` |
   | Bidang → No. Sertifikat Lama | `SHM No. DEMO-0001` |
   | Bidang → Jenis Hak | `HM` |
   | Bidang → Nama Pemegang Hak | `Demo Manual` |
   | Bidang → Luas (m²) | `1250` |
4. Klik **Simpan**. Tiket masuk antrian Loket **dan** DB Admin (status `diterima`).
5. **Logout** (klik nama akun → Logout).

---

## 2. Tahap Verifikator — Pemeriksaan Berkas

> ⚡ **Paralel dengan Warkah** — Verifikator dan Warkah sama-sama bisa mengambil tiket tanpa saling menunggu.

1. Login `verifikator1` / `verif123`.
2. Menu **Verifikator** (`/verifikator`) → ketik `MANUAL/0001` di **Smart Search** → **Add** tiket.
3. Buka tiket (`/verifikator/{id}`) → isi lembar kerja:
   - **Status Verifikasi**: `Lengkap`
   - **Catatan**: `Berkas verifikasi lengkap dan sah.`
4. Klik **Simpan**, lalu **Selesai** (tiket kembali ke DB Admin).
5. **Logout**.

> 💡 **Opsional — loop revisi eksternal:** alih-alih "Selesai", klik **Revisi**, isi `isi_revisi` (min 3 karakter)
> dengan `ke_stage = Loket`. Tiket kembali ke Loket berstatus `dikembalikan`; login `loket1` → menu Loket →
> perbaiki → klik **Resubmit** (`POST /loket/{id}/resubmit`) → tiket kembali ke Verifikator dengan kode pembetulan **P1**.

---

## 3. Tahap Warkah — Persiapan Data BT & SU + Serah ke Validator

1. Login `warkah1` / `warkah123`.
2. Menu **Warkah** (`/warkah`) → Smart Search `MANUAL/0001` → **Add**.
3. Buka tiket (`/warkah/{id}`) → isi lembar warkah:
   | Field | Nilai |
   |---|---|
   | Status Berkas Dataset | `Selesai` |
   | Status Data Sertipikat BT | `Selesai` |
   | Status Data Sertipikat SU | `Selesai` |
   | Status Sosialisasi | `Selesai` |
   | Status Public Response | `Selesai` |
   | Status Sertipikat | **`Diserahkan`** ⚠️ (membuka Validator) |
   | Status Dokumen BT | `Ada` |
   | Status Dokumen SU | `Ada` |
   | Tanggal Diserahkan | hari ini |
   | Jumlah Berkas | `3` |
   | Jumlah Halaman | `12` |
   | Berkas Digabung | `0` |
   | Jumlah Berkas Dikembalikan | `0` |
   | Keterangan Status | `Data warkah lengkap dan siap.` |
4. Klik **Simpan** → lalu **Selesai** dengan catatan `Selesai warkah: data BT & SU diserahkan ke Validator.`
5. **Logout**.

> ⚠️ Sebelum langkah ini, jika ada yang mencoba **Add** di Validator, sistem menolak:
> `Tiket {kode} belum diserahkan oleh Warkah ke Validator (diserahkan_ke_validator belum aktif); belum dapat diproses di tahap validasi_btel.`

---

## 4. Tahap Validator BT & SU — Validasi Paralel

> Keduanya paralel. **Wajib Warkah sudah "Selesai"** (poin §3) — itulah gate `diserahkan_ke_validator`.

### 4a. Validator BT
1. Login `vbtel1` / `vbtel123` → menu `/validator-bt` → Add `MANUAL/0001`.
2. Isi: **Status Validasi**: `Lulus`, **Catatan**: `Validasi BT sesuai.` → **Simpan** → **Selesai**.
3. **Logout**.

### 4b. Validator SU
1. Login `vsuel1` / `vsuel123` → menu `/validator-su` → Add `MANUAL/0001`.
2. Isi: **Status Validasi**: `Lulus`, **Catatan**: `Validasi SU sesuai.` → **Simpan** → **Selesai**.
3. **Logout**.

> 💡 **Opsional — loop revisi internal:** Validator bisa mengembalikan ke **Warkah** (bukan Loket):
> klik **Revisi** dengan `ke_stage = Warkah`. Warkah melihat notifikasi **"Revisi Menunggu Saya"**,
> memperbaiki, lalu Selesai. Catatan: revisi internal menaikkan `revisi_ke` tetapi **tidak** menaikkan
> kode pembetulan (P0 tetap P0) — beda dengan revisi eksternal lewat Loket.

---

## 5. Tahap Alih Media BT & SU — Penerbitan Sertifikat Elektronik

> ⚠️ **Gate AND penuh:** tahap ini baru bisa **Add** setelah **semua** tahap sebelumnya selesai
> (verifikasi ✅ warkah ✅ validasi BT ✅ validasi SU ✅). Jika belum, sistem menolak:
> `Tiket {kode} belum selesai pada tahap verifikasi; belum dapat diproses di tahap alih_media_btel.`

### 5a. Alih Media BT
1. Login `ambt1` / `ambt123` → menu `/alih-media-bt` → Add `MANUAL/0001`.
2. Isi lembar kerja:
   - Scan Buku Tanah: `Sudah`
   - Upload KKP: `Sudah`
   - TTD Elektronik: `Sudah`
   - Tanggal Terbit Sertifikat EL: hari ini
3. **Simpan** → **Selesai** (sertifikat BT elektronik terbit).

### 5b. Alih Media SU
1. Login `amsu1` / `amsu123` → menu `/alih-media-su` → Add `MANUAL/0001`.
2. Isi:
   - Scan Surat Ukur: `Sudah`
   - Upload KKP: `Sudah`
   - TTD Elektronik: `Sudah`
   - Tanggal Terbit Sertifikat EL: hari ini
3. **Simpan** → **Selesai** (sertifikat SU elektronik terbit).

> Yang terakhir selesai memicu status tiket → **`SELESAI`** + `tanggal_selesai` terisi otomatis.

---

## 6. Verifikasi Hasil

1. **Tracking publik** (tanpa login): buka `GET /tracking/MANUAL/0001`
   → tampil status `Selesai (Sertifikat El. Terbit)`, timeline lengkap 14 peristiwa, dan badge pembetulan (jika ada).
2. **Admin**: login `admin` / `admin123` → menu **Admin** (`/admin`) → tiket `MANUAL/0001` ada di **DB Tiket**
   → buka detail → lihat catatan final tiap tahap → klik **Arsipkan** → pindah ke **Arsip** (opsional).
3. **Pemimpin** (opsional): login `pemimpin` / `pemimpin123` untuk melihat monitoring semua tiket.

---

## Lampiran — Peta Route per Tahap (referensi)

| Tahap | URL prefix | Add | Simpan | Selesai | Revisi | Resubmit |
|---|---|---|---|---|---|---|
| Loket | `/loket` | `POST /loket` (store) | — | — | — | `POST /loket/{id}/resubmit` |
| Verifikator | `/verifikator` | `POST /verifikator/add/{id}` | `POST /verifikator/{id}/simpan` | `POST /verifikator/{id}/selesai` | `POST /verifikator/{id}/revisi` | — |
| Warkah | `/warkah` | `POST /warkah/add/{id}` | `POST /warkah/{id}/simpan` | `POST /warkah/{id}/selesai` | `POST /warkah/{id}/revisi` | — |
| Validator BT | `/validator-bt` | `POST /validator-bt/add/{id}` | `POST /validator-bt/{id}/simpan` | `POST /validator-bt/{id}/selesai` | `POST /validator-bt/{id}/revisi` | — |
| Validator SU | `/validator-su` | `POST /validator-su/add/{id}` | `POST /validator-su/{id}/simpan` | `POST /validator-su/{id}/selesai` | `POST /validator-su/{id}/revisi` | — |
| Alih Media BT | `/alih-media-bt` | `POST /alih-media-bt/add/{id}` | `POST /alih-media-bt/{id}/simpan` | `POST /alih-media-bt/{id}/selesai` | `POST /alih-media-bt/{id}/revisi` | — |
| Alih Media SU | `/alih-media-su` | `POST /alih-media-su/add/{id}` | `POST /alih-media-su/{id}/simpan` | `POST /alih-media-su/{id}/selesai` | `POST /alih-media-su/{id}/revisi` | — |

> Sumber field & urutan langkah: payload yang sama dengan `DemoAlurRealtimeTest` (dijamin lulus HTTP route asli).