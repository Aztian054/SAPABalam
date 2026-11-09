@extends('layouts.app')

@section('title', 'Detail Berkas ' . $tiket->kode_tiket)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Berkas {{ $tiket->kode_tiket }}</h4>
        <p class="text-muted small mb-0">Semua data pendaftaran, alur permohonan, dan alur warkah.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pendaftaran.edit', $tiket) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3">Data Pemohon & Berkas</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted" style="width:40%">No Tiket</th><td class="font-monospace fw-bold">{{ $tiket->kode_tiket }}</td></tr>
                <tr><th class="text-muted">No. Berkas</th><td>{{ $tiket->no_berkas ?? '-' }}</td></tr>
                <tr><th class="text-muted">Nama Pemohon</th><td>{{ $tiket->nama_pemohon }}</td></tr>
                <tr><th class="text-muted">NIK</th><td>{{ $tiket->nik_pemohon ?? '-' }}</td></tr>
                <tr><th class="text-muted">No HP</th><td>{{ $tiket->no_hp_pemohon ?? '-' }}</td></tr>
                <tr><th class="text-muted">Kelurahan/Desa</th><td>{{ $tiket->kelurahan_desa ?? '-' }}</td></tr>
                <tr><th class="text-muted">Kecamatan</th><td>{{ $tiket->kecamatan ?? '-' }}</td></tr>
                <tr><th class="text-muted">Jenis Permohonan</th><td>{{ $tiket->jenisPermohonan->kode ?? '-' }} — {{ $tiket->jenisPermohonan->nama ?? '-' }}</td></tr>
                <tr><th class="text-muted">No Hak Sekarang</th><td>{{ $tiket->no_hak_sekarang ?? '-' }}</td></tr>
                <tr><th class="text-muted">Jumlah Bidang</th><td>{{ $tiket->jumlah_bidang }}</td></tr>
                <tr><th class="text-muted">Tgl Daftar</th><td>{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td></tr>
                <tr><th class="text-muted">Tgl Selesai</th><td>{{ $tiket->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td></tr>
                <tr><th class="text-muted">Status Alur</th><td><span class="badge bg-{{ $tiket->status_badge }}">{{ $tiket->status_label }}</span></td></tr>
            </table>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3">Alur Warkah</h6>
            @forelse ($tiket->warkahPermintaans as $warkah)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $warkah->status_badge }}">{{ $warkah->jenis }}</span>
                    <span class="small">{{ $warkah->status_label }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Tidak memerlukan warkah.</p>
            @endforelse
        </div>

        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3">Diagram Alur Permohonan</h6>
            @php $alur = $tiket->alurEksekusi(); @endphp
            <ol class="list-unstyled mb-0">
                @foreach ($alur['items'] as $item)
                    <li class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-{{ $item['status'] === 'selesai' ? 'success' : ($item['status'] === 'proses' ? 'warning' : 'secondary') }}">{{ $item['no'] }}</span>
                        <span class="small fw-semibold">{{ $item['label'] }}</span>
                        <span class="badge bg-light text-dark border ms-auto" style="font-size:0.65rem;">{{ $item['status_label'] }}</span>
                    </li>
                @endforeach
            </ol>
            @if ($tiket->penanggungJawab)
                <div class="mt-3 p-2 rounded bg-light small">
                    <div class="fw-semibold">Sedang dipegang oleh:</div>
                    <div>{{ $tiket->penanggungJawab->name }} <span class="text-muted">({{ $tiket->penanggungJawab->jabatan_label }})</span></div>
                </div>
            @endif
            @if (! empty($alur['notice']))
                <p class="text-muted small mt-2 mb-0">{{ $alur['notice'] }}</p>
            @endif
        </div>
    </div>
</div>
@endsection