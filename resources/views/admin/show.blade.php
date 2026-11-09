@extends('layouts.app')

@section('title', 'Admin — Detail Berkas')

@section('content')
    @php($bisaEditNoBerkas = true)

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Database Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Berkas</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.print_rapi', ['q' => $tiket->kode_tiket]) }}" target="_blank" rel="noopener"
               class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="bi bi-printer me-1"></i> Cetak Rekap
            </a>
        </div>
    </div>

    @include('partials.tiket_header')
    @include('partials.bidang_table')

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Petugas &amp; Pemegang Berkas</h6>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <small class="text-muted">Petugas Loket</small>
                        <strong class="text-dark">{{ $tiket->petugasLoket?->name ?? $tiket->nama_petugas_loket ?? '-' }}</strong>
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

        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Permintaan Warkah</h6>
                </div>
                <div class="card-body py-2">
                    @forelse($tiket->warkahPermintaans as $w)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div class="small">
                                <span class="fw-semibold text-dark">{{ strtoupper($w->jenis) }}</span>
                                <span class="text-muted d-block">{{ $w->status_label ?? $w->status }}</span>
                            </div>
                            <span class="badge bg-light border text-dark">{{ $w->batas_kembali?->format('d/m/Y') ?? '-' }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Tidak ada permintaan warkah.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($tiket->status === 'selesai')
        <div class="card card-custom my-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-archive text-primary me-2"></i>Arsip Berkas</h6></div>
            <div class="card-body">
                @if($tiket->arsips->isNotEmpty())
                    <ul class="list-group list-group-flush mb-0">
                        @foreach($tiket->arsips as $a)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $a->nama_arsip }} <small class="text-muted">({{ $a->tipe_dokumen ?? '-' }})</small></span>
                                <span class="badge bg-light border">{{ $a->tanggal_arsip?->format('d/m/Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">Berkas ini belum memiliki arsip.</p>
                @endif
            </div>
        </div>
    @endif

    @include('partials.timeline')
@endsection
