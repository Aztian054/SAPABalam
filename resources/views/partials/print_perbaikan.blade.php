<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Permohonan Perbaikan — {{ $tiket->kode_tiket }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #111; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0b2239; padding-bottom: 12px; margin-bottom: 18px; }
        .title { font-size: 15px; font-weight: 700; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data td, table.data th { border: 1px solid #9aa4b0; padding: 6px 8px; }
        table.data th { background: #eef2f7; text-align: left; }
        .box { border: 1px solid #9aa4b0; padding: 10px 12px; margin-bottom: 14px; }
        .ttd { margin-top: 30px; display: flex; justify-content: space-between; }
        .ttd div { text-align: center; width: 30%; }
        .ttd .line { margin-top: 64px; border-top: 1px solid #111; padding-top: 6px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()" style="background:#0b2239;color:#fff;border:0;padding:8px 16px;border-radius:6px;cursor:pointer;">🖨 Cetak / Simpan PDF</button>
        <a href="javascript:history.back()" style="margin-left:8px;">Kembali</a>
    </div>

    <div class="header">
        <div class="title">FORM PERMINTAAN PERBAIKAN / KELENGKAPAN BERKAS</div>
        <div>Kantor Pertanahan Kota Bandar Lampung<br><small>Loket Pelayanan Pertanahan Elektronik</small></div>
    </div>

    <p style="margin-top:0;">Dengan hormat, bersama ini kami sampaikan bahwa berkas permohonan di bawah ini masih memerlukan <b>perbaikan / kelengkapan</b> sebelum dapat diproses ke tahap berikutnya:</p>

    <table class="data">
        <tr><th style="width:30%;">Kode Tiket</th><td>{{ $tiket->kode_tiket }}</td></tr>
        <tr><th>Nama Pemohon</th><td>{{ $tiket->nama_pemohon }}</td></tr>
        <tr><th>NIK Pemohon</th><td>{{ $tiket->nik_pemohon ?? '-' }}</td></tr>
        <tr><th>Jenis Permohonan</th><td>{{ $tiket->jenisPermohonan?->nama ?? '-' }}</td></tr>
        <tr><th>Nomor Hak (Sekarang)</th><td>{{ $tiket->no_hak_sekarang ?? '-' }}</td></tr>
        <tr><th>Letak Tanah</th><td>Kel. {{ $tiket->kelurahan_desa ?? '-' }} , Kec. {{ $tiket->kecamatan ?? '-' }}</td></tr>
        <tr><th>Jumlah Bidang</th><td>{{ $tiket->jumlah_bidang }}</td></tr>
        <tr><th>Tanggal Masuk</th><td>{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><th>Tahap Pengirim</th><td>{{ ucwords(str_replace('_', ' ', $lastRevisi?->dari_stage ?? '-')) }}</td></tr>
    </table>

    <div class="box">
        <b>Uraian Perbaikan / Kelengkapan yang Diminta:</b>
        <p style="margin:8px 0 0; white-space:pre-wrap;">{{ $lastRevisi?->isi_revisi ?? '—' }}</p>
    </div>

    <p>
        <b>Catatan:</b> Perbaikan dapat dikirim kembali melalui Loket (atau tahap lanjutan) setelah pemohon melengkapinya.
        Revisi dinyatakan selesai setelah umumnya diverifikasi ulang pada tahap yang bersangkutan (P1, P2, P3, …).
    </p>

    <div class="ttd">
        <div>
            <div>Pemohon / Kuasa</div>
            <div class="line">(......................)</div>
        </div>
        <div></div>
        <div>
            <div>Petugas {{ ucwords(str_replace('_', ' ', $lastRevisi?->dari_stage ?? '')) }}</div>
            <div class="line">{{ $lastRevisi?->pengirim?->name ?? '' }}</div>
        </div>
    </div>
</body>
</html>