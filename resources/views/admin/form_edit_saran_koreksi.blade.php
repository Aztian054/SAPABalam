@extends('layouts.app')

@section('title', 'Admin — Edit Saran Koreksi')

@section('content')
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-chat-dots text-primary me-2"></i>Edit Saran Koreksi</h4>

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

    <div class="card card-custom max-w-640">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Saran Koreksi</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.form-pendaftaran.saran-koreksi.update', $saranKoreksi->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label class="form-label">Jenis Permohonan</label>
                    <select name="jenis_permohonan_id" class="form-select" required>
                        <option value="">— Pilih —</option>
                        @foreach($jenisPermohonans as $jp)
                            <option value="{{ $jp->id }}" {{ old('jenis_permohonan_id', $saranKoreksi->jenis_permohonan_id) == $jp->id ? 'selected' : '' }}>
                                {{ $jp->kode }} — {{ $jp->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Nama Dokumen Kurang</label>
                    <input name="nama_dokumen_kurang" class="form-control" maxlength="255" required
                           value="{{ old('nama_dokumen_kurang', $saranKoreksi->nama_dokumen_kurang) }}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Pesan Koreksi</label>
                    <textarea name="pesan_koreksi" class="form-control" rows="3" required>{{ old('pesan_koreksi', $saranKoreksi->pesan_koreksi) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dasar Hukum</label>
                    <input name="dasar_hukum" class="form-control" maxlength="255"
                           value="{{ old('dasar_hukum', $saranKoreksi->dasar_hukum) }}">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('admin.form-pendaftaran') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection