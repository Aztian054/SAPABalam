@extends('layouts.app')

@section('title', 'Admin — Manajemen Akun')

@section('content')
<div id="page-data" data-modal-on-error="{{ $errors->any() ? 'modalTambahAkun' : '' }}" style="display:none"></div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Akun Pengguna</h4>
        <button type="button" class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#modalTambahAkun">
            <i class="bi bi-person-plus me-1"></i>Tambah Akun
        </button>
    </div>
    <p class="text-muted small mb-3">
        Setiap jabatan boleh memiliki <strong>banyak akun</strong>; satu berkas hanya boleh dikerjakan satu akun dalam jabatan yang sama (<strong>Exclusive Ticket Claiming</strong>).
    </p>

    <form method="GET" class="mb-2">
        <select name="role" class="form-select max-w-280" onchange="this.form.submit()">
            <option value="">Semua Jabatan</option>
            @foreach(\App\Models\User::ROLES as $key => $label)
                <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Username</th><th>Password</th><th>Jabatan</th><th>NIP</th><th>No. HP</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->username }}</td>
                            <td><code class="text-dark">{{ $u->password_text ?? '—' }}</code></td>
                            <td><span class="badge bg-secondary">{{ $u->role_label }}</span></td>
                            <td><small class="text-muted">{{ $u->nip ?? '-' }}</small></td>
                            <td><small class="text-muted">{{ $u->no_hp ?? '-' }}</small></td>
                            <td>
                                <span class="badge bg-{{ $u->is_active ? 'success' : 'danger' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-outline-primary" title="Edit akun">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                @if ($u->role !== 'admin')
                                    <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-{{ $u->is_active ? 'dark' : 'success' }}">
                                            {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.hapus', $u->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus akun {{ $u->name }}? Data penugasannya ikut terhapus dan tidak dapat dibatalkan.')">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus akun">
                                            <i class="bi bi-trash me-1"></i>Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada akun.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- MODAL TAMBAH AKUN --}}
    <div class="modal fade" id="modalTambahAkun" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Tambah Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger border-0 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Input tidak valid:</strong>
                                <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="200" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Username <span class="text-danger">*</span></label>
                                <input name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" maxlength="50" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">NIP</label>
                                <input name="nip" value="{{ old('nip') }}" class="form-control @error('nip') is-invalid @enderror" maxlength="30" placeholder="NIP petugas">
                                @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">No. HP</label>
                                <input name="no_hp" value="{{ old('no_hp') }}" class="form-control @error('no_hp') is-invalid @enderror" maxlength="20" placeholder="08xxxxxxxxxx">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold small">Jabatan <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">— Pilih Jabatan —</option>
                                @foreach(\App\Models\User::rolesNonAdmin() as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gold rounded-3 px-4 shadow-sm"><i class="bi bi-person-plus me-1"></i> Tambahkan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var pageData = document.getElementById('page-data');
        var modalOnError = pageData ? pageData.getAttribute('data-modal-on-error') : '';
        if (modalOnError) {
            var modal = new bootstrap.Modal(document.getElementById(modalOnError));
            modal.show();
        }
    });
</script>
@endsection