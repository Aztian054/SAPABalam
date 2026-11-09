@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Notifikasi</h4>
        <p class="text-muted small mb-0">Pemberitahuan in-app untuk aktivitas berkas, warkah, dan pengaduan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('notifikasi.preferensi') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-sliders me-1"></i> Preferensi
        </a>
        @if ($jumlahBelumDibaca > 0)
            <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm rounded-3 px-3">
                    <i class="bi bi-check2-all me-1"></i> Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>
</div>

<ul class="nav nav-pills mb-3 gap-2">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'belum' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['tab' => 'belum']) }}">
            Belum dibaca
            @if ($jumlahBelumDibaca > 0)
                <span class="badge bg-light text-dark ms-1">{{ $jumlahBelumDibaca }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'semua' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['tab' => 'semua']) }}">
            Semua
        </a>
    </li>
</ul>

<div class="card card-custom p-3">
    @forelse ($notifikasis as $notifikasi)
        <a href="{{ route('notifikasi.baca', $notifikasi) }}" class="d-flex gap-3 text-decoration-none text-reset py-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
            <span class="notif-ikon bg-{{ $notifikasi->warna }}-subtle text-{{ $notifikasi->warna }}">
                <i class="bi {{ $notifikasi->ikon }}"></i>
            </span>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <span class="fw-semibold {{ $notifikasi->sudah_dibaca ? 'text-muted' : 'text-dark' }}">
                        {{ $notifikasi->judul }}
                    </span>
                    <span class="text-muted small text-nowrap">{{ $notifikasi->waktu_label }}</span>
                </div>
                @if ($notifikasi->pesan)
                    <div class="small text-muted">{{ $notifikasi->pesan }}</div>
                @endif
            </div>
            @unless ($notifikasi->sudah_dibaca)
                <span class="badge bg-primary align-self-center">Baru</span>
            @endunless
        </a>
    @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
            {{ $tab === 'belum' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Belum ada notifikasi.' }}
        </div>
    @endforelse

    <div class="mt-3">{{ $notifikasis->links() }}</div>
</div>
@endsection

@section('styles')
<style>
    .notif-ikon {
        flex: 0 0 auto;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection
