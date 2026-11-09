@extends('layouts.app')

@section('title', 'Loket Penyerahan — Antrian Penyerahan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Antrian Penyerahan</h4>
        <p class="text-muted small mb-0">
            Ketik No Tiket / No Hak / NIK lalu tekan <strong>Cari</strong>. Daftar kosong sampai ada pencarian.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('penyerahan.tiket-saya') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-inbox me-1"></i> Tiket Saya
        </a>
        <a href="{{ route('penyerahan.selesai') }}" class="btn btn-outline-success btn-sm rounded-3 px-3">
            <i class="bi bi-check2-circle me-1"></i> Daftar Selesai
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Antrean Siap Serah', $ringkasan['antrean'], 'secondary', 'bi-database-fill'],
            ['Sedang Saya Pegang', $ringkasan['diproses'], 'primary', 'bi-person-check-fill'],
            ['Diserahkan Hari Ini', $ringkasan['hari_ini'], 'success', 'bi-check2-circle'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-12 col-xl-4">
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
    <form action="{{ route('penyerahan.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, No Hak, NIK, NIB, sertipikat…" autofocus>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Masuk dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Cari</button>
            <a href="{{ route('penyerahan.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                    <th>No Hak / Sertipikat</th>
                    <th>Status</th>
                    <th>Dipegang Oleh</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    @php($bisaAdd = $tiket->bisaDiAddPenyerahan(auth()->user()))
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('penyerahan.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a>
                        </td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>
                            {{ $tiket->nama_pemohon }}
                            <div class="text-muted" style="font-size:0.75rem;">{{ $tiket->nik_pemohon ?? '-' }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->kode ?? '-' }}</span></td>
                        <td>
                            {{ $tiket->no_hak_sekarang ?? '-' }}
                            @php($sertipikat = $tiket->bidangTanahs->first()?->no_sertifikat_lama)
                            @if ($sertipikat)
                                <div class="text-muted" style="font-size:0.75rem;">{{ $sertipikat }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $tiket->status_badge }}">{{ $tiket->status_label }}</span></td>
                        <td>
                            @if ($tiket->penanggungJawab)
                                {{ $tiket->penanggungJawab->name }}
                                <div class="text-muted" style="font-size:0.75rem;">{{ $tiket->penanggungJawab->jabatan_label }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('penyerahan.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if ($bisaAdd && auth()->user()->isLoketPenyerahan())
                                <form method="POST" action="{{ route('penyerahan.add', $tiket) }}" class="d-inline"
                                      onsubmit="return confirm('ADD berkas {{ $tiket->kode_tiket }}? Berkas akan dipegang Anda.')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary py-1 px-2">
                                        <i class="bi bi-plus-lg"></i> ADD
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" disabled>ADD</button>
                                <div class="text-muted" style="font-size:0.72rem;">
                                    {{ $tiket->penanggung_jawab_id !== null
                                        ? 'Sedang dipegang '.($tiket->penanggungJawab?->name ?? 'petugas lain')
                                        : $tiket->status_label }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            @if ($adaFilter)
                                Tidak ada berkas yang cocok dengan pencarian.
                            @else
                                <i class="bi bi-search me-1"></i>
                                Ketik No Tiket / No Hak / NIK lalu tekan <strong>Cari</strong> untuk menampilkan berkas.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>
@endsection
