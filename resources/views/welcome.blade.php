<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LOKET 2026') }} &mdash; Sistem Pelayanan Pertanahan</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/images/logobpn2026.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#0b2239; --gold:#c69214; }
        body { font-family:'Plus Jakarta Sans',sans-serif; }
        .hero { background:linear-gradient(135deg,var(--navy) 0%,#163659 100%); min-height:65vh; display:flex; align-items:center; }
        .btn-gold { background:var(--gold); color:#fff; border:none; }
        .btn-gold:hover { background:#b0810f; color:#fff; }
        .feature-card { border:none; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.05); transition:transform .2s; }
        .feature-card:hover { transform:translateY(-4px); }
        .footer-landing { background:var(--navy); }
        .max-w-560 { max-width:560px; }
        .hero-logo { width:150px; height:150px; object-fit:contain; }
        .feature-icon { width:48px; height:48px; }
    </style>
</head>
<body>
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 rounded-pill px-3 py-1 mb-3">
                    <i class="bi bi-shield-check text-warning"></i>
                    <small class="text-white-50">Sistem Resmi Kantor Pertanahan</small>
                </div>
                <h1 class="text-white mb-3 fw-bold" style="font-size:2.6rem">{{ config('app.name','LOKET 2026') }}</h1>
                <p class="lead text-white-50 mb-4 max-w-560">Digitalisasi alur permohonan pelayanan pertanahan &mdash; satu basis data terpusat, alur kerja 8 stage paralel, cetak formulir, tracking publik, dan arsip digital.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('tracking.index') }}" class="btn btn-light btn-lg px-4"><i class="bi bi-search me-2"></i>Cek Status Berkas</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-gold btn-lg px-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-gold btn-lg px-4"><i class="bi bi-box-arrow-in-right me-2"></i>Login Petugas</a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-5 text-center text-lg-end">
                <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo Kementerian ATR/BPN" class="img-fluid rounded-4 bg-white p-2 shadow-lg hero-logo">
                <div class="text-white-50 small mt-3">Sistem Loket Pelayanan<br>Pertanahan Elektronik</div>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-2">Alur Pelayanan Digital</h2>
        <p class="text-muted text-center mb-5">Proses permohonan yang transparan dan terukur</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card p-4 h-100">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-3 feature-icon"><i class="bi bi-person-plus-fill fs-5"></i></div>
                    <h5 class="fw-bold">Pendaftaran Loket</h5>
                    <p class="text-muted mb-0 small">Berkas didaftarkan dengan nomor tiket, data pemohon, jenis permohonan, dan informasi bidang tanah.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card p-4 h-100">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 d-inline-flex align-items-center justify-content-center mb-3 feature-icon"><i class="bi bi-search fs-5"></i></div>
                    <h5 class="fw-bold">Pemeriksaan & Warkah</h5>
                    <p class="text-muted mb-0 small">Verifikator memeriksa kelengkapan berkas secara paralel dengan Warkah menyiapkan data pendukung.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card feature-card p-4 h-100">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-inline-flex align-items-center justify-content-center mb-3 feature-icon"><i class="bi bi-check-circle-fill fs-5"></i></div>
                    <h5 class="fw-bold">Validasi & Alih Media</h5>
                    <p class="text-muted mb-0 small">Validator BT & SU memvalidasi, Alih Media memproses &mdash; sertifikat elektronik terbit.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container text-center">
        <div class="row g-4">
            <div class="col-6 col-md-3"><h2 class="fw-bold text-primary mb-1">9</h2><small class="text-muted text-uppercase fw-semibold">Role Pengguna</small></div>
            <div class="col-6 col-md-3"><h2 class="fw-bold text-primary mb-1">8</h2><small class="text-muted text-uppercase fw-semibold">Stage Alur</small></div>
            <div class="col-6 col-md-3"><h2 class="fw-bold text-primary mb-1">100%</h2><small class="text-muted text-uppercase fw-semibold">Digital Real-Time</small></div>
            <div class="col-6 col-md-3"><h2 class="fw-bold text-primary mb-1">6 Tahap</h2><small class="text-muted text-uppercase fw-semibold">Proses Paralel</small></div>
        </div>
    </div>
</section>
<footer class="footer-landing py-4 text-center text-white-50 small">&copy; {{ date('Y') }} Kantor Pertanahan Kota Bandar Lampung &bull; {{ config('app.name') }}</footer>
</body>
</html>