<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidangTanah extends Model
{
    protected $fillable = [
        'tiket_id',
        'nib',
        'no_sertifikat_lama',
        'no_sertifikat_elektronik',
        'jenis_hak',
        'nama_pemegang_hak',
        'desa_kelurahan',
        'kecamatan',
        'status_plotting',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }
}
