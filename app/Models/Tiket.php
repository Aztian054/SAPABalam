<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tiket extends Model
{
    /** Peran/Bidang pekerjaan back office Seksi 1/2 (Blueprint V2, modul 04). */
    public const PERAN_BIDANG = [
        'verifikasi' => 'Verifikasi',
        'validator_bt' => 'Validator BT',
        'validator_su' => 'Validator SU',
        'alih_media_bt' => 'Alih Media BT',
        'alih_media_su' => 'Alih Media SU',
    ];

    /** Hubungan penerima berkas pada serah terima (Blueprint V2, modul 07). */
    public const HUBUNGAN_PENERIMA = [
        'pemohon' => 'Pemohon',
        'kuasa' => 'Kuasa',
        'keluarga' => 'Keluarga',
    ];

    protected $fillable = [
        'kode_tiket', 'no_berkas', 'nomor_antrian', 'nomor_urut_berkas',
        'tanggal_masuk', 'jenis_permohonan_id', 'nama_pemohon', 'nik_pemohon',
        'no_hp_pemohon', 'no_hak_sekarang', 'no_hak_sebelumnya',
        'kelurahan_desa', 'kecamatan', 'jumlah_bidang', 'petugas_loket_id',
        'nama_petugas_loket', 'nomor_telepon', 'nomor_tiket_ppat',
        'nomor_tiket_non_ppat', 'status', 'keterangan',
        'tanggal_selesai', 'created_by', 'diarsipkan_pada',
        'penanggung_jawab_id', 'diambil_pada', 'dilepas_admin_at',
        'peran_bidang', 'ditolak_pada', 'alasan_penolakan',
        'penerima_nama', 'penerima_hubungan', 'penerima_no_identitas',
        'catatan_penyerahan', 'diserahkan_pada', 'diserahkan_oleh_id',
        'ploting_status', 'ploting_catatan', 'ploting_selesai_at', 'ploting_oleh_id',
        'is_demo',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_bidang' => 'integer',
        'nomor_urut_berkas' => 'integer',
        'diambil_pada' => 'datetime',
        'ditolak_pada' => 'datetime',
        'dilepas_admin_at' => 'datetime',
        'diserahkan_pada' => 'datetime',
        'ploting_selesai_at' => 'datetime',
        'diarsipkan_pada' => 'datetime',
        'is_demo' => 'boolean',
    ];

    public static function generateNextKodeTiket(int $iterasi = 1): string
    {
        $today = Carbon::today();
        $dateCode = $today->format('dmy'); // DDMMYY

        // Hitung berapa tiket yang dibuat hari ini
        $countToday = self::whereDate('tanggal_masuk', $today)->count() + 1;

        return sprintf('K/%d/%s/%d', $countToday, $dateCode, $iterasi);
    }

    /** Tiket yang sudah masuk arsip otomatis (read-only). */
    public function scopeArsip(Builder $query): Builder
    {
        return $query->whereNotNull('diarsipkan_pada');
    }

    /** Kandidat arsip otomatis: selesai, belum diarsipkan, lewat N hari. */
    public function scopeSiapDiarsipkan(Builder $query, ?int $hari = null): Builder
    {
        $hari ??= Setting::angka('hari_arsip', 30);
        $batas = now()->subDays($hari);

        return $query->where('status', 'selesai')
            ->whereNull('diarsipkan_pada')
            ->where(function (Builder $q) use ($batas) {
                $q->where(fn (Builder $w) => $w->whereNotNull('diserahkan_pada')->where('diserahkan_pada', '<=', $batas))
                    ->orWhere(fn (Builder $w) => $w->whereNull('diserahkan_pada')
                        ->whereNotNull('tanggal_selesai')
                        ->whereDate('tanggal_selesai', '<=', $batas->toDateString()));
            });
    }

    /**
     * Cari tiket berdasarkan nomor tiket saja (toleran spasi/tanda baca).
     */
    public function scopeCariNomor(Builder $query, string $kode): Builder
    {
        $kode = trim($kode);

        if ($kode === '') {
            return $query;
        }

        $normal = preg_replace('/[^A-Za-z0-9]/', '', $kode);

        return $query->where(function (Builder $q) use ($kode, $normal) {
            $q->where('kode_tiket', 'like', "%{$kode}%");

            if ($normal !== '') {
                $q->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(kode_tiket, ' ', ''), '-', ''), '/', ''), '.', '') LIKE ?",
                    ["%{$normal}%"]
                );
            }
        });
    }

    /**
     * Pencarian pintar lintas kolom (Blueprint V2, bagian D): nomor tiket,
     * No. Berkas, pemohon, NIK, no hak, no HP, NIB, dan nomor sertipikat.
     * Toleran spasi/tanda baca pada nomor tiket.
     */
    public function scopeCariPintar(Builder $query, string $kata): Builder
    {
        $kata = trim($kata);

        if ($kata === '') {
            return $query;
        }

        $normal = preg_replace('/[^A-Za-z0-9]/', '', $kata);
        $like = "%{$kata}%";

        return $query->where(function (Builder $q) use ($like, $normal) {
            $q->where('kode_tiket', 'like', $like)
                ->orWhere('no_berkas', 'like', $like)
                ->orWhere('nama_pemohon', 'like', $like)
                ->orWhere('nik_pemohon', 'like', $like)
                ->orWhere('no_hak_sekarang', 'like', $like)
                ->orWhere('no_hak_sebelumnya', 'like', $like)
                ->orWhere('no_hp_pemohon', 'like', $like)
                ->orWhere('nomor_telepon', 'like', $like)
                ->orWhereHas('bidangTanahs', function (Builder $r) use ($like) {
                    $r->where('nib', 'like', $like)
                        ->orWhere('no_sertifikat_lama', 'like', $like)
                        ->orWhere('no_sertifikat_elektronik', 'like', $like);
                });

            if ($normal !== '') {
                $q->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(kode_tiket, ' ', ''), '-', ''), '/', ''), '.', '') LIKE ?",
                    ["%{$normal}%"]
                );
            }
        });
    }

    /** Apakah tiket sedang dipegang staf Seksi 1/2 (diproses atau ditolak). */
    public function dipegangStafSeksi(): bool
    {
        return in_array($this->status, ['diproses', 'ditolak'], true)
            && $this->penanggung_jawab_id !== null
            && $this->penanggungJawab?->isStafSeksi();
    }

    /** Tombol ADD aktif untuk staf: tiket baru, atau tiket ditolak miliknya sendiri. */
    public function bisaDiAddOleh(User $user): bool
    {
        if (! $user->isStafSeksi()) {
            return false;
        }

        if ($this->status === 'di_database') {
            return true;
        }

        return $this->status === 'ditolak' && $this->penanggung_jawab_id === $user->id;
    }

    /** Alasan tombol ADD nonaktif, dipakai sebagai teks bantuan di tabel. */
    public function alasanTidakBisaDiAdd(User $user): ?string
    {
        if ($this->bisaDiAddOleh($user)) {
            return null;
        }

        return match ($this->status) {
            'diproses' => 'Sudah diambil oleh '.($this->penanggungJawab?->name ?? 'petugas lain'),
            'ditolak' => 'Ditolak, menjadi tanggung jawab '.($this->penanggungJawab?->name ?? 'staf lain'),
            'selesai' => 'Tiket sudah selesai',
            default => 'Tiket tidak dapat di-ADD saat ini',
        };
    }

    public function getPeranBidangLabelAttribute(): string
    {
        return self::PERAN_BIDANG[$this->peran_bidang] ?? '-';
    }

    /** Lama tiket dipegang staf, relatif (contoh: "2 hari 3 jam"). */
    public function getLamaDiambilAttribute(): ?string
    {
        if ($this->diambil_pada === null) {
            return null;
        }

        $totalJam = (int) $this->diambil_pada->diffInHours(now());
        $hari = intdiv($totalJam, 24);
        $jam = $totalJam % 24;

        if ($hari > 0) {
            return $jam > 0 ? "{$hari} hari {$jam} jam" : "{$hari} hari";
        }

        return $jam > 0 ? "{$jam} jam" : 'baru saja';
    }

    /** Sudah masuk arsip otomatis? Tiket arsip hanya boleh dibaca. */
    public function getIsArsipAttribute(): bool
    {
        return $this->diarsipkan_pada !== null;
    }

    /** Lama simpan sejak diserahkan sampai diarsipkan, contoh: "30 hari". */
    public function getLamaSimpanArsipAttribute(): ?string
    {
        if ($this->diserahkan_pada === null || $this->diarsipkan_pada === null) {
            return null;
        }

        $hari = (int) $this->diserahkan_pada->diffInDays($this->diarsipkan_pada);

        return $hari > 0 ? "{$hari} hari" : 'kurang dari 1 hari';
    }

    /** Apakah tiket pernah disentuh Staf Seksi 1/2 (dipakai sebagai peringatan penyerahan). */
    public function pernahDiprosesSeksi(): bool
    {
        return $this->riwayatStatuses()
            ->whereIn('changed_by', User::where('role', 'staf_seksi')->pluck('id')->all())
            ->exists();
    }

    /** Tombol ADD aktif di Antrian Penyerahan: tiket ada di Database dan belum dipegang. */
    public function bisaDiAddPenyerahan(User $user): bool
    {
        return $this->status === 'di_database'
            && $this->penanggung_jawab_id === null
            && ($user->isLoketPenyerahan() || $user->isAdmin());
    }

    public function getPenerimaHubunganLabelAttribute(): string
    {
        return self::HUBUNGAN_PENERIMA[$this->penerima_hubungan] ?? '-';
    }

    /** Penanda macet: tiket dipegang melewati batas hari (bukan status). */
    public function getIsMacetAttribute(): bool
    {
        if ($this->status !== 'diproses' || $this->diambil_pada === null) {
            return false;
        }

        $batas = Setting::angka('batas_macet_hari', 3);

        return $this->diambil_pada->diffInDays(now()) > $batas;
    }

    public function jenisPermohonan(): BelongsTo
    {
        return $this->belongsTo(JenisPermohonan::class);
    }

    public function petugasLoket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_loket_id');
    }

    /** Staf penolak yang bertanggung jawab menyelesaikan tiket Ditolak. */
    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    public function plotingOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ploting_oleh_id');
    }

    /** Petugas Loket Penyerahan yang menyerahkan berkas. */
    public function diserahkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diserahkan_oleh_id');
    }

    public function warkahPermintaans(): HasMany
    {
        return $this->hasMany(WarkahPermintaan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bidangTanahs(): HasMany
    {
        return $this->hasMany(BidangTanah::class)->orderBy('urutan');
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
     * Alur Eksekusi tiket versi V2: Loket → Seksi → Penyerahan → Selesai.
     *
     * Diturunkan dari status tiket dan pemegang tiket saat ini, bukan dari
     * penugasan per tahap seperti alur lama. `no_berkas` tidak memengaruhi
     * alur — kunci alur tetap `kode_tiket`.
     *
     * @return array{items: list<array{no: int, label: string, status: string, status_label: string}>, notice: ?string, notice_type: ?string}
     */
    public function alurEksekusi(): array
    {
        $status = (string) $this->status;
        $pemegang = $this->penanggungJawab;

        $diSeksi = $status === 'diproses' && (bool) $pemegang?->isStafSeksi();
        $diPenyerahan = $status === 'diproses' && (bool) $pemegang?->isLoketPenyerahan();
        $seksiSelesai = in_array($status, ['selesai', 'di_database'], true);

        $items = [
            [
                'no' => 1,
                'label' => 'Loket Pendaftaran — Registrasi Berkas',
                'status' => $status === 'batal' ? 'batal' : 'selesai',
            ],
            [
                'no' => 2,
                'label' => 'Seksi 1/2 — Pemeriksaan & Penyelesaian Berkas',
                'status' => $diSeksi || $status === 'ditolak'
                    ? 'proses'
                    : ($seksiSelesai ? 'selesai' : 'menunggu'),
            ],
            [
                'no' => 3,
                'label' => 'Loket Penyerahan — Serah Terima ke Pemohon',
                'status' => $diPenyerahan ? 'proses' : ($status === 'selesai' ? 'selesai' : 'menunggu'),
            ],
            [
                'no' => 4,
                'label' => 'Selesai — Berkas Diterima Pemohon',
                'status' => $status === 'selesai' ? 'selesai' : 'menunggu',
            ],
        ];

        $notice = null;
        $noticeType = null;

        if ($status === 'batal') {
            $notice = $this->keterangan ? 'Permohonan dibatalkan: '.$this->keterangan : 'Permohonan telah dibatalkan.';
            $noticeType = 'danger';
        } elseif ($status === 'ditolak') {
            $notice = 'Berkas ditolak di Seksi 1/2 — menunggu perbaikan lalu ADD ULANG.'
                .($this->alasan_penolakan ? ' Alasan: '.$this->alasan_penolakan : '');
            $noticeType = 'warning';
        }

        return [
            'items' => array_map(
                fn (array $item): array => $item + ['status_label' => $this->statusAlurLabel($item['status'])],
                $items
            ),
            'notice' => $notice,
            'notice_type' => $noticeType,
        ];
    }

    /** Label Indonesia untuk status alur. */
    private function statusAlurLabel(string $status): string
    {
        return match ($status) {
            'selesai' => 'Selesai',
            'proses' => 'Berjalan',
            'batal' => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'di_database' => 'secondary',
            'diproses' => 'primary',
            'ditolak' => 'danger',
            'selesai' => 'success',
            'batal' => 'danger',
            default => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'di_database' => 'Di Database Admin',
            'diproses' => 'Sedang Diproses',
            'ditolak' => 'Ditolak — Menunggu Perbaikan',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }
}
