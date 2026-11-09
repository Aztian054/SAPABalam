<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ApiAuthController — autentikasi & dashboard untuk SPA React.
 *
 * Mode stateful Sanctum: login/logout memakai cookie session (bukan bearer
 * token). Endpoint me/dashboard dilindungi guard auth:sanctum supaya tetap
 * kompatibel dengan API client berbasis token.
 */
class ApiAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $fieldType = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$fieldType => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password yang Anda masukkan salah, atau akun tidak aktif.',
            ], 401);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Selamat datang kembali, '.$user->name.'.',
            'data' => ['user' => $this->serializeUser($user)],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => ['user' => $this->serializeUser($user)],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Middleware auth:sanctum mengubah default guard → RequestGuard (tanpa
        // logout). Keluar lewat guard 'web' yang memegang session cookie SPA.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Anda telah berhasil keluar sistem.',
        ]);
    }

    public function dashboard(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();

        $totalTiket = Tiket::count();
        $tiketHariIni = Tiket::whereDate('tanggal_masuk', $today)->count();
        $tiketSelesai = Tiket::where('status', 'selesai')->count();
        $tiketDitolak = Tiket::where('status', 'ditolak')->count();

        $stageCounts = [
            'di_database' => Tiket::where('status', 'di_database')->count(),
            'diproses' => Tiket::where('status', 'diproses')->count(),
            'ditolak' => $tiketDitolak,
            'selesai' => $tiketSelesai,
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        // Data grafik — 6 bulan terakhir (konsisten dengan Dashboard blade)
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

        // Tiket terbaru — disaring sesuai jabatan V2.
        $recentQuery = Tiket::with(['jenisPermohonan'])->latest();

        if (! $user->isAdmin() && ! $user->isKakan()) {
            if ($user->isLoketPendaftaran()) {
                $recentQuery->where(fn ($q) => $q
                    ->where('created_by', $user->id)
                    ->orWhere('petugas_loket_id', $user->id));
            } elseif ($user->isStafSeksi()) {
                $recentQuery->where('penanggung_jawab_id', $user->id);
            } elseif ($user->isKasiSeksi()) {
                $stafIds = User::where('role', 'staf_seksi')
                    ->where('seksi', $user->seksi)
                    ->pluck('id')
                    ->all();
                $recentQuery->whereIn('penanggung_jawab_id', $stafIds);
            } elseif ($user->isLoketPenyerahan()) {
                $recentQuery->where(fn ($q) => $q
                    ->where('penanggung_jawab_id', $user->id)
                    ->orWhere('diserahkan_oleh_id', $user->id));
            } elseif ($user->isPetugasWarkah()) {
                $recentQuery->whereHas('warkahPermintaans');
            } else {
                $recentQuery->whereRaw('1 = 0');
            }
        }

        $recentTikets = $recentQuery->take(8)->get()->map(function (Tiket $t) {
            return [
                'id' => $t->id,
                'kode_tiket' => $t->kode_tiket,
                'no_berkas' => $t->no_berkas,
                'nama_pemohon' => $t->nama_pemohon,
                'status' => $t->status,
                'status_label' => $t->status_label,
                'status_badge' => $t->status_badge,
                'tanggal_masuk' => $t->tanggal_masuk->format('d/m/Y'),
                'jenis_permohonan' => $t->jenisPermohonan?->nama,
            ];
        });

        // Aktivitas terkini — 6 entri terakhir
        $recentActivities = RiwayatStatus::with(['tiket', 'user'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function (RiwayatStatus $r) {
                return [
                    'id' => $r->id,
                    'stage_dari' => $r->stage_dari,
                    'stage_ke' => $r->stage_ke,
                    'keterangan' => $r->keterangan,
                    'waktu' => $r->created_at->format('d/m/Y H:i'),
                    'tiket_kode' => $r->tiket?->kode_tiket,
                    'user_name' => $r->user?->name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_tiket' => $totalTiket,
                'tiket_hari_ini' => $tiketHariIni,
                'tiket_selesai' => $tiketSelesai,
                'tiket_ditolak' => $tiketDitolak,
                'stage_counts' => $stageCounts,
                'monthly_data' => $monthlyData,
                'recent_tikets' => $recentTikets,
                'recent_activities' => $recentActivities,
                'roles' => User::ROLES,
                'user' => $this->serializeUser($user),
            ],
        ]);
    }

    /** Serialisasi user yang aman untuk dikirim ke frontend. */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $user->role_label,
            'nip' => $user->nip,
            'no_hp' => $user->no_hp,
            'is_active' => $user->is_active,
        ];
    }
}
