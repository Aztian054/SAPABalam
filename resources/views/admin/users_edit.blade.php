@extends('layouts.app')

@section('title', 'Admin — Edit Akun')

@section('content')
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-person-gear text-primary me-2"></i>Edit Akun Pengguna</h4>

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
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Role: <span class="badge bg-secondary">{{ $user->role_label }}</span></h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label class="form-label">Nama Lengkap</label>
                    <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Username</label>
                    <input name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">NIP</label>
                        <input name="nip" class="form-control" maxlength="30" value="{{ old('nip', $user->nip) }}" placeholder="NIP petugas">
                    </div>
                    <div class="col-6">
                        <label class="form-label">No. HP</label>
                        <input name="no_hp" class="form-control" maxlength="20" value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Seksi</label>
                    <select name="seksi" class="form-select">
                        <option value="">— Tidak terikat seksi —</option>
                        @foreach(\App\Models\User::SEKSI as $key => $label)
                            <option value="{{ $key }}" @selected((string) old('seksi', $user->seksi) === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Untuk role Staf Seksi, Kasi Seksi, dan Kasi Unit.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Jabatan (Input Manual)</label>
                    <input name="jabatan" class="form-control" maxlength="100" value="{{ old('jabatan', $user->jabatan) }}" placeholder="Contoh: Validator">
                    <div class="form-text">Nama jabatan bebas; dapat diisi Admin atau diubah sendiri oleh pemilik akun lewat Profil.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <div class="form-text">
                        Password saat ini: <strong class="text-dark">{{ $user->password_text ?? '—' }}</strong>.
                        Minimal 6 karakter; biarkan kosong untuk mempertahankan password lama.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div><span class="badge bg-secondary fs-6">{{ $user->role_label }}</span></div>
                    <div class="form-text text-danger">
                        <i class="bi bi-lock-fill me-1"></i>Role tidak dapat diubah pada akun yang sudah dibuat agar hak akses dan alur berkas tidak rusak.
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Simpan Perubahan</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-light border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection