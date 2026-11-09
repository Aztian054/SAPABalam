@extends('layouts.app')

@section('title', 'Admin — Log Aktivitas')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-journal-text text-primary me-2"></i>Log Aktivitas</h4>
            <p class="text-muted small mb-0">
                Audit perubahan penting (akun, setting, lepas tiket) dan aktivitas masuk/keluar.
                Retensi {{ $retensiHari }} hari. Aktivitas Super Admin tidak dicatat.
            </p>
        </div>
    </div>

    <form method="GET" class="card card-custom mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Cari pelaku / objek / detail</label>
                    <input type="text" name="cari" value="{{ request('cari') }}" class="form-control" placeholder="mis. loket_baru">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Aksi</label>
                    <select name="aksi" class="form-select">
                        <option value="">Semua aksi</option>
                        @foreach ($daftarAksi as $aksi)
                            <option value="{{ $aksi }}" @selected(request('aksi') === $aksi)>{{ ucwords(strtolower($aksi)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Dari tanggal</label>
                    <input type="date" name="dari" value="{{ request('dari') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sampai tanggal</label>
                    <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-bpn flex-grow-1"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                    <a href="{{ route('admin.log') }}" class="btn btn-light border">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:150px;">Waktu</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Objek</th>
                        <th>Detail</th>
                        <th style="width:120px;">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <div>{{ $log->created_at?->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $log->created_at?->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->nama_pelaku }}</div>
                                @if ($log->jabatan_pelaku_label !== '')
                                    <small class="text-muted">{{ $log->jabatan_pelaku_label }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-info text-dark">{{ $log->aksi_label }}</span></td>
                            <td class="fw-semibold">{{ $log->objek ?? '—' }}</td>
                            <td class="small" style="min-width:220px;">{{ $log->detail ?? '—' }}</td>
                            <td class="small text-muted">{{ $log->ip ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada log yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white border-0 py-2">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
