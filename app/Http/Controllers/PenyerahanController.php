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
 * Loket Penyerahan (Blueprint V2, modul 07).
 *
 * Pola: Antrian Penyerahan → ADD → SELESAI. Satu-satunya aksi penutup,
 * tidak ada Tolak. Tiket yang dipegang memakai `penanggung_jawab_id` +
 * `diambil_pada` dengan status `diproses`, lalu `selesai` setelah diserahkan.
 */
class PenyerahanController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan() || $user->isAdmin(), 403);

        $adaFilter = $request->filled('q')
            || $request->filled('tanggal_dari')
            || $request->filled('tanggal_sampai');

        // Blueprint V2: Antrian Penyerahan kosong sampai ada pencarian.
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penanggungJawab']);

        if (! $adaFilter) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('status', ['di_database', 'diproses']);

            if ($request->filled('q')) {
                $query->cariPintar($request->string('q')->toString());
            }

            $this->terapkanTanggal($query, $request, 'tanggal_masuk');
        }

        $tikets = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('penyerahan.index', [
            'tikets' => $tikets,
            'adaFilter' => $adaFilter,
            'ringkasan' => $this->ringkasan($user),
        ]);
    }

    public function tiketSaya(): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan() || $user->isAdmin(), 403);

        $tikets = Tiket::with(['jenisPermohonan', 'bidangTanahs'])
            ->where('penanggung_jawab_id', $user->id)
            ->where('status', 'diproses')
            ->orderBy('diambil_pada')
            ->get();

        return view('penyerahan.tiket_saya', compact('tikets'));
    }

    public function add(Tiket $tiket): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan(), 403);

        if (! $tiket->bisaDiAddPenyerahan($user)) {
            return back()->with('error', "Berkas {$tiket->kode_tiket} tidak dapat di-ADD. "
                .($tiket->penanggung_jawab_id !== null
                    ? 'Sedang dipegang '.($tiket->penanggungJawab?->name ?? 'petugas lain').'.'
                    : 'Status berkas saat ini: '.$tiket->status_label.'.'));
        }

        $belumDiprosesSeksi = ! $tiket->pernahDiprosesSeksi();

        DB::transaction(function () use ($user, $tiket) {
            $tiket->update([
                'status' => 'diproses',
                'penanggung_jawab_id' => $user->id,
                'diambil_pada' => now(),
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'di_database',
                'stage_ke' => 'diproses',
                'changed_by' => $user->id,
                'keterangan' => 'ADD untuk penyerahan berkas.',
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'tiket.diambil',
            penerimaIds: $this->notifikasi->idKasiSeksi($this->notifikasi->seksiTiket($tiket)),
            judul: "Berkas {$tiket->kode_tiket} diambil Loket Penyerahan",
            pesan: "{$user->name} menyiapkan penyerahan berkas kepada pemohon.",
            url: route('seksi.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        $this->notifikasi->kirim(
            tipe: 'tiket.diteruskan',
            penerimaIds: $this->notifikasi->idStafTerkait($tiket, $user->id),
            judul: "Berkas {$tiket->kode_tiket} sampai di Loket Penyerahan",
            pesan: "{$user->name} menyiapkan penyerahan berkas kepada pemohon.",
            url: route('seksi.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        $response = redirect()->route('penyerahan.tiket-saya')
            ->with('success', "Berkas {$tiket->kode_tiket} di-ADD. Cocokkan identitas pemohon lalu SERAHKAN.");

        if ($belumDiprosesSeksi) {
            $response->with('warning', "Perhatian: {$tiket->kode_tiket} belum memiliki riwayat pengerjaan Seksi 1/2. Pastikan berkas memang sudah tuntas.");
        }

        return $response;
    }

    public function selesaiProses(Request $request, Tiket $tiket): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan(), 403);

        $validated = $request->validate([
            'penerima_nama' => ['required', 'string', 'min:3', 'max:150'],
            'penerima_hubungan' => ['required', Rule::in(array_keys(Tiket::HUBUNGAN_PENERIMA))],
            'penerima_no_identitas' => [
                Rule::requiredIf(fn () => $request->input('penerima_hubungan') === 'kuasa'),
                'nullable', 'string', 'max:50',
            ],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($tiket->status !== 'diproses' || $tiket->penanggung_jawab_id !== $user->id) {
            return back()->with('error', 'Berkas ini tidak sedang Anda pegang.');
        }

        DB::transaction(function () use ($user, $tiket, $validated) {
            $tiket->update([
                'status' => 'selesai',
                'tanggal_selesai' => now()->toDateString(),
                'penerima_nama' => $validated['penerima_nama'],
                'penerima_hubungan' => $validated['penerima_hubungan'],
                'penerima_no_identitas' => $validated['penerima_no_identitas'] ?? null,
                'catatan_penyerahan' => $validated['catatan'] ?? null,
                'diserahkan_pada' => now(),
                'diserahkan_oleh_id' => $user->id,
                'penanggung_jawab_id' => null,
                'diambil_pada' => null,
            ]);

            $hubungan = $tiket->penerima_hubungan_label;
            $catatan = $validated['catatan'] ?? null;

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Penyerahan',
                'stage_ke' => 'selesai',
                'changed_by' => $user->id,
                'keterangan' => "SELESAI — diserahkan kepada {$validated['penerima_nama']} ({$hubungan})"
                    .($catatan !== null && $catatan !== '' ? ". Catatan: {$catatan}" : '.'),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'tiket.selesai',
            penerimaIds: array_merge(
                [$tiket->created_by],
                $this->notifikasi->idKasiSeksi($this->notifikasi->seksiTiket($tiket)),
                $this->notifikasi->idStafTerkait($tiket),
            ),
            judul: "Tiket {$tiket->kode_tiket} selesai",
            pesan: 'Berkas sudah diserahkan kepada '.$tiket->penerima_nama.' ('.$tiket->penerima_hubungan_label.').',
            url: route('penyerahan.show', $tiket),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        return redirect()->route('penyerahan.selesai')
            ->with('success', "Berkas {$tiket->kode_tiket} SELESAI diserahkan kepada {$tiket->penerima_nama}.");
    }

    public function selesai(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan() || $user->isAdmin(), 403);

        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'diserahkanOleh'])
            ->where('status', 'selesai');

        if (! $user->isAdmin()) {
            $query->where('diserahkan_oleh_id', $user->id);
        }

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        $this->terapkanTanggal($query, $request, 'diserahkan_pada');

        $tikets = $query->orderByDesc('diserahkan_pada')->paginate(20)->withQueryString();

        return view('penyerahan.selesai', compact('tikets'));
    }

    public function show(Tiket $tiket): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPenyerahan() || $user->isAdmin(), 403);

        $dalamLingkup = $user->isAdmin()
            || $tiket->penanggung_jawab_id === null
            || $tiket->penanggung_jawab_id === $user->id
            || $tiket->status === 'selesai';

        abort_unless($dalamLingkup, 403);

        $tiket->load(['jenisPermohonan', 'bidangTanahs', 'penanggungJawab', 'riwayatStatuses.user']);

        return view('penyerahan.show', [
            'tiket' => $tiket,
            'bisaSerah' => $user->isLoketPenyerahan()
                && $tiket->status === 'diproses'
                && $tiket->penanggung_jawab_id === $user->id,
            'hubunganPenerima' => Tiket::HUBUNGAN_PENERIMA,
        ]);
    }

    /** @return array{antrean:int, diproses:int, hari_ini:int} */
    private function ringkasan(User $user): array
    {
        return [
            'antrean' => Tiket::where('status', 'di_database')->whereNull('penanggung_jawab_id')->count(),
            'diproses' => Tiket::where('penanggung_jawab_id', $user->id)->where('status', 'diproses')->count(),
            'hari_ini' => Tiket::where('diserahkan_oleh_id', $user->id)
                ->whereDate('diserahkan_pada', now()->toDateString())
                ->count(),
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
