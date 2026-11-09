@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Pelayanan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-clipboard-data text-primary me-2"></i>Laporan & Rekapitulasi Pelayanan</h4>
        <p class="text-muted small mb-0">Monitoring kinerja penyelesaian berkas permohonan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success btn-sm rounded-3"><i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel</a>
        <a href="{{ route('reports.print_rapi', request()->all()) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-3"><i class="bi bi-file-earmark-pdf me-1"></i> Cetak / PDF</a>
    </div>
</div>
<div class="card card-custom p-4 mb-4">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3"><label class="form-label small fw-bold">Tanggal Awal</label><input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}"></div>
        <div class="col-md-3"><label class="form-label small fw-bold">Tanggal Akhir</label><input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}"></div>
        <div class="col-md-3"><label class="form-label small fw-bold">Jenis Layanan</label><select name="jenis_permohonan_id" class="form-select form-select-sm"><option value="">-- Semua --</option>@foreach($jenisPermohonans as $jp)<option value="{{ $jp->id }}" {{ $jenisPermohonanId == $jp->id ? 'selected' : '' }}>{{ $jp->kode }} - {{ $jp->nama }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label small fw-bold">Status</label><select name="status" class="form-select form-select-sm"><option value="">-- Semua Status --</option>@foreach(['di_database'=>'Di Database','diproses'=>'Sedang Diproses','ditolak'=>'Ditolak','selesai'=>'Selesai','batal'=>'Dibatalkan'] as $val=>$lbl)<option value="{{ $val }}" {{ $status===$val?'selected':'' }}>{{ $lbl }}</option>@endforeach</select></div>
        <div class="col-12"><input type="text" name="q" class="form-control" placeholder="Cari kode tiket / no. berkas / nama pemohon / NIK..." value="{{ request('q') }}"></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-gold"><i class="bi bi-funnel me-1"></i>Filter</button><a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a></div>
    </form>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card card-custom p-3 border-start border-4 border-primary text-center"><div class="text-muted small fw-bold text-uppercase">Total</div><h3 class="fw-bold my-1 text-primary">{{ number_format($stats['total']) }}</h3></div></div>
    <div class="col-md-3"><div class="card card-custom p-3 border-start border-4 border-success text-center"><div class="text-muted small fw-bold text-uppercase">Selesai</div><h3 class="fw-bold my-1 text-success">{{ number_format($stats['selesai']) }}</h3></div></div>
    <div class="col-md-3"><div class="card card-custom p-3 border-start border-4 border-warning text-center"><div class="text-muted small fw-bold text-uppercase">Dalam Proses</div><h3 class="fw-bold my-1 text-warning">{{ number_format($stats['proses']) }}</h3></div></div>
    <div class="col-md-3"><div class="card card-custom p-3 border-start border-4 border-secondary text-center"><div class="text-muted small fw-bold text-uppercase">Ditolak</div><h3 class="fw-bold my-1 text-secondary">{{ number_format($stats['ditolak']) }}</h3></div></div>
</div>
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle small mb-0">
            <thead class="table-light"><tr><th>No</th><th>No. Tiket</th><th>No. Berkas</th><th>Tgl Masuk</th><th>Pemohon</th><th>Jenis</th><th>Bidang</th><th>Status</th><th>Petugas Loket</th></tr></thead>
            <tbody>
            @forelse($tikets as $idx=>$t)
                <tr>
                    <td>{{ $idx+1 }}</td>
                    <td class="fw-bold">{{ $t->kode_tiket }}</td>
                    <td>{{ $t->no_berkas ?: '-' }}</td>
                    <td>{{ $t->tanggal_masuk->format('d/m/Y') }}</td>
                    <td>{{ $t->nama_pemohon }}</td>
                    <td>{{ $t->jenisPermohonan->kode }} - {{ $t->jenisPermohonan->nama }}</td>
                    <td class="text-center">{{ $t->bidangTanahs->count() }}</td>
                    <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                    <td>{{ $t->petugasLoket->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-5 text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">{{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }}</small>
        {{ $tikets->links() }}
    </div>
</div>
@endsection
