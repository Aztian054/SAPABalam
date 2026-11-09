<?php

namespace App\Http\Controllers;

use App\Models\BidangTanah;
use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\PendaftaranDraft;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\WarkahPermintaan;
use App\Services\NotifikasiService;
use App\Services\TiketNomorService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Wizard pendaftaran 4 tahap (Blueprint V2).
 *
 * Tahap 1 menyimpan draf + memesan nomor tiket, Tahap 2 menunggu ploting,
 * Tahap 3 mengisi data permohonan, dan Tahap 4 menerbitkan tiket
 * berstatus `di_database` beserta permintaan warkah.
 */
class PendaftaranController extends Controller
{
    public function __construct(
        private readonly TiketNomorService $nomorService,
        private readonly NotifikasiService $notifikasi,
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'warkahPermintaans', 'plotingOleh', 'penanggungJawab'])
            ->where('created_by', $user->id);

        $this->terapkanPencarian($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $tikets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $ringkasan = [
            'total' => Tiket::where('created_by', $user->id)->count(),
            'hari_ini' => Tiket::where('created_by', $user->id)->whereDate('tanggal_masuk', Carbon::today())->count(),
            'di_database' => Tiket::where('created_by', $user->id)->where('status', 'di_database')->count(),
            'diproses' => Tiket::where('created_by', $user->id)->where('status', 'diproses')->count(),
            'ditolak' => Tiket::where('created_by', $user->id)->where('status', 'ditolak')->count(),
            'selesai' => Tiket::where('created_by', $user->id)->where('status', 'selesai')->count(),
        ];

        return view('pendaftaran.index', compact('tikets', 'ringkasan'));
    }

    public function create(): View
    {
        $draf = PendaftaranDraft::where('user_id', Auth::id())->latest()->first();

        return view('pendaftaran.tahap1', [
            'draf' => $draf,
            'nomorOtomatis' => $this->nomorService->generate(),
        ]);
    }

    public function storeTahap1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pemohon' => ['required', 'string', 'max:200'],
            'nik_pemohon' => ['nullable', 'digits:16'],
            'no_hp_pemohon' => ['required', 'string', 'max:20'],
            'kelurahan_desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'no_berkas' => ['nullable', 'string'],
        ]);

        $noBerkas = trim((string) ($validated['no_berkas'] ?? ''));

        $draf = PendaftaranDraft::create([
            'user_id' => Auth::id(),
            'kode_tiket' => $this->nomorService->generate(),
            'no_berkas' => $noBerkas !== '' ? $noBerkas : null,
            'tahap' => 1,
            'ploting_status' => 'belum',
            'data' => [
                'biodata' => [
                    'nama_pemohon' => $validated['nama_pemohon'],
                    'nik_pemohon' => $validated['nik_pemohon'] ?? null,
                    'no_hp_pemohon' => $validated['no_hp_pemohon'],
                    'kelurahan_desa' => $validated['kelurahan_desa'],
                    'kecamatan' => $validated['kecamatan'] ?? null,
                ],
            ],
        ]);

        return redirect()->route('pendaftaran.tahap2', $draf)
            ->with('success', "Nomor tiket {$draf->kode_tiket} dipesan. Lanjut ke Tahap 2 — Ploting.");
    }

    public function tahap2(PendaftaranDraft $draf): View
    {
        $this->pastikanMilikSendiri($draf);

        return view('pendaftaran.tahap2', [
            'draf' => $draf,
            'biodata' => $draf->biodata(),
        ]);
    }

    public function simpanTahap2(Request $request, PendaftaranDraft $draf): RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        $validated = $request->validate([
            'ploting' => ['required', 'in:sudah,belum'],
        ]);

        if ($validated['ploting'] === 'sudah') {
            if ($draf->dikirim_pada !== null && ! $draf->isPlotingSelesai()) {
                return back()->with('warning', 'Berkas sudah dikirim ke Loket Ploting. Tunggu petugas ploting menyatakan selesai.');
            }

            $draf->update([
                'tahap' => 2,
                'ploting_status' => 'sudah',
                'ploting_selesai_at' => now(),
            ]);

            return redirect()->route('pendaftaran.tahap3', $draf)
                ->with('success', 'Ploting dinyatakan sudah. Lanjut ke Tahap 3 — Data Permohonan.');
        }

        $draf->update([
            'tahap' => 2,
            'ploting_status' => 'belum',
            'dikirim_pada' => $draf->dikirim_pada ?? now(),
        ]);

        return redirect()->route('pendaftaran.antrian', $draf)
            ->with('warning', 'Berkas masuk antrian Ploting. Serahkan cetak antrian ke Loket Ploting.');
    }

    public function antrian(PendaftaranDraft $draf): View
    {
        $this->pastikanMilikSendiri($draf);

        return view('pendaftaran.antrian', [
            'draf' => $draf,
            'biodata' => $draf->biodata(),
        ]);
    }

    public function tahap3(PendaftaranDraft $draf): View|RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        if (! $draf->isPlotingSelesai()) {
            return redirect()->route('pendaftaran.tahap2', $draf)
                ->with('warning', 'Tahap 3 terbuka setelah ploting dinyatakan sudah.');
        }

        return view('pendaftaran.tahap3', [
            'draf' => $draf,
            'permohonan' => $draf->permohonan(),
            'bidang' => $draf->bidang(),
            'jenisPermohonans' => JenisPermohonan::where('is_active', true)->orderBy('kode')->get(),
            'jenisHaks' => JenisHak::where('is_active', true)->orderBy('urutan')->orderBy('kode')->get(),
        ]);
    }

    public function simpanTahap3(Request $request, PendaftaranDraft $draf): RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        if (! $draf->isPlotingSelesai()) {
            return redirect()->route('pendaftaran.tahap2', $draf)
                ->with('warning', 'Tahap 3 terbuka setelah ploting dinyatakan sudah.');
        }

        $validated = $request->validate([
            'jenis_permohonan_id' => ['required', 'exists:jenis_permohonans,id'],
            'no_hak_sekarang' => ['nullable', 'string', 'max:100'],
            'no_hak_sebelumnya' => ['nullable', 'string'],
            'kelurahan_desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'bidang' => ['required', 'array', 'min:1'],
            'bidang.*.nib' => ['nullable', 'string', 'max:50'],
            'bidang.*.no_sertifikat_lama' => ['nullable', 'string', 'max:100'],
            'bidang.*.jenis_hak' => ['nullable', 'string', 'max:10'],
            'bidang.*.nama_pemegang_hak' => ['nullable', 'string', 'max:200'],
            'bidang.*.desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'bidang.*.kecamatan' => ['nullable', 'string', 'max:100'],
        ]);

        $data = $draf->data ?? [];
        $data['permohonan'] = [
            'jenis_permohonan_id' => (int) $validated['jenis_permohonan_id'],
            'no_hak_sekarang' => $validated['no_hak_sekarang'] ?? null,
            'no_hak_sebelumnya' => $validated['no_hak_sebelumnya'] ?? null,
            'kelurahan_desa' => $validated['kelurahan_desa'],
            'kecamatan' => $validated['kecamatan'],
            'jumlah_bidang' => count($validated['bidang']),
            'keterangan' => $validated['keterangan'] ?? null,
            'bidang' => array_values($validated['bidang']),
        ];

        $draf->update(['tahap' => 3, 'data' => $data]);

        return redirect()->route('pendaftaran.tahap4', $draf)
            ->with('success', 'Data permohonan tersimpan. Lanjut ke Tahap 4 — Kebutuhan Warkah.');
    }

    public function tahap4(PendaftaranDraft $draf): View|RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        if ($draf->permohonan() === [] || empty($draf->permohonan()['bidang'])) {
            return redirect()->route('pendaftaran.tahap3', $draf)
                ->with('warning', 'Lengkapi data permohonan pada Tahap 3 terlebih dahulu.');
        }

        return view('pendaftaran.tahap4', [
            'draf' => $draf,
            'biodata' => $draf->biodata(),
            'permohonan' => $draf->permohonan(),
        ]);
    }

    public function simpanTahap4(Request $request, PendaftaranDraft $draf): RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        $validated = $request->validate([
            'warkah' => ['required', 'in:none,BT,SU,keduanya'],
        ]);

        if ($draf->permohonan() === [] || empty($draf->permohonan()['bidang'])) {
            return redirect()->route('pendaftaran.tahap3', $draf)
                ->with('warning', 'Lengkapi data permohonan pada Tahap 3 terlebih dahulu.');
        }

        $tiket = DB::transaction(function () use ($draf, $validated) {
            $user = Auth::user();
            $biodata = $draf->biodata();
            $permohonan = $draf->permohonan();
            $hariIni = Carbon::today();

            $nomor = $this->nomorService->tersedia($draf->kode_tiket, $draf->id)
                ? $draf->kode_tiket
                : $this->nomorService->generate(kecualiDraftId: $draf->id);

            $tiket = Tiket::create([
                'kode_tiket' => $nomor,
                'no_berkas' => $draf->no_berkas,
                'nomor_antrian' => 'A-'.str_pad((string) (Tiket::count() + 1), 3, '0', STR_PAD_LEFT),
                'nomor_urut_berkas' => (Tiket::max('nomor_urut_berkas') ?? 0) + 1,
                'tanggal_masuk' => $hariIni,
                'jenis_permohonan_id' => $permohonan['jenis_permohonan_id'],
                'nama_pemohon' => $biodata['nama_pemohon'],
                'nik_pemohon' => $biodata['nik_pemohon'] ?? null,
                'no_hp_pemohon' => $biodata['no_hp_pemohon'],
                'no_hak_sekarang' => $permohonan['no_hak_sekarang'] ?? null,
                'no_hak_sebelumnya' => $permohonan['no_hak_sebelumnya'] ?? null,
                'kelurahan_desa' => $permohonan['kelurahan_desa'],
                'kecamatan' => $permohonan['kecamatan'],
                'jumlah_bidang' => $permohonan['jumlah_bidang'],
                'petugas_loket_id' => $user->id,
                'nama_petugas_loket' => $user->name,
                'nomor_telepon' => $biodata['no_hp_pemohon'],
                'created_by' => $user->id,
                'status' => 'di_database',
                'keterangan' => $permohonan['keterangan'] ?? null,
                'ploting_status' => 'sudah',
                'ploting_catatan' => $draf->ploting_catatan,
                'ploting_selesai_at' => $draf->ploting_selesai_at ?? now(),
                'ploting_oleh_id' => $draf->ploting_oleh_id,
            ]);

            foreach ($permohonan['bidang'] as $urutan => $b) {
                BidangTanah::create([
                    'tiket_id' => $tiket->id,
                    'nib' => $b['nib'] ?? null,
                    'no_sertifikat_lama' => $b['no_sertifikat_lama'] ?? null,
                    'jenis_hak' => $b['jenis_hak'] ?? null,
                    'nama_pemegang_hak' => $b['nama_pemegang_hak'] ?? null,
                    'desa_kelurahan' => $b['desa_kelurahan'] ?? $permohonan['kelurahan_desa'],
                    'kecamatan' => $b['kecamatan'] ?? $permohonan['kecamatan'],
                    'urutan' => $urutan + 1,
                ]);
            }

            foreach ($this->jenisWarkah($validated['warkah']) as $jenis) {
                WarkahPermintaan::create([
                    'tiket_id' => $tiket->id,
                    'jenis' => $jenis,
                    'status' => 'diminta',
                    'diminta_oleh_id' => $user->id,
                    'tanggal_permintaan' => now(),
                ]);
            }

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Pendaftaran',
                'stage_ke' => 'Database',
                'changed_by' => $user->id,
                'keterangan' => 'Pendaftaran selesai. Tiket masuk Database Admin.',
            ]);

            $draf->delete();

            return $tiket;
        });

        $this->notifikasi->kirim(
            tipe: 'tiket.dibuat',
            penerimaIds: $this->notifikasi->idStafSeksi(),
            judul: 'Tiket baru di Database',
            pesan: "{$tiket->kode_tiket} — {$tiket->nama_pemohon}. Silakan cari dan ADD bila siap dikerjakan.",
            url: route('seksi.index'),
            objekTipe: 'tiket',
            objekId: $tiket->id,
        );

        if ($tiket->warkahPermintaans()->exists()) {
            $jenis = $tiket->warkahPermintaans()->pluck('jenis')->implode(' & ');

            $this->notifikasi->kirim(
                tipe: 'warkah.diminta',
                penerimaIds: $this->notifikasi->idPetugasWarkah(),
                judul: 'Permintaan warkah baru',
                pesan: "Warkah {$jenis} untuk tiket {$tiket->kode_tiket} diminta Loket Pendaftaran.",
                url: route('warkah-permintaan.index'),
                objekTipe: 'tiket',
                objekId: $tiket->id,
            );
        }

        return redirect()->route('pendaftaran.index')
            ->with('success', "Berkas {$tiket->kode_tiket} terdaftar di Database. ".($tiket->warkahPermintaans()->exists() ? 'Permintaan warkah dikirim ke Petugas Warkah.' : 'Tidak memerlukan warkah.'));
    }

    public function show(Tiket $tiket): View
    {
        abort_unless($tiket->created_by === Auth::id(), 403);

        $tiket->load(['jenisPermohonan.persyaratanDokumens', 'bidangTanahs', 'petugasLoket', 'plotingOleh', 'warkahPermintaans', 'penanggungJawab']);

        return view('pendaftaran.show', compact('tiket'));
    }

    public function edit(Tiket $tiket): View
    {
        abort_unless($tiket->created_by === Auth::id(), 403);

        $tiket->load(['jenisPermohonan', 'bidangTanahs', 'warkahPermintaans']);

        return view('pendaftaran.edit', compact('tiket'));
    }

    public function update(Request $request, Tiket $tiket): RedirectResponse
    {
        abort_unless($tiket->created_by === Auth::id(), 403);

        $validated = $request->validate([
            'no_berkas' => ['nullable', 'string'],
            'nama_pemohon' => ['required', 'string', 'max:200'],
            'nik_pemohon' => ['nullable', 'digits:16'],
            'no_hp_pemohon' => ['nullable', 'string', 'max:20'],
            'kelurahan_desa' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'no_hak_sekarang' => ['nullable', 'string', 'max:100'],
        ]);

        $noBerkas = trim((string) ($validated['no_berkas'] ?? ''));
        $validated['no_berkas'] = $noBerkas !== '' ? $noBerkas : null;

        $tiket->update($validated);

        return redirect()->route('pendaftaran.index')
            ->with('success', "Berkas {$tiket->kode_tiket} diperbarui.");
    }

    public function destroy(Tiket $tiket): RedirectResponse
    {
        abort_unless($tiket->created_by === Auth::id(), 403);

        if (in_array($tiket->status, ['diproses', 'selesai', 'ditolak'])) {
            return redirect()->route('pendaftaran.index')
                ->with('error', "Berkas {$tiket->kode_tiket} tidak dapat dihapus karena sudah diproses staf.");
        }

        $tiket->delete();

        return redirect()->route('pendaftaran.index')
            ->with('success', "Berkas {$tiket->kode_tiket} dihapus.");
    }

    public function cetak(Tiket $tiket): View
    {
        abort_unless($tiket->created_by === Auth::id(), 403);

        $tiket->load(['jenisPermohonan.persyaratanDokumens', 'bidangTanahs', 'petugasLoket']);

        return view('pendaftaran.print_receipt', compact('tiket'));
    }

    /**
     * No. Berkas boleh diisi/diubah kapan saja oleh pembuat berkas.
     * Format bebas (tanpa validasi panjang/keunikan); kosong disimpan null.
     */
    public function updateNoBerkas(Request $request, Tiket $tiket): RedirectResponse
    {
        abort_unless(Auth::id() === $tiket->created_by, 403);

        $validated = $request->validate([
            'no_berkas' => ['nullable', 'string'],
        ]);

        $noBerkas = trim((string) ($validated['no_berkas'] ?? ''));

        $tiket->update(['no_berkas' => $noBerkas !== '' ? $noBerkas : null]);

        return back()->with('success', "No. Berkas {$tiket->kode_tiket} diperbarui.");
    }

    public function batal(PendaftaranDraft $draf): RedirectResponse
    {
        $this->pastikanMilikSendiri($draf);

        $nomor = $draf->kode_tiket;
        $draf->delete();

        return redirect()->route('pendaftaran.index')
            ->with('warning', "Draf pendaftaran {$nomor} dibatalkan. Nomor tiket dilepas kembali.");
    }

    /** @return list<string> */
    private function jenisWarkah(string $pilihan): array
    {
        return match ($pilihan) {
            'BT' => ['BT'],
            'SU' => ['SU'],
            'keduanya' => ['BT', 'SU'],
            default => [],
        };
    }

    private function pastikanMilikSendiri(PendaftaranDraft $draf): void
    {
        abort_unless($draf->user_id === Auth::id(), 403);
    }

    private function terapkanPencarian(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $kata = trim($request->string('q')->toString());

            $query->where(function ($q) use ($kata) {
                $q->cariNomor($kata)
                    ->orWhere('no_berkas', 'like', "%{$kata}%")
                    ->orWhere('nama_pemohon', 'like', "%{$kata}%")
                    ->orWhere('nik_pemohon', 'like', "%{$kata}%");
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_masuk', '>=', $request->date('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_masuk', '<=', $request->date('tanggal_sampai'));
        }
    }
}
