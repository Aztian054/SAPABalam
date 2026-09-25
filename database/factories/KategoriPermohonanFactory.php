<?php

namespace Database\Factories;

use App\Models\KategoriPermohonan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriPermohonan>
 */
class KategoriPermohonanFactory extends Factory
{
    protected $model = KategoriPermohonan::class;

    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('??##')),
            'nama' => fake()->unique()->words(2, true),
            'urutan' => fake()->unique()->numberBetween(1, 99),
            'is_active' => true,
        ];
    }
}
