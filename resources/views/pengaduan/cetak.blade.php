@extends('layouts.app')

@section('title', 'Cetak Pengaduan ' . $pengaduan->nomor_pengaduan)

@section('content')
<style>
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print, .no-print * { display: none !important; }
        .kop-surat { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-tengah { text-align: center; }
        .kop-tengah h1 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; }
        .kop-tengah h2 { margin: 0; font-size: 14px; text-transform: uppercase; }
        .kop-tengah p { margin: 2px 0; font-size: 11px; color: #555; }
        .lembar { padding: 20px; }
        .table-print { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-print td, .table-print th { border: 1px solid #333; padding: 6px 8px; font-size: 12px; vertical-align: top; }
        .table-print th { background: #f0f0f0; font-weight: bold; }
        .ttd { margin-top: 40px; }
        .ttd-line { border-top: 1px solid #333; height: 30px; margin-bottom: 5px; }
        .page-break { page-break-after: always; }
    }
    .no-print { position: fixed; top: 10px; right: 10px; z-index: 1000; background: #007bff; color: white; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
</style>

<button class="btn btn-primary no-print" onclick="window.print()">
    <i class="bi bi-printer me-1"></i> Cetak
</button>
<a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-outline-secondary no-print" style="margin-left:10px;">
    <i class="bi bi-arrow-left me-1"></i> Kembali
</a>

<div id="print-area">
    <div class="lembar">
        <!-- Kop Surat -->
        <div class="kop-surat">
            <div class="kop-tengah">
                <h1>PEMERINTAH KOTA BANDAR LAMPUNG</h1>
                <h2>KANTOR PERTANAHAN</h2>
                <p>Jl. Letkol Iskandar, Kota Bandar Lampung, Lampung 35111</p>
                <p>Telp: 0721-771234 &bull; Website: www.balamlampung.kab.go.id &bull; Email: kantorpertanahan@balamlampung.go.id</p>
            </div>
        </div>

        <div class="text-center mb-3">
            <h4 class="fw-bold text-uppercase">FORMULIR PENGADUAN MASYARAKAT</h4>
            <p class="text-muted small">Nomor: <strong class="font-monospace">{{ $pengaduan->nomor_pengaduan }}</strong> &nbsp;&nbsp;|&nbsp;&nbsp; Tanggal: {{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</p>
        </div>

        <!-- Data Pengaduan -->
        <table class="table-print">
            <tr>
                <th style="width:30%">Nomor Pengaduan</th>
                <td>{{ $pengaduan->nomor_pengaduan }}</td>
            </tr>
            <tr>
                <th>Kategori</th>
                <td>{{ $pengaduan->jenis_label }}</td>
            </tr>
            <tr>
                <th>Seksi Tujuan</th>
                <td>{{ $pengaduan->seksi_tujuan_label }}</td>
            </tr>
            <tr>
                <th>Pengadu</th>
                <td>{{ $pengaduan->pengadu_label }}@if(!$pengaduan->is_anonim) (NIK: {{ $pengaduan->nik_pengadu ?? '-' }})@endif</td>
            </tr>
            <tr>
                <th>Tanggal Dibuat</th>
                <td>{{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $pengaduan->status_label }}</td>
            </tr>
        </table>

        <!-- Uraian -->
        <div class="mt-3">
            <h5 class="fw-bold">Uraian Pengaduan</h5>
            <div style="white-space:pre-line; border:1px solid #ccc; padding:10px; background:#fafafa;">{{ $pengaduan->uraian }}</div>
        </div>

        <!-- Detail Tanah jika ada -->
        @if ($pengaduan->butuhDetailTanah())
            <div class="mt-3 page-break">
                <h5 class="fw-bold">Detail Permasalahan Tanah</h5>
                <div style="white-space:pre-line; border:1px solid #ccc; padding:10px; background:#fafafa;">{{ $pengaduan->uraian }}</div>
            </div>
        @endif

        <!-- Catatan Kronologis -->
        <div class="mt-3">
            <h5 class="fw-bold">Catatan Kronologis</h5>
            <table class="table-print">
                <thead>
                    <tr>
                        <th style="width:8%">Urutan</th>
                        <th style="width:15%">Tanggal</th>
                        <th style="width:20%">Dari</th>
                        <th style="width:20%">Jenis</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach ($pengaduan->catatans as $catatan)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $catatan->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $catatan->dari_sisi }} @if($catatan->seksi) (Seksi {{ $catatan->seksi }}) @endif</td>
                            <td>{{ $catatan->jenis_label }}</td>
                            <td>{{ $catatan->catatan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="ttd mt-5">
            <div class="row">
                <div class="col-md-6">
                    <div class="ttd-line"></div>
                    <div class="small text-center">Yang Membuat Pengaduan</div>
                </div>
                <div class="col-md-6">
                    <div class="ttd-line"></div>
                    <div class="small text-center">Kasi Tujuan</div>
                </div>
            </div>
        </div>

        <div class="mt-3 small text-muted">
            Dicetak dari Sistem Loket Pertanahan Elektronik BMN BALAM V2.0<br>
            Halaman ini tidak berisi file lampiran — semua input berupa teks.
        </div>
    </div>
</div>
@endsection
