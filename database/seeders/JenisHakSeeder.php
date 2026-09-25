<?php

namespace Database\Seeders;

use App\Models\JenisHak;
use Illuminate\Database\Seeder;

class JenisHakSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'HM',  'nama' => 'Hak Milik',                    'urutan' => 1],
            ['kode' => 'HGB', 'nama' => 'Hak Guna Bangunan',            'urutan' => 2],
            ['kode' => 'HGU', 'nama' => 'Hak Guna Usaha',               'urutan' => 3],
            ['kode' => 'HP',  'nama' => 'Hak Pakai',                    'urutan' => 4],
            ['kode' => 'HPL', 'nama' => 'Hak Pengelolaan Lahan',        'urutan' => 5],
        ];

        foreach ($data as $item) {
            JenisHak::updateOrCreate(
                ['kode' => $item['kode']],
                ['nama' => $item['nama'], 'urutan' => $item['urutan'], 'is_active' => true]
            );
        }
    }
}
