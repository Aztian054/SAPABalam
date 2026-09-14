@extends('layouts.app')

@section('title', 'Admin — Daftar Tiket (Database)')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-database-fill-add text-primary me-2"></i>Daftar Tiket (Database)</h4>
        <a href="{{ route('admin.create_tiket') }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus-circle me-1"></i>Tambah Tiket
        </a>
    </div>

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total Tiket', 'value' => $stats['total'], 'icon' => 'ticket-perforated-fill', 'color' => 'primary'],
                ['label' => 'Menunggu di DB', 'value' => $stats['menunggu'], 'icon' => 'hourglass-split', 'color' => 'warning'],
                ['label' => 'Sedang Diproses', 'value' => $stats['proses'], 'icon' => 'gear-wide-connected', 'color' => 'info'],
                ['label' => 'Selesai', 'value' => $stats['selesai'], 'icon' => 'check2-circle', 'color' => 'success'],
                ['label' => 'Revisi', 'value' => $stats['dikembalikan'], 'icon' => 'arrow-counterclockwise', 'color' => 'danger'],
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
                    <input type="text" name="q" class="form-control" placeholder="Cari kode tiket / pemohon / NIK..." value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['diterima','verifikasi','warkah','validasi_btel','validasi_suel','alih_media_btel','alih_media_suel','selesai','dikembalikan','batal'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>Pemohon</th><th>Jenis</th><th>Masuk</th><th>BT/SU</th><th>PIC Warkah</th><th>Sertipikat</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tikets as $t)
                        <tr>
                            <td><a href="{{ route('admin.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a></td>
                            <td>{{ $t->nama_pemohon }}</td>
                            <td><small>{{ $t->jenisPermohonan?->nama }}</small></td>
                            <td><small>{{ $t->tanggal_masuk?->format('d/m/Y') }}</small></td>
                            <td>
                                <span class="badge bg-light border text-{{ $t->status_pra_btel === 'selesai' ? 'success' : 'secondary' }}">BT {{ $t->status_pra_btel }}</span>
                                <span class="badge bg-light border text-{{ $t->status_pra_suel === 'selesai' ? 'success' : 'secondary' }}">SU {{ $t->status_pra_suel }}</span>
                            </td>
                            <td><small class="text-muted">{{ $t->monitor_warkah ?? '-' }}</small></td>
                            <td>
                                @if($t->monitor_sertipikat)
                                    <span class="badge bg-dark-subtle text-dark">{{ ucwords(str_replace('_', ' ', $t->monitor_sertipikat)) }}</span>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">Tidak ada tiket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tikets->links() }}
        </div>
    </div>
@endsection