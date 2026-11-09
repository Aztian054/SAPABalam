@extends('layouts.app')

@section('title', 'Seksi — Daftar Selesai')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Selesai</h4>
        <p class="text-muted small mb-0">
            @if (auth()->user()->isKasiSeksi())
                Tiket {{ auth()->user()->seksi_label }} yang sudah selesai.
            @else
                Tiket yang pernah Anda proses dan sudah selesai.
            @endif
        </p>
    </div>
    <a href="{{ route('seksi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-search me-1"></i> Cari &amp; ADD
    </a>
</div>

<div class="card card-custom p-4">
    <form action="{{ route('seksi.selesai') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, No Hak, NIK…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Masuk dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('seksi.selesai') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Tiket</th>
                    <th>No. Berkas</th>
                    <th>Pemohon</th>
                    <th>Jenis</th>
                    <th>No Hak</th>
                    <th>Tgl Masuk</th>
                    <th>Tgl Selesai</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td><a href="{{ route('seksi.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a></td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>{{ $tiket->no_hak_sekarang ?? '-' }}</td>
                        <td>{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $tiket->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('seksi.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada tiket selesai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>
@endsection
