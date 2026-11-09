@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <h4 class="fw-bold text-dark mb-3"><i class="bi bi-person-circle text-primary me-2"></i>Profil Saya</h4>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-x-circle-fill me-1"></i>
            <strong>Periksa kembali isian berikut:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- Informasi tetap: role tidak dapat diubah sendiri --}}
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-body text-center position-relative">
                    {{-- Foto Profil sebagai area unggah --}}
                    <div class="avatar-upload-wrapper position-relative d-inline-block mb-3" id="avatarWrapper">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px;">
                            @if ($user->foto_profil_url)
                                <img id="avatarImg" src="{{ $user->foto_profil_url }}" alt="Foto Profil" class="rounded-circle w-100 h-100 object-fit-cover">
                            @else
                                <i id="avatarIcon" class="bi bi-person-fill fs-1"></i>
                            @endif
                        </div>
                        <div class="avatar-overlay position-absolute top-0 start-0 w-100 h-100 rounded-circle d-flex align-items-center justify-content-center" id="avatarOverlay" style="display:none;">
                            <div class="text-white text-center">
                                <i class="bi bi-camera-fill fs-4 mb-1"></i><br>
                                <small class="fw-bold">Ubah Foto</small>
                            </div>
                        </div>
                        <input type="file" id="fotoInput" accept="image/jpeg,image/png,image/webp" class="d-none" aria-label="Pilih foto profil">
                    </div>

                    <h6 class="fw-bold mb-1">{{ $user->name }}</h6>
                    <div class="text-muted small mb-2">{{ $user->username }}</div>
                    <span class="badge bg-primary text-uppercase">{{ $user->role_label }}</span>
                    @if ($user->seksi)
                        <div class="mt-2"><small class="text-muted">{{ $user->seksi_label }}</small></div>
                    @endif

                    <hr>

                    <div class="text-start small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Role</span>
                            <span class="fw-semibold text-dark">{{ $user->role_label }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Seksi</span>
                            <span class="fw-semibold text-dark">{{ $user->seksi_label }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Email</span>
                            <span class="fw-semibold text-dark text-break ms-2 text-end">{{ $user->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="fw-semibold {{ $user->is_active ? 'text-success' : 'text-danger' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-start mt-3 mb-0">
                        <i class="bi bi-lock-fill text-danger me-1"></i>
                        Role bersifat tetap dan tidak dapat diubah dari halaman Profil. Hubungi Admin bila role perlu disesuaikan.
                    </div>

                    {{-- Foto info (read-only) --}}
                    <div class="mt-3">
                        @if ($user->foto_profil_url)
                            <div class="form-text small text-muted">Foto profil saat ini akan diganti saat mengunggah foto baru.</div>
                        @else
                            <div class="form-text small text-muted">Belum ada foto profil. Klik area foto untuk mengunggah.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Biodata yang dapat diedit mandiri --}}
            <div class="card card-custom mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Biodata</h6>
                    <button type="button" id="editProfilBtn" class="btn btn-sm btn-gold" aria-label="Edit Profil">
                        <i class="bi bi-pencil me-1"></i> Edit Profil
                    </button>
                </div>
                <div class="card-body">
                    <form id="formBiodata">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input name="name" id="inputName" class="form-control @error('name') is-invalid @enderror" maxlength="100" value="{{ old('name', $user->name) }}" disabled required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label">NIP</label>
                                <input name="nip" id="inputNip" class="form-control @error('nip') is-invalid @enderror" maxlength="30" value="{{ old('nip', $user->nip) }}" disabled placeholder="Nomor Induk Pegawai">
                                @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. HP</label>
                                <input name="no_hp" id="inputNoHp" class="form-control @error('no_hp') is-invalid @enderror" maxlength="20" value="{{ old('no_hp', $user->no_hp) }}" disabled required placeholder="08xxxxxxxxxx">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input name="jabatan" id="inputJabatan" class="form-control @error('jabatan') is-invalid @enderror" maxlength="100" value="{{ old('jabatan', $user->jabatan) }}" disabled required placeholder="Contoh: Validator">
                            <div class="form-text">Jabatan bebas, bukan penentu hak akses. Dapat diubah sendiri di sini atau oleh Admin.</div>
                            @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div id="biodataActionButtons" class="d-none mt-3 d-flex gap-2">
                            <button type="button" id="btnBatalBiodata" class="btn btn-outline-secondary btn-sm" aria-label="Batal">
                                <i class="bi bi-x me-1"></i> Batal
                            </button>
                            <button type="submit" id="btnSimpanBiodata" class="btn btn-gold btn-sm" aria-label="Simpan biodata">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Keamanan akun: username & password lewat modal --}}
            <div class="card card-custom">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shield-lock text-primary me-2"></i>Keamanan Akun</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Username</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="inputUsernameDisplay" class="form-control" value="{{ $user->username }}" readonly>
                            <button type="button" id="btnUbahUsername" class="btn btn-outline-primary btn-sm" aria-label="Ubah Username">
                                <i class="bi bi-pencil me-1"></i> Ubah Username
                            </button>
                        </div>
                        <div class="form-text">Username dipakai untuk login. Tidak dapat berupa email.</div>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label fw-semibold mb-0">Password</label>
                        <button type="button" id="btnUbahPassword" class="btn btn-outline-primary btn-sm ms-auto" aria-label="Ubah Password">
                            <i class="bi bi-key me-1"></i> Ubah Password
                        </button>
                    </div>
                    <div class="form-text">Minimal 6 karakter. Password lama tidak dapat dilihat kembali.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]').content;

        // ==================== FOTO PROFIL (Drag & Drop + Klik) ====================
        const avatarWrapper = document.getElementById('avatarWrapper');
        const avatarOverlay = document.getElementById('avatarOverlay');
        const avatarImg = document.getElementById('avatarImg');
        const avatarIcon = document.getElementById('avatarIcon');
        const fotoInput = document.getElementById('fotoInput');

        // Show overlay on hover
        avatarWrapper.addEventListener('mouseenter', () => {
            if (avatarOverlay) avatarOverlay.style.display = 'flex';
        });
        avatarWrapper.addEventListener('mouseleave', () => {
            if (avatarOverlay) avatarOverlay.style.display = 'none';
        });

        // Click to open file dialog
        avatarWrapper.addEventListener('click', (e) => {
            if (e.target !== fotoInput) {
                fotoInput.click();
            }
        });

        // Keyboard access: Enter/Space
        avatarWrapper.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fotoInput.click();
            }
        });

        // Handle file selection
        fotoInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                uploadFoto(this.files[0]);
            }
        });

        // Drag and drop on avatar area
        avatarWrapper.addEventListener('dragover', (e) => {
            e.preventDefault();
            avatarWrapper.classList.add('drag-active');
        });

        avatarWrapper.addEventListener('dragleave', () => {
            avatarWrapper.classList.remove('drag-active');
        });

        avatarWrapper.addEventListener('drop', (e) => {
            e.preventDefault();
            avatarWrapper.classList.remove('drag-active');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                uploadFoto(e.dataTransfer.files[0]);
            }
        });

        // Prevent browser from opening file when dropped outside avatar area
        window.addEventListener('dragover', (e) => e.preventDefault());
        window.addEventListener('drop', (e) => e.preventDefault());

        async function uploadFoto(file) {
            // Client-side validation
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const maxSize = 2 * 1024 * 1024; // 2 MB

            if (!allowedTypes.includes(file.type)) {
                tampilkanNotifikasi('Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.', 'danger');
                return;
            }

            if (file.size > maxSize) {
                tampilkanNotifikasi('Ukuran foto maksimal 2 MB.', 'danger');
                return;
            }

            // Show preview immediately
            const url = URL.createObjectURL(file);
            if (avatarImg) {
                avatarImg.src = url;
            } else if (avatarIcon) {
                avatarIcon.style.display = 'none';
                const img = document.createElement('img');
                img.id = 'avatarImg';
                img.src = url;
                img.className = 'rounded-circle w-100 h-100 object-fit-cover';
                avatarWrapper.querySelector('.d-inline-flex').appendChild(img);
            }

            const formData = new FormData();
            formData.append('foto', file);

            try {
                const response = await fetch("{{ route('profil.foto.update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.message) {
                    tampilkanNotifikasi(data.message, 'success');
                    // Update avatar di dropdown kanan atas jika ada
                    updateAvatarDropdown(data.user?.foto_profil_url);
                } else {
                    // Kembalikan foto lama
                    tampilkanNotifikasi(data.message || 'Gagal mengunggah foto.', 'danger');
                    fotoInput.value = '';
                }
            } catch (error) {
                tampilkanNotifikasi('Terjadi kesalahan saat mengunggah foto.', 'danger');
                fotoInput.value = '';
            }
        }

        function updateAvatarDropdown(url) {
            // Update avatar image in navbar dropdown if exists
            const dropdownAvatar = document.querySelector('.user-menu-btn img, .navbar img.avatar-img');
            if (dropdownAvatar && url) {
                dropdownAvatar.src = url;
            }
        }

        // ==================== BIODATA TOGGLE ====================
        const editProfilBtn = document.getElementById('editProfilBtn');
        const btnSimpanBiodata = document.getElementById('btnSimpanBiodata');
        const btnBatalBiodata = document.getElementById('btnBatalBiodata');
        const biodataActionButtons = document.getElementById('biodataActionButtons');
        const formBiodata = document.getElementById('formBiodata');

        const inputName = document.getElementById('inputName');
        const inputNip = document.getElementById('inputNip');
        const inputNoHp = document.getElementById('inputNoHp');
        const inputJabatan = document.getElementById('inputJabatan');

        let biodataSnapshot = {};

        function simpanSnapshot() {
            biodataSnapshot = {
                name: inputName.value,
                nip: inputNip.value,
                no_hp: inputNoHp.value,
                jabatan: inputJabatan.value,
            };
        }

        function aktifkanField() {
            inputName.disabled = false;
            inputNip.disabled = false;
            inputNoHp.disabled = false;
            inputJabatan.disabled = false;
            inputName.classList.remove('bg-light');
            inputNip.classList.remove('bg-light');
            inputNoHp.classList.remove('bg-light');
            inputJabatan.classList.remove('bg-light');
            editProfilBtn.classList.add('d-none');
            biodataActionButtons.classList.remove('d-none');
            inputName.focus();
        }

        function nonaktifkanField() {
            inputName.disabled = true;
            inputNip.disabled = true;
            inputNoHp.disabled = true;
            inputJabatan.disabled = true;
            inputName.classList.add('bg-light');
            inputNip.classList.add('bg-light');
            inputNoHp.classList.add('bg-light');
            inputJabatan.classList.add('bg-light');
            editProfilBtn.classList.remove('d-none');
            biodataActionButtons.classList.add('d-none');
        }

        function restoreSnapshot() {
            inputName.value = biodataSnapshot.name;
            inputNip.value = biodataSnapshot.nip;
            inputNoHp.value = biodataSnapshot.no_hp;
            inputJabatan.value = biodataSnapshot.jabatan;
        }

        // Default: semua field disabled, tampil seperti teks biasa
        nonaktifkanField();
        inputName.classList.add('bg-light');
        inputNip.classList.add('bg-light');
        inputNoHp.classList.add('bg-light');
        inputJabatan.classList.add('bg-light');

        editProfilBtn.addEventListener('click', () => {
            simpanSnapshot();
            aktifkanField();
        });

        btnBatalBiodata.addEventListener('click', (e) => {
            e.preventDefault();
            restoreSnapshot();
            nonaktifkanField();
            // Hapus error styling
            [inputName, inputNip, inputNoHp, inputJabatan].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });
        });

        formBiodata.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Hapus error lama
            [inputName, inputNip, inputNoHp, inputJabatan].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });

            btnSimpanBiodata.disabled = true;
            btnSimpanBiodata.innerHTML = '<i class="bi bi-bootstrap-reboot me-1"></i> Memuat...';

            const formData = new FormData(formBiodata);

            try {
                const response = await fetch("{{ route('profil.update') }}", {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    tampilkanNotifikasi(data.message, 'success');
                    nonaktifkanField();
                    // Perbarui nama di kartu profil dan header kanan atas
                    const namaBaru = data.user?.name;
                    if (namaBaru) {
                        // Update nama di kartu profil (kiri)
                        const namaDisplay = document.querySelector('.card-body.text-center h6.fw-bold');
                        if (namaDisplay) namaDisplay.textContent = namaBaru;
                        // Update nama di header kanan atas
                        const namaHeader = document.querySelector('.text-end .fw-bold.text-dark');
                        if (namaHeader) namaHeader.textContent = namaBaru;
                    }
                } else if (response.status === 422) {
                    // Tampilkan error di bawah field yang bersangkutan
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = formBiodata.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                input.parentElement.appendChild(errorDiv);
                            }
                        });
                    }
                    tampilkanNotifikasi('Periksa kembali isian yang belum benar.', 'danger');
                } else {
                    tampilkanNotifikasi(data.message || 'Gagal menyimpan biodata.', 'danger');
                }
            } catch (error) {
                tampilkanNotifikasi('Terjadi kesalahan saat menyimpan biodata.', 'danger');
            } finally {
                btnSimpanBiodata.disabled = false;
                btnSimpanBiodata.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan';
            }
        });

        // ==================== MODAL UBAH USERNAME ====================
        const btnUbahUsername = document.getElementById('btnUbahUsername');
        const modalUsernameEl = document.getElementById('modalUbahUsername');
        const modalUsername = new bootstrap.Modal(modalUsernameEl);
        const inputUsernameBaru = document.getElementById('inputUsernameBaru');
        const inputCurrentPasswordUsername = document.getElementById('inputCurrentPasswordUsername');
        const formModalUsername = document.getElementById('formModalUsername');
        const btnSimpanUsername = document.getElementById('btnSimpanUsername');

        btnUbahUsername.addEventListener('click', () => {
            inputUsernameBaru.value = '';
            inputCurrentPasswordUsername.value = '';
            inputUsernameBaru.focus();
            // Hapus error
            [inputUsernameBaru, inputCurrentPasswordUsername].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });
            modalUsername.show();
        });

        formModalUsername.addEventListener('submit', async (e) => {
            e.preventDefault();

            [inputUsernameBaru, inputCurrentPasswordUsername].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });

            btnSimpanUsername.disabled = true;
            btnSimpanUsername.innerHTML = 'Memuat...';

            const formData = new FormData();
            formData.append('username', inputUsernameBaru.value);
            formData.append('current_password', inputCurrentPasswordUsername.value);

            try {
                const response = await fetch("{{ route('profil.username.update') }}", {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    tampilkanNotifikasi(data.message, 'success');
                    modalUsername.hide();
                    // Perbarui tampilan username
                    const usernameBaru = data.user?.username;
                    if (usernameBaru) {
                        document.getElementById('inputUsernameDisplay').value = usernameBaru;
                        const usernameSmall = document.querySelector('.text-muted small');
                        if (usernameSmall) usernameSmall.textContent = usernameBaru + ' ({{ $user->email }})';
                    }
                } else if (response.status === 422) {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = formModalUsername.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                input.parentElement.appendChild(errorDiv);
                            }
                        });
                    }
                } else {
                    tampilkanNotifikasi(data.message || 'Gagal mengubah username.', 'danger');
                }
            } catch (error) {
                tampilkanNotifikasi('Terjadi kesalahan saat mengubah username.', 'danger');
            } finally {
                btnSimpanUsername.disabled = false;
                btnSimpanUsername.innerHTML = 'Simpan username';
            }
        });

        // Reset form saat modal ditutup
        modalUsernameEl.addEventListener('hidden.bs.modal', () => {
            formModalUsername.reset();
            [inputUsernameBaru, inputCurrentPasswordUsername].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });
            btnUbahUsername.focus();
        });

        // ==================== MODAL UBAH PASSWORD ====================
        const btnUbahPassword = document.getElementById('btnUbahPassword');
        const modalPasswordEl = document.getElementById('modalUbahPassword');
        const modalPassword = new bootstrap.Modal(modalPasswordEl);
        const inputCurrentPassword = document.getElementById('inputCurrentPassword');
        const inputPasswordBaru = document.getElementById('inputPasswordBaru');
        const inputPasswordKonfirmasi = document.getElementById('inputPasswordKonfirmasi');
        const formModalPassword = document.getElementById('formModalPassword');
        const btnSimpanPassword = document.getElementById('btnSimpanPassword');

        btnUbahPassword.addEventListener('click', () => {
            inputCurrentPassword.value = '';
            inputPasswordBaru.value = '';
            inputPasswordKonfirmasi.value = '';
            inputCurrentPassword.focus();
            // Hapus error
            [inputCurrentPassword, inputPasswordBaru, inputPasswordKonfirmasi].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });
            modalPassword.show();
        });

        // Toggle password visibility
        document.querySelectorAll('[data-toggle-password]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                var input = document.getElementById(tombol.getAttribute('data-toggle-password'));
                var ikon = tombol.querySelector('i');
                if (!input) return;
                var tampil = input.getAttribute('type') === 'password';
                input.setAttribute('type', tampil ? 'text' : 'password');
                ikon.className = tampil ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });

        formModalPassword.addEventListener('submit', async (e) => {
            e.preventDefault();

            [inputCurrentPassword, inputPasswordBaru, inputPasswordKonfirmasi].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });

            btnSimpanPassword.disabled = true;
            btnSimpanPassword.innerHTML = 'Memuat...';

            const formData = new FormData();
            formData.append('current_password', inputCurrentPassword.value);
            formData.append('password', inputPasswordBaru.value);
            formData.append('password_confirmation', inputPasswordKonfirmasi.value);

            try {
                const response = await fetch("{{ route('profil.password.update') }}", {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok) {
                    tampilkanNotifikasi(data.message, 'success');
                    modalPassword.hide();
                } else if (response.status === 422) {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = formModalPassword.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                input.parentElement.appendChild(errorDiv);
                            }
                        });
                    }
                } else {
                    tampilkanNotifikasi(data.message || 'Gagal mengubah password.', 'danger');
                }
            } catch (error) {
                tampilkanNotifikasi('Terjadi kesalahan saat mengubah password.', 'danger');
            } finally {
                btnSimpanPassword.disabled = false;
                btnSimpanPassword.innerHTML = 'Simpan password';
            }
        });

        // Reset form saat modal ditutup
        modalPasswordEl.addEventListener('hidden.bs.modal', () => {
            formModalPassword.reset();
            [inputCurrentPassword, inputPasswordBaru, inputPasswordKonfirmasi].forEach(input => {
                input.classList.remove('is-invalid');
                const errorDiv = input.parentElement.querySelector('.invalid-feedback');
                if (errorDiv) errorDiv.remove();
            });
            btnUbahPassword.focus();
        });

        // ==================== NOTIFIKASI ====================
        function tampilkanNotifikasi(pesan, tipe = 'success') {
            // Hapus notifikasi lama jika ada
            const lama = document.querySelector('.toast-notification');
            if (lama) lama.remove();

            const toast = document.createElement('div');
            toast.className = `toast-notification position-fixed bottom-0 end-0 p-3`;
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <div class="toast bg-${tipe === 'success' ? 'success' : 'danger'} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body">
                        <i class="bi bi-${tipe === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        ${pesan}
                    </div>
                </div>
            `;
            document.body.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();

            setTimeout(() => toast.remove(), 3500);
        }
    });
</script>

<!-- Modal Ubah Username -->
<div class="modal fade" id="modalUbahUsername" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formModalUsername">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalUbahUsernameLabel">Ubah Username</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username Baru <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="inputUsernameBaru" class="form-control @error('username') is-invalid @enderror" maxlength="50" minlength="4" placeholder="Masukkan username baru" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Minimal 4 karakter. Tidak boleh berupa email.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="inputCurrentPasswordUsername" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="inputCurrentPasswordUsername" aria-label="Lihat password saat ini">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanUsername" class="btn btn-gold btn-sm">Simpan username</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="modalUbahPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formModalPassword">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalUbahPasswordLabel">Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="inputCurrentPassword" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="inputCurrentPassword" aria-label="Lihat password saat ini">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="inputPasswordBaru" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="inputPasswordBaru" aria-label="Lihat password baru">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Minimal 6 karakter.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="inputPasswordKonfirmasi" class="form-control @error('password_confirmation') is-invalid @enderror" autocomplete="new-password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="inputPasswordKonfirmasi" aria-label="Lihat konfirmasi password baru">
                                <i class="bi bi-eye"></i>
                            </button>
                            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSimpanPassword" class="btn btn-gold btn-sm">Simpan password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .avatar-upload-wrapper {
        cursor: pointer;
    }
    .avatar-upload-wrapper.drag-active {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }
    .avatar-overlay {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(2px);
        cursor: pointer;
    }
    .form-control:disabled {
        background-color: #f8f9fa;
        border: none;
        color: #495057;
    }
    .form-control:disabled:focus {
        box-shadow: none;
    }
    .drag-active {
        border-color: #0d6efd !important;
    }
</style>
