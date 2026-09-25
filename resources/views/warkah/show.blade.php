@extends('layouts.app')

@section('title', 'Warkah — Detail Berkas')

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="fw-bold text-dark mb-0">Detail Berkas {{ $tiket->kode_tiket }}</h4>
    </div>

    @include('partials.tiket_header')
    @include('partials.stage_actions', ['stage' => 'warkah', 'stageLabel' => 'Warkah', 'canSelesai' => $canSelesai ?? true])
    @include('partials.bidang_table')

    @if($isActive && $mine)
        {{-- Serah Terima & Pengembalian Berkas BT/SU --}}
        @php $sp = $lembarKerja->status_pengembalian; @endphp
        <div class="card card-custom mb-3 border-start border-4 {{ $sp === 'dikembalikan' ? 'border-success' : ($sp === 'dipinjam' ? 'border-warning' : 'border-secondary') }}">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-box-arrow-in-right text-primary me-2"></i>Serah Terima &amp; Pengembalian Berkas BT/SU</h6>
                @if($sp === 'dipinjam')
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>DIPINJAM</span>
                @elseif($sp === 'dikembalikan')
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>DIKEMBALIKAN</span>
                @else
                    <span class="badge bg-secondary"><i class="bi bi-circle me-1"></i>BELUM DISERAHKAN</span>
                @endif
            </div>
            <div class="card-body py-3">
                <div class="row g-3 align-items-start">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Status Berkas BT/SU</small>
                        @if($tiket->isDiserahkanKeValidator())
                            <strong class="text-primary">Telah Diserahkan ke Validator BT/SU</strong>
                        @else
                            <strong class="text-secondary">Belum Diserahkan ke Validator BT/SU</strong>
                        @endif
                        <br><small class="text-muted d-block mt-1">{{ \App\Models\LembarKerjaWarkah::STATUS_PENGEMBALIAN[$sp] ?? 'Belum Diserahkan' }}</small>
                    </div>
                    @if($lembarKerja->nama_penerima_validator)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Petugas Validator Penerima</small>
                            <strong>{{ $lembarKerja->nama_penerima_validator }}</strong>
                        </div>
                    @endif
                    @if($lembarKerja->waktu_serah)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tanggal &amp; Jam Serah</small>
                            <strong>{{ $lembarKerja->waktu_serah->format('d/m/Y H:i') }}</strong>
                        </div>
                    @elseif($lembarKerja->tanggal_diserahkan)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tanggal Diserahkan</small>
                            <strong>{{ $lembarKerja->tanggal_diserahkan->format('d/m/Y') }}</strong>
                        </div>
                    @endif
                    @if($lembarKerja->status_berkas_bt || $lembarKerja->status_berkas_su)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Kondisi Berkas Saat Serah</small>
                            <strong>
                                BT: <span class="text-uppercase">{{ \App\Models\LembarKerjaWarkah::KONDISI_BERKAS[$lembarKerja->status_berkas_bt] ?? '-' }}</span>
                                &bull;
                                SU: <span class="text-uppercase">{{ \App\Models\LembarKerjaWarkah::KONDISI_BERKAS[$lembarKerja->status_berkas_su] ?? '-' }}</span>
                            </strong>
                        </div>
                    @endif
                    @if($lembarKerja->petugas_pengembali)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Petugas Pengembali</small>
                            <strong>{{ $lembarKerja->petugas_pengembali }}</strong>
                        </div>
                    @endif
                    @if($lembarKerja->waktu_kembali)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Waktu Terima Kembali</small>
                            <strong>{{ $lembarKerja->waktu_kembali->format('d/m/Y H:i') }}</strong>
                        </div>
                    @elseif($lembarKerja->tanggal_kembali)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Tanggal Kembali</small>
                            <strong>{{ $lembarKerja->tanggal_kembali->format('d/m/Y') }}</strong>
                        </div>
                    @endif
                    @if($lembarKerja->kondisi_berkas_kembali)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Kondisi Berkas Saat Kembali</small>
                            <strong class="text-uppercase">{{ \App\Models\LembarKerjaWarkah::KONDISI_BERKAS[$lembarKerja->kondisi_berkas_kembali] }}</strong>
                        </div>
                    @endif
                    @if($lembarKerja->catatan_kondisi_berkas)
                        <div class="col-12">
                            <small class="text-muted d-block">Catatan Kondisi Berkas (Saat Serah)</small>
                            <div class="border rounded-1 px-2 py-1 bg-light">{{ $lembarKerja->catatan_kondisi_berkas }}</div>
                        </div>
                    @endif
                    @if($lembarKerja->catatan_pengembalian)
                        <div class="col-12">
                            <small class="text-muted d-block">Catatan Pengembalian</small>
                            <div class="border rounded-1 px-2 py-1 bg-light">{{ $lembarKerja->catatan_pengembalian }}</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-white d-flex gap-2 flex-wrap align-items-center justify-content-between">
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @if(! $tiket->isDiserahkanKeValidator())
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSerah{{ $tiket->id }}">
                            <i class="bi bi-send me-1"></i>Serahkan Berkas Warkah
                        </button>
                    @else
                        <span class="btn btn-success disabled" style="pointer-events:none">
                            <i class="bi bi-check-circle me-1"></i>✓ Diserahkan — {{ $lembarKerja->waktu_serah?->format('d/m/Y H:i') ?? $lembarKerja->tanggal_diserahkan?->format('d/m/Y') }}
                        </span>
                    @endif

                    @if($sp === 'dipinjam')
                        @if($canKonfirmasiKembali)
                            <button type="button" class="btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalKembali{{ $tiket->id }}">
                                <i class="bi bi-arrow-return-left me-1"></i>Konfirmasi Pengembalian Berkas
                            </button>
                        @else
                            <button type="button" class="btn btn-warning text-white disabled" disabled>
                                <i class="bi bi-lock-fill me-1"></i>Konfirmasi Pengembalian Berkas
                            </button>
                        @endif
                    @elseif($sp === 'dikembalikan')
                        <span class="btn btn-success disabled" style="pointer-events:none">
                            <i class="bi bi-check-circle me-1"></i>✓ Berkas Telah Dikembalikan — {{ $lembarKerja->waktu_kembali?->format('d/m/Y H:i') ?? $lembarKerja->tanggal_kembali?->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
                @if($sp === 'dipinjam' && ! $canKonfirmasiKembali)
                    <small class="text-danger">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Pengembalian terkunci sampai Alih Media selesai:
                        @if(! $alihMediaSelesai['alih_media_btel'])<span class="badge bg-danger text-white me-1">Alih Media BT</span>@endif
                        @if(! $alihMediaSelesai['alih_media_suel'])<span class="badge bg-danger text-white">Alih Media SU</span>@endif
                        belum selesai.
                    </small>
                @endif
            </div>
        </div>

        {{-- Modal Serahkan Berkas Warkah --}}
        @if(! $tiket->isDiserahkanKeValidator())
            <div class="modal fade" id="modalSerah{{ $tiket->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h6 class="modal-title"><i class="bi bi-send me-1"></i>Serahkan Berkas Warkah — Serah Terima ke Validator BT/SU</h6>
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('warkah.kirim', $tiket->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <p class="small text-muted mb-3">
                                    Berkas hardcopy BT/SU akan <strong>dipinjamkan ke Validator / Alih Media</strong>.
                                    Setelah tercatat, gerbang Validator BT/SU terbuka secara paralel tanpa menunggu Verifikator.
                                </p>
                                <div class="mb-3">
                                    <label class="form-label">Petugas Validator BT/SU Penerima <span class="text-danger">*</span></label>
                                    <select name="penerima_validator_id" class="form-select" required>
                                        <option value="">— Pilih Petugas Penerima —</option>
                                        @foreach($validatorUsers as $vu)
                                            <option value="{{ $vu->id }}">{{ $vu->name }} — {{ $vu->role_label }}</option>
                                        @endforeach
                                    </select>
                                    @if($validatorUsers->isEmpty())
                                        <small class="text-danger d-block">Belum ada akun Validator BT/SU aktif pada sistem.</small>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal &amp; Jam Serah <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="waktu_serah" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Status Berkas BT <span class="text-danger">*</span></label>
                                        <select name="status_berkas_bt" class="form-select" required>
                                            <option value="">— Pilih —</option>
                                            @foreach(\App\Models\LembarKerjaWarkah::KONDISI_BERKAS as $kode => $label)
                                                <option value="{{ $kode }}" {{ old('status_berkas_bt') === $kode ? 'selected' : '' }}>{{ strtoupper($label) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status Berkas SU <span class="text-danger">*</span></label>
                                        <select name="status_berkas_su" class="form-select" required>
                                            <option value="">— Pilih —</option>
                                            @foreach(\App\Models\LembarKerjaWarkah::KONDISI_BERKAS as $kode => $label)
                                                <option value="{{ $kode }}" {{ old('status_berkas_su') === $kode ? 'selected' : '' }}>{{ strtoupper($label) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Catatan Kondisi Berkas</label>
                                    <textarea name="catatan_kondisi_berkas" rows="3" class="form-control" placeholder="Contoh: Berkas BT &amp; SU lengkap, fisik baik, siap diverifikasi.">{{ old('catatan_kondisi_berkas') }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Serahkan Berkas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Konfirmasi Pengembalian Berkas --}}
        @if($sp === 'dipinjam' && $canKonfirmasiKembali)
            <div class="modal fade" id="modalKembali{{ $tiket->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h6 class="modal-title"><i class="bi bi-arrow-return-left me-1"></i>Konfirmasi Pengembalian Berkas BT/SU</h6>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('warkah.pengembalian', $tiket->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-warning py-2">
                                    <i class="bi bi-hourglass-split me-1"></i>
                                    Alih Media BT &amp; SU telah selesai. Konfirmasikan penerimaan kembali hardcopy BT/SU ke Warkah.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Petugas Pengembali <span class="text-danger">*</span></label>
                                    <input type="text" name="petugas_pengembali" class="form-control" required maxlength="100" value="{{ auth()->user()->name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Waktu Terima Kembali Berkas <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="waktu_kembali" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kondisi Berkas <span class="text-danger">*</span></label>
                                    <select name="kondisi_berkas_kembali" class="form-select" required>
                                        <option value="">— Pilih —</option>
                                        @foreach(\App\Models\LembarKerjaWarkah::KONDISI_BERKAS as $kode => $label)
                                            <option value="{{ $kode }}" {{ old('kondisi_berkas_kembali') === $kode ? 'selected' : '' }}>{{ ucfirst($label) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Catatan Pengembalian</label>
                                    <textarea name="catatan_pengembalian" rows="3" class="form-control" placeholder="Contoh: Berkas BT/SU diterima kembali lengkap tanpa kerusakan.">{{ old('catatan_pengembalian') }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning text-white"><i class="bi bi-check-circle me-1"></i>Konfirmasi Pengembalian Berkas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Lembar Kerja Warkah --}}
        <div class="card card-custom mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-archive text-primary me-2"></i>Lembar Kerja Warkah</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('warkah.simpan', $tiket->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Data Sertipikat Buku Tanah (BT)</label>
                            <select name="status_data_sertipikat_bt" class="form-select">
                                @foreach(['belum', 'proses', 'selesai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_data_sertipikat_bt ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data Sertipikat Surat Ukur (SU)</label>
                            <select name="status_data_sertipikat_su" class="form-select">
                                @foreach(['belum', 'proses', 'selesai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_data_sertipikat_su ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Warkah (Sosialisasi)</label>
                            <select name="status_sosialisasi" class="form-select">
                                @foreach(['belum', 'proses', 'selesai'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_sosialisasi ?? 'belum') === $opt ? 'selected' : '' }}>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Dokumen BT</label>
                            <select name="status_dokumen_bt" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['ada', 'tidak_ada'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_dokumen_bt ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Dokumen SU</label>
                            <select name="status_dokumen_su" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach(['ada', 'tidak_ada'] as $opt)
                                    <option value="{{ $opt }}" {{ ($lembarKerja->status_dokumen_su ?? '') === $opt ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $opt)) }}</option>
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
                            <label class="form-label">Jumlah Berkas</label>
                            <input type="number" min="0" name="jumlah_berkas" class="form-control" value="{{ $lembarKerja->jumlah_berkas }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jumlah Halaman</label>
                            <input type="number" min="0" name="jumlah_halaman" class="form-control" value="{{ $lembarKerja->jumlah_halaman }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Status</label>
                            <textarea name="keterangan_status" rows="2" class="form-control">{{ $lembarKerja->keterangan_status }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3" class="form-control">{{ $lembarKerja->catatan }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                        <button class="btn btn-gold"><i class="bi bi-save me-1"></i>Simpan Progres Warkah</button>
                        <small class="text-muted">Serah terima &amp; pengembalian berkas dikelola pada kartu <strong>“Serah Terima &amp; Pengembalian Berkas BT/SU”</strong> di atas.</small>
                    </div>
                </form>
            </div>
        </div>

        @elseif($isActive && !$mine)
        <div class="alert alert-warning"><i class="bi bi-person-lock me-1"></i>Berkas ini sedang diproses akun lain pada tahap Warkah.</div>
    @endif

    @include('partials.timeline')
@endsection