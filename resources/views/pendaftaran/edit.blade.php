@extends('layouts.app')

@section('title', 'Edit Berkas ' . $tiket->kode_tiket)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Berkas {{ $tiket->kode_tiket }}</h4>
        <p class="text-muted small mb-0">Edit semua data pendaftaran sekaligus (gabungan 4 tahap).</p>
    </div>
    <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="card card-custom p-4">
    <form method="POST" action="{{ route('pendaftaran.update', $tiket) }}">
        @csrf
        @method('PUT')

        <h6 class="fw-bold mb-3">Tahap 1 — Biodata & No. Berkas</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">No. Berkas</label>
                <input type="text" name="no_berkas" value="{{ old('no_berkas', $tiket->no_berkas) }}" class="form-control form-control-sm" placeholder="Nomor resmi BPN">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama Pemohon <span class="text-danger">*</span></label>
                <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon', $tiket->nama_pemohon) }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">NIK Pemohon</label>
                <input type="text" name="nik_pemohon" value="{{ old('nik_pemohon', $tiket->nik_pemohon) }}" class="form-control form-control-sm" maxlength="16">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">No HP Pemohon</label>
                <input type="text" name="no_hp_pemohon" value="{{ old('no_hp_pemohon', $tiket->no_hp_pemohon) }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Kelurahan/Desa</label>
                <input type="text" name="kelurahan_desa" value="{{ old('kelurahan_desa', $tiket->kelurahan_desa) }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Kecamatan</label>
                <input type="text" name="kecamatan" value="{{ old('kecamatan', $tiket->kecamatan) }}" class="form-control form-control-sm">
            </div>
        </div>

        <h6 class="fw-bold mb-3">Tahap 3 — Jenis Permohonan & No Hak</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">No Hak Sekarang</label>
                <input type="text" name="no_hak_sekarang" value="{{ old('no_hak_sekarang', $tiket->no_hak_sekarang) }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Jumlah Bidang</label>
                <input type="number" name="jumlah_bidang" value="{{ old('jumlah_bidang', $tiket->jumlah_bidang) }}" class="form-control form-control-sm" min="1">
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-gold btn-sm"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
            <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
        </div>
    </form>
</div>
@endsection