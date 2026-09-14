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
                        <div class="text-center" style="min-width:34px;">
                            <span class="d-inline-block rounded-circle bg-primary text-white fw-bold" style="width:30px;height:30px;line-height:30px;font-size:0.7rem;">{{ $loop->iteration }}</span>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark small">{{ $r->stage_dari }} → {{ $r->stage_ke }}</div>
                            <div class="text-muted small">{{ $r->keterangan }}</div>
                            <div class="text-secondary" style="font-size:0.72rem;">
                                {{ $r->user?->name ?? 'Sistem' }} &bull; {{ $r->created_at?->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>