<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Role target V2 (Blueprint). */
    public const ROLES = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'loket_pendaftaran' => 'Loket Pendaftaran',
        'loket_ploting' => 'Loket Ploting',
        'petugas_warkah' => 'Petugas Warkah',
        'staf_seksi' => 'Staf Seksi',
        'kasi_seksi' => 'Kasi Seksi',
        'kasi_unit' => 'Kasi Unit',
        'kasubbag_tu' => 'Kasubbag TU',
        'loket_penyerahan' => 'Loket Penyerahan',
        'loket_pengaduan' => 'Loket Pengaduan',
        'kakan' => 'Kakan',
    ];

    /**
     * Urutan hierarki Role untuk tampilan Manajemen Akun.
     *
     * Super Admin sengaja tidak dimasukkan karena akun bayangan tidak pernah
     * ditampilkan. Role di luar daftar ini akan berada paling bawah.
     */
    public const ROLE_HIERARCHY = [
        'admin' => 1,
        'kakan' => 2,
        'loket_pendaftaran' => 3,
        'loket_pengaduan' => 4,
        'loket_ploting' => 5,
        'petugas_warkah' => 6,
        'loket_penyerahan' => 7,
        'kasubbag_tu' => 8,
        'kasi_seksi' => 9,
        'kasi_unit' => 10,
        'staf_seksi' => 11,
    ];

    /** Pilihan seksi untuk role yang terikat unit. */
    public const SEKSI = [
        '1' => 'Seksi 1',
        '2' => 'Seksi 2',
        '3' => 'Seksi 3',
        '4' => 'Seksi 4',
        '5' => 'Seksi 5',
        'TU' => 'Tata Usaha',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'seksi',
        'jabatan',
        'is_active',
        'is_demo',
        // Kolom monitoring rekap (migrasi 2026_09_13): NIP & No. HP petugas.
        'nip',
        'no_hp',
        'kode_akun',
        'foto_profil',
        // Pandangan admin penuh (migrasi 2026_09_15): password plaintext untuk pengelolaan akun.
        'password_text',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        // Salinan plaintext hanya untuk halaman admin (Manajemen Akun);
        // tidak boleh ikut terserialisasi ke JSON/API/frontend mana pun.
        'password_text',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /** Kakan — monitoring seluruh alur (read-only). */
    public function isKakan(): bool
    {
        return in_array($this->role, ['kakan', 'admin', 'super_admin'], true);
    }

    public function isLoketPendaftaran(): bool
    {
        return $this->role === 'loket_pendaftaran';
    }

    public function isLoketPloting(): bool
    {
        return $this->role === 'loket_ploting';
    }

    public function isPetugasWarkah(): bool
    {
        return $this->role === 'petugas_warkah';
    }

    public function isStafSeksi(): bool
    {
        return $this->role === 'staf_seksi';
    }

    public function isKasiSeksi(): bool
    {
        return $this->role === 'kasi_seksi';
    }

    public function isKasiUnit(): bool
    {
        return in_array($this->role, ['kasi_unit', 'kasubbag_tu'], true);
    }

    /** Kasi Seksi 1 dan 2 hanya yang boleh melihat daftar permohonan/tiket. */
    public function canViewPermohonan(): bool
    {
        return $this->isKasiSeksi() && in_array($this->seksi, ['1', '2'], true);
    }

    public function isLoketPenyerahan(): bool
    {
        return $this->role === 'loket_penyerahan';
    }

    public function isLoketPengaduan(): bool
    {
        return $this->role === 'loket_pengaduan';
    }

    /** Kasi unit, Kasubbag TU, dan Kasi Seksi 1/2 menangani pengaduan unitnya. */
    public function isPenanganPengaduan(): bool
    {
        return $this->isKasiUnit() || $this->isKasiSeksi();
    }

    /** Kode unit tujuan pengaduan: Seksi 1–5 atau TU. */
    public function seksiPengaduan(): ?string
    {
        if ($this->role === 'kasubbag_tu') {
            return 'TU';
        }

        return $this->seksi;
    }

    /** Role V2 yang terikat seksi (1–5/TU). */
    public function requiresSeksi(): bool
    {
        return in_array($this->role, ['staf_seksi', 'kasi_seksi', 'kasi_unit'], true);
    }

    /** Role yang boleh dipilih saat membuat akun baru (Super Admin & Admin dikunci — hanya satu akun). */
    public static function rolesNonAdmin(): array
    {
        return array_filter(
            self::ROLES,
            fn (string $key): bool => ! in_array($key, ['admin', 'super_admin'], true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /** Role yang dapat difilter di Manajemen Akun, sudah mengikuti hierarki tabel. */
    public static function rolesForAccountFilter(): array
    {
        $roles = self::ROLES;
        unset($roles['super_admin']);

        $terurut = [];
        foreach (array_keys(self::ROLE_HIERARCHY) as $role) {
            if (isset($roles[$role])) {
                $terurut[$role] = $roles[$role];
            }
        }

        return $terurut;
    }

    /**
     * Urutan tabel Manajemen Akun:
     * hierarki role → nomor seksi → nama → username.
     */
    public function scopeUrutHierarki(Builder $query): Builder
    {
        $roleCases = [];
        $roleBindings = [];
        foreach (self::ROLE_HIERARCHY as $role => $urutan) {
            $roleCases[] = 'WHEN ? THEN '.$urutan;
            $roleBindings[] = $role;
        }

        $seksiOrder = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, 'TU' => 99];
        $seksiCases = [];
        $seksiBindings = [];
        foreach ($seksiOrder as $seksi => $urutan) {
            $seksiCases[] = 'WHEN ? THEN '.$urutan;
            $seksiBindings[] = $seksi;
        }

        return $query
            ->orderByRaw('CASE role '.implode(' ', $roleCases).' ELSE 99 END', $roleBindings)
            ->orderByRaw('CASE seksi '.implode(' ', $seksiCases).' ELSE 99 END', $seksiBindings)
            ->orderBy('name')
            ->orderBy('username');
    }

    /** Label role yang ramah tampilan, contoh: "Kasi Seksi 1" / "Staf Seksi 2". */
    public function getRoleLabelAttribute(): string
    {
        $label = self::ROLES[$this->role] ?? ucfirst((string) $this->role);

        if (
            in_array($this->role, ['kasi_seksi', 'kasi_unit', 'staf_seksi'], true)
            && $this->seksi !== null
            && $this->seksi !== ''
        ) {
            return $label.' '.$this->seksi;
        }

        return $label;
    }

    /** Jabatan manual; fallback ke label role bila belum diisi. */
    public function getJabatanLabelAttribute(): string
    {
        return $this->jabatan !== null && $this->jabatan !== ''
            ? $this->jabatan
            : $this->role_label;
    }

    /** Label seksi, contoh: "Seksi 2" / "Tata Usaha". */
    public function getSeksiLabelAttribute(): string
    {
        if ($this->seksi === null || $this->seksi === '') {
            return '-';
        }

        return self::SEKSI[$this->seksi] ?? (string) $this->seksi;
    }

    /** URL lengkap foto profil, atau null jika belum ada. */
    public function getFotoProfilUrlAttribute(): ?string
    {
        if ($this->foto_profil === null || $this->foto_profil === '') {
            return null;
        }

        return asset('storage/'.$this->foto_profil);
    }

    /** Generate kode akun acak, non-sequential, dan unik. Format: AK-XXXXXXX. */
    public static function generateKodeAkun(): string
    {
        do {
            $kode = 'AK-'.strtoupper(Str::random(7));
        } while (self::where('kode_akun', $kode)->exists());

        return $kode;
    }

    /** Hapus file foto profil lama dari storage jika ada. */
    public function hapusFotoLama(): void
    {
        if ($this->foto_profil !== null && $this->foto_profil !== '') {
            Storage::disk('public')->delete($this->foto_profil);
        }
    }

    /** Notifikasi in-app (Blueprint V2, modul 16). */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /** Preferensi tipe notifikasi milik pengguna (Blueprint V2, modul 16). */
    public function notifikasiPreferensis(): HasMany
    {
        return $this->hasMany(NotifikasiPreferensi::class);
    }
}
