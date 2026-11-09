<?php

/*
|--------------------------------------------------------------------------
| Menu Sidebar Default (Database-Driven Menu)
|--------------------------------------------------------------------------
| Sumber data awal tabel `menus`. Dipakai oleh migration
| 2026_10_28_000001_create_menus_table dan MenuSeeder agar sidebar
| langsung terisi setelah migrate/seed.
|
| Setiap entri: label, route (nama route), icon (kelas Bootstrap Icons +
| warna teks), active (pola routeIs() agar menu tetap menyala di halaman
| turunannya).
*/

return [

    'defaults' => [

        'super_admin' => [
            ['label' => 'Lihat/Coba Sebagai Akun', 'route' => 'super-admin.impersonate.index', 'icon' => 'bi-person-badge-fill text-warning', 'active' => ['super-admin.impersonate*']],
            ['label' => 'Backup Basis Data', 'route' => 'super-admin.backup.index', 'icon' => 'bi-database-down text-danger', 'active' => ['super-admin.backup*']],
            ['label' => 'Data & Skenario Demo', 'route' => 'super-admin.demo.index', 'icon' => 'bi-magic text-info', 'active' => ['super-admin.demo*'], 'butuh_demo' => true],
        ],

        'admin' => [
            ['label' => 'Daftar Berkas (Database)', 'route' => 'admin.index', 'icon' => 'bi-database-fill-add text-info', 'active' => ['admin.index', 'admin.show']],
            ['label' => 'Berkas Selesai', 'route' => 'admin.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['admin.selesai']],
            ['label' => 'Daftar Pengaduan', 'route' => 'pengaduan-unit.index', 'icon' => 'bi-chat-left-text-fill text-primary', 'active' => ['pengaduan-unit.index', 'pengaduan-unit.show']],
            ['label' => 'Pengaduan Selesai', 'route' => 'pengaduan-unit.selesai', 'icon' => 'bi-check2-all text-success', 'active' => ['pengaduan-unit.selesai']],
            ['label' => 'Arsip Folder', 'route' => 'admin.arsip', 'icon' => 'bi-archive-fill text-info', 'active' => ['admin.arsip*']],
            ['label' => 'Manajemen Akun', 'route' => 'admin.users', 'icon' => 'bi-people-fill text-primary', 'active' => ['admin.users*']],
            ['label' => 'Kelola Form Pendaftaran', 'route' => 'admin.form-pendaftaran', 'icon' => 'bi-ui-checks text-success', 'active' => ['admin.form-pendaftaran*']],
            ['label' => 'Setting', 'route' => 'admin.setting', 'icon' => 'bi-gear-fill text-secondary', 'active' => ['admin.setting*']],
            ['label' => 'Log Aktivitas', 'route' => 'admin.log', 'icon' => 'bi-journal-text text-info', 'active' => ['admin.log*']],
        ],

        'loket_pendaftaran' => [
            ['label' => 'Pendaftaran Baru', 'route' => 'pendaftaran.create', 'icon' => 'bi-plus-square-fill text-warning', 'active' => ['pendaftaran.create', 'pendaftaran.tahap*']],
            ['label' => 'Daftar Berkas Permohonan', 'route' => 'pendaftaran.index', 'icon' => 'bi-folder2-open text-info', 'active' => ['pendaftaran.index', 'pendaftaran.cetak']],
            ['label' => 'Ploting', 'route' => 'ploting.index', 'icon' => 'bi-pin-map-fill text-danger', 'active' => ['ploting.*', 'pendaftaran.antrian']],
            ['label' => 'Daftar Arsip', 'route' => 'arsip.index', 'icon' => 'bi-archive-fill text-secondary', 'active' => ['arsip.*']],
        ],

        'loket_ploting' => [
            ['label' => 'Daftar Berkas Ploting', 'route' => 'ploting.index', 'icon' => 'bi-pin-map-fill text-danger', 'active' => ['ploting.*']],
            ['label' => 'Daftar Arsip', 'route' => 'arsip.index', 'icon' => 'bi-archive-fill text-secondary', 'active' => ['arsip.*']],
        ],

        'petugas_warkah' => [
            ['label' => 'Permintaan Warkah', 'route' => 'warkah-permintaan.index', 'icon' => 'bi-inbox-fill text-warning', 'active' => ['warkah-permintaan.index', 'warkah-permintaan.show']],
            ['label' => 'Berkas Pinjaman', 'route' => 'warkah-permintaan.pinjaman', 'icon' => 'bi-box-arrow-up-right text-primary', 'active' => ['warkah-permintaan.pinjaman']],
            ['label' => 'Berkas Dikembalikan', 'route' => 'warkah-permintaan.dikembalikan', 'icon' => 'bi-arrow-return-left text-success', 'active' => ['warkah-permintaan.dikembalikan']],
            ['label' => 'Arsip', 'route' => 'arsip.index', 'icon' => 'bi-archive-fill text-secondary', 'active' => ['arsip.*']],
        ],

        'staf_seksi' => [
            ['label' => 'Cari & ADD Berkas', 'route' => 'seksi.index', 'icon' => 'bi-search text-primary', 'active' => ['seksi.index', 'seksi.show']],
            ['label' => 'Tiket Saya', 'route' => 'seksi.tiket-saya', 'icon' => 'bi-inbox-fill text-warning', 'active' => ['seksi.tiket-saya']],
            ['label' => 'Daftar Selesai', 'route' => 'seksi.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['seksi.selesai']],
            ['label' => 'Daftar Ditolak', 'route' => 'seksi.ditolak', 'icon' => 'bi-x-octagon-fill text-danger', 'active' => ['seksi.ditolak']],
        ],

        'kasi_seksi' => [
            ['label' => 'Daftar Permohonan Seksi', 'route' => 'seksi.index', 'icon' => 'bi-kanban-fill text-primary', 'active' => ['seksi.index', 'seksi.show']],
            ['label' => 'Daftar Selesai', 'route' => 'seksi.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['seksi.selesai']],
            ['label' => 'Daftar Ditolak', 'route' => 'seksi.ditolak', 'icon' => 'bi-x-octagon-fill text-danger', 'active' => ['seksi.ditolak']],
            ['label' => 'Daftar Pengaduan', 'route' => 'pengaduan-unit.index', 'icon' => 'bi-chat-left-text-fill text-info', 'active' => ['pengaduan-unit.index', 'pengaduan-unit.show']],
            ['label' => 'Pengaduan Selesai', 'route' => 'pengaduan-unit.selesai', 'icon' => 'bi-check2-all text-success', 'active' => ['pengaduan-unit.selesai']],
        ],

        'kasi_unit' => [
            ['label' => 'Daftar Pengaduan', 'route' => 'pengaduan-unit.index', 'icon' => 'bi-chat-left-text-fill text-primary', 'active' => ['pengaduan-unit.index', 'pengaduan-unit.show']],
            ['label' => 'Pengaduan Selesai', 'route' => 'pengaduan-unit.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['pengaduan-unit.selesai']],
        ],

        'kasubbag_tu' => [
            ['label' => 'Daftar Pengaduan', 'route' => 'pengaduan-unit.index', 'icon' => 'bi-chat-left-text-fill text-primary', 'active' => ['pengaduan-unit.index', 'pengaduan-unit.show']],
            ['label' => 'Pengaduan Selesai', 'route' => 'pengaduan-unit.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['pengaduan-unit.selesai']],
        ],

        'loket_penyerahan' => [
            ['label' => 'Antrian Penyerahan', 'route' => 'penyerahan.index', 'icon' => 'bi-box-arrow-right text-success', 'active' => ['penyerahan.index', 'penyerahan.show']],
            ['label' => 'Tiket Saya', 'route' => 'penyerahan.tiket-saya', 'icon' => 'bi-inbox-fill text-warning', 'active' => ['penyerahan.tiket-saya']],
            ['label' => 'Daftar Selesai', 'route' => 'penyerahan.selesai', 'icon' => 'bi-check2-circle text-primary', 'active' => ['penyerahan.selesai']],
        ],

        'loket_pengaduan' => [
            ['label' => 'Buat Pengaduan', 'route' => 'pengaduan.create', 'icon' => 'bi-plus-square-fill text-warning', 'active' => ['pengaduan.create']],
            ['label' => 'Daftar Pengaduan', 'route' => 'pengaduan.index', 'icon' => 'bi-chat-left-text-fill text-primary', 'active' => ['pengaduan.index', 'pengaduan.show']],
            ['label' => 'Pengaduan Selesai', 'route' => 'pengaduan.selesai', 'icon' => 'bi-check2-circle text-success', 'active' => ['pengaduan.selesai']],
        ],

        'kakan' => [
            ['label' => 'Monitoring & Evaluasi', 'route' => 'kakan.index', 'icon' => 'bi-clipboard-data-fill text-primary', 'active' => ['kakan.*']],
        ],

    ],

];
