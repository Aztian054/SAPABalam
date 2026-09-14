@extends('layouts.app')

@section('title', 'Warkah — Detail Tiket')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Tiket {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'warkah', 'stageLabel' => 'Warkah'])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-archive text-primary me-2"></i>Lembar Kerja Warkah</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('warkah.simpan', $tiket->id) }}" method="POST">
                    @csrf
                    @if(($lembarKerja->status_sertipikat ?? null) === 'diserahkan' || $tiket->diserahkan_ke_validator)
                        <div class="alert alert-info py-2">
                            <i class="bi bi-unlock-fill me-1"></i>
                            Status sertipikat <strong>DISERAHKAN</strong> — gerbang <strong>Validator BT/SU</strong> telah terbuka.
                            Validator dapat mulai secara paralel (tanpa menunggu Verifikator).
                        </div>
                    @endif
                    @php
                        $fields = [
                            'status_berkas_dataset' => 'Berkas & Dataset',
                            'status_data_sertipikat_bt' => 'Data Sertipikat Buku Tanah (BT)',
                            'status_data_sertipikat_su' => 'Data Sertipikat Surat Ukur (SU)',
                            'status_sosialisasi' => 'Sosialisasi / Pemberitahuan',
                            'status_public_response' => 'Public Response / Tanggapan',
                        ];
                    @endphp
                    <div class="row g-3">
                        @foreach($fields as $key => $label)
                            <div class="col-md-6">
                                <label class="form-label">{{ $label }}</label>
                                <select name="{{ $key }}" class="form-select">
                                    @foreach(['belum', 'proses', 'selesai'] as $opt)
                                        <option value="{{ $opt }}" {{ ($lembarKerja->{$key} ?? 'belum') === $opt ? 'selected' : '' }}>
                                            {{ ucfirst($opt) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                        <div class="col-md-6">
                            <label class="form-label">Status Sertipikat</label>
                            <select name="status_sertipikat" class="form-select">
                                @php $statusSert = $lembarKerja->status_sertipikat; @endphp
                                @foreach(['proses', 'diserahkan'] as $opt)
                                    <option value="{{ $opt }}" {{ $statusSert === $opt ? 'selected' : '' }}>{{ strtoupper($opt) }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih <strong>DISERAHKAN</strong> untuk membuka Validator BT/SU (alur paralel).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batas (Tanjung) — Luas Terukur</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-bounding-box"></i></span>
                                <input type="number" step="0.01" name="dimensi_luas" class="form-control" disabled
                                       value="{{ optional($tiket->bidangTanahs->first())->luas_m2 }}">
                                <span class="input-group-text bg-white text-muted small">dari Bidang</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Dokumen BT</label>
                            <select name="status_dokumen_bt" class="form-select">
                                @foreach(['ada', 'tidak_ada'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_dokumen_bt ?? 'ada') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Dokumen SU</label>
                            <select name="status_dokumen_su" class="form-select">
                                @foreach(['ada', 'tidak_ada'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_dokumen_su ?? 'ada') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gabungan / Combine</label>
                            <select name="gabungan" class="form-select">
                                <option value="0" {{ !$lembarKerja->gabungan ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ $lembarKerja->gabungan ? 'selected' : '' }}>Ya</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Diserahkan</label>
                            <input type="date" name="tanggal_diserahkan" class="form-control" value="{{ optional($lembarKerja->tanggal_diserahkan)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" class="form-control" value="{{ optional($lembarKerja->tanggal_kembali)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Berkas</label>
                            <input type="number" min="0" name="jumlah_berkas" class="form-control" value="{{ $lembarKerja->jumlah_berkas }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Halaman</label>
                            <input type="number" min="0" name="jumlah_halaman" class="form-control" value="{{ $lembarKerja->jumlah_halaman }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Berkas Dikembalikan</label>
                            <input type="number" min="0" name="jumlah_berkas_dikembalikan" class="form-control" value="{{ $lembarKerja->jumlah_berkas_dikembalikan }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="pengembalian_sementara" value="1" id="pengembalianSementara" {{ $lembarKerja->pengembalian_sementara ? 'checked' : '' }}>
                                <label class="form-check-label" for="pengembalianSementara">Pengembalian Sementara (dokumen dikembalikan dulu, akan diserahkan lagi)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Status</label>
                            <textarea name="keterangan_status" rows="2" class="form-control">{{ $lembarKerja->keterangan_status }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" rows="3" class="form-control">{{ $lembarKerja->catatan }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Progres Warkah</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($isActive && !$mine)
        <div class="alert alert-warning"><i class="bi bi-person-lock me-1"></i>Tiket ini sedang diproses akun lain pada tahap Warkah.</div>
    @endif

    @include('partials.timeline')
@endsection