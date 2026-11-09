<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi upload foto profil mandiri.
 */
class UploadFotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'foto' => 'foto profil',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => 'Silakan pilih file foto.',
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format foto yang didukung: JPG, JPEG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
