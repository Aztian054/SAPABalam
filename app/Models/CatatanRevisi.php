<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanRevisi extends Model
{
    protected $table = 'catatan_revisi';

    protected $fillable = [
        'tiket_id',
        'pengirim_id',
        'penerima_id',
        'dari_stage',
        'ke_stage',
        'revisi_ke',
        'isi_revisi',
        'sudah_diproses',
        'tanggal_masuk',
        'tanggal_selesai',
    ];

    protected $casts = [
        'sudah_diproses' => 'boolean',
        'revisi_ke' => 'integer',
        'tanggal_masuk' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }
}
