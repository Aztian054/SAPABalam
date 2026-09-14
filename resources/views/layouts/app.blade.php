<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LOKET 2026') — Sistem Pelayanan Pertanahan BMN Balam</title>

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bpn-navy: #0b2239;
            --bpn-navy-light: #163659;
            --bpn-gold: #c69214;
            --bpn-gold-hover: #b0810f;
            --bpn-bg: #f4f6f9;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bpn-bg);
            color: #334155;
            min-height: 100vh;
        }
        /* Sidebar Styling */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0b2239 0%, #061524 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }
        #sidebar .brand-box {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        #sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            margin: 0.2rem 0.75rem;
            font-weight: 500;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        #sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }
        #sidebar .nav-link.active {
            color: #fff;
            background: var(--bpn-gold);
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(198, 146, 20, 0.3);
        }
        #sidebar .nav-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 1.2rem 1.25rem 0.4rem;
            font-weight: 700;
        }
        /* Content Area */
        #main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #fff;
            padding: 0.85rem 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.04);
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-bpn {
            background: var(--bpn-navy);
            color: #fff;
            border: none;
            font-weight: 600;
        }
        .btn-bpn:hover {
            background: var(--bpn-navy-light);
            color: #fff;
        }
        .btn-gold {
            background: var(--bpn-gold);
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .btn-gold:hover {
            background: var(--bpn-gold-hover);
            color: #fff;
        }
        .badge-stage {
            font-weight: 600;
            padding: 0.45em 0.85em;
            border-radius: 6px;
            font-size: 0.82rem;
        }
        /* Warna badge khusus dokumen (Validasi SU = ungu, Alih Media SU = indigo). */
        .bg-purple {
            background-color: #6f42c1 !important;
            color: #fff !important;
        }
        .bg-indigo {
            background-color: #3949ab !important;
            color: #fff !important;
        }
        .btn-locked, .btn-locked:hover {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }
        @media (max-width: 992px) {
            #sidebar { margin-left: -260px; }
            #sidebar.show { margin-left: 0; }
            #main-content { margin-left: 0; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav id="sidebar">
        <div class="brand-box d-flex align-items-center gap-3">
            <div class="bg-warning text-dark p-2 rounded-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                <i class="bi bi-building-fill-check fs-4"></i>
            </div>
            <div>
                <h6 class="mb-0 text-white fw-bold tracking-wide">LOKET 2026</h6>
                <small class="text-secondary" style="font-size: 0.75rem;">Kantah Kota Bandar Lampung</small>
            </div>
        </div>

        <div class="py-2">
            <div class="nav-heading">Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
            <a href="{{ route('tracking.index') }}" target="_blank" class="nav-link">
                <i class="bi bi-qr-code-scan"></i> Portal Tracking Publik
            </a>

            @if(auth()->user()->isAdmin())
                <div class="nav-heading">Admin (DB Tiket Terpadu)</div>
                <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index') || request()->routeIs('admin.show') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-add"></i> Daftar Tiket (Database)
                </a>
                <a href="{{ route('admin.create_tiket') }}" class="nav-link {{ request()->routeIs('admin.create_tiket') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Tiket
                </a>
                <a href="{{ route('admin.selesai') }}" class="nav-link {{ request()->routeIs('admin.selesai') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle"></i> Tiket Selesai
                </a>
                <a href="{{ route('admin.revisi') }}" class="nav-link {{ request()->routeIs('admin.revisi') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat"></i> Revisi (Perbaikan)
                </a>
                <a href="{{ route('admin.arsip') }}" class="nav-link {{ request()->routeIs('admin.arsip*') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill"></i> Arsip Folder
                </a>
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Manajemen Akun
                </a>
            @endif

            @if(auth()->user()->isPemimpin())
                <div class="nav-heading">Monitoring</div>
                <a href="{{ route('pemimpin.index') }}" class="nav-link {{ request()->routeIs('pemimpin.*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard Pemimpin
                </a>
            @endif

            @if(auth()->user()->role === 'loket')
                <div class="nav-heading">Stage 1 : Loket</div>
                <a href="{{ route('loket.create') }}" class="nav-link {{ request()->routeIs('loket.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill"></i> Pendaftaran Baru
                </a>
                <a href="{{ route('loket.index') }}" class="nav-link {{ request()->routeIs('loket.index') || request()->routeIs('loket.show') || request()->routeIs('loket.edit') ? 'active' : '' }}">
                    <i class="bi bi-ticket-detailed-fill"></i> Daftar Tiket Loket
                </a>
            @endif

            @if(auth()->user()->role === 'verifikator')
                <div class="nav-heading">Stage 2 : Verifikator</div>
                <a href="{{ route('verifikator.index') }}" class="nav-link {{ request()->routeIs('verifikator.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check-fill"></i> Verifikasi Berkas
                </a>
            @endif

            @if(auth()->user()->role === 'warkah')
                <div class="nav-heading">Stage 3 : Warkah</div>
                <a href="{{ route('warkah.index') }}" class="nav-link {{ request()->routeIs('warkah.*') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill"></i> Lembar Kerja Warkah
                </a>
            @endif

            @if(auth()->user()->role === 'validator_btel')
                <div class="nav-heading">Stage 4A : Validator BT</div>
                <a href="{{ route('validator_btel.index') }}" class="nav-link {{ request()->routeIs('validator_btel.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Validasi Pra-BTel
                </a>
            @endif

            @if(auth()->user()->role === 'validator_suel')
                <div class="nav-heading">Stage 4B : Validator SU</div>
                <a href="{{ route('validator_suel.index') }}" class="nav-link {{ request()->routeIs('validator_suel.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-shaded"></i> Validasi Pra-SuEl
                </a>
            @endif

            @if(auth()->user()->role === 'alih_media_btel')
                <div class="nav-heading">Stage 5A : Alih Media BT</div>
                <a href="{{ route('alih_media_btel.index') }}" class="nav-link {{ request()->routeIs('alih_media_btel.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-diff-fill"></i> Alih Media BT
                </a>
            @endif

            @if(auth()->user()->role === 'alih_media_suel')
                <div class="nav-heading">Stage 5B : Alih Media SU</div>
                <a href="{{ route('alih_media_suel.index') }}" class="nav-link {{ request()->routeIs('alih_media_suel.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Alih Media SU
                </a>
            @endif

            <div class="nav-heading">Monitoring & Laporan</div>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan & Rekap SLA
            </a>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div id="main-content">
        <!-- Top Navbar -->
        <header class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-md-block">
                    <span class="text-muted small">Sistem Loket Pelayanan Pertanahan Elektronik &bull; </span>
                    <span class="fw-semibold text-dark">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>

            <!-- User Menu -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</div>
                    <span class="badge bg-primary text-uppercase" style="font-size:0.7rem;">{{ auth()->user()->role_label }}</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:42px; height:42px;">
                        <i class="bi bi-person-fill fs-5 text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ auth()->user()->username }} ({{ auth()->user()->email }})</small>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2 mt-1">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar Sistem
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        <div class="container-fluid px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error') || session('danger'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-x-circle-fill fs-5 me-2"></i>
                    <div>{{ session('error') ?? session('danger') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Page Body -->
        <main class="container-fluid px-4 py-3 flex-grow-1">
            @unless(request()->routeIs('dashboard'))
                <div class="mb-3">
                    @php
                        $backUrl = url()->previous();
                        $backUrl = ($backUrl === url()->current() || $backUrl === route('login') || !\Illuminate\Support\Str::startsWith($backUrl, url('/')))
                            ? route('dashboard')
                            : $backUrl;
                    @endphp
                    <a href="{{ $backUrl }}" class="btn btn-sm btn-light border shadow-sm rounded-3 px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            @endunless
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white py-3 px-4 text-center border-top text-muted small mt-auto">
            &copy; {{ date('Y') }} Kantor Pertanahan Kota Bandar Lampung &bull; Sistem Loket Pelayanan Elektronik &bull; LOKET 2026 v2.2
        </footer>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
    </script>
    @yield('scripts')
</body>
</html>
