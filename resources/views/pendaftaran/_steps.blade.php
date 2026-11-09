@php
    $langkah = [
        1 => ['Biodata', 'Data pemohon & nomor tiket'],
        2 => ['Ploting', 'Konfirmasi ploting bidang'],
        3 => ['Permohonan', 'Jenis, no hak & letak tanah'],
        4 => ['Warkah', 'Kebutuhan warkah BT/SU'],
    ];
@endphp

<div class="card card-custom p-3 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-stretch">
        @foreach ($langkah as $nomor => [$judul, $keterangan])
            @php
                $selesai = $nomor < $aktif;
                $sekarang = $nomor === $aktif;
            @endphp
            <div class="flex-fill border rounded-3 px-3 py-2 {{ $sekarang ? 'border-primary bg-primary bg-opacity-10' : ($selesai ? 'border-success bg-success bg-opacity-10' : 'bg-light') }}"
                 style="min-width: 12rem;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill {{ $sekarang ? 'bg-primary' : ($selesai ? 'bg-success' : 'bg-secondary') }}">
                        @if ($selesai)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $nomor }}
                        @endif
                    </span>
                    <span class="fw-semibold small">{{ $judul }}</span>
                </div>
                <div class="text-muted" style="font-size: 0.74rem;">{{ $keterangan }}</div>
            </div>
        @endforeach
    </div>
</div>
