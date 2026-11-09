<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi pembuatan akun oleh Admin/Super Admin.
 *
 * `role` dibatasi ke role non-admin: Admin biasa tidak boleh membuat
 * akun `super_admin` maupun `admin` (akun bayangan/akun tunggal).
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in(array_keys(User::rolesNonAdmin()))],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'seksi' => [
                'nullable',
                'required_if:role,staf_seksi,kasi_seksi,kasi_unit',
                Rule::in(array_keys(User::SEKSI)),
            ],
            'nip' => ['nullable', 'string', 'max:30'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'role.in' => 'Role Admin/Super Admin tidak dapat dibuat dari Manajemen Akun.',
            'seksi.required_if' => 'Role ini wajib memiliki seksi.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'password' => 'password',
            'role' => 'role',
            'jabatan' => 'jabatan',
            'seksi' => 'seksi',
            'nip' => 'NIP',
            'no_hp' => 'no. HP',
        ];
    }
}
