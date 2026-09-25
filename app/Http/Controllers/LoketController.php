<?php

namespace App\Http\Controllers;

use App\Models\BidangTanah;
use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Services\TiketFlowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoketController extends Controller
{
    public function index(Request $request)
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs'])
            ->where(fn ($q) => $q
                ->where('created_by', Auth::id())
                ->orWhere('petugas_loket_id', Auth::id()));

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tikets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $revisiBelumDiproses = Tiket::where('status', 'dikembalikan')
            ->whereHas('catatanRevisis', fn ($q) => $q->where('sudah_diproses', false))
            ->with('catatanRevisis')
            ->get();

        $jenisPermohonans = JenisPermohonan::where('is_active', true)->with('persyaratanDokumens')->get();
        $jenisHaks = JenisHak::where('is_active', true)->orderBy('urutan')->orderBy('kode')->get();

        return view('loket.index', compact('tikets', 'revisiBelumDiproses', 'jenisPermohonans', 'jenisHaks'));
    }

    /**
     * Deprecated (V2.1): pembuatan tiket dipindah ke modal di halaman index.
     */
    public function create()
    {
        return redirect()->route('loket.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Nomor tiket diisi MANUAL oleh Loket — format bebas (dokumen Alur_00).
            'kode_tiket' => 'required|string|max:50|unique:tikets,kode_tiket',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'nama_pemohon' => 'required|string|max:200',
            'nik_pemohon' => 'nullable|string|max:20',
            'no_hp_pemohon' => 'required|string|max:20',
            'email_pemohon' => ['nullable', 'email:rfc', 'max:255'],
            'no_hak_sekarang' => 'nullable|string|max:100',
            'no_hak_sebelumnya' => 'nullable|string',
            'kelurahan_desa' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'jumlah_bidang' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'bidang' => 'required|array|min:1',
            'bidang.*.nib' => 'nullable|string|max:50',
            'bidang.*.no_sertifikat_lama' => 'nullable|string|max:100',
            'bidang.*.jenis_hak' => ['nullable', Rule::in(JenisHak::where('is_active', true)->pluck('kode')->all() ?: ['HM', 'HGB', 'HGU', 'HP', 'HPL'])],
            'bidang.*.nama_pemegang_hak' => 'nullable|string|max:200',
            'bidang.*.desa_kelurahan' => 'nullable|string|max:100',
            'bidang.*.kecamatan' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            $jp = JenisPermohonan::findOrFail($validated['jenis_permohonan_id']);
            $today = Carbon::today();

            $user = Auth::user();
            $kodeTiket = $validated['kode_tiket'];

            $tiket = Tiket::create([
                'kode_tiket' => $kodeTiket,
                'nomor_antrian' => 'A-'.str_pad(Tiket::count() + 1, 3, '0', STR_PAD_LEFT),
                'nomor_urut_berkas' => (Tiket::max('nomor_urut_berkas') ?? 0) + 1,
                'status_pembetulan' => 'P0',
                'tanggal_masuk' => $today,
                'jenis_permohonan_id' => $jp->id,
                'nama_pemohon' => $validated['nama_pemohon'],
                'nik_pemohon' => $validated['nik_pemohon'] ?? null,
                'no_hp_pemohon' => $validated['no_hp_pemohon'],
                'email_pemohon' => $validated['email_pemohon'] ?? null,
                'no_hak_sekarang' => $validated['no_hak_sekarang'] ?? null,
                'no_hak_sebelumnya' => $validated['no_hak_sebelumnya'] ?? null,
                'kelurahan_desa' => $validated['kelurahan_desa'] ?? null,
                'kecamatan' => $validated['kecamatan'] ?? null,
                'jumlah_bidang' => $validated['jumlah_bidang'],
                'petugas_loket_id' => $user->id,
                'nama_petugas_loket' => $user->name,
                'nomor_telepon' => $validated['no_hp_pemohon'],
                'created_by' => $user->id,
                'status' => 'diterima',
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($validated['bidang'] ?? [] as $i => $b) {
                BidangTanah::create([
                    'tiket_id' => $tiket->id,
                    'nib' => $b['nib'] ?? null,
                    'no_sertifikat_lama' => $b['no_sertifikat_lama'] ?? null,
                    'jenis_hak' => $b['jenis_hak'] ?? null,
                    'nama_pemegang_hak' => $b['nama_pemegang_hak'] ?? null,
                    'desa_kelurahan' => $b['desa_kelurahan'] ?? ($validated['kelurahan_desa'] ?? null),
                    'kecamatan' => $b['kecamatan'] ?? ($validated['kecamatan'] ?? null),
                    'urutan' => $i + 1,
                ]);
            }

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Pendaftaran',
                'stage_ke' => 'DB Admin',
                'changed_by' => $user->id,
                'keterangan' => 'Berkas terdaftar di Loket dan masuk Database Admin.',
            ]);

            return redirect()->route('loket.show', $tiket->id)
                ->with('success', "Berkas {$tiket->kode_tiket} berhasil didaftarkan. Berkas kini berada di Database Admin.");
        });
    }

    public function show(int $id)
    {
        $tiket = Tiket::with([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'penugasans.user',
            'riwayatStatuses.user',
            'catatanRevisis',
        ])->findOrFail($id);

        return view('loket.show', compact('tiket'));
    }

    public function edit(int $id)
    {
        $tiket = Tiket::with('bidangTanahs')->findOrFail($id);
        $jenisPermohonans = JenisPermohonan::where('is_active', true)->get();

        return view('loket.edit', compact('tiket', 'jenisPermohonans'));
    }

    public function update(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);

        $validated = $request->validate([
            'nama_pemohon' => 'required|string|max:200',
            'nik_pemohon' => 'nullable|string|max:20',
            'no_hp_pemohon' => 'required|string|max:20',
            'no_hak_sekarang' => 'nullable|string|max:100',
            'kelurahan_desa' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
        ]);

        $tiket->update($validated);

        return redirect()->route('loket.show', $tiket->id)->with('success', 'Data berkas berhasil diperbarui.');
    }

    /** Pemohon menyerahkan perbaikan revisi → tiket kembali ke tahap asal. */
    public function resubmit(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);

        $request->validate(['catatan_perbaikan' => 'nullable|string']);

        try {
            $service = app(TiketFlowService::class);
            $service->resubmitAfterRevisi(Auth::user(), $tiket, $request->input('catatan_perbaikan'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loket.show', $tiket->id)
            ->with('success', "Perbaikan revisi berkas {$tiket->kode_tiket} diterima dan dikirim ke tahap berikutnya.");
    }

    public function printReceipt(int $id)
    {
        $tiket = Tiket::with(['jenisPermohonan.persyaratanDokumens', 'bidangTanahs', 'petugasLoket'])->findOrFail($id);

        return view('loket.print_receipt', compact('tiket'));
    }

    public function printChecklist(int $id)
    {
        $tiket = Tiket::with(['jenisPermohonan.persyaratanDokumens', 'bidangTanahs', 'petugasLoket'])->findOrFail($id);

        return view('loket.print_checklist', compact('tiket'));
    }
}
