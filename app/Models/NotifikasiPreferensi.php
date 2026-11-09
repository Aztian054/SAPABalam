<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Preferensi satu tipe notifikasi untuk satu pengguna.
 *
 * Baris hanya ada bila pengguna pernah menyimpan preferensi; tipe tanpa
 * baris dianggap aktif agar akun lama tetap menerima notifikasi.
 */
class NotifikasiPreferensi extends Model
{
    protected $fillable = [
        'user_id',
        'tipe',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
