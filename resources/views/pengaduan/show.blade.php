@extends('layouts.app')

@section('title', 'Pengaduan ' . $pengaduan->nomor_pengaduan)

@section('content')
@php
    $user = auth()->user();
    $petugasUnit = $user->isPenanganPengaduan() && ! $user->isAdmin();
    $bisaKirimUlang = $user->isLoketPengaduan() && $pengaduan->status === 'dikembalikan';
    $bisaTerima = $petugasUnit && $pengaduan->status === 'terkirim';
    $bisaTutup = $petugasUnit && $pengaduan->status === 'diterima';
    $bisaKembalikan = $petugasUnit && in_array($pengaduan->status, ['terkirim', 'diterima'], true);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pengaduan {{ $pengaduan->nomor_pengaduan }}</h4>
        <p class="text-muted small mb-0">
            Dibuat {{ $pengaduan->tanggal_buat?->format('d/m/Y') ?? '-' }}
            oleh {{ $pengaduan->pembuat->name ?? '-' }}
        </p>
    </div>
    <a href="{{ $user->isPenanganPengaduan() ? route('pengaduan-unit.index') : route('pengaduan.index') }}"
       class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i> Daftar Pengaduan
    </a>
    <a href="{{ route('pengaduan.cetak-preview', $pengaduan) }}"
       class="btn btn-outline-primary btn-sm rounded-3 px-3">
        <i class="bi bi-file-pdf me-1"></i> Cetak PDF
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-custom p-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">No Pengaduan</div>
                    <div class="h5 fw-bold font-monospace mb-0">{{ $pengaduan->nomor_pengaduan }}</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-{{ $pengaduan->status_badge }} fs-6">{{ $pengaduan->status_label }}</span>
                    @if ($pengaduan->is_terlambat)
                        <div><span class="badge bg-danger mt-1">TERLAMBAT</span></div>
                    @endif
                </div>
            </div>

            <dl class="row small mb-0">
                <dt class="col-sm-4 text-muted fw-normal">Jenis</dt><dd class="col-sm-8">{{ $pengaduan->jenis_label }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Seksi tujuan</dt><dd class="col-sm-8">{{ $pengaduan->seksi_tujuan_label }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Pengadu</dt>
                <dd class="col-sm-8">
                    {{ $pengaduan->pengadu_label }}
                    @unless ($pengaduan->is_anonim)
                        @if ($pengaduan->nik_pengadu)
                            <div class="text-muted" style="font-size:0.78rem;">NIK {{ $pengaduan->nik_pengadu }}</div>
                        @endif
                        @if ($pengaduan->no_hp_pengadu)
                            <div class="text-muted" style="font-size:0.78rem;">HP {{ $pengaduan->no_hp_pengadu }}</div>
                        @endif
                        @if ($pengaduan->email_pengadu)
                            <div class="text-muted" style="font-size:0.78rem;">Email {{ $pengaduan->email_pengadu }}</div>
                        @endif
                        @if ($pengaduan->alamat_pengadu)
                            <div class="text-muted" style="font-size:0.78rem;">{{ $pengaduan->alamat_pengadu }}</div>
                        @endif
                    @endunless
                </dd>
                <dt class="col-sm-4 text-muted fw-normal">Tiket terkait</dt>
                <dd class="col-sm-8">
                    @if ($pengaduan->tiket)
                        <span class="font-monospace">{{ $pengaduan->tiket->kode_tiket }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </dd>
                @if ($pengaduan->diterima_pada)
                    <dt class="col-sm-4 text-muted fw-normal">Diterima</dt>
                    <dd class="col-sm-8">{{ $pengaduan->diterima_pada->format('d/m/Y H:i') }} &middot; {{ $pengaduan->diterimaOleh->name ?? '-' }}</dd>
                @endif
                @if ($pengaduan->selesai_pada)
                    <dt class="col-sm-4 text-muted fw-normal">Selesai</dt>
                    <dd class="col-sm-8">{{ $pengaduan->selesai_pada->format('d/m/Y H:i') }} &middot; {{ $pengaduan->selesaiOleh->name ?? '-' }}</dd>
                @endif
            </dl>
        </div>

        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text text-primary me-2"></i>Uraian Pengaduan</h6>
            <p class="mb-0" style="white-space:pre-line;">{{ $pengaduan->uraian }}</p>
        </div>

        @if ($bisaKirimUlang)
            <div class="card card-custom p-4 mb-3 border-start border-4 border-danger">
                <h6 class="fw-bold mb-3"><i class="bi bi-arrow-repeat text-danger me-2"></i>Kirim Ulang</h6>
                <p class="small text-muted">Pengaduan dikembalikan unit tujuan{{ $pengaduan->catatan_terakhir ? ': '.$pengaduan->catatan_terakhir : '.' }}</p>
                <form action="{{ route('pengaduan.kirim-ulang', $pengaduan) }}" method="POST"
                      onsubmit="return confirm('Kirim ulang pengaduan ini? Nomor LP tidak berubah.')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="seksi_tujuan" class="form-label fw-semibold">Seksi tujuan baru <span class="text-danger">*</span></label>
                            <select name="seksi_tujuan" id="seksi_tujuan" class="form-select @error('seksi_tujuan') is-invalid @enderror" required>
                                @foreach ($daftarSeksi as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected((string) $pengaduan->seksi_tujuan === (string) $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('seksi_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-7">
                            <label for="catatan_kirim_ulang" class="form-label fw-semibold">Catatan perbaikan <span class="text-danger">*</span></label>
                            <input type="text" name="catatan" id="catatan_kirim_ulang" value="{{ old('catatan') }}"
                                   class="form-control @error('catatan') is-invalid @enderror" required minlength="3" maxlength="1000">
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger mt-3">
                        <i class="bi bi-send me-1"></i> KIRIM ULANG
                    </button>
                </form>
            </div>
        @endif

        @if ($bisaTerima || $bisaTutup || $bisaKembalikan)
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-gear text-primary me-2"></i>Tindakan Unit {{ $pengaduan->seksi_tujuan_label }}</h6>

                @if ($bisaTerima)
                    <form action="{{ route('pengaduan-unit.terima', $pengaduan) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-inbox me-1"></i> TERIMA
                        </button>
                        <span class="small text-muted ms-2">Pengaduan menjadi tanggung jawab unit.</span>
                    </form>
                @endif

                @if ($bisaTutup)
                    <form action="{{ route('pengaduan-unit.tutup', $pengaduan) }}" method="POST" class="mb-3"
                          onsubmit="return confirm('Tutup pengaduan ini? Status final, tidak bisa dibuka kembali.')">
                        @csrf
                        <label for="catatan_hasil" class="form-label fw-semibold">Catatan hasil <span class="text-danger">*</span></label>
                        <textarea name="catatan" id="catatan_hasil" rows="3" class="form-control mb-2 @error('catatan') is-invalid @enderror"
                                  required minlength="10" maxlength="2000"
                                  placeholder="Jelaskan hasil penanganan minimal 10 karakter.">{{ old('catatan') }}</textarea>
                        @error('catatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle me-1"></i> TUTUP (SELESAI)
                        </button>
                    </form>
                @endif

                @if ($bisaKembalikan)
                    <hr>
                    <form action="{{ route('pengaduan-unit.kembalikan', $pengaduan) }}" method="POST"
                          onsubmit="return confirm('Kembalikan pengaduan ke Loket Pengaduan?')">
                        @csrf
                        <label for="catatan_kembali" class="form-label fw-semibold">Alasan pengembalian <span class="text-danger">*</span></label>
                        <input type="text" name="catatan" id="catatan_kembali" value="{{ old('catatan') }}"
                               class="form-control mb-2 @error('catatan') is-invalid @enderror" required minlength="3" maxlength="1000"
                               placeholder="Contoh: bukan kewenangan unit ini.">
                        @error('catatan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-arrow-return-left me-1"></i> KEMBALIKAN
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Pengaduan</h6>
            @forelse ($pengaduan->riwayats as $riwayat)
                <div class="border-start border-3 border-primary ps-3 pb-3">
                    <div class="small text-muted">
                        {{ $riwayat->created_at?->format('d/m/Y H:i') }}
                        &middot; {{ $riwayat->user->name ?? '-' }}
                    </div>
                    <div class="small fw-semibold">
                        {{ $riwayat->dari_status ? (\App\Models\Pengaduan::STATUS[$riwayat->dari_status] ?? ucfirst($riwayat->dari_status)).' → ' : '' }}{{ $riwayat->ke_status_label }}
                    </div>
                    <div class="small text-muted">{{ $riwayat->catatan }}</div>
                </div>
            @empty
                <div class="text-muted small">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
