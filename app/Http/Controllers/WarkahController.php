<?php

namespace App\Http\Controllers;

use App\Models\LembarKerjaWarkah;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
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

        return view('warkah.show', compact('tiket', 'isActive', 'mine', 'lembarKerja', 'stage'))
            ->with('stageLabel', $this->stageLabel());
    }

    /** Simpan progres pekerjaan Warkah. */
    public function simpanProgres(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        $request->validate([
            'status_berkas_dataset' => 'nullable|in:belum,proses,selesai',
            'status_data_sertipikat_bt' => 'nullable|in:belum,proses,selesai',
            'status_data_sertipikat_su' => 'nullable|in:belum,proses,selesai',
            'status_sosialisasi' => 'nullable|in:belum,proses,selesai',
            'status_public_response' => 'nullable|in:belum,proses,selesai',
            // Keluaran Warkah (dokumen FINAL — konfigurasi alur paralel 13 Sep 2026).
            'status_sertipikat' => 'nullable|in:proses,diserahkan',
            'status_dokumen_bt' => 'nullable|in:ada,tidak_ada',
            'status_dokumen_su' => 'nullable|in:ada,tidak_ada',
            'tanggal_diserahkan' => 'nullable|date',
            'tanggal_kembali' => 'nullable|date',
            'jumlah_berkas' => 'nullable|integer|min:0',
            'jumlah_halaman' => 'nullable|integer|min:0',
            'gabungan' => 'nullable|boolean',
            'jumlah_berkas_dikembalikan' => 'nullable|integer|min:0',
            'pengembalian_sementara' => 'nullable|boolean',
            'keterangan_status' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $lembarKerja = LembarKerjaWarkah::firstOrCreate(
            ['tiket_id' => $tiket->id],
            ['petugas_id' => Auth::id()]
        );

        $lembarKerja->update([
            'status_berkas_dataset' => $request->input('status_berkas_dataset', $lembarKerja->status_berkas_dataset),
            'status_data_sertipikat_bt' => $request->input('status_data_sertipikat_bt', $lembarKerja->status_data_sertipikat_bt),
            'status_data_sertipikat_su' => $request->input('status_data_sertipikat_su', $lembarKerja->status_data_sertipikat_su),
            'status_sosialisasi' => $request->input('status_sosialisasi', $lembarKerja->status_sosialisasi),
            'status_public_response' => $request->input('status_public_response', $lembarKerja->status_public_response),
            'status_sertipikat' => $request->input('status_sertipikat'),
            'status_dokumen_bt' => $request->input('status_dokumen_bt'),
            'status_dokumen_su' => $request->input('status_dokumen_su'),
            'tanggal_diserahkan' => $request->input('tanggal_diserahkan'),
            'tanggal_kembali' => $request->input('tanggal_kembali'),
            'jumlah_berkas' => $request->input('jumlah_berkas'),
            'jumlah_halaman' => $request->input('jumlah_halaman'),
            'gabungan' => $request->boolean('gabungan'),
            'jumlah_berkas_dikembalikan' => $request->input('jumlah_berkas_dikembalikan'),
            'pengembalian_sementara' => $request->boolean('pengembalian_sementara'),
            'keterangan_status' => $request->input('keterangan_status'),
            'catatan' => $request->input('catatan'),
            'petugas_id' => Auth::id(),
        ]);

        // Gerbang Validator (alur paralel): status sertipikat DISERAHKAN menandai
        // diserahkan_ke_validator = true → membuka tahap Validator BT/SU
        // (tanpa menunggu Verifikator).
        if ($request->input('status_sertipikat') === 'diserahkan' && ! $tiket->diserahkan_ke_validator) {
            $tiket->update(['diserahkan_ke_validator' => true]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Warkah',
                'stage_ke' => 'Validator (diserahkan_ke_validator)',
                'changed_by' => Auth::id(),
                'keterangan' => 'Warkah menyerahkan sertipikat ke Validator (status DISERAHKAN). Validator BT/SU kini dapat mulai secara paralel.',
            ]);
        }

        return back()->with('success', 'Progres lembar kerja Warkah disimpan.');
    }

    protected function ensureMine(Tiket $tiket): void
    {
        $penugasan = $tiket->penugasanAktif($this->stageName());
        if (! $penugasan || $penugasan->user_id !== Auth::id()) {
            abort(403, 'Tiket tidak sedang dalam antrian pekerjaan Anda.');
        }
    }
}
