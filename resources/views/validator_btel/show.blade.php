@extends('layouts.app')

@section('title', 'Validator BT — Detail Tiket')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Tiket {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'validasi_btel', 'stageLabel' => 'Validator BT'])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Lembar Validasi Pra-Buku Tanah Elektronik</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('validator_btel.simpan', $tiket->id) }}" method="POST">
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
                            <label class="form-label">Kesesuaian NIB</label>
                            <select name="kesesuaian_nib" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['sesuai', 'tidak_sesuai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembar->kesesuaian_nib ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Validasi <span class="text-danger">*</span></label>
                            <select name="status_validasi" class="form-select" required>
                                <option value="proses" {{ ($lembar->status_validasi ?? 'proses') === 'proses' ? 'selected' : '' }}>Proses</option>
                                <option value="lulus" {{ ($lembar->status_validasi ?? '') === 'lulus' ? 'selected' : '' }}>LULUS</option>
                                <option value="perlu_koreksi" {{ ($lembar->status_validasi ?? '') === 'perlu_koreksi' ? 'selected' : '' }}>Perlu Koreksi</option>
                                <option value="ditolak" {{ ($lembar->status_validasi ?? '') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan Validasi</label>
                            <textarea name="catatan" rows="3" class="form-control">{{ $lembar->catatan ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Hasil Validasi BT</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($isActive && !$mine)
        <div class="alert alert-warning"><i class="bi bi-person-lock me-1"></i>Tiket ini sedang diproses akun lain pada tahap Validator BT.</div>
    @endif

    @include('partials.timeline')
@endsection