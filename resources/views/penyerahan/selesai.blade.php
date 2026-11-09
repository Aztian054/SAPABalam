@extends('layouts.app')

@section('title', 'Loket Penyerahan — Daftar Selesai')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Selesai</h4>
        <p class="text-muted small mb-0">Riwayat penyerahan berkas yang Anda tutup. Tanggal acuan: Tgl Diserahkan.</p>
    </div>
    <a href="{{ route('penyerahan.tiket-saya') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
        <i class="bi bi-inbox me-1"></i> Tiket Saya
    </a>
</div>

<div class="card card-custom p-4">
    <form action="{{ route('penyerahan.selesai') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, No Hak, NIK, penerima…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Serah dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('penyerahan.selesai') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Tgl Daftar</th>
                    <th>Tgl Diserahkan</th>
                    <th>Durasi Total</th>
                    <th>Penerima</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    @php
                        $durasi = '-';
                        if ($tiket->tanggal_masuk && $tiket->diserahkan_pada) {
                            $hari = (int) $tiket->tanggal_masuk->diffInDays($tiket->diserahkan_pada);
                            $durasi = $hari > 0 ? $hari.' hari' : 'kurang dari 1 hari';
                        }
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td><a href="{{ route('penyerahan.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a></td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $tiket->diserahkan_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $durasi }}</td>
                        <td>
                            {{ $tiket->penerima_nama ?? '-' }}
                            <div class="text-muted" style="font-size:0.75rem;">{{ $tiket->penerima_hubungan_label }}</div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('penyerahan.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Belum ada berkas yang diserahkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>
@endsection
