<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\TiketPenugasan;
use App\Models\User;
use App\Services\TiketFlowService;
use Illuminate\Http\Request;

class PemimpinController extends Controller
{
    /** Dashboard monitoring eksekutif untuk Pemimpin. */
    public function index(Request $request)
    {
        $stageCounts = [
            'diterima' => Tiket::where('status', 'diterima')->count(),
            'verifikasi' => Tiket::where('status', 'verifikasi')->count(),
            'warkah' => Tiket::where('status', 'warkah')->count(),
            'validasi_btel' => Tiket::where('status', 'validasi_btel')->count(),
            'validasi_suel' => Tiket::where('status', 'validasi_suel')->count(),
            'alih_media_btel' => Tiket::where('status', 'alih_media_btel')->count(),
            'alih_media_suel' => Tiket::where('status', 'alih_media_suel')->count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'dikembalikan' => Tiket::where('status', 'dikembalikan')->count(),
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        // Beban kerja per petugas (penugasan aktif per tahap)
        $beban = User::whereIn('role', array_keys(TiketFlowService::ROLE_STAGE))
            ->get()
            ->map(function ($u) {
                $u->beban_aktif = TiketPenugasan::where('user_id', $u->id)
                    ->where('status', 'proses')
                    ->count();
                $u->total_selesai = TiketPenugasan::where('user_id', $u->id)
                    ->where('status', 'selesai')
                    ->count();

                return $u;
            })
            ->filter(fn ($u) => $u->total_selesai > 0 || $u->beban_aktif > 0)
            ->sortByDesc('beban_aktif');

        // Filtering tiket untuk pemantauan
        $q = Tiket::with(['jenisPermohonan', 'petugasLoket'])->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $s = trim($request->q);
            $q->where(fn ($w) => $w->where('kode_tiket', 'like', "%{$s}%")->orWhere('nama_pemohon', 'like', "%{$s}%"));
        }
        $tikets = $q->take(30)->get();

        $activities = RiwayatStatus::with(['tiket', 'user'])->latest()->take(10)->get();
        $selesaiTotal = $stageCounts['selesai'];

        return view('pemimpin.index', compact(
            'stageCounts', 'selesaiTotal', 'beban', 'tikets', 'activities'
        ));
    }

    /** Detail tiket untuk monitoring. */
    public function show(int $id)
    {
        $tiket = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'penugasans.user',
            'riwayatStatuses.user',
            'catatanRevisis',
        ])->findOrFail($id);

        return view('pemimpin.show', compact('tiket'));
    }
}
