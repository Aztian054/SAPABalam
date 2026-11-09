@extends('layouts.app')

@section('title', 'Super Admin — Backup')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-database-down text-primary me-2"></i>Backup Basis Data</h4>
            <p class="text-muted small mb-0">
                Unduh dump SQL seluruh tabel. Pemulihan dilakukan manual oleh operator.
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Ringkasan</h6></div>
                <div class="card-body">
                    <dl class="row mb-3 small">
                        <dt class="col-sm-4">Driver basis data</dt>
                        <dd class="col-sm-8"><span class="badge bg-dark">{{ $driver }}</span></dd>

                        <dt class="col-sm-4">Jumlah tabel</dt>
                        <dd class="col-sm-8">{{ $jumlahTabel }} tabel</dd>

                        <dt class="col-sm-4">Contoh nama file</dt>
                        <dd class="col-sm-8"><code>{{ $namaFile }}</code></dd>
                    </dl>

                    <div class="alert alert-warning border-0 py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Simpan file di tempat aman. File berisi data akun (termasuk password ter-hash)
                        dan seluruh riwayat layanan.
                    </div>

                    <form action="{{ route('super-admin.backup.unduh') }}" method="POST"
                        onsubmit="return confirm('Unduh backup basis data sekarang? File berisi data sensitif.')">
                        @csrf
                        <button class="btn btn-gold">
                            <i class="bi bi-download me-1"></i>Unduh Backup (.sql)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold">Catatan Operasional</h6></div>
                <div class="card-body small text-muted">
                    <ul class="ps-3 mb-0">
                        <li>Setiap unduhan tercatat di <strong>Log Aktivitas</strong>.</li>
                        <li>Dump memuat struktur tabel dan seluruh baris data.</li>
                        <li>Untuk memulihkan, impor file lewat alat basis data (mis. phpMyAdmin).</li>
                        <li>Disarankan membuat backup sebelum perubahan besar.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
