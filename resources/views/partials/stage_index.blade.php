@php
    // Peta nama tahap di DB (tiket_penugasan.stage) → prefix nama route:
    // tahap 'verifikasi' dilayani route 'verifikator.*', 'validasi_btel' → 'validator_btel.*', dst.
    $routeBase = [
        'verifikasi' => 'verifikator',
        'validasi_btel' => 'validator_btel',
        'validasi_suel' => 'validator_suel',
    ][$stage ?? ''] ?? $stage ?? '';
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center">
            <div class="fw-bold fs-4 text-primary">{{ $stats['total_db'] }}</div>
            <small class="text-muted">Tersedia di DB Admin</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center">
            <div class="fw-bold fs-4 text-success">{{ $stats['active'] }}</div>
            <small class="text-muted">Antrian Aktif Saya</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center">
            <div class="fw-bold fs-4 text-info">{{ $stats['history'] }}</div>
            <small class="text-muted">Pernah Diproses</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center">
            <div class="fw-bold fs-4 text-success">{{ $stats['lengkap'] }}</div>
            <small class="text-muted">Berkas Selesai</small>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="card card-custom p-3 text-center">
            <div class="fw-bold fs-4 text-warning">{{ $stats['revisi'] }}</div>
            <small class="text-muted">Perlu Revisi</small>
        </div>
    </div>
</div>

<div class="alert alert-primary d-flex align-items-center border-0 shadow-sm">
    <i class="bi bi-database-fill-add fs-4 me-3"></i>
    <div class="w-100">
        <strong>Pola Kerja {{ $stageLabel }}</strong>
        <span class="d-block small">Cari berkas di Database Admin <span class="badge bg-primary rounded-pill">{{ $stats['total_db'] }}</span> berkas tersedia →
            klik <b>Add</b> untuk mengambil → proses → <b>Proses Selesai</b> → kembali ke DB Admin.</span>
    </div>
</div>

@if(isset($revisiMenunggu) && $revisiMenunggu->isNotEmpty())
    <div class="card card-custom mb-4 border-start border-4 border-danger">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-arrow-repeat text-danger me-2"></i>Revisi Menunggu Saya</h6>
            <span class="badge bg-danger rounded-pill">{{ $revisiMenunggu->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>Pemohon</th><th>Catatan Revisi</th><th>Revisi Ke</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revisiMenunggu as $rt)
                        @php $cr = $rt->catatanRevisis->where('sudah_diproses', false)->last(); @endphp
                        <tr>
                            <td><a href="{{ route($routeBase . '.show', $rt->id) }}" class="text-decoration-none fw-semibold">{{ $rt->kode_tiket }}</a></td>
                            <td>{{ $rt->nama_pemohon }}</td>
                            <td>
                                <small class="text-muted">{{ Str::limit($cr?->isi_revisi ?? '-', 80) }}</small>
                            </td>
                            <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle">Pembetulan ke-{{ $cr?->revisi_ke ?? '-' }}</span></td>
                            <td class="text-end">
                                <form action="{{ route($routeBase . '.add', $rt->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Ambil dari menu Revisi untuk diperbaiki">
                                        <i class="bi bi-plus-circle me-1"></i>Ambil Revisi
                                    </button>
                                </form>
                                <a href="{{ route($routeBase . '.show', $rt->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-search text-primary me-2"></i>Smart Search — Database Admin</h6>
    </div>
    <div class="card-body">
        <form action="{{ route($routeBase . '.search') }}" method="GET" class="row g-2">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Cari kode tiket, nama pemohon, NIK, atau no. HP..." value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-gold"><i class="bi bi-search me-1"></i>Cari & Add</button>
            </div>
        </form>
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-collection-fill text-success me-2"></i>Antrian Aktif Saya</h6>
        @if($activeTikets->isNotEmpty())
            <span class="badge bg-success rounded-pill">{{ $activeTikets->count() }}</span>
        @endif
    </div>
    @if($activeTikets->isEmpty())
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada berkas dalam antrian Anda.<br>
            <small>Gunakan Smart Search di atas lalu klik <b>Add</b> untuk mengambil berkas dari Database Admin.</small>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>Pemohon</th><th>Jenis Permohonan</th><th>Di-Add</th><th>Status</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeTikets as $t)
                        <tr>
                            <td><a href="{{ route($routeBase . '.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a></td>
                            <td>{{ $t->nama_pemohon }}</td>
                            <td><small>{{ $t->jenisPermohonan?->nama }}</small></td>
                            <td><small>{{ $t->penugasans->where('status', 'proses')->first()?->tanggal_add?->format('d/m/Y H:i') }}</small></td>
                            <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route($routeBase . '.show', $t->id) }}" class="btn btn-sm btn-gold"><i class="bi bi-pencil-square me-1"></i>Proses</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($history->isNotEmpty())
<div class="card card-custom">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i>Riwayat Diproses (Terakhir)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Kode Tiket</th><th>Pemohon</th><th>Status Penugasan</th><th>Detail</th></tr>
            </thead>
            <tbody>
                @foreach($history as $t)
                    <tr>
                        <td><a href="{{ route($routeBase . '.show', $t->id) }}" class="text-decoration-none">{{ $t->kode_tiket }}</a></td>
                        <td>{{ $t->nama_pemohon }}</td>
                        <td>
                            @php $p = $t->penugasans->last(); @endphp
                            <span class="badge bg-{{ $p?->status === 'selesai' ? 'success' : ($p?->status === 'proses' ? 'warning' : 'secondary') }}">{{ $p?->status ?? '-' }}</span>
                        </td>
                        <td><a href="{{ route($routeBase . '.show', $t->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
    </div>
</div>