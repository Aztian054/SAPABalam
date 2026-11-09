@extends('layouts.app')

@section('title', 'Unit ' . auth()->user()->seksi_label . ' — Daftar Pengaduan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Pengaduan {{ auth()->user()->seksi_label }}</h4>
        <p class="text-muted small mb-0">Pengaduan yang ditujukan ke unit Anda. Mode penanganan, bukan pembuatan.</p>
    </div>
    <a href="{{ route('pengaduan-unit.selesai') }}" class="btn btn-outline-success btn-sm rounded-3 px-3">

        <i class="bi bi-check2-circle me-1"></i> Pengaduan Selesai
    </a>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Masuk', $ringkasan['masuk'], 'warning', 'bi-inbox-fill'],
            ['Diterima', $ringkasan['diterima'], 'primary', 'bi-gear-fill'],
            ['Selesai Bulan Ini', $ringkasan['selesai_bulan_ini'], 'success', 'bi-check2-circle'],
            ['Terlambat', $ringkasan['terlambat'], 'danger', 'bi-exclamation-triangle-fill'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-6 col-xl-3">
            <div class="card card-custom p-3 border-start border-4 border-{{ $warna }} h-100">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">{{ $label }}</div>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h4 class="fw-bold mb-0 text-{{ $warna }}">{{ number_format($nilai) }}</h4>
                    <i class="bi {{ $ikon }} text-{{ $warna }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-custom p-4">
    <form action="{{ route('pengaduan-unit.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Pengaduan, pengadu, No Tiket…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Jenis</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($jenisPengaduan as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\Pengaduan::STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Masuk dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm" title="sampai">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
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
                    <th>Ringkasan Uraian</th>
                    <th>Tgl Masuk</th>
                    <th>Lama</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengaduans as $pengaduan)
                    <tr @class(['table-danger' => $pengaduan->is_terlambat])>
                        <td class="text-muted">{{ $pengaduans->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('pengaduan-unit.show', $pengaduan) }}" class="fw-bold font-monospace text-decoration-none">{{ $pengaduan->nomor_pengaduan }}</a>
                            @if ($pengaduan->is_demo)<span class="badge bg-info text-dark ms-1">DEMO</span>@endif
                            @if ($pengaduan->tiket)
                                <div class="text-muted" style="font-size:0.72rem;">tiket: {{ $pengaduan->tiket->kode_tiket }}</div>
                            @endif
                        </td>
                        <td>{{ $pengaduan->pengadu_label }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $pengaduan->jenis_label }}</span></td>
                        <td><span class="small">{{ \Illuminate\Support\Str::limit($pengaduan->uraian, 45) }}</span></td>
                        <td>{{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            {{ $pengaduan->lama_menunggu ?? '-' }}
                            @if ($pengaduan->is_terlambat)
                                <span class="badge bg-danger ms-1">TERLAMBAT</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $pengaduan->status_badge }}">{{ $pengaduan->status_label }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('pengaduan-unit.show', $pengaduan) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if (! auth()->user()->isAdmin() && $pengaduan->status === 'terkirim')
                                <form method="POST" action="{{ route('pengaduan-unit.terima', $pengaduan) }}" class="d-inline"
                                      onsubmit="return confirm('Terima pengaduan {{ $pengaduan->nomor_pengaduan }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary py-1 px-2">Terima</button>
                                </form>
                            @elseif (! auth()->user()->isAdmin() && $pengaduan->status === 'diterima')
                                <a href="{{ route('pengaduan-unit.show', $pengaduan) }}" class="btn btn-sm btn-success py-1 px-2">Tutup</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada pengaduan untuk unit ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pengaduans->links() }}</div>
</div>
@endsection
