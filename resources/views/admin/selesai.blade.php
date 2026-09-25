@extends('layouts.app')

@section('title', 'Admin — Berkas Selesai')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Berkas Selesai (Menunggu Arsip)</h4>
    </div>

    {{-- ═══ Filter Card ═══ --}}
    <div class="card card-custom mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Cari</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Kode / pemohon / NIK…" value="{{ request('q') }}">
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
                        <th class="col-check"><input type="checkbox" class="form-check-input" id="chkAll" title="Centang semua di halaman ini"></th>
                        <th>Kode Tiket</th><th>Pemohon</th><th>Jenis</th><th>Hak</th><th>Selesai</th><th>Pembetulan</th><th>Arsip</th><th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tikets as $t)
                        @php $alreadyArsip = $t->arsips->isNotEmpty(); @endphp
                        <tr>
                            <td><input type="checkbox" class="form-check-input tiket-cb" value="{{ $t->id }}" @if($alreadyArsip) disabled title="Sudah diarsip" @endif></td>
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
                            <td><span class="badge bg-success-subtle text-success">{{ $t->status_pembetulan }}</span></td>
                            <td>
                                @if($alreadyArsip)
                                    <span class="badge bg-primary-subtle text-primary"><i class="bi bi-archive me-1"></i>Diarsipkan</span>
                                @else
                                    <span class="badge bg-light border text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-gold"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">Belum ada berkas selesai sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div class="text-muted small">Menampilkan {{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }} berkas</div>
            {{ $tikets->links() }}
        </div>
    </div>
    {{-- ═══ Floating Bulk Action Bar ═══ --}}
    <div id="bulkBar" class="position-fixed bottom-0 start-0 w-100 bg-white border-top shadow-lg py-2 px-3 d-none" style="z-index:1050;">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="text-dark"><strong id="bulkCount">0</strong> berkas dipilih <span class="text-muted small ms-2">(yang sudah diarsip diabaikan)</span></span>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" onclick="openBulkModal('selected')"><i class="bi bi-archive me-1"></i>Arsipkan Terpilih</button>
                <button class="btn btn-gold btn-sm" onclick="openBulkModal('all')"><i class="bi bi-archive-fill me-1"></i>Arsipkan Semua Hasil Filter</button>
            </div>
        </div>
    </div>

    {{-- ═══ Modal Konfirmasi Arsip Massal ═══ --}}
    <div class="modal fade" id="bulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold" id="bulkModalLabel"><i class="bi bi-archive me-1"></i>Arsipkan Berkas Massal</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="bulkForm" method="POST" action="{{ route('admin.selesai.arsipkan-massal') }}">
                    @csrf
                    <input type="hidden" name="semua" id="bulkSemua" value="0">
                    <input type="hidden" name="ids" id="bulkIds">

                    {{-- Teruskan filter aktif untuk mode "Semua" --}}
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    @if(request('tahun'))<input type="hidden" name="tahun" value="{{ request('tahun') }}">@endif
                    @if(request('jenis_permohonan_id'))<input type="hidden" name="jenis_permohonan_id" value="{{ request('jenis_permohonan_id') }}">@endif
                    @if(request('jenis_hak'))<input type="hidden" name="jenis_hak" value="{{ request('jenis_hak') }}">@endif
                    @if(request('petugas'))<input type="hidden" name="petugas" value="{{ request('petugas') }}">@endif

                    <div class="modal-body">
                        <div id="bulkAlertInfo" class="alert alert-info py-2 small mb-3"></div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Folder <span class="text-danger">*</span></label>
                            <select name="folder_id" class="form-select" required>
                                <option value="">— Pilih Folder —</option>
                                @foreach($folders as $f)
                                    <option value="{{ $f->id }}">{{ $f->nama_folder }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Arsip <span class="text-danger">*</span></label>
                            <input type="text" name="nama_arsip" class="form-control" placeholder="Contoh: Arsip Agustus 2026" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Dokumen</label>
                                <input type="text" name="tipe" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gold" id="bulkSubmitBtn"><i class="bi bi-archive me-1"></i>Arsipkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chkAll = document.getElementById('chkAll');
    const cbs = document.querySelectorAll('.tiket-cb:not(:disabled)');
    const bulkBar = document.getElementById('bulkBar');
    const bulkCount = document.getElementById('bulkCount');
    const bulkIds = document.getElementById('bulkIds');
    const bulkSemua = document.getElementById('bulkSemua');

    function updateBar() {
        const checked = document.querySelectorAll('.tiket-cb:checked:not(:disabled)');
        const n = checked.length;
        bulkCount.textContent = n;
        bulkBar.classList.toggle('d-none', n === 0);
        bulkIds.value = [...checked].map(cb => cb.value).join(',');
    }

    chkAll.addEventListener('change', () => {
        cbs.forEach(cb => { cb.checked = chkAll.checked; });
        updateBar();
    });

    cbs.forEach(cb => cb.addEventListener('change', () => {
        chkAll.checked = [...cbs].every(c => c.checked);
        updateBar();
    }));
});

function openBulkModal(mode) {
    const bulkSemua = document.getElementById('bulkSemua');
    const bulkIds = document.getElementById('bulkIds');
    const info = document.getElementById('bulkAlertInfo');
    const checked = document.querySelectorAll('.tiket-cb:checked:not(:disabled)');
    const total = checked.length;

    if (mode === 'selected') {
        if (total === 0) { alert('Centang minimal satu berkas terlebih dahulu.'); return; }
        bulkSemua.value = '0';
        bulkIds.value = [...checked].map(cb => cb.value).join(',');
        info.innerHTML = `<i class="bi bi-info-circle me-1"></i>akan mengarsipkan <strong>${total}</strong> berkas terpilih.`;
    } else {
        bulkSemua.value = '1';
        bulkIds.value = '';
        info.innerHTML = `<i class="bi bi-info-circle me-1"></i>akan mengarsipkan <strong>semua berkas</strong> sesuai filter aktif (yang sudah diarsip diabaikan).`;
    }

    const modal = new bootstrap.Modal(document.getElementById('bulkModal'));
    modal.show();
}
</script>
@endsection
