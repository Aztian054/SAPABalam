<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\PengaduanCatatan;
use App\Models\PengaduanRiwayat;
use App\Models\Setting;
use App\Models\User;
use App\Services\NotifikasiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Loket Pengaduan (Blueprint V2, modul 08).
 *
 * Membuat Nomor Pengaduan `LP` + ddmmyyyy + urut 4 digit secara otomatis,
 * mengirimkannya ke 1 unit tujuan, dan mengirim ulang bila dikembalikan.
 * Penanganan (terima, tutup, kembalikan, minta bantuan, balas bantuan)
 * ada di PengaduanUnitController.
 */
class PengaduanController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);

        $query = Pengaduan::with(['tiket', 'pembuat']);

        if (! $user->isAdmin()) {
            $query->where('pembuat_id', $user->id);
        }

        $this->terapkanFilter($query, $request);

        $pengaduans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('pengaduan.index', [
            'pengaduans' => $pengaduans,
            'ringkasan' => $this->ringkasan($user),
        ]);
    }

    public function create(): View
    {
        abort_unless(Auth::user()->isLoketPengaduan(), 403);

        return view('pengaduan.create', [
            'jenisPengaduan' => Pengaduan::JENIS,
            'daftarSeksi' => User::SEKSI,
            'nomorPengaduan' => $this->nomorBaru(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan(), 403);

        $anonim = $request->boolean('is_anonim');

        $aturan = [
            'nama_pengadu' => [Rule::requiredIf(! $anonim), 'nullable', 'string', 'max:150'],
            'nik_pengadu' => ['nullable', 'digits:16'],
            'no_hp_pengadu' => [Rule::requiredIf(! $anonim), 'nullable', 'string', 'max:30'],
            'email_pengadu' => ['nullable', 'email', 'max:100'],
            'alamat_pengadu' => ['nullable', 'string', 'max:255'],
            'is_anonim' => ['nullable', 'boolean'],
            'jenis' => ['required', Rule::in(array_keys(Pengaduan::JENIS))],
            'seksi_tujuan' => ['required', Rule::in(array_keys(User::SEKSI))],
            'uraian' => ['required', 'string', 'min:20', 'max:2000'],
            // Field khusus Permasalahan Tanah
            'hal' => ['nullable', 'string', 'max:255'],
            'bertindak_untuk' => ['nullable', 'string', 'max:20'],
            'surat_kuasa' => ['nullable', 'string', 'max:255'],
            'nama_kuasa' => ['nullable', 'string', 'max:150'],
            'nik_kuasa' => ['nullable', 'digits:16'],
            'alamat_kuasa' => ['nullable', 'string', 'max:255'],
            'telp_kuasa' => ['nullable', 'string', 'max:30'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'luas' => ['nullable', 'string', 'max:100'],
            'status_tanah' => ['nullable', 'string', 'max:100'],
            'kategori_aduan' => ['nullable', 'string', 'max:100'],
            'kelompok_aduan' => ['nullable', 'string', 'max:100'],
            'uraian_masalah_1' => ['nullable', 'string', 'max:500'],
            'uraian_masalah_2' => ['nullable', 'string', 'max:500'],
            'uraian_masalah_3' => ['nullable', 'string', 'max:500'],
            'uraian_masalah_4' => ['nullable', 'string', 'max:500'],
            'uraian_masalah_5' => ['nullable', 'string', 'max:500'],
            'lampiran_1' => ['nullable', 'string', 'max:255'],
            'lampiran_2' => ['nullable', 'string', 'max:255'],
            'lampiran_3' => ['nullable', 'string', 'max:255'],
            'lampiran_4' => ['nullable', 'string', 'max:255'],
        ];

        $validated = $request->validate($aturan);

        $nomorPengaduan = $this->nomorBaru();

        $uraianLengkap = $validated['uraian'];

        if ($validated['jenis'] === 'permasalahan_tanah') {
            $detail = $this->susunDetailTanah($validated);
            $uraianLengkap = $detail."\n\n---\n\nUraian Singkat:\n".$validated['uraian'];
        }

        $pengaduan = DB::transaction(function () use ($validated, $user, $anonim, $nomorPengaduan, $uraianLengkap) {
            $pengaduan = Pengaduan::create([
                'nomor_pengaduan' => $nomorPengaduan,
                'tanggal_buat' => now()->toDateString(),
                'pembuat_id' => $user->id,
                'nama_pengadu' => $anonim ? null : $validated['nama_pengadu'],
                'nik_pengadu' => $anonim ? null : ($validated['nik_pengadu'] ?? null),
                'no_hp_pengadu' => $anonim ? null : $validated['no_hp_pengadu'],
                'email_pengadu' => $anonim ? null : ($validated['email_pengadu'] ?? null),
                'alamat_pengadu' => $validated['alamat_pengadu'] ?? null,
                'is_anonim' => $anonim,
                'jenis' => $validated['jenis'],
                'uraian' => $uraianLengkap,
                'seksi_tujuan' => $validated['seksi_tujuan'],
                'status' => 'terkirim',
            ]);

            PengaduanRiwayat::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_status' => null,
                'ke_status' => 'terkirim',
                'oleh_id' => $user->id,
                'catatan' => 'Pengaduan dibuat dan dikirim ke '.$this->seksiLabel($validated['seksi_tujuan']).'.',
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'loket',
                'seksi' => null,
                'jenis' => 'kirim_awal',
                'catatan' => 'Pengaduan dikirim dari Loket Pengaduan ke '.$this->seksiLabel($validated['seksi_tujuan']).'.',
                'urutan' => 1,
            ]);

            return $pengaduan;
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.masuk',
            penerimaIds: $this->notifikasi->idUnitPengaduan((string) $pengaduan->seksi_tujuan),
            judul: "Pengaduan baru {$pengaduan->nomor_pengaduan}",
            pesan: 'Jenis '.$pengaduan->jenis_label.' — segera TERIMA dan tindak lanjuti.',
            url: route('pengaduan-unit.index'),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return redirect()->route('pengaduan.show', $pengaduan)
            ->with('success', "Pengaduan {$pengaduan->nomor_pengaduan} terkirim ke {$this->seksiLabel($pengaduan->seksi_tujuan)}.");
    }

    public function show(Pengaduan $pengaduan): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);
        abort_unless($user->isAdmin() || $pengaduan->pembuat_id === $user->id, 403);

        $pengaduan->load(['tiket', 'pembuat', 'diterimaOleh', 'selesaiOleh', 'riwayats.user', 'catatans', 'bantuans']);

        return view('pengaduan.show', [
            'pengaduan' => $pengaduan,
            'daftarSeksi' => User::SEKSI,
        ]);
    }

    public function cetakPreview(Pengaduan $pengaduan): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);
        abort_unless($user->isAdmin() || $pengaduan->pembuat_id === $user->id, 403);

        $pengaduan->load(['tiket', 'pembuat', 'diterimaOleh', 'selesaiOleh', 'riwayats.user', 'catatans', 'bantuans']);

        return view('pengaduan.cetak_preview', [
            'pengaduan' => $pengaduan,
        ]);
    }

    public function cetakPdf(Pengaduan $pengaduan)
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);
        abort_unless($user->isAdmin() || $pengaduan->pembuat_id === $user->id, 403);

        $pengaduan->load(['tiket', 'pembuat', 'diterimaOleh', 'selesaiOleh', 'riwayats.user', 'catatans', 'bantuans']);

        $pdf = Pdf::loadView('pengaduan.cetak_pdf', [
            'pengaduan' => $pengaduan,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($pengaduan->nomor_pengaduan.'.pdf');
    }

    public function cetak(Pengaduan $pengaduan)
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);
        abort_unless($user->isAdmin() || $pengaduan->pembuat_id === $user->id, 403);

        $pengaduan->load(['tiket', 'pembuat', 'diterimaOleh', 'selesaiOleh', 'riwayats.user', 'catatans', 'bantuans']);

        return view('pengaduan.cetak', [
            'pengaduan' => $pengaduan,
        ]);
    }

    public function kirimUlang(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanMilikLoket($pengaduan, $user);

        if ($pengaduan->status !== 'dikembalikan') {
            return back()->with('error', 'Kirim ulang hanya untuk pengaduan yang dikembalikan unit tujuan.');
        }

        $validated = $request->validate([
            'seksi_tujuan' => ['required', Rule::in(array_keys(User::SEKSI))],
            'catatan' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $lamaSeksi = $pengaduan->seksi_tujuan;

        DB::transaction(function () use ($pengaduan, $user, $validated, $lamaSeksi) {
            $pengaduan->update([
                'status' => 'terkirim',
                'seksi_tujuan' => $validated['seksi_tujuan'],
                'catatan_terakhir' => $validated['catatan'],
            ]);

            PengaduanRiwayat::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_status' => 'dikembalikan',
                'ke_status' => 'terkirim',
                'oleh_id' => $user->id,
                'catatan' => 'Kirim ulang ke '.$this->seksiLabel($validated['seksi_tujuan']).". Catatan: {$validated['catatan']}",
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'loket',
                'seksi' => null,
                'jenis' => 'kirim_awal',
                'catatan' => 'Kirim ulang dari '.$this->seksiLabel($lamaSeksi).' ke '.$this->seksiLabel($validated['seksi_tujuan']).". Catatan: {$validated['catatan']}",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.masuk',
            penerimaIds: $this->notifikasi->idUnitPengaduan((string) $pengaduan->seksi_tujuan),
            judul: "Pengaduan dikirim ulang: {$pengaduan->nomor_pengaduan}",
            pesan: 'Pengaduan sebelumnya dikembalikan dan kini ditujukan ke unit Anda.',
            url: route('pengaduan-unit.index'),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return redirect()->route('pengaduan.show', $pengaduan)
            ->with('success', "Pengaduan {$pengaduan->nomor_pengaduan} dikirim ulang.");
    }

    public function selesai(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isLoketPengaduan() || $user->isAdmin(), 403);

        $query = Pengaduan::with(['tiket', 'selesaiOleh'])->where('status', 'selesai');

        if (! $user->isAdmin()) {
            $query->where('pembuat_id', $user->id);
        }

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        $this->terapkanTanggal($query, $request, 'selesai_pada');

        $pengaduans = $query->orderByDesc('selesai_pada')->paginate(20)->withQueryString();

        return view('pengaduan.selesai', compact('pengaduans'));
    }

    private function pastikanMilikLoket(Pengaduan $pengaduan, $user): void
    {
        abort_unless($user->isLoketPengaduan(), 403);
        abort_unless($pengaduan->pembuat_id === $user->id, 403);
    }

    /** @param  Builder<Pengaduan>  $query */
    private function terapkanFilter(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis')->toString());
        }

        if ($request->filled('seksi_tujuan')) {
            $query->where('seksi_tujuan', $request->string('seksi_tujuan')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $this->terapkanTanggal($query, $request, 'tanggal_buat');
    }

    private function terapkanTanggal(Builder $query, Request $request, string $kolom): void
    {
        if ($request->filled('tanggal_dari')) {
            $query->whereDate($kolom, '>=', Carbon::parse($request->string('tanggal_dari')->toString()));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate($kolom, '<=', Carbon::parse($request->string('tanggal_sampai')->toString()));
        }
    }

    private function nomorBaru(): string
    {
        $prefix = Setting::teks('prefix_pengaduan', 'LP');
        $padding = Setting::angka('padding_tiket', 4);
        $tanggal = now()->format('dmY');

        for ($urut = 1; $urut <= 9999; $urut++) {
            $nomor = $prefix.$tanggal.str_pad((string) $urut, $padding, '0', STR_PAD_LEFT);

            if (! Pengaduan::where('nomor_pengaduan', $nomor)->exists()) {
                return $nomor;
            }
        }

        throw new ValidationException('Kuota nomor pengaduan harian habis. Hubungi Admin.');
    }

    private function seksiLabel(string $seksi): string
    {
        return User::SEKSI[$seksi] ?? $seksi;
    }

    /** Susun detail surat formal untuk jenis permasalahan_tanah. */
    private function susunDetailTanah(array $v): string
    {
        $baris = [];

        $baris[] = 'HAL: '.($v['hal'] ?? '-');
        $baris[] = '';
        $baris[] = 'Yang bertanda tangan di bawah ini: '.($v['nama_pengadu'] ?? '-');
        $baris[] = 'Nama (Pengadu): '.($v['nama_pengadu'] ?? '-');
        $baris[] = 'Nomor Identitas: '.($v['nik_pengadu'] ?? '-');
        $baris[] = 'Bertindak Untuk: '.($v['bertindak_untuk'] === 'kuasa' ? 'Penerima Kuasa' : 'Diri Sendiri');
        $baris[] = 'Surat Kuasa (jika dikuasakan): '.($v['surat_kuasa'] ?? '-');
        $baris[] = 'Nama & Nomor Identitas Kuasa: '.($v['nama_kuasa'] ?? '-').' / '.($v['nik_kuasa'] ?? '-');
        $baris[] = 'Alamat Kuasa: '.($v['alamat_kuasa'] ?? '-');
        $baris[] = 'Nomor Telp/HP/Email Kuasa: '.($v['telp_kuasa'] ?? '-');
        $baris[] = '';
        $baris[] = 'Dengan ini menyampaikan pengaduan masalah tanah:';
        $baris[] = 'Letak: '.($v['desa_kelurahan'] ?? '-').', '.($v['kecamatan'] ?? '-').', '.($v['kabupaten_kota'] ?? '-').', '.($v['provinsi'] ?? '-');
        $baris[] = 'Desa/Kelurahan: '.($v['desa_kelurahan'] ?? '-');
        $baris[] = 'Kecamatan: '.($v['kecamatan'] ?? '-');
        $baris[] = 'Kabupaten/Kota: '.($v['kabupaten_kota'] ?? '-');
        $baris[] = 'Provinsi: '.($v['provinsi'] ?? '-');
        $baris[] = 'Luas: '.($v['luas'] ?? '-');
        $baris[] = 'Status Tanah: '.($v['status_tanah'] ?? '-');
        $baris[] = 'Pokok Masalah: '.($v['kategori_aduan'] ?? '-');
        $baris[] = 'Kategori Aduan: '.($v['kategori_aduan'] ?? '-');
        $baris[] = 'Kelompok Aduan: '.($v['kelompok_aduan'] ?? '-');
        $baris[] = '';
        $baris[] = 'Uraian Singkat Masalah:';
        for ($i = 1; $i <= 5; $i++) {
            $key = "uraian_masalah_{$i}";
            if (($v[$key] ?? '') !== '') {
                $baris[] = "{$i}. {$v[$key]}";
            }
        }
        $baris[] = '';
        $baris[] = 'Data yang Dilampirkan:';
        for ($i = 1; $i <= 4; $i++) {
            $key = "lampiran_{$i}";
            if (($v[$key] ?? '') !== '') {
                $baris[] = "{$i}. {$v[$key]}";
            }
        }

        return implode(PHP_EOL, $baris);
    }

    /** @return array{hari_ini:int, terkirim:int, diterima:int, dikembalikan:int, selesai:int} */
    private function ringkasan(User $user): array
    {
        $base = fn (): Builder => Pengaduan::query()
            ->when(! $user->isAdmin(), fn (Builder $q) => $q->where('pembuat_id', $user->id));

        return [
            'hari_ini' => $base()->whereDate('tanggal_buat', now()->toDateString())->count(),
            'terkirim' => $base()->where('status', 'terkirim')->count(),
            'diterima' => $base()->where('status', 'diterima')->count(),
            'dikembalikan' => $base()->where('status', 'dikembalikan')->count(),
            'selesai' => $base()->where('status', 'selesai')->count(),
        ];
    }
}
