<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Setting (Admin & Super Admin).
 *
 * Nilai disimpan di tabel `settings` sehingga dapat diubah tanpa deploy.
 * Perubahan berlaku untuk data baru; data lama tidak dihitung ulang.
 */
class SettingController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $log,
    ) {}

    public function edit(): View
    {
        $nilai = [];

        foreach (array_keys(Setting::DEFNISI) as $key) {
            $nilai[$key] = Setting::get($key);
        }

        return view('admin.setting', [
            'nilai' => $nilai,
            'definisi' => Setting::DEFNISI,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'prefix_tiket' => strtoupper(trim((string) $request->input('prefix_tiket'))),
            'prefix_pengaduan' => strtoupper(trim((string) $request->input('prefix_pengaduan'))),
        ]);

        $data = $request->validate($this->aturan(), [], $this->atribut());

        $lama = [];
        foreach (array_keys($data) as $key) {
            $lama[$key] = (string) Setting::get($key);
        }

        Setting::simpan($data);
        $this->catatPerubahan($lama, $data);

        return back()->with('success', 'Pengaturan berhasil disimpan. Perubahan berlaku untuk data baru.');
    }

    /**
     * Catat hanya nilai yang benar-benar berubah ke log audit.
     *
     * @param  array<string, string>  $lama
     * @param  array<string, mixed>  $baru
     */
    private function catatPerubahan(array $lama, array $baru): void
    {
        $perubahan = [];

        foreach ($baru as $key => $value) {
            $nilaiBaru = (string) $value;

            if ($lama[$key] !== $nilaiBaru) {
                $perubahan[] = Setting::DEFNISI[$key]['label'].': '.$lama[$key].' → '.$nilaiBaru;
            }
        }

        if ($perubahan !== []) {
            $this->log->catat('UBAH SETTING', 'Setting', implode('; ', $perubahan));
        }
    }

    /**
     * Susun aturan validasi dari definisi setting agar label, tipe, dan
     * batas nilai hanya didefinisikan di satu tempat.
     *
     * @return array<string, array<int, string>>
     */
    private function aturan(): array
    {
        $aturan = [];

        foreach (Setting::DEFNISI as $key => $definisi) {
            $aturan[$key] = $definisi['tipe'] === 'angka'
                ? ['required', 'integer', 'min:'.$definisi['min'], 'max:'.$definisi['max']]
                : ['required', 'string', 'max:5', 'regex:/^[A-Z0-9]+$/'];
        }

        return $aturan;
    }

    /** @return array<string, string> */
    private function atribut(): array
    {
        $atribut = [];

        foreach (Setting::DEFNISI as $key => $definisi) {
            $atribut[$key] = $definisi['label'];
        }

        return $atribut;
    }
}
