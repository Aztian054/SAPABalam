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
        'status_data_sertipikat_bt',
        'status_data_sertipikat_su',
        'status_sosialisasi',
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
        'status_pengembalian',
        'keterangan_status',
        'catatan',
        // Detail serah terima & pengembalian berkas BT/SU (upgrade 17 Sep 2026).
        'penerima_validator_id',
        'nama_penerima_validator',
        'waktu_serah',
        'status_berkas_bt',
        'status_berkas_su',
        'catatan_kondisi_berkas',
        'petugas_pengembali',
        'waktu_kembali',
        'kondisi_berkas_kembali',
        'catatan_pengembalian',
    ];

    /** Nilai yang sah untuk siklus pengembalian berkas BT/SU. */
    public const STATUS_PENGEMBALIAN = [
        'belum_diserahkan' => 'Belum Diserahkan',
        'dipinjam' => 'Dipinjam (di Validator/Alih Media)',
        'dikembalikan' => 'Berkas Telah Dikembalikan',
    ];

    /** Kondisi fisik berkas BT/SU saat serah terima / pengembalian. */
    public const KONDISI_BERKAS = [
        'lengkap' => 'Lengkap',
        'rusak' => 'Rusak',
        'kurang' => 'Kurang',
    ];

    protected $casts = [
        'tanggal_eksekusi' => 'datetime',
        'tanggal_diserahkan' => 'date',
        'tanggal_kembali' => 'date',
        'waktu_serah' => 'datetime',
        'waktu_kembali' => 'datetime',
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

    /** Validator BT/SU yang menerima serah terima berkas. */
    public function penerimaValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_validator_id');
    }
}
