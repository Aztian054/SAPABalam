<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /** Daftar 9 peran pada V2.0 */
    public const ROLES = [
        'admin' => 'Admin',
        'pemimpin' => 'Pemimpin',
        'loket' => 'Loket Penerimaan',
        'verifikator' => 'Verifikator',
        'warkah' => 'Warkah',
        'validator_btel' => 'Validator BT',
        'validator_suel' => 'Validator SU',
        'alih_media_btel' => 'Alih Media BT',
        'alih_media_suel' => 'Alih Media SU',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        // Kolom monitoring rekap (migrasi 2026_09_13): NIP & No. HP petugas.
        'nip',
        'no_hp',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPemimpin(): bool
    {
        return $this->role === 'pemimpin' || $this->role === 'admin';
    }

    public function isLoket(): bool
    {
        return $this->role === 'loket' || $this->role === 'admin';
    }

    public function isVerifikator(): bool
    {
        return $this->role === 'verifikator' || $this->role === 'admin';
    }

    public function isWarkah(): bool
    {
        return $this->role === 'warkah' || $this->role === 'admin';
    }

    public function isValidatorBt(): bool
    {
        return $this->role === 'validator_btel' || $this->role === 'admin';
    }

    public function isValidatorSu(): bool
    {
        return $this->role === 'validator_suel' || $this->role === 'admin';
    }

    public function isAlihMediaBt(): bool
    {
        return $this->role === 'alih_media_btel' || $this->role === 'admin';
    }

    public function isAlihMediaSu(): bool
    {
        return $this->role === 'alih_media_suel' || $this->role === 'admin';
    }

    /** Role yang bekerja pada tahap validator (BT & SU). */
    public function isValidator(): bool
    {
        return in_array($this->role, ['validator_btel', 'validator_suel', 'admin']);
    }

    /** Role yang bekerja pada tahap alih media (BT & SU). */
    public function isAlihMedia(): bool
    {
        return in_array($this->role, ['alih_media_btel', 'alih_media_suel', 'admin']);
    }

    /** Role petugas pelaksana tahap (selain Admin/Pemimpin). */
    public function isPetugas(): bool
    {
        return in_array($this->role, [
            'loket', 'verifikator', 'warkah',
            'validator_btel', 'validator_suel',
            'alih_media_btel', 'alih_media_suel',
        ]);
    }

    /** Jabatan yang boleh dipilih saat membuat akun baru (Admin dikunci — hanya satu akun). */
    public static function rolesNonAdmin(): array
    {
        return array_filter(self::ROLES, fn (string $key): bool => $key !== 'admin', ARRAY_FILTER_USE_KEY);
    }

    /** Label role yang ramah tampilan, contoh: "Validator BT". */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }
}
