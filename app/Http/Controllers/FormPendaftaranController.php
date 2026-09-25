<?php

namespace App\Http\Controllers;

use App\Models\BidangTanah;
use App\Models\JenisHak;
use App\Models\JenisPermohonan;
use App\Models\KategoriPermohonan;
use App\Models\PersyaratanDokumen;
use App\Models\SaranKoreksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Admin "Kelola Form Pendaftaran" â€” CRUD master konten form pendaftaran.
 *
 * Admin dapat mengelola isi dropdown/tabel tanpa perlu ubahan kode (mandiri):
 *  - Jenis Permohonan (termasuk persyaratan dokumen baris-dinamis)
 *  - Persyaratan Dokumen (per jenis permohonan)
 *  - Saran Koreksi (template pesan revisi Verifikator/Warkah)
 *  - Jenis Hak (dropdown bidang tanah)
 *  - Kategori Permohonan (kategori jenis permohonan)
 */
class FormPendaftaranController extends Controller
{
    // ---------------- Halaman utama (5 tab) ----------------

    public function index(): View
    {
        return view('admin.form_pendaftaran', [
            'jenisPermohonans' => JenisPermohonan::with(['persyaratanDokumens', 'tikets'])
                ->orderBy('kode')
                ->get(),
            'kategoris' => KategoriPermohonan::withCount('jenisPermohonans')
                ->orderBy('urutan')
                ->orderBy('kode')
                ->get(),
            'jenisHaks' => JenisHak::withCount('bidangTanahs')
                ->orderBy('urutan')
                ->orderBy('kode')
                ->get(),
            'saranKoreksis' => SaranKoreksi::with('jenisPermohonan')
                ->orderBy('id')
                ->get(),
        ]);
    }

    // ---------------- Jenis Permohonan ----------------

    public function jenisPermohonanStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jenis_permohonans,kode',
            'nama' => 'required|string|max:200',
            'kategori' => 'required|exists:kategori_permohonans,kode',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'persyaratan' => 'nullable|array|max:30',
            'persyaratan.*.nama_dokumen' => 'nullable|string|max:255',
            'persyaratan.*.wajib' => 'nullable|boolean',
            'persyaratan.*.keterangan' => 'nullable|string|max:255',
        ]);

        $jp = JenisPermohonan::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncPersyaratan($jp, $validated['persyaratan'] ?? []);

        return redirect()->route('admin.form-pendaftaran')
            ->with('success', "Jenis Permohonan {$jp->kode} â€” {$jp->nama} berhasil ditambahkan.");
    }

    public function jenisPermohonanEdit(int $id): View
    {
        $jenisPermohonan = JenisPermohonan::with('persyaratanDokumens')->findOrFail($id);

        return view('admin.form_edit_jenis_permohonan', [
            'jenisPermohonan' => $jenisPermohonan,
            'kategoris' => KategoriPermohonan::where('is_active', true)->orderBy('urutan')->orderBy('kode')->get(),
        ]);
    }

    public function jenisPermohonanUpdate(Request $request, int $id): RedirectResponse
    {
        $jp = JenisPermohonan::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jenis_permohonans,kode,'.$jp->id,
            'nama' => 'required|string|max:200',
            'kategori' => 'required|exists:kategori_permohonans,kode',
            'deskripsi' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'persyaratan' => 'nullable|array|max:30',
            'persyaratan.*.nama_dokumen' => 'nullable|string|max:255',
            'persyaratan.*.wajib' => 'nullable|boolean',
            'persyaratan.*.keterangan' => 'nullable|string|max:255',
        ]);

        $jp->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncPersyaratan($jp, $validated['persyaratan'] ?? []);

        return redirect()->route('admin.form-pendaftaran')
            ->with('success', "Jenis Permohonan {$jp->kode} â€” {$jp->nama} berhasil diperbarui.");
    }

    public function jenisPermohonanToggle(int $id): RedirectResponse
    {
        $jp = JenisPermohonan::findOrFail($id);
        $jp->update(['is_active' => ! $jp->is_active]);

        $status = $jp->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Jenis Permohonan {$jp->kode} â€” {$jp->nama} berhasil {$status}.");
    }

    public function jenisPermohonanDestroy(int $id): RedirectResponse
    {
        $jp = JenisPermohonan::findOrFail($id);

        if ($jp->tikets()->exists()) {
            return back()->with('error', "Jenis Permohonan {$jp->kode} sudah dipakai {$jp->tikets()->count()} berkas. Nonaktifkan alih-alih menghapus.");
        }

        $nama = $jp->nama;
        $jp->delete();

        return back()->with('success', "Jenis Permohonan {$nama} berhasil dihapus.");
    }

    // ---------------- Persyaratan Dokumen ----------------

    public function persyaratanStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'nama_dokumen' => 'required|string|max:255',
            'wajib' => 'nullable|boolean',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jp = JenisPermohonan::findOrFail($validated['jenis_permohonan_id']);
        $urut = PersyaratanDokumen::where('jenis_permohonan_id', $jp->id)->count() + 1;

        PersyaratanDokumen::create([
            'jenis_permohonan_id' => $jp->id,
            'nama_dokumen' => $validated['nama_dokumen'],
            'wajib' => $request->boolean('wajib'),
            'keterangan' => $validated['keterangan'] ?? null,
            'urutan' => $urut,
        ]);

        return back()->with('success', "Persyaratan \"{$validated['nama_dokumen']}\" ditambahkan ke {$jp->nama}.");
    }

    public function persyaratanDestroy(int $id): RedirectResponse
    {
        $persyaratan = PersyaratanDokumen::findOrFail($id);

        $nama = $persyaratan->nama_dokumen;
        $jpId = $persyaratan->jenis_permohonan_id;
        $persyaratan->delete();

        // Rapikan urutan setelah penghapusan.
        PersyaratanDokumen::where('jenis_permohonan_id', $jpId)
            ->orderBy('urutan')
            ->get()
            ->each(function (PersyaratanDokumen $item, int $index) {
                $item->update(['urutan' => $index + 1]);
            });

        return back()->with('success', "Persyaratan \"{$nama}\" berhasil dihapus.");
    }

    // ---------------- Saran Koreksi ----------------

    public function saranKoreksiStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'nama_dokumen_kurang' => 'required|string|max:255',
            'pesan_koreksi' => 'required|string',
            'dasar_hukum' => 'nullable|string|max:255',
        ]);

        SaranKoreksi::create($validated);

        return back()->with('success', 'Saran koreksi berhasil ditambahkan.');
    }

    public function saranKoreksiEdit(int $id): View
    {
        $saranKoreksi = SaranKoreksi::with('jenisPermohonan')->findOrFail($id);

        return view('admin.form_edit_saran_koreksi', [
            'saranKoreksi' => $saranKoreksi,
            'jenisPermohonans' => JenisPermohonan::where('is_active', true)->orderBy('kode')->get(),
        ]);
    }

    public function saranKoreksiUpdate(Request $request, int $id): RedirectResponse
    {
        $saran = SaranKoreksi::findOrFail($id);

        $validated = $request->validate([
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'nama_dokumen_kurang' => 'required|string|max:255',
            'pesan_koreksi' => 'required|string',
            'dasar_hukum' => 'nullable|string|max:255',
        ]);

        $saran->update($validated);

        return redirect()->route('admin.form-pendaftaran')
            ->with('success', 'Saran koreksi berhasil diperbarui.');
    }

    public function saranKoreksiDestroy(int $id): RedirectResponse
    {
        $saran = SaranKoreksi::findOrFail($id);
        $nama = $saran->nama_dokumen_kurang;
        $saran->delete();

        return back()->with('success', "Saran koreksi \"{$nama}\" berhasil dihapus.");
    }

    // ---------------- Jenis Hak ----------------

    public function jenisHakStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jenis_haks,kode',
            'nama' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        JenisHak::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Jenis Hak {$validated['kode']} â€” {$validated['nama']} berhasil ditambahkan.");
    }

    public function jenisHakEdit(int $id): View
    {
        return view('admin.form_edit_jenis_hak', [
            'jenisHak' => JenisHak::findOrFail($id),
        ]);
    }

    public function jenisHakUpdate(Request $request, int $id): RedirectResponse
    {
        $jenisHak = JenisHak::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:jenis_haks,kode,'.$jenisHak->id,
            'nama' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $jenisHak->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.form-pendaftaran')
            ->with('success', "Jenis Hak {$jenisHak->kode} â€” {$jenisHak->nama} berhasil diperbarui.");
    }

    public function jenisHakToggle(int $id): RedirectResponse
    {
        $jenisHak = JenisHak::findOrFail($id);
        $jenisHak->update(['is_active' => ! $jenisHak->is_active]);

        $status = $jenisHak->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Jenis Hak {$jenisHak->kode} berhasil {$status}.");
    }

    public function jenisHakDestroy(int $id): RedirectResponse
    {
        $jenisHak = JenisHak::findOrFail($id);

        if (BidangTanah::where('jenis_hak', $jenisHak->kode)->exists()) {
            return back()->with('error', "Jenis Hak {$jenisHak->kode} sudah dipakai bidang tanah. Nonaktifkan alih-alih menghapus.");
        }

        $nama = $jenisHak->nama;
        $jenisHak->delete();

        return back()->with('success', "Jenis Hak {$nama} berhasil dihapus.");
    }
    // ---------------- Kategori Permohonan ----------------

    public function kategoriStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kategori_permohonans,kode',
            'nama' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        KategoriPermohonan::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Kategori {$validated['nama']} berhasil ditambahkan.");
    }

    public function kategoriEdit(int $id): View
    {
        return view('admin.form_edit_kategori', [
            'kategori' => KategoriPermohonan::findOrFail($id),
        ]);
    }

    public function kategoriUpdate(Request $request, int $id): RedirectResponse
    {
        $kategori = KategoriPermohonan::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kategori_permohonans,kode,'.$kategori->id,
            'nama' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $kategori->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.form-pendaftaran')
            ->with('success', "Kategori {$kategori->nama} berhasil diperbarui.");
    }

    public function kategoriToggle(int $id): RedirectResponse
    {
        $kategori = KategoriPermohonan::findOrFail($id);
        $kategori->update(['is_active' => ! $kategori->is_active]);

        $status = $kategori->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori {$kategori->nama} berhasil {$status}.");
    }

    public function kategoriDestroy(int $id): RedirectResponse
    {
        $kategori = KategoriPermohonan::findOrFail($id);

        if (JenisPermohonan::where('kategori', $kategori->kode)->exists()) {
            return back()->with('error', "Kategori {$kategori->nama} masih dipakai jenis permohonan. Nonaktifkan alih-alih menghapus.");
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('success', "Kategori {$nama} berhasil dihapus.");
    }

    /**
     * Sinkronisasi baris persyaratan pada tambah/ubah Jenis Permohonan.
     * Baris dikirim ber-urutan; isian kosong dilewati.
     *
     * @param  array  $rows  array:<int, array{nama_dokumen?: string, wajib?: bool, keterangan?: string}>
     */
    private function syncPersyaratan(JenisPermohonan $jp, array $rows): void
    {
        PersyaratanDokumen::where('jenis_permohonan_id', $jp->id)->delete();

        $urutan = 0;
        foreach ($rows as $row) {
            $nama = trim($row['nama_dokumen'] ?? '');
            if ($nama === '') {
                continue;
            }

            $urutan++;
            PersyaratanDokumen::create([
                'jenis_permohonan_id' => $jp->id,
                'nama_dokumen' => $nama,
                'wajib' => (bool) ($row['wajib'] ?? false),
                'keterangan' => $row['keterangan'] ?? null,
                'urutan' => $urutan,
            ]);
        }
    }
}
