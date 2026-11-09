@extends('layouts.app')

@section('title', 'Seksi — Daftar Ditolak')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Ditolak</h4>
        <p class="text-muted small mb-0">
            @if (auth()->user()->isKasiSeksi())
                Tiket yang ditolak staf {{ auth()->user()->seksi_label }}.
            @else
                Tiket yang Anda tolak. Perbaiki masalahnya lalu ADD ULANG.
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seksi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-search me-1"></i> Cari &amp; ADD
        </a>
        <a href="{{ route('seksi.tiket-saya') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-inbox me-1"></i> Tiket Saya
        </a>
    </div>
</div>

<div class="card card-custom p-4">
    <form action="{{ route('seksi.ditolak') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, No Hak, NIK…">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Ditolak dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('seksi.ditolak') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>Ditolak Oleh</th>
                    <th>Tgl Ditolak</th>
                    <th>Alasan</th>
                    <th>Lama Menunggu</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    @php
                        $sudahDitindaklanjuti = $tiket->status !== 'ditolak';
                        $milikSaya = auth()->user()->isStafSeksi() && $tiket->penanggung_jawab_id === auth()->id();
                        $lamJam = (int) $tiket->ditolak_pada->diffInHours(now());
                        $lama = $lamJam >= 24 ? intdiv($lamJam, 24).' hari' : $lamJam.' jam';
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td><a href="{{ route('seksi.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a></td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>
                            {{ $tiket->penanggungJawab->name ?? '-' }}
                            <div class="text-muted" style="font-size:0.75rem;">{{ $tiket->penanggungJawab->seksi_label ?? '-' }}</div>
                        </td>
                        <td>{{ $tiket->ditolak_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td><span style="font-size:0.8rem;">{{ $tiket->alasan_penolakan ?? '-' }}</span></td>
                        <td><span class="badge bg-light text-dark border">{{ $lama }}</span></td>
                        <td>
                            @if ($sudahDitindaklanjuti)
                                <span class="badge bg-success">Sudah ditindaklanjuti</span>
                            @else
                                <span class="badge bg-danger">Menunggu Perbaikan</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('seksi.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if (! $sudahDitindaklanjuti && $milikSaya)
                                <button type="button" class="btn btn-sm btn-danger py-1 px-2 btn-add"
                                        data-bs-toggle="modal" data-bs-target="#modalAddTiket"
                                        data-action="{{ route('seksi.add', $tiket) }}"
                                        data-kode="{{ $tiket->kode_tiket }}"
                                        data-no-berkas="{{ $tiket->no_berkas ?? '' }}"
                                        data-pemohon="{{ $tiket->nama_pemohon }}"
                                        data-label="ADD ULANG">
                                    <i class="bi bi-arrow-repeat"></i> ADD ULANG
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Belum ada tiket yang ditolak.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>

@if (auth()->user()->isStafSeksi())
    @include('seksi._add_modal')
@endif
@endsection

@section('scripts')
@if (auth()->user()->isStafSeksi())
<script>
    (function () {
        const form = document.getElementById('formAddTiket');
        const label = document.getElementById('addSubmit');

        document.querySelectorAll('.btn-add').forEach((tombol) => {
            tombol.addEventListener('click', () => {
                form.action = tombol.dataset.action;
                document.getElementById('addKode').textContent = tombol.dataset.kode;
                document.getElementById('addPemohon').textContent = tombol.dataset.pemohon;
                document.getElementById('addNoBerkas').textContent = tombol.dataset.noBerkas || '-';
                label.textContent = tombol.dataset.label;
            });
        });

        @if ($errors->has('peran_bidang'))
            new bootstrap.Modal(document.getElementById('modalAddTiket')).show();
        @endif
    })();
</script>
@endif
@endsection
