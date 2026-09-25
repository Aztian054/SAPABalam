@extends('layouts.app')

@section('title', 'Alih Media SU — Detail Berkas')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'alih_media_suel', 'stageLabel' => 'Alih Media SU'])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-spreadsheet-fill text-primary me-2"></i>Lembar Kerja Alih Media SU</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('alih_media_suel.simpan', $tiket->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Scan Surat Ukur</label>
                            <select name="status_scan_surat_ukur" class="form-select">
                                @foreach(['belum', 'sudah', 'kualitas_buruk'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->status_scan_surat_ukur ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Upload KKP</label>
                            <select name="status_upload_kkp" class="form-select">
                                @foreach(['belum', 'sudah'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->status_upload_kkp ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">TTD Elektronik</label>
                            <select name="status_ttd_elektronik" class="form-select">
                                @foreach(['belum', 'sudah'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->status_ttd_elektronik ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Terbit Sertifikat El.</label>
                            <input type="date" name="tanggal_terbit_sertifikat_el" class="form-control" value="{{ $lembar->tanggal_terbit_sertifikat_el?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control">{{ $lembar->catatan ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-gold"><i class="bi bi-save me-1"></i>Simpan Progres Alih Media SU</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($isActive && !$mine)
        <div class="alert alert-warning"><i class="bi bi-person-lock me-1"></i>Berkas ini sedang diproses akun lain pada tahap Alih Media SU.</div>
    @endif

    @include('partials.timeline')
@endsection