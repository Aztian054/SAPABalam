@php
    // Peta nama tahap di DB (tiket_penugasan.stage) → prefix nama route:
    // tahap 'verifikasi' dilayani route 'verifikator.*', 'validasi_btel' → 'validator_btel.*', dst.
    $routeBase = [
        'verifikasi' => 'verifikator',
        'validasi_btel' => 'validator_btel',
        'validasi_suel' => 'validator_suel',
    ][$stage ?? ''] ?? $stage ?? '';

    // Tujuan pengembalian bawaan per tahap (bila tidak diubah petugas).
    $keStageDefault = [
        'verifikasi' => 'loket',
        'warkah' => 'admin',
        'validasi_btel' => 'warkah',
        'validasi_suel' => 'warkah',
        'alih_media_btel' => 'admin',
        'alih_media_suel' => 'admin',
    ][$stage ?? ''] ?? 'admin';
@endphp

@php
    $activePenugasan = $tiket->penugasanAktif($stage ?? '');
    $isMine = $activePenugasan && $activePenugasan->user_id === auth()->id();
    $canSelesai = $canSelesai ?? true;
@endphp

@if($stage && $isMine)
    <div class="card card-custom border-0 shadow-sm mb-3" style="background:#f0f7ff;">
        <div class="card-body py-3">
            <div class="text-primary mb-2">
                <i class="bi bi-check2-circle me-1"></i>
                <strong>Berkas ini sedang ada dalam antrian pekerjaan Anda.</strong>
                @if(! $canSelesai)
                    <small class="d-block text-muted mt-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Proses Selesai masih terkunci — berkas BT/SU belum dicatat pengembaliannya ke Warkah.
                    </small>
                @else
                    <small class="d-block text-muted">Catatan pada lembar kerja di bawah otomatis dipakai untuk proses selesai maupun revisi.</small>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelesai{{ $tiket->id }}" {{ ! $canSelesai ? 'disabled' : '' }}>
                    <i class="bi bi-check2-square me-1"></i>Proses Selesai
                </button>
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalRevisi{{ $tiket->id }}">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan (Revisi)
                </button>
                <a href="{{ route($routeBase . '.print-perbaikan', $tiket->id) }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer me-1"></i>Cetak Form Perbaikan
                </a>
                <form action="{{ route($routeBase . '.lepas', $tiket->id) }}" method="POST" onsubmit="return confirm('Lepas berkas ini kembali ke DB Admin?')">
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
                <input type="hidden" name="catatan" id="catatanSelesai{{ $tiket->id }}">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title">Selesaikan {{ $tiket->kode_tiket }}</h6>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Konfirmasi bahwa pekerjaan pada tahap <strong>{{ $stageLabel ?? '' }}</strong> telah selesai.
                    Berkas akan kembali ke <strong>Database Admin</strong> (atau berstatus SELESAI bila seluruh tahap BT & SU beres).</p>
                    <p><small class="text-muted">Catatan akan otomatis diambil dari kolom catatan pada lembar kerja.</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-square me-1"></i>Ya, Selesaikan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Revisi --}}
    <div class="modal fade" id="modalRevisi{{ $tiket->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route($routeBase . '.revisi', $tiket->id) }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="isi_revisi" id="isiRevisi{{ $tiket->id }}">
                <div class="modal-header bg-warning">
                    <h6 class="modal-title text-dark">Kembalikan {{ $tiket->kode_tiket }}</h6>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tujuan Pengembalian</label>
                        <select name="ke_stage" class="form-select">
                            <option value="admin" {{ ($keStageDefault ?? 'admin') === 'admin' ? 'selected' : '' }}>DB Admin (perbaikan antar-tahap)</option>
                            <option value="loket" {{ ($keStageDefault ?? '') === 'loket' ? 'selected' : '' }}>Loket (pemohon melengkapi berkas)</option>
                            <option value="verifikasi" {{ ($keStageDefault ?? '') === 'verifikasi' ? 'selected' : '' }}>Kembali ke Verifikator</option>
                            <option value="warkah" {{ ($keStageDefault ?? '') === 'warkah' ? 'selected' : '' }}>Kembali ke Warkah</option>
                        </select>
                    </div>
                    <p><small class="text-muted">Isi revisi akan otomatis diambil dari kolom catatan pada lembar kerja (wajib diisi).</small></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white"><i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Salin catatan lembar kerja (id=catatan) ke field tersembunyi saat modal dikonfirmasi. --}}
    <script>
        (function () {
            const syncKeSelesai = () => {
                const catatanUtama = document.getElementById('catatan');
                document.getElementById('catatanSelesai{{ $tiket->id }}').value = catatanUtama ? catatanUtama.value : '';
            };

            const syncKeRevisi = (e) => {
                const catatanUtama = document.getElementById('catatan');
                const isiRevisi = catatanUtama ? catatanUtama.value.trim() : '';

                if (! isiRevisi) {
                    e.preventDefault();
                    alert('Harap isi catatan pada lembar kerja terlebih dahulu untuk mengembalikan berkas (revisi).');
                    return false;
                }

                document.getElementById('isiRevisi{{ $tiket->id }}').value = isiRevisi;
            };

            document.getElementById('modalSelesai{{ $tiket->id }}').addEventListener('submit', syncKeSelesai);
            document.getElementById('modalRevisi{{ $tiket->id }}').addEventListener('submit', syncKeRevisi);
        })();
    </script>
@endif