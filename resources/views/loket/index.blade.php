@extends('layouts.app')

@section('title', 'Loket — Daftar Tiket')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-1">Loket Penerimaan</h4>
            <p class="text-muted small mb-0">
                Selamat datang, <strong>{{ auth()->user()->name }}</strong> &mdash; daftar tiket yang Anda daftarkan / Anda pegang.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('loket.create') }}" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Daftarkan Tiket Baru
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="bi bi-printer me-1"></i> Rekap Laporan
            </a>
        </div>
    </div>
</div>

<!-- Statistik Saya -->
@php
    $totalSaya   = $tikets->total();
    $diproses    = $tikets->whereIn('status', ['diterima','verifikasi','warkah','validasi_btel','validasi_suel','alih_media_btel','alih_media_suel','dikembalikan'])->count();
    $selesaiSaya = $tikets->where('status', 'selesai')->count();
    $dibatalkan  = $tikets->where('status', 'batal')->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Tiket Saya</div>
                    <h3 class="fw-bold my-1 text-dark">{{ number_format($totalSaya) }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle"><i class="bi bi-ticket-perforated-fill fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Dalam Proses</div>
                    <h3 class="fw-bold my-1 text-warning">{{ number_format($diproses) }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle"><i class="bi bi-hourglass-split fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Selesai</div>
                    <h3 class="fw-bold my-1 text-success">{{ number_format($selesaiSaya) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle"><i class="bi bi-patch-check-fill fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Revisi Pending</div>
                    <h3 class="fw-bold my-1 text-danger">{{ number_format($revisiBelumDiproses->count()) }}</h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle"><i class="bi bi-arrow-counterclockwise fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Peringatan revisi belum diproses -->
@if($revisiBelumDiproses->isNotEmpty())
    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex flex-wrap align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div class="flex-grow-1">
            <strong>{{ $revisiBelumDiproses->count() }} tiket revisi masih menunggu diproses. </strong>
            Perbaikan dari pemohon perlu dikirim ulang ke tahap asal.
        </div>
        @foreach($revisiBelumDiproses as $rt)
            <a href="{{ route('loket.show', $rt->id) }}" class="btn btn-sm btn-outline-danger rounded-3 px-3">
                <i class="bi bi-ticket me-1"></i> {{ $rt->kode_tiket }}
            </a>
        @endforeach
    </div>
@endif
<!-- Daftar tiket -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Daftar Tiket Penerimaan</h6>
        <form method="GET" class="d-flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="min-width:200px;" placeholder="Cari kode tiket / nama / NIK...">
            <select name="status" class="form-select form-select-sm" style="min-width:180px;">
                <option value="">Semua Status</option>
                @foreach(['diterima'=>'Diterima','verifikasi'=>'Verifikasi','warkah'=>'Warkah','validasi_btel'=>'Validasi BT','validasi_suel'=>'Validasi SU','alih_media_btel'=>'Alih Media BT','alih_media_suel'=>'Alih Media SU','selesai'=>'Selesai','dikembalikan'=>'Revisi','batal'=>'Batal'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary rounded-3 px-3" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('loket.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Tiket</th>
                    <th>Pemohon</th>
                    <th>Jenis Permohonan</th>
                    <th class="text-center">Bidang</th>
                    <th>Status</th>
                    <th>Tgl Masuk</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $t)
                    <tr>
                        <td>
                            <a href="{{ route('loket.show', $t->id) }}" class="fw-bold text-primary text-decoration-none">{{ $t->kode_tiket }}</a>
                            @if($t->status == 'dikembalikan')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1 d-block w-fit">Revisi</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $t->nama_pemohon }}</div>
                            <small class="text-muted">{{ $t->nik_pemohon ?? '-' }} &bull; {{ $t->no_hp_pemohon ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $t->jenisPermohonan->kode }}</span>
                            <small class="d-block text-truncate text-muted" style="max-width:160px;">{{ $t->jenisPermohonan->nama }}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-dark-subtle text-dark">{{ $t->bidangTanahs->count() }}</span></td>
                        <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                        <td>
                            <small>{{ $t->created_at?->format('d/m/Y H:i') }}</small>
                            <small class="d-block text-muted">Target: {{ $t->tanggal_target_selesai?->format('d/m/Y') ?? '-' }}</small>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('loket.show', $t->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('loket.printReceipt', $t->id) }}" class="btn btn-sm btn-outline-success py-1 px-2" target="_blank" title="Cetak tanda terima"><i class="bi bi-receipt"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada tiket. <a href="{{ route('loket.create') }}" class="text-primary">Daftarkan tiket pertama</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tikets->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">Menampilkan {{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }} tiket</span>
                {{ $tikets->links() }}
            </div>
        </div>
    @endif
</div>
@endsection