@extends('layouts.app')

@section('title', 'Admin — Manajemen Akun')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Manajemen Akun Pengguna</h4>
    </div>
    <p class="text-muted small mb-3">
        Setiap jabatan boleh memiliki <strong>banyak akun</strong>; satu tiket hanya boleh dikerjakan satu akun dalam jabatan yang sama (<strong>Exclusive Ticket Claiming</strong>).
    </p>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card card-custom">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Akun</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Nama Lengkap</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Username</label>
                            <input name="username" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">NIP</label>
                                <input name="nip" class="form-control" maxlength="30" placeholder="NIP petugas">
                            </div>
                            <div class="col-6">
                                <label class="form-label">No. HP</label>
                                <input name="no_hp" class="form-control" maxlength="20" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <select name="role" class="form-select" required>
                                @foreach(\App\Models\User::ROLES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Simpan Akun</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <form method="GET" class="mb-2">
                <select name="role" class="form-select" onchange="this.form.submit()">
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
                            <tr><th>Nama</th><th>Username</th><th>Jabatan</th><th>NIP / HP</th><th>Status</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td class="fw-semibold">{{ $u->name }}</td>
                                    <td>{{ $u->username }}</td>
                                    <td><span class="badge bg-secondary">{{ $u->role_label }}</span></td>
                                    <td><small class="text-muted">{{ $u->nip ?? '-' }}<br>{{ $u->no_hp ?? '-' }}</small></td>
                                    <td>
                                        <span class="badge bg-{{ $u->is_active ? 'success' : 'danger' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-{{ $u->is_active ? 'dark' : 'success' }}">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada akun.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection