@extends('layouts.app')

@section('title', 'Kakan — Monitoring & Evaluasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-clipboard-data text-primary me-2"></i>Monitoring &amp; Evaluasi</h4>
        <p class="text-muted small mb-0">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> &mdash; ringkasan layanan dan berkas yang sedang berjalan.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-printer me-1"></i> Rekap Laporan
        </a>
        <a href="{{ route('kakan.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Segarkan
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Total Berkas', $totalTiket, 'primary', 'folder-fill'],
            ['Di Database Admin', $statusCounts['di_database'], 'secondary', 'database-fill'],
            ['Sedang Diproses', $statusCounts['diproses'], 'warning', 'hourglass-split'],
            ['Ditolak', $statusCounts['ditolak'], 'danger', 'x-octagon-fill'],
            ['Selesai', $statusCounts['selesai'], 'success', 'patch-check-fill'],
        ];
    @endphp
    @foreach($kartu as [$label, $jumlah, $warna, $ikon])
        <div class="col-xl col-md-4 col-6">
            <div class="card card-custom p-3 h-100 border-start border-4 border-{{ $warna }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">{{ $label }}</small>
                        <h3 class="fw-bold text-{{ $warna === 'secondary' ? 'dark' : $warna }} mb-0">{{ number_format($jumlah) }}</h3>
                    </div>
                    <span class="bg-{{ $warna }} bg-opacity-10 text-{{ $warna }} rounded-circle p-3">
                        <i class="bi bi-{{ $ikon }} fs-4"></i>
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Beban Kerja per Seksi</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Seksi</th><th class="text-end">Berkas Diproses</th></tr></thead>
                    <tbody>
                        @foreach($perSeksi as $baris)
                            <tr>
                                <td class="fw-semibold">{{ $baris['label'] }}</td>
                                <td class="text-end">
                                    @if($baris['jumlah'] > 0)
                                        <span class="badge bg-warning text-dark">{{ $baris['jumlah'] }}</span>
                                    @else
                                        <span class="text-muted small">0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-activity text-primary me-2"></i>Aktivitas Terbaru</h6>
            </div>
            <div class="card-body py-2">
                @forelse($activities as $a)
                    <div class="d-flex gap-2 border-bottom py-2">
                        <i class="bi bi-arrow-left-right text-muted mt-1"></i>
                        <div class="small">
                            <span class="fw-semibold text-dark">{{ $a->tiket?->kode_tiket ?? '-' }}</span>
                            <span class="text-muted d-block">[{{ $a->stage_dari }} &rarr; {{ $a->stage_ke }}] {{ $a->keterangan }}</span>
                            <span class="text-secondary" style="font-size:0.7rem;">{{ $a->user?->name ?? 'Sistem' }} &bull; {{ $a->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('kakan.index') }}" class="card card-custom mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari no. tiket / no. berkas / pemohon&hellip;">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Semua Status —</option>
                    @foreach(['di_database' => 'Di Database Admin', 'diproses' => 'Sedang Diproses', 'ditolak' => 'Ditolak', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan'] as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="seksi" class="form-select form-select-sm">
                    <option value="">— Semua Seksi —</option>
                    @foreach(\App\Models\User::SEKSI as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('seksi') === (string) $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-gold btn-sm rounded-3 w-100"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
                @if(request()->hasAny('q', 'status', 'seksi'))
                    <a href="{{ route('kakan.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Reset</a>
                @endif
            </div>
        </div>
    </div>
</form>

<div class="card card-custom">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Daftar Berkas Permohonan</h6>
        <span class="badge bg-light border text-dark">{{ $tikets->total() }} berkas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Tiket</th>
                    <th>No. Berkas</th>
                    <th>Pemohon</th>
                    <th>Jenis Permohonan</th>
                    <th>Status</th>
                    <th>Pemegang</th>
                    <th>Masuk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->kode_tiket }}</td>
                        <td class="small">{{ $t->no_berkas ?: '—' }}</td>
                        <td>{{ $t->nama_pemohon }}</td>
                        <td class="small">{{ $t->jenisPermohonan?->nama ?? '-' }}</td>
                        <td><span class="badge bg-{{ $t->status_badge }} text-uppercase">{{ $t->status_label }}</span></td>
                        <td class="small">{{ $t->penanggungJawab?->name ?? '-' }}</td>
                        <td class="small">{{ $t->tanggal_masuk?->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('kakan.show', $t) }}" class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada berkas yang cocok dengan filter saat ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tikets->hasPages())
        <div class="card-body py-2">{{ $tikets->links() }}</div>
    @endif
</div>
@endsection
