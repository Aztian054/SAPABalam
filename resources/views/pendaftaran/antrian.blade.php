@extends('layouts.app')

@section('title', 'Antrian Ploting — ' . $draf->kode_tiket)

@section('styles')
<style>
    @media print {
        #sidebar, #sidebarBackdrop, .topbar, .no-print { display: none !important; }
        #main-content { margin-left: 0 !important; }
        body { background: #fff !important; }
        .card-custom { box-shadow: none !important; border: 1px solid #999 !important; }
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
    <div>
        <h4 class="fw-bold mb-1">Antrian Ploting</h4>
        <p class="text-muted small mb-0">Serahkan lembar ini bersama berkas ke Loket Ploting.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        <button type="button" onclick="window.print()" class="btn btn-gold btn-sm px-3">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>
</div>

@include('pendaftaran._steps', ['aktif' => 2])

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="text-center border-bottom pb-3 mb-3">
                <div class="text-uppercase fw-bold small text-muted">Antrian Ploting Bidang Tanah</div>
                <div class="display-6 fw-bold font-monospace">{{ $draf->kode_tiket }}</div>
                <div class="small text-muted">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
            </div>

            <dl class="row mb-3">
                <dt class="col-sm-4 text-muted fw-normal">Nama Pemohon</dt><dd class="col-sm-8 fw-semibold">{{ $biodata['nama_pemohon'] ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No. Berkas</dt><dd class="col-sm-8">{{ $draf->no_berkas ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">NIK</dt><dd class="col-sm-8">{{ $biodata['nik_pemohon'] ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No. HP</dt><dd class="col-sm-8">{{ $biodata['no_hp_pemohon'] ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Kelurahan / Kecamatan</dt>
                <dd class="col-sm-8">{{ $biodata['kelurahan_desa'] ?? '-' }} / {{ $biodata['kecamatan'] ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Waktu Kirim</dt>
                <dd class="col-sm-8">{{ $draf->dikirim_pada?->translatedFormat('d F Y H:i') ?? now()->translatedFormat('d F Y H:i') }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Petugas Pendaftar</dt><dd class="col-sm-8">{{ $draf->user?->name ?? auth()->user()->name }}</dd>
            </dl>

            <div class="alert alert-warning border-0 mb-0 small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Pendaftaran tertahan di Tahap 2 sampai Loket Ploting menyatakan <strong>Sudah Ploting</strong>.
                Setelah itu buka menu <strong>Ploting &rarr; Sudah Ploting &rarr; Lanjut Pendaftaran</strong>.
            </div>
        </div>
    </div>
</div>
@endsection
