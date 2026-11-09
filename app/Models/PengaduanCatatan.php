<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu catatan kronologis pada pengaduan (urutan 1, 2, 3...).
 * Transparan bagi semua Kasi yang menjadi target dalam rantai bantuan.
 */
class PengaduanCatatan extends Model
{
    protected $table = 'pengaduan_catatans';

    protected $fillable = [
        'pengaduan_id',
        'dari_sisi',
        'seksi',
        'jenis',
        'catatan',
        'urutan',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'kirim_awal' => 'Kirim Awal',
            'minta_bantuan' => 'Minta Bantuan',
            'balasan_bantuan' => 'Balasan Bantuan',
            'penyelesaian_akhir' => 'Penyelesaian Akhir',
            default => ucfirst((string) $this->jenis),
        };
    }
}
