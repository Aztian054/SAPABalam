<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi perubahan akun oleh Admin/Super Admin.
 *
 * Field `role` sengaja `prohibited`: role akun tidak dapat diubah dan
 * Admin biasa tidak boleh menaikkan level siapa pun menjadi `super_admin`.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Akun super_admin diperlakukan seolah tidak ada (404) sebelum validasi,
     * agar keberadaannya tidak dapat ditebak lewat pesan kesalahan.
     */
    protected function prepareForValidation(): void
    {
        if (User::find($this->route('id'))?->isSuperAdmin()) {
            abort(404);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $target = User::find($this->route('id'));
        $idTarget = $target?->id;

        return [
            'name' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($idTarget)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($idTarget)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['prohibited'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'seksi' => [
                'nullable',
                Rule::requiredIf(fn (): bool => (bool) $target?->requiresSeksi()),
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
            'role.prohibited' => 'Role akun tidak dapat diubah, termasuk menjadi Super Admin.',
            'seksi.required' => 'Role ini wajib memiliki seksi.',
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
