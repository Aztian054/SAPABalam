@php
    $bolehEditNoBerkas = ($bisaEditNoBerkas ?? false)
        && auth()->check()
        && (auth()->user()->isAdmin() || auth()->id() === $tiket->created_by);
@endphp

<div class="d-flex justify-content-between align-items-center border-bottom py-1 gap-2">
    <small class="text-muted text-nowrap">No. Berkas</small>
    @if($bolehEditNoBerkas)
        <form method="POST" action="{{ route('pendaftaran.no-berkas.update', $tiket) }}" class="d-flex gap-1 align-items-center m-0">
            @csrf
            @method('PUT')
            <input type="text" name="no_berkas" value="{{ old('no_berkas', $tiket->no_berkas) }}"
                   class="form-control form-control-sm" style="max-width:220px" placeholder="Nomor resmi BPN">
            <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2" title="Simpan No. Berkas">
                <i class="bi bi-check2"></i>
            </button>
        </form>
    @else
        <strong class="text-dark">{{ $tiket->no_berkas ?: '—' }}</strong>
    @endif
</div>
