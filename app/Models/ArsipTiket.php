<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArsipTiket extends Model
{
    protected $table = 'arsip_tiket';

    protected $fillable = [
        'tiket_id',
        'folder_id',
        'nama_arsip',
        'tipe_dokumen',
        'tanggal_arsip',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_arsip' => 'date',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ArsipFolder::class, 'folder_id');
    }
}
