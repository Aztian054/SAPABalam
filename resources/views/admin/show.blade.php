@extends('layouts.app')

@section('title', 'Admin — Detail Berkas')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
        @if($tiket->status === 'selesai')
            <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>Selesai</span>
        @endif
    </div>

    @include('partials.tiket_header')
    @include('partials.bidang_table')

    <!-- Peta Penugasan -->
    <div class="card card-custom mb-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Peta Penugasan Tahap</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tahap</th><th>Petugas</th><th>Status</th><th>Di-Add</th><th>Selesai</th><th>Catatan</th></tr>
                </thead>
                <tbody>
                    @php $labelMap = \App\Models\Tiket::STAGES; @endphp
                    @forelse($penugasanPerStage as $p)
                        <tr>
                            <td>{{ $labelMap[$p->stage] ?? $p->stage }}</td>
                            <td>{{ $p->user?->name }} <small class="text-muted">({{ $p->user?->role_label }})</small></td>
                            <td><span class="badge bg-{{ $p->status === 'selesai' ? 'success' : ($p->status === 'proses' ? 'warning' : 'secondary') }}">{{ $p->status }}</span></td>
                            <td><small>{{ $p->tanggal_add?->format('d/m/Y H:i') }}</small></td>
                            <td><small>{{ $p->tanggal_selesai?->format('d/m/Y H:i') ?? '-' }}</small></td>
                            <td><small class="text-muted">{{ $p->catatan ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penugasan, berkas masih menunggu di-Add dari DB Admin.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Revisi -->
    @if($tiket->catatanRevisis->isNotEmpty())
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Catatan Revisi</h6></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Revisi</th><th>Dari</th><th>Ke</th><th>Isi</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($tiket->catatanRevisis as $r)
                            <tr>
                                <td>ke-{{ $r->revisi_ke }}</td>
                                <td>{{ $r->dari_stage }}</td>
                                <td>{{ $r->ke_stage }}</td>
                                <td><small>{{ $r->isi_revisi }}</small></td>
                                <td><span class="badge bg-{{ $r->sudah_diproses ? 'success' : 'danger' }}">{{ $r->sudah_diproses ? 'Diproses' : 'Menunggu' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Arsip -->
    @if($tiket->status === 'selesai')
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bi bi-archive text-primary me-2"></i>Arsip Berkas</h6></div>
            <div class="card-body">
                @if($tiket->arsips->isNotEmpty())
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($tiket->arsips as $a)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $a->nama_arsip }} <small class="text-muted">({{ $a->tipe_dokumen ?? '-' }})</small></span>
                                <span class="badge bg-light border">{{ $a->tanggal_arsip?->format('d/m/Y') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <form action="{{ route('admin.arsipkan', $tiket->id) }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-3">
                        <select name="folder_id" class="form-select" required>
                            <option value="">— Pilih Folder —</option>
                            @foreach($folders as $f)
                                <option value="{{ $f->id }}">{{ $f->nama_folder }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><input name="nama_arsip" class="form-control" placeholder="Nama arsip" required></div>
                    <div class="col-md-2"><input name="tipe" class="form-control" placeholder="Tipe dokumen"></div>
                    <div class="col-md-2"><input name="keterangan" class="form-control" placeholder="Keterangan"></div>
                    <div class="col-auto"><button class="btn btn-gold"><i class="bi bi-archive me-1"></i>Arsipkan</button></div>
                </form>
            </div>
        </div>
    @endif

    @include('partials.timeline')
@endsection