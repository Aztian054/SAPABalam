<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use App\Services\TiketFlowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();

        $totalTiket = Tiket::count();
        $tiketHariIni = Tiket::whereDate('tanggal_masuk', $today)->count();
        $tiketSelesai = Tiket::where('status', 'selesai')->count();
        $tiketDikembalikan = Tiket::where('status', 'dikembalikan')->count();

        $stageCounts = [
            'diterima' => Tiket::where('status', 'diterima')->count(),
            'verifikasi' => Tiket::where('status', 'verifikasi')->count(),
            'warkah' => Tiket::where('status', 'warkah')->count(),
            'validasi_btel' => Tiket::where('status', 'validasi_btel')->count(),
            'validasi_suel' => Tiket::where('status', 'validasi_suel')->count(),
            'alih_media_btel' => Tiket::where('status', 'alih_media_btel')->count(),
            'alih_media_suel' => Tiket::where('status', 'alih_media_suel')->count(),
            'selesai' => $tiketSelesai,
            'dikembalikan' => $tiketDikembalikan,
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        $recentQuery = Tiket::with(['jenisPermohonan', 'bidangTanahs'])->latest();

        if ($user->isAdmin() || $user->isPemimpin()) {
            // Semua tiket
        } elseif (isset(TiketFlowService::ROLE_STAGE[$user->role])) {
            $stage = TiketFlowService::ROLE_STAGE[$user->role];
            $recentQuery->whereHas('penugasans', function ($q) use ($stage, $user) {
                $q->where('stage', $stage)->where('user_id', $user->id);
            });
        } elseif ($user->role === 'loket') {
            $recentQuery->where('petugas_loket_id', $user->id);
        }

        $recentTikets = $recentQuery->take(8)->get();

        $recentActivities = RiwayatStatus::with(['tiket', 'user'])
            ->latest()->take(6)->get();

        $roles = User::ROLES;

        // Monthly ticket data for charts (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'label' => $month->format('M Y'),
                'count' => Tiket::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        return view('dashboard.index', compact(
            'totalTiket', 'tiketHariIni', 'tiketSelesai', 'tiketDikembalikan',
            'stageCounts', 'recentTikets', 'recentActivities', 'roles', 'user',
            'monthlyData'
        ));
    }
}
