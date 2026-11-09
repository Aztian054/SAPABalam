<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();

        $totalTiket = Tiket::count();
        $tiketHariIni = Tiket::whereDate('tanggal_masuk', $today)->count();
        $tiketSelesai = Tiket::where('status', 'selesai')->count();
        $tiketDitolak = Tiket::where('status', 'ditolak')->count();

        $statusCounts = [
            'di_database' => Tiket::where('status', 'di_database')->count(),
            'diproses' => Tiket::where('status', 'diproses')->count(),
            'ditolak' => $tiketDitolak,
            'selesai' => $tiketSelesai,
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        $recentTikets = $this->queryTerlihat($user)
            ->with(['jenisPermohonan'])
            ->latest()
            ->take(8)
            ->get();

        $recentActivities = RiwayatStatus::with(['tiket', 'user'])
            ->when(! $user->isAdmin() && ! $user->isKakan(), function (Builder $q) use ($user) {
                $q->whereHas('tiket', function (Builder $inner) use ($user) {
                    $this->filterTerlihat($inner, $user);
                });
            })
            ->latest()
            ->take(6)
            ->get();

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

        return view('dashboard.index', [
            'totalTiket' => $totalTiket,
            'tiketHariIni' => $tiketHariIni,
            'tiketSelesai' => $tiketSelesai,
            'tiketDitolak' => $tiketDitolak,
            'statusCounts' => $statusCounts,
            'recentTikets' => $recentTikets,
            'recentActivities' => $recentActivities,
            'user' => $user,
            'monthlyData' => $monthlyData,
        ]);
    }

    /** Query tiket yang boleh dilihat pengguna pada dashboard. */
    private function queryTerlihat(User $user): Builder
    {
        return $this->filterTerlihat(Tiket::query(), $user);
    }

    private function filterTerlihat(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isKakan()) {
            return $query;
        }

        if ($user->isLoketPendaftaran()) {
            return $query->where(fn (Builder $q) => $q
                ->where('created_by', $user->id)
                ->orWhere('petugas_loket_id', $user->id));
        }

        if ($user->isStafSeksi()) {
            return $query->where('penanggung_jawab_id', $user->id)
                ->whereIn('status', ['diproses', 'ditolak']);
        }

        if ($user->isKasiSeksi()) {
            $stafIds = User::where('role', 'staf_seksi')
                ->where('seksi', $user->seksi)
                ->pluck('id')
                ->all();

            return $query->whereIn('penanggung_jawab_id', $stafIds)
                ->whereIn('status', ['diproses', 'ditolak']);
        }

        if ($user->isLoketPenyerahan()) {
            return $query->where(fn (Builder $q) => $q
                ->where('penanggung_jawab_id', $user->id)
                ->orWhere('diserahkan_oleh_id', $user->id));
        }

        if ($user->isPetugasWarkah()) {
            return $query->whereHas('warkahPermintaans');
        }

        return $query->whereRaw('1 = 0');
    }
}
