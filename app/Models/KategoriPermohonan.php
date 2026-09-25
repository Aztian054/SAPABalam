<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPermohonan extends Model
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
     * Jenis permohonan yang berada di kategori ini.
     */
    public function jenisPermohonans(): HasMany
    {
        return $this->hasMany(JenisPermohonan::class, 'kategori', 'kode');
    }
}
