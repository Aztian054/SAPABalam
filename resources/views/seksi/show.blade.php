@extends('layouts.app')

@section('title', 'Seksi — Detail ' . $tiket->kode_tiket)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Berkas {{ $tiket->kode_tiket }}</h4>
        <p class="text-muted small mb-0">{{ $tiket->nama_pemohon }} &middot; {{ $tiket->jenisPermohonan->nama ?? '-' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('seksi.tiket-saya') }}" class="btn btn-outline-secondary btn-sm">Tiket Saya</a>
        <a href="{{ route('seksi.index') }}" class="btn btn-outline-primary btn-sm">Cari &amp; ADD</a>
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
                <dt class="col-sm-4 text-muted fw-normal">No Hak Sebelumnya</dt><dd class="col-sm-8">{{ $tiket->no_hak_sebelumnya ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Letak Tanah</dt>
                <dd class="col-sm-8">{{ $tiket->kelurahan_desa ?? '-' }} / {{ $tiket->kecamatan ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Jumlah Bidang</dt><dd class="col-sm-8">{{ $tiket->jumlah_bidang }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Penanggung Jawab</dt>
                <dd class="col-sm-8">
                    @if ($tiket->penanggungJawab)
                        {{ $tiket->penanggungJawab->name }} &mdash; {{ $tiket->penanggungJawab->role_label }} / {{ $tiket->penanggungJawab->seksi_label }}
                    @else
                        -
                    @endif
                </dd>
                <dt class="col-sm-4 text-muted fw-normal">Peran / Bidang</dt><dd class="col-sm-8">{{ $tiket->peran_bidang_label }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Waktu ADD / Lama</dt>
                <dd class="col-sm-8">
                    {{ $tiket->diambil_pada?->format('d/m/Y H:i') ?? '-' }}
                    @if ($tiket->lama_diambil) <span class="text-muted">&middot; {{ $tiket->lama_diambil }}</span> @endif
                </dd>
                @if ($tiket->ditolak_pada)
                    <dt class="col-sm-4 text-muted fw-normal">Terakhir Ditolak</dt>
                    <dd class="col-sm-8 text-danger">{{ $tiket->ditolak_pada->format('d/m/Y H:i') }} &mdash; {{ $tiket->alasan_penolakan }}</dd>
                @endif
            </dl>
        </div>

        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-map text-success me-2"></i>Bidang Tanah</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>NIB</th><th>Sertipikat Lama</th><th>Jenis Hak</th><th>Pemegang Hak</th><th>Desa / Kecamatan</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($tiket->bidangTanahs as $bidang)
                            <tr>
                                <td>{{ $bidang->urutan }}</td>
                                <td>{{ $bidang->nib ?? '-' }}</td>
                                <td>{{ $bidang->no_sertifikat_lama ?? '-' }}</td>
                                <td>{{ $bidang->jenis_hak ?? '-' }}</td>
                                <td>{{ $bidang->nama_pemegang_hak ?? '-' }}</td>
                                <td>{{ $bidang->desa_kelurahan ?? '-' }} / {{ $bidang->kecamatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada bidang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($bisaProses)
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-send-check text-primary me-2"></i>KIRIM Hasil Pekerjaan</h6>

                <form action="{{ route('seksi.selesai.store', $tiket) }}" method="POST" class="mb-4"
                      onsubmit="return confirm('KIRIM sebagai SELESAI? Berkas kembali ke Database.')">
                    @csrf
                    <label for="catatan_selesai" class="form-label fw-semibold">Selesai &mdash; catatan <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan_selesai" rows="2" class="form-control mb-2" required minlength="3" maxlength="1000"
                              placeholder="Contoh: Berkas lengkap, data BT sesuai."></textarea>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check2-circle me-1"></i> SELESAI
                    </button>
                </form>

                <hr>

                <form action="{{ route('seksi.tolak', $tiket) }}" method="POST"
                      onsubmit="return confirm('KIRIM sebagai DITOLAK? Tiket menjadi tanggung jawab Anda dan tidak dapat di-ADD staf lain.')">
                    @csrf
                    <label for="catatan_tolak" class="form-label fw-semibold">Tolak &mdash; alasan <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan_tolak" rows="2" class="form-control mb-2" required minlength="3" maxlength="1000"
                              placeholder="Contoh: Berkas kurang lengkap / data tidak sesuai."></textarea>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-x-octagon me-1"></i> TOLAK
                    </button>
                </form>
            </div>
        @elseif ($tiket->status === 'diproses')
            <div class="alert alert-warning border-0">
                <i class="bi bi-person-lock me-1"></i>
                Berkas sedang diproses {{ $tiket->penanggungJawab->name ?? 'akun lain' }}. Hanya pemegang berkas yang dapat KIRIM.
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-archive text-primary me-2"></i>Warkah</h6>
            @forelse ($tiket->warkahPermintaans as $warkah)
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <span class="small fw-semibold">{{ $warkah->jenis }}</span>
                    <span class="badge bg-{{ $warkah->status_badge }}">{{ $warkah->status_label }}</span>
                </div>
            @empty
                <div class="text-muted small">Tidak ada permintaan warkah.</div>
            @endforelse
            <div class="form-text mt-2">Daftar warkah hanya baca. Perubahan dilakukan Petugas Warkah.</div>
        </div>

        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat / Diagram Alur</h6>
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
