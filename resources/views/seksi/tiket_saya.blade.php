@extends('layouts.app')

@section('title', 'Seksi — Tiket Saya')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tiket Saya</h4>
        <p class="text-muted small mb-0">Berkas yang Anda ADD dan belum Anda KIRIM (Selesai/Tolak).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seksi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-search me-1"></i> Cari &amp; ADD
        </a>
        <a href="{{ route('seksi.ditolak') }}" class="btn btn-outline-danger btn-sm rounded-3 px-3">
            <i class="bi bi-x-octagon me-1"></i> Daftar Ditolak
        </a>
    </div>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Tiket</th>
                    <th>No. Berkas</th>
                    <th>Pemohon</th>
                    <th>Jenis</th>
                    <th>Peran / Bidang</th>
                    <th>Waktu ADD</th>
                    <th>Lama di Saya</th>
                    <th>Warkah</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    <tr class="{{ $tiket->is_macet ? 'table-danger' : '' }}">
                        <td class="text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('seksi.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a>
                            @if ($tiket->is_macet)
                                <span class="badge bg-danger ms-1">MACET</span>
                            @endif
                        </td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>{{ $tiket->peran_bidang_label }}</td>
                        <td>{{ $tiket->diambil_pada?->format('d/m H:i') ?? '-' }}</td>
                        <td>{{ $tiket->lama_diambil ?? '-' }}</td>
                        <td>
                            @forelse ($tiket->warkahPermintaans as $warkah)
                                <span class="badge bg-{{ $warkah->status_badge }}">{{ $warkah->jenis }}: {{ $warkah->status_label }}</span>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td class="text-end">
                            <a href="{{ route('seksi.show', $tiket) }}" class="btn btn-sm btn-gold py-1 px-2">
                                <i class="bi bi-play-fill"></i> Proses
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Tidak ada berkas di antrian Anda. Buka <a href="{{ route('seksi.index') }}">Cari &amp; ADD</a> untuk mengambil berkas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
