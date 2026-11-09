<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\LogAktivitasService;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Manajemen Akun (Admin & Super Admin).
 *
 * Akun `super_admin` adalah akun bayangan/developer:
 * - tidak pernah muncul di daftar maupun filter;
 * - tidak dapat dibuka, diubah, dinonaktifkan, atau dihapus lewat panel Admin;
 * - role `super_admin` tidak dapat dibuat/dinaikkan lewat form.
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $log,
        private readonly NotifikasiService $notifikasi,
    ) {}

    public function index(Request $request): View
    {
        $role = trim((string) $request->query('role', ''));
        $roleValid = array_keys(User::rolesForAccountFilter());

        $users = User::query()
            ->where('role', '!=', 'super_admin')
            ->when(
                $role !== '' && in_array($role, $roleValid, true),
                fn ($query) => $query->where('role', $role),
            )
            ->urutHierarki()
            ->get();

        return view('admin.users', compact('users'));
    }

    /** Formulir edit akun — role tidak dapat diubah. */
    public function edit(int $id): View
    {
        $user = $this->cariAkunTerlihat($id);

        return view('admin.users_edit', compact('user'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $akun = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            // Salinan plaintext untuk pandangan admin (kolom password_text).
            'password_text' => $data['password'],
            'role' => $data['role'],
            'seksi' => $data['seksi'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'nip' => $data['nip'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'is_active' => true,
        ]);

        $this->beriTahuKasiStaf(
            $akun,
            'akun.staf_baru',
            'Staf baru di seksi Anda',
            "{$akun->name} ditambahkan sebagai staf Seksi {$akun->seksi}.",
        );

        $this->log->catat('BUAT AKUN', $data['username'], 'Role: '.$data['role'].' / Jabatan: '.($data['jabatan'] ?? '-'));

        return back()->with('success', "Akun {$data['name']} berhasil dibuat.");
    }

    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = $this->cariAkunTerlihat($id);

        $seksiLama = $user->seksi;

        $data = $request->safe()->only(['name', 'username', 'email', 'seksi', 'jabatan', 'nip', 'no_hp']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->string('password')->toString());
            // Sinkronkan salinan plaintext agar tetap sinkron dengan hash.
            $data['password_text'] = $request->string('password')->toString();
        }

        // Role akun yang sudah dibuat tidak pernah diubah agar RBAC & alur tiket tidak rusak.
        $user->update($data);

        if ($user->role === 'staf_seksi' && $seksiLama !== $user->seksi) {
            $this->notifikasi->kirim(
                tipe: 'akun.staf_pindah_seksi',
                penerimaIds: array_merge(
                    $this->notifikasi->idKasiSeksi($seksiLama),
                    $this->notifikasi->idKasiSeksi($user->seksi),
                ),
                judul: 'Staf pindah seksi',
                pesan: "{$user->name} dipindahkan dari Seksi ".($seksiLama ?? '-').' ke Seksi '.($user->seksi ?? '-').'.',
                url: route('notifikasi.index'),
                objekTipe: 'user',
                objekId: $user->id,
            );
        }

        $this->log->catat(
            'UBAH AKUN',
            $user->username,
            $request->filled('password') ? 'Data akun & password diperbarui' : 'Data akun diperbarui',
        );

        return redirect()->route('admin.users')->with('success', "Data akun {$user->name} berhasil diperbarui.");
    }

    public function toggle(int $id): RedirectResponse
    {
        $user = $this->cariAkunTerlihat($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Status akun admin tidak dapat diubah.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $this->beriTahuKasiStaf(
            $user,
            $user->is_active ? 'akun.staf_aktif' : 'akun.staf_nonaktif',
            $user->is_active ? 'Staf diaktifkan kembali' : 'Staf dinonaktifkan',
            "{$user->name} ({$user->username}) ".($user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan')." di Seksi {$user->seksi}.",
        );

        $this->log->catat(
            'UBAH STATUS AKUN',
            $user->username,
            $user->is_active ? 'Diaktifkan' : 'Dinonaktifkan',
        );

        return back()->with('success', "Status akun {$user->name} diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = $this->cariAkunTerlihat($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Akun admin tidak dapat dihapus.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $nama = $user->name;
        $username = $user->username;
        $role = $user->role;

        $this->beriTahuKasiStaf(
            $user,
            'akun.staf_dihapus',
            'Staf dihapus dari seksi Anda',
            "{$user->name} ({$user->username}) dihapus dari Seksi {$user->seksi}.",
        );

        $user->delete();

        $this->log->catat('HAPUS AKUN', $username, 'Role: '.$role);

        return back()->with('success', "Akun {$nama} berhasil dihapus.");
    }

    /**
     * Kirim notifikasi in-app ke Kasi Seksi terkait perubahan akun staf.
     * Hanya berlaku untuk role `staf_seksi` yang memiliki seksi.
     */
    private function beriTahuKasiStaf(User $staf, string $tipe, string $judul, string $pesan): void
    {
        if ($staf->role !== 'staf_seksi' || $staf->seksi === null) {
            return;
        }

        $this->notifikasi->kirim(
            tipe: $tipe,
            penerimaIds: $this->notifikasi->idKasiSeksi($staf->seksi),
            judul: $judul,
            pesan: $pesan,
            url: route('notifikasi.index'),
            objekTipe: 'user',
            objekId: $staf->id,
        );
    }

    /**
     * Ambil akun yang boleh dikelola Admin.
     * Akun `super_admin` diperlakukan seolah tidak ada (404) agar keberadaannya tidak terbaca.
     */
    private function cariAkunTerlihat(int $id): User
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin()) {
            abort(404);
        }

        return $user;
    }
}
