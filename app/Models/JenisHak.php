<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisHak extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'urutan', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Bidang tanah yang menggunakan jenis hak ini.
     */
    public function bidangTanahs(): HasMany
    {
        return $this->hasMany(BidangTanah::class, 'jenis_hak', 'kode');
    }
}
