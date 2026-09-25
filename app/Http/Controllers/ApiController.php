<?php

namespace App\Http\Controllers;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    public function tracking(string $kodeTiket): JsonResponse
    {
        $tiket = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'riwayatStatuses' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
        ])->where('kode_tiket', urldecode($kodeTiket))->first();

        if (! $tiket) {
            return response()->json([
                'success' => false,
                'message' => 'Kode tiket tidak ditemukan dalam sistem.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'no_tiket' => $tiket->kode_tiket,
                'status_pembetulan' => $tiket->status_pembetulan,
                'status' => $tiket->status,
                'status_label' => $tiket->status_label,
                'status_badge' => $tiket->status_badge,
                'tanggal_masuk' => $tiket->tanggal_masuk->format('d/m/Y'),
                'tanggal_selesai' => $tiket->tanggal_selesai?->format('d/m/Y'),
                'nama_pemohon' => $tiket->nama_pemohon,
                'jenis_permohonan' => [
                    'kode' => $tiket->jenisPermohonan->kode,
                    'nama' => $tiket->jenisPermohonan->nama,
                    'kategori' => $tiket->jenisPermohonan->kategori,
                ],
                'jumlah_bidang' => $tiket->jumlah_bidang,
                'bidang_tanah' => $tiket->bidangTanahs->map(function ($b) {
                    return [
                        'nib' => $b->nib,
                        'no_sertifikat_lama' => $b->no_sertifikat_lama,
                        'no_sertifikat_elektronik' => $b->no_sertifikat_elektronik,
                        'jenis_hak' => $b->jenis_hak,
                        'nama_pemegang_hak' => $b->nama_pemegang_hak,
                        'desa_kelurahan' => $b->desa_kelurahan,
                        'kecamatan' => $b->kecamatan,
                    ];
                }),
                'riwayat_timeline' => $tiket->riwayatStatuses->map(function ($r) {
                    return [
                        'stage_dari' => $r->stage_dari,
                        'stage_ke' => $r->stage_ke,
                        'keterangan' => $r->keterangan,
                        'waktu' => $r->created_at->format('d/m/Y H:i:s'),
                    ];
                }),
            ],
        ]);
    }

    public function stats(): JsonResponse
    {
        $today = Carbon::today();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tiket' => Tiket::count(),
                'tiket_hari_ini' => Tiket::whereDate('tanggal_masuk', $today)->count(),
                'tiket_selesai' => Tiket::where('status', 'selesai')->count(),
                'stage_counts' => [
                    'diterima' => Tiket::where('status', 'diterima')->count(),
                    'verifikasi' => Tiket::where('status', 'verifikasi')->count(),
                    'warkah' => Tiket::where('status', 'warkah')->count(),
                    'validasi_btel' => Tiket::where('status', 'validasi_btel')->count(),
                    'validasi_suel' => Tiket::where('status', 'validasi_suel')->count(),
                    'alih_media_btel' => Tiket::where('status', 'alih_media_btel')->count(),
                    'alih_media_suel' => Tiket::where('status', 'alih_media_suel')->count(),
                    'selesai' => Tiket::where('status', 'selesai')->count(),
                    'dikembalikan' => Tiket::where('status', 'dikembalikan')->count(),
                    'batal' => Tiket::where('status', 'batal')->count(),
                ],
            ],
        ]);
    }

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
