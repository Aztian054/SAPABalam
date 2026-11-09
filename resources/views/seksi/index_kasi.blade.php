@extends('layouts.app')

@section('title', 'Kasi Seksi — Daftar Permohonan Seksi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Permohonan {{ auth()->user()->seksi_label }}</h4>
        <p class="text-muted small mb-0">
            Hanya tiket yang sudah di-ADD staf {{ auth()->user()->seksi_label }}. Mode pantau (read-only).
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seksi.ditolak') }}" class="btn btn-outline-danger btn-sm rounded-3 px-3">
            <i class="bi bi-x-octagon me-1"></i> Daftar Ditolak
        </a>
        <a href="{{ route('seksi.selesai') }}" class="btn btn-outline-success btn-sm rounded-3 px-3">
            <i class="bi bi-check2-circle me-1"></i> Daftar Selesai
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Diproses', $ringkasan['diproses'], 'primary', 'bi-gear-fill'],
            ['Ditolak', $ringkasan['ditolak'], 'danger', 'bi-x-octagon-fill'],
            ['Macet', $ringkasan['macet'], 'warning', 'bi-exclamation-triangle-fill'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-6 col-xl-4">
            <div class="card card-custom p-3 border-start border-4 border-{{ $warna }} h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">{{ $label }}</div>
                        <h4 class="fw-bold my-1 text-{{ $warna }}">{{ number_format($nilai) }}</h4>
                    </div>
                    <div class="bg-{{ $warna }} bg-opacity-10 text-{{ $warna }} p-2 rounded-circle"><i class="bi {{ $ikon }}"></i></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-custom p-4">
    <form action="{{ route('seksi.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, No Hak, NIK, staf…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">ADD dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('seksi.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Staf</th>
                    <th>Peran</th>
                    <th>Waktu ADD</th>
                    <th>Lama</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    <tr class="{{ $tiket->is_macet ? 'table-danger' : '' }}">
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td><a href="{{ route('seksi.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a></td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>{{ $tiket->penanggungJawab->name ?? '-' }}</td>
                        <td>{{ $tiket->peran_bidang_label }}</td>
                        <td>{{ $tiket->diambil_pada?->format('d/m H:i') ?? '-' }}</td>
                        <td>
                            {{ $tiket->lama_diambil ?? '-' }}
                            @if ($tiket->is_macet)
                                <span class="badge bg-danger ms-1">MACET</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $tiket->status_badge }}">{{ $tiket->status_label }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('seksi.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Belum ada tiket yang di-ADD staf seksi ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>
@endsection
