@extends('layouts.app')

@section('title', 'Cetak Pengaduan ' . $pengaduan->nomor_pengaduan)

@section('content')
<style>
    @page { size: A4; margin: 20mm 20mm 20mm 20mm; }
    body { font-family: 'Segoe UI', 'Arial', sans-serif; font-size: 11pt; line-height: 1.4; color: #333; }
    .kop-surat { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
    .kop-tengah { text-align: center; }
    .kop-tengah h1 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 2px; }
    .kop-tengah h2 { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; }
    .kop-tengah p { margin: 2px 0; font-size: 11px; color: #555; }
    .table table { width: 100%; border-collapse: collapse; }
    .table td, .table th { border: 1px solid #dee2e6; padding: 6px 8px; font-size: 0.875rem; vertical-align: top; }
    .table-light { background-color: #f8f9fa; }
    .text-muted { color: #6c757d !important; }
    .fw-bold { font-weight: 700 !important; }
    .fw-semibold { font-weight: 600 !important; }
    .text-muted { color: #6c757d !important; }
    .text-dark { color: #212529 !important; }
    .badge { font-size: 0.75rem; }
    .table-sm td, .table-sm th { padding: 0.4rem 0.5rem; }
</style>
<div class="kop-surat">
    <div class="d-flex align-items-center">
        <img src="{{ asset('assets/images/logobpn2026.png') }}" alt="Logo BPN" style="height: 60px; width: auto;">
        <div class="ms-3">
            <h1 class="fw-bold text-uppercase mb-1" style="font-size: 18px; letter-spacing: 2px;">PEMERINTAH KOTA BANDAR LAMPUNG</h1>
            <h2 class="fw-bold text-uppercase mb-1" style="font-size: 14px; text-transform: uppercase; letter-spacing: 2px;">KANTOR PERTANAHAN</h2>
            <p class="text-muted small mb-0" style="font-size: 11px;">Jl. Letkol Iskandar, Kota Bandar Lampung, Lampung 35111</p>
            <p class="text-muted small mb-0" style="font-size: 11px;">Telp: 0721-771234 &bull; Email: kantorpertanahan@balamlampung.go.id</p>
        </div>
    </div>
</div>

<div class="text-center mb-3">
    <h4 class="fw-bold text-uppercase mb-1">FORMULIR PENGADUAN MASYARAKAT</h4>
    <p class="text-muted small mb-0">
        Nomor: <strong class="font-monospace">{{ $pengaduan->nomor_pengaduan }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Tanggal: {{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}
    </p>
</div>

<table class="table table-sm mt-3">
    <tbody>
        <tr><th style="width:25%">Nomor Pengaduan</th><td class="font-monospace fw-bold">{{ $pengaduan->nomor_pengaduan }}</td></tr>
        <tr><th>Kategori</th><td>{{ $pengaduan->jenis_label }}</td></tr>
        <tr><th>Seksi Tujuan</th><td>{{ $pengaduan->seksi_tujuan_label }}</td></tr>
        <tr><th>Pengadu</th><td>{{ $pengaduan->pengadu_label }}</td></tr>
        <tr><th>Tiket Terkait</th><td><span class="font-monospace">{{ $pengaduan->tiket->kode_tiket ?? '-' }}</span></td></tr>
        <tr><th>Status</th><td><span class="badge bg-{{ $pengaduan->status_badge }}">{{ $pengaduan->status_label }}</span></td></tr>
        <tr><th>Tanggal Dibuat</th><td>{{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</td></tr>
    </tbody>
</table>

<h6 class="fw-bold mt-4 mb-3">Uraian Pengaduan</h6>
<p class="mb-0" style="white-space:pre-line; border:1px solid #ccc; padding:10px; background:#fafafa;">{{ $pengaduan->uraian }}</p>

<h6 class="fw-bold mt-4 mb-3">Catatan Kronologis</h6>
<table class="table table-sm">
    <thead class="table-light">
        <tr><th style="width:8%">Urutan</th><th style="width:18%">Tanggal</th><th style="width:20%">Dari</th><th style="width:20%">Jenis</th><th>Catatan</th></tr>
    </thead>
    <tbody>
    @php $no = 1; @endphp
    @foreach ($pengaduan->catatans as $catatan)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $catatan->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $catatan->dari_sisi }} @if($catatan->seksi) (Seksi {{ $catatan->seksi }}) @endif</td>
            <td><span class="badge bg-light text-dark border">{{ $catatan->jenis_label }}</span></td>
            <td>{{ $catatan->catatan }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-muted">Belum ada catatan.</td></tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-5">
        <div class="row">
            <div class="col-md-6 mt-4">
                <div class="border-bottom border-black" style="height: 1px;"></div>
                <div class="text-center small fw-bold mt-1">Yang Membuat Pengaduan</div>
            </div>
            <div class="col-md-6 mt-4">
                <div class="border-bottom border-black" style="height: 1px;"></div>
                <div class="text-center small fw-bold mt-1">Kasi Tujuan</div>
            </div>
        </div>
    </div>

    <div class="mt-4 small text-muted">
        Dicetak dari Sistem Loket Pertanahan Elektronik BMN BALAM V2.0
    </div>
</div>
</section>
