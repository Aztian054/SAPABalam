@extends('layouts.app')

@section('title', 'Pemimpin — Monitoring & Evaluasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-clipboard-data text-primary me-2"></i>Monitoring &amp; Evaluasi</h4>
        <p class="text-muted small mb-0">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> &mdash; ringkasan layanan, beban kerja, dan berkas yang sedang berjalan.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-printer me-1"></i> Rekap Laporan
        </a>
        <a href="{{ route('pemimpin.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-clockwise me-1"></i> Segarkan
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Berkas Selesai</small>
                    <h3 class="fw-bold text-dark mb-0">{{ $selesaiTotal }}</h3>
                    <small class="text-muted">Total sertifikat elek. terbit</small>
                </div>
                <span class="badge bg-success rounded-circle p-3"><i class="bi bi-check2-circle fs-4 text-white"></i></span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Perlu Perbaikan</small>
                    <h3 class="fw-bold text-danger mb-0">{{ $stageCounts['dikembalikan'] ?? 0 }}</h3>
                    <small class="text-muted">Dikembalikan ke tahap asal</small>
                </div>
                <span class="badge rounded-circle p-3" style="background:#a3aab6;"><i class="bi bi-arrow-repeat fs-4 text-white"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Status Alur Layanan (Tahap Saat Ini)</h6>
    </div>
    <div class="card-body">
        @php
            $stages = [
                'diterima'      => ['Diterima / DB Admin', 'warning'],
                'verifikasi'    => ['Verifikasi Berkas', 'info'],
                'warkah'        => ['Pencarian & Data Warkah', 'secondary'],
                'validasi_btel' => ['Validasi Pra-BTel', 'primary'],
                'validasi_suel' => ['Validasi Pra-SuEl', 'primary'],
                'alih_media_btel' => ['Alih Media Pra-BTel', 'dark'],
                'alih_media_suel' => ['Alih Media Pra-SuEl', 'dark'],
                'selesai'       => ['Selesai', 'success'],
                'dikembalikan'  => ['Dikembalikan (Revisi)', 'danger'],
                'batal'         => ['Batal', 'danger'],
            ];
        @endphp
        <div class="row g-2">
            @foreach($stages as $kode => [$label, $warna])
                <div class="col-xl-6 col-xxl-4 col-6">
                    <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-1">
                        <span class="small fw-semibold text-dark">{{ $label }}</span>
                        <a href="{{ route('pemimpin.index', ['status' => $kode]) }}" class="badge bg-{{ $warna }} text-white">{{ $stageCounts[$kode] ?? 0 }}</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<form method="GET" action="{{ route('pemimpin.index') }}" class="card card-custom mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode tiket / nama pemohon&hellip;">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">— Semua Status —</option>
                    @foreach($stages as $kode => [$label, $warna])
                        <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-gold btn-sm rounded-3 w-100"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
                @if(request()->hasAny('q', 'status'))
                    <a href="{{ route('pemimpin.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Reset</a>
                @endif
            </div>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card card-custom h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people text-primary me-2"></i>Beban Kerja Petugas</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Petugas</th><th>Peran</th><th>Sedang Diproses</th><th>Selesai</th></tr>
                    </thead>
                    <tbody>
                        @forelse($beban as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td><span class="badge bg-light border text-dark">{{ $u->role_label }}</span></td>
                                <td>
                                    @if($u->beban_aktif > 0)
                                        <span class="badge bg-warning text-dark">{{ $u->beban_aktif }} proses</span>
                                    @else
                                        <span class="text-muted small">0</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success">{{ $u->total_selesai }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada petugas dengan penugasan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
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
<div class="card card-custom">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Daftar Berkas Permohonan</h6>
        <span class="badge bg-light border text-dark">{{ $tikets->count() }} berkas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Tiket</th>
                    <th>Pemohon</th>
                    <th>Jenis Permohonan</th>
                    <th>Status</th>
                    <th>Petugas Loket</th>
                    <th>Masuk</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->kode_tiket }}</td>
                        <td>{{ $t->nama_pemohon }}</td>
                        <td class="small">{{ $t->jenisPermohonan?->nama ?? '-' }}</td>
                        <td><span class="badge bg-{{ $t->status_badge }} text-uppercase">{{ $t->status_label }}</span></td>
                        <td class="small">{{ $t->petugasLoket?->name ?? '-' }}</td>
                        <td class="small">{{ $t->tanggal_masuk?->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('pemimpin.show', $t->id) }}" class="btn btn-outline-primary btn-sm rounded-3">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada berkas yang cocok dengan filter saat ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection