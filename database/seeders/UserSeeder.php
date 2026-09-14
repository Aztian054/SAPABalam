<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Akun utama per dokumen "ALUR DIAGRAM VERSI RIZKI - REVISI (FINAL)", bagian "Cara Cepat Login".
            ['name' => 'Administrator Sistem',       'username' => 'admin',        'email' => 'admin@loket.balam.go.id',   'password' => Hash::make('admin123'),     'role' => 'admin',           'is_active' => true],
            ['name' => 'Kepala Kantor (Pemimpin)',   'username' => 'pemimpin',     'email' => 'pemimpin@loket.balam.go.id',  'password' => Hash::make('pemimpin123'),  'role' => 'pemimpin',        'is_active' => true],
            ['name' => 'Petugas Loket Penerimaan',   'username' => 'loket1',       'email' => 'loket@loket.balam.go.id',     'password' => Hash::make('loket123'),     'role' => 'loket',           'is_active' => true],
            ['name' => 'Petugas Verifikator Berkas', 'username' => 'verifikator1', 'email' => 'verifikator@loket.balam.go.id', 'password' => Hash::make('verif123'), 'role' => 'verifikator', 'is_active' => true],
            ['name' => 'Petugas Arsip Warkah',       'username' => 'warkah1',      'email' => 'warkah@loket.balam.go.id',    'password' => Hash::make('warkah123'),    'role' => 'warkah',          'is_active' => true],
            ['name' => 'Validator Buku Tanah (BT)',  'username' => 'vbtel1',       'email' => 'vbtel1@loket.balam.go.id',    'password' => Hash::make('vbtel123'),     'role' => 'validator_btel',  'is_active' => true],
            ['name' => 'Validator Surat Ukur (SU)',  'username' => 'vsuel1',       'email' => 'vsuel1@loket.balam.go.id',    'password' => Hash::make('vsuel123'),     'role' => 'validator_suel',  'is_active' => true],
            ['name' => 'Petugas Alih Media BT',      'username' => 'ambt1',        'email' => 'ambt1@loket.balam.go.id',     'password' => Hash::make('ambt123'),      'role' => 'alih_media_btel', 'is_active' => true],
            ['name' => 'Petugas Alih Media SU',      'username' => 'amsu1',        'email' => 'amsu1@loket.balam.go.id',     'password' => Hash::make('amsu123'),      'role' => 'alih_media_suel', 'is_active' => true],

            // Akun kedua per jabatan (non-admin) - 2 akun aktif per tahap. Admin tetap 1.
            ['name' => 'Kepala Kantor (Pemimpin) 2',  'username' => 'pemimpin2',    'email' => 'pemimpin2@loket.balam.go.id', 'password' => Hash::make('pemimpin123'),  'role' => 'pemimpin',        'is_active' => true],
            ['name' => 'Petugas Loket Penerimaan 2',  'username' => 'loket2',       'email' => 'loket2@loket.balam.go.id',    'password' => Hash::make('loket123'),     'role' => 'loket',           'is_active' => true],
            ['name' => 'Petugas Verifikator Berkas 2', 'username' => 'verifikator2', 'email' => 'verifikator2@loket.balam.go.id', 'password' => Hash::make('verif123'), 'role' => 'verifikator', 'is_active' => true],
            ['name' => 'Petugas Arsip Warkah 2',      'username' => 'warkah2',      'email' => 'warkah2@loket.balam.go.id',   'password' => Hash::make('warkah123'),    'role' => 'warkah',          'is_active' => true],
            ['name' => 'Validator Buku Tanah (BT) 2', 'username' => 'vbtel2',       'email' => 'vbtel2@loket.balam.go.id',    'password' => Hash::make('vbtel123'),     'role' => 'validator_btel',  'is_active' => true],
            ['name' => 'Validator Surat Ukur (SU) 2', 'username' => 'vsuel2',       'email' => 'vsuel2@loket.balam.go.id',    'password' => Hash::make('vsuel123'),     'role' => 'validator_suel',  'is_active' => true],
            ['name' => 'Petugas Alih Media BT 2',     'username' => 'ambt2',        'email' => 'ambt2@loket.balam.go.id',     'password' => Hash::make('ambt123'),      'role' => 'alih_media_btel', 'is_active' => true],
            ['name' => 'Petugas Alih Media SU 2',     'username' => 'amsu2',        'email' => 'amsu2@loket.balam.go.id',     'password' => Hash::make('amsu123'),      'role' => 'alih_media_suel', 'is_active' => true],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }
}
