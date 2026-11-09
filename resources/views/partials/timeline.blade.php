@php
    /**
     * Riwayat Timeline memuat nama petugas yang menangani berkas, sehingga tidak
     * boleh tampil di halaman petugas. Aturan akses:
     *   - hanya Loket Pendaftaran, Admin, dan Kakan;
     *   - hanya setelah tiket berstatus "selesai" (arsip final).
     * Peran petugas memakai "Alur Eksekusi Tiket" (partials.alur_eksekusi)
     * sebagai gantinya.
     */
    $bolehLihatTimeline = (auth()->user()?->isAdmin() || auth()->user()?->isKakan() || auth()->user()?->isLoketPendaftaran())
        && $tiket->status === 'selesai';
@endphp

@if($bolehLihatTimeline)
    <div class="card card-custom mb-3">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Timeline</h6>
        </div>
        <div class="card-body">
            @if($tiket->riwayatStatuses->isEmpty())
                <p class="text-muted mb-0">Belum ada riwayat.</p>
            @else
                <div class="timeline">
                    @foreach($tiket->riwayatStatuses as $r)
                        <div class="d-flex gap-3 mb-3">
                            <div class="text-center timeline-label">
                                <span class="d-inline-block rounded-circle bg-primary text-white fw-bold timeline-badge">{{ $loop->iteration }}</span>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark small">{{ $r->stage_dari }} → {{ $r->stage_ke }}</div>
                                <div class="text-muted small">{{ $r->keterangan }}</div>
                                <div class="text-secondary" style="font-size:0.72rem;">
                                    {{ $r->pelaku_label }} &bull; {{ $r->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Petugas: alur eksekusi sudah tampil lewat partials.tiket_header. --}}
@endif