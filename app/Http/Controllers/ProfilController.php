<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfilRequest;
use App\Http\Requests\UpdateUsernameRequest;
use App\Http\Requests\UploadFotoRequest;
use App\Models\User;
use App\Services\LogAktivitasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Profil mandiri pengguna.
 *
 * Pengguna hanya dapat mengubah biodata, username, dan password miliknya.
 * Role & seksi tetap dikunci agar hak akses tidak dapat dinaikkan sendiri.
 */
class ProfilController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $log,
    ) {}

    public function edit(): View
    {
        return view('profil.edit', [
            'user' => auth()->user(),
        ]);
    }

    /** Update biodata (nama, NIP, no HP, jabatan). */
    public function update(UpdateProfilRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->safe()->only(['name', 'no_hp', 'nip', 'jabatan']));

        $this->log->catat('UBAH PROFIL', $user->username, 'Biodata diperbarui sendiri oleh pemilik akun');

        return response()->json([
            'message' => 'Biodata berhasil diperbarui.',
            'user' => [
                'name' => $user->name,
                'nip' => $user->nip,
                'no_hp' => $user->no_hp,
                'jabatan' => $user->jabatan,
            ],
        ]);
    }

    /** Update username dengan verifikasi password saat ini. */
    public function updateUsername(UpdateUsernameRequest $request): JsonResponse
    {
        $user = $request->user();
        $usernameLama = $user->username;

        $user->update(['username' => $request->string('username')->toString()]);

        $this->log->catat(
            'UBAH USERNAME',
            $user->username,
            'Username: '.$usernameLama.' → '.$user->username,
        );

        return response()->json([
            'message' => 'Username berhasil diperbarui.',
            'user' => [
                'username' => $user->username,
            ],
        ]);
    }

    /** Update password dengan verifikasi password saat ini. */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $passwordBaru = $request->string('password')->toString();

        $user->update([
            'password' => Hash::make($passwordBaru),
            // Sinkronkan salinan plaintext agar tampilan admin & eye icon tetap akurat.
            'password_text' => $passwordBaru,
        ]);

        $this->log->catat('UBAH PASSWORD', $user->username, 'Password diperbarui sendiri oleh pemilik akun');

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    /** Upload foto profil untuk akun yang sedang login. */
    public function uploadFoto(UploadFotoRequest $request): JsonResponse
    {
        $user = $request->user();

        // Pastikan user memiliki kode_akun. Jika belum, generate yang unik.
        if ($user->kode_akun === null || $user->kode_akun === '') {
            $user->update(['kode_akun' => User::generateKodeAkun()]);
            $user->refresh();
        }

        // Hapus foto lama dari storage jika ada (satu akun hanya menyimpan 1 foto).
        $user->hapusFotoLama();

        // Simpan foto baru di avatars/ dengan nama file acak.
        $file = $request->file('foto');
        $ekstensi = $file->getClientOriginalExtension();
        $namaFile = uniqid('avatar_', true).'.'.$ekstensi;
        $folder = 'avatars';

        $path = $file->storeAs($folder, $namaFile, 'public');

        // Update path foto di database.
        $user->update(['foto_profil' => $path]);

        $this->log->catat('UPLOAD FOTO PROFIL', $user->username, 'Foto profil diunggah: '.$path);

        return response()->json([
            'message' => 'Foto profil diperbarui.',
            'user' => [
                'foto_profil_url' => $user->foto_profil_url,
            ],
        ]);
    }
}
