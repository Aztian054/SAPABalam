@extends('layouts.app')

@section('title', 'Super Admin — Lihat/Coba Sebagai Akun')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-person-badge-fill text-primary me-2"></i>Lihat/Coba Sebagai Akun</h4>
            <p class="text-muted small mb-0">
                Tampilan dan hak akses dapat diverifikasi tanpa mengubah data.
                Mode ini <strong>baca-saja</strong> dan setiap penggunaan tercatat di Log.
            </p>
        </div>
    </div>

    <div class="alert alert-warning border-0 shadow-sm py-2 small">
        <i class="bi bi-shield-lock-fill me-1"></i>
        Selama mode lihat/coba aktif, semua aksi yang mengubah data diblokir.
        Gunakan tombol <strong>Kembali ke Super Admin</strong> pada banner di atas halaman.
    </div>

    @forelse ($penggunaPerJabatan as $role => $daftar)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">{{ $daftar->first()->role_label }}</h6>
                <span class="badge bg-secondary text-uppercase">{{ $role }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nama</th><th>Username</th><th>Seksi</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td>{{ $u->username }}</td>
                                <td>{{ $u->seksi_label }}</td>
                                <td class="text-end">
                                    <form action="{{ route('super-admin.impersonate.start', $u->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Coba Sebagai
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card card-custom">
            <div class="card-body text-center text-muted py-4">
                Belum ada akun aktif selain Super Admin.
            </div>
        </div>
    @endforelse
@endsection
