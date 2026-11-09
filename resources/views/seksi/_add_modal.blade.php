@php($daftarPeran = $peranBidang ?? \App\Models\Tiket::PERAN_BIDANG)

<div class="modal fade" id="modalAddTiket" tabindex="-1" aria-labelledby="modalAddTiketLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formAddTiket" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modalAddTiketLabel">ADD Berkas</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <dl class="row small mb-3">
                    <dt class="col-4 text-muted fw-normal">No Tiket</dt>
                    <dd class="col-8 font-monospace fw-bold" id="addKode">-</dd>
                    <dt class="col-4 text-muted fw-normal">No. Berkas</dt>
                    <dd class="col-8" id="addNoBerkas">-</dd>
                    <dt class="col-4 text-muted fw-normal">Pemohon</dt>
                    <dd class="col-8 mb-0" id="addPemohon">-</dd>
                </dl>

                <label for="peran_bidang" class="form-label fw-semibold">Peran / Bidang <span class="text-danger">*</span></label>
                <select name="peran_bidang" id="peran_bidang" class="form-select @error('peran_bidang') is-invalid @enderror" required>
                    <option value="">— Pilih pekerjaan —</option>
                    @foreach ($daftarPeran as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('peran_bidang') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Peran/Bidang dicatat pada tiket dan tampil di Tiket Saya.</div>
                @error('peran_bidang') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="addSubmit">ADD</button>
            </div>
        </form>
    </div>
</div>
