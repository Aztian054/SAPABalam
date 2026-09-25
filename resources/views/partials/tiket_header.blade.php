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
                @if($tiket->status_pembetulan !== 'P0')
                    <span class="badge bg-dark text-uppercase ms-1" style="font-size:0.72rem;">{{ $tiket->status_pembetulan }}</span>
                @endif
                @if($tiket->diserahkan_ke_validator)
                    <span class="badge bg-info text-dark text-uppercase ms-1" style="font-size:0.72rem;">
                        <i class="bi bi-unlock-fill me-1"></i>Diserahkan ke Validator
                    </span>
                @endif
                <small class="text-muted d-block mt-1">
                    Revisi ke-{{ $tiket->revisi_ke }} &bull; Masuk {{ $tiket->tanggal_masuk?->format('d/m/Y') }}
                </small>
            </div>
            <div class="text-md-end">
                <small class="d-block text-muted">Jenis Permohonan</small>
                <strong class="text-dark">{{ $tiket->jenisPermohonan?->nama }}</strong>
                <div class="mt-1">
                    <span class="badge bg-light text-dark border">
                        BT: <span class="text-{{ $tiket->status_pra_btel === 'selesai' ? 'success' : ($tiket->status_pra_btel === 'proses' ? 'warning' : 'secondary') }}">{{ strtoupper($tiket->status_pra_btel ?? 'menunggu') }}</span>
                    </span>
                    <span class="badge bg-light text-dark border">
                        SU: <span class="text-{{ $tiket->status_pra_suel === 'selesai' ? 'success' : ($tiket->status_pra_suel === 'proses' ? 'warning' : 'secondary') }}">{{ strtoupper($tiket->status_pra_suel ?? 'menunggu') }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Pemohon</small><strong class="text-dark">{{ $tiket->nama_pemohon }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">NIK</small><strong class="text-dark">{{ $tiket->nik_pemohon ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">No. HP</small><strong class="text-dark">{{ $tiket->no_hp_pemohon }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <small class="text-muted">Email Pemohon</small>
                    <strong class="text-dark">
                        {{ $tiket->email_pemohon ?? '-' }}
                        @if(!empty($tiket->email_pemohon) && $tiket->status === 'dikembalikan' && auth()->user()?->role !== 'pemimpin')
                            <form action="{{ route('tikets.kirim_notifikasi_revisi', $tiket->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0 ms-1 text-primary" title="Kirim ulang notifikasi revisi ke email pemohon">
                                    <i class="bi bi-envelope-arrow-up"></i>
                                </button>
                            </form>
                        @endif
                    </strong>
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