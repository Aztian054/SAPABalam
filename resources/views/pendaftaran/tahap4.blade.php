@extends('layouts.app')

@section('title', 'Pendaftaran — Tahap 4 Warkah')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold mb-1">Tahap 4 &mdash; Kebutuhan Warkah</h4>
    <p class="text-muted small mb-0">Nomor tiket <strong class="font-monospace">{{ $draf->kode_tiket }}</strong> &middot; {{ $biodata['nama_pemohon'] ?? '-' }}</p>
</div>

@include('pendaftaran._steps', ['aktif' => 4])

<form action="{{ route('pendaftaran.tahap4.store', $draf) }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-archive text-primary me-2"></i>Apakah permohonan ini membutuhkan warkah?</h6>

                <div class="row g-2">
                    @php
                        $pilihan = [
                            'none' => ['Tidak', 'Pendaftaran selesai dan langsung masuk Database.', 'bi-x-circle', 'border-secondary'],
                            'BT' => ['Buku Tanah (BT)', 'Satu permintaan warkah BT dikirim ke Petugas Warkah.', 'bi-journal-bookmark', 'border-warning'],
                            'SU' => ['Surat Ukur (SU)', 'Satu permintaan warkah SU dikirim ke Petugas Warkah.', 'bi-rulers', 'border-info'],
                            'keduanya' => ['BT &amp; SU', 'Dua permintaan warkah dibuat otomatis.', 'bi-collection', 'border-primary'],
                        ];
                    @endphp

                    @foreach ($pilihan as $nilai => [$judul, $keterangan, $ikon, $warna])
                        <div class="col-md-6">
                            <label class="card h-100 p-3 border-2 {{ $warna }} pilihan-warkah {{ old('warkah', $draf->pilihanWarkah()) === $nilai ? 'bg-primary bg-opacity-10' : '' }}" style="cursor:pointer;">
                                <div class="d-flex gap-3">
                                    <input class="form-check-input mt-1" type="radio" name="warkah" value="{{ $nilai }}"
                                           @checked(old('warkah', $draf->pilihanWarkah()) === $nilai)>
                                    <div>
                                        <div class="fw-semibold d-flex align-items-center gap-2">
                                            <i class="bi {{ $ikon }}"></i> {!! $judul !!}
                                        </div>
                                        <div class="text-muted small">{!! $keterangan !!}</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                @error('warkah') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                <div class="alert alert-secondary border-0 mt-3 mb-0 small">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Setelah diselesaikan, tiket berstatus <strong>Di Database</strong> dan masuk antrean Staf Seksi 1/2.
                    Permintaan warkah tampil di menu Petugas Warkah.
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('pendaftaran.tahap3', $draf) }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-gold px-4" onclick="return confirm('Selesaikan pendaftaran {{ $draf->kode_tiket }}?')">
                        <i class="bi bi-check2-circle me-1"></i> Selesaikan Pendaftaran
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clipboard-data text-primary me-2"></i>Ringkasan Pendaftaran</h6>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">No. Tiket</dt><dd class="col-7 font-monospace fw-semibold">{{ $draf->kode_tiket }}</dd>
                    <dt class="col-5 text-muted fw-normal">Pemohon</dt><dd class="col-7">{{ $biodata['nama_pemohon'] ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">NIK</dt><dd class="col-7">{{ $biodata['nik_pemohon'] ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Kelurahan / Kec.</dt>
                    <dd class="col-7">{{ $permohonan['kelurahan_desa'] ?? '-' }} / {{ $permohonan['kecamatan'] ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Jumlah Bidang</dt><dd class="col-7">{{ $permohonan['jumlah_bidang'] ?? 0 }}</dd>
                    <dt class="col-5 text-muted fw-normal">No. Hak</dt><dd class="col-7">{{ $permohonan['no_hak_sekarang'] ?? '-' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Ploting</dt><dd class="col-7"><span class="badge bg-success">Sudah</span></dd>
                </dl>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.pilihan-warkah').forEach((label) => {
        label.addEventListener('click', () => {
            document.querySelectorAll('.pilihan-warkah').forEach((item) => item.classList.remove('bg-primary', 'bg-opacity-10'));
            label.classList.add('bg-primary', 'bg-opacity-10');
        });
    });
</script>
@endsection
