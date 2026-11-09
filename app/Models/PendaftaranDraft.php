<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Draf wizard pendaftaran 4 tahap.
 *
 * `data` menyimpan jawaban per tahap:
 *   biodata     → nama, nik, no_hp, kelurahan, kecamatan, alamat, kode_tiket
 *   permohonan  → jenis_permohonan_id, no_hak_sekarang, no_hak_sebelumnya, jumlah_bidang, keterangan, bidang[]
 *   warkah      → none|bt|su|keduanya
 *
 * `no_berkas` disimpan sebagai kolom sendiri (bukan di dalam `data`) agar
 * dapat diubah kapan saja tanpa menyentuh isi tahap lainnya.
 */
class PendaftaranDraft extends Model
{
    protected $fillable = [
        'user_id',
        'kode_tiket',
        'no_berkas',
        'tahap',
        'ploting_status',
        'dikirim_pada',
        'ploting_catatan',
        'ploting_oleh_id',
        'ploting_selesai_at',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'tahap' => 'integer',
        'dikirim_pada' => 'datetime',
        'ploting_selesai_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plotingOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ploting_oleh_id');
    }

    public function isPlotingSelesai(): bool
    {
        return $this->ploting_status === 'sudah';
    }

    public function butuhPloting(): bool
    {
        return $this->ploting_status !== 'sudah';
    }

    /** @return array<string, mixed> */
    public function biodata(): array
    {
        return (array) ($this->data['biodata'] ?? []);
    }

    /** @return array<string, mixed> */
    public function permohonan(): array
    {
        return (array) ($this->data['permohonan'] ?? []);
    }

    /** @return list<array<string, mixed>> */
    public function bidang(): array
    {
        $bidang = $this->permohonan()['bidang'] ?? [];

        return is_array($bidang) ? array_values($bidang) : [];
    }

    public function pilihanWarkah(): string
    {
        return (string) ($this->data['warkah'] ?? 'none');
    }
}
