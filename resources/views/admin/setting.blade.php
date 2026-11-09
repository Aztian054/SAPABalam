@extends('layouts.app')

@section('title', 'Admin — Setting')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-gear-fill text-primary me-2"></i>Setting</h4>
    </div>

    <p class="text-muted small mb-3">
        Perubahan berlaku untuk <strong>data baru</strong>. Data lama tidak dihitung ulang.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm py-2">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.setting.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card card-custom h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-warning me-1"></i>Batas Waktu</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Batas hari penanda MACET</label>
                            <input type="number" name="batas_macet_hari" min="1" max="365"
                                value="{{ old('batas_macet_hari', $nilai['batas_macet_hari']) }}"
                                class="form-control @error('batas_macet_hari') is-invalid @enderror" required>
                            <div class="form-text">Tiket yang dipegang melewati batas ini ditandai MACET (status tidak berubah).</div>
                            @error('batas_macet_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Batas kembali warkah (hari)</label>
                            <input type="number" name="batas_warkah_hari" min="1" max="365"
                                value="{{ old('batas_warkah_hari', $nilai['batas_warkah_hari']) }}"
                                class="form-control @error('batas_warkah_hari') is-invalid @enderror" required>
                            <div class="form-text">Dipakai sebagai saran batas pinjaman baru pada form warkah.</div>
                            @error('batas_warkah_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Hari menuju arsip otomatis</label>
                            <input type="number" name="hari_arsip" min="1" max="365"
                                value="{{ old('hari_arsip', $nilai['hari_arsip']) }}"
                                class="form-control @error('hari_arsip') is-invalid @enderror" required>
                            <div class="form-text">Berkas selesai dipindahkan ke arsip setelah melewati jumlah hari ini.</div>
                            @error('hari_arsip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Batas tanggap pengaduan (hari)</label>
                            <input type="number" name="batas_tanggap_pengaduan_hari" min="1" max="365"
                                value="{{ old('batas_tanggap_pengaduan_hari', $nilai['batas_tanggap_pengaduan_hari']) }}"
                                class="form-control @error('batas_tanggap_pengaduan_hari') is-invalid @enderror" required>
                            <div class="form-text">Pengaduan yang belum ditanggapi melewati batas ini ditandai TERLAMBAT.</div>
                            @error('batas_tanggap_pengaduan_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-custom h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-123 text-primary me-1"></i>Penomoran &amp; Retensi</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Prefix No. Tiket</label>
                                <input type="text" name="prefix_tiket" maxlength="5"
                                    value="{{ old('prefix_tiket', $nilai['prefix_tiket']) }}"
                                    class="form-control text-uppercase @error('prefix_tiket') is-invalid @enderror" required>
                                @error('prefix_tiket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold small">Prefix No. Pengaduan</label>
                                <input type="text" name="prefix_pengaduan" maxlength="5"
                                    value="{{ old('prefix_pengaduan', $nilai['prefix_pengaduan']) }}"
                                    class="form-control text-uppercase @error('prefix_pengaduan') is-invalid @enderror" required>
                                @error('prefix_pengaduan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Jumlah digit urut tiket</label>
                            <input type="number" name="padding_tiket" min="2" max="5"
                                value="{{ old('padding_tiket', $nilai['padding_tiket']) }}"
                                class="form-control @error('padding_tiket') is-invalid @enderror" required>
                            <div class="form-text">Contoh: 3 → {{ $nilai['prefix_tiket'] }}28102026<strong>001</strong>.</div>
                            @error('padding_tiket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Retensi log (hari)</label>
                            <input type="number" name="retensi_log_hari" min="1" max="3650"
                                value="{{ old('retensi_log_hari', $nilai['retensi_log_hari']) }}"
                                class="form-control @error('retensi_log_hari') is-invalid @enderror" required>
                            <div class="form-text">Log aktivitas lebih lama dari ini boleh dibersihkan scheduler.</div>
                            @error('retensi_log_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-gold px-4"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
        </div>
    </form>
@endsection
