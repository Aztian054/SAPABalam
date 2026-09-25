@extends('layouts.app')

@section('title', 'Admin — Edit Jenis Permohonan')

@section('content')
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-card-checklist text-primary me-2"></i>Edit Jenis Permohonan</h4>

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

    <div class="card card-custom max-w-760">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">{{ $jenisPermohonan->kode }} — {{ $jenisPermohonan->nama }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.form-pendaftaran.jenis-permohonan.update', $jenisPermohonan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input name="kode" class="form-control" maxlength="10" required value="{{ old('kode', $jenisPermohonan->kode) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama</label>
                        <input name="nama" class="form-control" maxlength="200" required value="{{ old('nama', $jenisPermohonan->nama) }}">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="">— Pilih Kategori —</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->kode }}" {{ old('kategori', $jenisPermohonan->kategori) === $k->kode ? 'selected' : '' }}>{{ $k->kode }} — {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $jenisPermohonan->deskripsi) }}</textarea>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                           {{ old('is_active', $jenisPermohonan->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label">Aktif</label>
                </div>
<div class="mb-2">
                    <label class="form-label fw-bold">Persyaratan Dokumen</label>
                    <div id="jp-persyaratan-rows">
                        @forelse($jenisPermohonan->persyaratanDokumens as $p)
                            <div class="input-group input-group-sm mb-1">
                                <input name="persyaratan[{{ $loop->index }}][nama_dokumen]" class="form-control" value="{{ old('persyaratan.'.$loop->index.'.nama_dokumen', $p->nama_dokumen) }}" placeholder="Nama dokumen">
                                <div class="input-group-text">
                                    <input type="checkbox" name="persyaratan[{{ $loop->index }}][wajib]" value="1"
                                           {{ $p->wajib ? 'checked' : '' }} class="form-check-input mt-0" title="Wajib">
                                </div>
                                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()" title="Hapus baris">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @empty
                            <div class="input-group input-group-sm mb-1">
                                <input name="persyaratan[0][nama_dokumen]" class="form-control" placeholder="Nama dokumen">
                                <div class="input-group-text">
                                    <input type="checkbox" name="persyaratan[0][wajib]" value="1" checked class="form-check-input mt-0" title="Wajib">
                                </div>
                                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()" title="Hapus baris">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addPersyaratanRow()">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Baris Dokumen
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('admin.form-pendaftaran') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function addPersyaratanRow() {
            const wrap = document.getElementById('jp-persyaratan-rows');
            const idx = wrap.querySelectorAll('.input-group').length;
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mb-1';
            row.innerHTML = `
                <input name="persyaratan[${idx}][nama_dokumen]" class="form-control" placeholder="Nama dokumen">
                <div class="input-group-text">
                    <input type="checkbox" name="persyaratan[${idx}][wajib]" value="1" checked class="form-check-input mt-0" title="Wajib">
                </div>
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()" title="Hapus baris">
                    <i class="bi bi-x"></i>
                </button>`;
            wrap.appendChild(row);
        }
    </script>
@endsection