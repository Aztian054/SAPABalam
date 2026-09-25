<?php

namespace App\Http\Controllers;

use App\Models\LembarKerjaWarkah;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarkahController extends StageWorkflow
{
    protected function stageName(): string
    {
        return 'warkah';
    }

    protected function stageLabel(): string
    {
        return 'Pencarian & Data Warkah';
    }

    protected function viewBase(): string
    {
        return 'warkah';
    }

    /** Dashboard Warkah — termasuk panel "Berkas Dipinjam (menunggu pengembalian)". */
    public function index(Request $request)
    {
        $view = parent::index($request);

        // Berkas BT/SU yang sedang dipinjam Validator/Alih Media → menunggu
        // Warkah mencatat pengembaliannya.
        $berkasDipinjam = LembarKerjaWarkah::with('tiket')
            ->where('status_pengembalian', 'dipinjam')
            ->latest('updated_at')
            ->get();

        return $view->with('berkasDipinjam', $berkasDipinjam);
    }

    /** Detail khusus: inisialisasi lembar kerja warkah bila belum ada. */
    public function show(int $id)
    {
        $tiket = Tiket::with([
            'jenisPermohonan.persyaratanDokumens',
            'bidangTanahs',
            'penugasans.user',
            'riwayatStatuses.user',
            'catatanRevisis',
        ])->findOrFail($id);

        $stage = $this->stageName();
        $isActive = (bool) $tiket->penugasanAktif($stage);
        $mine = $tiket->penugasanAktif($stage)?->user_id === Auth::id();

        $lembarKerja = LembarKerjaWarkah::firstOrCreate(
            ['tiket_id' => $tiket->id],
            ['petugas_id' => Auth::id()]
        );

        // Proses Selesai Warkah hanya dibuka setelah berkas BT/SU dikembalikan.
        $canSelesai = $this->flow->isPengembalianConfirmed($tiket);

        // Akun Validator BT/SU aktif — calon penerima serah terima berkas.
        $validatorUsers = User::whereIn('role', ['validator_btel', 'validator_suel'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Konfirmasi Pengembalian Berkas aktif hanya bila berkas sedang dipinjam
        // DAN Alih Media BT & SU keduanya sudah selesai.
        $canKonfirmasiKembali = $lembarKerja->status_pengembalian === 'dipinjam'
            && $tiket->isAlihMediaSelesai();

        $alihMediaSelesai = [
            'alih_media_btel' => $tiket->isStageSelesai('alih_media_btel'),
            'alih_media_suel' => $tiket->isStageSelesai('alih_media_suel'),
        ];

        return view('warkah.show', compact('tiket', 'isActive', 'mine', 'lembarKerja', 'stage', 'canSelesai', 'validatorUsers', 'canKonfirmasiKembali', 'alihMediaSelesai'))
            ->with('stageLabel', $this->stageLabel());
    }

    /** Simpan progres pekerjaan Warkah. */
    public function simpanProgres(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        $request->validate([
            'status_data_sertipikat_bt' => 'nullable|in:belum,proses,selesai',
            'status_data_sertipikat_su' => 'nullable|in:belum,proses,selesai',
            'status_sosialisasi' => 'nullable|in:belum,proses,selesai',
            // Keluaran Warkah (dokumen FINAL — konfigurasi alur paralel 13 Sep 2026).
            'status_dokumen_bt' => 'nullable|in:ada,tidak_ada',
            'status_dokumen_su' => 'nullable|in:ada,tidak_ada',
            'jumlah_berkas' => 'nullable|integer|min:0',
            'jumlah_halaman' => 'nullable|integer|min:0',
            'gabungan' => 'nullable|boolean',
            'keterangan_status' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $lembarKerja = LembarKerjaWarkah::firstOrCreate(
            ['tiket_id' => $tiket->id],
            ['petugas_id' => Auth::id()]
        );

        $lembarKerja->update([
            'status_data_sertipikat_bt' => $request->input('status_data_sertipikat_bt', $lembarKerja->status_data_sertipikat_bt),
            'status_data_sertipikat_su' => $request->input('status_data_sertipikat_su', $lembarKerja->status_data_sertipikat_su),
            'status_sosialisasi' => $request->input('status_sosialisasi', $lembarKerja->status_sosialisasi),
            'status_dokumen_bt' => $request->input('status_dokumen_bt'),
            'status_dokumen_su' => $request->input('status_dokumen_su'),
            'jumlah_berkas' => $request->input('jumlah_berkas'),
            'jumlah_halaman' => $request->input('jumlah_halaman'),
            'gabungan' => $request->boolean('gabungan'),
            'keterangan_status' => $request->input('keterangan_status'),
            'catatan' => $request->input('catatan'),
            'petugas_id' => Auth::id(),
        ]);

        return back()->with('success', 'Progres lembar kerja Warkah disimpan.');
    }

    /**
     * SERAHKAN BERKAS WARKAH ke Validator BT/SU — mencatat serah terima lengkap
     * (penerima, tanggal & jam serah, kondisi berkas BT/SU, catatan kondisi) dan
     * membuka gerbang Validator BT/SU secara paralel (tanpa menunggu Verifikator).
     */
    public function kirim(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        if ($tiket->isDiserahkanKeValidator()) {
            return back()->with('info', 'Berkas BT/SU berkas ini sudah diserahkan ke Validator (masih dipinjam atau sudah dikembalikan).');
        }

        $validated = $request->validate([
            'penerima_validator_id' => 'required|integer|exists:users,id',
            'waktu_serah' => 'required|date_format:Y-m-d\TH:i',
            'status_berkas_bt' => 'required|in:lengkap,rusak,kurang',
            'status_berkas_su' => 'required|in:lengkap,rusak,kurang',
            'catatan_kondisi_berkas' => 'nullable|string',
        ]);

        $penerima = User::findOrFail((int) $validated['penerima_validator_id']);
        if (! in_array($penerima->role, ['validator_btel', 'validator_suel'], true)) {
            return back()->with('error', 'Penerima berkas harus berperan Validator BT atau Validator SU.');
        }

        $waktuSerah = Carbon::parse($validated['waktu_serah']);

        $lembarKerja = LembarKerjaWarkah::firstOrCreate(
            ['tiket_id' => $tiket->id],
            ['petugas_id' => Auth::id()]
        );

        $lembarKerja->update([
            'status_sertipikat' => 'diserahkan',
            'status_pengembalian' => 'dipinjam',
            'tanggal_diserahkan' => $waktuSerah->toDateString(),
            'waktu_serah' => $waktuSerah,
            'penerima_validator_id' => $penerima->id,
            'nama_penerima_validator' => $penerima->name.' ('.$penerima->role_label.')',
            'status_berkas_bt' => $validated['status_berkas_bt'],
            'status_berkas_su' => $validated['status_berkas_su'],
            'catatan_kondisi_berkas' => $validated['catatan_kondisi_berkas'] ?? null,
        ]);

        $tiket->update(['diserahkan_ke_validator' => true]);

        RiwayatStatus::create([
            'tiket_id' => $tiket->id,
            'stage_dari' => 'Warkah',
            'stage_ke' => 'Validator (diserahkan_ke_validator)',
            'changed_by' => Auth::id(),
            'keterangan' => 'Warkah menyerahkan berkas BT/SU ke Validator — status DIPINJAM. Penerima: '.$penerima->name.', jam serah: '.$waktuSerah->format('d/m/Y H:i').', kondisi BT: '.$validated['status_berkas_bt'].', SU: '.$validated['status_berkas_su'].'.',
        ]);

        return back()->with('success', 'Berkas BT/SU diserahkan ke Validator (status: DIPINJAM). Serah terima: '.$penerima->name.' — '.$waktuSerah->format('d/m/Y H:i').'.');
    }

    /**
     * KONFIRMASI PENGEMBALIAN BERKAS BT/SU dari Alih Media ke Warkah (DIKEMBALIKAN).
     * Hanya aktif ketika berkas sedang dipinjam DAN Alih Media BT & SU sudah selesai.
     * Waktu terima kembali diisi manual sesuai kondisi aktual hardcopy diterima Warkah.
     */
    public function catatPengembalian(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        $lembarKerja = LembarKerjaWarkah::firstOrCreate(
            ['tiket_id' => $tiket->id],
            ['petugas_id' => Auth::id()]
        );

        if ($lembarKerja->status_pengembalian === 'dikembalikan') {
            return back()->with('info', 'Pengembalian berkas BT/SU untuk berkas ini sudah tercatat.');
        }

        if ($lembarKerja->status_pengembalian !== 'dipinjam') {
            return back()->with('error', 'Berkas BT/SU belum diserahkan ke Validator; belum ada pinjaman yang perlu dikembalikan.');
        }

        if (! $tiket->isAlihMediaSelesai()) {
            return back()->with('error', 'Konfirmasi Pengembalian Berkas hanya dapat dilakukan setelah Alih Media BT & SU selesai.');
        }

        $validated = $request->validate([
            'petugas_pengembali' => 'required|string|max:100',
            'waktu_kembali' => 'required|date_format:Y-m-d\TH:i',
            'kondisi_berkas_kembali' => 'required|in:lengkap,rusak,kurang',
            'catatan_pengembalian' => 'nullable|string',
            'jumlah_berkas_dikembalikan' => 'nullable|integer|min:0',
        ]);

        $waktuKembali = Carbon::parse($validated['waktu_kembali']);

        $lembarKerja->update([
            'status_pengembalian' => 'dikembalikan',
            'tanggal_kembali' => $waktuKembali->toDateString(),
            'waktu_kembali' => $waktuKembali,
            'petugas_pengembali' => $validated['petugas_pengembali'],
            'kondisi_berkas_kembali' => $validated['kondisi_berkas_kembali'],
            'catatan_pengembalian' => $validated['catatan_pengembalian'] ?? null,
            'keterangan_status' => $validated['catatan_pengembalian'] ?? null,
            'jumlah_berkas_dikembalikan' => $validated['jumlah_berkas_dikembalikan'] ?? null,
        ]);

        RiwayatStatus::create([
            'tiket_id' => $tiket->id,
            'stage_dari' => 'Alih Media (peminjam)',
            'stage_ke' => 'Warkah (dikembalikan)',
            'changed_by' => Auth::id(),
            'keterangan' => 'Berkas BT/SU hardcopy diterima kembali oleh Warkah '.$waktuKembali->format('d/m/Y H:i').' — status DIKEMBALIKAN. Petugas pengembali: '.$validated['petugas_pengembali'].'.',
        ]);

        return back()->with('success', 'Pengembalian berkas BT/SU ke Warkah dicatat (status: DIKEMBALIKAN).');
    }

    /**
     * Selesai tahap Warkah — HANYA bisa setelah pengembalian berkas BT/SU tercatat
     * (status_pengembalian = dikembalikan). Berbeda dari tahap lain di StageWorkflow.
     */
    public function selesai(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        $lembarKerja = LembarKerjaWarkah::where('tiket_id', $tiket->id)->first();
        if (! $lembarKerja || $lembarKerja->status_pengembalian !== 'dikembalikan') {
            return back()->with('error', 'Berkas BT/SU belum dikembalikan ke Warkah. Konfirmasi pengembalian lebih dulu melalui tombol "Konfirmasi Pengembalian Berkas".');
        }

        $catatan = $request->input('catatan');

        try {
            $this->flow->done(Auth::user(), $tiket, $catatan);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $msg = $tiket->status === 'selesai'
            ? "Berkas {$tiket->kode_tiket} telah SELESAI 100% (semua tahap termasuk pengembalian berkas BT/SU beres)."
            : "Berkas {$tiket->kode_tiket} dinyatakan selesai dan kembali ke Database Admin.";

        return redirect()->route('warkah.index')->with('success', $msg);
    }

    protected function ensureMine(Tiket $tiket): void
    {
        $penugasan = $tiket->penugasanAktif($this->stageName());
        if (! $penugasan || $penugasan->user_id !== Auth::id()) {
            abort(403, 'Berkas tidak sedang dalam antrian pekerjaan Anda.');
        }
    }
}
