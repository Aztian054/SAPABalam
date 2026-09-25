@extends('layouts.app')

@section('title', 'Validator SU — Detail Berkas')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'validasi_suel', 'stageLabel' => 'Validator SU'])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-shaded text-primary me-2"></i>Lembar Validasi Pra-Surat Ukur Elektronik</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('validator_suel.simpan', $tiket->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kesesuaian Nama</label>
                            <select name="kesesuaian_nama" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['sesuai', 'tidak_sesuai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->kesesuaian_nama ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kesesuaian Luas</label>
                            <select name="kesesuaian_luas" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['sesuai', 'tidak_sesuai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->kesesuaian_luas ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cocok Letak</label>
                            <select name="cocok_letak" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['sesuai', 'tidak_sesuai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->cocok_letak ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Validasi <span class="text-danger">*</span></label>
                            <select name="status_validasi" class="form-select" required>
                                @php $sv = ($lembar->status_validasi ?? ''); @endphp
                                @if(! in_array($sv, ['lulus', 'ditolak'], true))
                                    <option value="{{ $sv }}" selected>{{ ucwords($sv) }} (disimpan)</option>
                                @endif
                                <option value="lulus" {{ $sv === 'lulus' ? 'selected' : '' }}>LULUS</option>
                                <option value="ditolak" {{ $sv === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan Validasi</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control">{{ $lembar->catatan ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-gold"><i class="bi bi-save me-1"></i>Simpan Hasil Validasi SU</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($isActive && !$mine)
        <div class="alert alert-warning"><i class="bi bi-person-lock me-1"></i>Berkas ini sedang diproses akun lain pada tahap Validator SU.</div>
    @endif

    @include('partials.timeline')
@endsection