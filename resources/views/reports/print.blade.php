<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Laporan Pelayanan — Kantor Pertanahan Kota Bandar Lampung</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #000; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h3 { margin: 0; font-size: 15px; text-transform: uppercase; }
        .header h4 { margin: 2px 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.grid { border: 1px solid #000; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 5px; }
        table.grid th { background: #f0f0f0; text-align: center; }
        .no-print { margin-bottom: 15px; text-align: right; }
        .no-print button { padding: 8px 16px; font-weight: bold; cursor: pointer; background: #0b2239; color: #fff; border: none; border-radius: 4px; }
        .periode { text-align: center; font-size: 10px; margin: -4px 0 14px; }
        .col-no { width: 25px; }
        .col-tiket { width: 100px; }
        .col-tgl { width: 70px; }
        .col-bidang { width: 50px; }
        .col-status { width: 80px; }
        .empty-row { text-align: center; padding: 20px; }
        .ttd-table { width: 100%; margin-top: 30px; }
        .sig-space { height: 60px; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    @include('partials.kop_surat', ['subtitle' => 'REKAPITULASI LAPORAN PELAYANAN LOKET & MONITORING BERKAS'])

    <p class="periode">
        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </p>

    <table class="grid">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tiket">No. Tiket</th>
                <th class="col-tgl">Tgl Masuk</th>
                <th>Nama Pemohon / Instansi</th>
                <th>Jenis Layanan</th>
                <th class="col-bidang">Bidang</th>
                <th class="col-status">Status</th>
                <th class="col-status">Tgl Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tikets as $idx => $t)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">{{ $t->kode_tiket }}</td>
                    <td style="text-align: center;">{{ $t->tanggal_masuk->format('d/m/Y') }}</td>
                    <td>{{ $t->nama_pemohon }}</td>
                    <td>{{ $t->jenisPermohonan->kode }} - {{ $t->jenisPermohonan->nama }}</td>
                    <td style="text-align: center;">{{ $t->bidangTanahs->count() }}</td>
                    <td style="text-align: center; text-transform: uppercase; font-weight: bold;">{{ $t->status }}</td>
                    <td style="text-align: center;">{{ $t->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-row">Tidak ada data permohonan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                <div>Bandar Lampung, {{ now()->format('d F Y') }}</div>
                <div>Kepala Kantor Pertanahan Kota Bandar Lampung</div>
                <div class="sig-space"></div>
                <div>( <strong>........................................................</strong> )</div>
                <div>NIP. ....................................................</div>
            </td>
        </tr>
    </table>

</body>
</html>
