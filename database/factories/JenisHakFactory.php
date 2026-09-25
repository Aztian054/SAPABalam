<?php

namespace Database\Factories;

use App\Models\JenisHak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisHak>
 */
class JenisHakFactory extends Factory
{
    protected $model = JenisHak::class;

    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->bothify('??#')),
            'nama' => fake()->unique()->words(2, true),
            'urutan' => fake()->unique()->numberBetween(1, 99),
            'is_active' => true,
        ];
    }
}
