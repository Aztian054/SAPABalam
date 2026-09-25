@extends('layouts.app')

@section('title', 'Verifikator — Detail Berkas')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'verifikasi', 'stageLabel' => 'Verifikator'])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clipboard2-check text-primary me-2"></i>Hasil Pemeriksaan Verifikator</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('verifikator.simpan', $tiket->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status Pemeriksaan <span class="text-danger">*</span></label>
                            <select name="status_verifikasi" class="form-select" required>
                                <option value="">— Pilih —</option>
                                <option value="lengkap" {{ ($verifikasi->status ?? '') === 'lengkap' ? 'selected' : '' }}>LENGKAP — lanjut ke Warkah</option>
                                <option value="perbaikan" {{ ($verifikasi->status ?? '') === 'perbaikan' ? 'selected' : '' }}>PERBAIKAN — kembali ke pemohon</option>
                                <option value="konsul" {{ ($verifikasi->status ?? '') === 'konsul' ? 'selected' : '' }}>KONSULTASI — perlu pengecekan lanjut</option>
                                <option value="batal" {{ ($verifikasi->status ?? '') === 'batal' ? 'selected' : '' }}>BATAL — permohonan tidak dilanjutkan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dokumen Kurang (jika tidak lengkap)</label>
                            <select name="dokumen_kurang[]" class="form-select" id="dokumenKurang" multiple>
                                @foreach($templateKoreksis as $sk)
                                    <option value="{{ $sk->nama_dokumen_kurang }}">{{ $sk->nama_dokumen_kurang }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tekan Ctrl untuk memilih banyak dokumen.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan / Saran Koreksi</label>
                            <textarea name="catatan" id="catatan" rows="4" class="form-control" placeholder="Tulis catatan pemeriksaan...">{{ $verifikasi->catatan ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-gold"><i class="bi bi-save me-1"></i>Simpan Hasil Pemeriksaan</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($isActive && !$mine)
        <div class="alert alert-warning">
            <i class="bi bi-person-lock me-1"></i>
            Berkas ini sedang diproses oleh akun lain pada tahap Verifikator.
        </div>
    @endif

    @include('partials.timeline')
@endsection