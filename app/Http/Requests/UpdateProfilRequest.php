<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi perubahan biodata mandiri lewat halaman Profil.
 *
 * Role & seksi bersifat tetap: tidak boleh ikut berubah dari form ini.
 */
class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($this->user()?->id)],
            'no_hp' => ['required', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'max:100'],
            'role' => ['prohibited'],
            'seksi' => ['prohibited'],
            'email' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'nip' => 'NIP',
            'no_hp' => 'no. HP',
            'jabatan' => 'jabatan',
            'role' => 'role',
            'seksi' => 'seksi',
            'email' => 'email',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.unique' => 'NIP sudah digunakan akun lain.',
            'no_hp.required' => 'No. HP wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'role.prohibited' => 'Role tidak dapat diubah dari halaman Profil.',
            'seksi.prohibited' => 'Seksi tidak dapat diubah dari halaman Profil.',
            'email.prohibited' => 'Email tidak dapat diubah dari halaman Profil.',
        ];
    }
}
