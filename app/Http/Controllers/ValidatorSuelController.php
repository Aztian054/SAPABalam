<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use App\Models\ValidasiSuel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatorSuelController extends StageWorkflow
{
    protected function stageName(): string
    {
        return 'validasi_suel';
    }

    protected function stageLabel(): string
    {
        return 'Validasi Pra-Surat Ukur Elektronik (SU)';
    }

    protected function viewBase(): string
    {
        return 'validator_suel';
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

        $stage = $this->stageName();
        $isActive = (bool) $tiket->penugasanAktif($stage);
        $mine = $tiket->penugasanAktif($stage)?->user_id === Auth::id();

        $lembar = ValidasiSuel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'validator_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        return view('validator_suel.show', compact('tiket', 'isActive', 'mine', 'lembar', 'stage'))
            ->with('stageLabel', $this->stageLabel());
    }

    public function simpanHasil(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $penugasan = $tiket->penugasanAktif($this->stageName());
        if (! $penugasan || $penugasan->user_id !== Auth::id()) {
            return back()->with('error', 'Berkas tidak sedang dalam antrian Anda.');
        }

        $request->validate([
            'kesesuaian_nama' => 'nullable|in:sesuai,tidak_sesuai',
            'kesesuaian_luas' => 'nullable|in:sesuai,tidak_sesuai',
            'cocok_letak' => 'nullable|in:sesuai,tidak_sesuai',
            'status_validasi' => 'required|in:lulus,ditolak',
            'catatan' => 'nullable|string',
        ]);

        $lembar = ValidasiSuel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'validator_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        $lembar->update([
            'kesesuaian_nama' => $request->input('kesesuaian_nama'),
            'kesesuaian_luas' => $request->input('kesesuaian_luas'),
            'cocok_letak' => $request->input('cocok_letak'),
            'status_validasi' => $request->input('status_validasi'),
            'catatan' => $request->input('catatan'),
            'validator_id' => Auth::id(),
        ]);

        return back()->with('success', 'Hasil validasi SU disimpan.');
    }
}
