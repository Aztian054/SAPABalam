<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\WarkahPermintaan;
use App\Models\WarkahPermintaanRiwayat;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pengelolaan pinjaman warkah fisik (Blueprint V2, modul 06).
 *
 * Petugas Warkah menangani permintaan hasil Tahap 4:
 * diminta → diterima → siap → dipinjam (→ terlambat) → dikembalikan.
 * Perpanjangan hanya lewat UBAH BATAS + alasan, dengan riwayat.
 */
class WarkahPermintaanController extends Controller
{
    private const KONDISI = [
        'baik' => 'Baik',
        'rusak' => 'Rusak',
        'tidak_lengkap' => 'Tidak Lengkap',
    ];

    public function index(Request $request): View
    {
        $this->pastikanPetugas();

        $query = WarkahPermintaan::with(['tiket.jenisPermohonan'])
            ->whereIn('status', ['diminta', 'diterima', 'siap']);

        $this->terapkanFilter($query, $request);

        $permintaans = $query->orderBy('tanggal_permintaan')->orderBy('id')->paginate(20)->withQueryString();

        return view('warkah_permintaan.index', [
            'permintaans' => $permintaans,
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    public function pinjaman(Request $request): View
    {
        $this->pastikanPetugas();

        $query = WarkahPermintaan::with(['tiket.jenisPermohonan', 'peminjam'])
            ->sedangDipinjam()
            ->orderBy('batas_kembali');

        $this->terapkanFilter($query, $request);

        if ($request->boolean('terlambat')) {
            $query->whereDate('batas_kembali', '<', now()->toDateString());
        }

        $pinjamen = $query->paginate(20)->withQueryString();

        return view('warkah_permintaan.pinjaman', [
            'pinjamen' => $pinjamen,
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    public function dikembalikan(Request $request): View
    {
        $this->pastikanPetugas();

        $query = WarkahPermintaan::with(['tiket.jenisPermohonan', 'peminjam'])
            ->where('status', 'dikembalikan')
            ->orderByDesc('tanggal_kembali');

        $this->terapkanFilter($query, $request);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_kembali', '>=', Carbon::parse($request->string('tanggal_dari')->toString()));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_kembali', '<=', Carbon::parse($request->string('tanggal_sampai')->toString()));
        }

        $pengembalians = $query->paginate(20)->withQueryString();

        return view('warkah_permintaan.dikembalikan', [
            'pengembalians' => $pengembalians,
            'ringkasan' => $this->ringkasan(),
        ]);
    }

    public function show(WarkahPermintaan $warkahPermintaan): View
    {
        $this->pastikanPetugas();

        $warkahPermintaan->load(['tiket.jenisPermohonan', 'tiket.bidangTanahs', 'peminjam', 'riwayats.user']);

        $stafSeksi = User::where('role', 'staf_seksi')
            ->where('is_active', true)
            ->orderBy('seksi')
            ->orderBy('name')
            ->get();

        return view('warkah_permintaan.show', [
            'warkah' => $warkahPermintaan,
            'stafSeksi' => $stafSeksi,
            'kondisi' => self::KONDISI,
            'batasHari' => Setting::angka('batas_warkah_hari', 7),
        ]);
    }

    public function terima(WarkahPermintaan $warkahPermintaan): RedirectResponse
    {
        $this->pastikanPetugas();

        if ($warkahPermintaan->status !== 'diminta') {
            return back()->with('error', 'Hanya permintaan berstatus Diminta yang dapat diterima.');
        }

        $this->ubahStatus($warkahPermintaan, 'diterima', 'TERIMA', 'Berkas fisik sedang disiapkan.');

        return back()->with('success', "Warkah {$warkahPermintaan->jenis} untuk {$warkahPermintaan->tiket?->kode_tiket} diterima dan sedang disiapkan.");
    }

    public function siap(WarkahPermintaan $warkahPermintaan, NotifikasiService $notifikasi): RedirectResponse
    {
        $this->pastikanPetugas();

        if ($warkahPermintaan->status !== 'diterima') {
            return back()->with('error', 'Hanya permintaan berstatus Diterima yang dapat ditandai siap.');
        }

        $this->ubahStatus($warkahPermintaan, 'siap', 'TANDAI SIAP', 'Berkas siap diambil di ruang warkah.');

        $tiket = $warkahPermintaan->tiket;
        $pemegang = $tiket?->penanggungJawab;

        if ($tiket !== null) {
            $notifikasi->kirim(
                tipe: 'warkah.siap',
                penerimaIds: array_merge(
                    [$pemegang?->id],
                    $notifikasi->idKasiSeksi($pemegang?->seksi),
                ),
                judul: "Warkah {$warkahPermintaan->jenis} siap diambil",
                pesan: "Berkas {$tiket->kode_tiket} — silakan ambil di ruang warkah.",
                url: route('seksi.show', $tiket),
                objekTipe: 'warkah',
                objekId: $warkahPermintaan->id,
            );
        }

        return back()->with('success', "Warkah {$warkahPermintaan->jenis} ditandai SIAP DIAMBIL.");
    }

    public function serahkan(Request $request, WarkahPermintaan $warkahPermintaan, NotifikasiService $notifikasi): RedirectResponse
    {
        $this->pastikanPetugas();

        if ($warkahPermintaan->status !== 'siap') {
            return back()->with('error', 'Warkah hanya dapat diserahkan bila berstatus Siap Diambil.');
        }

        $validated = $request->validate([
            'peminjam_id' => ['nullable', 'integer', 'exists:users,id'],
            'nama_peminjam' => ['required_without:peminjam_id', 'nullable', 'string', 'max:200'],
            'seksi' => ['required_without:peminjam_id', 'nullable', Rule::in(array_keys(User::SEKSI))],
            'batas_kembali' => ['required', 'date', 'after:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $peminjam = isset($validated['peminjam_id'])
            ? User::where('role', 'staf_seksi')->find($validated['peminjam_id'])
            : null;

        if (isset($validated['peminjam_id']) && $peminjam === null) {
            throw ValidationException::withMessages([
                'peminjam_id' => 'Peminjam harus akun Staf Seksi yang aktif.',
            ]);
        }

        DB::transaction(function () use ($warkahPermintaan, $validated, $peminjam) {
            $dari = $warkahPermintaan->status;

            $warkahPermintaan->update([
                'status' => 'dipinjam',
                'peminjam_id' => $peminjam?->id,
                'nama_peminjam' => $peminjam?->name ?? $validated['nama_peminjam'],
                'seksi' => $peminjam?->seksi ?? $validated['seksi'],
                'tanggal_pinjam' => now()->toDateString(),
                'batas_kembali' => $validated['batas_kembali'],
                'catatan' => $validated['catatan'] ?? $warkahPermintaan->catatan,
            ]);

            $this->catatRiwayat(
                $warkahPermintaan,
                'SERAHKAN',
                $dari,
                'dipinjam',
                "Diserahkan kepada {$warkahPermintaan->nama_peminjam} (".$warkahPermintaan->seksi."). Batas kembali: {$validated['batas_kembali']}.",
            );
        });

        $notifikasi->kirim(
            tipe: 'warkah.dipinjam',
            penerimaIds: [$warkahPermintaan->peminjam_id],
            judul: "Warkah {$warkahPermintaan->jenis} dipinjam",
            pesan: 'Batas kembali '.Carbon::parse($validated['batas_kembali'])->format('d/m/Y').'.',
            url: route('warkah-permintaan.pinjaman'),
            objekTipe: 'warkah',
            objekId: $warkahPermintaan->id,
        );

        return back()->with('success', "Warkah {$warkahPermintaan->jenis} diserahkan (DIPINJAM) kepada {$warkahPermintaan->nama_peminjam}.");
    }

    public function kembalikan(Request $request, WarkahPermintaan $warkahPermintaan, NotifikasiService $notifikasi): RedirectResponse
    {
        $this->pastikanPetugas();

        if (! in_array($warkahPermintaan->status, ['dipinjam', 'terlambat'], true)) {
            return back()->with('error', 'Warkah ini tidak sedang dipinjam.');
        }

        $validated = $request->validate([
            'pengembali' => ['required', 'string', 'max:200'],
            'kondisi' => ['required', Rule::in(array_keys(self::KONDISI))],
            'catatan' => ['required_unless:kondisi,baik', 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($warkahPermintaan, $validated) {
            $dari = $warkahPermintaan->status;

            $warkahPermintaan->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
                'pengembali' => $validated['pengembali'],
                'kondisi' => $validated['kondisi'],
                'catatan' => $validated['catatan'] ?? $warkahPermintaan->catatan,
            ]);

            $this->catatRiwayat(
                $warkahPermintaan,
                'KEMBALIKAN',
                $dari,
                'dikembalikan',
                "Diterima kembali oleh {$validated['pengembali']}. Kondisi: ".self::KONDISI[$validated['kondisi']]
                    .(isset($validated['catatan']) ? ". Catatan: {$validated['catatan']}" : ''),
            );
        });

        $tiket = $warkahPermintaan->tiket;
        $pemegang = $tiket?->penanggungJawab;

        if ($tiket !== null) {
            $notifikasi->kirim(
                tipe: 'warkah.dikembalikan',
                penerimaIds: array_merge(
                    [$pemegang?->id],
                    $notifikasi->idKasiSeksi($pemegang?->seksi),
                ),
                judul: 'Warkah dikembalikan',
                pesan: "Warkah {$warkahPermintaan->jenis} untuk {$tiket->kode_tiket} sudah kembali ke warkah.",
                url: route('warkah-permintaan.dikembalikan'),
                objekTipe: 'warkah',
                objekId: $warkahPermintaan->id,
            );
        }

        return back()->with('success', "Pengembalian warkah {$warkahPermintaan->jenis} tercatat.");
    }

    public function ubahBatas(Request $request, WarkahPermintaan $warkahPermintaan, NotifikasiService $notifikasi): RedirectResponse
    {
        $this->pastikanPetugas();

        if (! in_array($warkahPermintaan->status, ['dipinjam', 'terlambat'], true)) {
            return back()->with('error', 'Batas kembali hanya dapat diubah saat warkah dipinjam.');
        }

        $validated = $request->validate([
            'batas_kembali' => ['required', 'date', 'after:'.($warkahPermintaan->tanggal_pinjam?->toDateString() ?? now()->toDateString())],
            'alasan' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $statusLama = $warkahPermintaan->status;
        $batasLama = $warkahPermintaan->batas_kembali;
        $batasBaru = Carbon::parse($validated['batas_kembali'])->startOfDay();
        $statusBaru = $batasBaru->lt(now()->startOfDay()) ? 'terlambat' : 'dipinjam';

        $warkahPermintaan->update([
            'batas_kembali' => $batasBaru->toDateString(),
            'status' => $statusBaru,
            'catatan' => $validated['alasan'],
        ]);

        WarkahPermintaanRiwayat::create([
            'warkah_permintaan_id' => $warkahPermintaan->id,
            'aksi' => 'UBAH BATAS',
            'status_dari' => $statusLama,
            'status_ke' => $statusBaru,
            'batas_lama' => $batasLama,
            'batas_baru' => $batasBaru,
            'oleh_id' => Auth::id(),
            'catatan' => $validated['alasan'],
        ]);

        $notifikasi->kirim(
            tipe: 'warkah.batas_diubah',
            penerimaIds: [$warkahPermintaan->peminjam_id],
            judul: 'Batas kembali diperpanjang',
            pesan: 'Batas baru '.$batasBaru->format('d/m/Y').'. Alasan: '.$validated['alasan'],
            url: route('warkah-permintaan.pinjaman'),
            objekTipe: 'warkah',
            objekId: $warkahPermintaan->id,
        );

        return back()->with('success', 'Batas kembali diperbarui menjadi '.$batasBaru->format('d/m/Y').'.');
    }

    private function ubahStatus(WarkahPermintaan $warkah, string $ke, string $aksi, string $catatan): void
    {
        $dari = $warkah->status;

        DB::transaction(function () use ($warkah, $dari, $ke, $aksi, $catatan) {
            $warkah->update(['status' => $ke]);
            $this->catatRiwayat($warkah, $aksi, $dari, $ke, $catatan);
        });
    }

    private function catatRiwayat(
        WarkahPermintaan $warkah,
        string $aksi,
        ?string $dari,
        string $ke,
        ?string $catatan,
    ): void {
        WarkahPermintaanRiwayat::create([
            'warkah_permintaan_id' => $warkah->id,
            'aksi' => $aksi,
            'status_dari' => $dari,
            'status_ke' => $ke,
            'oleh_id' => Auth::id(),
            'catatan' => $catatan,
        ]);
    }

    private function pastikanPetugas(): void
    {
        $user = Auth::user();
        abort_unless($user->isPetugasWarkah() || $user->isWarkah() || $user->isAdmin(), 403);
    }

    /** @param  Builder<WarkahPermintaan>  $query */
    private function terapkanFilter(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $kata = $request->string('q')->toString();
            $query->whereHas('tiket', fn (Builder $t) => $t->cariPintar($kata));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
    }

    /** @return array{diminta:int, siap:int, dipinjam:int, terlambat:int, kembali_hari_ini:int} */
    private function ringkasan(): array
    {
        return [
            'diminta' => WarkahPermintaan::where('status', 'diminta')->count(),
            'siap' => WarkahPermintaan::where('status', 'siap')->count(),
            'dipinjam' => WarkahPermintaan::where('status', 'dipinjam')->count(),
            'terlambat' => WarkahPermintaan::whereIn('status', ['dipinjam', 'terlambat'])
                ->whereDate('batas_kembali', '<', now()->toDateString())
                ->count(),
            'kembali_hari_ini' => WarkahPermintaan::where('status', 'dikembalikan')
                ->whereDate('tanggal_kembali', now()->toDateString())
                ->count(),
        ];
    }
}
