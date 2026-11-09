<?php

namespace App\Http\Controllers;

use App\Models\ArsipTiket;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Daftar Arsip (Blueprint V2, modul 13).
 *
 * Arsip adalah turunan dari tiket `selesai` yang melewati batas hari
 * (`diarsipkan_pada` terisi). Halaman ini read-only: tidak ada aksi yang
 * mengubah status, menghapus, atau meminjamkan warkah.
 */
class ArsipController extends Controller
{
    private const ROLE_PENGLIHAT = ['loket_pendaftaran', 'loket_ploting', 'petugas_warkah'];

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || in_array($user->role, self::ROLE_PENGLIHAT, true), 403);

        $query = Tiket::arsip()->with(['jenisPermohonan', 'bidangTanahs', 'diserahkanOleh']);

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_permohonan_id', $request->integer('jenis'));
        }

        $this->terapkanTanggal($query, $request, 'diarsipkan_pada');

        $tikets = $query->orderByDesc('diarsipkan_pada')->paginate(20)->withQueryString();

        return view('arsip.index', [
            'tikets' => $tikets,
            'daftarJenis' => JenisPermohonan::orderBy('nama')->get(),
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    public function show(Tiket $tiket): View
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || in_array($user->role, self::ROLE_PENGLIHAT, true), 403);
        abort_unless($tiket->is_arsip, 404);

        $tiket->load([
            'jenisPermohonan',
            'bidangTanahs',
            'diserahkanOleh',
            'riwayatStatuses.user',
            'arsips.folder',
        ]);

        return view('arsip.show', compact('tiket'));
    }

    /** @return array{total:int, bulan_ini:int, folder:int} */
    private function ringkasan(): array
    {
        return [
            'total' => Tiket::arsip()->count(),
            'bulan_ini' => Tiket::arsip()
                ->whereBetween('diarsipkan_pada', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'folder' => ArsipTiket::whereNotNull('folder_id')->count(),
        ];
    }

    private function terapkanTanggal(Builder $query, Request $request, string $kolom): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->whereDate($kolom, '>=', Carbon::parse($request->string('tanggal_dari')->toString()));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate($kolom, '<=', Carbon::parse($request->string('tanggal_sampai')->toString()));
        }
    }
}
