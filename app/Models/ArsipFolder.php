<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArsipFolder extends Model
{
    protected $table = 'arsip_folder';

    protected $fillable = [
        'nama_folder',
        'jenis_dokumen',
        'lokasi_fisik',
        'keterangan',
    ];

    public function arsipTikets(): HasMany
    {
        return $this->hasMany(ArsipTiket::class, 'folder_id');
    }
}
