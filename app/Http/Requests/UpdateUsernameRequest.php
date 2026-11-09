<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi perubahan username mandiri lewat halaman Profil.
 */
class UpdateUsernameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:4',
                'max:50',
                'not_regex:/@/',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'current_password' => ['required', 'string', 'current_password'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'username',
            'current_password' => 'password saat ini',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 4 karakter.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'username.not_regex' => 'Username tidak boleh mengandung tanda @ karena akan dikenali sebagai email saat login.',
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password' => 'Password saat ini tidak sesuai.',
        ];
    }
}
