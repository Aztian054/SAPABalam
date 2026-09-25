@extends('layouts.app')

@section('title', 'Admin — Arsip Folder')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-archive-fill text-primary me-2"></i>Manajemen Arsip Folder</h4>
    </div>

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card card-custom">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Tambah Folder</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.arsip.store') }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Nama Folder</label>
                            <input name="nama_folder" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Jenis Dokumen</label>
                            <input name="jenis_dokumen" class="form-control" placeholder="mis. Sertifikat, Surat Ukur...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi Fisik</label>
                            <input name="lokasi_fisik" class="form-control" placeholder="mis. Rak 3, Lemari 2">
                        </div>
                        <button class="btn btn-gold"><i class="bi bi-folder-plus me-1"></i>Simpan Folder</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card card-custom">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Daftar Folder</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nama</th><th>Jenis</th><th>Lokasi Fisik</th><th class="text-end">Jml Arsip</th></tr>
                        </thead>
                        <tbody>
                            @forelse($folders as $f)
                                <tr>
                                    <td class="fw-semibold">{{ $f->nama_folder }}</td>
                                    <td>{{ $f->jenis_dokumen ?? '-' }}</td>
                                    <td>{{ $f->lokasi_fisik ?? '-' }}</td>
                                    <td class="text-end"><span class="badge bg-info">{{ $f->arsip_tikets_count }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada folder arsip.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection