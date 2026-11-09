@extends('layouts.app')

@section('title', 'Ploting Berkas')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Ploting Berkas</h4>
        <p class="text-muted small mb-0">
            @if ($bisaMemproses)
                Daftar berkas yang menunggu ploting dan yang sudah selesai diploting.
            @else
                Pantau status ploting berkas pendaftaran Anda.
            @endif
        </p>
    </div>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'menunggu' ? 'active' : '' }}" href="{{ route('ploting.index', ['tab' => 'menunggu']) }}">
            <i class="bi bi-hourglass-split me-1"></i> Menunggu Ploting
            <span class="badge bg-warning text-dark ms-1">{{ $jumlahMenunggu }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'selesai' ? 'active' : '' }}" href="{{ route('ploting.index', ['tab' => 'selesai']) }}">
            <i class="bi bi-check2-circle me-1"></i> Sudah Ploting
            <span class="badge bg-success ms-1">{{ $jumlahSelesai }}</span>
        </a>
    </li>
</ul>

<div class="card card-custom p-4">
    <form action="{{ route('ploting.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="col-md-5">
            <label class="form-label small fw-semibold mb-1">Cari nomor tiket / no. berkas / pemohon</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="JP26102026001 atau nama pemohon">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">{{ $tab === 'selesai' ? 'Selesai dari' : 'Kirim dari' }}</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-search me-1"></i> Filter</button>
            <a href="{{ route('ploting.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                @if ($tab === 'menunggu')
                    <tr>
                        <th style="width:3rem">No</th>
                        <th>No Tiket</th>
                        <th>No. Berkas</th>
                        <th>Pemohon</th>
                        <th>NIK / No HP</th>
                        <th>Waktu Kirim</th>
                        <th>Lama Menunggu</th>
                        <th>Petugas Pendaftar</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                @else
                    <tr>
                        <th style="width:3rem">No</th>
                        <th>No Tiket</th>
                        <th>No. Berkas</th>
                        <th>Pemohon</th>
                        <th>Catatan Ploting</th>
                        <th>Waktu Selesai</th>
                        <th>Diploting Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @forelse ($daftar as $draf)
                    <tr>
                        <td class="text-muted">{{ $daftar->firstItem() + $loop->index }}</td>
                        <td class="fw-bold font-monospace">{{ $draf->kode_tiket }}</td>
                        <td>{{ $draf->no_berkas ?: '-' }}</td>
                        <td>{{ $draf->biodata()['nama_pemohon'] ?? '-' }}</td>

                        @if ($tab === 'menunggu')
                            <td>
                                {{ $draf->biodata()['nik_pemohon'] ?? '-' }}
                                <div class="text-muted" style="font-size:0.75rem">{{ $draf->biodata()['no_hp_pemohon'] ?? '-' }}</div>
                            </td>
                            <td>{{ $draf->dikirim_pada?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                @if ($draf->dikirim_pada)
                                    @php
                                        $lamaJam = (int) $draf->dikirim_pada->diffInHours(now());
                                        $lama = $lamaJam >= 24 ? intdiv($lamaJam, 24).' hari' : $lamaJam.' jam';
                                    @endphp
                                    <span class="badge bg-light text-dark border">{{ $lama }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $draf->user->name ?? '-' }}</td>
                        @else
                            <td>{{ $draf->ploting_catatan ?? '-' }}</td>
                            <td>{{ $draf->ploting_selesai_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $draf->plotingOleh->name ?? '-' }}</td>
                        @endif

                        <td class="text-end text-nowrap">
                            @if ($tab === 'menunggu')
                                @if ($draf->user_id === auth()->id())
                                    <a href="{{ route('pendaftaran.antrian', $draf) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Cetak ulang antrian">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @endif
                                @if ($bisaMemproses)
                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2 btn-proses"
                                            data-bs-toggle="modal" data-bs-target="#modalPloting"
                                            data-kode="{{ $draf->kode_tiket }}"
                                            data-pemohon="{{ $draf->biodata()['nama_pemohon'] ?? '-' }}"
                                            data-action="{{ route('ploting.selesai', $draf) }}">
                                        <i class="bi bi-play-fill"></i> Proses
                                    </button>
                                @endif
                            @else
                                @if ($draf->user_id === auth()->id())
                                    <a href="{{ route('pendaftaran.tahap3', $draf) }}" class="btn btn-sm btn-gold py-1 px-2">
                                        Lanjut Pendaftaran <i class="bi bi-arrow-right"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            {{ $tab === 'menunggu' ? 'Tidak ada berkas yang menunggu ploting.' : 'Belum ada berkas yang selesai diploting.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $daftar->links() }}</div>
</div>

@if ($bisaMemproses)
    <div class="modal fade" id="modalPloting" tabindex="-1" aria-labelledby="modalPlotingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formPloting" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalPlotingLabel">Selesaikan Ploting</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="text-muted small">No Tiket</div>
                        <div class="fw-bold font-monospace" id="plotingKode">-</div>
                        <div class="text-muted small mt-1">Pemohon: <span id="plotingPemohon">-</span></div>
                    </div>
                    <label for="catatan" class="form-label fw-semibold">Catatan Ploting <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan" rows="3" class="form-control" required minlength="3" maxlength="500"
                              placeholder="Contoh: Bidang sesuai peta, tidak ada tumpang tindih.">{{ old('catatan') }}</textarea>
                    @error('catatan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan &amp; Kirim</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.btn-proses').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            document.getElementById('formPloting').action = tombol.dataset.action;
            document.getElementById('plotingKode').textContent = tombol.dataset.kode;
            document.getElementById('plotingPemohon').textContent = tombol.dataset.pemohon;
        });
    });

    @if ($errors->has('catatan'))
        new bootstrap.Modal(document.getElementById('modalPloting')).show();
    @endif
</script>
@endsection
