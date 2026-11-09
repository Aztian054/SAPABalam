<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Daftar akun ATR/BPN Kantor Pertanahan Kota Bandar Lampung.
     *
     * `role` + `seksi` menentukan hak akses (RBAC & Exclusive Ticket Claiming),
     * sedangkan `jabatan` adalah identitas pegawai yang tampil di riwayat alur
     * berkas. Super Admin tetap dipertahankan sebagai akun bayangan.
     */
    public function run(): void
    {
        $this->gantiUsernameLama();

        $users = [
            ['name' => 'Super Admin', 'username' => 'superadmin', 'password' => 'superadmin123', 'role' => 'super_admin', 'jabatan' => 'Super Admin', 'nip' => '100000000000000001', 'no_hp' => '081100000001'],
            ['name' => 'Muhammad Rizki Aztian', 'username' => 'admin', 'password' => 'admin123', 'role' => 'admin', 'jabatan' => 'Admin ATR BPN BANDAR LAMPUNG', 'nip' => '1234567890', 'no_hp' => '082361707406'],
            ['name' => 'Ahmad Zulkifli', 'username' => 'kakan', 'password' => 'kakan123', 'role' => 'kakan', 'jabatan' => 'KAKAN', 'nip' => '196508151990031004', 'no_hp' => '081100000008'],
            ['name' => 'Kasubbag TU', 'username' => 'kasubbag', 'password' => 'kasubbag123', 'role' => 'kasubbag_tu', 'seksi' => 'TU', 'jabatan' => 'kasubbag Tata Usaha', 'nip' => '198807072013012007', 'no_hp' => '081100000012'],
            ['name' => 'KASI Seksi Survei dan Pemetaan', 'username' => 'kasi1', 'password' => 'kasi123', 'role' => 'kasi_seksi', 'seksi' => '1', 'jabatan' => 'KASI Seksi Survei dan Pemetaan', 'nip' => '198504042010011004', 'no_hp' => '081100000005'],
            ['name' => 'kasi Seksi Penetapan Hak dan Pendaftaran', 'username' => 'kasi2', 'password' => 'kasi123', 'role' => 'kasi_seksi', 'seksi' => '2', 'jabatan' => 'kasi Seksi Penetapan Hak dan Pendaftaran', 'nip' => '198605052011012005', 'no_hp' => '081100000010'],
            ['name' => 'kasi Seksi Penataan dan Pemberdayaan', 'username' => 'kasi3', 'password' => 'kasi123', 'role' => 'kasi_seksi', 'seksi' => '3', 'jabatan' => 'kasi Seksi Penataan dan Pemberdayaan', 'nip' => '198706062012011006', 'no_hp' => '081100000011'],
            ['name' => 'Kasi Seksi Pengadaan Tanah dan Pengembangan', 'username' => 'kasi4', 'password' => 'kasi123', 'role' => 'kasi_seksi', 'seksi' => '4', 'jabatan' => 'Kasi Seksi Pengadaan Tanah dan Pengembangan', 'nip' => '198908082014011008', 'no_hp' => '081100000013'],
            ['name' => 'Kasi Seksi Pengendalian dan Penanganan Sengketa', 'username' => 'kasi5', 'password' => 'kasi123', 'role' => 'kasi_seksi', 'seksi' => '5', 'jabatan' => 'Kasi Seksi Pengendalian dan Penanganan Sengketa', 'nip' => '199009092015011009', 'no_hp' => '081100000014'],
            ['name' => 'LOKET PENDAFTARAN', 'username' => 'loket1', 'password' => 'loket123', 'role' => 'loket_pendaftaran', 'jabatan' => 'LOKET PENDAFTARAN', 'nip' => '199501012020012001', 'no_hp' => '081100000002'],
            ['name' => 'Loket Pengaduan', 'username' => 'pengaduan1', 'password' => 'loket123', 'role' => 'loket_pengaduan', 'jabatan' => 'LOKET PENGADUAN', 'nip' => '199906062024011006', 'no_hp' => '081100000007'],
            ['name' => 'LOKET PENYERAHAN', 'username' => 'serah1', 'password' => 'loket123', 'role' => 'loket_penyerahan', 'jabatan' => 'LOKET PENYERAHAN', 'nip' => '199805052023012005', 'no_hp' => '081100000006'],
            ['name' => 'Loket Ploting', 'username' => 'ploting', 'password' => 'ploting123', 'role' => 'loket_ploting', 'jabatan' => 'Loket Ploting', 'nip' => '199602022021011002', 'no_hp' => '081100000003'],
            ['name' => 'Dewi Anggraini', 'username' => 'warkah', 'password' => 'warkah123', 'role' => 'petugas_warkah', 'jabatan' => 'PETUGAS WARKAH', 'nip' => '199206102019032024', 'no_hp' => '085390123456'],
            ['name' => 'AlihMediaBT', 'username' => 'alihmediabt', 'password' => 'alihmedia123', 'role' => 'staf_seksi', 'seksi' => '2', 'jabatan' => 'AlihMedia BT', 'nip' => '199804042023011004', 'no_hp' => '081100000009'],
            ['name' => 'AlihmediaSU', 'username' => 'alihmediasu', 'password' => 'alihmedia123', 'role' => 'staf_seksi', 'seksi' => '1', 'jabatan' => 'AlihMedia SU', 'nip' => '196508151990031004', 'no_hp' => '081100000008'],
            ['name' => 'ValidatorBT', 'username' => 'validatorbt', 'password' => 'validator123', 'role' => 'staf_seksi', 'seksi' => '2', 'jabatan' => 'Validator BT', 'nip' => '199703032022012003', 'no_hp' => '081100000004'],
            ['name' => 'ValidatorSU', 'username' => 'validatorsu', 'password' => 'validator123', 'role' => 'staf_seksi', 'seksi' => '1', 'jabatan' => 'Validator SU', 'nip' => '196508151990031004', 'no_hp' => '081100000008'],
        ];

        foreach ($users as $user) {
            $username = $user['username'];

            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $user['name'],
                    'email' => $username.'@loket.balam.go.id',
                    'password' => Hash::make($user['password']),
                    'password_text' => $user['password'],
                    'role' => $user['role'],
                    'seksi' => $user['seksi'] ?? null,
                    'jabatan' => $user['jabatan'],
                    'nip' => $user['nip'],
                    'no_hp' => $user['no_hp'],
                    'is_active' => true,
                ]
            );
        }

        $this->nonaktifkanAkunLama();
    }

    /**
     * Samakan username lama dengan daftar akun baru tanpa membuat duplikat.
     * Akun lama tetap membawa riwayat penugasannya.
     */
    private function gantiUsernameLama(): void
    {
        $pemetaan = [
            'loketdaftar1' => 'loket1',
            'loketploting1' => 'ploting',
            'warkah1' => 'warkah',
            'kasubbagtu' => 'kasubbag',
        ];

        foreach ($pemetaan as $lama => $baru) {
            $akun = User::where('username', $lama)->first();

            if ($akun === null || User::where('username', $baru)->exists()) {
                continue;
            }

            $akun->update([
                'username' => $baru,
                'email' => $baru.'@loket.balam.go.id',
            ]);
        }
    }

    /**
     * Akun lama yang tidak ada di daftar baru dinonaktifkan, bukan dihapus,
     * agar riwayat penugasan tetap utuh.
     */
    private function nonaktifkanAkunLama(): void
    {
        User::query()
            ->whereIn('username', ['staf1a', 'staf2a', 'kasiunit3'])
            ->update(['is_active' => false]);
    }
}
