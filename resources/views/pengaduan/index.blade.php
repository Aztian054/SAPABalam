@extends('layouts.app')

@section('title', 'Loket Pengaduan — Daftar Pengaduan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Pengaduan</h4>
        <p class="text-muted small mb-0">Pengaduan yang Anda buat dan kirimkan ke unit tujuan.</p>
    </div>
    <div class="d-flex gap-2">
        @if (auth()->user()->isLoketPengaduan())
            <a href="{{ route('pengaduan.create') }}" class="btn btn-primary btn-sm rounded-3 px-3">
                <i class="bi bi-plus-lg me-1"></i> Buat Pengaduan
            </a>
        @endif
        <a href="{{ route('pengaduan.selesai') }}" class="btn btn-outline-success btn-sm rounded-3 px-3">
            <i class="bi bi-check2-circle me-1"></i> Pengaduan Selesai
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Hari Ini', $ringkasan['hari_ini'], 'secondary', 'bi-calendar-day'],
            ['Terkirim', $ringkasan['terkirim'], 'warning', 'bi-send-fill'],
            ['Diterima', $ringkasan['diterima'], 'primary', 'bi-inbox-fill'],
            ['Dikembalikan', $ringkasan['dikembalikan'], 'danger', 'bi-arrow-return-left'],
            ['Selesai', $ringkasan['selesai'], 'success', 'bi-check2-circle'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-6 col-xl">
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
    <div class="d-flex flex-wrap gap-2 mb-3">
        @php($aktif = request('status'))
        <a href="{{ route('pengaduan.index', request()->except('status', 'page')) }}"
           class="btn btn-sm {{ $aktif ? 'btn-outline-secondary' : 'btn-secondary' }}">Semua</a>
        @foreach (['terkirim', 'diterima', 'dikembalikan', 'selesai'] as $status)
            <a href="{{ route('pengaduan.index', array_merge(request()->except('status', 'page'), ['status' => $status])) }}"
               class="btn btn-sm {{ $aktif === $status ? 'btn-secondary' : 'btn-outline-secondary' }}">
                {{ \App\Models\Pengaduan::STATUS[$status] }}
            </a>
        @endforeach
    </div>

    <form action="{{ route('pengaduan.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Pengaduan, pengadu, No Tiket…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Jenis</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\Pengaduan::JENIS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Seksi tujuan</label>
            <select name="seksi_tujuan" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\User::SEKSI as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('seksi_tujuan') === (string) $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Buat dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search"></i></button>
            <a href="{{ route('pengaduan.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Seksi Tujuan</th>
                    <th>Status</th>
                    <th>Catatan Terakhir</th>
                    <th>Tgl Buat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengaduans as $pengaduan)
                    <tr @class(['table-danger' => $pengaduan->is_terlambat])>
                        <td class="text-muted">{{ $pengaduans->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('pengaduan.show', $pengaduan) }}" class="fw-bold font-monospace text-decoration-none">{{ $pengaduan->nomor_pengaduan }}</a>
                            @if ($pengaduan->is_demo)<span class="badge bg-info text-dark ms-1">DEMO</span>@endif
                            @if ($pengaduan->tiket)
                                <div class="text-muted" style="font-size:0.72rem;">tiket: {{ $pengaduan->tiket->kode_tiket }}</div>
                            @endif
                        </td>
                        <td>{{ $pengaduan->pengadu_label }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $pengaduan->jenis_label }}</span></td>
                        <td>{{ $pengaduan->seksi_tujuan_label }}</td>
                        <td>
                            <span class="badge bg-{{ $pengaduan->status_badge }}">{{ $pengaduan->status_label }}</span>
                            @if ($pengaduan->is_terlambat)
                                <span class="badge bg-danger ms-1">TERLAMBAT</span>
                            @endif
                        </td>
                        <td><span class="small">{{ \Illuminate\Support\Str::limit($pengaduan->catatan_terakhir, 40) ?: '-' }}</span></td>
                        <td>{{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if ($pengaduan->status === 'dikembalikan')
                                <a href="{{ route('pengaduan.show', $pengaduan) }}" class="btn btn-sm btn-danger py-1 px-2">
                                    <i class="bi bi-arrow-repeat"></i> Kirim Ulang
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada pengaduan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pengaduans->links() }}</div>
</div>
@endsection
