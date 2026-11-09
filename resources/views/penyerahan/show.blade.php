@extends('layouts.app')

@section('title', 'Loket Penyerahan — Detail ' . $tiket->kode_tiket)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Berkas {{ $tiket->kode_tiket }}</h4>
        <p class="text-muted small mb-0">{{ $tiket->nama_pemohon }} &middot; {{ $tiket->jenisPermohonan->nama ?? '-' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('penyerahan.tiket-saya') }}" class="btn btn-outline-secondary btn-sm">Tiket Saya</a>
        <a href="{{ route('penyerahan.index') }}" class="btn btn-outline-primary btn-sm">Antrian Penyerahan</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">No Tiket</div>
                    <div class="h5 fw-bold font-monospace mb-0">{{ $tiket->kode_tiket }}</div>
                </div>
                <span class="badge bg-{{ $tiket->status_badge }} fs-6">{{ $tiket->status_label }}</span>
            </div>

            <dl class="row small mb-0">
                <dt class="col-sm-4 text-muted fw-normal">No. Berkas</dt><dd class="col-sm-8">{{ $tiket->no_berkas ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Pemohon</dt><dd class="col-sm-8">{{ $tiket->nama_pemohon }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">NIK</dt><dd class="col-sm-8">{{ $tiket->nik_pemohon ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No. HP</dt><dd class="col-sm-8">{{ $tiket->no_hp_pemohon ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Jenis Permohonan</dt><dd class="col-sm-8">{{ $tiket->jenisPermohonan->nama ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No Hak Sekarang</dt><dd class="col-sm-8">{{ $tiket->no_hak_sekarang ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Letak Tanah</dt>
                <dd class="col-sm-8">{{ $tiket->kelurahan_desa ?? '-' }} / {{ $tiket->kecamatan ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Jumlah Bidang</dt><dd class="col-sm-8">{{ $tiket->jumlah_bidang }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Dipegang Oleh</dt>
                <dd class="col-sm-8">{{ $tiket->penanggungJawab->name ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Waktu ADD</dt>
                <dd class="col-sm-8">
                    {{ $tiket->diambil_pada?->format('d/m/Y H:i') ?? '-' }}
                    @if ($tiket->lama_diambil) <span class="text-muted">&middot; {{ $tiket->lama_diambil }}</span> @endif
                </dd>
                @if ($tiket->diserahkan_pada)
                    <dt class="col-sm-4 text-muted fw-normal">Diserahkan</dt>
                    <dd class="col-sm-8">
                        {{ $tiket->diserahkan_pada->format('d/m/Y H:i') }}
                        &mdash; {{ $tiket->penerima_nama }} ({{ $tiket->penerima_hubungan_label }})
                        @if ($tiket->penerima_no_identitas)
                            <span class="text-muted">&middot; {{ $tiket->penerima_no_identitas }}</span>
                        @endif
                    </dd>
                @endif
                @if ($tiket->catatan_penyerahan)
                    <dt class="col-sm-4 text-muted fw-normal">Catatan Serah</dt>
                    <dd class="col-sm-8">{{ $tiket->catatan_penyerahan }}</dd>
                @endif
            </dl>
        </div>

        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-map text-success me-2"></i>Bidang Tanah</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>NIB</th><th>Sertipikat Lama</th><th>Jenis Hak</th><th>Pemegang Hak</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($tiket->bidangTanahs as $bidang)
                            <tr>
                                <td>{{ $bidang->urutan }}</td>
                                <td>{{ $bidang->nib ?? '-' }}</td>
                                <td>{{ $bidang->no_sertifikat_lama ?? '-' }}</td>
                                <td>{{ $bidang->jenis_hak ?? '-' }}</td>
                                <td>{{ $bidang->nama_pemegang_hak ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada bidang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($bisaSerah)
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-box-arrow-right text-success me-2"></i>Serah Terima Berkas</h6>
                <form action="{{ route('penyerahan.selesai.store', $tiket) }}" method="POST"
                      onsubmit="return confirm('SELESAIkan berkas ini? Status final dan tidak dapat dibatalkan.')">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="penerima_nama" class="form-label fw-semibold">Nama penerima <span class="text-danger">*</span></label>
                            <input type="text" name="penerima_nama" id="penerima_nama" value="{{ old('penerima_nama', $tiket->nama_pemohon) }}"
                                   class="form-control @error('penerima_nama') is-invalid @enderror" required minlength="3" maxlength="150">
                            @error('penerima_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="penerima_hubungan" class="form-label fw-semibold">Hubungan <span class="text-danger">*</span></label>
                            <select name="penerima_hubungan" id="penerima_hubungan" class="form-select @error('penerima_hubungan') is-invalid @enderror" required>
                                @foreach ($hubunganPenerima as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('penerima_hubungan', 'pemohon') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('penerima_hubungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="penerima_no_identitas" class="form-label fw-semibold">No identitas penerima</label>
                            <input type="text" name="penerima_no_identitas" id="penerima_no_identitas" value="{{ old('penerima_no_identitas') }}"
                                   class="form-control @error('penerima_no_identitas') is-invalid @enderror" maxlength="50"
                                   placeholder="Wajib bila kuasa">
                            @error('penerima_no_identitas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label for="catatan_penyerahan" class="form-label fw-semibold">Catatan <span class="text-muted small">(opsional)</span></label>
                            <textarea name="catatan" id="catatan_penyerahan" rows="2" class="form-control" maxlength="1000"
                                      placeholder="Contoh: diterima langsung oleh pemohon."></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success px-4 mt-3">
                        <i class="bi bi-check2-circle me-1"></i> SELESAI
                    </button>
                </form>
            </div>
        @elseif ($tiket->status === 'diproses')
            <div class="alert alert-warning border-0">
                <i class="bi bi-person-lock me-1"></i>
                Berkas sedang dipegang {{ $tiket->penanggungJawab->name ?? 'petugas lain' }}.
            </div>
        @elseif ($tiket->status === 'di_database')
            <div class="alert alert-info border-0">
                <i class="bi bi-info-circle me-1"></i>
                Berkas masih di Database. ADD dari Antrian Penyerahan untuk menyerahkannya.
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat</h6>
            @forelse ($tiket->riwayatStatuses as $riwayat)
                <div class="border-start border-3 border-primary ps-3 pb-3">
                    <div class="small text-muted">
                        {{ $riwayat->created_at?->format('d/m/Y H:i') }}
                        &middot; {{ $riwayat->pelaku_label }}
                    </div>
                    <div class="small fw-semibold">{{ ucfirst((string) $riwayat->stage_dari) }} &rarr; {{ ucfirst((string) $riwayat->stage_ke) }}</div>
                    <div class="small text-muted">{{ $riwayat->keterangan }}</div>
                </div>
            @empty
                <div class="text-muted small">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
