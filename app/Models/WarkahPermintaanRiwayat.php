<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat tindakan warkah (Blueprint V2, modul 06).
 *
 * Menyimpan jejak TERIMA / SIAP / SERAHKAN / KEMBALIKAN / UBAH BATAS
 * beserta batas lama-baru agar perubahan dapat diaudit.
 */
class WarkahPermintaanRiwayat extends Model
{
    protected $fillable = [
        'warkah_permintaan_id',
        'aksi',
        'status_dari',
        'status_ke',
        'batas_lama',
        'batas_baru',
        'oleh_id',
        'catatan',
    ];

    protected $casts = [
        'batas_lama' => 'date',
        'batas_baru' => 'date',
    ];

    public function warkahPermintaan(): BelongsTo
    {
        return $this->belongsTo(WarkahPermintaan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'oleh_id');
    }
}
