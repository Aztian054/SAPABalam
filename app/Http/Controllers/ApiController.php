<?php

namespace App\Http\Controllers;

use App\Models\JenisPermohonan;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /** Master data jenis permohonan untuk konsumen API internal. */
    public function jenisPermohonan(): JsonResponse
    {
        $data = JenisPermohonan::where('is_active', true)
            ->with('persyaratanDokumens')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
