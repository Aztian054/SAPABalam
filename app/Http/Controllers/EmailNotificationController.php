<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use App\Services\TiketFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailNotificationController extends Controller
{
    /** Kirim ulang notifikasi email revisi ke pemohon oleh petugas yang bertanggung jawab. */
    public function kirimUlangRevisi(Request $request, int $id): RedirectResponse
    {
        $tiket = Tiket::with('jenisPermohonan')->findOrFail($id);

        if (empty($tiket->email_pemohon) || ! filter_var($tiket->email_pemohon, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Email pemohon belum terisi atau tidak valid pada berkas ini.');
        }

        $catatan = $tiket->catatanRevisis()->latest('id')->first()?->isi_revisi;

        app(TiketFlowService::class)->sendRevisiEmail($tiket, $catatan);

        return back()->with('success', "Notifikasi revisi berhasil dikirim ulang ke {$tiket->email_pemohon}.");
    }
}
