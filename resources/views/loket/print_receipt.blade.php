<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima — {{ $tiket->kode_tiket }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #111; margin: 28px; font-size: 12px; }
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 8px; margin-bottom: 14px; }
        .header h2 { margin: 0; font-size: 16px; letter-spacing: 1px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .kode { text-align: center; margin: 16px 0; font-size: 13px; }
        .kode .box { display: inline-block; border: 2px solid #333; padding: 6px 16px; font-weight: bold; background: #f6f6f6; }
        table.info { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.info td { padding: 2px 6px; vertical-align: top; font-size: 12px; }
        table.info .lbl { width: 35%; color: #444; }
        table.data { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px 6px; text-align: left; font-size: 11px; }
        table.data th { background: #eef1f5; }
        .section-title { font-weight: bold; margin-top: 14px; font-size: 12px; }
        .sign { margin-top: 60px; text-align: center; width: 280px; float: right; font-size: 12px; }
        .sign .name { margin-top: 80px; border-top: 1px solid #333; padding-top: 4px; font-weight: bold; }
        .clear { clear: both; }
        .judul-terima { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .catatan-persyaratan { font-size: 11px; }
        .adan-bidang { text-align: center; }
        .footer-note { margin-top: 24px; font-size: 10px; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
        @media print { body { margin: 10mm; } .print-btn { display: none; } }
        .print-btn { margin-bottom: 14px; }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Cetak</button>

    @include('partials.kop_surat')

    <div class="kode">
        <span class="box">KODE TIKET: {{ $tiket->kode_tiket }}</span>
    </div>

    <div class="judul-terima">
        TANDA TERIMA BERKAS PERMOHONAN PERTANAHAN
    </div>

    <table class="info">
        <tr><td class="lbl">Tanggal Masuk</td><td>: {{ $tiket->tanggal_masuk?->format('d/m/Y') }}</td>
            <td class="lbl">Jenis Permohonan</td><td>: {{ $tiket->jenisPermohonan?->nama ?? '-' }}</td></tr>
        <tr><td class="lbl">Jumlah Bidang</td><td>: {{ $tiket->jumlah_bidang }}</td>
            <td class="lbl">Nomor Antrian</td><td>: {{ $tiket->nomor_antrian ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Data Pemohon</div>
    <table class="info">
        <tr><td class="lbl">Nama Pemohon</td><td>: {{ $tiket->nama_pemohon }}</td>
            <td class="lbl">NIK</td><td>: {{ $tiket->nik_pemohon ?? '-' }}</td></tr>
        <tr><td class="lbl">No. HP</td><td>: {{ $tiket->no_hp_pemohon }}</td>
            <td class="lbl">No. Hak Sekarang</td><td>: {{ $tiket->no_hak_sekarang ?? '-' }}</td></tr>
        <tr><td class="lbl">Kelurahan / Kecamatan</td><td colspan="3">: {{ $tiket->kelurahan_desa ?? '-' }}, {{ $tiket->kecamatan ?? '-' }}</td></tr>
    </table>

    <div class="section-title">Data Bidang Tanah</div>
    <table class="data">
        <thead>
            <tr><th>#</th><th>NIB</th><th>No. Sertifikat Lama</th><th>Jenis Hak</th><th>Pemegang Hak</th><th>Kelurahan / Kecamatan</th></tr>
        </thead>
        <tbody>
            @forelse($tiket->bidangTanahs as $b)
                <tr>
                    <td>{{ $b->urutan }}</td>
                    <td>{{ $b->nib ?? '-' }}</td>
                    <td>{{ $b->no_sertifikat_lama ?? '-' }}</td>
                    <td>{{ $b->jenis_hak ?? '-' }}</td>
                    <td>{{ $b->nama_pemegang_hak ?? '-' }}</td>
                    <td>{{ $b->desa_kelurahan ?? '-' }}, {{ $b->kecamatan ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="adan-bidang">Tidak ada data bidang tanah.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="catatan-persyaratan">Daftar persyaratan permohonan terlampir pada <strong>Checklist Berkas</strong>.

    <div class="sign">
        <div>Petugas Loket,</div>
        <div>Bandar Lampung, {{ now()->format('d/m/Y') }}</div>
        <div class="name">{{ $tiket->petugasLoket?->name ?? '-' }}</div>
    </div>
    <div class="clear"></div>

    <div class="footer-note">
        Dokumen ini dicetak otomatis oleh Sistem Loket Pelayanan Pertanahan Elektronik &bull; Kantor Pertanahan Kota Bandar Lampung
    </div>
</body>
</html>