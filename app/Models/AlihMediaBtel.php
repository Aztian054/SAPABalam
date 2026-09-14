<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlihMediaBtel extends Model
{
    protected $table = 'alih_media_btel';

    protected $fillable = [
        'tiket_id',
        'bidang_id',
        'petugas_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_scan_buku_tanah',
        'status_upload_kkp',
        'status_ttd_elektronik',
        'tanggal_terbit_sertifikat_el',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_terbit_sertifikat_el' => 'date',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangTanah::class, 'bidang_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
