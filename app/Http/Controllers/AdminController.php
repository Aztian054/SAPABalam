<?php

namespace App\Http\Controllers;

use App\Models\ArsipFolder;
use App\Models\ArsipTiket;
use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\TiketPenugasan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Database Tiket Terpadu — milik Admin, semua tiket terpusat di sini.
     * Status "diterima" = sedang menunggu di-Add oleh tahap berikutnya.
     */
    public function index(Request $request)
    {
        $query = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'petugasLoket',
            'penugasans.user',
            'lembarKerjaWarkahs',
        ]);

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

        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }

        $tikets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $stats = [
            'total' => Tiket::count(),
            'menunggu' => Tiket::where('status', 'diterima')->count(),
            'proses' => Tiket::whereIn('status', ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel', 'alih_media_btel', 'alih_media_suel'])->count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'dikembalikan' => Tiket::where('status', 'dikembalikan')->count(),
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        // Nama penanggung jawab tahap Warkah + status sertipikat untuk setiap tiket (monitoring Final-Rizki).
        foreach ($tikets as $tp) {
            $tp['monitor_warkah'] = $tp->penugasans->where('stage', 'warkah')->last()?->user?->name;
            $tp['monitor_sertipikat'] = $tp->lembarKerjaWarkahs->last()?->status_sertipikat;
        }

        return view('admin.index', compact('tikets', 'stats'));
    }

    // ---------------- Menu Selesai (migrasi ke arsip) ----------------
    /**
     * Query terpusat untuk pencarian tiket berstatus "selesai".
     * Dipakai bersama oleh halaman Selesai dan aksi arsip massal
     * agar filter (q, tahun, jenis_permohonan, jenis_hak, petugas) konsisten.
     */
    private function filterSelesaiQuery(Request $request): Builder
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'arsips'])
            ->where('status', 'selesai');

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
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
            'petugasList' => $this->petugasSelesaiOptions(),
            'folders' => ArsipFolder::orderBy('nama_folder')->get(),
        ]);
    }

    /**
     * Arsip massal tiket selesai — mendukung dua mode:
     *  - mode ids[]: arsip hanya tiket yang dicentang di tabel;
     *  - mode semua=1: arsip seluruh tiket pada hasil filter aktif.
     * Tiket yang sudah memiliki arsip otomatis dilewati (tidak dobel).
     */
    public function arsipkanMassal(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'required|exists:arsip_folder,id',
            'nama_arsip' => 'required|string|max:200',
            'tipe' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
            'ids' => 'nullable|string',
            'semua' => 'nullable|boolean',
        ]);

        $folder = ArsipFolder::findOrFail($validated['folder_id']);
        $namaArsip = trim($validated['nama_arsip']);

        if (! empty($validated['semua'])) {
            $targetIds = $this->filterSelesaiQuery($request)
                ->whereDoesntHave('arsips')
                ->pluck('tikets.id');
        } else {
            $targetIds = collect(explode(',', $validated['ids'] ?? ''))
                ->filter(fn ($id) => $id !== '')
                ->map(fn ($id) => (int) trim($id))
                ->values();
        }

        if ($targetIds->isEmpty()) {
            return back()->with('warning', 'Tidak ada tiket selesai yang dipilih untuk diarsipkan.');
        }

        $gebyah = DB::transaction(function () use ($targetIds, $folder, $namaArsip, $validated) {
            $jumlah = 0;

            Tiket::query()
                ->where('status', 'selesai')
                ->whereIn('id', $targetIds)
                ->whereDoesntHave('arsips')
                ->get()
                ->each(function (Tiket $tiket) use ($folder, $namaArsip, $validated, &$jumlah) {
                    ArsipTiket::create([
                        'tiket_id' => $tiket->id,
                        'folder_id' => $folder->id,
                        'nama_arsip' => $namaArsip,
                        'tipe_dokumen' => $validated['tipe'] ?? null,
                        'tanggal_arsip' => now()->toDateString(),
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);

                    RiwayatStatus::create([
                        'tiket_id' => $tiket->id,
                        'stage_dari' => 'DB Admin',
                        'stage_ke' => 'Arsip',
                        'changed_by' => Auth::id(),
                        'keterangan' => "Tiket diarsipkan massal ke folder {$folder->nama_folder} ({$namaArsip}).",
                    ]);

                    $jumlah++;
                });

            return $jumlah;
        });

        return back()->with('success', "{$gebyah} tiket selesai berhasil diarsipkan massal ke folder {$folder->nama_folder}.");
    }

    // ---------------- Menu Revisi (Admin melihat semua revisi) ----------------
    public function revisi(Request $request)
    {
        $query = Tiket::where('status', 'dikembalikan')
            ->whereHas('catatanRevisis', fn ($q) => $q->where('sudah_diproses', false))
            ->with(['jenisPermohonan', 'catatanRevisis', 'petugasLoket']);

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%");
            });
        }

        $tikets = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.revisi', compact('tikets'));
    }

    /** Admin menghapus (membatalkan) sebuah revisi yang salah kirim. */
    public function revisiHapus(int $id)
    {
        $tiket = Tiket::findOrFail($id);

        if ($tiket->status !== 'dikembalikan') {
            return back()->with('error', 'Tiket ini bukan berstatus revisi.');
        }

        $tiket->update(['status' => 'diterima', 'status_pembetulan' => $tiket->status_pembetulan ?? 'P1']);

        RiwayatStatus::create([
            'tiket_id' => $tiket->id,
            'stage_dari' => 'Revisi',
            'stage_ke' => 'DB Admin',
            'changed_by' => Auth::id(),
            'keterangan' => 'Revisi dihapus oleh Admin; tiket dikembalikan ke Database Admin tanpa status revisi.',
        ]);

        return back()->with('success', "Revisi tiket {$tiket->kode_tiket} dihapus; tiket kembali menunggu di Database Admin.");
    }

    // ---------------- Tambah Tiket Khusus (menu Admin) ----------------
    public function createTiket()
    {
        $jenisPermohonans = JenisPermohonan::where('is_active', true)->with('persyaratanDokumens')->get();

        return view('admin.create_tiket', compact('jenisPermohonans'));
    }

    public function storeTiket(Request $request)
    {
        $validated = $request->validate([
            // Nomor tiket diisi MANUAL oleh Admin — format bebas (dokumen Alur_00).
            'kode_tiket' => 'required|string|max:50|unique:tikets,kode_tiket',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'nama_pemohon' => 'required|string|max:200',
            'nik_pemohon' => 'nullable|string|max:20',
            'no_hp_pemohon' => 'required|string|max:20',
            'satuan_kerja' => 'nullable|string|max:200',
            'no_hak_sekarang' => 'nullable|string|max:100',
            'no_hak_sebelumnya' => 'nullable|string',
            'kelurahan_desa' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'jumlah_bidang' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'bidang' => 'required|array|min:1',
            'bidang.*.nib' => 'nullable|string|max:50',
            'bidang.*.no_sertifikat_lama' => 'nullable|string|max:100',
            'bidang.*.jenis_hak' => 'nullable|in:HM,HGB,HGU,HP,HPL',
            'bidang.*.nama_pemegang_hak' => 'nullable|string|max:200',
            'bidang.*.luas_m2' => 'nullable|numeric|min:0',
            'bidang.*.desa_kelurahan' => 'nullable|string|max:100',
            'bidang.*.kecamatan' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            $jp = JenisPermohonan::findOrFail($validated['jenis_permohonan_id']);
            $today = Carbon::today();
            $user = Auth::user();

            $tiket = Tiket::create([
                'kode_tiket' => $validated['kode_tiket'],
                'nomor_antrian' => 'A-'.str_pad(Tiket::count() + 1, 3, '0', STR_PAD_LEFT),
                'nomor_urut_berkas' => (Tiket::max('nomor_urut_berkas') ?? 0) + 1,
                'status_pembetulan' => 'P0',
                'tanggal_masuk' => $today,
                'jenis_permohonan_id' => $jp->id,
                'nama_pemohon' => $validated['nama_pemohon'],
                'nik_pemohon' => $validated['nik_pemohon'] ?? null,
                'no_hp_pemohon' => $validated['no_hp_pemohon'],
                'satuan_kerja' => $validated['satuan_kerja'] ?? null,
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
                'tanggal_target_selesai' => $today->copy()->addDays($jp->batas_hari_sla),
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($validated['bidang'] ?? [] as $i => $b) {
                BidangTanah::create([
                    'tiket_id' => $tiket->id,
                    'nib' => $b['nib'] ?? null,
                    'no_sertifikat_lama' => $b['no_sertifikat_lama'] ?? null,
                    'jenis_hak' => $b['jenis_hak'] ?? null,
                    'nama_pemegang_hak' => $b['nama_pemegang_hak'] ?? null,
                    'luas_m2' => $b['luas_m2'] ?? null,
                    'desa_kelurahan' => $b['desa_kelurahan'] ?? ($validated['kelurahan_desa'] ?? null),
                    'kecamatan' => $b['kecamatan'] ?? ($validated['kecamatan'] ?? null),
                    'urutan' => $i + 1,
                ]);
            }

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Admin',
                'stage_ke' => 'DB Admin',
                'changed_by' => $user->id,
                'keterangan' => 'Tiket ditambahkan langsung oleh Admin. Berkas berada di Database Admin.',
            ]);

            return redirect()->route('admin.show', $tiket->id)
                ->with('success', "Tiket {$tiket->kode_tiket} berhasil ditambahkan ke Database Admin.");
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
            'arsips',
        ])->findOrFail($id);

        $penugasanPerStage = TiketPenugasan::where('tiket_id', $tiket->id)->get();
        $folders = ArsipFolder::all();

        return view('admin.show', compact('tiket', 'penugasanPerStage', 'folders'));
    }

    /** Registrasi tiket selesai ke arsip folder fisik. */
    public function arsipkan(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);

        $request->validate([
            'folder_id' => 'required|exists:arsip_folder,id',
            'nama_arsip' => 'required|string|max:200',
            'tipe' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        ArsipTiket::create([
            'tiket_id' => $tiket->id,
            'folder_id' => $request->folder_id,
            'nama_arsip' => $request->nama_arsip,
            'tipe_dokumen' => $request->tipe,
            'tanggal_arsip' => now()->toDateString(),
            'keterangan' => $request->keterangan,
        ]);

        RiwayatStatus::create([
            'tiket_id' => $tiket->id,
            'stage_dari' => 'DB Admin',
            'stage_ke' => 'Arsip',
            'changed_by' => Auth::id(),
            'keterangan' => "Tiket diarsipkan ke folder {$request->nama_arsip}.",
        ]);

        return back()->with('success', "Tiket {$tiket->kode_tiket} berhasil diarsipkan.");
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

    // ---------------- Manajemen Pengguna ----------------
    public function users(Request $request)
    {
        $query = User::orderBy('role');
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        return view('admin.users', compact('users'));
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:'.implode(',', array_keys(User::ROLES)),
            'nip' => 'nullable|string|max:30',
            'no_hp' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nip' => $request->nip,
            'no_hp' => $request->no_hp,
            'is_active' => true,
        ]);

        return back()->with('success', "Akun {$request->name} berhasil dibuat.");
    }

    public function usersToggle(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', "Status akun {$user->name} diperbarui.");
    }
}
