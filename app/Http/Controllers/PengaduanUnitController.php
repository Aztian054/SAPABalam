<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\PengaduanBantuan;
use App\Models\PengaduanCatatan;
use App\Models\PengaduanRiwayat;
use App\Models\User;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Penanganan pengaduan oleh unit tujuan (Blueprint V2, modul 09).
 *
 * Kasi Seksi 1–5 / Kasubbag TU hanya melihat pengaduan seksinya, lalu
 * TERIMA → TUTUP, atau KEMBALIKAN bila salah tujuan. Kasi asli
 * (seksi_tujuan) juga dapat MEMINTA BANTUAN ke Kasi lain dan hanya dia
 * yang boleh klik SELESAI AKHIR.
 */
class PengaduanUnitController extends Controller
{
    public function __construct(private readonly NotifikasiService $notifikasi) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isPenanganPengaduan() || $user->isAdmin(), 403);

        $query = Pengaduan::with(['tiket', 'pembuat']);

        if (! $user->isAdmin()) {
            // Hanya pengaduan di mana user adalah target (asli atau tujuan bantuan)
            $seksi = $user->seksiPengaduan();
            $query->where(function (Builder $q) use ($seksi) {
                $q->where('seksi_tujuan', $seksi)
                    ->orWhereHas('bantuans', fn (Builder $b) => $b->where('ke_kasi', $seksi));
            });
        }

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->string('jenis')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $this->terapkanTanggal($query, $request, 'tanggal_buat');

        $pengaduans = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('pengaduan.unit_index', [
            'pengaduans' => $pengaduans,
            'jenisPengaduan' => Pengaduan::JENIS,
            'ringkasan' => $this->ringkasan($user),
        ]);
    }

    public function show(Pengaduan $pengaduan): View
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        $pengaduan->load(['tiket', 'pembuat', 'diterimaOleh', 'selesaiOleh', 'riwayats.user', 'catatans', 'bantuans']);

        return view('pengaduan-unit.show', [
            'pengaduan' => $pengaduan,
            'daftarSeksi' => User::SEKSI,
        ]);
    }

    public function terima(Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        if ($pengaduan->status !== 'terkirim') {
            return back()->with('error', 'Hanya pengaduan berstatus Terkirim yang dapat diterima.');
        }

        DB::transaction(function () use ($pengaduan, $user) {
            $pengaduan->update([
                'status' => 'diterima',
                'diterima_oleh_id' => $user->id,
                'diterima_pada' => now(),
            ]);

            PengaduanRiwayat::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_status' => 'terkirim',
                'ke_status' => 'diterima',
                'oleh_id' => $user->id,
                'catatan' => "Diterima oleh {$user->name}.",
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'kasi',
                'seksi' => $user->seksiPengaduan(),
                'jenis' => 'minta_bantuan',
                'catatan' => "Diterima oleh {$user->name} (Kasi Seksi {$user->seksi}). Siap ditindaklanjuti.",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.diterima',
            penerimaIds: [$pengaduan->pembuat_id],
            judul: "Pengaduan {$pengaduan->nomor_pengaduan} diterima",
            pesan: 'Pengaduan sedang ditangani '.$pengaduan->seksi_tujuan_label.'.',
            url: route('pengaduan.show', $pengaduan),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return back()->with('success', "Pengaduan {$pengaduan->nomor_pengaduan} diterima. Tindak lanjuti lalu TUTUP dengan catatan hasil.");
    }

    public function tutup(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        // Hanya Kasi asli (seksi_tujuan) yang boleh klik Selesai Akhir
        if (! $pengaduan->adalahKasiAsli($user)) {
            return back()->with('error', 'Hanya Kasi tujuan awal dari Loket yang dapat menyelesaikan pengaduan ini.');
        }

        if ($pengaduan->status !== 'diterima') {
            return back()->with('error', 'Hanya pengaduan berstatus Diterima yang dapat ditutup.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        DB::transaction(function () use ($pengaduan, $user, $validated) {
            $pengaduan->update([
                'status' => 'selesai',
                'catatan_terakhir' => $validated['catatan'],
                'selesai_oleh_id' => $user->id,
                'selesai_pada' => now(),
            ]);

            PengaduanRiwayat::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_status' => 'diterima',
                'ke_status' => 'selesai',
                'oleh_id' => $user->id,
                'catatan' => "Ditutup oleh {$user->name}. Hasil: {$validated['catatan']}",
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'kasi',
                'seksi' => $user->seksiPengaduan(),
                'jenis' => 'penyelesaian_akhir',
                'catatan' => "Penyelesaian akhir oleh {$user->name} (Kasi Seksi {$user->seksi}). Hasil: {$validated['catatan']}",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.selesai',
            penerimaIds: [$pengaduan->pembuat_id],
            judul: "Pengaduan {$pengaduan->nomor_pengaduan} selesai",
            pesan: 'Pengaduan sudah ditutup. Hasil tindak lanjut dapat dibaca pada detail pengaduan.',
            url: route('pengaduan.show', $pengaduan),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return back()->with('success', "Pengaduan {$pengaduan->nomor_pengaduan} selesai dan ditutup.");
    }

    public function mintaBantuan(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        // Hanya Kasi asli yang boleh meminta bantuan
        if (! $pengaduan->adalahKasiAsli($user)) {
            return back()->with('error', 'Hanya Kasi tujuan awal yang dapat meminta bantuan.');
        }

        if ($pengaduan->status !== 'diterima') {
            return back()->with('error', 'Pengaduan harus sudah diterima sebelum meminta bantuan.');
        }

        $validated = $request->validate([
            'ke_kasi' => ['required', Rule::in(array_keys(User::SEKSI)), 'different:'.$user->seksiPengaduan()],
            'catatan' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $dariKasi = $user->seksiPengaduan();

        DB::transaction(function () use ($pengaduan, $validated, $dariKasi) {
            $bantuan = PengaduanBantuan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_kasi' => $dariKasi,
                'ke_kasi' => $validated['ke_kasi'],
                'catatan_dari' => $validated['catatan'],
                'catatan_ke' => null,
                'status' => 'diajukan',
                'urutan' => $pengaduan->nextUrutanBantuan(),
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'kasi',
                'seksi' => $dariKasi,
                'jenis' => 'minta_bantuan',
                'catatan' => "Kasi Seksi {$dariKasi} meminta bantuan ke Kasi Seksi {$validated['ke_kasi']}. Catatan: {$validated['catatan']}",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.bantuan',
            penerimaIds: $this->notifikasi->idUnitPengaduan($validated['ke_kasi']),
            judul: "Permintaan Bantuan: {$pengaduan->nomor_pengaduan}",
            pesan: "Kasi Seksi {$dariKasi} meminta bantuan penyelesaian. Silakan balas catatan.",
            url: route('pengaduan-unit.show', $pengaduan),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return back()->with('success', "Permintaan bantuan dikirim ke Kasi Seksi {$validated['ke_kasi']}.");
    }

    public function balasBantuan(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        $seksiUser = $user->seksiPengaduan();

        // Hanya Kasi yang menjadi tujuan bantuan (ke_kasi) yang boleh balas
        $bantuan = $pengaduan->bantuans()->where('ke_kasi', $seksiUser)->where('status', 'diajukan')->latest('urutan')->first();

        if (! $bantuan) {
            return back()->with('error', 'Tidak ada permintaan bantuan yang menunggu balasan untuk unit Anda.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        DB::transaction(function () use ($pengaduan, $bantuan, $validated, $seksiUser) {
            $bantuan->update([
                'catatan_ke' => $validated['catatan'],
                'status' => 'ditanggapi',
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'kasi',
                'seksi' => $seksiUser,
                'jenis' => 'balasan_bantuan',
                'catatan' => "Balasan dari Kasi Seksi {$seksiUser} atas permintaan bantuan Kasi Seksi {$bantuan->dari_kasi}. Balasan: {$validated['catatan']}",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        // Notifikasi ke Kasi asli (dari_kasi)
        $pengaduan->refresh();
        $kasiAsli = User::where('role', 'kasi_seksi')
            ->where('seksi', $bantuan->dari_kasi)
            ->first();

        if ($kasiAsli) {
            $this->notifikasi->kirim(
                tipe: 'pengaduan.bantuan_balasan',
                penerimaIds: [$kasiAsli->id],
                judul: "Balasan Bantuan: {$pengaduan->nomor_pengaduan}",
                pesan: "Kasi Seksi {$seksiUser} telah membalas permintaan bantuan Anda.",
                url: route('pengaduan-unit.show', $pengaduan),
                objekTipe: 'pengaduan',
                objekId: $pengaduan->id,
            );
        }

        return back()->with('success', 'Balasan bantuan telah dikirim.');
    }

    public function kembalikan(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanUnit($pengaduan, $user);

        if (! in_array($pengaduan->status, ['terkirim', 'diterima'], true)) {
            return back()->with('error', 'Pengaduan ini tidak dapat dikembalikan.');
        }

        $validated = $request->validate([
            'catatan' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $dari = $pengaduan->status;

        DB::transaction(function () use ($pengaduan, $user, $validated, $dari) {
            $pengaduan->update([
                'status' => 'dikembalikan',
                'catatan_terakhir' => $validated['catatan'],
                'dikembalikan_pada' => now(),
            ]);

            PengaduanRiwayat::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_status' => $dari,
                'ke_status' => 'dikembalikan',
                'oleh_id' => $user->id,
                'catatan' => "Dikembalikan oleh {$user->name}. Alasan: {$validated['catatan']}",
            ]);

            PengaduanCatatan::create([
                'pengaduan_id' => $pengaduan->id,
                'dari_sisi' => 'kasi',
                'seksi' => $user->seksiPengaduan(),
                'jenis' => 'minta_bantuan',
                'catatan' => "Dikembalikan oleh {$user->name} (Kasi Seksi {$user->seksi}). Alasan: {$validated['catatan']}",
                'urutan' => $pengaduan->nextUrutanCatatan(),
            ]);
        });

        $this->notifikasi->kirim(
            tipe: 'pengaduan.dikembalikan',
            penerimaIds: [$pengaduan->pembuat_id],
            judul: "Pengaduan {$pengaduan->nomor_pengaduan} dikembalikan",
            pesan: 'Pengaduan dikembalikan ke Loket Pengaduan. Periksa alasan lalu kirim ulang ke unit yang tepat.',
            url: route('pengaduan.show', $pengaduan),
            objekTipe: 'pengaduan',
            objekId: $pengaduan->id,
        );

        return back()->with('warning', "Pengaduan {$pengaduan->nomor_pengaduan} dikembalikan ke Loket Pengaduan untuk dikirim ulang.");
    }

    public function selesai(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user->isPenanganPengaduan() || $user->isAdmin(), 403);

        $query = Pengaduan::with(['tiket', 'selesaiOleh'])->where('status', 'selesai');

        if (! $user->isAdmin()) {
            $seksi = $user->seksiPengaduan();
            $query->where(function (Builder $q) use ($seksi) {
                $q->where('seksi_tujuan', $seksi)
                    ->orWhereHas('bantuans', fn (Builder $b) => $b->where('ke_kasi', $seksi));
            });
        }

        if ($request->filled('q')) {
            $query->cariPintar($request->string('q')->toString());
        }

        $this->terapkanTanggal($query, $request, 'selesai_pada');

        $pengaduans = $query->orderByDesc('selesai_pada')->paginate(20)->withQueryString();

        return view('pengaduan.selesai', compact('pengaduans'));
    }

    private function pastikanUnit(Pengaduan $pengaduan, User $user): void
    {
        // Admin hanya memantau; aksi pengaduan khusus petugas unit.
        abort_unless($user->isPenanganPengaduan(), 403);
        abort_unless($pengaduan->bolehDilihatOleh($user), 403);
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

    /** @return array{masuk:int, diterima:int, selesai_bulan_ini:int, terlambat:int} */
    private function ringkasan(User $user): array
    {
        $seksi = $user->seksiPengaduan();
        $base = fn (): Builder => Pengaduan::query()
            ->when(! $user->isAdmin(), function (Builder $q) use ($seksi) {
                $q->where(function (Builder $qq) use ($seksi) {
                    $qq->where('seksi_tujuan', $seksi)
                        ->orWhereHas('bantuans', fn (Builder $bb) => $bb->where('ke_kasi', $seksi));
                });
            });

        return [
            'masuk' => $base()->where('status', 'terkirim')->count(),
            'diterima' => $base()->where('status', 'diterima')->count(),
            'selesai_bulan_ini' => $base()->where('status', 'selesai')
                ->whereBetween('selesai_pada', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'terlambat' => $base()->where('status', 'terkirim')
                ->where('created_at', '<', now()->subDays((int) config('loket.batas_tanggap_pengaduan_hari', 2)))
                ->count(),
        ];
    }
}
