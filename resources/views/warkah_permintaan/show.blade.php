@extends('layouts.app')

@section('title', 'Warkah ' . $warkah->jenis . ' — ' . ($warkah->tiket->kode_tiket ?? '-'))

@section('content')
@php
    $tiket = $warkah->tiket;
    $sedangDipinjam = in_array($warkah->status, ['dipinjam', 'terlambat'], true);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Warkah {{ $warkah->jenis }} — <span class="font-monospace">{{ $tiket->kode_tiket ?? '-' }}</span></h4>
        <p class="text-muted small mb-0">
            {{ \App\Models\WarkahPermintaan::JENIS[$warkah->jenis] ?? '' }}
            &middot; diminta {{ $warkah->tanggal_permintaan?->format('d/m/Y H:i') ?? '-' }}
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $warkah->status_badge }} px-3 py-2">{{ $warkah->status_label }}</span>
        <a href="{{ route('warkah-permintaan.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-ticket-detailed text-primary me-2"></i>Data Tiket</h6>
            <dl class="row small mb-0">
                <dt class="col-sm-4 text-muted fw-normal">No Tiket</dt>
                <dd class="col-sm-8"><span class="font-monospace">{{ $tiket->kode_tiket ?? '-' }}</span></dd>
                <dt class="col-sm-4 text-muted fw-normal">No. Berkas</dt>
                <dd class="col-sm-8"><span class="font-monospace">{{ $tiket->no_berkas ?? '-' }}</span></dd>
                <dt class="col-sm-4 text-muted fw-normal">Pemohon</dt><dd class="col-sm-8">{{ $tiket->nama_pemohon ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Jenis layanan</dt><dd class="col-sm-8">{{ $tiket->jenisPermohonan->nama ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No Hak</dt><dd class="col-sm-8">{{ $tiket->no_hak_sekarang ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Lokasi</dt>
                <dd class="col-sm-8">{{ collect([$tiket->kelurahan_desa, $tiket->kecamatan])->filter()->implode(', ') ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Pemegang berkas</dt><dd class="col-sm-8">{{ $tiket->penanggungJawab->name ?? '-' }}</dd>
            </dl>
        </div>

        @if ($warkah->status === 'diminta')
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-inbox text-primary me-2"></i>Tindakan</h6>
                <form method="POST" action="{{ route('warkah-permintaan.terima', $warkah) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="bi bi-inbox me-1"></i> TERIMA PERMINTAAN</button>
                    <span class="small text-muted ms-2">Berkas fisik mulai disiapkan.</span>
                </form>
            </div>
        @elseif ($warkah->status === 'diterima')
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-check2-circle text-success me-2"></i>Tindakan</h6>
                <form method="POST" action="{{ route('warkah-permintaan.siap', $warkah) }}">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> TANDAI SIAP DIAMBIL</button>
                    <span class="small text-muted ms-2">Staf pemegang tiket akan menerima notifikasi.</span>
                </form>
            </div>
        @endif

        @if ($warkah->status === 'siap')
            <div class="card card-custom p-4 mb-3 border-start border-4 border-primary">
                <h6 class="fw-bold mb-3"><i class="bi bi-box-arrow-up-right text-primary me-2"></i>Serahkan (Pinjamkan)</h6>
                <form method="POST" action="{{ route('warkah-permintaan.serahkan', $warkah) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="peminjam_id" class="form-label fw-semibold">Peminjam (akun staf) <span class="text-danger">*</span></label>
                            <select name="peminjam_id" id="peminjam_id" class="form-select @error('peminjam_id') is-invalid @enderror">
                                <option value="">— pilih staf —</option>
                                @foreach ($stafSeksi as $staf)
                                    <option value="{{ $staf->id }}" @selected((string) old('peminjam_id') === (string) $staf->id)>
                                        {{ $staf->name }} — {{ $staf->seksi_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('peminjam_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="batas_kembali" class="form-label fw-semibold">Batas kembali <span class="text-danger">*</span></label>
                            <input type="date" name="batas_kembali" id="batas_kembali" required
                                   value="{{ old('batas_kembali', now()->addDays($batasHari)->toDateString()) }}"
                                   class="form-control @error('batas_kembali') is-invalid @enderror">
                            @error('batas_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Default {{ $batasHari }} hari dari hari ini.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_peminjam" class="form-label fw-semibold">Nama peminjam (bila bukan akun)</label>
                            <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}"
                                   class="form-control @error('nama_peminjam') is-invalid @enderror" maxlength="200">
                            @error('nama_peminjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="seksi" class="form-label fw-semibold">Seksi peminjam</label>
                            <select name="seksi" id="seksi" class="form-select @error('seksi') is-invalid @enderror">
                                <option value="">— pilih seksi —</option>
                                @foreach (\App\Models\User::SEKSI as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('seksi') === (string) $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('seksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan_serah" class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" id="catatan_serah" rows="2" maxlength="1000"
                                      class="form-control @error('catatan') is-invalid @enderror">{{ old('catatan') }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-box-arrow-up-right me-1"></i> SERAHKAN</button>
                </form>
            </div>
        @endif

        @if ($sedangDipinjam)
            <div class="card card-custom p-4 mb-3 border-start border-4 border-success">
                <h6 class="fw-bold mb-3"><i class="bi bi-arrow-return-left text-success me-2"></i>Catat Pengembalian</h6>
                <form method="POST" action="{{ route('warkah-permintaan.kembalikan', $warkah) }}"
                      onsubmit="return confirm('Catat pengembalian warkah ini?')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pengembali" class="form-label fw-semibold">Nama pengembali <span class="text-danger">*</span></label>
                            <input type="text" name="pengembali" id="pengembali" required maxlength="200"
                                   value="{{ old('pengembali', $warkah->nama_peminjam) }}"
                                   class="form-control @error('pengembali') is-invalid @enderror">
                            @error('pengembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="kondisi" class="form-label fw-semibold">Kondisi berkas <span class="text-danger">*</span></label>
                            <select name="kondisi" id="kondisi" required class="form-select @error('kondisi') is-invalid @enderror">
                                @foreach ($kondisi as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('kondisi') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kondisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan_kembali" class="form-label fw-semibold">Catatan kondisi</label>
                            <textarea name="catatan" id="catatan_kembali" rows="2" maxlength="1000"
                                      class="form-control @error('catatan') is-invalid @enderror"
                                      placeholder="Wajib bila kondisi bukan Baik.">{{ old('catatan') }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3"><i class="bi bi-arrow-return-left me-1"></i> KEMBALIKAN</button>
                </form>
            </div>

            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event text-warning me-2"></i>Ubah Batas Kembali</h6>
                <p class="small text-muted">Perpanjangan dilakukan di luar sistem (lisan/WA). Catat batas baru dan alasannya di sini.</p>
                <form method="POST" action="{{ route('warkah-permintaan.ubah-batas', $warkah) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="batas_baru" class="form-label fw-semibold">Batas baru <span class="text-danger">*</span></label>
                            <input type="date" name="batas_kembali" id="batas_baru" required
                                   value="{{ old('batas_kembali', $warkah->batas_kembali?->toDateString()) }}"
                                   class="form-control @error('batas_kembali') is-invalid @enderror">
                            @error('batas_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-7">
                            <label for="alasan" class="form-label fw-semibold">Alasan <span class="text-danger">*</span></label>
                            <input type="text" name="alasan" id="alasan" required minlength="3" maxlength="500"
                                   value="{{ old('alasan') }}"
                                   class="form-control @error('alasan') is-invalid @enderror"
                                   placeholder="Contoh: permintaan lisan peminjam, pekerjaan belum selesai.">
                            @error('alasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-warning mt-3"><i class="bi bi-calendar-check me-1"></i> SIMPAN BATAS BARU</button>
                </form>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Status Pinjaman</h6>
            <dl class="row small mb-0">
                <dt class="col-sm-5 text-muted fw-normal">Peminjam</dt>
                <dd class="col-sm-7">{{ $warkah->nama_peminjam ?? $warkah->peminjam->name ?? '-' }}</dd>
                <dt class="col-sm-5 text-muted fw-normal">Seksi</dt>
                <dd class="col-sm-7">{{ \App\Models\User::SEKSI[$warkah->seksi] ?? $warkah->seksi ?? '-' }}</dd>
                <dt class="col-sm-5 text-muted fw-normal">Tanggal pinjam</dt>
                <dd class="col-sm-7">{{ $warkah->tanggal_pinjam?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="col-sm-5 text-muted fw-normal">Batas kembali</dt>
                <dd class="col-sm-7">
                    {{ $warkah->batas_kembali?->format('d/m/Y') ?? '-' }}
                    @if ($warkah->sisa_waktu_label)
                        <div class="text-muted" style="font-size:0.78rem;">{{ $warkah->sisa_waktu_label }}</div>
                    @endif
                </dd>
                <dt class="col-sm-5 text-muted fw-normal">Tanggal kembali</dt>
                <dd class="col-sm-7">{{ $warkah->tanggal_kembali?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="col-sm-5 text-muted fw-normal">Kondisi</dt>
                <dd class="col-sm-7">{{ $warkah->kondisi ? ucfirst(str_replace('_', ' ', $warkah->kondisi)) : '-' }}</dd>
                <dt class="col-sm-5 text-muted fw-normal">Catatan</dt>
                <dd class="col-sm-7">{{ $warkah->catatan ?? '-' }}</dd>
            </dl>
        </div>

        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Warkah</h6>
            @forelse ($warkah->riwayats as $riwayat)
                <div class="border-start border-3 border-primary ps-3 pb-3">
                    <div class="small text-muted">
                        {{ $riwayat->created_at?->format('d/m/Y H:i') }}
                        &middot; {{ $riwayat->user->name ?? 'Sistem' }}
                    </div>
                    <div class="small fw-semibold">
                        {{ $riwayat->aksi }}
                        @if ($riwayat->batas_lama || $riwayat->batas_baru)
                            <span class="text-muted fw-normal">
                                ({{ $riwayat->batas_lama?->format('d/m/Y') ?? '-' }} → {{ $riwayat->batas_baru?->format('d/m/Y') ?? '-' }})
                            </span>
                        @endif
                    </div>
                    @if ($riwayat->catatan)
                        <div class="small text-muted">{{ $riwayat->catatan }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted small">Belum ada riwayat tindakan.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
