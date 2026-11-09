<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Monitoring Kakan (Blueprint V2): seluruh alur dibaca dari status tiket dan
 * pemegang tiket saat ini — bukan dari penugasan per tahap seperti alur lama.
 */
class KakanController extends Controller
{
    public function index(Request $request): View
    {
        $statusCounts = [
            'di_database' => Tiket::where('status', 'di_database')->count(),
            'diproses' => Tiket::where('status', 'diproses')->count(),
            'ditolak' => Tiket::where('status', 'ditolak')->count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        $perSeksi = $this->ringkasanSeksi();

        $query = Tiket::with(['jenisPermohonan', 'petugasLoket', 'penanggungJawab'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('seksi')) {
            $seksi = $request->string('seksi')->toString();
            $query->whereHas('penanggungJawab', fn (Builder $q) => $q->where('seksi', $seksi));
        }

        $tikets = $query->paginate(20)->withQueryString();

        $activities = RiwayatStatus::with(['tiket', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('kakan.index', [
            'statusCounts' => $statusCounts,
            'perSeksi' => $perSeksi,
            'tikets' => $tikets,
            'activities' => $activities,
            'totalTiket' => array_sum($statusCounts),
        ]);
    }

    public function show(Tiket $tiket): View
    {
        $tiket->load([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'petugasLoket',
            'penanggungJawab',
            'creator',
            'riwayatStatuses.user',
        ]);

        return view('kakan.show', compact('tiket'));
    }

    /**
     * Jumlah tiket yang sedang dipegang tiap seksi, dikelompokkan per seksi
     * staf (1–5/TU). Hanya tiket berstatus `diproses` yang punya pemegang.
     *
     * @return list<array{seksi: string, label: string, jumlah: int}>
     */
    private function ringkasanSeksi(): array
    {
        $jumlah = Tiket::query()
            ->where('status', 'diproses')
            ->whereNotNull('penanggung_jawab_id')
            ->join('users', 'users.id', '=', 'tikets.penanggung_jawab_id')
            ->selectRaw('users.seksi as seksi, COUNT(*) as jumlah')
            ->groupBy('users.seksi')
            ->pluck('jumlah', 'seksi');

        $hasil = [];

        foreach (User::SEKSI as $kode => $label) {
            $hasil[] = [
                'seksi' => (string) $kode,
                'label' => $label,
                'jumlah' => (int) ($jumlah[$kode] ?? 0),
            ];
        }

        return $hasil;
    }
}
