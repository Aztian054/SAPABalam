@extends('layouts.app')

@section('title', 'Loket — Daftar Berkas')

@section('content')
<div id="page-data" data-modal-on-error="{{ $errors->any() ? 'modalTambahTiket' : '' }}" style="display:none"></div>
<div class="row g-3 mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-1">Loket Penerimaan</h4>
            <p class="text-muted small mb-0">
                Selamat datang, <strong>{{ auth()->user()->name }}</strong> &mdash; daftar berkas yang Anda daftarkan / Anda pegang.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-gold btn-sm rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahTiket">
                <i class="bi bi-plus-circle me-1"></i> Daftarkan Tiket Baru
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
                <i class="bi bi-printer me-1"></i> Rekap Laporan
            </a>
        </div>
    </div>
</div>

<!-- Statistik Saya -->
@php
    $totalSaya   = $tikets->total();
    $diproses    = $tikets->whereIn('status', ['diterima','verifikasi','warkah','validasi_btel','validasi_suel','alih_media_btel','alih_media_suel','dikembalikan'])->count();
    $selesaiSaya = $tikets->where('status', 'selesai')->count();
    $dibatalkan  = $tikets->where('status', 'batal')->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Berkas Saya</div>
                    <h3 class="fw-bold my-1 text-dark">{{ number_format($totalSaya) }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle"><i class="bi bi-ticket-perforated-fill fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Dalam Proses</div>
                    <h3 class="fw-bold my-1 text-warning">{{ number_format($diproses) }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle"><i class="bi bi-hourglass-split fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Selesai</div>
                    <h3 class="fw-bold my-1 text-success">{{ number_format($selesaiSaya) }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle"><i class="bi bi-patch-check-fill fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Revisi Pending</div>
                    <h3 class="fw-bold my-1 text-danger">{{ number_format($revisiBelumDiproses->count()) }}</h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle"><i class="bi bi-arrow-counterclockwise fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Peringatan revisi belum diproses -->
@if($revisiBelumDiproses->isNotEmpty())
    <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex flex-wrap align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div class="flex-grow-1">
            <strong>{{ $revisiBelumDiproses->count() }} berkas revisi masih menunggu diproses. </strong>
            Perbaikan dari pemohon perlu dikirim ulang ke tahap asal.
        </div>
        @foreach($revisiBelumDiproses as $rt)
            <a href="{{ route('loket.show', $rt->id) }}" class="btn btn-sm btn-outline-danger rounded-3 px-3">
                <i class="bi bi-ticket me-1"></i> {{ $rt->kode_tiket }}
            </a>
        @endforeach
    </div>
@endif
<!-- Daftar berkas -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Daftar Berkas Penerimaan</h6>
        <form method="GET" class="d-flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari kode tiket / nama / NIK...">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                @foreach(['diterima'=>'Diterima','verifikasi'=>'Verifikasi','warkah'=>'Warkah','validasi_btel'=>'Validasi BT','validasi_suel'=>'Validasi SU','alih_media_btel'=>'Alih Media BT','alih_media_suel'=>'Alih Media SU','selesai'=>'Selesai','dikembalikan'=>'Revisi','batal'=>'Batal'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-gold rounded-3 px-3" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
                <a href="{{ route('loket.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Tiket</th>
                    <th>Pemohon</th>
                    <th>Jenis Permohonan</th>
                    <th class="text-center">Bidang</th>
                    <th>Status</th>
                    <th>Tgl Masuk</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $t)
                    <tr>
                        <td>
                            <a href="{{ route('loket.show', $t->id) }}" class="fw-bold text-primary text-decoration-none">{{ $t->kode_tiket }}</a>
                            @if($t->status == 'dikembalikan')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1 d-block w-fit">Revisi</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $t->nama_pemohon }}</div>
                            <small class="text-muted">{{ $t->nik_pemohon ?? '-' }} &bull; {{ $t->no_hp_pemohon ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $t->jenisPermohonan->kode }}</span>
                            <small class="d-block text-truncate text-muted cell-label">{{ $t->jenisPermohonan->nama }}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-dark-subtle text-dark">{{ $t->bidangTanahs->count() }}</span></td>
                        <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                        <td>
                            <small>{{ $t->created_at?->format('d/m/Y H:i') }}</small>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('loket.show', $t->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('loket.printReceipt', $t->id) }}" class="btn btn-sm btn-outline-success py-1 px-2" target="_blank" title="Cetak tanda terima"><i class="bi bi-receipt"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada berkas. <button type="button" class="btn btn-link p-0 align-baseline text-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTiket">Daftarkan tiket pertama</button>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tikets->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">Menampilkan {{ $tikets->firstItem() ?? 0 }}–{{ $tikets->lastItem() ?? 0 }} dari {{ $tikets->total() }} berkas</span>
                {{ $tikets->links() }}
            </div>
        </div>
    @endif
</div>
{{-- MODAL DAFTARKAN BERKAS BARU --}}
<div class="modal fade modal-fullscreen-lg-down" id="modalTambahTiket" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white py-3" style="background: var(--bpn-gold);">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Daftarkan Tiket / Berkas Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('loket.store') }}" id="formLoket">
                @csrf
<div class="modal-body">
                    <p class="text-muted small mb-3">Nomor tiket diisi <strong>manual</strong> (format bebas). Berkas langsung masuk <strong>Database Admin</strong>.</p>
                    @if($errors->any())
                        <div class="alert alert-danger border-0 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Input tidak valid:</strong>
                            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Permohonan <span class="text-danger">*</span></label>
                            <select name="jenis_permohonan_id" id="jenisPermohonanSelect" class="form-select @error('jenis_permohonan_id') is-invalid @enderror" required>
                                <option value="">— Pilih —</option>
                                @foreach($jenisPermohonans as $jp)
                                    <option value="{{ $jp->id }}" @selected(old('jenis_permohonan_id') == $jp->id)>{{ $jp->kode }} — {{ $jp->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_permohonan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kode Tiket <span class="text-danger">*</span></label>
                            <input type="text" name="kode_tiket" value="{{ old('kode_tiket') }}" class="form-control @error('kode_tiket') is-invalid @enderror" placeholder="Contoh: BLM/2026/0001" required maxlength="50">
                            @error('kode_tiket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Diisi manual, format bebas.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Pemohon <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" class="form-control @error('nama_pemohon') is-invalid @enderror" placeholder="Nama lengkap pemohon" required maxlength="200">
                            @error('nama_pemohon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIK Pemohon</label>
                            <input type="text" name="nik_pemohon" value="{{ old('nik_pemohon') }}" class="form-control" placeholder="16 digit NIK" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. HP Pemohon <span class="text-danger">*</span></label>
                            <input type="text" name="no_hp_pemohon" value="{{ old('no_hp_pemohon') }}" class="form-control @error('no_hp_pemohon') is-invalid @enderror" placeholder="08xxxxxxxxxx" required maxlength="20">
                            @error('no_hp_pemohon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. Hak Sekarang</label>
                            <input type="text" name="no_hak_sekarang" value="{{ old('no_hak_sekarang') }}" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">No. Hak Sebelumnya</label>
                            <input type="text" name="no_hak_sebelumnya" value="{{ old('no_hak_sebelumnya') }}" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kelurahan / Desa</label>
                            <input type="text" name="kelurahan_desa" value="{{ old('kelurahan_desa') }}" class="form-control" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan') }}" class="form-control" maxlength="100">
                        </div>
                    </div>
                <div class="border-top pt-3 mt-3">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3 text-primary me-2"></i>Data Bidang Tanah</h6>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <label class="form-label small fw-semibold mb-0">Jumlah Bidang</label>
                        <input type="number" name="jumlah_bidang" id="jumlahBidang" min="1" value="{{ old('jumlah_bidang', 1) }}" class="form-control form-control-sm w-auto" required>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="loketSyncBidangRows()"><i class="bi bi-arrow-repeat"></i> Sesuaikan</button>
                    </div>
                    <div id="bidangContainer"></div>
                    @error('jumlah_bidang')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @error('bidang')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
                <div class="border-top pt-3 mt-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check2-square text-success me-2"></i>Persyaratan Dokumen</h6>
                    <div id="persyaratanList" class="text-muted small">— Pilih jenis permohonan —</div>
                    <div class="alert alert-warning border-0 p-2 small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>Dokumen asli diperiksa petugas loket dan dikembalikan ke pemohon.
                    </div>
                </div>
                {{-- Satu .modal-body saja: modal-dialog-scrollable hanya menggulir satu body; footer harus sibling agar tombol Simpan selalu terlihat. --}}
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal"><i class="bi bi-arrow-left me-1"></i> Kembali</button>
                    <button type="submit" class="btn btn-gold rounded-3 px-4 shadow-sm"><i class="bi bi-check2-circle me-1"></i> Daftarkan Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@php
    $jenisData = $jenisPermohonans->mapWithKeys(fn ($jp) => [$jp->id => [
        'id' => $jp->id, 'kode' => $jp->kode, 'nama' => $jp->nama,
        'persyaratan' => $jp->persyaratanDokumens->map(fn ($pd) => ['nama' => $pd->nama_dokumen, 'wajib' => (bool) $pd->wajib])->values(),
    ]]);
@endphp

@endsection

@section('scripts')
<script type="application/json" id="loketJenisDataJson">@json($jenisData)</script>
<script type="application/json" id="loketOldBidangJson">@json(old('bidang', []))</script>
<script type="application/json" id="loketJenisHaksJson">@json($jenisHaks->map(fn ($jh) => ['kode' => $jh->kode, 'nama' => $jh->nama])->values())</script>
<script>
    const LOKET_JENIS_DATA = JSON.parse(document.getElementById('loketJenisDataJson').textContent);
    const LOKET_JENIS_HAKS = JSON.parse(document.getElementById('loketJenisHaksJson').textContent);
    let loketOldBidang = JSON.parse(document.getElementById('loketOldBidangJson').textContent);

    function loketRenderPersyaratan() {
        const sel = document.getElementById('jenisPermohonanSelect');
        const list = document.getElementById('persyaratanList');
        const jp = LOKET_JENIS_DATA[sel.value];
        if (!jp) { list.innerHTML = '— Pilih jenis permohonan —'; return; }
        if (!jp.persyaratan.length) { list.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Tidak ada dokumen persyaratan khusus.</span>'; return; }
        let html = '<ul class="list-unstyled mb-0">';
        jp.persyaratan.forEach(p => {
            html += '<li class="d-flex align-items-center gap-2 py-1"><i class="bi bi-' + (p.wajib ? 'file-earmark-check text-danger' : 'file-earmark-minus text-muted') + '"></i> ' + p.nama + (p.wajib ? ' <span class="badge bg-danger-subtle text-danger">wajib</span>' : '') + '</li>';
        });
        list.innerHTML = html + '</ul>';
    }

    function loketBidangRowHTML(idx, v) {
        v = v || {};
        const opt = '<option value="">— Pilih —</option>' + LOKET_JENIS_HAKS.map(h => '<option value="' + h.kode + '"' + (v.jenis_hak === h.kode ? ' selected' : '') + '>' + h.kode + ' — ' + h.nama + '</option>').join('');
        return '<div class="border rounded-3 p-3 mb-3 bidang-row"><div class="row g-2">'
            + '<div class="col-md-2"><label class="form-label small fw-semibold mb-1 text-primary">Bidang ' + (idx + 1) + '</label></div>'
            + '<div class="col-md-3"><label class="form-label small fw-semibold mb-1">NIB</label><input type="text" name="bidang[' + idx + '][nib]" value="' + (v.nib || '') + '" class="form-control form-control-sm" maxlength="50"></div>'
            + '<div class="col-md-3"><label class="form-label small fw-semibold mb-1">No. Sertifikat Lama</label><input type="text" name="bidang[' + idx + '][no_sertifikat_lama]" value="' + (v.no_sertifikat_lama || '') + '" class="form-control form-control-sm" maxlength="100"></div>'
            + '<div class="col-md-2"><label class="form-label small fw-semibold mb-1">Jenis Hak</label><select name="bidang[' + idx + '][jenis_hak]" class="form-select form-select-sm">' + opt + '</select></div>'
            + '<div class="col-md-3"><label class="form-label small fw-semibold mb-1">Pemegang Hak</label><input type="text" name="bidang[' + idx + '][nama_pemegang_hak]" value="' + (v.nama_pemegang_hak || '') + '" class="form-control form-control-sm" maxlength="200"></div>'
            + '<div class="col-md-2"><label class="form-label small fw-semibold mb-1">Desa/Kelurahan</label><input type="text" name="bidang[' + idx + '][desa_kelurahan]" value="' + (v.desa_kelurahan || '') + '" class="form-control form-control-sm" maxlength="100"></div>'
            + '<div class="col-md-2"><label class="form-label small fw-semibold mb-1">Kecamatan</label><input type="text" name="bidang[' + idx + '][kecamatan]" value="' + (v.kecamatan || '') + '" class="form-control form-control-sm" maxlength="100"></div>'
            + '</div></div>';
    }

    function loketSyncBidangRows() {
        const container = document.getElementById('bidangContainer');
        const inp = document.getElementById('jumlahBidang');
        let n = Math.max(1, parseInt(inp.value, 10) || 1);
        inp.value = n;
        const old = Array.isArray(loketOldBidang) ? loketOldBidang : [];
        let html = '';
        for (let i = 0; i < n; i++) html += loketBidangRowHTML(i, old[i] || {});
        container.innerHTML = html;
        loketUpdateBidangCounter();
    }

    function loketUpdateBidangCounter() {
        const n = document.querySelectorAll('#bidangContainer .bidang-row').length;
        const inp = document.getElementById('jumlahBidang');
        if (inp && parseInt(inp.value, 10) !== n) inp.value = Math.max(1, n);
    }

    document.addEventListener('DOMContentLoaded', function () {
        loketRenderPersyaratan();
        loketSyncBidangRows();
        document.getElementById('jenisPermohonanSelect').addEventListener('change', loketRenderPersyaratan);
        document.getElementById('jumlahBidang').addEventListener('change', loketSyncBidangRows);
        document.getElementById('formLoket').addEventListener('submit', function (e) {
            if (document.querySelectorAll('#bidangContainer .bidang-row').length < 1) {
                e.preventDefault();
                alert('Minimal satu bidang tanah harus diisi.');
            }
        });
        var pageData = document.getElementById('page-data');
        var modalOnError = pageData ? pageData.getAttribute('data-modal-on-error') : '';
        if (modalOnError) {
            var modal = new bootstrap.Modal(document.getElementById(modalOnError));
            modal.show();
        }
    });
</script>
@endsection