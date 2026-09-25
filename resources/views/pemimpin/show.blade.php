@extends('layouts.app')

@section('title', 'Pemimpin — Detail Berkas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Detail Berkas Pemimpin</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('pemimpin.index') }}">Pemimpin &mdash; Monitoring</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Berkas</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('loket.printReceipt', $tiket->id) }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-receipt me-1"></i> Cetak Tanda Terima
        </a>
        <a href="{{ route('tracking.show', $tiket->kode_tiket) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Lacak
        </a>
    </div>
</div>

@include('partials.tiket_header')

@if($tiket->status === 'dikembalikan')
<div class="alert alert-danger d-flex align-items-start shadow-sm border-0 mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div>
        <h6 class="fw-bold text-danger mb-1">Berkas Dikembalikan untuk Perbaikan Revisi</h6>
        <p class="small mb-0">Berkas sedang menunggu perbaikan dari pemohon sebelum dikirim ulang oleh petugas loket.</p>
    </div>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-check text-primary me-2"></i>Persyaratan Dokumen</h6>
            </div>
            <div class="card-body py-2">
                @forelse($tiket->jenisPermohonan?->persyaratanDokumens ?? collect() as $d)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span class="small">{{ $d->nama_dokumen }}</span>
                        <span class="badge {{ $d->wajib ? 'bg-danger' : 'bg-secondary text-white' }} text-uppercase" style="font-size:0.62rem;">
                            {{ $d->wajib ? 'Wajib' : 'Opsional' }}
                        </span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada daftar persyaratan untuk jenis permohonan ini.</p>
                @endforelse
            </div>
        </div>
    </div>
    </div>
<div class="card card-custom mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Petugas &amp; Penugasan</h6>
    </div>
    <div class="card-body py-2">
        <div class="d-flex justify-content-between border-bottom py-1">
            <small class="text-muted">Petugas Loket</small>
            <strong class="text-dark">{{ $tiket->petugasLoket?->name ?? $tiket->creator?->name ?? '-' }}</strong>
        </div>
        @forelse($tiket->penugasans->sortByDesc('id') as $p)
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="fw-semibold small d-block">{{ $p->stage_label }}</span>
                    <small class="text-muted">{{ $p->user?->name ?? '-' }}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $p->status === 'selesai' ? 'success' : ($p->status === 'proses' ? 'warning' : 'secondary') }} text-uppercase" style="font-size:0.65rem;">{{ $p->status }}</span>
                    <small class="d-block text-muted" style="font-size:0.7rem;">{{ $p->tanggal_add?->format('d/m/Y') }}</small>
                </div>
            </div>
        @empty
            <p class="text-muted small mb-0 pt-1">Belum ada penugasan ke tahap lain.</p>
        @endforelse
    </div>
</div>

@include('partials.bidang_table')

@include('partials.timeline')
@endsection