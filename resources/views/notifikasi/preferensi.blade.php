@extends('layouts.app')

@section('title', 'Preferensi Notifikasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Preferensi Notifikasi</h4>
        <p class="text-muted small mb-0">Pilih peristiwa yang ingin Anda terima lewat lonceng notifikasi.</p>
    </div>
    <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Notifikasi
    </a>
</div>

<div class="alert alert-light border small">
    <i class="bi bi-info-circle me-1"></i>
    Semua tipe aktif secara default. Mematikan satu tipe hanya menyembunyikan notifikasi untuk akun Anda —
    data dan alur berkas tetap berjalan normal.
</div>

<form action="{{ route('notifikasi.preferensi.simpan') }}" method="POST">
    @csrf

    @foreach ($grup as $namaGrup => $daftarTipe)
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-2">
                <i class="bi {{ $namaGrup === 'Tiket' ? 'bi-ticket-detailed' : ($namaGrup === 'Warkah' ? 'bi-archive' : 'bi-chat-left-text') }} text-primary me-2"></i>
                {{ $namaGrup }}
            </h6>

            @foreach ($daftarTipe as $tipe => $label)
                @php($wajib = \App\Services\NotifikasiService::isWajib($tipe))
                <div class="form-check form-switch py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="tipe[]" value="{{ $tipe }}"
                           id="tipe-{{ $loop->parent->index }}-{{ $loop->index }}"
                           @checked($wajib || ($pilihan[$tipe] ?? true))
                           @disabled($wajib)>
                    <label class="form-check-label ps-2" for="tipe-{{ $loop->parent->index }}-{{ $loop->index }}">
                        <span class="fw-semibold">{{ $label }}</span>
                        @if ($wajib)
                            <span class="badge bg-secondary-subtle text-secondary border ms-1">Wajib</span>
                        @endif
                        <span class="d-block text-muted small font-monospace">{{ $tipe }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary px-3">Batal</a>
        <button type="submit" class="btn btn-primary px-3">
            <i class="bi bi-check2-circle me-1"></i> Simpan Preferensi
        </button>
    </div>
</form>
@endsection
