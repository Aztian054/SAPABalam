<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan operasional yang dapat diubah Admin tanpa deploy.
 *
 * Nilai disimpan sebagai teks di tabel `settings`; pemakaian di kode
 * memakai helper `angka()` / `teks()` agar tipe data konsisten.
 * Bila suatu kunci belum ada di tabel, nilai fallback diambil dari
 * `config/loket.php` sehingga instalasi lama tetap berjalan.
 *
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Kunci setting beserta batas validasinya (dipakai form & validasi).
     *
     * `default` menjadi nilai cadangan terakhir bila config juga tidak ada.
     *
     * @var array<string, array{label: string, tipe: string, min?: int, max?: int, default: int|string}>
     */
    public const DEFNISI = [
        'batas_macet_hari' => ['label' => 'Batas hari penanda MACET', 'tipe' => 'angka', 'min' => 1, 'max' => 365, 'default' => 3],
        'batas_warkah_hari' => ['label' => 'Batas kembali warkah (hari)', 'tipe' => 'angka', 'min' => 1, 'max' => 365, 'default' => 7],
        'hari_arsip' => ['label' => 'Hari menuju arsip otomatis', 'tipe' => 'angka', 'min' => 1, 'max' => 365, 'default' => 30],
        'batas_tanggap_pengaduan_hari' => ['label' => 'Batas tanggap pengaduan (hari)', 'tipe' => 'angka', 'min' => 1, 'max' => 365, 'default' => 2],
        'retensi_log_hari' => ['label' => 'Retensi log (hari)', 'tipe' => 'angka', 'min' => 1, 'max' => 3650, 'default' => 90],
        'prefix_tiket' => ['label' => 'Prefix No. Tiket', 'tipe' => 'teks', 'default' => 'JP'],
        'prefix_pengaduan' => ['label' => 'Prefix No. Pengaduan', 'tipe' => 'teks', 'default' => 'LP'],
        'padding_tiket' => ['label' => 'Jumlah digit urut tiket', 'tipe' => 'angka', 'min' => 2, 'max' => 5, 'default' => 3],
    ];

    /** Cache nilai per instance aplikasi (array store saat test). */
    private const KUNCI_CACHE = 'loket.settings';

    /** @return array<string, string> */
    public static function semua(): array
    {
        return Cache::remember(self::KUNCI_CACHE, now()->addHour(), fn (): array => static::query()
            ->pluck('value', 'key')
            ->all());
    }

    /** Nilai mentah; fallback ke config('loket.{key}') lalu default definisi. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $nilai = self::semua()[$key] ?? null;

        if ($nilai !== null && $nilai !== '') {
            return $nilai;
        }

        return $default ?? config('loket.'.$key, self::DEFNISI[$key]['default'] ?? null);
    }

    public static function angka(string $key, ?int $default = null): int
    {
        return (int) self::get($key, $default);
    }

    public static function teks(string $key, ?string $default = null): string
    {
        return (string) self::get($key, $default);
    }

    /**
     * Simpan sekumpulan setting sekaligus (satu transaksi logis).
     *
     * @param  array<string, mixed>  $data
     */
    public static function simpan(array $data): void
    {
        foreach ($data as $key => $value) {
            if (! array_key_exists($key, self::DEFNISI)) {
                continue;
            }

            static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        Cache::forget(self::KUNCI_CACHE);
    }
}
