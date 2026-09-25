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
            // Password disimpan sebagai hash (password) + salinan plaintext (password_text) untuk pandangan admin.
            ['name' => 'Muhammad Rizki Aztian',       'username' => 'admin',        'email' => 'admin@loket.balam.go.id',   'password' => Hash::make('admin123'),     'password_text' => 'admin123',     'role' => 'admin',           'nip' => '1234567890', 'no_hp' => '082361707406', 'is_active' => true],
            ['name' => 'Ahmad Zulkifli',      'username' => 'pemimpin',     'email' => 'pemimpin@loket.balam.go.id',  'password' => Hash::make('pemimpin123'),  'password_text' => 'pemimpin123',  'role' => 'pemimpin',        'nip' => '196508151990031004', 'no_hp' => '081284456789', 'is_active' => true],
            ['name' => 'Siti Rahayu',         'username' => 'loket1',       'email' => 'loket@loket.balam.go.id',     'password' => Hash::make('loket123'),     'password_text' => 'loket123',     'role' => 'loket',           'nip' => '199005122020042023', 'no_hp' => '085282660101', 'is_active' => true],
            ['name' => 'Budi Santoso',        'username' => 'verifikator1', 'email' => 'verifikator@loket.balam.go.id', 'password' => Hash::make('verif123'), 'password_text' => 'verif123', 'role' => 'verifikator', 'nip' => '198711152015091001', 'no_hp' => '081387654321', 'is_active' => true],
            ['name' => 'Dewi Anggraini',      'username' => 'warkah1',      'email' => 'warkah@loket.balam.go.id',    'password' => Hash::make('warkah123'),    'password_text' => 'warkah123',    'role' => 'warkah',          'nip' => '199206102019032024', 'no_hp' => '085390123456', 'is_active' => true],
            ['name' => 'Muhammad Iqbal',      'username' => 'vbtel1',       'email' => 'vbtel1@loket.balam.go.id',    'password' => Hash::make('vbtel123'),     'password_text' => 'vbtel123',     'role' => 'validator_btel',  'nip' => '198803202014101001', 'no_hp' => '082134567890', 'is_active' => true],
            ['name' => 'Ratna Sari',          'username' => 'vsuel1',       'email' => 'vsuel1@loket.balam.go.id',    'password' => Hash::make('vsuel123'),     'password_text' => 'vsuel123',     'role' => 'validator_suel',  'nip' => '199301272020042005', 'no_hp' => '085286456789', 'is_active' => true],
            ['name' => 'Rizky Ramadhan',      'username' => 'ambt1',        'email' => 'ambt1@loket.balam.go.id',     'password' => Hash::make('ambt123'),      'password_text' => 'ambt123',      'role' => 'alih_media_btel', 'nip' => '199508122022012001', 'no_hp' => '081290345678', 'is_active' => true],
            ['name' => 'Lina Marlina',        'username' => 'amsu1',        'email' => 'amsu1@loket.balam.go.id',     'password' => Hash::make('amsu123'),      'password_text' => 'amsu123',      'role' => 'alih_media_suel', 'nip' => '199009152018032006', 'no_hp' => '085777654321', 'is_active' => true],

            // Akun kedua per jabatan (non-admin) - 2 akun aktif per tahap. Admin tetap 1.
            ['name' => 'Sri Wahyuni',         'username' => 'pemimpin2',    'email' => 'pemimpin2@loket.balam.go.id', 'password' => Hash::make('pemimpin123'),  'password_text' => 'pemimpin123',  'role' => 'pemimpin',        'nip' => '197205091997122007', 'no_hp' => '081398765432', 'is_active' => true],
            ['name' => 'Andi Pratama',        'username' => 'loket2',       'email' => 'loket2@loket.balam.go.id',    'password' => Hash::make('loket123'),     'password_text' => 'loket123',     'role' => 'loket',           'nip' => '199402022021041002', 'no_hp' => '085212345678', 'is_active' => true],
            ['name' => 'Nurhayati',           'username' => 'verifikator2', 'email' => 'verifikator2@loket.balam.go.id', 'password' => Hash::make('verif123'), 'password_text' => 'verif123', 'role' => 'verifikator', 'nip' => '199110201916042008', 'no_hp' => '082189012345', 'is_active' => true],
            ['name' => 'Eko Prasetyo',        'username' => 'warkah2',      'email' => 'warkah2@loket.balam.go.id',   'password' => Hash::make('warkah123'),    'password_text' => 'warkah123',    'role' => 'warkah',          'nip' => '199608302022051009', 'no_hp' => '081245678901', 'is_active' => true],
            ['name' => 'Dian Puspita',        'username' => 'vbtel2',       'email' => 'vbtel2@loket.balam.go.id',    'password' => Hash::make('vbtel123'),     'password_text' => 'vbtel123',     'role' => 'validator_btel',  'nip' => '199202062019032010', 'no_hp' => '085890123456', 'is_active' => true],
            ['name' => 'Agus Salim',          'username' => 'vsuel2',       'email' => 'vsuel2@loket.balam.go.id',    'password' => Hash::make('vsuel123'),     'password_text' => 'vsuel123',     'role' => 'validator_suel',  'nip' => '199008172015041011', 'no_hp' => '082267890123', 'is_active' => true],
            ['name' => 'Putri Melati',        'username' => 'ambt2',        'email' => 'ambt2@loket.balam.go.id',     'password' => Hash::make('ambt123'),      'password_text' => 'ambt123',      'role' => 'alih_media_btel', 'nip' => '199705212023062012', 'no_hp' => '085700234567', 'is_active' => true],
            ['name' => 'Rudi Hartono',        'username' => 'amsu2',        'email' => 'amsu2@loket.balam.go.id',     'password' => Hash::make('amsu123'),      'password_text' => 'amsu123',      'role' => 'alih_media_suel', 'nip' => '199312302019031011', 'no_hp' => '081356789012', 'is_active' => true],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }
}
