@extends('layouts.app')

@section('title', 'Warkah Dikembalikan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Berkas Dikembalikan</h4>
        <p class="text-muted small mb-0">Riwayat pengembalian warkah beserta kondisi berkas.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('warkah-permintaan.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-inbox me-1"></i> Permintaan Warkah
        </a>
        <a href="{{ route('warkah-permintaan.pinjaman') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> Berkas Pinjaman
        </a>
    </div>
</div>

<div class="card card-custom p-4">
    <form action="{{ route('warkah-permintaan.dikembalikan') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="No Tiket, No. Berkas, pemohon, No Hak…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Kembali dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Tiket</th>
                    <th>No. Berkas</th>
                    <th>Warkah</th>
                    <th>Peminjam</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Lama Pinjam</th>
                    <th>Kondisi</th>
                    <th>Pengembali</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengembalians as $warkah)
                    <tr>
                        <td class="text-muted">{{ $pengembalians->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="fw-bold font-monospace text-decoration-none">{{ $warkah->tiket->kode_tiket ?? '-' }}</a>
                        </td>
                        <td class="font-monospace small">{{ $warkah->tiket->no_berkas ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $warkah->jenis }}</span></td>
                        <td>
                            {{ $warkah->nama_peminjam ?? $warkah->peminjam->name ?? '-' }}
                            <div class="text-muted" style="font-size:0.72rem;">{{ \App\Models\User::SEKSI[$warkah->seksi] ?? $warkah->seksi ?? '-' }}</div>
                        </td>
                        <td>{{ $warkah->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $warkah->tanggal_kembali?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $warkah->lama_pinjam ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $warkah->kondisi === 'baik' ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', (string) $warkah->kondisi)) }}
                            </span>
                        </td>
                        <td>{{ $warkah->pengembali ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada warkah yang dikembalikan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pengembalians->links() }}</div>
</div>
@endsection
