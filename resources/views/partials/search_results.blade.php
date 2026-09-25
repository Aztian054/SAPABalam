@php
    // Peta nama tahap di DB (tiket_penugasan.stage) → prefix nama route:
    // tahap 'verifikasi' dilayani route 'verifikator.*', 'validasi_btel' → 'validator_btel.*', dst.
    $routeBase = [
        'verifikasi' => 'verifikator',
        'validasi_btel' => 'validator_btel',
        'validasi_suel' => 'validator_suel',
    ][$stage ?? ''] ?? $stage ?? '';
@endphp
<div class="table-responsive">
    <table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Kode Tiket</th><th>Pemohon</th><th>Jenis Permohonan</th><th>Bidang</th>
                <th>Tanggal Masuk</th><th>Status</th><th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tikets as $t)
                <tr>
                    <td>
                        <a href="{{ route($routeBase . '.show', $t->id) }}" class="text-decoration-none fw-semibold">{{ $t->kode_tiket }}</a>
                    </td>
                    <td>{{ $t->nama_pemohon }}</td>
                    <td><small>{{ $t->jenisPermohonan?->nama }}</small></td>
                    <td>{{ $t->jumlah_bidang }}</td>
                    <td>{{ $t->tanggal_masuk?->format('d/m/Y') }}</td>
                    <td><span class="badge bg-{{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                    <td class="text-end">
                        @php $active = $t->penugasanAktif($stage); @endphp
                        @if($active)
                            <span class="badge bg-secondary text-uppercase" style="font-size:0.68rem;">
                                <i class="bi bi-lock-fill me-1"></i>Diproses {{ $active->user_id === auth()->id() ? 'oleh Anda' : 'akun lain' }}
                            </span>
                        @elseif(!empty($t->locked))
                            <span class="badge bg-dark-subtle text-dark text-uppercase" style="font-size:0.68rem;" data-bs-toggle="tooltip" title="Validasi Pra-Buku Tanah / Pra-Surat Ukur belum selesai kedua-duanya.">
                                <i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                        @else
                            <form action="{{ route($routeBase . '.add', $t->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ambil dari DB Admin">
                                    <i class="bi bi-plus-circle me-1"></i>Add
                                </button>
                            </form>
                        @endif
                        <a href="{{ route($routeBase . '.show', $t->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-search fs-2 d-block mb-2"></i>
                        Tidak ditemukan berkas yang dapat di-Add
                        @if($search) untuk pencarian "{{ $search }}".@endif
                        @php $isValidatorStage = in_array($stage ?? '', ['validasi_btel', 'validasi_suel'], true); @endphp
                        <small class="d-block mt-1">
                            @if($isValidatorStage)
                                Pastikan Warkah telah menandai status sertipikat <strong>DISERAHKAN</strong> pada berkas yang dicari.
                            @else
                                Pastikan tahap prasyarat telah selesai pada berkas yang dicari.
                            @endif
                        </small>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>