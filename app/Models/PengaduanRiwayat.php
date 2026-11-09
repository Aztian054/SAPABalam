<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanRiwayat extends Model
{
    protected $fillable = [
        'pengaduan_id',
        'dari_status',
        'ke_status',
        'oleh_id',
        'catatan',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oleh_id');
    }

    public function getKeStatusLabelAttribute(): string
    {
        return Pengaduan::STATUS[$this->ke_status] ?? ucfirst((string) $this->ke_status);
    }
}
