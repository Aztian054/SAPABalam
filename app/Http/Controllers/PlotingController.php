<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranDraft;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Ploting dua sisi (Blueprint V2, E.3):
 *  - Loket Ploting memproses draf yang masuk antrian (belum → sudah + catatan).
 *  - Loket Pendaftaran melihat tab Menunggu / Sudah Ploting dan melanjutkan
 *    pendaftaran ke Tahap 3.
 */
class PlotingController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $bisaMemproses = $user->isLoketPloting();

        $dasar = PendaftaranDraft::with(['user', 'plotingOleh']);

        if (! $bisaMemproses) {
            $dasar->where('user_id', $user->id);
        }

        if ($request->filled('q')) {
            $kata = trim($request->string('q')->toString());
            $dasar->where(function ($q) use ($kata) {
                $q->where('kode_tiket', 'like', "%{$kata}%")
                    ->orWhere('no_berkas', 'like', "%{$kata}%")
                    ->orWhere('data', 'like', "%{$kata}%");
            });
        }

        $tab = $request->string('tab')->toString() === 'selesai' ? 'selesai' : 'menunggu';

        $daftar = (clone $dasar);
        $daftar->where('ploting_status', $tab === 'selesai' ? 'sudah' : 'belum');

        if ($tab === 'menunggu') {
            $daftar->whereNotNull('dikirim_pada');
        }

        $kolomTanggal = $tab === 'selesai' ? 'ploting_selesai_at' : 'dikirim_pada';

        if ($request->filled('tanggal_dari')) {
            $daftar->whereDate($kolomTanggal, '>=', $request->date('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $daftar->whereDate($kolomTanggal, '<=', $request->date('tanggal_sampai'));
        }

        $daftar = $daftar->orderBy($kolomTanggal, $tab === 'selesai' ? 'desc' : 'asc')->paginate(15)->withQueryString();

        $jumlahMenunggu = (clone $dasar)->where('ploting_status', 'belum')->whereNotNull('dikirim_pada')->count();
        $jumlahSelesai = (clone $dasar)->where('ploting_status', 'sudah')->count();

        return view('ploting.index', compact(
            'daftar', 'tab', 'bisaMemproses', 'jumlahMenunggu', 'jumlahSelesai'
        ));
    }

    public function selesai(Request $request, PendaftaranDraft $draf): RedirectResponse
    {
        abort_unless(Auth::user()->isLoketPloting(), 403);

        if ($draf->ploting_status === 'sudah' || $draf->dikirim_pada === null) {
            return redirect()->route('ploting.index')
                ->with('warning', "Draf {$draf->kode_tiket} tidak berada dalam antrian ploting.");
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $draf->update([
            'ploting_status' => 'sudah',
            'ploting_catatan' => $validated['catatan'],
            'ploting_oleh_id' => Auth::id(),
            'ploting_selesai_at' => now(),
            'dikirim_pada' => $draf->dikirim_pada ?? now(),
            'tahap' => max(2, $draf->tahap),
        ]);

        $this->notifikasi->kirim(
            tipe: 'tiket.ploting_selesai',
            penerimaIds: [$draf->user_id],
            judul: "Ploting {$draf->kode_tiket} selesai — lanjutkan pendaftaran",
            pesan: 'Berkas sudah dipetakan. Lanjutkan ke Tahap 3 untuk mengisi data permohonan.',
            url: route('pendaftaran.tahap3', $draf),
            objekTipe: 'draf',
            objekId: $draf->id,
        );

        return redirect()->route('ploting.index', ['tab' => 'selesai'])
            ->with('success', "Ploting {$draf->kode_tiket} selesai. Loket Pendaftaran dapat melanjutkan pendaftaran.");
    }
}
