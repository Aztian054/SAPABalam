<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LOKET 2026') — Sistem Pelayanan Pertanahan BMN Balam</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/images/logobpn2026.svg') }}">

    <script>
        (function () {
            try {
                var t = localStorage.getItem('loket-theme');
                if (t === 'dark' || t === 'light') {
                    document.documentElement.setAttribute('data-bs-theme', t);
                }
            } catch (e) {}
        })();
    </script>
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --bpn-navy: #0b2239;
            --bpn-navy-light: #163659;
            --bpn-gold: #c69214;
            --bpn-gold-hover: #b0810f;
            --bpn-bg: #f4f6f9;
            /* Lebar sidebar responsif (bukan px keras), agar sinkron dengan #main-content. */
            --sidebar-w: clamp(240px, 22vw, 280px);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bpn-bg);
            color: #334155;
            min-height: 100vh;
        }
        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(rgba(11,34,57,0.92) 0%, rgba(6,21,36,0.96) 100%), url("{{ asset('asset/images/bgLoket01.jpeg') }}") center/cover no-repeat;
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
            margin-left: var(--sidebar-w);
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
        /*= Utility tetap (pengganti inline px di seluruh aplikasi) =*/
        .logo-sm { width: 56px; height: 56px; }
        .sidebar-logo { width: 46px; height: 46px; }
        .sidebar-close-btn { top: 14px; right: 12px; z-index: 5; }
        .theme-toggle { width: 38px; height: 38px; }
        .user-menu-btn { width: 42px; height: 42px; }
        .chart-container { position: relative; height: 220px; }
        .col-check { width: 40px; }
        .cell-label { max-width: 160px; }
        .max-w-280 { max-width: 280px; }
        .max-w-560 { max-width: 560px; }
        .max-w-640 { max-width: 640px; }
        .max-w-760 { max-width: 760px; }
        .timeline-label { min-width: 34px; }
        .timeline-badge {
            width: 30px;
            height: 30px;
            line-height: 30px;
            font-size: 0.7rem;
        }
        @media (max-width: 992px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-w)); }
            #sidebar.show { margin-left: 0; }
            #main-content { margin-left: 0; }
        }
        /*= Dark mode (data-bs-theme dark) =*/
        [data-bs-theme="dark"] body { background-color: #0c1725; color: #cbd5e1; }
        [data-bs-theme="dark"] .topbar { background-color: #101d2f; }
        [data-bs-theme="dark"] .topbar .text-dark, [data-bs-theme="dark"] .topbar .fw-bold, [data-bs-theme="dark"] .topbar .fw-semibold { color: #e2e8f0; }
        [data-bs-theme="dark"] .card-custom, [data-bs-theme="dark"] .card, [data-bs-theme="dark"] .card-header { background-color: #16233a; border-color: #22344b; }
        [data-bs-theme="dark"] .card-body { color: #cbd5e1; }
        [data-bs-theme="dark"] .table { --bs-table-bg: transparent; }
        [data-bs-theme="dark"] .table-light { --bs-table-bg: #16233a; --bs-table-color: #e2e8f0; }
        [data-bs-theme="dark"] .table-hover tbody tr:hover { color: #e2e8f0; }
        [data-bs-theme="dark"] footer { background-color: #101d2f !important; border-color: #22344b !important; color: #94a3b8 !important; }
        [data-bs-theme="dark"] .text-dark { color: #e2e8f0 !important; }
        [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
        [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #0f1a2b; color: #e2e8f0; border-color: #2b3f5a; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #64748b; }
        [data-bs-theme="dark"] .modal-content, [data-bs-theme="dark"] .modal-header, [data-bs-theme="dark"] .modal-footer { background-color: #16233a; border-color: #22344b; }
        [data-bs-theme="dark"] .bg-white { background-color: #16233a !important; }
        /*= Sidebar backdrop (mobile) =*/
        .sidebar-backdrop { position: fixed; inset: 0; z-index: 999; background: rgba(6,15,26,0.55); display: none; }
        .sidebar-backdrop.show { display: block; }

        /*= Modal responsif: body selalu bisa scroll, tombol footer selalu terlihat =*/
        .modal-dialog-scrollable .modal-content {
            max-height: calc(100vh - var(--bs-modal-margin, 1.75rem) * 2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .modal-header { flex-wrap: wrap; }
        .modal-footer { flex-wrap: wrap; }
        @media (max-width: 575.98px) {
            .modal-header, .modal-footer { padding: 0.85rem 1rem; }
            .modal-body { padding: 1rem; }
            .modal-footer .btn { flex: 1 1 auto; min-width: 0; }
            .modal-title { font-size: 1rem; }
        }
        @media (max-height: 480px) and (min-width: 576px) {
            .modal-dialog-scrollable .modal-content { max-height: calc(100vh - 1rem); }
        }

        /*= Responsive utilities =*/
        @media (max-width: 991.98px) {
            .container-fluid.px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
            .modal-xl { max-width: 100%; margin: 0.5rem; }
            .topbar { padding-left: 1rem; padding-right: 1rem; }
            .brand-box { padding: 1rem; }
            #sidebar { width: 100%; max-width: 320px; }
            .card-custom { border-radius: 8px; }
        }
        @media (max-width: 575.98px) {
            .btn-group .btn { padding: 0.375rem 0.5rem; font-size: 0.8125rem; }
            .table-responsive { border: none; }
            h4, h5, h6 { font-size: 1.1rem; }
            .fs-4 { font-size: 1.5rem !important; }
            .fs-3 { font-size: 1.75rem !important; }
            .badge { font-size: 0.7em; padding: 0.35em 0.6em; }
            .card-body { padding: 1rem; }
            .chart-container { height: 180px !important; }
            .card-custom .p-4 { padding: 0.75rem !important; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav id="sidebar">
        <div class="brand-box d-flex align-items-center gap-3">
            <button type="button" class="btn-close btn-close-white d-lg-none position-absolute sidebar-close-btn" id="sidebarClose" aria-label="Tutup menu"></button>
            <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo BPN" class="rounded-circle bg-white object-fit-contain p-1 shadow-sm sidebar-logo">
            <div>
                <h6 class="mb-0 text-white fw-bold tracking-wide">LOKET 2026</h6>
                <small class="text-secondary" style="font-size: 0.75rem;">Kantah Kota Bandar Lampung</small>
            </div>
        </div>

        <div class="py-2">
            {{-- ─── Utama (standalone) ─── --}}
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill text-warning"></i> Dashboard
            </a>

            {{-- ─── Admin (DB Berkas Terpadu) ─── --}}
            @if(auth()->user()->isAdmin())
                <div class="nav-heading">Admin (DB Berkas Terpadu)</div>
                <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.index') || request()->routeIs('admin.show') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-add text-info"></i> Daftar Berkas (Database)
                </a>
                <a href="{{ route('admin.selesai') }}" class="nav-link {{ request()->routeIs('admin.selesai') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle text-success"></i> Berkas Selesai
                </a>
                <a href="{{ route('admin.revisi') }}" class="nav-link {{ request()->routeIs('admin.revisi') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat text-warning"></i> Revisi (Perbaikan)
                </a>
                <a href="{{ route('admin.arsip') }}" class="nav-link {{ request()->routeIs('admin.arsip*') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill text-info"></i> Arsip Folder
                </a>
            @endif

            {{-- ─── Stage sections (petugas roles) ─── --}}
            @if(auth()->user()->role === 'loket')
                <div class="nav-heading">Stage 1 : Loket</div>
                <a href="{{ route('loket.index') }}" class="nav-link {{ request()->routeIs('loket.index') || request()->routeIs('loket.show') || request()->routeIs('loket.edit') ? 'active' : '' }}">
                    <i class="bi bi-ticket-detailed-fill text-danger"></i> Daftar Berkas Loket
                </a>
            @endif

            @if(auth()->user()->role === 'verifikator')
                <div class="nav-heading">Stage 2 : Verifikator</div>
                <a href="{{ route('verifikator.index') }}" class="nav-link {{ request()->routeIs('verifikator.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check-fill text-success"></i> Verifikasi Berkas
                </a>
            @endif

            @if(auth()->user()->role === 'warkah')
                <div class="nav-heading">Stage 3 : Warkah</div>
                <a href="{{ route('warkah.index') }}" class="nav-link {{ request()->routeIs('warkah.*') ? 'active' : '' }}">
                    <i class="bi bi-archive-fill text-info"></i> Lembar Kerja Warkah
                </a>
            @endif

            @if(auth()->user()->role === 'validator_btel')
                <div class="nav-heading">Stage 4A : Validator BT</div>
                <a href="{{ route('validator_btel.index') }}" class="nav-link {{ request()->routeIs('validator_btel.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check text-primary"></i> Validasi Pra-BTel
                </a>
            @endif

            @if(auth()->user()->role === 'validator_suel')
                <div class="nav-heading">Stage 4B : Validator SU</div>
                <a href="{{ route('validator_suel.index') }}" class="nav-link {{ request()->routeIs('validator_suel.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-shaded text-primary"></i> Validasi Pra-SuEl
                </a>
            @endif

            @if(auth()->user()->role === 'alih_media_btel')
                <div class="nav-heading">Stage 5A : Alih Media BT</div>
                <a href="{{ route('alih_media_btel.index') }}" class="nav-link {{ request()->routeIs('alih_media_btel.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-diff-fill text-success"></i> Alih Media BT
                </a>
            @endif

            @if(auth()->user()->role === 'alih_media_suel')
                <div class="nav-heading">Stage 5B : Alih Media SU</div>
                <a href="{{ route('alih_media_suel.index') }}" class="nav-link {{ request()->routeIs('alih_media_suel.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-info"></i> Alih Media SU
                </a>
            @endif

            {{-- ─── Monitoring ─── --}}
            <div class="nav-heading">Monitoring :</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill text-primary"></i> Manajemen Akun
                </a>
                <a href="{{ route('admin.form-pendaftaran') }}" class="nav-link {{ request()->routeIs('admin.form-pendaftaran*') ? 'active' : '' }}">
                    <i class="bi bi-ui-checks text-success"></i> Kelola Form Pendaftaran
                </a>
            @endif
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph-fill text-warning"></i> Monitoring Laporan
            </a>
        </div>
    </nav>

    <div id="sidebarBackdrop" class="sidebar-backdrop d-lg-none"></div>

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

            <button type="button" id="themeToggle" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 theme-toggle" title="Ganti tema terang / gelap">
                <i class="bi bi-moon-stars-fill fs-6" id="themeToggleIcon"></i>
            </button>
            <!-- User Menu -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</div>
                    <span class="badge bg-primary text-uppercase" style="font-size:0.7rem;">{{ auth()->user()->role_label }}</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2 user-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
        (function () {
            var sidebar = document.getElementById('sidebar');
            var toggle = document.getElementById('sidebarToggle');
            var closeBtn = document.getElementById('sidebarClose');
            var backdrop = document.getElementById('sidebarBackdrop');

            function openSidebar() { if (sidebar) sidebar.classList.add('show'); if (backdrop) backdrop.classList.add('show'); }
            function closeSidebar() { if (sidebar) sidebar.classList.remove('show'); if (backdrop) backdrop.classList.remove('show'); }

            if (toggle) toggle.addEventListener('click', function () {
                if (sidebar.classList.contains('show')) closeSidebar(); else openSidebar();
            });
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });

            var themeBtn = document.getElementById('themeToggle');
            var themeIcon = document.getElementById('themeToggleIcon');
            function applyTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                if (themeIcon) themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill fs-6' : 'bi bi-moon-stars-fill fs-6';
                try { localStorage.setItem('loket-theme', theme); } catch (e) {}
            }
            if (themeBtn) themeBtn.addEventListener('click', function () {
                var cur = document.documentElement.getAttribute('data-bs-theme') || 'light';
                applyTheme(cur === 'dark' ? 'light' : 'dark');
            });
        })();
    </script>
    @yield('scripts')
</body>
</html>
