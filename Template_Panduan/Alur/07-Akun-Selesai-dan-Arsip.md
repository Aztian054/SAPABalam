# 07 — Menu Selesai & Arsip

Dokumen ini menjelaskan menu **Selesai** — daftar tiket yang telah selesai diproses — dan bagaimana tiket diarsipkan ke **Arsip Folder**.

---

## 1. Peran Menu Selesai

Menu **Selesai** menampilkan seluruh tiket berstatus `selesai` (tahap akhir sebelum arsip).

- **Admin** dapat memindahkan tiket ke **Arsip** dari menu ini (tombol **"Pindahkan ke Arsip"**).
- **Akun lain** hanya dapat **melihat** (read-only).

> Tiket yang sudah dipindahkan ke Arsip **tidak lagi muncul** di menu Selesai.

---

## 2. Alur Kerja

```
1. Alih Media selesai (kedua sub-bidang BT & SU) → status = 'selesai'.
2. Tiket masuk di menu Selesai (Admin) dan daftar tiket di akun penanggung jawab.
3. Admin membuka menu Selesai → pilih tiket.
4. Klik "Pindahkan ke Arsip" → pilih Folder Arsip (atau buat folder baru).
5. Tiket berpindah ke Arsip Folder dan hilang dari menu Selesai.
```

---

## 3. Arsip Folder

| Aksi | Role | Keterangan |
|------|------|------------|
| Buat Folder | Admin | Memberi nama folder arsip (mis. bulanan/tahunan) |
| Tambahkan Tiket Selesai ke Folder | Admin | Arsipkan tiket satu per satu / massal |
| Export Arsip | Admin | Tombol Export PDF & Excel |

**Alur:** *Buat Folder → Tambahkan Tiket Selesai ke dalam Folder → Export.*

---

## 4. Akses

| Role | Lihat Selesai | Arsipkan / Kelola Folder |
|------|---------------|--------------------------|
| `admin` | ✅ Ya | ✅ Ya |
| `pemimpin` | ✅ Ya (view-only) | ❌ Tidak |
| Akun stage lain | ✅ Ya (tiket terkait) | ❌ Tidak |

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx`.*