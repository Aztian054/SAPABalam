<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiketPenugasan extends Model
{
    protected $table = 'tiket_penugasan';

    protected $fillable = [
        'tiket_id',
        'user_id',
        'stage',
        'status',
        'tanggal_add',
        'tanggal_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal_add' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Label tahap dari konstanta Tiket::STAGES. */
    public function getStageLabelAttribute(): string
    {
        return Tiket::STAGES[$this->stage] ?? ucfirst($this->stage);
    }
}
