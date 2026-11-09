<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Back office Seksi 1 & 2 (Blueprint V2, modul 04–05).
 *
 * Staf Seksi memakai pola Cari → ADD → proses → KIRIM (Selesai/Tolak).
 * Kasi Seksi hanya memantau tiket yang sudah di-ADD staf seksinya.
 */
class SeksiController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isKasiSeksi()) {
            return $this->indexKasi($request, $user);
        }

        abort_unless($user->isStafSeksi(), 403);

        $adaFilter = $request->filled('q')
            || $request->filled('status')
            || $request->filled('tanggal_dari')
            || $request->filled('tanggal_sampai');

        // Blueprint V2: daftar kosong sampai ada pencarian.
        $query = $adaFilter
            ? Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penanggungJawab', 'warkahPermintaans'])
            : Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penanggungJawab', 'warkahPermintaans'])->whereRaw('1 = 0');

        if ($adaFilter) {
            $query->whereIn('status', ['di_database', 'diproses', 'ditolak']);

            if ($request->filled('q')) {
                $query->cariPintar($request->string('q')->toString());
            }

            if ($request->filled('status')) {
                $query->where('status', $request->string('status')->toString());
            }

            $this->terapkanTanggal($query, $request, 'tanggal_masuk');
        }

        $tikets = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('seksi.index', [
            'tikets' => $tikets,
            'adaFilter' => $adaFilter,
            'ringkasan' => $this->ringkasanStaf($user),
        ]);
    }

    public function tiketSaya(): View
    {
        $user = Auth::user();
        abort_unless($user->isStafSeksi(), 403);

        $tikets = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'warkahPermintaans'])
            ->where('penanggung_jawab_id', $user->id)
            ->where('status', 'diproses')
            ->orderBy('diambil_pada')
            ->get();

        return view('seksi.tiket_saya', compact('tikets'));
    }

    public function ditolak(Request $request): View
    {
        $user = Auth::user();
        $query = Tiket::with(['jenisPermohonan', 'penanggungJawab', 'bidangTanahs'])
            ->whereNotNull('ditolak_pada');

        if ($user->isKasiSeksi()) {
            $query->whereHas('penanggungJawab', fn (Builder $q) => $q
                ->where('role', 'staf_seksi')
                ->where('seksi', $user->seksi));
        } else {
            abort_unless($user->isStafSeksi(), 403);
            $query->where('penanggung_jawab_id', $user->id);
        }

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        $this->terapkanTanggal($query, $request, 'ditolak_pada');

        $tikets = $query->orderByDesc('ditolak_pada')->paginate(20)->withQueryString();

        return view('seksi.ditolak', compact('tikets'));
    }

    public function selesai(Request $request): View
    {
        $user = Auth::user();

        if ($user->isKasiSeksi() && ! $user->canViewPermohonan()) {
            abort(403, 'Akses riwayat selesai permohonan hanya untuk Kasi Seksi 1 dan 2. Silakan gunakan menu Pengaduan.');
        }

        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs'])
            ->where('status', 'selesai')
            ->whereHas('riwayatStatuses', function (Builder $q) use ($user) {
                if ($user->isKasiSeksi()) {
                    $q->whereIn('changed_by', $this->stafIdsSeksi($user));
                } else {
                    abort_unless($user->isStafSeksi(), 403);
                    $q->where('changed_by', $user->id);
                }
            });

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        $this->terapkanTanggal($query, $request, 'tanggal_masuk');

        $tikets = $query->orderByDesc('tanggal_selesai')->paginate(20)->withQueryString();

        return view('seksi.selesai', compact('tikets'));
    }

    public function show(Tiket $tiket): View
    {
        $user = Auth::user();

        if ($user->isKasiSeksi() && ! $user->canViewPermohonan()) {
            abort(403, 'Akses detail permohonan hanya untuk Kasi Seksi 1 dan 2. Silakan gunakan menu Pengaduan.');
        }

        if ($user->isKasiSeksi()) {
            $this->pastikanLingkupKasi($tiket, $user);
        } else {
            abort_unless($user->isStafSeksi(), 403);
        }

        $tiket->load([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'penanggungJawab',
            'warkahPermintaans',
            'riwayatStatuses.user',
        ]);

        $bisaProses = $user->isStafSeksi()
            && $tiket->status === 'diproses'
            && $tiket->penanggung_jawab_id === $user->id;

        return view('seksi.show', [
            'tiket' => $tiket,
            'bisaProses' => $bisaProses,
            'peranBidang' => Tiket::PERAN_BIDANG,
        ]);
    }

    public function add(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isStafSeksi(), 403);

        $validated = $request->validate([
            'peran_bidang' => ['required', Rule::in(array_keys(Tiket::PERAN_BIDANG))],
        ]);

        if (! $tiket->bisaDiAddOleh($user)) {
            return back()->with('error', "Berkas {$tiket->kode_tiket} tidak dapat di-ADD: ".$tiket->alasanTidakBisaDiAdd($user));
        }

        $dariStatus = $tiket->status;

        DB::transaction(function () use ($user, $tiket, $validated, $dariStatus) {
            $dari = $dariStatus;

            $tiket->update([
                'status' => 'diproses',
                'penanggung_jawab_id' => $user->id,
                'diambil_pada' => now(),
                'peran_bidang' => $validated['peran_bidang'],
                'dilepas_admin_at' => null,
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => $dari,
                'stage_ke' => 'diproses',
                'changed_by' => $user->id,
                'keterangan' => ($dari === 'ditolak' ? 'ADD ULANG' : 'ADD')
                    ." — {$tiket->peran_bidang_label}.",
            ]);
        });

        $this->notifikasi->kirim(
            tipe: $dariStatus === 'ditolak' ? 'tiket.ditolak_ulang' : 'tiket.diambil',
            penerimaIds: $this->notifikasi->idKasiSeksi($user->seksi),
            judul: $dariStatus === 'ditolak'
                ? "Tiket {$tiket->kode_tiket} diproses kembali"
                : "Staf Anda mengambil tiket {$tiket->kode_tiket}",
            pesan: "{$user->name} (".$tiket->peran_bidang_label.') mulai mengerjakan berkas.',
            url: route('seksi.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        $this->notifikasi->kirim(
            tipe: 'tiket.diteruskan',
            penerimaIds: $this->notifikasi->idStafTerkait($tiket, $user->id),
            judul: "Berkas {$tiket->kode_tiket} kini di akun lain",
            pesan: "{$user->name} mengambil alih berkas di Seksi {$user->seksi}.",
            url: route('seksi.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        return redirect()->route('seksi.tiket-saya')
            ->with('success', "Berkas {$tiket->kode_tiket} berhasil di-ADD. Kerjakan lalu KIRIM (Selesai/Tolak).");
    }

    public function selesaiProses(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isStafSeksi(), 403);

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        if ($tiket->status !== 'diproses' || $tiket->penanggung_jawab_id !== $user->id) {
            return back()->with('error', 'Berkas ini tidak sedang Anda proses.');
        }

        DB::transaction(function () use ($user, $tiket, $validated) {
            $peran = $tiket->peran_bidang_label;

            $tiket->update([
                'status' => 'di_database',
                'penanggung_jawab_id' => null,
                'diambil_pada' => null,
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Seksi 1/2',
                'stage_ke' => 'di_database',
                'changed_by' => $user->id,
                'keterangan' => "KIRIM — Selesai ({$peran}). Catatan: {$validated['catatan']}",
            ]);
        });

        return redirect()->route('seksi.tiket-saya')
            ->with('success', "Berkas {$tiket->kode_tiket} selesai dan kembali ke Database.");
    }

    public function tolak(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isStafSeksi(), 403);

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        if ($tiket->status !== 'diproses' || $tiket->penanggung_jawab_id !== $user->id) {
            return back()->with('error', 'Berkas ini tidak sedang Anda proses.');
        }

        DB::transaction(function () use ($user, $tiket, $validated) {
            $peran = $tiket->peran_bidang_label;

            $tiket->update([
                'status' => 'ditolak',
                'ditolak_pada' => now(),
                'alasan_penolakan' => $validated['catatan'],
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Seksi 1/2',
                'stage_ke' => 'ditolak',
                'changed_by' => $user->id,
                'keterangan' => "KIRIM — Tolak ({$peran}). Alasan: {$validated['catatan']}",
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'tiket.ditolak',
            penerimaIds: array_merge(
                [$tiket->created_by],
                $this->notifikasi->idKasiSeksi($user->seksi),
                $this->notifikasi->idStafTerkait($tiket, $user->id),
            ),
            judul: "Tiket {$tiket->kode_tiket} ditolak",
            pesan: "{$tiket->kode_tiket} perlu perbaikan sebelum ADD ULANG. Alasan tersimpan di detail berkas.",
            url: route('seksi.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        return redirect()->route('seksi.tiket-saya')
            ->with('warning', "Berkas {$tiket->kode_tiket} DITOLAK dan menjadi tanggung jawab Anda. Buka Daftar Ditolak untuk ADD ULANG setelah diperbaiki.");
    }

    /** Daftar Permohonan Seksi (Kasi, read-only) — hanya tiket yang sudah di-ADD staf seksinya. */
    private function indexKasi(Request $request, User $user): View
    {
        // Hanya Kasi Seksi 1 dan 2 yang boleh melihat daftar permohonan/tiket
        if (! $user->canViewPermohonan()) {
            abort(403, 'Akses daftar permohonan hanya untuk Kasi Seksi 1 dan 2. Silakan gunakan menu Pengaduan.');
        }

        $stafIds = $this->stafIdsSeksi($user);

        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penanggungJawab'])
            ->whereIn('penanggung_jawab_id', $stafIds)
            ->whereIn('status', ['diproses', 'ditolak']);

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $this->terapkanTanggal($query, $request, 'diambil_pada');

        $tikets = $query->orderByDesc('diambil_pada')->paginate(20)->withQueryString();

        return view('seksi.index_kasi', [
            'tikets' => $tikets,
            'ringkasan' => [
                'diproses' => Tiket::whereIn('penanggung_jawab_id', $stafIds)->where('status', 'diproses')->count(),
                'ditolak' => Tiket::whereIn('penanggung_jawab_id', $stafIds)->where('status', 'ditolak')->count(),
                'macet' => Tiket::whereIn('penanggung_jawab_id', $stafIds)
                    ->where('status', 'diproses')
                    ->where('diambil_pada', '<', now()->subDays((int) config('loket.batas_macet_hari', 3)))
                    ->count(),
            ],
        ]);
    }

    /** @return array{diproses:int, ditolak:int, macet:int} */
    private function ringkasanStaf(User $user): array
    {
        return [
            'di_database' => Tiket::where('status', 'di_database')->count(),
            'diproses' => Tiket::where('penanggung_jawab_id', $user->id)->where('status', 'diproses')->count(),
            'ditolak' => Tiket::where('penanggung_jawab_id', $user->id)->whereNotNull('ditolak_pada')->count(),
            'macet' => Tiket::where('penanggung_jawab_id', $user->id)
                ->where('status', 'diproses')
                ->where('diambil_pada', '<', now()->subDays((int) config('loket.batas_macet_hari', 3)))
                ->count(),
        ];
    }

    /** @return list<int> */
    private function stafIdsSeksi(User $user): array
    {
        return User::where('role', 'staf_seksi')
            ->where('seksi', $user->seksi)
            ->pluck('id')
            ->all();
    }

    private function pastikanLingkupKasi(Tiket $tiket, User $user): void
    {
        $stafIds = $this->stafIdsSeksi($user);
        $dalamLingkup = ($tiket->penanggung_jawab_id !== null
                && in_array($tiket->penanggung_jawab_id, $stafIds, true))
            || $tiket->riwayatStatuses()->whereIn('changed_by', $stafIds)->exists();

        abort_unless($dalamLingkup, 403);
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
