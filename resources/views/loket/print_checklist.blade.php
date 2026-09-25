<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checklist Berkas — {{ $tiket->kode_tiket }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #111; margin: 28px; font-size: 12px; }
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 8px; margin-bottom: 14px; }
        .header h2 { margin: 0; font-size: 16px; letter-spacing: 1px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .kode { text-align: center; margin: 16px 0; font-size: 13px; }
        .kode .box { display: inline-block; border: 2px solid #333; padding: 6px 16px; font-weight: bold; background: #f6f6f6; }
        table.info { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table.info td { padding: 2px 6px; vertical-align: top; font-size: 12px; }
        table.data { width: 100%; border-collapse: collapse; margin: 12px 0; }
        table.data th, table.data td { border: 1px solid #333; padding: 5px 6px; text-align: left; font-size: 11px; vertical-align: top; }
        table.data th { background: #eef1f5; }
        .col-no-b { width: 28px; }
        table.data .col-sifat { width: 90px; }
        table.data .col-ket { width: 120px; }
        .judul-checklist { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .empty-checklist { text-align: center; }
        .chk { text-align: center; width: 34px; }
        .chk span { display: inline-block; width: 12px; height: 12px; border: 1px solid #333; }
        .sign { margin-top: 60px; width: 100%; }
        .sign td { width: 33%; text-align: center; font-size: 12px; vertical-align: bottom; }
        .sign .name { margin-top: 80px; border-top: 1px solid #333; padding-top: 4px; font-weight: bold; }
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

    <div class="judul-checklist">
        CHECKLIST KELENGKAPAN BERKAS PERMOHONAN PERTANAHAN
    </div>

    <table class="info">
        <tr><td style="width:25%;">Nama Pemohon</td><td>: {{ $tiket->nama_pemohon }}</td>
            <td style="width:25%;">NIK</td><td>: {{ $tiket->nik_pemohon ?? '-' }}</td></tr>
        <tr><td>Jenis Permohonan</td><td>: {{ $tiket->jenisPermohonan?->nama ?? '-' }}</td>
            <td>Jumlah Bidang</td><td>: {{ $tiket->jumlah_bidang }}</td></tr>
        <tr><td>Tanggal Masuk</td><td>: {{ $tiket->tanggal_masuk?->format('d/m/Y') }}</td>
            <td>Status</td><td>: {{ $tiket->status_label }}</td></tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="col-no-b">No</th>
                <th>Persyaratan Dokumen</th>
                <th class="col-sifat">Sifat</th>
                <th class="col-ket">Keterangan</th>
                <th class="chk">Lengkap</th>
            </tr>
        </thead>
        <tbody>
            @php $persyaratans = $tiket->jenisPermohonan?->persyaratanDokumens ?? collect(); @endphp
            @forelse($persyaratans as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $d->nama_dokumen }}</td>
                    <td><span style="font-weight:bold;">{{ $d->wajib ? 'WAJIB' : 'Opsional' }}</span></td>
                    <td>{{ $d->keterangan ?? '-' }}</td>
                    <td class="chk"><span>&nbsp;</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-checklist">Belum ada daftar persyaratan untuk jenis permohonan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td>
                <div>Pemohon,</div>
                <div class="name">{{ $tiket->nama_pemohon }}</div>
            </td>
            <td>
                <div>Petugas Loket,</div>
                <div class="name">{{ $tiket->petugasLoket?->name ?? '-' }}</div>
            </td>
            <td>
                <div>Verifikator,</div>
                <div class="name">&nbsp;</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dicetak otomatis oleh Sistem Loket Pelayanan Pertanahan Elektronik &bull; Kantor Pertanahan Kota Bandar Lampung
    </div>
</body>
</html>