# 02 — Akun Loket (Stage 1: Pendaftaran)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Loket** — titik masuk semua permohonan sesuai alur FINAL.

---

## 1. Peran Loket

Loket adalah **titik masuk** (entry point) semua permohonan. Petugas Loket:
1. **Mendaftarkan** permohonan baru (registrasi tiket).
2. **Memantau** tiket milik akun sendiri (Daftar Tiket Loket).
3. **Menerima kembali** tiket revisi dari tahapan lain (dikembalikan).
4. Mencetak **tanda terima** dan **checklist**.

> **Privasi:** *Daftar Tiket Loket HANYA menampilkan tiket milik akun pembuatnya* — tidak menampilkan tiket akun Loket lain. Fitur menambahkan tiket **HANYA** ada di menu Daftar Tiket, dan **HANYA di akun Loket** (selain Admin).

---

## 2. Menu pada Sidebar Loket

```
📋 STAGE 1: LOKET
├── ➕ Pendaftaran Baru   → /loket/create
└── 🎫 Daftar Tiket Loket → /loket
```

| Menu | URL | Isi |
|------|-----|-----|
| **Pendaftaran Baru** | `/loket/create` | Form registrasi tiket baru |
| **Daftar Tiket Loket** | `/loket` | Tiket milik akun sendiri (aktif, revisi, selesai) |

---

## 3. Registrasi Tiket Baru

### 3.1 Form Registrasi

| Field | Keterangan | Format / Contoh |
|-------|------------|-----------------|
| **Nomor Tiket** | Diisi **MANUAL** oleh petugas — kode loket / no antrian / ddmmyy / no urut berkas, **format bebas** (sesuai kebiasaan kantor; sistem tidak auto-generate) | `K/41/251124/1` |
| **Pembetulan Berkas Ke** | Iterasi perbaikan berkas | `P0`, `P1`, `P2`, ... |
| **Nama Pemohon** | Nama lengkap pemohon | GURUH SAMODRA AJI |
| **Jenis Permohonan** | 15 jenis permohonan didukung | JP01 – JP15 |
| **Nomor Hak Sekarang** | Format: Jenis Hak.No/Kel | `M.1088/NEGERI OLOK` |
| **Nomor Hak Sebelumnya** | Wajib jika ada `-DAHULU` | `B.1201/...-DAHULU` |
| **Kelurahan Sekarang** | Daftar kelurahan | WAY LAGA |
| **Nama Petugas Loket** | Terisi otomatis dari akun | Baynur |
| **Nomor Telepon** | Nomor aktif pemohon | 0813-xxx |

### 3.2 Setelah Submit

```
status   = 'diterima'
status_pembetulan = 'P0'
→ Tiket tersimpan di Daftar Tiket Loket (milik akun sendiri)
→ Tiket masuk ke Daftar Tiket Admin (database seluruh tiket)
→ Siap di-Add oleh Verifikator dan Warkah (paralel)
```

> Loket **tidak mengirim** tiket ke tahap berikutnya. Begitu tiket ada di DB Admin, **Verifikator ║ Warkah** bisa mengambilnya secara **paralel** dengan pola **Search → Add** (Exclusive Claim per jabatan). Validator & Alih Media mengikuti gate status masing-masing (lihat 00).

---

## 4. Aksi di Detail Tiket Loket (`/loket/{id}`)

| Aksi | Deskripsi |
|------|-----------|
| **Edit** | Ubah data tiket |
| **Re-Submit** | Serahkan ulang berkas perbaikan dari pemohon (tiket revisi) |
| **Cetak Tanda Terima** | `/loket/{id}/print-receipt` |
| **Cetak Checklist** | `/loket/{id}/print-checklist` |
| **Tracking** | Lihat posisi & riwayat tiket |

---

## 5. Alur Revisi di Loket

```
Tahapan lain menemukan masalah → tiket ditandai "dikembalikan"
→ Tiket muncul di menu "Revisi" akun yang mengirim revisi
→ Petugas yang bertanggung jawab memperbaiki
→ Bila berkas pemohon perlu dilengkapi → tiket kembali ke Loket
→ Pemohon datang & melengkapi berkas → petugas klik "Re-Submit"
→ Tiket kembali ke tahap yang membutuhkan perbaikan
```

### Level Pembetulan (tak terbatas)

| Iterasi | Level |
|---------|-------|
| Awal | P0 |
| Perbaikan ke-1 | P1 |
| Perbaikan ke-2 | P2 |
| Perbaikan ke-3 | P3 |
| ... | Pn (tidak dibatasi) |

---

## 6. Ringkasan Status Tiket di Loket

| Aksi Loket | Status yang Diubah |
|------------|--------------------|
| Daftar baru | `status='diterima'`, `status_pembetulan='P0'`, masuk DB Admin |
| Re-Submit perbaikan | Kembali ke tahap yang mengirim revisi |

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx` + konfigurasi alur paralel (nomor tiket manual; Verifikator ║ Warkah).*