<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembarKerjaWarkah extends Model
{
    protected $table = 'lembar_kerja_warkahs';

    protected $fillable = [
        'tiket_id',
        'petugas_id',
        'tanggal_eksekusi',
        'status_berkas_dataset',
        'status_data_sertipikat_bt',
        'status_data_sertipikat_su',
        'status_sosialisasi',
        'status_public_response',
        // Kolom keluaran sesuai "ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL)".
        'status_sertipikat',
        'status_dokumen_bt',
        'status_dokumen_su',
        'tanggal_diserahkan',
        'tanggal_kembali',
        'jumlah_berkas',
        'jumlah_halaman',
        'gabungan',
        'jumlah_berkas_dikembalikan',
        'pengembalian_sementara',
        'keterangan_status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_eksekusi' => 'datetime',
        'tanggal_diserahkan' => 'date',
        'tanggal_kembali' => 'date',
        'gabungan' => 'boolean',
        'pengembalian_sementara' => 'boolean',
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
