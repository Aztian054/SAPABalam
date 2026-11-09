@extends('layouts.app')

@section('title', 'Pendaftaran — Tahap 3 Permohonan')

@php
    $barisBidang = old('bidang', $bidang);
    if (empty($barisBidang)) {
        $barisBidang = [[]];
    }
@endphp

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Tahap 3 &mdash; Data Permohonan</h4>
    <p class="text-muted small mb-0">Nomor tiket <strong class="font-monospace">{{ $draf->kode_tiket }}</strong> &middot; {{ $draf->biodata()['nama_pemohon'] ?? '-' }}</p>
</div>

@include('pendaftaran._steps', ['aktif' => 3])

<form action="{{ route('pendaftaran.tahap3.store', $draf) }}" method="POST" id="formTahap3">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Data Permohonan</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="jenis_permohonan_id" class="form-label fw-semibold">Jenis Permohonan <span class="text-danger">*</span></label>
                        <select name="jenis_permohonan_id" id="jenis_permohonan_id" class="form-select @error('jenis_permohonan_id') is-invalid @enderror" required>
                            <option value="">— Pilih jenis permohonan —</option>
                            @foreach ($jenisPermohonans as $jp)
                                <option value="{{ $jp->id }}" @selected((string) old('jenis_permohonan_id', $permohonan['jenis_permohonan_id'] ?? '') === (string) $jp->id)>
                                    {{ $jp->kode }} — {{ $jp->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_permohonan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="no_hak_sekarang" class="form-label fw-semibold">No. Hak Sekarang</label>
                        <input type="text" name="no_hak_sekarang" id="no_hak_sekarang"
                               class="form-control @error('no_hak_sekarang') is-invalid @enderror"
                               value="{{ old('no_hak_sekarang', $permohonan['no_hak_sekarang'] ?? '') }}"
                               placeholder="HM.123/Kedaton" maxlength="100">
                        @error('no_hak_sekarang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="no_hak_sebelumnya" class="form-label fw-semibold">No. Hak Sebelumnya</label>
                        <input type="text" name="no_hak_sebelumnya" id="no_hak_sebelumnya"
                               class="form-control @error('no_hak_sebelumnya') is-invalid @enderror"
                               value="{{ old('no_hak_sebelumnya', $permohonan['no_hak_sebelumnya'] ?? '') }}"
                               placeholder="Wajib bila ada penanda -DAHULU">
                        @error('no_hak_sebelumnya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kelurahan_desa" class="form-label fw-semibold">Letak Tanah — Kelurahan <span class="text-danger">*</span></label>
                        <input type="text" name="kelurahan_desa" id="kelurahan_desa"
                               class="form-control @error('kelurahan_desa') is-invalid @enderror"
                               value="{{ old('kelurahan_desa', $permohonan['kelurahan_desa'] ?? $draf->biodata()['kelurahan_desa'] ?? '') }}" required maxlength="100">
                        @error('kelurahan_desa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="kecamatan" class="form-label fw-semibold">Letak Tanah — Kecamatan <span class="text-danger">*</span></label>
                        <input type="text" name="kecamatan" id="kecamatan"
                               class="form-control @error('kecamatan') is-invalid @enderror"
                               value="{{ old('kecamatan', $permohonan['kecamatan'] ?? $draf->biodata()['kecamatan'] ?? '') }}" required maxlength="100">
                        @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror" maxlength="1000">{{ old('keterangan', $permohonan['keterangan'] ?? '') }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-map text-success me-2"></i>Bidang Tanah</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="tambahBidang">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Bidang
                    </button>
                </div>

                <div id="daftarBidang">
                    @foreach ($barisBidang as $i => $b)
                        <div class="border rounded-3 p-3 mb-3 bidang-baris">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary">Bidang <span class="nomor-bidang">{{ $i + 1 }}</span></span>
                                <button type="button" class="btn btn-sm btn-link text-danger hapus-bidang {{ count($barisBidang) === 1 ? 'd-none' : '' }}">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">NIB</label>
                                    <input type="text" name="bidang[{{ $i }}][nib]" data-field="nib" class="form-control form-control-sm" value="{{ $b['nib'] ?? '' }}" maxlength="50">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">No. Sertifikat Lama</label>
                                    <input type="text" name="bidang[{{ $i }}][no_sertifikat_lama]" data-field="no_sertifikat_lama" class="form-control form-control-sm" value="{{ $b['no_sertifikat_lama'] ?? '' }}" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Jenis Hak</label>
                                    <select name="bidang[{{ $i }}][jenis_hak]" data-field="jenis_hak" class="form-select form-select-sm">
                                        <option value="">—</option>
                                        @foreach ($jenisHaks as $jh)
                                            <option value="{{ $jh->kode }}" @selected(($b['jenis_hak'] ?? '') === $jh->kode)>{{ $jh->kode }} — {{ $jh->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Nama Pemegang Hak</label>
                                    <input type="text" name="bidang[{{ $i }}][nama_pemegang_hak]" data-field="nama_pemegang_hak" class="form-control form-control-sm" value="{{ $b['nama_pemegang_hak'] ?? '' }}" maxlength="200">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Kelurahan / Desa</label>
                                    <input type="text" name="bidang[{{ $i }}][desa_kelurahan]" data-field="desa_kelurahan" class="form-control form-control-sm" value="{{ $b['desa_kelurahan'] ?? '' }}" maxlength="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1">Kecamatan</label>
                                    <input type="text" name="bidang[{{ $i }}][kecamatan]" data-field="kecamatan" class="form-control form-control-sm" value="{{ $b['kecamatan'] ?? '' }}" maxlength="100">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('bidang') <div class="text-danger small">{{ $message }}</div> @enderror
                @error('bidang.*.jenis_hak') <div class="text-danger small">{{ $message }}</div> @enderror
                <p class="small text-muted mb-0">Jumlah bidang: <strong id="jumlahBidang">{{ count($barisBidang) }}</strong>. Minimal 1 bidang.</p>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clipboard-check text-primary me-2"></i>Ringkasan</h6>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">No. Tiket</dt><dd class="col-7 font-monospace fw-semibold">{{ $draf->kode_tiket }}</dd>
                    <dt class="col-5 text-muted fw-normal">Pemohon</dt><dd class="col-7">{{ $draf->biodata()['nama_pemohon'] ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Ploting</dt>
                    <dd class="col-7"><span class="badge bg-success">Sudah</span></dd>
                    <dt class="col-5 text-muted fw-normal">Diploting oleh</dt><dd class="col-7">{{ $draf->plotingOleh?->name ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Catatan ploting</dt><dd class="col-7">{{ $draf->ploting_catatan ?? '-' }}</dd>
                </dl>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="{{ route('pendaftaran.tahap2', $draf) }}" class="btn btn-outline-secondary">Kembali</a>
                <button type="submit" class="btn btn-gold px-4">Simpan &amp; Lanjut <i class="bi bi-arrow-right ms-1"></i></button>
            </div>
        </div>
    </div>
</form>

<template id="templateBidang">
    <div class="border rounded-3 p-3 mb-3 bidang-baris">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-secondary">Bidang <span class="nomor-bidang"></span></span>
            <button type="button" class="btn btn-sm btn-link text-danger hapus-bidang"><i class="bi bi-trash"></i> Hapus</button>
        </div>
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label small mb-1">NIB</label><input type="text" data-field="nib" class="form-control form-control-sm" maxlength="50"></div>
            <div class="col-md-4"><label class="form-label small mb-1">No. Sertifikat Lama</label><input type="text" data-field="no_sertifikat_lama" class="form-control form-control-sm" maxlength="100"></div>
            <div class="col-md-4"><label class="form-label small mb-1">Jenis Hak</label>
                <select data-field="jenis_hak" class="form-select form-select-sm">
                    <option value="">—</option>
                    @foreach ($jenisHaks as $jh)
                        <option value="{{ $jh->kode }}">{{ $jh->kode }} — {{ $jh->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><label class="form-label small mb-1">Nama Pemegang Hak</label><input type="text" data-field="nama_pemegang_hak" class="form-control form-control-sm" maxlength="200"></div>
            <div class="col-md-4"><label class="form-label small mb-1">Kelurahan / Desa</label><input type="text" data-field="desa_kelurahan" class="form-control form-control-sm" maxlength="100"></div>
            <div class="col-md-4"><label class="form-label small mb-1">Kecamatan</label><input type="text" data-field="kecamatan" class="form-control form-control-sm" maxlength="100"></div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script>
    (function () {
        const daftar = document.getElementById('daftarBidang');
        const template = document.getElementById('templateBidang');
        const labelJumlah = document.getElementById('jumlahBidang');

        function urutkanUlang() {
            const baris = daftar.querySelectorAll('.bidang-baris');

            baris.forEach((el, index) => {
                el.querySelector('.nomor-bidang').textContent = index + 1;
                el.querySelectorAll('[data-field]').forEach((input) => {
                    input.name = `bidang[${index}][${input.dataset.field}]`;
                });
                el.querySelectorAll('.hapus-bidang').forEach((btn) => {
                    btn.classList.toggle('d-none', baris.length === 1);
                });
            });

            labelJumlah.textContent = baris.length;
        }

        document.getElementById('tambahBidang')?.addEventListener('click', () => {
            const salinan = template.content.firstElementChild.cloneNode(true);
            daftar.appendChild(salinan);
            urutkanUlang();
        });

        daftar.addEventListener('click', (event) => {
            const tombol = event.target.closest('.hapus-bidang');
            if (!tombol) {
                return;
            }

            if (daftar.querySelectorAll('.bidang-baris').length > 1) {
                tombol.closest('.bidang-baris').remove();
                urutkanUlang();
            }
        });

        urutkanUlang();
    })();
</script>
@endsection
