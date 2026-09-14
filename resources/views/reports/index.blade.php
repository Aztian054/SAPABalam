@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Pelayanan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1">Laporan & Rekapitulasi Pelayanan</h4>
        <p class="text-muted small mb-0">Monitoring kinerja penyelesaian berkas permohonan dan evaluasi SLA.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success btn-sm rounded-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
        </a>
        <a href="{{ route('reports.print', request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-printer me-1"></i> Cetak Rekap Laporan
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-custom p-4 mb-4">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold">Tanggal Awal</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold">Tanggal Akhir</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold">Jenis Layanan</label>
            <select name="jenis_permohonan_id" class="form-select form-select-sm">
                <option value="">-- Semua Jenis Layanan --</option>
                @foreach($jenisPermohonans as $jp)
                    <option value="{{ $jp->id }}" {{ $jenisPermohonanId == $jp->id ? 'selected' : '' }}>
                        {{ $jp->kode }} - {{ $jp->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold">Status Akhir</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">-- Semua Status --</option>
                @foreach([
                    'diterima' => 'Diterima di Loket',
                    'verifikasi' => 'Pemeriksaan Verifikator',
                    'warkah' => 'Pencarian & Data Warkah',
                    'validasi_btel' => 'Validasi Pra-Buku Tanah El.',
                    'validasi_suel' => 'Validasi Pra-Surat Ukur El.',
                    'alih_media_btel' => 'Alih Media Pra-Buku Tanah El.',
                    'alih_media_suel' => 'Alih Media Pra-Surat Ukur El.',
                    'selesai' => 'Selesai (Sertifikat El. Terbit)',
                    'dikembalikan' => 'Dikembalikan (Perlu Perbaikan)',
                    'batal' => 'Dibatalkan',
                ] as $val => $lbl)
                    <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari kode tiket / nama pemohon..." value="{{ request('q') }}">
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search me-1"></i> Tampilkan Data</button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-primary text-center">
            <div class="text-muted small fw-bold text-uppercase">Total Masuk</div>
            <h3 class="fw-bold my-1 text-dark">{{ number_format($stats['total']) }}</h3>
            <small class="text-muted">Kumulatif Sistem</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-success text-center">
            <div class="text-muted small fw-bold text-uppercase">Selesai (Sertifikat El)</div>
            <h3 class="fw-bold my-1 text-success">{{ number_format($stats['selesai']) }}</h3>
            <small class="text-success fw-semibold">Penyelesaian: {{ $stats['total'] > 0 ? round(($stats['selesai'] / $stats['total']) * 100, 1) : 0 }}%</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-warning text-center">
            <div class="text-muted small fw-bold text-uppercase">Dalam Proses</div>
            <h3 class="fw-bold my-1 text-warning">{{ number_format($stats['proses']) }}</h3>
            <small class="text-muted">Di berbagai stage</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-danger text-center">
            <div class="text-muted small fw-bold text-uppercase">Perbaikan / Batal</div>
            <h3 class="fw-bold my-1 text-danger">{{ number_format($stats['dikembalikan'] + $stats['batal']) }}</h3>
            <small class="text-muted">{{ $stats['dikembalikan'] }} Perbaikan, {{ $stats['batal'] }} Batal</small>
        </div>
    </div>
</div>

<!-- Table Rekap -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>No. Tiket</th>
                    <th>Tanggal Masuk</th>
                    <th>Pemohon / Instansi</th>
                    <th>Jenis Permohonan</th>
                    <th>Jumlah Bidang</th>
                    <th>Target SLA</th>
                    <th>Status Akhir</th>
                    <th>Petugas Loket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $idx => $t)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            @if(auth()->user()->isLoket())
                                <a href="{{ route('loket.show', $t->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $t->kode_tiket }}
                                </a>
                            @else
                                <span class="fw-bold text-dark">{{ $t->kode_tiket }}</span>
                            @endif
                        </td>
                        <td>{{ $t->tanggal_masuk->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $t->nama_pemohon }}</div>
                            <small class="text-muted">{{ $t->satuan_kerja ?? 'Perorangan' }}</small>
                        </td>
                        <td>{{ $t->jenisPermohonan->kode }} - {{ $t->jenisPermohonan->nama }}</td>
                        <td class="text-center">{{ $t->bidangTanahs->count() }} Bidang</td>
                        <td>{{ $t->tanggal_target_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span>
                        </td>
                        <td>{{ $t->petugasLoket->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Tidak ada data permohonan pada rentang tanggal yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">Menampilkan {{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }} data</small>
        {{ $tikets->links() }}
    </div>
</div>
@endsection
