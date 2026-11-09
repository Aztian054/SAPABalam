@extends('layouts.app')

@section('title', 'Super Admin — Data & Skenario Demo')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="bi bi-magic text-primary me-2"></i>Data &amp; Skenario Demo</h4>
            <p class="text-muted small mb-0">
                Membuat paket contoh untuk pelatihan alur. Semua baris bertanda
                <span class="badge bg-info text-dark">DEMO</span> dan berprefiks
                <code>{{ $prefix }}</code> pada nama pemohon/pengadu.
            </p>
        </div>
        <span class="badge bg-warning text-dark">
            <i class="bi bi-hdd-network me-1"></i>Mode demo aktif
        </span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm py-2">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-primary">{{ $ringkasan['tiket'] }}</div>
                    <div class="small text-muted">Tiket demo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-warning">{{ $ringkasan['warkah'] }}</div>
                    <div class="small text-muted">Warkah demo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-danger">{{ $ringkasan['pengaduan'] }}</div>
                    <div class="small text-muted">Pengaduan demo</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-custom h-100">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-success">{{ $ringkasan['pengguna'] }}</div>
                    <div class="small text-muted">Akun demo</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam text-success me-1"></i>Seed Data Demo</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Membuat satu paket data untuk lima skenario: alur normal, ditolak,
                        selesai, macet, warkah, dan pengaduan.
                    </p>

                    <ul class="small ps-3 mb-3">
                        <li>3 akun demo (<code>demo_loket</code>, <code>demo_staf</code>, <code>demo_warkah</code>)</li>
                        <li>5 tiket: normal, ditolak, selesai, macet, dan antrean warkah</li>
                        <li>2 warkah: pinjaman berjalan &amp; permintaan baru</li>
                        <li>2 pengaduan: terlambat ditanggapi &amp; selesai</li>
                    </ul>

                    @unless ($punyaJenisPermohonan)
                        <div class="alert alert-warning border-0 py-2 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Master <strong>Jenis Permohonan</strong> masih kosong. Tambahkan satu jenis
                            permohonan aktif terlebih dahulu agar tiket demo dapat dibuat.
                        </div>
                    @endunless

                    @if ($ringkasan['total'] > 0)
                        <div class="alert alert-info border-0 py-2 small mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Data demo sudah ada. Hapus dulu sebelum membuat ulang.
                        </div>
                    @endif

                    <form action="{{ route('super-admin.demo.seed') }}" method="POST"
                        onsubmit="return confirm('Buat data demo sekarang?')">
                        @csrf
                        <button class="btn btn-success"
                            @disabled($ringkasan['total'] > 0 || ! $punyaJenisPermohonan)>
                            <i class="bi bi-plus-circle me-1"></i>Seed Data Demo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-custom h-100 border-danger-subtle">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-trash3 text-danger me-1"></i>Hapus Data Demo</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Menghapus <strong>hanya</strong> baris bertanda demo beserta riwayat turunannya.
                        Tiket, warkah, pengaduan, akun, dan master data asli tidak tersentuh.
                    </p>

                    <form action="{{ route('super-admin.demo.reset') }}" method="POST"
                        onsubmit="return confirm('Hapus seluruh data demo? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Ketik <code>{{ $frasaKonfirmasi }}</code> untuk melanjutkan
                            </label>
                            <input type="text" name="konfirmasi" value="{{ old('konfirmasi') }}"
                                class="form-control @error('konfirmasi') is-invalid @enderror"
                                placeholder="{{ $frasaKonfirmasi }}" autocomplete="off">
                            @error('konfirmasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('paham') is-invalid @enderror" type="checkbox"
                                name="paham" id="paham" value="1" @checked(old('paham'))>
                            <label class="form-check-label small" for="paham">
                                Saya paham data demo akan dihapus permanen.
                            </label>
                            @error('paham')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button class="btn btn-outline-danger" @disabled($ringkasan['total'] === 0)>
                            <i class="bi bi-trash3 me-1"></i>Hapus Data Demo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom mt-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-diagram-3 text-primary me-1"></i>Skenario yang Dibuat</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Skenario</th><th>Isi</th><th>Titik Periksa</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">Alur Normal</td>
                        <td>Tiket diproses &amp; dipegang Staf Seksi 1</td>
                        <td>Chip status, kolom Dipegang Oleh</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Alur Ditolak</td>
                        <td>Tiket ditolak dengan alasan</td>
                        <td>Daftar Ditolak, riwayat alasan</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Alur Selesai</td>
                        <td>Tiket selesai &amp; sudah diserahkan</td>
                        <td>Berkas Selesai, calon arsip</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Alur Macet</td>
                        <td>Dipegang melewati batas hari penanda</td>
                        <td>Chip <span class="badge bg-danger">MACET</span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Alur Warkah</td>
                        <td>Permintaan baru (tiket di database) &amp; pinjaman berjalan</td>
                        <td>Daftar Warkah, chip TERLAMBAT</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Alur Pengaduan</td>
                        <td>Pengaduan terlambat ditanggapi &amp; selesai</td>
                        <td>Daftar Pengaduan unit tujuan</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
