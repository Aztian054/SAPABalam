@extends('layouts.app')

@section('title', 'Admin — Berkas Selesai')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Berkas Selesai</h4>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
            <i class="bi bi-eye me-1"></i>Mode Monitoring
        </span>
    </div>

    {{-- ═══ Filter Card ═══ --}}
    <div class="card card-custom mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Cari</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Kode / no. berkas / pemohon / NIK…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Tahun Selesai</label>
                    <select name="tahun" class="form-select form-select-sm">
                        <option value="">Semua Tahun</option>
                        @foreach($tahuns as $th)
                            <option value="{{ $th }}" @selected(request('tahun') == $th)>{{ $th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Jenis Permohonan</label>
                    <select name="jenis_permohonan_id" class="form-select form-select-sm">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisPermohonans as $jp)
                            <option value="{{ $jp->id }}" @selected(request('jenis_permohonan_id') == $jp->id)>{{ $jp->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Jenis Hak</label>
                    <select name="jenis_hak" class="form-select form-select-sm">
                        <option value="">Semua Hak</option>
                        @foreach($jenisHaks as $jh)
                            <option value="{{ $jh->kode }}" @selected(request('jenis_hak') == $jh->kode)>{{ $jh->kode }} — {{ $jh->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Petugas</label>
                    <select name="petugas" class="form-select form-select-sm">
                        <option value="">Semua Petugas</option>
                        @foreach($petugasList as $p)
                            <option value="{{ $p->id }}" @selected(request('petugas') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-gold btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="{{ route('admin.selesai') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter"><i class="bi bi-x-lg"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ Table Card ═══ --}}
    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tblSelesai">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>No. Berkas</th><th>Pemohon</th><th>Jenis</th><th>Hak</th><th>Selesai</th><th>Arsip</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tikets as $t)
                        @php $alreadyArsip = $t->arsips->isNotEmpty(); @endphp
                        <tr>
                            <td><a href="{{ route('admin.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a></td>
                            <td>{{ $t->nama_pemohon }}</td>
                            <td><small>{{ $t->jenisPermohonan?->nama }}</small></td>
                            <td>
                                @forelse($t->bidangTanahs as $b)
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $b->jenis_hak }}</span>
                                @empty
                                    <small class="text-muted">—</small>
                                @endforelse
                            </td>
                            <td><small>{{ $t->tanggal_selesai?->format('d/m/Y') ?? '-' }}</small></td>
                            <td><small>{{ $t->no_berkas ?: '—' }}</small></td>
                            <td>
                                @if($alreadyArsip)
                                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-archive me-1"></i>Diarsipkan</span>
                                @else
                                    <span class="badge bg-light border text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-gold">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">Belum ada berkas selesai sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div class="text-muted small">Menampilkan {{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }} berkas</div>
            {{ $tikets->links() }}
        </div>
    </div>
@endsection
