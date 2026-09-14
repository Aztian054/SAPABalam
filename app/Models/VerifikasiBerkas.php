<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiBerkas extends Model
{
    protected $table = 'verifikasi_berkas';

    protected $fillable = [
        'tiket_id',
        'petugas_id',
        'status_pembetulan',
        'iterasi',
        'tanggal_diterima',
        'tanggal_selesai',
        'status',
        'catatan',
        'dokumen_kurang',
    ];

    protected $casts = [
        'tanggal_diterima' => 'date',
        'tanggal_selesai' => 'date',
        'iterasi' => 'integer',
        'dokumen_kurang' => 'array',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
