<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Berkas {{ $tiket->kode_tiket }} — Kantah Bandar Lampung</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/images/logobpn2026.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bpn-navy: #0b2239; --bpn-gold: #c69214; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .hero-banner {
            background: linear-gradient(135deg, #0b2239 0%, #163e66 100%);
            color: #fff;
            padding: 3rem 1rem 4rem;
            text-align: center;
        }
        .result-card { margin-top: -35px; border-radius: 16px; }
        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            background: #fff;
        }
        .stepper-item { position: relative; text-align: center; flex: 1; }
        .stepper-circle {
            width: 44px; height: 44px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 0.5rem; font-weight: bold;
        }
        .logo-sm { width: 56px; height: 56px; }
        .max-w-600 { max-width: 600px; }
        .max-w-900 { max-width: 900px; }
        .td-label { width: 120px; }
    </style>
</head>
<body>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo BPN" class="rounded-circle bg-white object-fit-contain p-1 shadow logo-sm">
                <h5 class="mb-0 fw-bold">Kantor Pertanahan Kota Bandar Lampung</h5>
            </div>
            <h2 class="fw-bold mb-2">Detail Status Berkas</h2>
            <p class="text-white-50 mb-0 max-w-600 mx-auto">
                Link pelacakan permanen dari loket. Status diperbarui otomatis setiap ada perpindahan tahap.
            </p>
        </div>
    </div>

    <div class="container py-4 max-w-900">
        <div class="card card-custom p-4 mb-4 border-top border-4 border-primary result-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                <div>
                    <span class="badge bg-secondary mb-1">Kode Tiket Resmi</span>
                    <h3 class="fw-bold text-dark mb-0">{{ $tiket->kode_tiket }}</h3>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $tiket->status_badge }} fs-6 px-3 py-2">{{ $tiket->status_label }}</span>
                    @if($tiket->status_pembetulan !== 'P0')
                        <span class="badge bg-warning text-dark fs-6">{{ $tiket->status_pembetulan }}</span>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('tracking.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i>Cari Berkas Lain
                        </a>
                    </div>
                </div>
            </div>
@php
                $posMap = [
                    'diterima' => 1, 'verifikasi' => 2, 'warkah' => 3,
                    'validasi_btel' => 4, 'validasi_suel' => 4,
                    'alih_media_btel' => 5, 'alih_media_suel' => 5,
                    'selesai' => 6,
                ];
                $currentPos = $posMap[$tiket->status] ?? 0;
                $stages = [
                    1 => ['label' => '1. Loket', 'icon' => 'bi-inbox-fill'],
                    2 => ['label' => '2. Verifikasi', 'icon' => 'bi-clipboard-check-fill'],
                    3 => ['label' => '3. Warkah', 'icon' => 'bi-archive-fill'],
                    4 => ['label' => '4. Validasi Pra-El', 'icon' => 'bi-shield-check'],
                    5 => ['label' => '5. Alih Media El', 'icon' => 'bi-file-earmark-diff-fill'],
                    6 => ['label' => 'Selesai', 'icon' => 'bi-patch-check-fill'],
                ];
                $isFrozen = in_array($tiket->status, ['dikembalikan', 'batal']);
            @endphp

            <div class="py-3">
                <div class="d-flex justify-content-between text-center">
                    @foreach($stages as $pos => $s)
                        @php
                            $isPassed = $pos <= $currentPos;
                            $isCurrent = $pos === $currentPos && !$isFrozen;
                            $isBatal = $tiket->status === 'batal';
                            $isDikembali = $tiket->status === 'dikembalikan';
                        @endphp
                        <div class="stepper-item">
                            <div class="stepper-circle @if($isBatal && $pos === $currentPos) bg-danger text-white @elseif($isDikembali && $pos === $currentPos) bg-warning text-dark @elseif($isCurrent) bg-warning text-dark @elseif($isPassed) bg-success text-white @else bg-light text-muted border @endif">
                                <i class="bi {{ $s['icon'] }}"></i>
                            </div>
                            <div class="small fw-semibold @if($isBatal && $pos === $currentPos) text-danger fw-bold @elseif($isCurrent) text-dark fw-bold @elseif($isPassed) text-success @else text-muted @endif" style="font-size: 0.76rem;">
                                {{ $s['label'] }}
                            </div>
                            @if($pos === 4 || $pos === 5)
                                @php
                                    $statusBt = $pos === 4 ? 'validasi_btel' : 'alih_media_btel';
                                    $statusSu = $pos === 4 ? 'validasi_suel' : 'alih_media_suel';
                                @endphp
                                <div class="d-flex justify-content-center gap-1 mt-1">
                                    <span class="badge {{ $tiket->status === $statusBt ? 'bg-warning text-dark' : ($isPassed ? 'bg-success' : 'bg-light text-muted border') }} mt-1" style="font-size:0.62rem;">BT</span>
                                    <span class="badge {{ $tiket->status === $statusSu ? 'bg-warning text-dark' : ($isPassed ? 'bg-success' : 'bg-light text-muted border') }} mt-1" style="font-size:0.62rem;">SU</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
@if($tiket->status === 'dikembalikan')
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    <div class="small">
                        Berkas <strong>dikembalikan untuk perbaikan</strong> (revisi ke-{{ $tiket->revisi_ke }}).
                        Silakan hubungi petugas loket terkait catatan kekurangan berkas Anda.
                    </div>
                </div>
            @elseif($tiket->status === 'batal')
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-x-circle fs-4"></i>
                    <div class="small">Permohonan ini berstatus <strong>Dibatalkan</strong>. Hubungi petugas loket untuk informasi lebih lanjut.</div>
                </div>
            @elseif($tiket->status === 'selesai')
                <div class="alert alert-success d-flex align-items-center gap-3">
                    <i class="bi bi-patch-check-fill fs-1 text-success"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-success">Sertifikat Elektronik Telah Terbit!</h6>
                        <p class="small mb-0">Permohonan Anda telah selesai diproses dan Sertifikat Elektronik telah ditandatangani. Silakan datang ke Loket Pengambilan atau unduh melalui akun resmi Anda.</p>
                    </div>
                </div>
            @endif
<div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-fill text-primary"></i> Data Pemohon</h6>
                        <div class="table-responsive">
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted td-label">Nama Pemohon</td>
                                <td class="fw-bold">: {{ $tiket->nama_pemohon }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIK</td>
                                <td>: {{ $tiket->nik_pemohon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tgl. Masuk</td>
                                <td>: {{ $tiket->tanggal_masuk->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. HP</td>
                                <td>: {{ $tiket->no_hp_pemohon }}</td>
                            </tr>
                        </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-briefcase-fill text-warning"></i> Layanan Pertanahan</h6>
                        <div class="table-responsive">
                        <table class="table table-sm table-borderless small mb-0">
                            <tr>
                                <td class="text-muted td-label">Jenis Layanan</td>
                                <td class="fw-bold">: {{ $tiket->jenisPermohonan->nama }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Hak Sekarang</td>
                                <td>: {{ $tiket->no_hak_sekarang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lokasi</td>
                                <td>: {{ $tiket->kelurahan_desa ?? '-' }}, {{ $tiket->kecamatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jumlah Bidang</td>
                                <td>: {{ $tiket->jumlah_bidang }}</td>
                            </tr>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Perjalanan Berkas</h6>
            @if($tiket->riwayatStatuses->isNotEmpty())
                <div class="list-group list-group-flush small">
                    @foreach($tiket->riwayatStatuses as $r)
                        <div class="list-group-item px-0 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-primary">{{ $r->stage_ke }}</span>
                                <small class="text-muted">{{ $r->created_at->format('d/m/Y H:i') }} WIB</small>
                            </div>
                            <div class="text-muted">{{ $r->keterangan ?? '-' }}</div>
                            @if($r->user)
                                <small class="text-secondary">Oleh: {{ $r->user->name }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small mb-0">Belum ada riwayat perpindahan untuk berkas ini.</p>
            @endif
        </div>
    </div>

    <footer class="text-center text-muted small py-4 mt-5 border-top bg-white">
        &copy; {{ date('Y') }} Kantor Pertanahan Kota Bandar Lampung &bull; Layanan Cepat, Transparan dan Akuntabel.
    </footer>

</body>
</html>