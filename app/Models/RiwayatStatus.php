<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat perpindahan tahap/alur berkas.
 *
 * Nama, jabatan, dan role pelaku disalin saat kejadian agar riwayat tetap
 * utuh meskipun akun diubah atau dihapus. Role hanya untuk audit internal;
 * yang ditampilkan ke pengguna adalah nama + jabatan.
 */
class RiwayatStatus extends Model
{
    protected $table = 'riwayat_statuses';

    protected $fillable = [
        'tiket_id',
        'stage_dari',
        'stage_ke',
        'changed_by',
        'nama_pelaku',
        'jabatan_pelaku',
        'role_pelaku',
        'keterangan',
    ];

    protected static function booted(): void
    {
        static::creating(function (RiwayatStatus $riwayat): void {
            if ($riwayat->changed_by === null) {
                return;
            }

            $pelaku = User::find($riwayat->changed_by);

            if ($pelaku === null) {
                return;
            }

            if ($riwayat->nama_pelaku === null) {
                $riwayat->nama_pelaku = $pelaku->name;
            }

            if ($riwayat->jabatan_pelaku === null) {
                $riwayat->jabatan_pelaku = $pelaku->jabatan;
            }

            if ($riwayat->role_pelaku === null) {
                $riwayat->role_pelaku = $pelaku->role;
            }
        });
    }

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getNamaPelakuLabelAttribute(): string
    {
        return $this->nama_pelaku ?? $this->user?->name ?? 'Sistem';
    }

    public function getJabatanPelakuLabelAttribute(): string
    {
        return $this->jabatan_pelaku
            ?? $this->user?->jabatan
            ?? $this->user?->role_label
            ?? '';
    }

    /** Format tampilan: "Nama (Jabatan)". */
    public function getPelakuLabelAttribute(): string
    {
        $jabatan = $this->jabatan_pelaku_label;

        return $jabatan !== ''
            ? $this->nama_pelaku_label.' ('.$jabatan.')'
            : $this->nama_pelaku_label;
    }
}
