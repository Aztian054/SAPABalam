<div class="card card-custom mb-3">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Bidang Tanah ({{ $tiket->bidangTanahs->count() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>NIB</th><th>No. Sertifikat Lama</th><th>No. Sertifikat El.</th>
                    <th>Jenis Hak</th><th>Pemegang Hak</th><th>Kelurahan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tiket->bidangTanahs as $b)
                    <tr>
                        <td>{{ $b->urutan }}</td>
                        <td><span class="badge bg-light border text-dark">{{ $b->nib ?? '-' }}</span></td>
                        <td>{{ $b->no_sertifikat_lama ?? '-' }}</td>
                        <td>{{ $b->no_sertifikat_elektronik ?? '-' }}</td>
                        <td>{{ $b->jenis_hak ?? '-' }}</td>
                        <td>{{ $b->nama_pemegang_hak ?? '-' }}</td>
                        <td>{{ $b->desa_kelurahan ?? '-' }}, {{ $b->kecamatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data bidang tanah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>