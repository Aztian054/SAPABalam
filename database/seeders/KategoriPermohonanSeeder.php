<?php

namespace Database\Seeders;

use App\Models\KategoriPermohonan;
use Illuminate\Database\Seeder;

class KategoriPermohonanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'umum',      'nama' => 'Umum',            'urutan' => 1],
            ['kode' => 'bmn',       'nama' => 'BMN',             'urutan' => 2],
            ['kode' => 'alih_media', 'nama' => 'Alih Media',      'urutan' => 3],
        ];

        foreach ($data as $item) {
            KategoriPermohonan::updateOrCreate(
                ['kode' => $item['kode']],
                ['nama' => $item['nama'], 'urutan' => $item['urutan'], 'is_active' => true]
            );
        }
    }
}
