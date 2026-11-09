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
            /* Warna scrollbar kustom (dipakai halaman & sidebar). */
            --scrollbar-thumb: rgba(100, 116, 139, 0.45);
            --scrollbar-thumb-hover: var(--bpn-gold);
        }
        /*= Scrollbar kustom global: tipis, membulat, kontras di terang/gelap =*/
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) transparent;
        }
        *::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        *::-webkit-scrollbar-track {
            background: transparent;
        }
        *::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-thumb);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        *::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-thumb-hover);
        }
        *::-webkit-scrollbar-corner {
            background: transparent;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bpn-bg);
            color: #334155;
            min-height: 100vh;
            overflow-x: hidden;
        }
        img, svg, video, canvas {
            max-width: 100%;
        }
        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: linear-gradient(rgba(11,34,57,0.92) 0%, rgba(6,21,36,0.96) 100%), url("{{ asset('asset/images/bgLoket01.jpeg') }}") center/cover no-repeat;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }
        /* Area menu menggulir sendiri; brand tetap di atas (sticky scroll). */
        #sidebar .sidebar-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.28) transparent;
            padding-bottom: 1.25rem;
        }
        #sidebar .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }
        #sidebar .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.22);
            border-radius: 999px;
            border: none;
        }
        #sidebar .sidebar-scroll:hover::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.38);
        }
        #sidebar .brand-box {
            flex: 0 0 auto;
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
        /*= Mode developer: penanda & badge role per grup menu =*/
        #sidebar .badge-mode-dev {
            background: rgba(198, 146, 20, 0.16);
            color: #f0c04a;
            border: 1px solid rgba(198, 146, 20, 0.45);
            font-size: 0.68rem;
            letter-spacing: 0.08em;
        }
        #sidebar .role-badge {
            background: rgba(148, 163, 184, 0.14);
            color: #94a3b8;
            font-size: 0.58rem;
            letter-spacing: 0.05em;
        }
        /* Content Area */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            min-width: 0;
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
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }
        .topbar-date {
            line-height: 1.2;
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
        @media (max-width: 991.98px) {
            #sidebar { width: 100%; max-width: 320px; margin-left: -100%; }
            #sidebar.show { margin-left: 0; box-shadow: 0 0 40px rgba(0,0,0,0.4); }
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
        @media (max-width: 1199.98px) {
            #sidebar .nav-link { font-size: 0.88rem; }
        }
        @media (max-width: 991.98px) {
            .container-fluid.px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
            .modal-xl { max-width: 100%; margin: 0.5rem; }
            .topbar { padding-left: 1rem; padding-right: 1rem; }
            .brand-box { padding: 1rem; }
            .card-custom { border-radius: 8px; }
            main.container-fluid.py-3 { padding-top: 0.85rem !important; }
            footer { padding-left: 1rem !important; padding-right: 1rem !important; }
        }
        @media (max-width: 767.98px) {
            .topbar { padding-top: 0.6rem; padding-bottom: 0.6rem; }
            .topbar-date { display: none; }
            .table-responsive { -webkit-overflow-scrolling: touch; }
            .card-custom .card-header { padding: 0.85rem 1rem; }
            /* Tombol aksi di kepala halaman turun baris, tidak meluber. */
            .page-actions, main .d-flex.justify-content-between.align-items-center { flex-wrap: wrap; gap: 0.5rem; }
        }
        @media (max-width: 575.98px) {
            body { font-size: 0.9375rem; }
            .container-fluid.px-4 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
            main.container-fluid.py-3 { padding-top: 0.75rem !important; }
            .topbar { padding-left: 0.75rem; padding-right: 0.75rem; gap: 0.5rem; }
            .topbar-actions { gap: 0.35rem; }
            .theme-toggle, .user-menu-btn { width: 36px; height: 36px; }
            .btn-group .btn { padding: 0.375rem 0.5rem; font-size: 0.8125rem; }
            .table-responsive { border: none; margin-left: -0.25rem; margin-right: -0.25rem; }
            h4, h5, h6 { font-size: 1.1rem; }
            .fs-4 { font-size: 1.5rem !important; }
            .fs-3 { font-size: 1.75rem !important; }
            .badge { font-size: 0.7em; padding: 0.35em 0.6em; }
            .card-body { padding: 1rem; }
            .chart-container { height: 180px !important; }
            .card-custom .p-4 { padding: 0.75rem !important; }
            footer { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
            .pagination { flex-wrap: wrap; gap: 0.25rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation (dinamis dari tabel menus) -->
    @include('partials.sidebar')

    <!-- Main Content Wrapper -->
    <div id="main-content">
        <!-- Top Navbar -->
        <header class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle" aria-label="Buka menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-md-block topbar-date">
                    <span class="text-muted small">Sistem Loket Pelayanan Pertanahan Elektronik &bull; </span>
                    <span class="fw-semibold text-dark">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>

            <div class="topbar-actions">
                <button type="button" id="themeToggle" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 theme-toggle" title="Ganti tema terang / gelap">
                    <i class="bi bi-moon-stars-fill fs-6" id="themeToggleIcon"></i>
                </button>

                {{-- V2: Lonceng notifikasi in-app --}}
                <a href="{{ route('notifikasi.index') }}" id="notifBell"
                   class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 position-relative" title="Notifikasi">
                    <i class="bi bi-bell-fill fs-6 text-secondary"></i>
                    <span id="notifBadge"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ ($jumlahNotifikasi ?? 0) > 0 ? '' : 'd-none' }}">
                        {{ $jumlahNotifikasi ?? 0 }}
                    </span>
                </a>

                <!-- User Menu -->
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</div>
                        <span class="badge bg-primary text-uppercase" style="font-size:0.7rem;">{{ auth()->user()->role_label }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle p-2 user-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna">
                            @if (auth()->user()->foto_profil_url)
                                <img src="{{ auth()->user()->foto_profil_url }}" alt="Foto Profil" class="rounded-circle w-100 h-100 object-fit-cover avatar-img" style="width:32px;height:32px;">
                            @else
                                <i class="bi bi-person-fill fs-5 text-secondary"></i>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                <small class="text-muted">{{ auth()->user()->username }} ({{ auth()->user()->email }})</small>
                            </li>
                            @unless (session()->has('impersonate_id'))
                                <li>
                                    <a href="{{ route('profil.edit') }}" class="dropdown-item py-2">
                                        <i class="bi bi-person-gear me-2"></i> Profil Saya
                                    </a>
                                </li>
                            @endunless
                            <li>
                                @if (session()->has('impersonate_id'))
                                    {{-- Mode baca-saja memblokir POST, jadi Keluar Sistem diganti jalan kembali. --}}
                                    <a href="{{ route('impersonate.stop') }}" class="dropdown-item text-warning py-2 mt-1">
                                        <i class="bi bi-arrow-return-left me-2"></i> Kembali ke Super Admin
                                    </a>
                                @else
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2 mt-1">
                                            <i class="bi bi-box-arrow-right me-2"></i> Keluar Sistem
                                        </button>
                                    </form>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Alerts -->
        <div class="container-fluid px-4 pt-3">
            {{-- V2: banner mode lihat/coba sebagai akun (read-only) --}}
            @if (session()->has('impersonate_id'))
                <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-2" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-eye-fill fs-5"></i>
                        <div>
                            <strong>Anda melihat sebagai {{ auth()->user()->name }} ({{ auth()->user()->role_label }})</strong>
                            <div class="small">Mode baca-saja — semua aksi yang mengubah data diblokir.</div>
                        </div>
                    </div>
                    <a href="{{ route('impersonate.stop') }}" class="btn btn-sm btn-dark">
                        <i class="bi bi-arrow-return-left me-1"></i>Kembali ke Super Admin
                    </a>
                </div>
            @endif

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

            // V2: polling ringan badge notifikasi (60 detik)
            var notifBadge = document.getElementById('notifBadge');
            function muatJumlahNotifikasi() {
                if (!notifBadge) return;
                fetch('{{ route('notifikasi.jumlah') }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (d) {
                        if (!d) return;
                        var jumlah = parseInt(d.belum_dibaca, 10) || 0;
                        notifBadge.textContent = jumlah > 99 ? '99+' : jumlah;
                        notifBadge.classList.toggle('d-none', jumlah === 0);
                    })
                    .catch(function () {});
            }
            muatJumlahNotifikasi();
            setInterval(muatJumlahNotifikasi, 60000);
        })();
    </script>
    @yield('scripts')
</body>
</html>
