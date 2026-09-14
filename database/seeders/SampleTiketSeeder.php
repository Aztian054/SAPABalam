<?php

namespace Database\Seeders;

use App\Models\AlihMediaBtel;
use App\Models\AlihMediaSuel;
use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\LembarKerjaWarkah;
use App\Models\Tiket;
use App\Models\TiketPenugasan;
use App\Models\User;
use App\Models\ValidasiBtel;
use App\Models\ValidasiSuel;
use App\Models\VerifikasiBerkas;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleTiketSeeder extends Seeder
{
    public function run(): void
    {
        $loket = User::where('username', 'loket1')->first();
        $verif = User::where('username', 'verifikator1')->first();
        $warkah = User::where('username', 'warkah1')->first();
        $valbt = User::where('username', 'vbtel1')->first();
        $valsu = User::where('username', 'vsuel1')->first();
        $alihbt = User::where('username', 'ambt1')->first();
        $alihsu = User::where('username', 'amsu1')->first();

        $jp01 = JenisPermohonan::where('kode', 'JP01')->first();
        $jp02 = JenisPermohonan::where('kode', 'JP02')->first();
        $jp14 = JenisPermohonan::where('kode', 'JP14')->first();
        $today = Carbon::today();
        $dc = $today->format('dmy');

        // T1: Baru di Loket → DB Admin
        $t1 = Tiket::create([
            'kode_tiket' => "K/1/{$dc}/1", 'nomor_antrian' => 'A-001', 'nomor_urut_berkas' => 1,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today, 'jenis_permohonan_id' => $jp02->id,
            'nama_pemohon' => 'H. Hendra Setiawan', 'nik_pemohon' => '1871021405820003',
            'no_hp_pemohon' => '081278998811', 'kelurahan_desa' => 'Way Halim Permai',
            'kecamatan' => 'Way Halim', 'jumlah_bidang' => 1, 'petugas_loket_id' => $loket->id,
            'nama_petugas_loket' => $loket->name, 'nomor_telepon' => '081278998811',
            'nomor_tiket_non_ppat' => 'BK-2026-0001', 'status' => 'diterima', 'created_by' => $loket->id,
            'tanggal_target_selesai' => $today->copy()->addDays(7),
            'keterangan' => 'Peralihan hak jual beli.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t1->id, 'nib' => '08.01.04.05.00192', 'no_sertifikat_lama' => 'SHM No. 4102',
            'jenis_hak' => 'HM', 'nama_pemegang_hak' => 'H. Hendra Setiawan', 'luas_m2' => 240,
            'desa_kelurahan' => 'Way Halim Permai', 'kecamatan' => 'Way Halim',
        ]);

        // T2: Sedang diproses Verifikator
        $t2 = Tiket::create([
            'kode_tiket' => "K/2/{$dc}/1", 'nomor_antrian' => 'A-002', 'nomor_urut_berkas' => 2,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today, 'jenis_permohonan_id' => $jp14->id,
            'nama_pemohon' => 'KPKNL Bandar Lampung', 'nik_pemohon' => '1871010101010001',
            'no_hp_pemohon' => '081399887766', 'satuan_kerja' => 'KPKNL Bandar Lampung',
            'kelurahan_desa' => 'Kedaton', 'kecamatan' => 'Kedaton', 'jumlah_bidang' => 2,
            'petugas_loket_id' => $loket->id, 'nama_petugas_loket' => $loket->name,
            'nomor_telepon' => '081399887766', 'status' => 'verifikasi', 'created_by' => $loket->id,
            'tanggal_target_selesai' => $today->copy()->addDays(30), 'keterangan' => 'Sertifikasi BMN.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t2->id, 'nib' => '08.01.01.01.00841', 'no_sertifikat_lama' => 'SHM No. 7201',
            'jenis_hak' => 'HM', 'nama_pemegang_hak' => 'Pemerintah RI', 'luas_m2' => 2100,
            'desa_kelurahan' => 'Kedaton', 'kecamatan' => 'Kedaton',
        ]);
        TiketPenugasan::create([
            'tiket_id' => $t2->id, 'user_id' => $verif->id, 'stage' => 'verifikasi',
            'status' => 'proses', 'tanggal_add' => $today,
        ]);
        VerifikasiBerkas::create([
            'tiket_id' => $t2->id, 'petugas_id' => $verif->id, 'status_pembetulan' => 'P0',
            'iterasi' => 1, 'tanggal_diterima' => $today, 'status' => 'proses',
            'catatan' => 'Sedang meneliti kelengkapan berkas.',
        ]);

        // T3: Warkah (verifikasi selesai)
        $t3 = Tiket::create([
            'kode_tiket' => "K/3/{$dc}/1", 'nomor_antrian' => 'A-003', 'nomor_urut_berkas' => 3,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today->copy()->subDays(2),
            'jenis_permohonan_id' => $jp02->id, 'nama_pemohon' => 'Dra. Hj. Siti Aminah',
            'nik_pemohon' => '1871036507780002', 'no_hp_pemohon' => '082176543210',
            'kelurahan_desa' => 'Kedaton', 'kecamatan' => 'Kedaton', 'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket->id, 'nama_petugas_loket' => $loket->name,
            'nomor_telepon' => '082176543210', 'status' => 'warkah', 'created_by' => $loket->id,
            'tanggal_target_selesai' => $today->copy()->addDays(5), 'keterangan' => 'Peralihan hak waris.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t3->id, 'nib' => '08.01.03.02.00412', 'no_sertifikat_lama' => 'SHM No. 1092',
            'jenis_hak' => 'HM', 'nama_pemegang_hak' => 'Dra. Hj. Siti Aminah', 'luas_m2' => 385,
            'desa_kelurahan' => 'Kedaton', 'kecamatan' => 'Kedaton',
        ]);
        TiketPenugasan::create(['tiket_id' => $t3->id, 'user_id' => $verif->id, 'stage' => 'verifikasi', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(2), 'tanggal_selesai' => $today->copy()->subDay()]);
        TiketPenugasan::create(['tiket_id' => $t3->id, 'user_id' => $warkah->id, 'stage' => 'warkah', 'status' => 'proses', 'tanggal_add' => $today]);
        LembarKerjaWarkah::create([
            'tiket_id' => $t3->id, 'petugas_id' => $warkah->id,
            'status_berkas_dataset' => 'selesai', 'status_data_sertipikat_bt' => 'selesai',
            'status_data_sertipikat_su' => 'selesai', 'status_sosialisasi' => 'belum',
            'status_public_response' => 'belum', 'catatan' => 'Warkah ditemukan, data BT & SU siap.',
        ]);

        // T4: Validasi BT aktif
        $t4 = Tiket::create([
            'kode_tiket' => "K/4/{$dc}/1", 'nomor_antrian' => 'A-004', 'nomor_urut_berkas' => 4,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today->copy()->subDays(5),
            'jenis_permohonan_id' => $jp01->id, 'nama_pemohon' => 'BPJN Lampung',
            'nik_pemohon' => '1871000000000001', 'no_hp_pemohon' => '081198765432',
            'satuan_kerja' => 'Kementerian PUPR / BPJN', 'kelurahan_desa' => 'Sukarame',
            'kecamatan' => 'Sukarame', 'jumlah_bidang' => 1, 'petugas_loket_id' => $loket->id,
            'nama_petugas_loket' => $loket->name, 'nomor_telepon' => '081198765432',
            'status' => 'validasi_btel', 'status_pra_btel' => 'proses', 'status_pra_suel' => 'menunggu',
            'created_by' => $loket->id, 'tanggal_target_selesai' => $today->copy()->addDays(14),
            'keterangan' => 'Sertifikasi BMN Jalan Nasional.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t4->id, 'nib' => '08.01.05.02.00012', 'no_sertifikat_lama' => 'Alas Hak No. 45/BMN/2024',
            'jenis_hak' => 'HP', 'nama_pemegang_hak' => 'Pemerintah RI c.q. Kementerian PUPR',
            'luas_m2' => 8420, 'desa_kelurahan' => 'Sukarame', 'kecamatan' => 'Sukarame',
        ]);
        TiketPenugasan::create(['tiket_id' => $t4->id, 'user_id' => $valbt->id, 'stage' => 'validasi_btel', 'status' => 'proses', 'tanggal_add' => $today]);
        ValidasiBtel::create([
            'tiket_id' => $t4->id, 'validator_id' => $valbt->id, 'tanggal_mulai' => $today,
            'status_validasi' => 'proses', 'catatan' => 'Validasi data Buku Tanah berlangsung.',
        ]);

        // T5: Validasi SU aktif
        $t5 = Tiket::create([
            'kode_tiket' => "K/5/{$dc}/1", 'nomor_antrian' => 'A-005', 'nomor_urut_berkas' => 5,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today->copy()->subDays(4),
            'jenis_permohonan_id' => $jp02->id, 'nama_pemohon' => 'PT Maju Jaya',
            'nik_pemohon' => '1871050101200003', 'no_hp_pemohon' => '085612345678',
            'kelurahan_desa' => 'Sukabumi', 'kecamatan' => 'Sukabumi', 'jumlah_bidang' => 3,
            'petugas_loket_id' => $loket->id, 'nama_petugas_loket' => $loket->name,
            'nomor_telepon' => '085612345678', 'status' => 'validasi_suel',
            'status_pra_btel' => 'selesai', 'status_pra_suel' => 'proses',
            'created_by' => $loket->id, 'tanggal_target_selesai' => $today->copy()->addDays(14),
            'keterangan' => 'Validasi SU untuk 3 bidang.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t5->id, 'nib' => '08.01.02.01.00100', 'no_sertifikat_lama' => 'SHGB No. 50',
            'jenis_hak' => 'HGB', 'nama_pemegang_hak' => 'PT Maju Jaya', 'luas_m2' => 5200,
            'desa_kelurahan' => 'Sukabumi', 'kecamatan' => 'Sukabumi',
        ]);
        TiketPenugasan::create(['tiket_id' => $t5->id, 'user_id' => $valsu->id, 'stage' => 'validasi_suel', 'status' => 'proses', 'tanggal_add' => $today]);
        ValidasiSuel::create([
            'tiket_id' => $t5->id, 'validator_id' => $valsu->id, 'tanggal_mulai' => $today,
            'status_validasi' => 'proses', 'catatan' => 'Validasi data Surat Ukur.',
        ]);

        // T6: Alih Media BT aktif (validasi sudah selesai)
        $t6 = Tiket::create([
            'kode_tiket' => "K/6/{$dc}/1", 'nomor_antrian' => 'A-006', 'nomor_urut_berkas' => 6,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today->copy()->subDays(7),
            'jenis_permohonan_id' => $jp01->id, 'nama_pemohon' => 'Dinas PUPR Prov. Lampung',
            'nik_pemohon' => '1871020101010004', 'no_hp_pemohon' => '081111222333',
            'satuan_kerja' => 'Dinas PUPR Prov. Lampung', 'kelurahan_desa' => 'Rajabasa',
            'kecamatan' => 'Rajabasa', 'jumlah_bidang' => 1, 'petugas_loket_id' => $loket->id,
            'nama_petugas_loket' => $loket->name, 'nomor_telepon' => '081111222333',
            'status' => 'alih_media_btel', 'status_pra_btel' => 'proses', 'status_pra_suel' => 'selesai',
            'created_by' => $loket->id, 'tanggal_target_selesai' => $today->copy()->addDays(10),
            'keterangan' => 'Alih media BMN jalan provinsi.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t6->id, 'nib' => '08.01.06.01.00005',
            'no_sertifikat_lama' => 'SPPT BMN No. 099/PUPR/2025', 'jenis_hak' => 'HP',
            'nama_pemegang_hak' => 'Pemerintah Prov. Lampung', 'luas_m2' => 15000,
            'desa_kelurahan' => 'Rajabasa', 'kecamatan' => 'Rajabasa',
        ]);
        TiketPenugasan::create(['tiket_id' => $t6->id, 'user_id' => $alihbt->id, 'stage' => 'alih_media_btel', 'status' => 'proses', 'tanggal_add' => $today]);
        AlihMediaBtel::create([
            'tiket_id' => $t6->id, 'petugas_id' => $alihbt->id,
            'status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'belum',
            'status_ttd_elektronik' => 'belum', 'catatan' => 'Scan BT selesai, menunggu KKP.',
        ]);

        // T7: SELESAI (seluruh tahap kelar)
        $t7 = Tiket::create([
            'kode_tiket' => "K/7/{$dc}/1", 'nomor_antrian' => 'A-007', 'nomor_urut_berkas' => 7,
            'status_pembetulan' => 'P0', 'tanggal_masuk' => $today->copy()->subDays(14),
            'jenis_permohonan_id' => $jp01->id, 'nama_pemohon' => 'Balai PJJN Lampung',
            'nik_pemohon' => '1871000000000005', 'no_hp_pemohon' => '081122334455',
            'satuan_kerja' => 'Kementerian PUPR', 'kelurahan_desa' => 'Sukarame',
            'kecamatan' => 'Sukarame', 'jumlah_bidang' => 1, 'petugas_loket_id' => $loket->id,
            'nama_petugas_loket' => $loket->name, 'nomor_telepon' => '081122334455',
            'status_pra_btel' => 'selesai', 'status_pra_suel' => 'selesai',
            'status' => 'selesai', 'status_sps' => true, 'tanggal_sps' => $today->copy()->subDays(2),
            'created_by' => $loket->id, 'tanggal_selesai' => $today,
            'tanggal_target_selesai' => $today->copy()->addDays(14),
            'keterangan' => 'Pendaftaran pertama kali BMN. Selesai 100%.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t7->id, 'nib' => '08.01.05.02.00012', 'no_sertifikat_lama' => 'Alas Hak No. 45/BMN',
            'jenis_hak' => 'HP', 'no_sertifikat_elektronik' => 'AB-009841',
            'nama_pemegang_hak' => 'Pemerintah RI c.q. Kementerian PUPR', 'luas_m2' => 8420,
            'desa_kelurahan' => 'Sukarame', 'kecamatan' => 'Sukarame',
        ]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $verif->id, 'stage' => 'verifikasi', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(14), 'tanggal_selesai' => $today->copy()->subDays(12)]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $warkah->id, 'stage' => 'warkah', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(12), 'tanggal_selesai' => $today->copy()->subDays(10)]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $valbt->id, 'stage' => 'validasi_btel', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(10), 'tanggal_selesai' => $today->copy()->subDays(7)]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $valsu->id, 'stage' => 'validasi_suel', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(10), 'tanggal_selesai' => $today->copy()->subDays(8)]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $alihbt->id, 'stage' => 'alih_media_btel', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(7), 'tanggal_selesai' => $today->copy()->subDays(3)]);
        TiketPenugasan::create(['tiket_id' => $t7->id, 'user_id' => $alihsu->id, 'stage' => 'alih_media_suel', 'status' => 'selesai', 'tanggal_add' => $today->copy()->subDays(7), 'tanggal_selesai' => $today->copy()->subDays(2)]);
        AlihMediaBtel::create([
            'tiket_id' => $t7->id, 'petugas_id' => $alihbt->id,
            'status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'sudah',
            'status_ttd_elektronik' => 'sudah', 'tanggal_terbit_sertifikat_el' => $today->copy()->subDays(2),
            'catatan' => 'Sertifikat Elektronik AB-009841 diterbitkan.',
            'tanggal_mulai' => $today->copy()->subDays(7), 'tanggal_selesai' => $today->copy()->subDays(3),
        ]);
        AlihMediaSuel::create([
            'tiket_id' => $t7->id, 'petugas_id' => $alihsu->id,
            'status_scan_surat_ukur' => 'sudah', 'status_upload_kkp' => 'sudah',
            'status_ttd_elektronik' => 'sudah', 'tanggal_terbit_sertifikat_el' => $today->copy()->subDays(2),
            'catatan' => 'Surat Ukur Elektronik selesai.',
            'tanggal_mulai' => $today->copy()->subDays(7), 'tanggal_selesai' => $today->copy()->subDays(2),
        ]);
    }
}
