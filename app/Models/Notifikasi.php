<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notifikasi in-app (Blueprint V2, modul 16).
 *
 * Satu baris = satu penerima. Pesan tidak boleh memuat NIK, alamat, atau
 * isi berkas; cukup nomor tiket dan ringkasan.
 */
class Notifikasi extends Model
{
    protected $fillable = [
        'user_id',
        'tipe',
        'judul',
        'pesan',
        'url',
        'objek_tipe',
        'objek_id',
        'dibaca_pada',
    ];

    protected $casts = [
        'dibaca_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBelumDibaca(Builder $query): Builder
    {
        return $query->whereNull('dibaca_pada');
    }

    public function scopeUntukUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function getSudahDibacaAttribute(): bool
    {
        return $this->dibaca_pada !== null;
    }

    /** Ikon Bootstrap Icons berdasarkan awalan tipe. */
    public function getIkonAttribute(): string
    {
        $objek = explode('.', (string) $this->tipe)[0];

        return match ($objek) {
            'tiket' => 'bi-ticket-detailed-fill',
            'warkah' => 'bi-archive-fill',
            'pengaduan' => 'bi-chat-left-text-fill',
            default => 'bi-bell-fill',
        };
    }

    public function getWarnaAttribute(): string
    {
        return match (true) {
            str_ends_with((string) $this->tipe, '.macet'),
            str_ends_with((string) $this->tipe, '.terlambat'),
            str_ends_with((string) $this->tipe, '.ditolak'),
            str_ends_with((string) $this->tipe, '.dikembalikan') => 'danger',
            str_ends_with((string) $this->tipe, '.selesai'),
            str_ends_with((string) $this->tipe, '.siap') => 'success',
            str_ends_with((string) $this->tipe, '.diarsipkan') => 'secondary',
            default => 'primary',
        };
    }

    /** Waktu relatif sampai 7 hari, lalu tanggal absolut. */
    public function getWaktuLabelAttribute(): string
    {
        if ($this->created_at === null) {
            return '-';
        }

        if ($this->created_at->diffInDays(now()) >= 7) {
            return $this->created_at->format('d/m/Y H:i');
        }

        $menit = (int) $this->created_at->diffInMinutes(now());

        return match (true) {
            $menit < 1 => 'baru saja',
            $menit < 60 => "{$menit} menit lalu",
            $menit < 1440 => intdiv($menit, 60).' jam lalu',
            default => intdiv($menit, 1440).' hari lalu',
        };
    }
}
