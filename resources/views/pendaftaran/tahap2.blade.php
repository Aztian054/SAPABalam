@extends('layouts.app')

@section('title', 'Pendaftaran — Tahap 2 Ploting')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Tahap 2 &mdash; Ploting</h4>
    <p class="text-muted small mb-0">Nomor tiket <strong class="font-monospace">{{ $draf->kode_tiket }}</strong> &middot; {{ $biodata['nama_pemohon'] ?? '-' }}</p>
</div>

@include('pendaftaran._steps', ['aktif' => 2])

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-custom p-4">
            @if ($draf->ploting_status === 'sudah')
                <div class="alert alert-success border-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Ploting sudah dinyatakan selesai
                    @if ($draf->ploting_selesai_at) pada {{ $draf->ploting_selesai_at->translatedFormat('d M Y H:i') }} @endif.
                    @if ($draf->ploting_catatan)
                        <div class="small mt-1">Catatan: {{ $draf->ploting_catatan }}</div>
                    @endif
                </div>
                <a href="{{ route('pendaftaran.tahap3', $draf) }}" class="btn btn-gold px-4">
                    Lanjut ke Tahap 3 <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @else
                <div class="alert alert-warning border-0">
                    <i class="bi bi-hourglass-split me-2"></i>
                    @if ($draf->dikirim_pada)
                        Berkas sedang menunggu ploting sejak {{ $draf->dikirim_pada->translatedFormat('d M Y H:i') }}.
                    @else
                        Pilih <strong>Belum</strong> bila bidang tanah belum diploting.
                    @endif
                </div>

                @if ($draf->dikirim_pada)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('pendaftaran.antrian', $draf) }}" class="btn btn-outline-primary">
                            <i class="bi bi-printer me-1"></i> Cetak Ulang Antrian
                        </a>
                        <a href="{{ route('pendaftaran.tahap2', $draf) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise me-1"></i> Periksa Status Ploting
                        </a>
                    </div>
                    <p class="small text-muted mb-0">
                        Tahap 3 terbuka otomatis setelah petugas Loket Ploting menyatakan ploting selesai.
                    </p>
                @else
                    <form action="{{ route('pendaftaran.tahap2.store', $draf) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block">Status ploting bidang tanah</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="ploting" id="ploting_sudah" value="sudah" @checked(old('ploting') === 'sudah')>
                                <label class="form-check-label" for="ploting_sudah">
                                    <strong>Sudah diploting</strong> &mdash; langsung lanjut ke Tahap 3.
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ploting" id="ploting_belum" value="belum" @checked(old('ploting') === 'belum')>
                                <label class="form-check-label" for="ploting_belum">
                                    <strong>Belum diploting</strong> &mdash; cetak antrian dan serahkan ke Loket Ploting.
                                </label>
                            </div>
                            @error('ploting') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary">Nanti Saja</a>
                            <button type="submit" class="btn btn-gold px-4">Simpan &amp; Lanjut</button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard text-primary me-2"></i>Biodata Tersimpan</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted fw-normal">Nama</dt><dd class="col-7 fw-semibold">{{ $biodata['nama_pemohon'] ?? '-' }}</dd>
                <dt class="col-5 text-muted fw-normal">NIK</dt><dd class="col-7">{{ $biodata['nik_pemohon'] ?? '-' }}</dd>
                <dt class="col-5 text-muted fw-normal">No. HP</dt><dd class="col-7">{{ $biodata['no_hp_pemohon'] ?? '-' }}</dd>
                <dt class="col-5 text-muted fw-normal">Kelurahan</dt><dd class="col-7">{{ $biodata['kelurahan_desa'] ?? '-' }}</dd>
                <dt class="col-5 text-muted fw-normal">Kecamatan</dt><dd class="col-7">{{ $biodata['kecamatan'] ?? '-' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
