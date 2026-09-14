@php
    // Peta nama tahap di DB (tiket_penugasan.stage) → prefix nama route:
    // tahap 'verifikasi' dilayani route 'verifikator.*', 'validasi_btel' → 'validator_btel.*', dst.
    $routeBase = [
        'verifikasi' => 'verifikator',
        'validasi_btel' => 'validator_btel',
        'validasi_suel' => 'validator_suel',
    ][$stage ?? ''] ?? $stage ?? '';
@endphp

@php
    $activePenugasan = $tiket->penugasanAktif($stage ?? '');
    $isMine = $activePenugasan && $activePenugasan->user_id === auth()->id();
@endphp

@if($stage && $isMine)
    <div class="card card-custom border-0 shadow-sm mb-3" style="background:#f0f7ff;">
        <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="text-primary">
                <i class="bi bi-check2-circle me-1"></i>
                <strong>Tiket ini sedang ada dalam antrian pekerjaan Anda.</strong>
                <small class="d-block text-muted">Selesaikan (kembali ke DB Admin) atau kembalikan untuk revisi.</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelesai{{ $tiket->id }}">
                    <i class="bi bi-check2-square me-1"></i>Proses Selesai
                </button>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalRevisi{{ $tiket->id }}">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan (Revisi)
                </button>
                <a href="{{ route($routeBase . '.print-perbaikan', $tiket->id) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i>Cetak Form Perbaikan
                </a>
                <form action="{{ route($routeBase . '.lepas', $tiket->id) }}" method="POST" onsubmit="return confirm('Lepas tiket ini kembali ke DB Admin?')">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Lepas</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Selesai --}}
    <div class="modal fade" id="modalSelesai{{ $tiket->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route($routeBase . '.selesai', $tiket->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title">Selesaikan {{ $tiket->kode_tiket }}</h6>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Konfirmasi bahwa pekerjaan pada tahap <strong>{{ $stageLabel ?? '' }}</strong> telah selesai.
                    Tiket akan kembali ke <strong>Database Admin</strong> (atau berstatus SELESAI bila seluruh tahap BT & SU beres).</p>
                    <textarea name="catatan" rows="3" class="form-control" placeholder="Catatan penyelesaian (opsional)"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success"><i class="bi bi-check2-square me-1"></i>Ya, Selesaikan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Revisi --}}
    <div class="modal fade" id="modalRevisi{{ $tiket->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route($routeBase . '.revisi', $tiket->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header bg-warning">
                    <h6 class="modal-title text-dark">Kembalikan {{ $tiket->kode_tiket }}</h6>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tujuan Pengembalian</label>
                        <select name="ke_stage" class="form-select">
                            <option value="admin">DB Admin (perbaikan antar-tahap)</option>
                            <option value="loket">Loket (pemohon melengkapi berkas)</option>
                            <option value="verifikasi">Kembali ke Verifikator</option>
                            <option value="warkah">Kembali ke Warkah</option>
                        </select>
                    </div>
                    <label class="form-label">Isi Catatan Revisi <span class="text-danger">*</span></label>
                    <textarea name="isi_revisi" id="isi_revisi" rows="4" class="form-control" required placeholder="Jelaskan kekurangan yang harus diperbaiki..."></textarea>
                    @if(isset($templateKoreksis) && $templateKoreksis->isNotEmpty())
                        <label class="form-label mt-3">Contoh Saran Koreksi</label>
                        <select class="form-select" onchange="document.getElementById('isi_revisi').value = this.value;">
                            <option value="">— Pilih template —</option>
                            @foreach($templateKoreksis as $sk)
                                <option value="{{ $sk->pesan_koreksi }}">{{ $sk->nama_dokumen_kurang }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-warning text-white"><i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan</button>
                </div>
            </form>
        </div>
    </div>
@endif