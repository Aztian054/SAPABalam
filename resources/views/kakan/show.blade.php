@extends('layouts.app')

@section('title', 'Kakan — Detail Berkas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Detail Berkas</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('kakan.index') }}">Kakan &mdash; Monitoring</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Berkas</li>
            </ol>
        </nav>
    </div>
</div>

@include('partials.tiket_header')

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

    <div class="col-lg-7">
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Petugas &amp; Pemegang Berkas</h6>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Petugas Loket</small>
                    <strong class="text-dark">{{ $tiket->petugasLoket?->name ?? $tiket->creator?->name ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Pemegang Saat Ini</small>
                    <strong class="text-dark">
                        {{ $tiket->penanggungJawab?->name ?? '-' }}
                        @if($tiket->penanggungJawab)
                            <span class="badge bg-light text-dark border ms-1">{{ $tiket->penanggungJawab->jabatan_label }}</span>
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Diserahkan Kepada</small>
                    <strong class="text-dark">{{ $tiket->penerima_nama ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Tanggal Serah Terima</small>
                    <strong class="text-dark">{{ $tiket->diserahkan_pada?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.bidang_table')

@include('partials.timeline')
@endsection
