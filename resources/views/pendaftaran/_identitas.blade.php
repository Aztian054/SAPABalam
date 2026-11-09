{{-- Blueprint 02 — urutan baku: No Tiket → No. Berkas → Biodata Pemohon. --}}
<div class="col-12">
    <label class="form-label fw-semibold">No. Tiket</label>
    <input type="text" class="form-control font-monospace" value="{{ $nomorTiket }}" readonly aria-readonly="true">
    <div class="form-text">
        Dibuat otomatis oleh sistem (<strong>JP</strong> + tanggal + urut 3 digit) dan tidak dapat diubah.
    </div>
</div>

<div class="col-12">
    <label for="no_berkas" class="form-label fw-semibold">No. Berkas</label>
    <input type="text" name="no_berkas" id="no_berkas"
           class="form-control @error('no_berkas') is-invalid @enderror"
           value="{{ old('no_berkas', $noBerkas ?? '') }}"
           placeholder="Nomor resmi pusat BPN (opsional)">
    <div class="form-text">
        Diisi manual. Format bebas, tidak unik, dan boleh kosong.
    </div>
    @error('no_berkas') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-12">
    <hr class="my-1">
    <h6 class="fw-bold mb-0"><i class="bi bi-person-vcard text-primary me-2"></i>Biodata Pemohon</h6>
</div>

<div class="col-12">
    <label for="nama_pemohon" class="form-label fw-semibold">Nama Pemohon <span class="text-danger">*</span></label>
    <input type="text" name="nama_pemohon" id="nama_pemohon"
           class="form-control @error('nama_pemohon') is-invalid @enderror"
           value="{{ old('nama_pemohon') }}" required maxlength="200">
    @error('nama_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label for="nik_pemohon" class="form-label fw-semibold">NIK</label>
    <input type="text" name="nik_pemohon" id="nik_pemohon" inputmode="numeric"
           class="form-control @error('nik_pemohon') is-invalid @enderror"
           value="{{ old('nik_pemohon') }}"
           maxlength="16" pattern="\d{16}" placeholder="16 digit (opsional)">
    @error('nik_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label for="no_hp_pemohon" class="form-label fw-semibold">No. HP <span class="text-danger">*</span></label>
    <input type="text" name="no_hp_pemohon" id="no_hp_pemohon"
           class="form-control @error('no_hp_pemohon') is-invalid @enderror"
           value="{{ old('no_hp_pemohon') }}" required maxlength="20">
    @error('no_hp_pemohon') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label for="kelurahan_desa" class="form-label fw-semibold">Kelurahan / Desa <span class="text-danger">*</span></label>
    <input type="text" name="kelurahan_desa" id="kelurahan_desa"
           class="form-control @error('kelurahan_desa') is-invalid @enderror"
           value="{{ old('kelurahan_desa') }}" required maxlength="100">
    @error('kelurahan_desa') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="col-md-6">
    <label for="kecamatan" class="form-label fw-semibold">Kecamatan</label>
    <input type="text" name="kecamatan" id="kecamatan"
           class="form-control @error('kecamatan') is-invalid @enderror"
           value="{{ old('kecamatan') }}" maxlength="100">
    @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
