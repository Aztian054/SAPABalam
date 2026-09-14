<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $tiket = null;
        if ($request->filled('q')) {
            $tiket = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penugasans.user'])
                ->where('kode_tiket', 'like', "%{$request->q}%")
                ->first();

            if (! $tiket) {
                $tiket = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penugasans.user'])
                    ->where('nomor_telepon', 'like', "%{$request->q}%")
                    ->first();
            }
        }

        return view('tracking.index', compact('tiket'));
    }

    public function show(string $kode)
    {
        $tiket = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'penugasans.user',
            'riwayatStatuses',
        ])->where('kode_tiket', $kode)->firstOrFail();

        return view('tracking.show', compact('tiket'));
    }
}
