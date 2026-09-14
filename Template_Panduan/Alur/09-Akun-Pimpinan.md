# 09 — Akun Pimpinan (Monitoring Read-Only)

Dokumen ini menjelaskan alur, menu, dan fungsi akun **Pimpinan** — role untuk monitoring tanpa akses proses.

---

## 1. Peran Pimpinan

Pimpinan (misal: Kepala Kantor / Kasi) bertugas **memantau** seluruh aktivitas sistem secara **read-only**. Pimpinan memiliki **tampilan yang sama persis dengan Admin**, namun semua tombol aksi, form input, dan kolom pengelolaan **terkunci**.

---

## 2. Menu yang Tersedia

```
MONITORING (view-only)
├── 🖥️ Dashboard Pemimpin      → /pemimpin
└── 📋 Monitoring Tiket        (daftar tiket, revisi, selesai)
    └── 🖨️ Print Monitoring
```

| Menu | Isi |
|------|-----|
| **Dashboard** | Melihat ringkasan tiket seluruh akun (umum) |
| **Monitoring Tiket** | Melihat daftar tiket seluruh tahapan (daftar tiket, revisi, selesai) |
| **Print Monitoring** | Mencetak laporan monitoring |

> Menu **"Manajemen Akun"** tidak ditampilkan di akun Pimpinan.

---

## 3. Batasan Pimpinan

| Aksi | Diizinkan? |
|------|-----------|
| Melihat statistik & daftar tiket | ✅ Ya |
| Membuka detail tiket (read-only) | ✅ Ya |
| Print monitoring | ✅ Ya |
| Membuat / mengedit / menghapus tiket | ❌ Tidak |
| Memproses berkas (verifikasi, warkah, validasi, alih media) | ❌ Tidak |
| Menambah / mengelola akun | ❌ Tidak |

---

## 4. Laporan & Rekap SLA

Pimpinan juga dapat membuka **Laporan & Rekap SLA** (`/reports`) untuk melihat statistik dan mencetak/mengekspor laporan — tetap dalam mode read-only.

---

*Dokumen ini bagian dari panduan alur LOKET 2026 — diselaraskan dengan `ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL).docx`.*