{{-- Sidebar dinamis (Blueprint V2): menu dari tabel `menus`, dikelompokkan per role untuk Super Admin. --}}
@inject('sidebarService', 'App\Services\SidebarService')
@php
    $pengguna = auth()->user();
    $grupSidebar = $sidebarService->groupsFor($pengguna);
    $modeDeveloper = $pengguna->isSuperAdmin();
@endphp

<nav id="sidebar">
    <div class="brand-box d-flex align-items-center gap-3">
        <button type="button" class="btn-close btn-close-white d-lg-none position-absolute sidebar-close-btn" id="sidebarClose" aria-label="Tutup menu"></button>
        <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo BPN" class="rounded-circle bg-white object-fit-contain p-1 shadow-sm sidebar-logo">
        <div>
            <h6 class="mb-0 text-white fw-bold tracking-wide">SAPA Balam</h6>
            <small class="text-secondary" style="font-size: 0.75rem;">Kantah Kota Bandar Lampung</small>
        </div>
    </div>

    <div class="sidebar-scroll py-2">
        @if ($modeDeveloper)
            <div class="px-3 pt-3 pb-1">
                <span class="badge badge-mode-dev rounded-pill px-3 py-2">
                    <i class="bi bi-shield-lock-fill me-1"></i> MODE DEVELOPER
                </span>
                <div class="small text-secondary mt-2" style="font-size: 0.72rem;">
                    Menampilkan menu seluruh role.
                </div>
            </div>
        @endif

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill text-warning"></i> Dashboard
        </a>

        @foreach ($grupSidebar as $grup)
            @if ($modeDeveloper)
                <div class="nav-heading d-flex align-items-center justify-content-between gap-2">
                    <span>{{ $grup['label'] }}</span>
                    <span class="badge role-badge rounded-pill text-uppercase">{{ $grup['role'] }}</span>
                </div>
            @endif

            @foreach ($grup['menus'] as $menu)
                <a href="{{ route($menu->route) }}" class="nav-link {{ $menu->sedangAktif() ? 'active' : '' }}">
                    <i class="bi {{ $menu->icon }}"></i> {{ $menu->label }}
                </a>
            @endforeach
        @endforeach

        <div class="nav-heading">Monitoring</div>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph-fill text-warning"></i> Monitoring Laporan
        </a>
    </div>
</nav>

<div id="sidebarBackdrop" class="sidebar-backdrop d-lg-none"></div>
