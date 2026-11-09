# Dokumentasi Alur Sistem LOKET2026

> Folder ini berisi dokumentasi alur kerja sistem berdasarkan **kode implementasi aktual** (bukan dokumen lama yang sudah tidak relevan).  
> Super Admin **dieksklusi** dari semua dokumentasi alur operasional ini (hanya akses developer & darurat).

---

## File Dokumentasi

| File | Isi |
|------|-----|
| `ALUR_PERMOHONAN.md` | Alur permohonan berkas sertipikat tanah: Loket Pendaftaran → Staf Seksi → Loket Penyerahan → Admin → Arsip |
| `ALUR_PENGADUAN.md` | Alur pengaduan masyarakat: Loket Pengaduan → Kasi Tujuan → Bantuan Antar Kasi → Selesai Akhir |
| `SIDEBAR_PER_ROLE.md` | Menu sidebar untuk setiap role akun (excl. Super Admin) |
| `PENJELASAN_AKUN.md` | Penjelasan detail setiap role akun, fungsi, wewenang, dan akun default |

---

## Aturan Dokumentasi

1. Semua alur berdasarkan kode di `routes/web.php`, controller, dan model yang aktif
2. Super Admin tidak dimasukkan dalam alur operasional
3. Kasi Seksi 3, 4, 5 dan Kasubbag TU hanya melihat modul pengaduan (bukan tiket permohonan)
4. Dokumen ini menggantikan semua dokumentasi alur lama yang tidak sinkron dengan kode
