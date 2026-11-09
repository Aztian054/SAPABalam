@extends('layouts.app')

@section('title', 'Permintaan Warkah')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Permintaan Warkah</h4>
        <p class="text-muted small mb-0">Permintaan BT/SU dari Loket Pendaftaran yang belum selesai dipinjamkan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('warkah-permintaan.pinjaman') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-box-arrow-right me-1"></i> Berkas Pinjaman
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
    <form action="{{ route('warkah-permintaan.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, pemohon, No Hak…">
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
            <label class="form-label small fw-semibold mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (['diminta', 'diterima', 'siap'] as $nilai)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ \App\Models\WarkahPermintaan::STATUS[$nilai] }}</option>
                @endforeach
            </select>
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
                    <th>Jenis</th>
                    <th>Pemohon</th>
                    <th>Diminta</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permintaans as $warkah)
                    <tr>
                        <td class="text-muted">{{ $permintaans->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="fw-bold font-monospace text-decoration-none">{{ $warkah->tiket->kode_tiket ?? '-' }}</a>
                        </td>
                        <td class="font-monospace small">{{ $warkah->tiket->no_berkas ?? '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $warkah->jenis }}</span>
                            <div class="text-muted" style="font-size:0.72rem;">{{ \App\Models\WarkahPermintaan::JENIS[$warkah->jenis] ?? '' }}</div>
                        </td>
                        <td>
                            {{ $warkah->tiket->nama_pemohon ?? '-' }}
                            @if ($warkah->is_demo || $warkah->tiket?->is_demo)
                                <span class="badge bg-info text-dark ms-1">DEMO</span>
                            @endif
                        </td>
                        <td>{{ $warkah->tanggal_permintaan?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td><span class="badge bg-{{ $warkah->status_badge }}">{{ $warkah->status_label }}</span></td>
                        <td class="text-end text-nowrap">
                            @if ($warkah->status === 'diminta')
                                <form method="POST" action="{{ route('warkah-permintaan.terima', $warkah) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary py-1 px-2">Terima</button>
                                </form>
                            @elseif ($warkah->status === 'diterima')
                                <form method="POST" action="{{ route('warkah-permintaan.siap', $warkah) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2">Tandai Siap</button>
                                </form>
                            @elseif ($warkah->status === 'siap')
                                <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="btn btn-sm btn-primary py-1 px-2">Serahkan</a>
                            @endif
                            <a href="{{ route('warkah-permintaan.show', $warkah) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada permintaan warkah yang menunggu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $permintaans->links() }}</div>
</div>
@endsection
