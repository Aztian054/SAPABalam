<div class="card card-custom mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1 fw-bold text-dark">
                    <i class="bi bi-ticket-detailed-fill text-primary me-2"></i>{{ $tiket->kode_tiket }}
                </h5>
                <span class="badge bg-{{ $tiket->status_badge }} text-uppercase" style="font-size:0.72rem;">
                    {{ $tiket->status_label }}
                </span>
                <small class="text-muted d-block mt-1">
                    Masuk {{ $tiket->tanggal_masuk?->format('d/m/Y') }}
                    @if($tiket->penanggungJawab)
                        &bull; Pemegang: {{ $tiket->penanggungJawab->name }} ({{ $tiket->penanggungJawab->jabatan_label }})
                    @endif
                </small>
            </div>
            <div class="text-md-end">
                <small class="d-block text-muted">Jenis Permohonan</small>
                <strong class="text-dark">{{ $tiket->jenisPermohonan?->nama }}</strong>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                @include('partials.no_berkas')
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Pemohon</small><strong class="text-dark">{{ $tiket->nama_pemohon }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">NIK</small><strong class="text-dark">{{ $tiket->nik_pemohon ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">No. HP</small><strong class="text-dark">{{ $tiket->no_hp_pemohon }}</strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Hak Sekarang</small><strong class="text-dark">{{ $tiket->no_hak_sekarang ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Kelurahan</small><strong class="text-dark">{{ $tiket->kelurahan_desa ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Kecamatan</small><strong class="text-dark">{{ $tiket->kecamatan ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Jumlah Bidang</small><strong class="text-dark">{{ $tiket->jumlah_bidang }}</strong>
                </div>
            </div>
        </div>

        @if($tiket->keterangan)
            <div class="alert alert-light border mt-3 mb-0 py-2">
                <i class="bi bi-info-circle me-1"></i>{{ $tiket->keterangan }}
            </div>
        @endif
    </div>
</div>

@include('partials.alur_eksekusi')
