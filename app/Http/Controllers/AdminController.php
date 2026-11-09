<?php

namespace App\Http\Controllers;

use App\Models\ArsipFolder;
use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Monitoring & manajemen Admin (read-only untuk berkas).
 *
 * Admin hanya dapat melihat daftar/detail berkas, daftar pengaduan, serta
 * mengelola akun, form pendaftaran, setting, arsip folder, dan log.
 */
class AdminController extends Controller
{
    /**
     * Database Berkas — milik Admin, semua tiket terpusat di sini.
     * Status `di_database` = sedang menunggu di-ADD oleh Seksi 1/2.
     */
    public function index(Request $request)
    {
        $query = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'petugasLoket',
            'penanggungJawab',
            'warkahPermintaans',
        ]);

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->cariNomor($s)
                    ->orWhere('no_berkas', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }

        $tikets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => Tiket::count(),
            'menunggu' => Tiket::where('status', 'di_database')->count(),
            'proses' => Tiket::where('status', 'diproses')->count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'ditolak' => Tiket::where('status', 'ditolak')->count(),
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        return view('admin.index', compact('tikets', 'stats'));
    }

    // ---------------- Menu Selesai (migrasi ke arsip) ----------------
    /** Query terpusat pencarian tiket berstatus "selesai" (monitoring read-only). */
    private function filterSelesaiQuery(Request $request): Builder
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'arsips'])
            ->where('status', 'selesai');

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->cariNomor($s)
                    ->orWhere('no_berkas', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_selesai', $request->integer('tahun'));
        }

        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->integer('jenis_permohonan_id'));
        }

        if ($request->filled('jenis_hak')) {
            $query->whereHas('bidangTanahs', fn ($q) => $q->where('jenis_hak', $request->jenis_hak));
        }

        if ($request->filled('petugas')) {
            $query->where('petugas_loket_id', $request->integer('petugas'));
        }

        return $query;
    }

    /** Daftar tahun pada tanggal_selesai untuk dropdown filter. */
    private function tahunSelesaiOptions(): array
    {
        return Tiket::query()
            ->where('status', 'selesai')
            ->whereNotNull('tanggal_selesai')
            ->orderByDesc('tanggal_selesai')
            ->pluck('tanggal_selesai')
            ->map(fn ($tanggal) => (int) Carbon::parse($tanggal)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /** Daftar petugas loket yang tercatat sebagai petugas_loket pada tiket selesai. */
    private function petugasSelesaiOptions(): array
    {
        return User::query()
            ->whereIn('id', Tiket::where('status', 'selesai')->whereNotNull('petugas_loket_id')->distinct()->pluck('petugas_loket_id'))
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function selesai(Request $request)
    {
        $tikets = $this->filterSelesaiQuery($request)
            ->orderByDesc('tanggal_selesai')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.selesai', [
            'tikets' => $tikets,
            'tahuns' => $this->tahunSelesaiOptions(),
            'jenisPermohonans' => JenisPermohonan::where('is_active', true)->orderBy('nama')->get(),
            'jenisHaks' => JenisHak::where('is_active', true)->orderBy('urutan')->orderBy('kode')->get(),
            'petugasList' => $this->petugasSelesaiOptions(),
        ]);
    }

    public function show(int $id)
    {
        $tiket = Tiket::with([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'penanggungJawab',
            'riwayatStatuses.user',
            'arsips',
        ])->findOrFail($id);

        return view('admin.show', compact('tiket'));
    }

    // ---------------- Manajemen Arsip Folder ----------------
    public function arsipIndex()
    {
        $folders = ArsipFolder::withCount('arsipTikets')->get();

        return view('admin.arsip', compact('folders'));
    }

    public function arsipStore(Request $request)
    {
        $request->validate([
            'nama_folder' => 'required|string|max:200',
            'jenis_dokumen' => 'nullable|string|max:100',
            'lokasi_fisik' => 'nullable|string|max:200',
        ]);

        ArsipFolder::create($request->only(['nama_folder', 'jenis_dokumen', 'lokasi_fisik']));

        return back()->with('success', 'Folder arsip berhasil ditambahkan.');
    }

    // Manajemen Akun (index/store/update/toggle/hapus) dipindahkan ke
    // AccountController agar akun super_admin selalu tersembunyi dari Admin.
}
