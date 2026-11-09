<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris log audit/aktivitas.
 *
 * Nama, role, & jabatan pelaku disimpan sebagai salinan agar riwayat tetap
 * utuh bila akun diubah atau dihapus.
 */
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'nama_pelaku',
        'role_pelaku',
        'jabatan_pelaku',
        'aksi',
        'objek',
        'detail',
        'ip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Baris yang boleh tampil di halaman Log.
     *
     * Aktivitas Super Admin tidak pernah ditampilkan, termasuk baris lama
     * yang terlanjur tercatat sebelum aturan ini berlaku.
     */
    public function scopeTanpaSuperAdmin(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('role_pelaku')
                ->orWhere('role_pelaku', '!=', 'super_admin');
        });
    }

    /** Label aksi yang ramah dibaca (fallback: aksi apa adanya). */
    public function getAksiLabelAttribute(): string
    {
        return ucwords(strtolower($this->aksi));
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
            ? $this->nama_pelaku.' ('.$jabatan.')'
            : $this->nama_pelaku;
    }
}
