@extends('layouts.app')

@section('title', 'Admin — Kelola Form Pendaftaran')

@section('content')
    <h4 class="fw-bold text-dark mb-1"><i class="bi bi-ui-checks text-primary me-2"></i>Kelola Form Pendaftaran</h4>
    <p class="text-muted small mb-3">
        Kelola isi dropdown / master form pendaftaran secara mandiri. Perubahan langsung berlaku tanpa perlu ubahan kode.
    </p>

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

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-jp" type="button">
                <i class="bi bi-card-checklist me-1"></i>Jenis Permohonan
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kategori" type="button">
                <i class="bi bi-tags me-1"></i>Kategori
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-jenis-hak" type="button">
                <i class="bi bi-bank me-1"></i>Jenis Hak
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-saran" type="button">
                <i class="bi bi-chat-dots me-1"></i>Saran Koreksi
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- ========== TAB 1 : JENIS PERMOHONAN ========== --}}
        <div class="tab-pane fade show active" id="tab-jp">
            <div class="row g-3">
<div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Jenis Permohonan</h6></div>
                        <div class="card-body">
                            <form action="{{ route('admin.form-pendaftaran.jenis-permohonan.store') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Kode</label>
                                    <input name="kode" class="form-control" maxlength="10" required
                                           placeholder="Contoh: JP01" value="{{ old('kode') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nama</label>
                                    <input name="nama" class="form-control" maxlength="200" required
                                           placeholder="Nama jenis permohonan" value="{{ old('nama') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Kategori</label>
                                    <select name="kategori" class="form-select" required>
                                        <option value="">— Pilih Kategori —</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->kode }}" {{ old('kategori') === $k->kode ? 'selected' : '' }}>{{ $k->kode }} — {{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="2"
                                              placeholder="Deskripsi singkat (opsional)">{{ old('deskripsi') }}</textarea>
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Persyaratan Dokumen</label>
                                    <div id="jp-persyaratan-rows">
                                        <div class="input-group input-group-sm mb-1">
                                            <input name="persyaratan[0][nama_dokumen]" class="form-control" placeholder="Nama dokumen">
                                            <div class="input-group-text">
                                                <input type="checkbox" name="persyaratan[0][wajib]" value="1" checked class="form-check-input mt-0" title="Wajib">
                                            </div>
                                        </div>
                                        <div class="input-group input-group-sm mb-1">
                                            <input name="persyaratan[1][nama_dokumen]" class="form-control" placeholder="Nama dokumen">
                                            <div class="input-group-text">
                                                <input type="checkbox" name="persyaratan[1][wajib]" value="1" checked class="form-check-input mt-0" title="Wajib">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="addPersyaratanRow()">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Baris Dokumen
                                    </button>
                                </div>
                                <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
<div class="col-md-8">
                    <div class="card card-custom">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Persyaratan</th><th>Status</th><th class="text-end">Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($jenisPermohonans as $jp)
                                        <tr>
                                            <td class="fw-bold">{{ $jp->kode }}</td>
                                            <td>
                                                {{ $jp->nama }}
                                                @if($jp->deskripsi)<br><small class="text-muted">{{ Str::limit($jp->deskripsi, 55) }}</small>@endif
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $jp->kategori }}</span></td>
                                            <td><span class="badge bg-info">{{ $jp->persyaratanDokumens->count() }} dokumen</span></td>
                                            <td><span class="badge bg-{{ $jp->is_active ? 'success' : 'danger' }}">{{ $jp->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('admin.form-pendaftaran.jenis-permohonan.edit', $jp->id) }}" class="btn btn-sm btn-outline-primary" title="Edit termasuk persyaratan">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('admin.form-pendaftaran.jenis-permohonan.toggle', $jp->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-{{ $jp->is_active ? 'dark' : 'success' }}">{{ $jp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                                </form>
                                                @if($jp->tikets->isEmpty())
                                                    <form action="{{ route('admin.form-pendaftaran.jenis-permohonan.hapus', $jp->id) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Hapus Jenis Permohonan {{ $jp->kode }} — {{ $jp->nama }}?')">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada Jenis Permohonan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END TAB 1 --}}
{{-- ========== TAB 2 : KATEGORI ========== --}}
        <div class="tab-pane fade" id="tab-kategori">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Kategori</h6></div>
                        <div class="card-body">
                            <form action="{{ route('admin.form-pendaftaran.kategori.store') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Kode</label>
                                    <input name="kode" class="form-control" maxlength="20" required
                                           placeholder="Contoh: umum, bmn" value="{{ old('kode') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nama</label>
                                    <input name="nama" class="form-control" maxlength="100" required
                                           placeholder="Nama kategori" value="{{ old('nama') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Urutan</label>
                                    <input name="urutan" type="number" min="0" class="form-control" value="{{ old('urutan', 0) }}">
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                                <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-custom">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Urutan</th><th>Kode</th><th>Nama</th><th>Jml JP</th><th>Status</th><th class="text-end">Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($kategoris as $k)
                                        <tr>
                                            <td>{{ $k->urutan }}</td>
                                            <td class="fw-bold">{{ $k->kode }}</td>
                                            <td>{{ $k->nama }}</td>
                                            <td><span class="badge bg-info">{{ $k->jenis_permohonans_count }}</span></td>
                                            <td><span class="badge bg-{{ $k->is_active ? 'success' : 'danger' }}">{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('admin.form-pendaftaran.kategori.edit', $k->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.form-pendaftaran.kategori.toggle', $k->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-{{ $k->is_active ? 'dark' : 'success' }}">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                                </form>
                                                @if($k->jenis_permohonans_count === 0)
                                                    <form action="{{ route('admin.form-pendaftaran.kategori.hapus', $k->id) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Hapus kategori {{ $k->nama }}?')">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END TAB 2 --}}
{{-- ========== TAB 3 : JENIS HAK ========== --}}
        <div class="tab-pane fade" id="tab-jenis-hak">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Jenis Hak</h6></div>
                        <div class="card-body">
                            <form action="{{ route('admin.form-pendaftaran.jenis-hak.store') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Kode</label>
                                    <input name="kode" class="form-control" maxlength="10" required
                                           placeholder="Contoh: SHM, HGB" value="{{ old('kode') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nama</label>
                                    <input name="nama" class="form-control" maxlength="100" required
                                           placeholder="Hak Milik" value="{{ old('nama') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Urutan</label>
                                    <input name="urutan" type="number" min="0" class="form-control" value="{{ old('urutan', 0) }}">
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                                <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-custom">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Urutan</th><th>Kode</th><th>Nama</th><th>Jml Bidang</th><th>Status</th><th class="text-end">Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($jenisHaks as $jh)
                                        <tr>
                                            <td>{{ $jh->urutan }}</td>
                                            <td class="fw-bold">{{ $jh->kode }}</td>
                                            <td>{{ $jh->nama }}</td>
                                            <td><span class="badge bg-info">{{ $jh->bidang_tanahs_count }}</span></td>
                                            <td><span class="badge bg-{{ $jh->is_active ? 'success' : 'danger' }}">{{ $jh->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('admin.form-pendaftaran.jenis-hak.edit', $jh->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.form-pendaftaran.jenis-hak.toggle', $jh->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-{{ $jh->is_active ? 'dark' : 'success' }}">{{ $jh->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                                </form>
                                                @if($jh->bidang_tanahs_count === 0)
                                                    <form action="{{ route('admin.form-pendaftaran.jenis-hak.hapus', $jh->id) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Hapus Jenis Hak {{ $jh->kode }} — {{ $jh->nama }}?')">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada Jenis Hak.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END TAB 3 --}}
{{-- ========== TAB 4 : SARAN KOREKSI ========== --}}
        <div class="tab-pane fade" id="tab-saran">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Saran Koreksi</h6></div>
                        <div class="card-body">
                            <form action="{{ route('admin.form-pendaftaran.saran-koreksi.store') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label">Jenis Permohonan</label>
                                    <select name="jenis_permohonan_id" class="form-select" required>
                                        <option value="">— Pilih —</option>
                                        @foreach($jenisPermohonans as $jp)
                                            <option value="{{ $jp->id }}" {{ old('jenis_permohonan_id') == $jp->id ? 'selected' : '' }}>
                                                {{ $jp->kode }} — {{ $jp->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nama Dokumen Kurang</label>
                                    <input name="nama_dokumen_kurang" class="form-control" maxlength="255" required
                                           placeholder="Contoh: Surat Kuasa Notaris" value="{{ old('nama_dokumen_kurang') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Pesan Koreksi</label>
                                    <textarea name="pesan_koreksi" class="form-control" rows="2" required
                                              placeholder="Isi pesan revisi untuk template">{{ old('pesan_koreksi') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dasar Hukum</label>
                                    <input name="dasar_hukum" class="form-control" maxlength="255"
                                           placeholder="Opsional" value="{{ old('dasar_hukum') }}">
                                </div>
                                <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card card-custom">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Jenis Permohonan</th><th>Dokumen Kurang</th><th>Pesan Koreksi</th><th class="text-end">Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($saranKoreksis as $sk)
                                        <tr>
                                            <td>
                                                @if($sk->jenisPermohonan)
                                                    <span class="badge bg-secondary">{{ $sk->jenisPermohonan->kode }}</span> {{ $sk->jenisPermohonan->nama }}
                                                @else
                                                    <span class="badge bg-warning text-dark">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $sk->nama_dokumen_kurang }}</td>
                                            <td>
                                                {{ $sk->pesan_koreksi }}
                                                @if($sk->dasar_hukum)<br><small class="text-muted">Dasar hukum: {{ $sk->dasar_hukum }}</small>@endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('admin.form-pendaftaran.saran-koreksi.edit', $sk->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.form-pendaftaran.saran-koreksi.hapus', $sk->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Hapus saran koreksi {{ $sk->nama_dokumen_kurang }}?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada saran koreksi.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- END TAB 4 --}}
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