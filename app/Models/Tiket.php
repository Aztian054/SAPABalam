<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tiket extends Model
{
    /** Tahap-tahap yang tersedia pada alur V2.0. */
    public const STAGES = [
        'verifikasi' => 'Verifikasi Berkas',
        'warkah' => 'Pencarian & Data Warkah',
        'validasi_btel' => 'Validasi Pra-BTel',
        'validasi_suel' => 'Validasi Pra-SuEl',
        'alih_media_btel' => 'Alih Media Pra-BTel',
        'alih_media_suel' => 'Alih Media Pra-SuEl',
    ];

    protected $fillable = [
        'kode_tiket', 'nomor_antrian', 'nomor_urut_berkas', 'status_pembetulan',
        'tanggal_masuk', 'jenis_permohonan_id', 'nama_pemohon', 'nik_pemohon',
        'no_hp_pemohon', 'email_pemohon', 'no_hak_sekarang', 'no_hak_sebelumnya',
        'kelurahan_desa', 'kecamatan', 'jumlah_bidang', 'petugas_loket_id',
        'nama_petugas_loket', 'nomor_telepon', 'nomor_tiket_ppat',
        'nomor_tiket_non_ppat', 'status_pra_btel', 'status_pra_suel',
        'revisi_ke', 'status', 'keterangan',
        'tanggal_selesai', 'created_by',
        'diserahkan_ke_validator',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_bidang' => 'integer',
        'nomor_urut_berkas' => 'integer',
        'revisi_ke' => 'integer',
        'diserahkan_ke_validator' => 'boolean',
    ];

    public static function generateNextKodeTiket(int $iterasi = 1): string
    {
        $today = Carbon::today();
        $dateCode = $today->format('dmy'); // DDMMYY

        // Hitung berapa tiket yang dibuat hari ini
        $countToday = self::whereDate('tanggal_masuk', $today)->count() + 1;

        return sprintf('K/%d/%s/%d', $countToday, $dateCode, $iterasi);
    }

    public function jenisPermohonan(): BelongsTo
    {
        return $this->belongsTo(JenisPermohonan::class);
    }

    public function petugasLoket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_loket_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bidangTanahs(): HasMany
    {
        return $this->hasMany(BidangTanah::class)->orderBy('urutan');
    }

    public function verifikasiBerkas(): HasMany
    {
        return $this->hasMany(VerifikasiBerkas::class)->orderByDesc('iterasi');
    }

    public function latestVerifikasi(): HasOne
    {
        return $this->hasOne(VerifikasiBerkas::class)->latestOfMany();
    }

    public function lembarKerjaWarkahs(): HasMany
    {
        return $this->hasMany(LembarKerjaWarkah::class);
    }

    public function validasiBtel(): HasMany
    {
        return $this->hasMany(ValidasiBtel::class);
    }

    public function validasiSuel(): HasMany
    {
        return $this->hasMany(ValidasiSuel::class);
    }

    public function alihMediaBtel(): HasMany
    {
        return $this->hasMany(AlihMediaBtel::class);
    }

    public function alihMediaSuel(): HasMany
    {
        return $this->hasMany(AlihMediaSuel::class);
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(TiketPenugasan::class);
    }

    public function catatanRevisis(): HasMany
    {
        return $this->hasMany(CatatanRevisi::class)->orderByDesc('id');
    }

    public function arsips(): HasMany
    {
        return $this->hasMany(ArsipTiket::class);
    }

    public function riwayatStatuses(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class)->orderByDesc('created_at');
    }

    /**
     * Penugasan aktif pada tahap tertentu untuk tiket ini.
     * (Dipakai untuk anti-duplikat: satu tiket hanya dapat di-Add oleh
     *  satu akun pada tahap yang sama.)
     */
    public function penugasanAktif(string $stage): ?TiketPenugasan
    {
        return $this->penugasans()
            ->where('stage', $stage)
            ->where('status', 'proses')
            ->first();
    }

    /** Apakah penugasan (selesai) sudah pernah ada untuk tahap tersebut. */
    public function isStageSelesai(string $stage): bool
    {
        return $this->penugasans()
            ->where('stage', $stage)
            ->where('status', 'selesai')
            ->exists();
    }

    /** Apakah Validator BT & SU keduanya telah selesai (syarat masuk Alih Media). */
    public function isValidasiSelesai(): bool
    {
        return $this->isStageSelesai('validasi_btel') && $this->isStageSelesai('validasi_suel');
    }

    /** Apakah Alih Media BT & SU keduanya telah selesai (syarat Siap Selesai). */
    public function isAlihMediaSelesai(): bool
    {
        return $this->isStageSelesai('alih_media_btel') && $this->isStageSelesai('alih_media_suel');
    }

    /**
     * Apakah Warkah telah menandai penyerahan ke Validator
     * (`diserahkan_ke_validator = true`) — gerbang pembuka Validator BT/SU.
     */
    public function isDiserahkanKeValidator(): bool
    {
        return (bool) $this->diserahkan_ke_validator;
    }

    /** Apakah SEMUA tahap sebelum Alih Media selesai (gate AND penuh). */
    public function isSemuaTahapSelesai(): bool
    {
        return $this->isStageSelesai('verifikasi')
            && $this->isStageSelesai('warkah')
            && $this->isStageSelesai('validasi_btel')
            && $this->isStageSelesai('validasi_suel');
    }

    /** Status progres gabungan BT/SU. */
    public function getProgresBtelSuelAttribute(): array
    {
        return [
            'btel' => $this->status_pra_btel ?? 'menunggu',
            'suel' => $this->status_pra_suel ?? 'menunggu',
        ];
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'diterima' => 'warning',
            'verifikasi' => 'info',
            'warkah' => 'secondary',
            'validasi_btel' => 'primary',
            'validasi_suel' => 'purple',
            'alih_media_btel' => 'dark',
            'alih_media_suel' => 'indigo',
            'selesai' => 'success',
            'dikembalikan' => 'danger',
            'perbaikan' => 'warning',
            'batal' => 'danger',
            default => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diterima' => 'Diterima di Loket',
            'verifikasi' => 'Pemeriksaan Verifikator',
            'warkah' => 'Pencarian & Data Warkah',
            'validasi_btel' => 'Validasi Pra-Buku Tanah El.',
            'validasi_suel' => 'Validasi Pra-Surat Ukur El.',
            'alih_media_btel' => 'Alih Media Pra-Buku Tanah El.',
            'alih_media_suel' => 'Alih Media Pra-Surat Ukur El.',
            'selesai' => 'Selesai (Sertifikat El. Terbit)',
            'dikembalikan' => 'Dikembalikan (Perlu Perbaikan)',
            'perbaikan' => 'Perbaikan (Dalam Proses Revisi)',
            'batal' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
