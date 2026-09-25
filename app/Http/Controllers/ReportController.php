<?php

namespace App\Http\Controllers;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Services\SpreadsheetMlBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'penugasans.user', 'petugasLoket']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_masuk', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_masuk', '<=', $request->end_date);
        }

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%");
            });
        }

        $tikets = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => Tiket::count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'proses' => Tiket::whereNotIn('status', ['selesai', 'batal'])->count(),
            'dikembalikan' => Tiket::where('status', 'dikembalikan')->count(),
            'batal' => Tiket::where('status', 'batal')->count(),
        ];

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::today()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::today()->toDateString();

        $jenisPermohonans = JenisPermohonan::orderBy('kode')->get();
        $jenisPermohonanId = $request->filled('jenis_permohonan_id') ? $request->jenis_permohonan_id : '';
        $status = $request->filled('status') ? $request->status : '';

        return view('reports.index', compact(
            'tikets', 'stats', 'startDate', 'endDate',
            'jenisPermohonans', 'jenisPermohonanId', 'status'
        ));
    }

    public function print(Request $request)
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_masuk', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_masuk', '<=', $request->end_date);
        }

        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%");
            });
        }

        $tikets = $query->orderByDesc('id')->get();

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::today()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::today()->toDateString();

        return view('reports.print', compact('tikets', 'startDate', 'endDate'));
    }

    /**
     * Ekspor rekap monitoring ke Excel (.xls) — kolom mengikuti
     * "ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL)": petugas per tahap,
     * tanggal per tahap, status sertipikat, dst.
     */
    public function export(Request $request)
    {
        $query = Tiket::with([
            'jenisPermohonan',
            'bidangTanahs',
            'penugasans.user',
            'lembarKerjaWarkahs',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_masuk', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_masuk', '<=', $request->end_date);
        }
        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%");
            });
        }

        $tikets = $query->orderByDesc('id')->get();

        $headers = [
            'No', 'Kode Tiket', 'Nama Pemohon', 'NIK', 'Jenis Permohonan',
            'Tanggal Masuk', 'Tanggal Selesai', 'Status',
            'Pembetulan', 'Jumlah Bidang', 'Kelurahan/Desa', 'Kecamatan',
            'Petugas Loket', 'Verifikator', 'PIC Warkah', 'Validator BT', 'Validator SU',
            'Alih Media BT', 'Alih Media SU', 'Tgl Verifikasi', 'Tgl Warkah',
            'Tgl Validasi BT', 'Tgl Validasi SU', 'Tgl Alih Media BT', 'Tgl Alih Media SU',
            'Status Sertipikat', 'Keterangan',
        ];

        $rows = [];
        foreach ($tikets->values() as $i => $t) {
            $p = collect($t->penugasans);
            $pic = fn (string $stage) => $p->where('stage', $stage)->last()?->user?->name ?? '-';
            $doneDate = fn (string $stage) => $p->where('stage', $stage)->where('status', 'selesai')->first()?->tanggal_selesai;
            $warkahLast = $t->lembarKerjaWarkahs->last();

            $rows[] = [
                $i + 1,
                $t->kode_tiket,
                $t->nama_pemohon,
                $t->nik_pemohon ?? '-',
                $t->jenisPermohonan?->nama ?? '-',
                $t->tanggal_masuk?->format('d/m/Y') ?? '-',
                $t->tanggal_selesai?->format('d/m/Y') ?? '-',
                $t->status_label,
                $t->status_pembetulan ?? 'P0',
                max(1, (int) $t->jumlah_bidang),
                $t->kelurahan_desa ?? '-',
                $t->kecamatan ?? '-',
                $t->nama_petugas_loket ?? '-',
                $pic('verifikasi'),
                $pic('warkah'),
                $pic('validasi_btel'),
                $pic('validasi_suel'),
                $pic('alih_media_btel'),
                $pic('alih_media_suel'),
                optional($doneDate('verifikasi'))?->format('d/m/Y') ?? '-',
                optional($doneDate('warkah'))?->format('d/m/Y') ?? '-',
                optional($doneDate('validasi_btel'))?->format('d/m/Y') ?? '-',
                optional($doneDate('validasi_suel'))?->format('d/m/Y') ?? '-',
                optional($doneDate('alih_media_btel'))?->format('d/m/Y') ?? '-',
                optional($doneDate('alih_media_suel'))?->format('d/m/Y') ?? '-',
                $warkahLast?->status_sertipikat ? ucwords(str_replace('_', ' ', $warkahLast->status_sertipikat)) : '-',
                $warkahLast?->keterangan_status ?? '-',
            ];
        }

        $xml = SpreadsheetMlBuilder::build('Rekap Monitoring', $headers, $rows);

        $filename = 'Rekap_LOKET2026_'.now()->format('Ymd_His').'.xls';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Cetak rapi (landscape, PDF-ready via browser print dialog). */
    public function printRapi(Request $request)
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'petugasLoket']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis_permohonan_id')) {
            $query->where('jenis_permohonan_id', $request->jenis_permohonan_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_masuk', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_masuk', '<=', $request->end_date);
        }
        if ($request->filled('q')) {
            $s = trim($request->q);
            $query->where(function ($q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%");
            });
        }

        $tikets = $query->orderByDesc('id')->get();

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::today()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::today()->toDateString();
        $status = $request->filled('status') ? $request->status : '';

        return view('reports.print_rapi', compact('tikets', 'startDate', 'endDate', 'status'));
    }
}
