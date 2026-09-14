<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 — Sesi Berakhir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 480px;">
            <div class="card-body p-4 text-center">
                <div class="display-3 fw-bold text-warning mb-1">419</div>
                <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                <h1 class="h5 fw-semibold mt-3 mb-2">Sesi Anda Telah Berakhir</h1>
                <p class="text-muted small mb-4">
                    Halaman ini terlalu lama terbuka atau token keamanan tidak cocok lagi.
                    Muat ulang halaman untuk mendapatkan token baru, lalu coba kembali.
                </p>
                <a href="{{ url()->current() }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang Halaman
                </a>
                <a href="{{ route('login') }}" class="btn btn-link w-100 small text-decoration-none">
                    Kembali ke Halaman Masuk
                </a>
            </div>
        </div>
    </div>
</body>
</html>