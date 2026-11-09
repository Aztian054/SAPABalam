@extends('layouts.app')

@section('title', 'Pendaftaran Baru — Tahap 1')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Pendaftaran Baru</h4>
    <p class="text-muted small mb-0">Tahap 1 dari 4 &mdash; No. Tiket, No. Berkas, dan biodata pemohon.</p>
</div>

@include('pendaftaran._steps', ['aktif' => 1])

@if ($draf)
    <div class="alert alert-info border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="bi bi-info-circle-fill me-2"></i>
            Draf <strong>{{ $draf->kode_tiket }}</strong> belum selesai (Tahap {{ min(4, max(1, $draf->tahap)) }}).
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pendaftaran.tahap2', $draf) }}" class="btn btn-sm btn-primary">Lanjutkan Draf</a>
            <form action="{{ route('pendaftaran.batal', $draf) }}" method="POST" onsubmit="return confirm('Batalkan draf {{ $draf->kode_tiket }}? Nomor tiket akan dilepas.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
            </form>
        </div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-custom p-4">
            <form action="{{ route('pendaftaran.tahap1.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    @include('pendaftaran._identitas', [
                        'nomorTiket' => $nomorOtomatis,
                        'noBerkas' => $draf->no_berkas ?? '',
                    ])
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('pendaftaran.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-gold px-4">
                        Simpan &amp; Lanjut <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-signpost-split text-primary me-2"></i>Alur Wizard</h6>
            <ol class="ps-3 mb-0 small text-muted">
                <li class="mb-2"><strong class="text-dark">Biodata &amp; No Tiket</strong><br>Nomor tiket dipesan agar tidak bentrok.</li>
                <li class="mb-2"><strong class="text-dark">Ploting</strong><br>Bila belum diploting, cetak antrian dan serahkan ke Loket Ploting.</li>
                <li class="mb-2"><strong class="text-dark">Data Permohonan</strong><br>Jenis permohonan, no hak, letak tanah, dan bidang.</li>
                <li><strong class="text-dark">Kebutuhan Warkah</strong><br>BT, SU, keduanya, atau tidak perlu.</li>
            </ol>
        </div>
    </div>
</div>
@endsection
