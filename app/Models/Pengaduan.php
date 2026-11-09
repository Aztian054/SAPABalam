<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengaduan masyarakat (Blueprint V2, modul 08–09).
 *
 * Dibuat oleh Loket Pengaduan, ditangani Kasi unit tujuan:
 * terkirim → diterima → selesai, dengan cabang dikembalikan.
 *
 * Kategori pengaduan:
 * - permasalahan_tanah: form detail surat formal (Hal, biodata lanjutan, letak, luas, dst.)
 * - pungli: hanya catatan
 * - buruknya_pelayanan: hanya catatan
 * - pelanggaran_etik_ppat: hanya catatan
 * - kritik_saran_aspirasi: hanya catatan
 */
class Pengaduan extends Model
{
    public const JENIS = [
        'permasalahan_tanah' => 'Permasalahan Tanah (Sengketa & Konflik)',
        'pungli' => 'Pungli & Penyalahgunaan Wewenang',
        'buruknya_pelayanan' => 'Buruknya Pelayanan & SOP',
        'pelanggaran_etik_ppat' => 'Pelanggaran Etik PPAT',
        'kritik_saran_aspirasi' => 'Kritik, Saran, & Aspirasi (Fasilitas & Sistem)',
    ];

    public const STATUS = [
        'terkirim' => 'Terkirim',
        'diterima' => 'Diterima',
        'dikembalikan' => 'Dikembalikan',
        'selesai' => 'Selesai',
    ];

    protected $fillable = [
        'nomor_pengaduan',
        'tanggal_buat',
        'pembuat_id',
        'nama_pengadu',
        'nik_pengadu',
        'no_hp_pengadu',
        'email_pengadu',
        'alamat_pengadu',
        'is_anonim',
        'jenis',
        'tiket_id',
        'uraian',
        'seksi_tujuan',
        'status',
        'catatan_terakhir',
        'diterima_oleh_id',
        'diterima_pada',
        'selesai_oleh_id',
        'selesai_pada',
        'dikembalikan_pada',
        'is_demo',
    ];

    protected $casts = [
        'tanggal_buat' => 'date',
        'is_anonim' => 'boolean',
        'diterima_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'dikembalikan_pada' => 'datetime',
        'is_demo' => 'boolean',
    ];

    /**
     * Pencarian pintar: nomor pengaduan, nama/NIK/HP pengadu, dan nomor tiket
     * terkait.
     */
    public function scopeCariPintar(Builder $query, string $kata): Builder
    {
        $kata = trim($kata);

        if ($kata === '') {
            return $query;
        }

        $like = "%{$kata}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('nomor_pengaduan', 'like', $like)
                ->orWhere('nama_pengadu', 'like', $like)
                ->orWhere('nik_pengadu', 'like', $like)
                ->orWhere('no_hp_pengadu', 'like', $like)
                ->orWhere('email_pengadu', 'like', $like)
                ->orWhereHas('tiket', fn (Builder $t) => $t->where('kode_tiket', 'like', $like));
        });
    }

    /** Batasi ke pengaduan yang menjadi kewenangan unit pengguna. */
    public function scopeUntukUnit(Builder $query, User $user): Builder
    {
        return $query->where('seksi_tujuan', $user->seksiPengaduan());
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function tiket(): BelongsTo
    {
        return $this->belongsTo(Tiket::class);
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh_id');
    }

    public function selesaiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selesai_oleh_id');
    }

    public function riwayats(): HasMany
    {
        return $this->hasMany(PengaduanRiwayat::class)->orderBy('id');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst((string) $this->jenis);
    }

    public function getSeksiTujuanLabelAttribute(): string
    {
        return User::SEKSI[$this->seksi_tujuan] ?? (string) $this->seksi_tujuan;
    }

    public function getPengaduLabelAttribute(): string
    {
        return $this->is_anonim ? 'Anonim' : ($this->nama_pengadu ?? '-');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'terkirim' => 'warning',
            'diterima' => 'primary',
            'dikembalikan' => 'danger',
            'selesai' => 'success',
            default => 'secondary',
        };
    }

    /** Penanda terlambat: masih menunggu unit melewati batas tanggap (hari). */
    public function getIsTerlambatAttribute(): bool
    {
        if ($this->status !== 'terkirim' || $this->created_at === null) {
            return false;
        }

        return $this->created_at->diffInDays(now()) > Setting::angka('batas_tanggap_pengaduan_hari', 2);
    }

    /** Lama menunggu dalam teks ringkas, contoh: "2 hari 3 jam". */
    public function getLamaMenungguAttribute(): ?string
    {
        $mulai = $this->created_at;
        $akhir = $this->status === 'selesai' ? $this->selesai_pada : now();

        if ($mulai === null || $akhir === null) {
            return null;
        }

        $totalJam = (int) $mulai->diffInHours($akhir);
        $hari = intdiv($totalJam, 24);
        $jam = $totalJam % 24;

        if ($hari > 0) {
            return $jam > 0 ? "{$hari} hari {$jam} jam" : "{$hari} hari";
        }

        return $jam > 0 ? "{$jam} jam" : 'baru saja';
    }

    /** Apakah kategori memerlukan form detail surat formal (Permasalahan Tanah). */
    public function butuhDetailTanah(): bool
    {
        return $this->jenis === 'permasalahan_tanah';
    }

    /** Relasi catatan kronologis (urutan 1, 2, 3...). */
    public function catatans(): HasMany
    {
        return $this->hasMany(PengaduanCatatan::class)->orderBy('urutan', 'asc');
    }

    /** Relasi rantai bantuan antar Kasi. */
    public function bantuans(): HasMany
    {
        return $this->hasMany(PengaduanBantuan::class)->orderBy('urutan', 'asc');
    }

    /** Kasi asli yang menerima pengaduan langsung dari Loket (seksi_tujuan). */
    public function getKasiAsliAttribute(): ?string
    {
        return $this->seksi_tujuan;
    }

    /** Semua seksi Kasi yang terlibat dalam rantai (asli + tujuan bantuan). */
    public function getSemuaSeksiTerlibatAttribute(): array
    {
        $seksi = [$this->seksi_tujuan];

        foreach ($this->bantuans as $bantuan) {
            if (! in_array($bantuan->ke_kasi, $seksi, true)) {
                $seksi[] = $bantuan->ke_kasi;
            }
        }

        return $seksi;
    }

    /** Cek apakah user (Kasi) berhak melihat pengaduan ini. */
    public function bolehDilihatOleh(User $user): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isKakan()) {
            return true;
        }

        if ($user->isKasiSeksi() || $user->isKasiUnit()) {
            return in_array($user->seksiPengaduan(), $this->semuaSeksiTerlibat, true);
        }

        return false;
    }

    /** Cek apakah user adalah Kasi asli (yang boleh klik Selesai Akhir). */
    public function adalahKasiAsli(User $user): bool
    {
        return $user->isKasiSeksi() || $user->isKasiUnit()
            ? $user->seksiPengaduan() === $this->seksi_tujuan
            : false;
    }

    /** Urutan catatan berikutnya. */
    public function nextUrutanCatatan(): int
    {
        return (int) $this->catatans()->max('urutan') + 1;
    }

    /** Urutan bantuan berikutnya. */
    public function nextUrutanBantuan(): int
    {
        return (int) $this->bantuans()->max('urutan') + 1;
    }

    /** Bantuan terakhir yang belum ditanggapi. */
    public function getBantuanBelumDitanggapiAttribute(): ?PengaduanBantuan
    {
        return $this->bantuans()->where('status', 'diajukan')->latest('urutan')->first();
    }
}
