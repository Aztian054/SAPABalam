<?php

namespace App\Http\Controllers;

use App\Models\AlihMediaBtel;
use App\Models\Tiket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlihMediaBtelController extends StageWorkflow
{
    protected function stageName(): string
    {
        return 'alih_media_btel';
    }

    protected function stageLabel(): string
    {
        return 'Alih Media Pra-Buku Tanah Elektronik (BT)';
    }

    protected function viewBase(): string
    {
        return 'alih_media_btel';
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

        $lembar = AlihMediaBtel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'petugas_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        return view('alih_media_btel.show', compact('tiket', 'isActive', 'mine', 'lembar', 'stage'))
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
            'status_scan_buku_tanah' => 'nullable|in:belum,sudah,kualitas_buruk',
            'status_upload_kkp' => 'nullable|in:belum,sudah',
            'status_ttd_elektronik' => 'nullable|in:belum,sudah',
            'tanggal_terbit_sertifikat_el' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $lembar = AlihMediaBtel::firstOrCreate(
            ['tiket_id' => $tiket->id, 'petugas_id' => Auth::id()],
            ['tanggal_mulai' => now()->toDateString()]
        );

        $lembar->update($request->only([
            'status_scan_buku_tanah', 'status_upload_kkp',
            'status_ttd_elektronik', 'tanggal_terbit_sertifikat_el', 'catatan',
        ]) + ['petugas_id' => Auth::id()]);

        return back()->with('success', 'Progres Alih Media BT disimpan.');
    }
}
