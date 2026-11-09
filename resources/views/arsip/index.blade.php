@extends('layouts.app')

@section('title', 'Daftar Arsip')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Daftar Arsip</h4>
        <p class="text-muted small mb-0">
            Berkas selesai yang sudah melewati {{ \App\Models\Setting::angka('hari_arsip', 30) }} hari.
            Halaman ini hanya untuk membaca — tidak ada tindakan yang mengubah berkas.
        </p>
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $kartu = [
            ['Total Arsip', $ringkasan['total'], 'primary', 'bi-archive-fill'],
            ['Diarsipkan Bulan Ini', $ringkasan['bulan_ini'], 'success', 'bi-calendar-check-fill'],
            ['Tersimpan di Folder', $ringkasan['folder'], 'secondary', 'bi-folder-fill'],
        ];
    @endphp
    @foreach ($kartu as [$label, $nilai, $warna, $ikon])
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-4 border-{{ $warna }} h-100">
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">{{ $label }}</div>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <h4 class="fw-bold mb-0 text-{{ $warna }}">{{ number_format($nilai) }}</h4>
                    <i class="bi {{ $ikon }} text-{{ $warna }}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card card-custom p-4">
    <form action="{{ route('arsip.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Cari pintar</label>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                   placeholder="No Tiket, No. Berkas, pemohon, No Hak, NIB…">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Jenis layanan</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach ($daftarJenis as $jenis)
                    <option value="{{ $jenis->id }}" @selected((string) request('jenis') === (string) $jenis->id)>{{ $jenis->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Arsip dari</label>
            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">sampai</label>
            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.86rem">
            <thead class="table-light">
                <tr>
                    <th style="width:3rem">No</th>
                    <th>No Tiket</th>
                    <th>No. Berkas</th>
                    <th>Pemohon</th>
                    <th>Jenis Layanan</th>
                    <th>Lokasi</th>
                    <th>Tgl Selesai</th>
                    <th>Diarsipkan</th>
                    <th>Lama Simpan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tikets as $tiket)
                    <tr>
                        <td class="text-muted">{{ $tikets->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('arsip.show', $tiket) }}" class="fw-bold font-monospace text-decoration-none">{{ $tiket->kode_tiket }}</a>
                        </td>
                        <td>{{ $tiket->no_berkas ?: '-' }}</td>
                        <td>{{ $tiket->nama_pemohon }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $tiket->jenisPermohonan->nama ?? '-' }}</span></td>
                        <td>
                            <span class="small">
                                {{ collect([$tiket->kelurahan_desa, $tiket->kecamatan])->filter()->implode(', ') ?: '-' }}
                            </span>
                        </td>
                        <td>{{ $tiket->tanggal_selesai?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $tiket->diarsipkan_pada?->format('d/m/Y') ?? '-' }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $tiket->lama_simpan_arsip ?? '-' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('arsip.show', $tiket) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Belum ada berkas yang diarsipkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $tikets->links() }}</div>
</div>
@endsection
