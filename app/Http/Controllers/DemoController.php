<?php

namespace App\Http\Controllers;

use App\Services\DemoService;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Data & Skenario Demo — hanya Super Admin dan hanya saat mode demo aktif.
 *
 * Seed membuat paket contoh (akun, tiket, warkah, pengaduan) bertanda
 * `is_demo`; reset hanya menghapus baris bertanda tersebut dengan
 * konfirmasi ganda.
 */
class DemoController extends Controller
{
    public function __construct(
        private readonly DemoService $demo,
        private readonly LogAktivitasService $log,
    ) {}

    public function index(): View
    {
        $this->pastikanModeDemoAktif();

        return view('super_admin.demo', [
            'ringkasan' => $this->demo->ringkasan(),
            'punyaJenisPermohonan' => $this->demo->punyaJenisPermohonan(),
            'frasaKonfirmasi' => DemoService::KONFIRMASI,
            'prefix' => DemoService::PREFIX,
        ]);
    }

    public function seed(): RedirectResponse
    {
        $this->pastikanModeDemoAktif();

        try {
            $hasil = $this->demo->seed();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->log->catat('SEED DEMO', 'Data Demo', $this->uraikan($hasil));

        return back()->with('success', 'Data demo berhasil dibuat: '.$this->uraikan($hasil).'.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $this->pastikanModeDemoAktif();

        $request->merge(['konfirmasi' => strtoupper(trim((string) $request->input('konfirmasi')))]);

        $request->validate([
            'konfirmasi' => ['required', Rule::in([DemoService::KONFIRMASI])],
            'paham' => ['accepted'],
        ], [
            'konfirmasi.required' => 'Ketik frasa konfirmasi untuk menghapus data demo.',
            'konfirmasi.in' => 'Frasa konfirmasi harus tepat: '.DemoService::KONFIRMASI.'.',
            'paham.accepted' => 'Centang pernyataan bahwa data demo akan dihapus.',
        ]);

        if ($this->demo->ringkasan()['total'] === 0) {
            return back()->with('warning', 'Tidak ada data demo yang perlu dihapus.');
        }

        $hasil = $this->demo->reset();

        $this->log->catat('RESET DEMO', 'Data Demo', $this->uraikan($hasil));

        return back()->with('success', 'Data demo dihapus: '.$this->uraikan($hasil).'. Data asli tidak tersentuh.');
    }

    /** Mode demo wajib aktif; di produksi halaman ini diperlakukan tidak ada. */
    private function pastikanModeDemoAktif(): void
    {
        abort_unless($this->demo->aktif(), 403, 'Data demo hanya tersedia saat mode demo aktif (APP_ENV=local atau DEMO_MODE=true).');
    }

    /**
     * @param  array<string, int>  $hasil
     */
    private function uraikan(array $hasil): string
    {
        return "{$hasil['tiket']} tiket, {$hasil['warkah']} warkah, {$hasil['pengaduan']} pengaduan, {$hasil['pengguna']} akun";
    }
}
