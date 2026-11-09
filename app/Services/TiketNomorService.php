<?php

namespace App\Services;

use App\Models\PendaftaranDraft;
use App\Models\Setting;
use App\Models\Tiket;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Penomoran tiket V2 otomatis.
 *
 * Format: {JP}{ddmmyyyy}{urut 3 digit} — contoh JP26102026001.
 * Nomor dipesan sejak draf pendaftaran agar tidak bentrok saat draf selesai.
 */
class TiketNomorService
{
    public function prefix(?CarbonInterface $tanggal = null): string
    {
        $tanggal ??= Carbon::today();

        return Setting::teks('prefix_tiket', 'JP').$tanggal->format('dmY');
    }

    /**
     * Buat nomor berikutnya yang belum dipakai tiket maupun draf.
     */
    public function generate(?CarbonInterface $tanggal = null, ?int $kecualiDraftId = null): string
    {
        $prefix = $this->prefix($tanggal);
        $padding = Setting::angka('padding_tiket', 3);
        $maksimal = (int) str_repeat('9', $padding);

        for ($urut = 1; $urut <= $maksimal; $urut++) {
            $kode = $prefix.str_pad((string) $urut, $padding, '0', STR_PAD_LEFT);

            if ($this->tersedia($kode, $kecualiDraftId)) {
                return $kode;
            }
        }

        throw new \RuntimeException("Kuota nomor tiket harian ({$prefix}) habis. Hubungi Admin.");
    }

    /**
     * Cek nomor belum dipakai tiket/draf lain.
     */
    public function tersedia(string $kode, ?int $kecualiDraftId = null): bool
    {
        $dipakaiTiket = Tiket::where('kode_tiket', $kode)->exists();

        if ($dipakaiTiket) {
            return false;
        }

        return ! PendaftaranDraft::where('kode_tiket', $kode)
            ->when($kecualiDraftId, fn ($q) => $q->whereKeyNot($kecualiDraftId))
            ->exists();
    }
}
