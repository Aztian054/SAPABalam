<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LOKET 2026') }} — Sistem Pelayanan Pertanahan</title>

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f9;
            color: #334155;
            min-height: 100vh;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="container m-auto p-4 text-center">
        <i class="bi bi-building-check text-primary" style="font-size: 3rem;"></i>
        <h1 class="fw-bold text-dark mt-3 mb-0">{{ config('app.name', 'LOKET 2026') }}</h1>
        <p class="text-muted mt-1">Sistem Pelayanan Pertanahan Elektronik — Kantor Pertanahan Kota Bandar Lampung</p>
        <div class="d-flex justify-content-center gap-2 mt-4">
            <a href="{{ route('tracking.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i> Cek Status Tiket
            </a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                @endauth
            @endif
        </div>
    </div>
</body>
</html>