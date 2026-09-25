@extends('layouts.app')

@section('title', 'Admin — Revisi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-repeat text-danger me-2"></i>Menu Revisi (Perbaikan Berkas)</h4>
        </div>

    <div class="alert alert-warning border-0 shadow-sm small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Berikut berkas berstatus <b>Dikembalikan</b> yang menunggu perbaikan. Revisi dapat dikirim balik lintas tahap
        (Loket → Verifikator → Warkah → Validator → Alih Media) dan <b>tidak dibatasi</b> jumlah iterasi pembetulan.
    </div>

    <div class="card card-custom mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" placeholder="Cari kode tiket / pemohon..." value="{{ request('q') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-gold"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th><th>Pemohon</th><th>Tujuan Revisi</th><th>Isi Revisi</th><th>Tgl Revisi</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tikets as $t)
                        @php $cr = $t->catatanRevisis->where('sudah_diproses', false)->last(); @endphp
                        <tr>
                            <td><a href="{{ route('admin.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a></td>
                            <td>{{ $t->nama_pemohon }}</td>
                            <td><span class="badge bg-dark-subtle text-dark">{{ ucwords(str_replace('_', ' ', $cr?->ke_stage ?? '-')) }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($cr?->isi_revisi ?? '-', 100) }}</small></td>
                            <td><small>{{ optional($cr?->tanggal_masuk)->format('d/m/Y H:i') }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-gold"><i class="bi bi-eye"></i></a>
                                <form action="{{ route('admin.revisi.hapus', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus revisi ini dan kembalikan berkas ke Database Admin?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada revisi yang menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tikets->links() }}
        </div>
    </div>
@endsection