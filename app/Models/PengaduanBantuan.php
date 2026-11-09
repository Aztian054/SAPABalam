<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu rantai bantuan antar Kasi pada satu pengaduan.
 * Kasi asli meminta bantuan ke 1 Kasi lain, secara berurutan, tanpa batas pengulangan.
 */
class PengaduanBantuan extends Model
{
    protected $table = 'pengaduan_bantuans';

    protected $fillable = [
        'pengaduan_id',
        'dari_kasi',
        'ke_kasi',
        'catatan_dari',
        'catatan_ke',
        'status',
        'urutan',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diajukan' => 'Diajukan',
            'ditanggapi' => 'Ditanggapi',
            default => ucfirst((string) $this->status),
        };
    }
}
