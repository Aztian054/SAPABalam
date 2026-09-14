@extends('layouts.app')

@section('title', 'Edit Tiket — Loket')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Tiket</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('loket.index') }}">Loket</a></li>
                <li class="breadcrumb-item"><a href="{{ route('loket.show', $tiket->id) }}">Detail Tiket</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    </div>

<div class="alert alert-light border small py-2">
    <span class="fw-bold text-dark">{{ $tiket->kode_tiket }}</span>
    <span class="badge bg-{{ $tiket->status_badge }} text-uppercase mx-1">{{ $tiket->status_label }}</span>
    &bull; {{ $tiket->jenisPermohonan?->nama ?? '-' }}
    &bull; Jumlah bidang: <strong>{{ $tiket->jumlah_bidang }}</strong>
    &bull; Masuk: {{ $tiket->tanggal_masuk?->format('d/m/Y') }}
</div>

<div class="card card-custom">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-vcard text-primary me-2"></i>Data Pemohon &amp; Berkas</h6>
        <small class="text-muted">Kode tiket, jenis permohonan, dan data bidang tanah tidak dapat diubah lewat halaman ini.</small>
    </div>
    <div class="card-body">
        <form action="{{ route('loket.update', $tiket->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nama_pemohon" class="form-label small fw-semibold">Nama Pemohon <span class="text-danger">*</span></label>
                    <input type="text" name="nama_pemohon" id="nama_pemohon" class="form-control @error('nama_pemohon') is-invalid @enderror"
                           value="{{ old('nama_pemohon', $tiket->nama_pemohon) }}" required maxlength="200">
                    @error('nama_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="nik_pemohon" class="form-label small fw-semibold">NIK Pemohon</label>
                    <input type="text" name="nik_pemohon" id="nik_pemohon" class="form-control @error('nik_pemohon') is-invalid @enderror"
                           value="{{ old('nik_pemohon', $tiket->nik_pemohon) }}" maxlength="20">
                    @error('nik_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="no_hp_pemohon" class="form-label small fw-semibold">No. HP Pemohon <span class="text-danger">*</span></label>
                    <input type="text" name="no_hp_pemohon" id="no_hp_pemohon" class="form-control @error('no_hp_pemohon') is-invalid @enderror"
                           value="{{ old('no_hp_pemohon', $tiket->no_hp_pemohon) }}" required maxlength="20">
                    @error('no_hp_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="satuan_kerja" class="form-label small fw-semibold">Satuan Kerja (Satker)</label>
                    <input type="text" name="satuan_kerja" id="satuan_kerja" class="form-control @error('satuan_kerja') is-invalid @enderror"
                           value="{{ old('satuan_kerja', $tiket->satuan_kerja) }}" maxlength="200">
                    @error('satuan_kerja') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="no_hak_sekarang" class="form-label small fw-semibold">No. Hak Sekarang</label>
                    <input type="text" name="no_hak_sekarang" id="no_hak_sekarang" class="form-control @error('no_hak_sekarang') is-invalid @enderror"
                           value="{{ old('no_hak_sekarang', $tiket->no_hak_sekarang) }}" maxlength="100">
                    @error('no_hak_sekarang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="kelurahan_desa" class="form-label small fw-semibold">Kelurahan / Desa</label>
                    <input type="text" name="kelurahan_desa" id="kelurahan_desa" class="form-control @error('kelurahan_desa') is-invalid @enderror"
                           value="{{ old('kelurahan_desa', $tiket->kelurahan_desa) }}" maxlength="100">
                    @error('kelurahan_desa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="kecamatan" class="form-label small fw-semibold">Kecamatan</label>
                    <input type="text" name="kecamatan" id="kecamatan" class="form-control @error('kecamatan') is-invalid @enderror"
                           value="{{ old('kecamatan', $tiket->kecamatan) }}" maxlength="100">
                    @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-sm rounded-3 px-4 shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('loket.show', $tiket->id) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">Batal</a>
            </div>
        </form>
    </div>
</div>

@if($tiket->bidangTanahs->isNotEmpty())
    @include('partials.bidang_table')
@endif
@endsection