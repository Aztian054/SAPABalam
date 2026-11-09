@extends('layouts.app')

@section('title', 'Berkas Pinjaman Warkah')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Berkas Pinjaman</h4>
        <p class="text-muted small mb-0">Warkah BT/SU yang sedang dipegang staf Seksi 1/2.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('warkah-permintaan.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-inbox me-1"></i> Permintaan Warkah
        </a>
        <a href="{{ route('warkah-permintaan.dikembalikan') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-return-left me-1"></i> Dikembalikan
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Diminta', $ringkasan['diminta'], 'warning', 'bi-inbox-fill'],
            ['Siap Diambil', $ringkasan['siap'], 'success', 'bi-check2-circle'],
            ['Dipinjam', $ringkasan['dipinjam'], 'primary', 'bi-box-arrow-up-right'],
            ['Terlambat', $ringkasan['terlambat'], 'danger', 'bi-exclamation-triangle-fill'],
            ['Kembali Hari Ini', $ringkasan['kembali_hari_ini'], 'secondary', 'bi-archive-fill'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-6 col-xl">
            <div class="card card-custom p-3 border-start border-4 border-{{ $warna }} h-100">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.66rem;">{{ $label }}</div>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h4 class="fw-bold mb-0 text-{{ $warna }}">{{ number_format($nilai) }}</h4>
                    <i class="bi {{ $ikon }} text-{{ $warna }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-custom p-4">
    <form action="{{ route('warkah-permintaan.pinjaman') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="No Tiket, No. Berkas, pemohon, No Hak…">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Jenis warkah</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (\App\Models\WarkahPermintaan::JENIS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $nilai }} — {{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="terlambat" id="terlambat" value="1" @checked(request()->boolean('terlambat'))>
                <label class="form-check-label small" for="terlambat">Hanya yang terlambat</label>
            </div>
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
                    <th>Batas Kembali</th>
                    <th>Sisa Waktu</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pinjamen as $warkah)
                    <tr @class(['table-danger' => $warkah->is_terlambat])>
                        <td class="text-muted">{{ $pinjamen->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="fw-bold font-monospace text-decoration-none">{{ $warkah->tiket->kode_tiket ?? '-' }}</a>
                        </td>
                        <td class="font-monospace small">{{ $warkah->tiket->no_berkas ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $warkah->jenis }}</span></td>
                        <td>
                            {{ $warkah->nama_peminjam ?? $warkah->peminjam->name ?? '-' }}
                            <div class="text-muted" style="font-size:0.72rem;">
                                {{ \App\Models\User::SEKSI[$warkah->seksi] ?? $warkah->seksi ?? '-' }}
                            </div>
                        </td>
                        <td>{{ $warkah->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $warkah->batas_kembali?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $warkah->is_terlambat ? 'danger' : 'secondary' }}">{{ $warkah->sisa_waktu_label ?? '-' }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="btn btn-sm btn-success py-1 px-2">
                                <i class="bi bi-arrow-return-left me-1"></i>Kembalikan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Tidak ada warkah yang sedang dipinjam.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pinjamen->links() }}</div>
</div>
@endsection
