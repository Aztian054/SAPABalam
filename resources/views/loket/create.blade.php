@extends('layouts.app')

@section('title', 'Loket — Daftarkan Tiket Baru')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12">
        <h4 class="fw-bold mb-1">Daftarkan Tiket / Berkas Baru</h4>
        <p class="text-muted small mb-0">Nomor tiket diisi <strong>manual</strong> (format bebas). Tiket langsung masuk <strong>Database Admin</strong> dan menunggu di-Add tahap berikutnya.</p>
    </div>
</div>

<form method="POST" action="{{ route('loket.store') }}" id="formLoket">
    @csrf

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>Data Permohonan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Jenis Permohonan <span class="text-danger">*</span></label>
                            <select name="jenis_permohonan_id" id="jenisPermohonanSelect" class="form-select @error('jenis_permohonan_id') is-invalid @enderror" required>
                                <option value="">— Pilih Jenis Permohonan —</option>
                                @foreach($jenisPermohonans as $jp)
                                    <option value="{{ $jp->id }}" @selected(old('jenis_permohonan_id') == $jp->id)>{{ $jp->kode }} — {{ $jp->nama }}</option>
                                @endforeach
                            </select>
                            @error('jenis_permohonan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted" id="slaInfo"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nomor Tiket / Kode Tiket <span class="text-danger">*</span></label>
                            <input type="text" name="kode_tiket" value="{{ old('kode_tiket') }}" class="form-control @error('kode_tiket') is-invalid @enderror" placeholder="Contoh: BLM/2026/0001" required maxlength="50">
                            @error('kode_tiket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Diisi manual, format bebas (boleh memakai nomor tiket PPAT/non-PPAT).</small>
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
                            <label class="form-label fw-semibold small">Satuan Kerja</label>
                            <input type="text" name="satuan_kerja" value="{{ old('satuan_kerja') }}" class="form-control" placeholder="Nama satker pengguna BMN" maxlength="200">
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
                </div>
            </div>

<!-- Bidang Tanah -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3 text-primary me-2"></i>Data Bidang Tanah</h6>
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label small fw-semibold mb-0">Jumlah Bidang</label>
                        <input type="number" name="jumlah_bidang" id="jumlahBidang" min="1" value="{{ old('jumlah_bidang', 1) }}" class="form-control form-control-sm" style="width:80px;" required>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="syncBidangRows()"><i class="bi bi-arrow-repeat"></i> Sesuaikan</button>
                    </div>
                </div>
                <div class="card-body" id="bidangContainer">
                    <!-- Bidang rows rendered by JS -->
                </div>
                @error('jumlah_bidang')<div class="ps-3 pb-2 text-danger small">{{ $message }}</div>@enderror
                @error('bidang')<div class="ps-3 pb-2 text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="card card-custom mb-4">
                <div class="card-body">
                    <label class="form-label fw-semibold small">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="form-control" placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('loket.index') }}" class="btn btn-light rounded-3 px-4">Batal</a>
                        <button type="submit" class="btn btn-gold rounded-3 px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Daftarkan Tiket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN: Persyaratan -->
        <div class="col-lg-5">
            <div class="card card-custom mb-4" style="position:sticky;top:80px;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-check2-square text-success me-2"></i>Persyaratan Dokumen</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Pilih jenis permohonan untuk melihat daftar persyaratan yang harus diserahkan pemohon.</p>
                    <div id="persyaratanList" class="text-muted small">— Belum ada jenis permohonan dipilih —</div>
                    <p class="text-muted mt-3 mb-3">
                        <i class="bi bi-info-circle me-1"></i>Dokumen asli diperiksa petugas loket dan dikembalikan ke pemohon. Salinan dilegalisir diarsipkan bersama berkas.
                    </p>
                    <div class="alert alert-warning border-0 p-3 small mb-0">
                        <i class="bi bi-clock me-1"></i> <span id="slaBadgeText">Target penyelesaian mengikuti SLA jenis permohonan.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
@php
    $jenisData = $jenisPermohonans->mapWithKeys(function ($jp) {
        return [$jp->id => [
            'id'          => $jp->id,
            'kode'        => $jp->kode,
            'nama'        => $jp->nama,
            'sla'         => $jp->batas_hari_sla ?? 0,
            'persyaratan' => $jp->persyaratanDokumens->map(function ($pd) {
                return [
                    'nama'  => $pd->nama_dokumen,
                    'wajib' => (bool) $pd->wajib,
                ];
            })->values(),
        ]];
    });
@endphp
<script type="application/json" id="jenisDataJson">
@json($jenisData)
</script>
<script type="application/json" id="oldBidangJson">
@json(old('bidang', []))
</script>

<script>
// Data jenis permohonan diambil dari JSON island di atas (aman untuk linter JS)
    const JENIS_DATA = JSON.parse(document.getElementById('jenisDataJson').textContent);

    function renderPersyaratan() {
        const sel = document.getElementById('jenisPermohonanSelect');
        const list = document.getElementById('persyaratanList');
        const jp = JENIS_DATA[sel.value];
        if (!jp) {
            list.innerHTML = '— Belum ada jenis permohonan dipilih —';
            document.getElementById('slaInfo').textContent = '';
            document.getElementById('slaBadgeText').textContent = 'Target penyelesaian mengikuti SLA jenis permohonan.';
            return;
        }
        document.getElementById('slaInfo').innerHTML = 'SLA penyelesaian: <strong>' + jp.sla + ' hari kerja</strong> (target sejak tanggal masuk).';
        document.getElementById('slaBadgeText').textContent = 'Target penyelesaian ± ' + jp.sla + ' hari kerja setelah berkas dinyatakan lengkap.';
        if (!jp.persyaratan.length) {
            list.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Tidak ada dokumen persyaratan khusus.</span>';
            return;
        }
        let html = '<ul class="list-unstyled mb-0">';
        jp.persyaratan.forEach(p => {
            html += '<li class="d-flex align-items-center gap-2 py-1"><i class="bi bi-' + (p.wajib ? 'file-earmark-check text-danger' : 'file-earmark-minus text-muted') + '"></i> ' + p.nama + (p.wajib ? ' <span class="badge bg-danger-subtle text-danger">wajib</span>' : '') + '</li>';
        });
        html += '</ul>';
        list.innerHTML = html;
    }

    // Dynamic bidang rows
    function bidangRowHTML(idx, v) {
        v = v || {};
        let opt = '';
        ['HM','HGB','HGU','HP','HPL'].forEach(o => {
            opt += '<option value="' + o + '"' + (String(v.jenis_hak || '') === o ? ' selected' : '') + '>' + o + '</option>';
        });
        return `<div class="card bg-light border mb-3 bidang-row">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold small text-primary"><i class="bi bi-diagram-3 me-1"></i>Bidang ${idx + 1}</span>
                    ${idx > 0 ? `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="hapusBidang(this)"><i class="bi bi-x-lg"></i></button>` : ''}
                </div>
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label small fw-semibold mb-1">NIB</label>
                        <input type="text" name="bidang[${idx}][nib]" value="${v.nib || ''}" class="form-control form-control-sm" maxlength="50"></div>
                    <div class="col-md-3"><label class="form-label small fw-semibold mb-1">No. Sertifikat Lama</label>
                        <input type="text" name="bidang[${idx}][no_sertifikat_lama]" value="${v.no_sertifikat_lama || ''}" class="form-control form-control-sm" maxlength="100"></div>
                    <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Jenis Hak</label>
                        <select name="bidang[${idx}][jenis_hak]" class="form-select form-select-sm">${opt}</select></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold mb-1">Pemegang Hak</label>
                        <input type="text" name="bidang[${idx}][nama_pemegang_hak]" value="${v.nama_pemegang_hak || ''}" class="form-control form-control-sm" maxlength="200"></div>
                    <div class="col-md-3"><label class="form-label small fw-semibold mb-1">Luas (m²)</label>
                        <input type="number" step="0.01" min="0" name="bidang[${idx}][luas_m2]" value="${v.luas_m2 || ''}" class="form-control form-control-sm"></div>
                    <div class="col-md-4"><label class="form-label small fw-semibold mb-1">Desa/Kelurahan</label>
                        <input type="text" name="bidang[${idx}][desa_kelurahan]" value="${v.desa_kelurahan || ''}" class="form-control form-control-sm" maxlength="100"></div>
                    <div class="col-md-5"><label class="form-label small fw-semibold mb-1">Kecamatan</label>
                        <input type="text" name="bidang[${idx}][kecamatan]" value="${v.kecamatan || ''}" class="form-control form-control-sm" maxlength="100"></div>
                </div>
            </div>
        </div>`;
    }

    let oldBidang = JSON.parse(document.getElementById('oldBidangJson').textContent);
    oldBidang = Array.isArray(oldBidang) ? oldBidang : [];

    function syncBidangRows() {
        const container = document.getElementById('bidangContainer');
        const inp = document.getElementById('jumlahBidang');
        let n = Math.max(1, parseInt(inp.value, 10) || 1);
        inp.value = n;
        let html = '';
        for (let i = 0; i < n; i++) {
            html += bidangRowHTML(i, oldBidang[i] || {});
        }
        container.innerHTML = html;
        updateBidangCounter();
    }

    function hapusBidang(btn) {
        btn.closest('.bidang-row').remove();
        document.querySelectorAll('#bidangContainer .bidang-row').forEach((row, i) => {
            row.querySelector('.fw-bold.small.text-primary').textContent = 'Bidang ' + (i + 1);
        });
        updateBidangCounter();
    }

    function updateBidangCounter() {
        const n = document.querySelectorAll('#bidangContainer .bidang-row').length;
        const inp = document.getElementById('jumlahBidang');
        if (inp && parseInt(inp.value, 10) !== n) inp.value = Math.max(1, n);
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderPersyaratan();
        syncBidangRows();
        document.getElementById('jenisPermohonanSelect').addEventListener('change', renderPersyaratan);
        document.getElementById('jumlahBidang').addEventListener('change', syncBidangRows);
        document.getElementById('formLoket').addEventListener('submit', function (e) {
            if (document.querySelectorAll('#bidangContainer .bidang-row').length < 1) {
                e.preventDefault();
                alert('Minimal satu bidang tanah harus diisi.');
            }
        });
    });
</script>
@endsection