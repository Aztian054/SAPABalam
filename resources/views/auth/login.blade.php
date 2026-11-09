<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Loket Pertanahan Elektronik BMN BALAM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/images/logobpn2026.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bpn-navy: #0b2239;
            --bpn-gold: #c69214;
            --scrollbar-thumb: rgba(255,255,255,0.35);
        }
        /*= Scrollbar kustom: tipis & membulat =*/
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) transparent;
        }
        *::-webkit-scrollbar { width: 10px; height: 10px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-thumb);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        *::-webkit-scrollbar-thumb:hover { background-color: var(--bpn-gold); }
        *::-webkit-scrollbar-corner { background: transparent; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0b2239 0%, #173b61 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-x: hidden;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            max-width: 950px;
            width: 100%;
            overflow: hidden;
        }
        .btn-gold {
            background: var(--bpn-gold);
            color: #fff;
            font-weight: 600;
        }
        .btn-gold:hover {
            background: #b0810f;
            color: #fff;
        }
        .logo-sm { width: 56px; height: 56px; }
        @media (max-width: 767.98px) {
            body { padding: 1rem; align-items: flex-start; }
            .login-card { border-radius: 12px; }
        }
        @media (max-width: 575.98px) {
            body { padding: 0.75rem; }
            .login-card .p-4 { padding: 1.25rem !important; }
            .logo-sm { width: 44px; height: 44px; }
        }
    </style>
</head>
<body>

<div class="login-card row g-0">
    <!-- Left Column: Branding -->
    <div class="col-lg-5 p-4 p-md-5 d-flex flex-column justify-content-between text-white" style="background: linear-gradient(180deg, #07192c 0%, #0c2b4d 100%);">
        <div>
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo BPN" class="rounded-circle bg-white object-fit-contain p-1 shadow logo-sm">
                <div>
                    <h5 class="fw-bold mb-0">LOKET 2026</h5>
                    <small class="text-white-50">Kantah Kota Bandar Lampung</small>
                </div>
            </div>
            <h4 class="fw-bold mb-3 text-warning">Sistem Loket Pertanahan Elektronik</h4>
            <p class="text-white-50 small leading-relaxed">
                Platform digitalisasi permohonan berkas BMN &amp; umum terintegrasi: Loket Pendaftaran, Ploting, Seksi 1/2, Warkah, Penyerahan, dan Pengaduan.
            </p>
        </div>

        <div class="mt-4 pt-3 border-top border-secondary">
            <div class="d-flex align-items-center gap-2 text-warning mb-2">
                <i class="bi bi-shield-lock-fill"></i>
                <span class="small fw-semibold">Akses Terenkripsi &amp; Aman</span>
            </div>
        </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="col-lg-7 p-4 p-md-5 bg-white">
        <h4 class="fw-bold text-dark mb-1">Masuk ke Sistem</h4>
        <p class="text-muted small mb-4">Gunakan akun terdaftar sesuai peran operasional Anda.</p>

        @if(session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Username atau Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text" name="username" id="usernameInput" class="form-control bg-light border-start-0" placeholder="Masukkan username..." value="{{ old('username') }}" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-muted">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-secondary"></i></span>
                    <input type="password" name="password" id="passwordInput" class="form-control bg-light border-start-0" placeholder="Masukkan password..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-gold w-100 py-2 rounded-3 shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
            </button>
        </form>
    </div>
</div>

</body>
</html>
