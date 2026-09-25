<?php

namespace App\Http\Controllers;

use App\Models\SaranKoreksi;
use App\Models\Tiket;
use App\Services\TiketFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * StageWorkflow — perilaku umum seluruh tahap pelaksana (Verifikator, Warkah,
 * Validator BT/SU, Alih Media BT/SU) pada pola pull-based:
 *
 *   index   → Dashboard tahap (info jumlah)
 *   search  → Smart Search di Database Admin
 *   add     → Add / claim tiket ke antrian pribadi
 *   show    → Lihat detail tiket + lembar kerja tahap
 *   selesai → Done → kembali ke DB Admin
 *   revisi  → Kembalikan untuk perbaikan
 */
abstract class StageWorkflow extends Controller
{
    protected TiketFlowService $flow;

    abstract protected function stageName(): string;

    abstract protected function stageLabel(): string;

    abstract protected function viewBase(): string;

    public function __construct()
    {
        $this->flow = app(TiketFlowService::class);
    }

    /** Info counting untuk dashboard tahap. */
    protected function stats(): array
    {
        $user = Auth::user();
        $stage = $this->stageName();

        return [
            'total_db' => $this->flow->availableTikets($stage)->count(),
            'active' => $this->flow->activeTikets($user)->count(),
            'history' => $this->flow->historyTikets($user)->count(),
            'lengkap' => Tiket::where('status', 'selesai')->count(),
            'revisi' => Tiket::where('status', 'dikembalikan')->count(),
        ];
    }

    /** Dashboard tahap: antrian aktif + jumlah tersedia di DB Admin. */
    public function index(Request $request)
    {
        $user = Auth::user();
        $stage = $this->stageName();

        $activeTikets = $this->flow->activeTikets($user)
            ->with(['penugasans.user', 'riwayatStatuses'])
            ->get();

        $stats = $this->stats();
        $history = $this->flow->historyTikets($user)
            ->take(5)
            ->with('penugasans')
            ->get();

        // Menu "Revisi" per akun (dokumen Final-Rizki): revisi yang menunggu tahap saya.
        $revisiMenunggu = $this->flow->revisiForMe($user)->get();

        return view($this->viewBase().'.index', compact('activeTikets', 'stats', 'history', 'stage', 'revisiMenunggu'))
            ->with('stageLabel', $this->stageLabel());
    }

    /**
     * Smart Search di Database Admin (tanpa reload, via partial).
     */
    public function search(Request $request)
    {
        $stage = $this->stageName();
        $search = $request->input('q');
        $tikets = $this->flow->availableTikets($stage, $search)->take(25)->get();

        // Alih Media: tiket Validator yang belum selesai tetap TAMPIL tapi terkunci.
        if (in_array($stage, ['alih_media_btel', 'alih_media_suel'], true)) {
            foreach ($tikets as $row) {
                $row->setAttribute('locked', ! $row->isValidasiSelesai());
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['tikets' => $tikets->map(fn ($t) => $this->serializeForSearch($t))]);
        }

        return view($this->viewBase().'.search', compact('tikets', 'search', 'stage'));
    }

    /** Add tiket dari DB Admin ke antrian user (anti-duplikat via service). */
    public function add(Request $request, int $id)
    {
        $tiket = Tiket::with('bidangTanahs')->findOrFail($id);

        try {
            $this->flow->claim(Auth::user(), $tiket);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($this->viewBase().'.index')
            ->with('success', "Berkas {$tiket->kode_tiket} berhasil di-Add ke antrian pekerjaan Anda.");
    }

    /** Lihat detail tiket + lembar kerja tahap. */
    public function show(int $id)
    {
        $user = Auth::user();
        $stage = $this->stageName();

        $tiket = Tiket::with([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'penugasans.user',
            'riwayatStatuses.user',
            'catatanRevisis',
        ])->findOrFail($id);

        $isActive = (bool) $tiket->penugasanAktif($stage);
        $mine = $tiket->penugasanAktif($stage)?->user_id === $user->id;

        $templateKoreksis = SaranKoreksi::where('jenis_permohonan_id', $tiket->jenis_permohonan_id)->get();

        return view($this->viewBase().'.show', compact('tiket', 'isActive', 'mine', 'templateKoreksis', 'stage'))
            ->with('stageLabel', $this->stageLabel());
    }

    /** Selesai memproses → tiket kembali ke DB Admin (atau selesai total). */
    public function selesai(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $catatan = $request->input('catatan');

        try {
            $this->flow->done(Auth::user(), $tiket, $catatan);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = $tiket->status === 'selesai'
            ? "Berkas {$tiket->kode_tiket} telah SELESAI 100% (semua tahap termasuk BT & SU beres)."
            : "Berkas {$tiket->kode_tiket} dinyatakan selesai dan kembali ke Database Admin.";

        return redirect()->route($this->viewBase().'.index')->with('success', $msg);
    }

    /** Kembalikan tiket untuk revisi (dikembalikan ke tahap terkait / loket). */
    public function revisi(Request $request, int $id)
    {
        $request->validate([
            'isi_revisi' => 'required|string|min:3',
            'ke_stage' => 'nullable|string',
        ]);

        $tiket = Tiket::findOrFail($id);

        try {
            $this->flow->returnForRevisi(
                Auth::user(),
                $tiket,
                $request->input('isi_revisi'),
                $request->input('ke_stage', 'admin')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route($this->viewBase().'.index')
            ->with('warning', "Berkas {$tiket->kode_tiket} dikembalikan untuk revisi ke-{$tiket->revisi_ke}.");
    }

    /** Cetak Form Permohonan Perbaikan (printable) — dokumen FINAL. */
    public function printPerbaikan(int $id)
    {
        $tiket = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'penugasans.user',
            'catatanRevisis.pengirim',
        ])->findOrFail($id);

        $lastRevisi = $tiket->catatanRevisis()->latest('id')->first();

        return view('partials.print_perbaikan', compact('tiket', 'lastRevisi'));
    }

    /** Keluarkan tiket dari antrian tanpa menyelesaikan (kembali ke DB Admin). */
    public function lepas(Request $request, int $id)
    {
        $user = Auth::user();
        $stage = $this->stageName();
        $tiket = Tiket::findOrFail($id);

        $penugasan = $tiket->penugasanAktif($stage);
        if (! $penugasan || $penugasan->user_id !== $user->id) {
            return back()->with('error', 'Berkas tidak sedang dalam antrian Anda.');
        }

        $penugasan->update(['status' => 'batal']);
        $tiket->update(['status' => 'diterima']);

        return redirect()
            ->route($this->viewBase().'.index')
            ->with('warning', "Berkas {$tiket->kode_tiket} dilepas kembali ke Database Admin.");
    }

    protected function serializeForSearch(Tiket $tiket): array
    {
        $isAlihMedia = in_array($this->stageName(), ['alih_media_btel', 'alih_media_suel'], true);

        return [
            'id' => $tiket->id,
            'kode_tiket' => $tiket->kode_tiket,
            'nama_pemohon' => $tiket->nama_pemohon,
            'nik_pemohon' => $tiket->nik_pemohon,
            'status' => $tiket->status,
            'status_label' => $tiket->status_label,
            'status_badge' => $tiket->status_badge,
            'jumlah_bidang' => $tiket->jumlah_bidang,
            'jenis' => $tiket->jenisPermohonan?->nama,
            'tanggal_masuk' => $tiket->tanggal_masuk?->format('d/m/Y'),
            // Alih Media MELIHAT tiket Validator yang belum selesai, tetapi terkunci (Add nonaktif).
            'locked' => $isAlihMedia && ! $tiket->isValidasiSelesai(),
        ];
    }
}
