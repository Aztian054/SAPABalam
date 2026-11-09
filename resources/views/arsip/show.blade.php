@extends('layouts.app')

@section('title', 'Arsip ' . $tiket->kode_tiket)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 font-monospace">{{ $tiket->kode_tiket }}</h4>
        <p class="text-muted small mb-0">
            Diarsipkan {{ $tiket->diarsipkan_pada?->format('d/m/Y H:i') ?? '-' }}
            &middot; lama simpan {{ $tiket->lama_simpan_arsip ?? '-' }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-secondary align-self-center px-3 py-2"><i class="bi bi-archive-fill me-1"></i>Arsip — read-only</span>
        <a href="{{ route('arsip.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Daftar Arsip
        </a>
    </div>
</div>

<div class="alert alert-light border small">
    <i class="bi bi-info-circle me-1"></i>
    Berkas ini sudah ditutup dan diarsipkan otomatis. Tidak ada tindakan yang dapat dilakukan dari halaman ini.
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard text-primary me-2"></i>Data Pemohon</h6>
            <dl class="row small mb-0">
                <dt class="col-sm-4 text-muted fw-normal">No. Berkas</dt><dd class="col-sm-8">{{ $tiket->no_berkas ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Nama pemohon</dt><dd class="col-sm-8">{{ $tiket->nama_pemohon }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">NIK</dt><dd class="col-sm-8">{{ $tiket->nik_pemohon ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No HP</dt><dd class="col-sm-8">{{ $tiket->no_hp_pemohon ?? $tiket->nomor_telepon ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Tanggal masuk</dt><dd class="col-sm-8">{{ $tiket->tanggal_masuk?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Tanggal selesai</dt><dd class="col-sm-8">{{ $tiket->tanggal_selesai?->format('d/m/Y') ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Diserahkan oleh</dt><dd class="col-sm-8">{{ $tiket->diserahkanOleh->name ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Penerima</dt>
                <dd class="col-sm-8">
                    {{ $tiket->penerima_nama ?? '-' }}
                    @if ($tiket->penerima_hubungan_label)
                        <span class="text-muted">({{ $tiket->penerima_hubungan_label }})</span>
                    @endif
                </dd>
            </dl>
        </div>

        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-briefcase text-warning me-2"></i>Data Permohonan</h6>
            <dl class="row small mb-0">
                <dt class="col-sm-4 text-muted fw-normal">Jenis layanan</dt><dd class="col-sm-8">{{ $tiket->jenisPermohonan->nama ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">No Hak sekarang</dt><dd class="col-sm-8">{{ $tiket->no_hak_sekarang ?? '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Lokasi</dt>
                <dd class="col-sm-8">{{ collect([$tiket->kelurahan_desa, $tiket->kecamatan])->filter()->implode(', ') ?: '-' }}</dd>
                <dt class="col-sm-4 text-muted fw-normal">Jumlah bidang</dt><dd class="col-sm-8">{{ $tiket->jumlah_bidang }}</dd>
                @if ($tiket->keterangan)
                    <dt class="col-sm-4 text-muted fw-normal">Keterangan</dt><dd class="col-sm-8">{{ $tiket->keterangan }}</dd>
                @endif
            </dl>

            <div class="table-responsive mt-3">
                <table class="table table-sm align-middle mb-0" style="font-size:0.82rem">
                    <thead class="table-light">
                        <tr>
                            <th style="width:2.5rem">#</th>
                            <th>NIB</th>
                            <th>Sertifikat Lama</th>
                            <th>Jenis Hak</th>
                            <th>Desa/Kel.</th>
                            <th>Kecamatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tiket->bidangTanahs as $bidang)
                            <tr>
                                <td class="text-muted">{{ $bidang->urutan }}</td>
                                <td class="font-monospace">{{ $bidang->nib ?? '-' }}</td>
                                <td class="font-monospace">{{ $bidang->no_sertifikat_lama ?? '-' }}</td>
                                <td>{{ $bidang->jenis_hak ?? '-' }}</td>
                                <td>{{ $bidang->desa_kelurahan ?? '-' }}</td>
                                <td>{{ $bidang->kecamatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data bidang tanah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-folder-fill text-secondary me-2"></i>Penyimpanan Fisik</h6>
            @forelse ($tiket->arsips as $arsip)
                <div class="border rounded-3 p-3 mb-2">
                    <div class="fw-semibold">{{ $arsip->folder->nama_folder ?? 'Tanpa folder' }}</div>
                    <div class="small text-muted">
                        {{ $arsip->nama_arsip ?? $tiket->kode_tiket }}
                        &middot; {{ $arsip->tanggal_arsip?->format('d/m/Y') ?? '-' }}
                    </div>
                    @if ($arsip->folder?->lokasi_fisik)
                        <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $arsip->folder->lokasi_fisik }}</div>
                    @endif
                    @if ($arsip->keterangan)
                        <div class="small text-muted">{{ $arsip->keterangan }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted small">Belum tercatat pada folder fisik.</div>
            @endforelse
        </div>

        <div class="card card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Berkas</h6>
            @forelse ($tiket->riwayatStatuses->sortByDesc('id') as $riwayat)
                <div class="border-start border-3 border-primary ps-3 pb-3">
                    <div class="small text-muted">{{ $riwayat->created_at?->format('d/m/Y H:i') }} &middot; {{ $riwayat->pelaku_label }}</div>
                    <div class="small fw-semibold">{{ $riwayat->stage_ke ?? $riwayat->stage_dari ?? '-' }}</div>
                    @if ($riwayat->keterangan)
                        <div class="small text-muted">{{ $riwayat->keterangan }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted small">Belum ada riwayat.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
