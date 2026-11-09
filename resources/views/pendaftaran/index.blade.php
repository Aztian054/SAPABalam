@extends('layouts.app')

@section('title', 'Daftar Berkas Permohonan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Berkas Permohonan</h4>
        <p class="text-muted small mb-0">Tiket hasil pendaftaran Anda &mdash; {{ auth()->user()->name }}.</p>
    </div>
    <a href="{{ route('pendaftaran.create') }}" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Pendaftaran Baru
    </a>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Total', $ringkasan['total'], 'primary', 'bi-folder-fill'],
            ['Hari Ini', $ringkasan['hari_ini'], 'warning', 'bi-inbox-fill'],
            ['Di Database', $ringkasan['di_database'], 'secondary', 'bi-database-fill'],
            ['Diproses', $ringkasan['diproses'], 'info', 'bi-gear-fill'],
            ['Ditolak', $ringkasan['ditolak'], 'danger', 'bi-x-octagon-fill'],
            ['Selesai', $ringkasan['selesai'], 'success', 'bi-patch-check-fill'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-6 col-md-4 col-xl-2">
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
    <form action="{{ route('pendaftaran.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Cari nomor / No. Berkas / pemohon / NIK</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="JP26102026001 — No. Berkas — pemohon">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach (['di_database' => 'Di Database', 'diproses' => 'Diproses', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Tanggal dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Tanggal sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('pendaftaran.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Tiket</th>
                    <th>No. Berkas</th>
                    <th>Tgl Daftar</th>
                    <th>Tgl Selesai</th>
                    <th>Pemohon</th>
                    <th>Jenis</th>
                    <th>No Hak</th>
                    <th>Status Warkah</th>
                    <th>Status Alur</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-bold font-monospace">{{ $tiket->kode_tiket }}</div>
                        </td>
                        <td>{{ $tiket->no_berkas ?? '-' }}</td>
                        <td>{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $tiket->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            {{ $tiket->nama_pemohon }}
                            <div class="text-muted" style="font-size:0.75rem">{{ $tiket->nik_pemohon ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span>
                            <div class="text-muted" style="font-size:0.75rem">{{ $tiket->jumlah_bidang }} bidang</div>
                        </td>
                        <td>{{ $tiket->no_hak_sekarang ?? '-' }}</td>
                        <td>
                            @forelse ($tiket->warkahPermintaans as $warkah)
                                <span class="badge bg-{{ $warkah->status_badge }}">{{ $warkah->jenis }}: {{ $warkah->status_label }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td>
                            <span class="badge bg-{{ $tiket->status_badge }}">{{ $tiket->status_label }}</span>
                            @if ($tiket->is_macet)
                                <span class="badge bg-danger ms-1">MACET</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('pendaftaran.show', $tiket) }}" class="btn btn-sm btn-outline-info py-1 px-2" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pendaftaran.edit', $tiket) }}" class="btn btn-sm btn-outline-warning py-1 px-2" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if (! in_array($tiket->status, ['diproses', 'selesai', 'ditolak']))
                                <form method="POST" action="{{ route('pendaftaran.destroy', $tiket) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus berkas {{ $tiket->kode_tiket }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            Belum ada berkas. Mulai dari <a href="{{ route('pendaftaran.create') }}">Pendaftaran Baru</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $tikets->links() }}
    </div>
</div>
@endsection
