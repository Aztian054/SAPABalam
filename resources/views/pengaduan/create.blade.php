@extends('layouts.app')

@section('title', 'Loket Pengaduan — Buat Pengaduan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Buat Pengaduan</h4>
        <p class="text-muted small mb-0">Nomor pengaduan (LP) dibuat otomatis setelah KIRIM. Semua input berupa teks, tidak ada upload file.</p>
    </div>
    <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i> Daftar Pengaduan
    </a>
</div>

<form action="{{ route('pengaduan.store') }}" method="POST" id="formPengaduan">
    @csrf
    <div class="row g-3">
        <div class="col-lg-7">
            <!-- 1. Nomor LP Otomatis -->
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-hash text-primary me-2"></i>Informasi Tiket &amp; Tujuan</h6>
                <div class="mb-3">
                    <label class="form-label fw-semibold">No Tiket Pengaduan (LP)</label>
                    <input type="text" name="nomor_pengaduan" id="nomor_pengaduan"
                           value="{{ $nomorPengaduan }}" class="form-control font-monospace fw-bold text-uppercase"
                           readonly>
                    <div class="form-text small text-muted">Dibuat otomatis sesuai format admin: {{ config('loket.prefix_pengaduan', 'LP') }} + ddmmyyyy + urutan. Tidak dapat diubah.</div>
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label fw-semibold">Kategori Pengaduan <span class="text-danger">*</span></label>
                    <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror"
                            onchange="toggleDetailTanah(this.value)" required>
                        <option value="">— Pilih kategori —</option>
                        @foreach ($jenisPengaduan as $nilai => $label)
                            <option value="{{ $nilai }}" {{ old('jenis') === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="seksi_tujuan" class="form-label fw-semibold">Seksi Tujuan <span class="text-danger">*</span></label>
                    <select name="seksi_tujuan" id="seksi_tujuan" class="form-select @error('seksi_tujuan') is-invalid @enderror" required>
                        <option value="">— Pilih unit —</option>
                        @foreach ($daftarSeksi as $nilai => $label)
                            <option value="{{ $nilai }}" {{ old('seksi_tujuan') === (string) $nilai ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('seksi_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text small text-muted">Pilih 1 unit tujuan saja. Kasi unit tersebut yang akan menindaklanjuti atau meminta bantuan ke Kasi lain.</div>
                </div>
            </div>

            <!-- 2. Identitas Pengadu (Selalu tampil) -->
            <div class="card card-custom p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard text-primary me-2"></i>Identitas Pengadu</h6>

                <p class="small text-muted mb-3">
                    @if (old('jenis') === 'permasalahan_tanah')
                        Wajib diisi untuk keperluan administrasi surat formal.
                    @else
                        Anonim — Nama, NIK, &amp; No HP boleh dikosongkan.
                    @endif
                </p>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_anonim" id="is_anonim" value="1" class="form-check-input"
                           {{ old('is_anonim') ? 'checked' : '' }} onclick="toggleAnonim()">
                    <label class="form-check-label small" for="is_anonim">
                        Anonim — Nama, NIK, &amp; No HP boleh dikosongkan
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_pengadu" class="form-label fw-semibold">Nama Pengadu</label>
                        <input type="text" name="nama_pengadu" id="nama_pengadu" value="{{ old('nama_pengadu') }}"
                               class="form-control @error('nama_pengadu') is-invalid @enderror" maxlength="150">
                        @error('nama_pengadu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="nik_pengadu" class="form-label fw-semibold">Nomor Identitas (NIK)</label>
                        <input type="text" name="nik_pengadu" id="nik_pengadu" value="{{ old('nik_pengadu') }}"
                               class="form-control @error('nik_pengadu') is-invalid @enderror" maxlength="16" inputmode="numeric">
                        @error('nik_pengadu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="no_hp_pengadu" class="form-label fw-semibold">No HP</label>
                        <input type="text" name="no_hp_pengadu" id="no_hp_pengadu" value="{{ old('no_hp_pengadu') }}"
                               class="form-control @error('no_hp_pengadu') is-invalid @enderror" maxlength="30">
                        @error('no_hp_pengadu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email_pengadu" class="form-label fw-semibold">Email</label>
                        <input type="email" name="email_pengadu" id="email_pengadu" value="{{ old('email_pengadu') }}"
                               class="form-control @error('email_pengadu') is-invalid @enderror" maxlength="100">
                        @error('email_pengadu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="alamat_pengadu" class="form-label fw-semibold">Alamat Pemohon</label>
                        <textarea name="alamat_pengadu" id="alamat_pengadu" rows="2"
                                  class="form-control @error('alamat_pengadu') is-invalid @enderror" maxlength="255">{{ old('alamat_pengadu') }}</textarea>
                        @error('alamat_pengadu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- 3. Status Pelapor & Kuasa (hanya Tanah) -->
            <div id="kuasaSection" class="card card-custom p-4 mb-3" style="display:none;">
                <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-person text-primary me-2"></i>Status Pelapor &amp; Kuasa</h6>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Bertindak Untuk <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="bertindak_untuk" id="bertindakDiri" value="diri"
                               {{ old('bertindak_untuk') === 'diri' ? 'checked' : '' }} onchange="toggleKuasaFields()">
                        <label class="form-check-label" for="bertindakDiri">Diri Sendiri</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="bertindak_untuk" id="bertindakKuasa" value="kuasa"
                               {{ old('bertindak_untuk') === 'kuasa' ? 'checked' : '' }} onchange="toggleKuasaFields()">
                        <label class="form-check-label" for="bertindakKuasa">Dikuasakan (Penerima Kuasa)</label>
                    </div>
                    @error('bertindak_untuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div id="kuasaFields" style="display:none; margin-top:1rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="surat_kuasa" class="form-label fw-semibold">Surat Kuasa (Nomor/Tanggal)</label>
                            <input type="text" name="surat_kuasa" id="surat_kuasa" value="{{ old('surat_kuasa') }}"
                                   class="form-control @error('surat_kuasa') is-invalid @enderror" maxlength="255">
                            @error('surat_kuasa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nama_kuasa" class="form-label fw-semibold">Nama &amp; Nomor Identitas Kuasa</label>
                            <input type="text" name="nama_kuasa" id="nama_kuasa" value="{{ old('nama_kuasa') }}"
                                   class="form-control @error('nama_kuasa') is-invalid @enderror" maxlength="150">
                            @error('nama_kuasa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nik_kuasa" class="form-label fw-semibold">NIK Kuasa</label>
                            <input type="text" name="nik_kuasa" id="nik_kuasa" value="{{ old('nik_kuasa') }}"
                                   class="form-control @error('nik_kuasa') is-invalid @enderror" maxlength="16" inputmode="numeric">
                            @error('nik_kuasa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="alamat_kuasa" class="form-label fw-semibold">Alamat Kuasa</label>
                            <input type="text" name="alamat_kuasa" id="alamat_kuasa" value="{{ old('alamat_kuasa') }}"
                                   class="form-control @error('alamat_kuasa') is-invalid @enderror" maxlength="255">
                            @error('alamat_kuasa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="telp_kuasa" class="form-label fw-semibold">No. Telp/HP/Email Kuasa</label>
                            <input type="text" name="telp_kuasa" id="telp_kuasa" value="{{ old('telp_kuasa') }}"
                                   class="form-control @error('telp_kuasa') is-invalid @enderror" maxlength="30">
                            @error('telp_kuasa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Detail Objek Tanah (hanya Tanah) -->
            <div id="detailTanah" class="card card-custom p-4 mb-3" style="display:none;">
                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt text-primary me-2"></i>Detail Objek Tanah (Surat Formal)</h6>

                <div class="mb-3">
                    <label for="hal" class="form-label fw-semibold">Perihal / Hal</label>
                    <input type="text" name="hal" id="hal" value="{{ old('hal') }}"
                           class="form-control @error('hal') is-invalid @enderror" maxlength="255">
                    @error('hal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="desa_kelurahan" class="form-label fw-semibold">Desa / Kelurahan</label>
                        <input type="text" name="desa_kelurahan" id="desa_kelurahan" value="{{ old('desa_kelurahan') }}"
                               class="form-control @error('desa_kelurahan') is-invalid @enderror" maxlength="100">
                        @error('desa_kelurahan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="kecamatan" class="form-label fw-semibold">Kecamatan</label>
                        <input type="text" name="kecamatan" id="kecamatan" value="{{ old('kecamatan') }}"
                               class="form-control @error('kecamatan') is-invalid @enderror" maxlength="100">
                        @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="kabupaten_kota" class="form-label fw-semibold">Kabupaten / Kota</label>
                        <input type="text" name="kabupaten_kota" id="kabupaten_kota" value="{{ old('kabupaten_kota') }}"
                               class="form-control @error('kabupaten_kota') is-invalid @enderror" maxlength="100">
                        @error('kabupaten_kota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="provinsi" class="form-label fw-semibold">Provinsi</label>
                        <input type="text" name="provinsi" id="provinsi" value="{{ old('provinsi') }}"
                               class="form-control @error('provinsi') is-invalid @enderror" maxlength="100">
                        @error('provinsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="luas" class="form-label fw-semibold">Luas Tanah</label>
                        <input type="text" name="luas" id="luas" value="{{ old('luas') }}"
                               class="form-control @error('luas') is-invalid @enderror" maxlength="100"
                               placeholder="Contoh: 500 m²">
                        @error('luas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="status_tanah" class="form-label fw-semibold">Status Tanah yang Dipermasalahkan</label>
                        <input type="text" name="status_tanah" id="status_tanah" value="{{ old('status_tanah') }}"
                               class="form-control @error('status_tanah') is-invalid @enderror" maxlength="100"
                               placeholder="Contoh: SHM No. 123 / Girik / Tanah Negara">
                        @error('status_tanah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- 5. Pokok Masalah & Daftar Bukti (hanya Tanah) -->
            <div id="masalahSection" class="card card-custom p-4 mb-3" style="display:none;">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-task text-primary me-2"></i>Pokok Masalah &amp; Daftar Bukti</h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="kategori_aduan" class="form-label fw-semibold">Kategori Aduan</label>
                        <input type="text" name="kategori_aduan" id="kategori_aduan" value="{{ old('kategori_aduan') }}"
                               class="form-control @error('kategori_aduan') is-invalid @enderror" maxlength="100"
                               placeholder="Pelayanan Pertanahan / Pengaduan Pegawai / dll">
                        @error('kategori_aduan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="kelompok_aduan" class="form-label fw-semibold">Kelompok Aduan</label>
                        <select name="kelompok_aduan" id="kelompok_aduan"
                               class="form-select @error('kelompok_aduan') is-invalid @enderror">
                            <option value="">— Pilih —</option>
                            <option value="Dengan Pengawasan" {{ old('kelompok_aduan') === 'Dengan Pengawasan' ? 'selected' : '' }}>Dengan Pengawasan</option>
                            <option value="Tanpa Pengawasan" {{ old('kelompok_aduan') === 'Tanpa Pengawasan' ? 'selected' : '' }}>Tanpa Pengawasan</option>
                        </select>
                        @error('kelompok_aduan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Uraian Singkat Masalah (Poin-poin)</label>
                    <div id="poinContainer" class="row g-2">
                        @foreach(range(1, 5) as $i)
                            <div class="col-md-6 poin-item">
                                <textarea name="uraian_masalah_{{ $i }}" id="uraian_masalah_{{ $i }}" rows="2"
                                          class="form-control @error('uraian_masalah_'.$i) is-invalid @enderror"
                                          placeholder="Poin {{ $i }}...">{{ old('uraian_masalah_'.$i) }}</textarea>
                                @error('uraian_masalah_'.$i) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="tambahPoin()">
                        <i class="bi bi-plus me-1"></i> Tambah Poin (maksimal 5)
                    </button>
                </div>

                <div class="mb-3 mt-4">
                    <label class="form-label fw-semibold">Daftar Data yang Dilampirkan (Sebutkan Nama Dokumen)</label>
                    <div id="lampiranContainer" class="row g-2">
                        @foreach(range(1, 4) as $i)
                            <div class="col-md-6 lampiran-item">
                                <textarea name="lampiran_{{ $i }}" id="lampiran_{{ $i }}" rows="2"
                                          class="form-control @error('lampiran_'.$i) is-invalid @enderror"
                                          placeholder="Lampiran {{ $i }}...">{{ old('lampiran_'.$i) }}</textarea>
                                @error('lampiran_'.$i) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="tambahLampiran()">
                        <i class="bi bi-plus me-1"></i> Tambah Lampiran (maksimal 4)
                    </button>
                </div>
            </div>

            <!-- 6. Catatan / Uraian Pengaduan (Selalu tampil) -->
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text text-primary me-2"></i>Catatan / Uraian Pengaduan</h6>

                <div class="mb-3">
                    <label for="uraian" class="form-label fw-semibold">Uraian <span class="text-danger">*</span></label>
                    <textarea name="uraian" id="uraian" rows="6" class="form-control @error('uraian') is-invalid @enderror"
                              required minlength="20" maxlength="2000"
                              placeholder="{{ old('jenis') === 'permasalahan_tanah' ? 'Uraian singkat masalah (detail telah diisi di atas)' : 'Uraikan keluhan/saran minimal 20 karakter.' }}">{{ old('uraian') }}</textarea>
                    <div class="form-text small text-muted">Minimal 20 karakter. Tidak ada upload file — semua berupa teks.</div>
                    @error('uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-1"></i> KIRIM PENGADUAN
                    </button>
                    <a href="{{ route('pengaduan.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-custom p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Ketentuan</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Nomor pengaduan berformat <span class="font-monospace">LP + ddmmyyyy + urutan</span> dan tidak dapat diubah.</li>
                    <li>Pilih <strong>1 kategori</strong> dan <strong>1 Seksi tujuan</strong> saja.</li>
                    <li>Identitas pengadu diisi sekali dan otomatis terhubung ke surat formal (jika kategori Tanah).</li>
                    <li>Jika kategori <strong>Permasalahan Tanah</strong>, isi Detail Objek Tanah dan Pokok Masalah yang muncul.</li>
                    <li>Setelah KIRIM, pengaduan masuk ke Kasi tujuan dengan status <strong>Terkirim</strong>.</li>
                    <li>Kasi tujuan dapat <strong>menerima</strong>, <strong>menutup (selesai)</strong>, <strong>mengembalikan</strong>, atau <strong>meminta bantuan ke Kasi lain</strong>.</li>
                    <li>Hanya Kasi tujuan awal dari Loket yang dapat klik <strong>Tutup (Selesai)</strong>.</li>
                    <li>Semua catatan dari setiap Kasi yang menangani akan terlihat oleh semua Kasi dalam rantai (transparan).</li>
                </ul>
            </div>
        </div>
    </div>
</form>

<script>
    let poinCount = 5;
    let lampiranCount = 4;

    function toggleDetailTanah(val) {
        const detail = document.getElementById('detailTanah');
        const masalah = document.getElementById('masalahSection');
        const kuasa = document.getElementById('kuasaSection');
        const uraianPlaceholder = document.getElementById('uraian');

        if (val === 'permasalahan_tanah') {
            detail.style.display = 'block';
            masalah.style.display = 'block';
            kuasa.style.display = 'block';
            uraianPlaceholder.placeholder = 'Uraian singkat masalah (detail telah diisi di atas)';
        } else {
            detail.style.display = 'none';
            masalah.style.display = 'none';
            kuasa.style.display = 'none';
            document.getElementById('kuasaFields').style.display = 'none';
            uraianPlaceholder.placeholder = 'Uraikan keluhan/saran minimal 20 karakter.';
        }
    }

    function toggleKuasaFields() {
        const bertindak = document.querySelector('input[name="bertindak_untuk"]:checked');
        const fields = document.getElementById('kuasaFields');
        if (bertindak && bertindak.value === 'kuasa') {
            fields.style.display = 'block';
        } else {
            fields.style.display = 'none';
        }
    }

    function toggleAnonim() {
        const isAnonim = document.getElementById('is_anonim').checked;
        const nama = document.getElementById('nama_pengadu');
        const nik = document.getElementById('nik_pengadu');
        const hp = document.getElementById('no_hp_pengadu');
        const email = document.getElementById('email_pengadu');

        if (isAnonim) {
            nama.required = false;
            nik.required = false;
            hp.required = false;
            email.required = false;
            nama.placeholder = 'Dikosongkan (anonim)';
            nik.placeholder = 'Dikosongkan (anonim)';
            hp.placeholder = 'Dikosongkan (anonim)';
            email.placeholder = 'Dikosongkan (anonim)';
        } else {
            nama.required = true;
            nik.required = false;
            hp.required = true;
            nama.placeholder = '';
            nik.placeholder = '';
            hp.placeholder = '';
            email.placeholder = '';
        }
    }

    function tambahPoin() {
        if (poinCount >= 5) return;
        poinCount++;
        const container = document.getElementById('poinContainer');
        const div = document.createElement('div');
        div.className = 'col-md-6 poin-item';
        div.innerHTML = `
            <textarea name="uraian_masalah_${poinCount}" id="uraian_masalah_${poinCount}" rows="2"
                      class="form-control" placeholder="Poin ${poinCount}..."></textarea>
        `;
        container.appendChild(div);
    }

    function tambahLampiran() {
        if (lampiranCount >= 4) return;
        lampiranCount++;
        const container = document.getElementById('lampiranContainer');
        const div = document.createElement('div');
        div.className = 'col-md-6 lampiran-item';
        div.innerHTML = `
            <textarea name="lampiran_${lampiranCount}" id="lampiran_${lampiranCount}" rows="2"
                      class="form-control" placeholder="Lampiran ${lampiranCount}..."></textarea>
        `;
        container.appendChild(div);
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleDetailTanah(document.getElementById('jenis').value);
        toggleKuasaFields();
        toggleAnonim();
    });
</script>
@endsection
