@extends('layouts.app')

@section('title', 'Admin — Edit Jenis Hak')

@section('content')
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-bank text-primary me-2"></i>Edit Jenis Hak</h4>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-x-circle-fill me-1"></i>
            <strong>Periksa kembali isian berikut:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-custom max-w-560">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">{{ $jenisHak->kode }} — {{ $jenisHak->nama }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.form-pendaftaran.jenis-hak.update', $jenisHak->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label class="form-label">Kode</label>
                    <input name="kode" class="form-control" maxlength="10" required value="{{ old('kode', $jenisHak->kode) }}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Nama</label>
                    <input name="nama" class="form-control" maxlength="100" required value="{{ old('nama', $jenisHak->nama) }}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Urutan</label>
                    <input name="urutan" type="number" min="0" class="form-control" value="{{ old('urutan', $jenisHak->urutan) }}">
                </div>
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                           {{ old('is_active', $jenisHak->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Aktif</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('admin.form-pendaftaran') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection