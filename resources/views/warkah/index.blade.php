@extends('layouts.app')

@section('title', 'Warkah — Pencarian & Data Warkah')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-archive-fill text-primary me-2"></i>Stage 3 : Pencarian & Data Warkah</h4>
    </div>

    @if(isset($berkasDipinjam) && $berkasDipinjam->isNotEmpty())
        <div class="card card-custom mb-4 border-start border-4 border-warning">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-hourglass-split text-warning me-2"></i>Berkas BT/SU Dipinjam (menunggu pengembalian)</h6>
                <span class="badge bg-warning text-dark rounded-pill">{{ $berkasDipinjam->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Tiket</th><th>Pemohon</th><th>Tanggal Diserahkan</th><th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($berkasDipinjam as $bk)
                            <tr>
                                <td><a href="{{ route('warkah.show', $bk->tiket_id) }}" class="text-decoration-none fw-semibold">{{ $bk->tiket?->kode_tiket }}</a></td>
                                <td>{{ $bk->tiket?->nama_pemohon }}</td>
                                <td><small>{{ optional($bk->tanggal_diserahkan)->format('d/m/Y') }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('warkah.show', $bk->tiket_id) }}" class="btn btn-sm btn-warning text-white"><i class="bi bi-arrow-return-left me-1"></i>Catat Pengembalian</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                Catat pengembalian begitu hardcopy BT/SU diterima kembali dari Validator / Alih Media.
                Berkas hanya dapat <strong>diselesaikan</strong> setelah pengembalian tercatat.
            </div>
        </div>
    @endif

    @include('partials.stage_index', ['stage' => 'warkah', 'stageLabel' => 'Warkah'])
@endsection