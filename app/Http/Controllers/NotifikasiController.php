<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\NotifikasiPreferensi;
use App\Services\NotifikasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Notifikasi in-app (Blueprint V2, modul 16).
 *
 * Lonceng memakai endpoint jumlah untuk polling ringan; membuka notifikasi
 * menandainya sudah dibaca lalu mengarahkan ke tautan tujuan.
 */
class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $tab = $request->string('tab')->toString() === 'semua' ? 'semua' : 'belum';

        $query = Notifikasi::untukUser($user);

        if ($tab === 'belum') {
            $query->belumDibaca();
        }

        $notifikasis = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('notifikasi.index', [
            'notifikasis' => $notifikasis,
            'tab' => $tab,
            'jumlahBelumDibaca' => Notifikasi::untukUser($user)->belumDibaca()->count(),
        ]);
    }

    public function baca(Notifikasi $notifikasi): RedirectResponse
    {
        $this->pastikanMilik($notifikasi);

        if (! $notifikasi->sudah_dibaca) {
            $notifikasi->update(['dibaca_pada' => now()]);
        }

        if ($notifikasi->url !== null && $notifikasi->url !== '') {
            return redirect()->to($notifikasi->url);
        }

        return redirect()->route('notifikasi.index');
    }

    public function bacaSemua(): RedirectResponse
    {
        Notifikasi::untukUser(Auth::user())
            ->belumDibaca()
            ->update(['dibaca_pada' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /** Endpoint ringan untuk badge lonceng (dipolling tiap 60 detik). */
    public function jumlah(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'belum_dibaca' => Notifikasi::untukUser($user)->belumDibaca()->count(),
        ]);
    }

    public function preferensi(): View
    {
        $user = Auth::user();

        return view('notifikasi.preferensi', [
            'grup' => NotifikasiService::TIPE,
            'pilihan' => NotifikasiPreferensi::where('user_id', $user->id)->pluck('aktif', 'tipe'),
            'jumlahBelumDibaca' => Notifikasi::untukUser($user)->belumDibaca()->count(),
        ]);
    }

    public function simpanPreferensi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipe' => ['nullable', 'array'],
            'tipe.*' => ['string', Rule::in(NotifikasiService::semuaTipe())],
        ]);

        $dipilih = array_flip($validated['tipe'] ?? []);

        foreach (NotifikasiService::semuaTipe() as $tipe) {
            NotifikasiPreferensi::updateOrCreate(
                ['user_id' => Auth::id(), 'tipe' => $tipe],
                ['aktif' => NotifikasiService::isWajib($tipe) || array_key_exists($tipe, $dipilih)],
            );
        }

        return back()->with('success', 'Preferensi notifikasi disimpan.');
    }

    private function pastikanMilik(Notifikasi $notifikasi): void
    {
        abort_unless($notifikasi->user_id === Auth::id(), 403);
    }
}
