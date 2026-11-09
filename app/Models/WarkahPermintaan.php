<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Permintaan warkah BT/SU untuk satu tiket.
 *
 * Fase 1 hanya membuat baris permintaan (status `diminta`) dari wizard Tahap 4.
 * Pengelolaan pinjaman fisik (terima → siap → dipinjam → dikembalikan →
 * terlambat) menyusul pada fase warkah.
 */
class WarkahPermintaan extends Model
{
    public const STATUS = [
        'diminta' => 'Diminta',
        'diterima' => 'Diterima',
        'siap' => 'Siap Diambil',
        'dipinjam' => 'Dipinjam',
        'terlambat' => 'Terlambat',
        'dikembalikan' => 'Dikembalikan',
    ];

    public const JENIS = [
        'BT' => 'Buku Tanah',
        'SU' => 'Surat Ukur',
    ];

    protected $fillable = [
        'tiket_id',
        'jenis',
        'status',
        'diminta_oleh_id',
        'tanggal_permintaan',
        'peminjam_id',
        'nama_peminjam',
        'seksi',
        'tanggal_pinjam',
        'batas_kembali',
        'tanggal_kembali',
        'pengembali',
        'kondisi',
        'catatan',
        'is_demo',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'datetime',
        'tanggal_pinjam' => 'date',
        'batas_kembali' => 'date',
        'tanggal_kembali' => 'date',
        'is_demo' => 'boolean',
    ];

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function dimintaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diminta_oleh_id');
    }

    public function peminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    public function riwayats(): HasMany
    {
        return $this->hasMany(WarkahPermintaanRiwayat::class)->orderBy('id');
    }

    /** Masih berada di peminjam (termasuk lewat batas). */
    public function scopeSedangDipinjam(Builder $query): Builder
    {
        return $query->whereIn('status', ['dipinjam', 'terlambat']);
    }

    public function getIsTerlambatAttribute(): bool
    {
        if (! in_array($this->status, ['dipinjam', 'terlambat'], true)) {
            return false;
        }

        return $this->batas_kembali !== null && $this->batas_kembali->lt(now()->startOfDay());
    }

    /** Sisa hari menuju batas kembali; negatif berarti terlambat. */
    public function getSisaHariAttribute(): ?int
    {
        if ($this->batas_kembali === null || ! in_array($this->status, ['dipinjam', 'terlambat'], true)) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->batas_kembali, false);
    }

    public function getSisaWaktuLabelAttribute(): ?string
    {
        $sisa = $this->sisa_hari;

        if ($sisa === null) {
            return null;
        }

        return match (true) {
            $sisa > 1 => "{$sisa} hari",
            $sisa === 1 => 'besok',
            $sisa === 0 => 'hari ini',
            $sisa === -1 => 'terlambat 1 hari',
            default => 'terlambat '.abs($sisa).' hari',
        };
    }

    /** Lama pinjam teks ringkas untuk daftar pengembalian. */
    public function getLamaPinjamAttribute(): ?string
    {
        if ($this->tanggal_pinjam === null || $this->tanggal_kembali === null) {
            return null;
        }

        $hari = (int) $this->tanggal_pinjam->diffInDays($this->tanggal_kembali);

        return $hari > 0 ? "{$hari} hari" : 'kurang dari 1 hari';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'diminta' => 'warning',
            'diterima' => 'info',
            'siap' => 'success',
            'dipinjam' => 'primary',
            'terlambat' => 'danger',
            'dikembalikan' => 'secondary',
            default => 'light',
        };
    }
}
