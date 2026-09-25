@extends('layouts.app')

@section('title', 'Admin — Daftar Berkas (Database)')

@section('content')
<div id="page-data" data-modal-on-error="{{ $errors->any() ? 'modalTambahTiket' : '' }}" style="display:none"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-database-fill-add text-primary me-2"></i>Daftar Berkas (Database)</h4>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahTiket">
            <i class="bi bi-plus-circle me-1"></i>Tambah Berkas
        </button>
    </div>

    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total Berkas', 'value' => $stats['total'], 'icon' => 'ticket-perforated-fill', 'color' => 'primary'],
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
                                <a href="{{ route('admin.show', $t->id) }}" class="btn btn-sm btn-gold"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">Tidak ada berkas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $tikets->links() }}
        </div>
    </div>
{{-- MODAL TAMBAH BERKAS --}}
    <div class="modal fade" id="modalTambahTiket" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Berkas (Database Admin)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.store_tiket') }}">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Nomor tiket diisi <strong>manual</strong>. Berkas masuk ke Database Admin.</p>
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
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Pemohon <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" class="form-control @error('nama_pemohon') is-invalid @enderror" required maxlength="200">
                                @error('nama_pemohon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold small">NIK Pemohon</label><input type="text" name="nik_pemohon" value="{{ old('nik_pemohon') }}" class="form-control" maxlength="20"></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">No. HP Pemohon <span class="text-danger">*</span></label>
                                <input type="text" name="no_hp_pemohon" value="{{ old('no_hp_pemohon') }}" class="form-control @error('no_hp_pemohon') is-invalid @enderror" required maxlength="20">
                                @error('no_hp_pemohon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email Pemohon</label>
                                <input type="email" name="email_pemohon" value="{{ old('email_pemohon') }}" class="form-control @error('email_pemohon') is-invalid @enderror" placeholder="contoh@email.com" maxlength="255">
                                @error('email_pemohon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="form-text text-muted">Opsional. Digunakan untuk notifikasi revisi/perbaikan berkas.</small>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-semibold small">No. Hak Sekarang</label><input type="text" name="no_hak_sekarang" value="{{ old('no_hak_sekarang') }}" class="form-control" maxlength="100"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold small">No. Hak Sebelumnya</label><input type="text" name="no_hak_sebelumnya" value="{{ old('no_hak_sebelumnya') }}" class="form-control" maxlength="100"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold small">Kelurahan/Desa</label><input type="text" name="kelurahan_desa" value="{{ old('kelurahan_desa') }}" class="form-control" maxlength="100"></div>
                            <div class="col-md-6"><label class="form-label fw-semibold small">Kecamatan</label><input type="text" name="kecamatan" value="{{ old('kecamatan') }}" class="form-control" maxlength="100"></div>
                        </div>
                    </div>
<div class="modal-body border-top">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diagram-3 text-primary me-2"></i>Data Bidang Tanah</h6>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <label class="form-label small fw-semibold mb-0">Jumlah Bidang</label>
                            <input type="number" name="jumlah_bidang" id="jumlahBidang" min="1" value="{{ old('jumlah_bidang', 1) }}" class="form-control form-control-sm w-auto" required>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="adminSyncBidangRows()"><i class="bi bi-arrow-repeat"></i> Sesuaikan</button>
                        </div>
                        <div id="bidangContainer"></div>
                        @error('jumlah_bidang')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @error('bidang')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>
                    <div class="modal-body border-top">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check2-square text-success me-2"></i>Persyaratan Dokumen</h6>
                        <div id="persyaratanList" class="text-muted small">— Pilih jenis permohonan —</div>
                        <div class="alert alert-info border-0 p-2 small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Berkas berstatus <b>Diterima</b> di DB Admin.</div>
                    </div>
                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-3 px-4 shadow-sm"><i class="bi bi-check2-circle me-1"></i> Tambahkan ke Database</button>
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
<script type="application/json" id="adminJenisDataJson">@json($jenisData)</script>
<script type="application/json" id="adminOldBidangJson">@json(old('bidang', []))</script>
<script type="application/json" id="adminJenisHaksJson">@json($jenisHaks->map(fn ($jh) => ['kode' => $jh->kode, 'nama' => $jh->nama])->values())</script>
<script>
    const ADMIN_JENIS_DATA = JSON.parse(document.getElementById('adminJenisDataJson').textContent);
    const ADMIN_JENIS_HAKS = JSON.parse(document.getElementById('adminJenisHaksJson').textContent);
    let adminOldBidang = JSON.parse(document.getElementById('adminOldBidangJson').textContent);

    function adminRenderPersyaratan() {
        const sel = document.getElementById('jenisPermohonanSelect');
        const list = document.getElementById('persyaratanList');
        const jp = ADMIN_JENIS_DATA[sel.value];
        if (!jp) { list.innerHTML = '— Pilih jenis permohonan —'; return; }
        if (!jp.persyaratan.length) { list.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Tidak ada persyaratan khusus.</span>'; return; }
        let html = '<ul class="list-unstyled mb-0">';
        jp.persyaratan.forEach(p => {
            html += '<li class="d-flex align-items-center gap-2 py-1"><i class="bi bi-' + (p.wajib ? 'file-earmark-check text-danger' : 'file-earmark-minus text-muted') + '"></i> ' + p.nama + (p.wajib ? ' <span class="badge bg-danger-subtle text-danger">wajib</span>' : '') + '</li>';
        });
        list.innerHTML = html + '</ul>';
    }

    function adminBidangRowHTML(idx, v) {
        v = v || {};
        const opt = '<option value="">— Pilih —</option>' + ADMIN_JENIS_HAKS.map(h => '<option value="' + h.kode + '"' + (v.jenis_hak === h.kode ? ' selected' : '') + '>' + h.kode + ' — ' + h.nama + '</option>').join('');
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

    function adminSyncBidangRows() {
        const container = document.getElementById('bidangContainer');
        const inp = document.getElementById('jumlahBidang');
        let n = Math.max(1, parseInt(inp.value, 10) || 1);
        inp.value = n;
        const old = Array.isArray(adminOldBidang) ? adminOldBidang : [];
        let html = '';
        for (let i = 0; i < n; i++) html += adminBidangRowHTML(i, old[i] || {});
        container.innerHTML = html;
        adminUpdateBidangCounter();
    }

    function adminUpdateBidangCounter() {
        const n = document.querySelectorAll('#bidangContainer .bidang-row').length;
        const inp = document.getElementById('jumlahBidang');
        if (inp && parseInt(inp.value, 10) !== n) inp.value = Math.max(1, n);
    }

    document.addEventListener('DOMContentLoaded', function () {
        adminRenderPersyaratan();
        adminSyncBidangRows();
        document.getElementById('jenisPermohonanSelect').addEventListener('change', adminRenderPersyaratan);
        document.getElementById('jumlahBidang').addEventListener('change', adminSyncBidangRows);
        var pageData = document.getElementById('page-data');
        var modalOnError = pageData ? pageData.getAttribute('data-modal-on-error') : '';
        if (modalOnError) {
            var modal = new bootstrap.Modal(document.getElementById(modalOnError));
            modal.show();
        }
    });
</script>
@endsection