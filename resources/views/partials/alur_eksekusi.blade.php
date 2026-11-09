@php
    $alur = $tiket->alurEksekusi();

    $badgeWarna = [
        'selesai' => 'bg-success text-white',
        'proses' => 'bg-warning text-dark',
        'menunggu' => 'bg-light text-muted border',
        'batal' => 'bg-danger text-white',
    ];

    $pillWarna = [
        'selesai' => 'bg-success-subtle text-success border border-success-subtle',
        'proses' => 'bg-warning-subtle text-dark border border-warning-subtle',
        'menunggu' => 'bg-light text-muted border',
        'batal' => 'bg-danger-subtle text-danger border border-danger-subtle',
    ];
@endphp

<div class="card card-custom mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-signpost-split-fill text-primary me-2"></i>Alur Eksekusi Tiket
        </h6>
    </div>
    <div class="card-body py-3">
        @if(!empty($alur['notice']))
            <div class="alert alert-{{ $alur['notice_type'] ?? 'warning' }} py-2 small mb-3" role="alert">
                <i class="bi {{ ($alur['notice_type'] ?? '') === 'danger' ? 'bi-x-octagon-fill' : 'bi-arrow-clockwise' }} me-1"></i>
                {{ $alur['notice'] }}
            </div>
        @endif

        <div class="alur-eksekusi">
            @foreach($alur['items'] as $item)
                <div class="alur-step">
                    <span class="alur-badge {{ $badgeWarna[$item['status']] }}">
                        @if($item['status'] === 'selesai')
                            <i class="bi bi-check-lg"></i>
                        @elseif($item['status'] === 'batal')
                            <i class="bi bi-x-lg"></i>
                        @else
                            {{ $item['no'] }}
                        @endif
                    </span>
                    <div class="alur-body">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-semibold text-dark">{{ $item['label'] }}</span>
                            <span class="badge {{ $pillWarna[$item['status']] }} text-uppercase" style="font-size:0.65rem;">
                                {{ $item['status_label'] }}
                            </span>
                        </div>
                        @if(!empty($item['sub']))
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($item['sub'] as $namaSub => $statusSub)
                                    <span class="badge {{ $pillWarna[$statusSub] }} rounded-pill" style="font-size:0.65rem;">
                                        <i class="bi {{ $statusSub === 'selesai' ? 'bi-check-circle-fill' : ($statusSub === 'proses' ? 'bi-hourglass-split' : 'bi-circle') }} me-1"></i>
                                        {{ $namaSub }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .alur-eksekusi { position: relative; }
    .alur-step {
        position: relative;
        padding: 0 0 1rem 2.7rem;
    }
    .alur-step:last-child { padding-bottom: 0; }
    .alur-step::before {
        content: '';
        position: absolute;
        left: 0.98rem;
        top: 1.95rem;
        bottom: -0.4rem;
        width: 2px;
        background: #e2e8f0;
    }
    .alur-step:last-child::before { display: none; }
    .alur-badge {
        position: absolute;
        left: 0;
        top: 0;
        width: 2.05rem;
        height: 2.05rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.78rem;
        z-index: 1;
    }
    .alur-body { padding-top: 2px; }
</style>