@extends('layouts.app')

@section('title', 'Admin — Daftar Berkas (Database)')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-database-fill-add text-primary me-2"></i>Daftar Berkas (Database)</h4>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
            <i class="bi bi-eye me-1"></i>Mode Monitoring
        </span>
    </div>

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total Berkas', 'value' => $stats['total'], 'icon' => 'ticket-perforated-fill', 'color' => 'primary'],
                ['label' => 'Di Database', 'value' => $stats['menunggu'], 'icon' => 'hourglass-split', 'color' => 'warning'],
                ['label' => 'Sedang Diproses', 'value' => $stats['proses'], 'icon' => 'gear-wide-connected', 'color' => 'info'],
                ['label' => 'Selesai', 'value' => $stats['selesai'], 'icon' => 'check2-circle', 'color' => 'success'],
                ['label' => 'Ditolak', 'value' => $stats['ditolak'], 'icon' => 'x-octagon-fill', 'color' => 'danger'],
                ['label' => 'Batal', 'value' => $stats['batal'], 'icon' => 'x-circle', 'color' => 'dark'],
            ];
        @endphp
        @foreach($cards as $c)
            <div class="col-6 col-md-2">
                <div class="card card-custom p-3 text-center">
                    <div class="fw-bold fs-4 text-{{ $c['color'] }}">{{ $c['value'] }}</div>
                    <small class="text-muted">{{ $c['label'] }}</small>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-custom mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Cari kode tiket / no. berkas / pemohon / NIK..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['di_database' => 'Di Database', 'diproses' => 'Sedang Diproses', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $st => $label)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-gold"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>No. Berkas</th><th>Pemohon</th><th>Jenis</th><th>Masuk</th><th>Pemegang</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tikets as $t)
                        <tr>
                            <td><a href="{{ route('admin.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a></td>
                            <td><small>{{ $t->no_berkas ?: '-' }}</small></td>
                            <td>
                                {{ $t->nama_pemohon }}
                                @if ($t->is_demo)<span class="badge bg-info text-dark ms-1">DEMO</span>@endif
                            </td>
                            <td><small>{{ $t->jenisPermohonan?->nama }}</small></td>
                            <td><small>{{ $t->tanggal_masuk?->format('d/m/Y') }}</small></td>
                            <td>
                                @if($t->penanggungJawab)
                                    <small class="text-dark">{{ $t->penanggungJawab->name }}</small>
                                    <small class="text-muted d-block">{{ $t->penanggungJawab->jabatan_label }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-gold">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">Tidak ada berkas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tikets->links() }}
        </div>
    </div>
@endsection
