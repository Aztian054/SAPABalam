@extends('layouts.app')

@section('title', 'Pengaduan Selesai')

@section('content')
@php
    $petugasUnit = auth()->user()->isPenanganPengaduan() || auth()->user()->isAdmin();
    $ruteDaftar = $petugasUnit ? route('pengaduan-unit.index') : route('pengaduan.index');
    $ruteSelesai = $petugasUnit ? route('pengaduan-unit.selesai') : route('pengaduan.selesai');
    $ruteDetail = $petugasUnit ? 'pengaduan-unit.show' : 'pengaduan.show';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pengaduan Selesai</h4>
        <p class="text-muted small mb-0">Riwayat pengaduan yang sudah ditutup unit tujuan. Tanggal acuan: Tgl Selesai.</p>
    </div>
    <a href="{{ $ruteDaftar }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i> Daftar Pengaduan
    </a>
</div>

<div class="card card-custom p-4">
    <form action="{{ $ruteSelesai }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Pengaduan, pengadu, No Tiket…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Selesai dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('pengaduan.selesai') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Pengaduan</th>
                    <th>Pengadu</th>
                    <th>Jenis</th>
                    <th>Seksi Penanganan</th>
                    <th>Tgl Kirim</th>
                    <th>Tgl Selesai</th>
                    <th>Durasi</th>
                    <th>Catatan Penyelesaian</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengaduans as $pengaduan)
                    <tr>
                        <td class="text-muted">{{ $pengaduans->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route($ruteDetail, $pengaduan) }}" class="fw-bold font-monospace text-decoration-none">{{ $pengaduan->nomor_pengaduan }}</a>
                        </td>
                        <td>{{ $pengaduan->pengadu_label }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $pengaduan->jenis_label }}</span></td>
                        <td>{{ $pengaduan->seksi_tujuan_label }}</td>
                        <td>{{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $pengaduan->selesai_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $pengaduan->lama_menunggu ?? '-' }}</td>
                        <td><span class="small">{{ \Illuminate\Support\Str::limit($pengaduan->catatan_terakhir, 60) ?: '-' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route($ruteDetail, $pengaduan) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Belum ada pengaduan selesai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pengaduans->links() }}</div>
</div>
@endsection
