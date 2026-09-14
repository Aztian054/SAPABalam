<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidasiSuel extends Model
{
    protected $table = 'validasi_suel';

    protected $fillable = [
        'tiket_id',
        'bidang_id',
        'validator_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'kesesuaian_nama',
        'kesesuaian_luas',
        'cocok_letak',
        'status_validasi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(BidangTanah::class, 'bidang_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
