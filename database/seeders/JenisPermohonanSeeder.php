<?php

namespace Database\Seeders;

use App\Models\JenisPermohonan;
use App\Models\PersyaratanDokumen;
use App\Models\SaranKoreksi;
use Illuminate\Database\Seeder;

class JenisPermohonanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode' => 'JP01',
                'nama' => 'Pertama Kali',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Pertama Kali & Surat Kuasa (bila dikuasakan)',
                    'Fotokopi Identitas Diri (KTP, KK)',
                    'Bukti Kepemilikan Tanah / Alas Hak Asli',
                    'SPPT PBB Tahun Berjalan & Bukti Lunas',
                    'Surat Pernyataan Penguasaan Fisik Bidang Tanah (Sporadik)',
                    'Surat Keterangan Riwayat Tanah dari Kelurahan',
                ],
            ],
            [
                'kode' => 'JP02',
                'nama' => 'Peralihan Jual Beli',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Ditandatangani Pemohon/Kuasa',
                    'Sertifikat Asli',
                    'Akta Jual Beli (AJB) dari PPAT',
                    'Fotokopi KTP & KK Penjual dan Pembeli',
                    'Bukti Bayar BPHTB & PPh Final',
                    'SPPT PBB Tahun Berjalan & Bukti Lunas',
                ],
            ],
            [
                'kode' => 'JP03',
                'nama' => 'Perubahan Hak',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Perubahan Hak',
                    'Sertifikat Asli',
                    'Surat Permohonan Perubahan Hak',
                    'Fotokopi KTP Pemohon',
                    'SPPT PBB Tahun Berjalan',
                ],
            ],
            [
                'kode' => 'JP04',
                'nama' => 'Ganti Nama',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Ganti Nama',
                    'Sertifikat Asli',
                    'Fotokopi KTP & KK Pemohon',
                    'Surat Keterangan Ganti Nama dari Kelurahan / Disdukcapil',
                    'Bukti Pembayaran Pajak',
                ],
            ],
            [
                'kode' => 'JP05',
                'nama' => 'BN Jual Beli',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Berita Negara',
                    'Fotokopi KTP & KK Pemohon',
                    'Fotokopi Akta Jual Beli (AJB)',
                ],
            ],
            [
                'kode' => 'JP06',
                'nama' => 'Roya',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Roya',
                    'Sertifikat Tanah Asli',
                    'Sertifikat Hak Tanggungan (SHT) Asli',
                    'Surat Pelunasan / Keterangan Roya dari Kreditur / Bank',
                    'Fotokopi KTP Pemohon',
                ],
            ],
            [
                'kode' => 'JP07',
                'nama' => 'BN Kewarisan',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Surat Keterangan Kematian Pewaris',
                    'Surat Keterangan Waris / Akta Waris Notaris',
                    'Fotokopi KTP & KK Seluruh Ahli Waris',
                    'Berita Negara',
                ],
            ],
            [
                'kode' => 'JP08',
                'nama' => 'Blokir',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Blokir',
                    'Sertifikat Asli',
                    'Surat Permohonan Blokir',
                    'Fotokopi KTP Pemohon',
                    'Surat Kuasa (bila dikuasakan)',
                ],
            ],
            [
                'kode' => 'JP09',
                'nama' => 'Hapus BPHTB',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Fotokopi KTP Pemohon',
                    'Surat Keterangan Hapus BPHTB',
                    'Bukti Pembayaran PBB',
                ],
            ],
            [
                'kode' => 'JP10',
                'nama' => 'Peralihan Lelang',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Akta Lelang dari Pejabat Lelang',
                    'Fotokopi KTP Pembeli Lelang',
                    'Berita Negara',
                ],
            ],
            [
                'kode' => 'JP11',
                'nama' => 'BN Putusan Pengadilan',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Putusan Pengadilan yang Berkekuatan Hukum Tetap (Inkracht)',
                    'Fotokopi KTP Pemohon',
                    'Berita Negara',
                ],
            ],
            [
                'kode' => 'JP12',
                'nama' => 'Pengukuran untuk Pembaharuan',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Pengukuran',
                    'Sertifikat Asli',
                    'Fotokopi KTP Pemohon',
                    'Surat Permohonan Pengukuran',
                    'SPPT PBB Tahun Berjalan',
                ],
            ],
            [
                'kode' => 'JP13',
                'nama' => 'PTPGT',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Akta Pemberian Hak Tanggungan (APHT) dari PPAT',
                    'Fotokopi KTP Pemberi dan Penerima Hak Tanggungan',
                    'Surat Kuasa Membebankan Hak Tanggungan (SKMHT) jika ada',
                ],
            ],
            [
                'kode' => 'JP14',
                'nama' => 'Perbaikan',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan Perbaikan',
                    'Sertifikat Asli yang akan Diperbaiki',
                    'Fotokopi KTP Pemohon',
                    'Surat Pernyataan Perbaikan',
                    'Bukti Dokumen Pendukung',
                ],
            ],
            [
                'kode' => 'JP15',
                'nama' => 'Peralihan Hak (Lainnya)',
                'kategori' => 'umum',
                'persyaratan' => [
                    'Formulir Permohonan',
                    'Sertifikat Asli',
                    'Dokumen Alas Hak / Pelepasan Hak',
                    'Fotokopi KTP Pemohon',
                    'Surat Pernyataan Pemindahan Hak',
                ],
            ],
        ];

        foreach ($data as $item) {
            $jp = JenisPermohonan::updateOrCreate(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'kategori' => $item['kategori'],
                    'is_active' => true,
                ]
            );

            PersyaratanDokumen::where('jenis_permohonan_id', $jp->id)->delete();
            foreach ($item['persyaratan'] as $index => $req) {
                PersyaratanDokumen::create([
                    'jenis_permohonan_id' => $jp->id,
                    'nama_dokumen' => $req,
                    'wajib' => true,
                    'urutan' => $index + 1,
                ]);
            }

            // Default saran koreksi contoh
            SaranKoreksi::updateOrCreate(
                ['jenis_permohonan_id' => $jp->id, 'nama_dokumen_kurang' => 'Kelengkapan Identitas / Alas Hak'],
                [
                    'pesan_koreksi' => 'Harap melengkapi fotokopi KTP/KK yang masih berlaku atau melampirkan bukti alas hak/akta yang telah dilegalisir.',
                    'dasar_hukum' => 'PMNA/KBPN No. 3 Tahun 1997',
                ]
            );
        }
    }
}
