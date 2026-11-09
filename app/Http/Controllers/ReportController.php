<?php

namespace App\Http\Controllers;

use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Services\SpreadsheetMlBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->queryLaporan($request);

        $tikets = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $stats = [
            'total' => Tiket::count(),
            'selesai' => Tiket::where('status', 'selesai')->count(),
            'proses' => Tiket::where('status', 'diproses')->count(),
            'ditolak' => Tiket::where('status', 'ditolak')->count(),
            'di_database' => Tiket::where('status', 'di_database')->count(),
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
        $tikets = $this->queryLaporan($request)->orderByDesc('id')->get();

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::today()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::today()->toDateString();

        return view('reports.print', compact('tikets', 'startDate', 'endDate'));
    }

    /** Ekspor rekap monitoring V2 ke Excel (.xls). */
    public function export(Request $request)
    {
        $tikets = $this->queryLaporan($request)->orderByDesc('id')->get();

        $headers = [
            'No', 'Kode Tiket', 'No. Berkas', 'Nama Pemohon', 'NIK', 'Jenis Permohonan',
            'Tanggal Masuk', 'Tanggal Selesai', 'Status', 'Jumlah Bidang',
            'Kelurahan/Desa', 'Kecamatan', 'Petugas Loket', 'Pemegang Saat Ini',
            'Diserahkan Kepada', 'Tanggal Serah Terima', 'Keterangan',
        ];

        $rows = [];
        foreach ($tikets->values() as $i => $t) {
            $rows[] = [
                $i + 1,
                $t->kode_tiket,
                $t->no_berkas ?? '-',
                $t->nama_pemohon,
                $t->nik_pemohon ?? '-',
                $t->jenisPermohonan?->nama ?? '-',
                $t->tanggal_masuk?->format('d/m/Y') ?? '-',
                $t->tanggal_selesai?->format('d/m/Y') ?? '-',
                $t->status_label,
                max(1, (int) $t->jumlah_bidang),
                $t->kelurahan_desa ?? '-',
                $t->kecamatan ?? '-',
                $t->nama_petugas_loket ?? $t->petugasLoket?->name ?? '-',
                $t->penanggungJawab?->name ?? '-',
                $t->penerima_nama ?? '-',
                $t->diserahkan_pada?->format('d/m/Y H:i') ?? '-',
                $t->keterangan ?? '-',
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
        $tikets = $this->queryLaporan($request)->orderByDesc('id')->get();

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::today()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::today()->toDateString();
        $status = $request->filled('status') ? $request->status : '';

        return view('reports.print_rapi', compact('tikets', 'startDate', 'endDate', 'status'));
    }

    /** Query dasar laporan dengan filter yang sama untuk semua varian cetak/ekspor. */
    private function queryLaporan(Request $request): Builder
    {
        $query = Tiket::with(['jenisPermohonan', 'bidangTanahs', 'petugasLoket', 'penanggungJawab']);

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
            $query->where(function (Builder $q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('no_berkas', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%");
            });
        }

        return $query;
    }
}
