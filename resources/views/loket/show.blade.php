@extends('layouts.app')

@section('title', 'Detail Berkas — Loket')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>Detail Berkas</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('loket.index') }}">Loket</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Berkas</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('loket.edit', $tiket->id) }}" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> Edit Data
        </a>
        <a href="{{ route('loket.printReceipt', $tiket->id) }}" target="_blank" rel="noopener" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-receipt me-1"></i> Cetak Tanda Terima
        </a>
        <a href="{{ route('loket.printChecklist', $tiket->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-check2-square me-1"></i> Cetak Checklist
        </a>
        <a href="{{ route('tracking.show', $tiket->kode_tiket) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Lacak
        </a>
    </div>
</div>

@include('partials.tiket_header')

@php $revisiAktif = $tiket->catatanRevisis->where('sudah_diproses', false); @endphp

@if($tiket->status === 'dikembalikan')
<div class="alert alert-danger d-flex align-items-start shadow-sm border-0 mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
    <div class="flex-grow-1">
        <h6 class="fw-bold text-danger mb-1">Berkas Dikembalikan untuk Perbaikan Revisi</h6>
        @forelse($revisiAktif as $r)
            <div class="small mb-2">
                <strong>{{ $r->dari_stage }} &rarr; {{ $r->ke_stage }}</strong>
                <div>{{ $r->isi_revisi }}</div>
                <span class="text-muted d-block">Revisi ke-{{ $r->revisi_ke }} &bull; Masuk {{ $r->tanggal_masuk?->format('d/m/Y') }}</span>
            </div>
        @empty
            <p class="small mb-0">Berkas dikembalikan. Pastikan perbaikan dari pemohon sudah diterima lalu kirim ulang.</p>
        @endforelse
    </div>
</div>

@if($revisiAktif->isNotEmpty())
<div class="card card-custom mb-3 border-start border-4 border-danger">
    <div class="card-body">
        <h6 class="fw-bold text-dark mb-1"><i class="bi bi-send-check text-danger me-2"></i>Kirim Ulang Hasil Perbaikan</h6>
        <p class="text-muted small mb-3">Perbaikan dari pemohon akan dikirim ulang ke tahap yang mengembalikan berkas.</p>
        <form action="{{ route('loket.resubmit', $tiket->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="catatan_perbaikan" class="form-label small fw-semibold">Catatan Perbaikan (opsional)</label>
                <textarea name="catatan_perbaikan" id="catatan_perbaikan" rows="3" class="form-control" placeholder="Sebutkan perbaikan yang telah dilakukan oleh pemohon&hellip;">{{ old('catatan_perbaikan') }}</textarea>
            </div>
            <button type="submit" class="btn btn-danger btn-sm rounded-3 px-3">
                <i class="bi bi-send me-1"></i> Kirim Ulang ke Tahap Berikutnya
            </button>
        </form>
    </div>
</div>
@endif
@endif
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-check text-primary me-2"></i>Persyaratan Dokumen</h6>
                <a href="{{ route('loket.printChecklist', $tiket->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="bi bi-check2-square me-1"></i> Checklist
                </a>
            </div>
            <div class="card-body py-2">
                @php $persyaratans = $tiket->jenisPermohonan?->persyaratanDokumens ?? collect(); @endphp
                @if($persyaratans->isEmpty())
                    <p class="text-muted small mb-0">Belum ada daftar persyaratan untuk {{ $tiket->jenisPermohonan?->nama ?? 'jenis permohonan ini' }}.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($persyaratans as $i => $d)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-semibold small">{{ $i + 1 }}. {{ $d->nama_dokumen }}</span>
                                    @if($d->keterangan)
                                        <div class="small text-muted">{{ $d->keterangan }}</div>
                                    @endif
                                </div>
                                <span class="badge bg-{{ $d->wajib ? 'danger' : 'secondary' }} text-uppercase ms-2" style="font-size:0.65rem;">{{ $d->wajib ? 'Wajib' : 'Opsional' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
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
    </div>
</div>
@include('partials.bidang_table')

@include('partials.timeline')
@endsection