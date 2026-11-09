<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas hari penanda MACET
    |--------------------------------------------------------------------------
    |
    | Tiket yang dipegang (ADD) melewati jumlah hari ini akan diberi penanda
    | MACET. Penanda tidak mengubah status tiket.
    |
    */
    'batas_macet_hari' => env('LOKET_BATAS_MACET_HARI', 3),

    /*
    |--------------------------------------------------------------------------
    | Batas kembali warkah (hari)
    |--------------------------------------------------------------------------
    */
    'batas_warkah_hari' => env('LOKET_BATAS_WARKAH_HARI', 7),

    /*
    |--------------------------------------------------------------------------
    | Batas tanggap pengaduan (hari)
    |--------------------------------------------------------------------------
    |
    | Pengaduan berstatus terkirim melewati jumlah hari ini ditandai
    | TERLAMBAT (tidak mengubah status).
    |
    */
    'batas_tanggap_pengaduan_hari' => env('LOKET_BATAS_TANGGAP_PENGADUAN_HARI', 2),

    /*
    |--------------------------------------------------------------------------
    | Hari menuju arsip otomatis setelah tiket selesai
    |--------------------------------------------------------------------------
    */
    'hari_arsip' => env('LOKET_HARI_ARSIP', 30),

    /*
    |--------------------------------------------------------------------------
    | Retensi notifikasi in-app (hari)
    |--------------------------------------------------------------------------
    |
    | Notifikasi yang sudah dibaca dan melewati batas ini dihapus scheduler.
    | Notifikasi belum dibaca tidak pernah dihapus otomatis.
    |
    */
    'retensi_notifikasi_hari' => env('LOKET_RETENSI_NOTIFIKASI_HARI', 90),

    /*
    |--------------------------------------------------------------------------
    | Retensi log aktivitas (hari)
    |--------------------------------------------------------------------------
    |
    | Log aktivitas yang lebih lama dari batas ini dibersihkan scheduler.
    | Nilai dapat diubah Admin lewat halaman Setting.
    |
    */
    'retensi_log_hari' => env('LOKET_RETENSI_LOG_HARI', 90),

    /*
    |--------------------------------------------------------------------------
    | Mode Demo
    |--------------------------------------------------------------------------
    |
    | Menyalakan Skenario/Data Demo. Selain flag ini, lingkungan `local`
    | otomatis dianggap mode demo agar aman dipakai untuk pelatihan.
    |
    */
    'demo_mode' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Format nomor tiket
    |--------------------------------------------------------------------------
    |
    | JP + ddmmyyyy + urut 3 digit, contoh: JP26102026001
    |
    */
    'prefix_tiket' => 'JP',
    'prefix_pengaduan' => 'LP',
    'padding_tiket' => 3,

];
