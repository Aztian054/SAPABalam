<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use App\Models\ValidasiBtel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidatorBtelController extends StageWorkflow
{
    protected function stageName(): string
    {
        return 'validasi_btel';
    }

    protected function stageLabel(): string
    {
        return 'Validasi Pra-Buku Tanah Elektronik (BT)';
    }

    protected function viewBase(): string
    {
        return 'validator_btel';
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

        $lembar = ValidasiBtel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'validator_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        return view('validator_btel.show', compact('tiket', 'isActive', 'mine', 'lembar', 'stage'))
            ->with('stageLabel', $this->stageLabel());
    }

    public function simpanHasil(Request $request, int $id)
    {
        $tiket = Tiket::findOrFail($id);
        $this->ensureMine($tiket);

        $request->validate([
            'kesesuaian_nama' => 'nullable|in:sesuai,tidak_sesuai',
            'kesesuaian_luas' => 'nullable|in:sesuai,tidak_sesuai',
            'kesesuaian_nib' => 'nullable|in:sesuai,tidak_sesuai',
            'status_validasi' => 'required|in:lulus,ditolak',
            'catatan' => 'nullable|string',
        ]);

        $lembar = ValidasiBtel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'validator_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        $lembar->update([
            'kesesuaian_nama' => $request->input('kesesuaian_nama'),
            'kesesuaian_luas' => $request->input('kesesuaian_luas'),
            'kesesuaian_nib' => $request->input('kesesuaian_nib'),
            'status_validasi' => $request->input('status_validasi'),
            'catatan' => $request->input('catatan'),
            'validator_id' => Auth::id(),
        ]);

        return back()->with('success', 'Hasil validasi BT disimpan.');
    }

    protected function ensureMine(Tiket $tiket): void
    {
        $penugasan = $tiket->penugasanAktif($this->stageName());
        if (! $penugasan || $penugasan->user_id !== Auth::id()) {
            abort(403, 'Berkas tidak sedang dalam antrian pekerjaan Anda.');
        }
    }
}
