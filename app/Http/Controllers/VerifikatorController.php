<?php

namespace App\Http\Controllers;

use App\Models\SaranKoreksi;
use App\Models\Tiket;
use App\Models\VerifikasiBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikatorController extends StageWorkflow
{
    protected function stageName(): string
    {
        return 'verifikasi';
    }

    protected function stageLabel(): string
    {
        return 'Pemeriksaan Berkas Verifikator';
    }

    protected function viewBase(): string
    {
        return 'verifikator';
    }

    /** Detail khusus: tampilkan lembar verifikasi iterasi terakhir. */
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

        $verifikasi = VerifikasiBerkas::where('tiket_id', $tiket->id)->latest('iterasi')->first();
        $templateKoreksis = SaranKoreksi::where('jenis_permohonan_id', $tiket->jenis_permohonan_id)->get();

        return view('verifikator.show', compact('tiket', 'isActive', 'mine', 'verifikasi', 'templateKoreksis', 'stage'))
            ->with('stageLabel', $this->stageLabel());
    }

    /** Simpan hasil pemeriksaan verifikasi. */
    public function simpanHasil(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $penugasan = $tiket->penugasanAktif($this->stageName());
        if (! $penugasan || $penugasan->user_id !== Auth::id()) {
            return back()->with('error', 'Berkas tidak sedang dalam antrian Anda.');
        }

        $request->validate([
            'status_verifikasi' => 'required|in:lengkap,perbaikan,konsul,batal',
            'catatan' => 'nullable|string',
            'dokumen_kurang' => 'nullable|array',
            'dokumen_kurang.*' => 'string',
        ]);

        $verifikasi = VerifikasiBerkas::where('tiket_id', $tiket->id)->latest('iterasi')->first();
        if (! $verifikasi) {
            $verifikasi = new VerifikasiBerkas(['tiket_id' => $tiket->id, 'iterasi' => 1]);
        }

        $verifikasi->fill([
            'petugas_id' => Auth::id(),
            'status_pembetulan' => $tiket->status_pembetulan,
            'tanggal_selesai' => now()->toDateString(),
            'status' => $request->input('status_verifikasi'),
            'catatan' => $request->input('catatan'),
            'dokumen_kurang' => $request->input('dokumen_kurang', []),
        ])->save();

        return back()->with('success', 'Hasil pemeriksaan Verifikator disimpan.');
    }
}
