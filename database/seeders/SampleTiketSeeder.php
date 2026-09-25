<?php

namespace Database\Seeders;

use App\Models\BidangTanah;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleTiketSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Membersihkan data tiket lama...');

        // Matikan FK sementara agar TRUNCATE tidak kena constraint
        // (mis. validasi_btel.bidang_id → bidang_tanahs.id).
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        foreach ([
            'alih_media_suel', 'alih_media_btel', 'validasi_suel', 'validasi_btel',
            'lembar_kerja_warkahs', 'verifikasi_berkas', 'riwayat_statuses',
            'catatan_revisi', 'tiket_penugasan', 'bidang_tanahs', 'tikets',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        $loket1 = User::where('username', 'loket1')->firstOrFail();
        $loket2 = User::where('username', 'loket2')->firstOrFail();
        $jp = JenisPermohonan::query()->pluck('id', 'kode');
        $today = Carbon::today();
        $dc = $today->format('dmy');
        $seq = 0;

        $this->command?->info('Membuat 10 tiket baru...');

        // ====== LOKET 1 ======
        // T1: JP01 Pertama Kali
        $t1 = Tiket::create([
            'kode_tiket' => 'K/1/'.$dc.'/1',
            'nomor_antrian' => 'A-001',
            'nomor_urut_berkas' => 1,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP01'],
            'nama_pemohon' => 'Ahmad Fauzi',
            'nik_pemohon' => '1871010112850001',
            'no_hp_pemohon' => '081234560001',
            'kelurahan_desa' => 'Way Halim Permai',
            'kecamatan' => 'Way Halim',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket1->id,
            'nama_petugas_loket' => $loket1->name,
            'nomor_telepon' => '081234560001',
            'status' => 'diterima',
            'created_by' => $loket1->id,
            'keterangan' => 'Pendaftaran pertama kali hak milik atas tanah warisan.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t1->id,
            'nib' => '08.01.04.05.00011',
            'no_sertifikat_lama' => '-', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Ahmad Fauzi',
            'desa_kelurahan' => 'Way Halim Permai', 'kecamatan' => 'Way Halim', 'urutan' => 1,
        ]);

        // T2: JP02 Peralihan Jual Beli
        $t2 = Tiket::create([
            'kode_tiket' => 'K/2/'.$dc.'/1',
            'nomor_antrian' => 'A-002',
            'nomor_urut_berkas' => 2,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP02'],
            'nama_pemohon' => 'Rina Susanti',
            'nik_pemohon' => '1871024506900005',
            'no_hp_pemohon' => '081234560002',
            'kelurahan_desa' => 'Kedaton',
            'kecamatan' => 'Kedaton',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket1->id,
            'nama_petugas_loket' => $loket1->name,
            'nomor_telepon' => '081234560002',
            'nomor_tiket_ppat' => 'PPAT-2026-001',
            'status' => 'diterima',
            'created_by' => $loket1->id,
            'keterangan' => 'Peralihan hak jual beli tanah di kawasan padat.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t2->id,
            'nib' => '08.01.02.03.00022',
            'no_sertifikat_lama' => 'SHM No. 1201', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Rina Susanti',
            'desa_kelurahan' => 'Kedaton', 'kecamatan' => 'Kedaton', 'urutan' => 1,
        ]);

        // T3: JP04 Ganti Nama
        $t3 = Tiket::create([
            'kode_tiket' => 'K/3/'.$dc.'/1',
            'nomor_antrian' => 'A-003',
            'nomor_urut_berkas' => 3,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP04'],
            'nama_pemohon' => 'Budi Hartono',
            'nik_pemohon' => '1871031007800010',
            'no_hp_pemohon' => '081234560003',
            'kelurahan_desa' => 'Sukamiskin',
            'kecamatan' => 'Rajabasa',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket1->id,
            'nama_petugas_loket' => $loket1->name,
            'nomor_telepon' => '081234560003',
            'nomor_tiket_non_ppat' => 'BK-2026-001',
            'status' => 'diterima',
            'created_by' => $loket1->id,
            'keterangan' => 'Ganti nama sertifikat atas waris.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t3->id,
            'nib' => '08.01.03.04.00033',
            'no_sertifikat_lama' => 'SHM No. 2304', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Budi Hartono',
            'desa_kelurahan' => 'Sukamiskin', 'kecamatan' => 'Rajabasa', 'urutan' => 1,
        ]);

        // T4: JP06 Roya
        $t4 = Tiket::create([
            'kode_tiket' => 'K/4/'.$dc.'/1',
            'nomor_antrian' => 'A-004',
            'nomor_urut_berkas' => 4,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP06'],
            'nama_pemohon' => 'PT Maju Jaya',
            'nik_pemohon' => '1871020101900002',
            'no_hp_pemohon' => '081234560004',
            'kelurahan_desa' => 'Bumi Waras',
            'kecamatan' => 'Teluk Betung Selatan',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket1->id,
            'nama_petugas_loket' => $loket1->name,
            'nomor_telepon' => '081234560004',
            'nomor_tiket_ppat' => 'PPAT-2026-002',
            'status' => 'diterima',
            'created_by' => $loket1->id,
            'keterangan' => 'Roya atas hak tanggungan yang sudah lunas.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t4->id,
            'nib' => '08.01.05.06.00044',
            'no_sertifikat_lama' => 'SHM No. 3405', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'PT Maju Jaya',
            'desa_kelurahan' => 'Bumi Waras', 'kecamatan' => 'Teluk Betung Selatan', 'urutan' => 1,
        ]);

        // T5: JP13 PTPGT
        $t5 = Tiket::create([
            'kode_tiket' => 'K/5/'.$dc.'/1',
            'nomor_antrian' => 'A-005',
            'nomor_urut_berkas' => 5,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP13'],
            'nama_pemohon' => 'Dewi Anggraini',
            'nik_pemohon' => '1871044507920015',
            'no_hp_pemohon' => '081234560005',
            'kelurahan_desa' => 'Gedong Meneng',
            'kecamatan' => 'Rajabasa',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket1->id,
            'nama_petugas_loket' => $loket1->name,
            'nomor_telepon' => '081234560005',
            'nomor_tiket_non_ppat' => 'BK-2026-002',
            'status' => 'diterima',
            'created_by' => $loket1->id,
            'keterangan' => 'Pemberian Hak Tanggungan atas SHM untuk pinjaman bank.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t5->id,
            'nib' => '08.01.06.07.00055',
            'no_sertifikat_lama' => 'SHM No. 4506', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Dewi Anggraini',
            'desa_kelurahan' => 'Gedong Meneng', 'kecamatan' => 'Rajabasa', 'urutan' => 1,
        ]);

        // ====== LOKET 2 ======
        // T6: JP03 Perubahan Hak
        $t6 = Tiket::create([
            'kode_tiket' => 'K/6/'.$dc.'/1',
            'nomor_antrian' => 'B-006',
            'nomor_urut_berkas' => 6,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP03'],
            'nama_pemohon' => 'Surya Darma',
            'nik_pemohon' => '1871021405820020',
            'no_hp_pemohon' => '085212340006',
            'kelurahan_desa' => 'Labuan Ratu',
            'kecamatan' => 'Kedaton',
            'jumlah_bidang' => 2,
            'petugas_loket_id' => $loket2->id,
            'nama_petugas_loket' => $loket2->name,
            'nomor_telepon' => '085212340006',
            'nomor_tiket_ppat' => 'PPAT-2026-003',
            'status' => 'diterima',
            'created_by' => $loket2->id,
            'keterangan' => 'Perubahan hak dari HGB ke HM untuk kawasan perumahan.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t6->id,
            'nib' => '08.01.07.08.00066',
            'no_sertifikat_lama' => 'HGB No. 100', 'jenis_hak' => 'HGB',
            'nama_pemegang_hak' => 'Surya Darma',
            'desa_kelurahan' => 'Labuan Ratu', 'kecamatan' => 'Kedaton', 'urutan' => 1,
        ]);
        BidangTanah::create([
            'tiket_id' => $t6->id,
            'nib' => '08.01.07.08.00077',
            'no_sertifikat_lama' => 'HGB No. 101', 'jenis_hak' => 'HGB',
            'nama_pemegang_hak' => 'Surya Darma',
            'desa_kelurahan' => 'Labuan Ratu', 'kecamatan' => 'Kedaton', 'urutan' => 2,
        ]);

        // T7: JP05 BN Jual Beli
        $t7 = Tiket::create([
            'kode_tiket' => 'K/7/'.$dc.'/1',
            'nomor_antrian' => 'B-007',
            'nomor_urut_berkas' => 7,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP05'],
            'nama_pemohon' => 'Kartika Putri',
            'nik_pemohon' => '1871036008950030',
            'no_hp_pemohon' => '085212340007',
            'kelurahan_desa' => 'Kali Balau',
            'kecamatan' => 'Kedaton',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket2->id,
            'nama_petugas_loket' => $loket2->name,
            'nomor_telepon' => '085212340007',
            'nomor_tiket_non_ppat' => 'BK-2026-003',
            'status' => 'diterima',
            'created_by' => $loket2->id,
            'keterangan' => 'Berita Negara atas jual beli sertifikat tanah waris.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t7->id,
            'nib' => '08.01.08.09.00088',
            'no_sertifikat_lama' => 'SHM No. 5607', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Kartika Putri',
            'desa_kelurahan' => 'Kali Balau', 'kecamatan' => 'Kedaton', 'urutan' => 1,
        ]);

        // T8: JP07 BN Kewarisan
        $t8 = Tiket::create([
            'kode_tiket' => 'K/8/'.$dc.'/1',
            'nomor_antrian' => 'B-008',
            'nomor_urut_berkas' => 8,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP07'],
            'nama_pemohon' => 'Hendri Gunawan',
            'nik_pemohon' => '1871042503780040',
            'no_hp_pemohon' => '085212340008',
            'kelurahan_desa' => 'Way Kandis',
            'kecamatan' => 'Tanjung Karang Timur',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket2->id,
            'nama_petugas_loket' => $loket2->name,
            'nomor_telepon' => '085212340008',
            'nomor_tiket_ppat' => 'PPAT-2026-004',
            'status' => 'diterima',
            'created_by' => $loket2->id,
            'keterangan' => 'Berita Negara atas waris sertifikat atas nama almarhumah.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t8->id,
            'nib' => '08.01.09.10.00099',
            'no_sertifikat_lama' => 'SHM No. 6708', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Hendri Gunawan',
            'desa_kelurahan' => 'Way Kandis', 'kecamatan' => 'Tanjung Karang Timur', 'urutan' => 1,
        ]);

        // T9: JP11 BN Putusan Pengadilan
        $t9 = Tiket::create([
            'kode_tiket' => 'K/9/'.$dc.'/1',
            'nomor_antrian' => 'B-009',
            'nomor_urut_berkas' => 9,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP11'],
            'nama_pemohon' => 'Siti Rohmah',
            'nik_pemohon' => '1871046512900050',
            'no_hp_pemohon' => '085212340009',
            'kelurahan_desa' => 'Surabaya',
            'kecamatan' => 'Kedaton',
            'jumlah_bidang' => 1,
            'petugas_loket_id' => $loket2->id,
            'nama_petugas_loket' => $loket2->name,
            'nomor_telepon' => '085212340009',
            'nomor_tiket_non_ppat' => 'BK-2026-004',
            'status' => 'diterima',
            'created_by' => $loket2->id,
            'keterangan' => 'Berita Negara berdasarkan putusan pengadilan (eksekusi).',
        ]);
        BidangTanah::create([
            'tiket_id' => $t9->id,
            'nib' => '08.01.10.11.00110',
            'no_sertifikat_lama' => 'SHM No. 7809', 'jenis_hak' => 'HM',
            'nama_pemegang_hak' => 'Siti Rohmah',
            'desa_kelurahan' => 'Surabaya', 'kecamatan' => 'Kedaton', 'urutan' => 1,
        ]);

        // T10: JP12 Pengukuran Pembaharuan
        $t10 = Tiket::create([
            'kode_tiket' => 'K/10/'.$dc.'/1',
            'nomor_antrian' => 'B-010',
            'nomor_urut_berkas' => 10,
            'status_pembetulan' => 'P0',
            'tanggal_masuk' => $today,
            'jenis_permohonan_id' => $jp['JP12'],
            'nama_pemohon' => 'PT Lampung Makmur',
            'nik_pemohon' => '1871020101900060',
            'no_hp_pemohon' => '085212340010',
            'kelurahan_desa' => 'Sukadana Ham',
            'kecamatan' => 'Rajabasa',
            'jumlah_bidang' => 3,
            'petugas_loket_id' => $loket2->id,
            'nama_petugas_loket' => $loket2->name,
            'nomor_telepon' => '085212340010',
            'nomor_tiket_ppat' => 'PPAT-2026-005',
            'status' => 'diterima',
            'created_by' => $loket2->id,
            'keterangan' => 'Pengukuran pembaharuan untuk 3 bidang tanah industri.',
        ]);
        BidangTanah::create([
            'tiket_id' => $t10->id,
            'nib' => '08.01.11.12.00121',
            'no_sertifikat_lama' => 'HGB No. 201', 'jenis_hak' => 'HGB',
            'nama_pemegang_hak' => 'PT Lampung Makmur',
            'desa_kelurahan' => 'Sukadana Ham', 'kecamatan' => 'Rajabasa', 'urutan' => 1,
        ]);
        BidangTanah::create([
            'tiket_id' => $t10->id,
            'nib' => '08.01.11.12.00132',
            'no_sertifikat_lama' => 'HGB No. 202', 'jenis_hak' => 'HGB',
            'nama_pemegang_hak' => 'PT Lampung Makmur',
            'desa_kelurahan' => 'Sukadana Ham', 'kecamatan' => 'Rajabasa', 'urutan' => 2,
        ]);
        BidangTanah::create([
            'tiket_id' => $t10->id,
            'nib' => '08.01.11.12.00143',
            'no_sertifikat_lama' => 'HGB No. 203', 'jenis_hak' => 'HGB',
            'nama_pemegang_hak' => 'PT Lampung Makmur',
            'desa_kelurahan' => 'Sukadana Ham', 'kecamatan' => 'Rajabasa', 'urutan' => 3,
        ]);

        $this->command?->info('Selesai! 10 tiket baru dibuat:');
        $this->command?->table(
            ['Kode Tiket', 'Loket', 'Jenis Permohonan', 'Pemohon', 'Bidang', 'Status'],
            [
                [$t1->kode_tiket, 'Loket 1', 'JP01 - Pertama Kali', 'Ahmad Fauzi', '1', 'diterima'],
                [$t2->kode_tiket, 'Loket 1', 'JP02 - Peralihan Jual Beli', 'Rina Susanti', '1', 'diterima'],
                [$t3->kode_tiket, 'Loket 1', 'JP04 - Ganti Nama', 'Budi Hartono', '1', 'diterima'],
                [$t4->kode_tiket, 'Loket 1', 'JP06 - Roya', 'PT Maju Jaya', '1', 'diterima'],
                [$t5->kode_tiket, 'Loket 1', 'JP13 - PTPGT', 'Dewi Anggraini', '1', 'diterima'],
                [$t6->kode_tiket, 'Loket 2', 'JP03 - Perubahan Hak', 'Surya Darma', '2', 'diterima'],
                [$t7->kode_tiket, 'Loket 2', 'JP05 - BN Jual Beli', 'Kartika Putri', '1', 'diterima'],
                [$t8->kode_tiket, 'Loket 2', 'JP07 - BN Kewarisan', 'Hendri Gunawan', '1', 'diterima'],
                [$t9->kode_tiket, 'Loket 2', 'JP11 - BN Putusan Pengadilan', 'Siti Rohmah', '1', 'diterima'],
                [$t10->kode_tiket, 'Loket 2', 'JP12 - Pengukuran Pembaharuan', 'PT Lampung Makmur', '3', 'diterima'],
            ]
        );
    }
}
