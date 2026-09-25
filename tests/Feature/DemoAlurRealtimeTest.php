<?php

namespace Tests\Feature;

use App\Models\CatatanRevisi;
use App\Models\JenisPermohonan;
use App\Models\Tiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * DEMO ALUR & KONDISI REALTIME — 13 September 2026.
 *
 * Menjalankan "demo realtime" melalui HTTP route asli (bukan panggilan service
 * langsung) agar seluruh middleware, controller, view, dan transaksi DB yang
 * dipakai petugas benar-benar dieksekusi:
 *
 *   Gelombang 1 (T1) — Happy path akun #1: 6 tahap selesai → status SELESAI.
 *   Gelombang 2 (T2) — Happy path akun #2 (pembuktian banyak akun per jabatan).
 *   Gelombang 3 (T3) — Revisi eksternal P1: Verifikator → Loket → Resubmit.
 *   Gelombang 4 (T4) — Revisi internal (Validator → Warkah) + 3 kondisi error
 *                      (claim prematur Validator, gate Alih Media, anti-duplikat).
 *   Visibilitas — Catatan per tahap, catatan revisi, timeline, tracking publik.
 */
class DemoAlurRealtimeTest extends TestCase
{
    use RefreshDatabase;

    /** Pemetaan tahap → prefix route untuk HTTP route group. */
    private const ROUTE_BASE = [
        'verifikasi' => 'verifikator',
        'warkah' => 'warkah',
        'validasi_btel' => 'validator_btel',
        'validasi_suel' => 'validator_suel',
        'alih_media_btel' => 'alih_media_btel',
        'alih_media_suel' => 'alih_media_suel',
    ];

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function user(string $role, int $no = 1): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role))." {$no}",
            'username' => $role.$no,
            'email' => $role.$no.'@loket.test',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function jenis(string $kode = 'DEMO'): JenisPermohonan
    {
        return JenisPermohonan::create([
            'kode' => $kode,
            'nama' => 'Permohonan Demo Alur Realtime',
            'kategori' => 'umum',
            'is_active' => true,
        ]);
    }

    /** Registrasi via HTTP route Loket (POST /loket + simpan). */
    private function daftarkanTiket(User $loket, string $kode, string $pemohon = 'Demo'): Tiket
    {
        $this->actingAs($loket);

        $this->post(route('loket.store'), [
            'kode_tiket' => $kode,
            'jenis_permohonan_id' => $this->jenis(str_replace(['/', '.'], '', $kode))->id,
            'nama_pemohon' => $pemohon,
            'nik_pemohon' => '18710'.now()->format('ymdHis'),
            'no_hp_pemohon' => '08130000'.now()->format('His'),
            'no_hak_sekarang' => 'M.1088/NEGERI OLOK',
            'kelurahan_desa' => 'Way Laga',
            'kecamatan' => 'Panjang',
            'jumlah_bidang' => 1,
            'keterangan' => "Tiket demo realtime {$kode}",
            'bidang' => [[
                'nib' => '08.01.01.01.'.abs(crc32($kode)) % 999999,
                'no_sertifikat_lama' => 'SHM No. DEMO-'.$this->hashKode($kode),
                'jenis_hak' => 'HM',
                'nama_pemegang_hak' => $pemohon,
                'desa_kelurahan' => 'Way Laga',
                'kecamatan' => 'Panjang',
            ]],
        ])->assertSessionHasNoErrors();

        return Tiket::where('kode_tiket', $kode)->firstOrFail();
    }

    private function hashKode(string $kode): int
    {
        return abs(crc32($kode)) % 10000;
    }

    /** Add tiket dari DB Admin melalui HTTP route tahap. */
    private function addTiket(User $petugas, Tiket $tiket, string $stage): void
    {
        $this->actingAs($petugas);
        $this->post(route(self::ROUTE_BASE[$stage].'.add', $tiket->id))->assertSessionHasNoErrors();
    }

    /** Simpan hasil kerja stage melalui HTTP route ({base}.simpan). */
    private function simpanTahap(User $petugas, Tiket $tiket, string $stage, array $data): void
    {
        $this->actingAs($petugas);
        $this->post(route(self::ROUTE_BASE[$stage].'.simpan', $tiket->id), $data)->assertSessionHasNoErrors();
    }

    /** Proses Selesai tahap melalui HTTP route ({base}.selesai). */
    private function selesaiTahap(User $petugas, Tiket $tiket, string $stage, string $catatan): void
    {
        $this->actingAs($petugas);
        $this->post(route(self::ROUTE_BASE[$stage].'.selesai', $tiket->id), ['catatan' => $catatan])->assertSessionHasNoErrors();
        $tiket->refresh();
    }

    private function dataSimpanWarkah(): array
    {
        return [
            'status_data_sertipikat_bt' => 'selesai',
            'status_data_sertipikat_su' => 'selesai',
            'status_sosialisasi' => 'selesai',
            'status_dokumen_bt' => 'ada',
            'status_dokumen_su' => 'ada',
            'jumlah_berkas' => 3,
            'jumlah_halaman' => 12,
            'gabungan' => 0,
            'keterangan_status' => 'Data warkah lengkap dan siap.',
        ];
    }

    /** KIRIM berkas BT/SU ke Validator (gerbang paralel) melalui HTTP route warkah.kirim. */
    private function kirimBerkas(User $warkah, Tiket $tiket): void
    {
        $validator = User::whereIn('role', ['validator_btel', 'validator_suel'])
            ->where('is_active', true)
            ->first() ?? $this->user('validator_btel');

        $this->actingAs($warkah);
        $this->post(route('warkah.kirim', $tiket->id), [
            'penerima_validator_id' => $validator->id,
            'waktu_serah' => now()->format('Y-m-d\TH:i'),
            'status_berkas_bt' => 'lengkap',
            'status_berkas_su' => 'lengkap',
            'catatan_kondisi_berkas' => 'Berkas BT/SU diserahkan lengkap untuk demo alur.',
        ])->assertSessionHasNoErrors();
        $tiket->refresh();
    }

    /** Konfirmasi Pengembalian Berkas BT/SU ke Warkah melalui HTTP route warkah.pengembalian. */
    private function catatPengembalian(User $warkah, Tiket $tiket): void
    {
        $this->actingAs($warkah);
        $this->post(route('warkah.pengembalian', $tiket->id), [
            'petugas_pengembali' => 'Petugas Warkah '.$warkah->name,
            'waktu_kembali' => now()->format('Y-m-d\TH:i'),
            'kondisi_berkas_kembali' => 'lengkap',
            'catatan_pengembalian' => 'Berkas BT/SU diterima kembali oleh Warkah.',
            'jumlah_berkas_dikembalikan' => 3,
        ])->assertSessionHasNoErrors();
        $tiket->refresh();
    }

    /** Satu gelombang happy-path penuh: Loket → 6 tahap → SELESAI. */
    private function jalankanHappyPath(User $loket, User $verif, User $warkah, User $vbtel, User $vsuel, User $ambt, User $amsu, string $kode, int $no): void
    {
        $tiket = $this->daftarkanTiket($loket, $kode, "Pemohon Wave {$no}");

        // Jalur paralel: Verifikator ║ Warkah sama-sama dapat meng-Add.
        $this->addTiket($verif, $tiket, 'verifikasi');
        $this->addTiket($warkah, $tiket, 'warkah');

        // Warkah menyiapkan data lalu KIRIM berkas BT/SU (DIPINJAM) → buka Validator.
        $this->simpanTahap($warkah, $tiket, 'warkah', $this->dataSimpanWarkah());
        $this->kirimBerkas($warkah, $tiket);
        $this->assertTrue($tiket->fresh()->isDiserahkanKeValidator());

        // Verifikator menyelesaikan pemeriksaan.
        $this->simpanTahap($verif, $tiket, 'verifikasi', ['status_verifikasi' => 'lengkap', 'catatan' => 'Berkas verifikasi lengkap dan sah.']);
        $this->selesaiTahap($verif, $tiket, 'verifikasi', "Selesai verifikator {$no}: berkas dianggap lengkap.");

        // Validator BT & SU paralel.
        $this->addTiket($vbtel, $tiket, 'validasi_btel');
        $this->addTiket($vsuel, $tiket, 'validasi_suel');
        $this->simpanTahap($vbtel, $tiket, 'validasi_btel', ['status_validasi' => 'lulus', 'catatan' => 'Validasi BT sesuai.']);
        $this->selesaiTahap($vbtel, $tiket, 'validasi_btel', "Selesai validator BT {$no}.");
        $this->simpanTahap($vsuel, $tiket, 'validasi_suel', ['status_validasi' => 'lulus', 'catatan' => 'Validasi SU sesuai.']);
        $this->selesaiTahap($vsuel, $tiket, 'validasi_suel', "Selesai validator SU {$no}.");

        // Gate parallel: Warkah cukup KIRIM (belum harus selesai) — Alih Media dapat mulai.
        $this->addTiket($ambt, $tiket, 'alih_media_btel');
        $this->addTiket($amsu, $tiket, 'alih_media_suel');
        $this->simpanTahap($ambt, $tiket, 'alih_media_btel', ['status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah', 'tanggal_terbit_sertifikat_el' => now()->toDateString()]);
        $this->selesaiTahap($ambt, $tiket, 'alih_media_btel', "Selesai alih media BT {$no}: sertifikat BT elektronik terbit.");
        $this->simpanTahap($amsu, $tiket, 'alih_media_suel', ['status_scan_surat_ukur' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah', 'tanggal_terbit_sertifikat_el' => now()->toDateString()]);
        $this->selesaiTahap($amsu, $tiket, 'alih_media_suel', "Selesai alih media SU {$no}: sertifikat SU elektronik terbit.");

        // BELUM selesai — menunggu Warkah mencatat pengembalian hardcopy.
        $this->assertNotSame('selesai', $tiket->fresh()->status);

        // Warkah mencatat pengembalian lalu Proses Selesai → SELESAI.
        $this->catatPengembalian($warkah, $tiket);
        $this->selesaiTahap($warkah, $tiket, 'warkah', "Selesai warkah {$no}: berkas BT/SU telah dikembalikan ke Warkah.");

        $this->assertSame('selesai', $tiket->fresh()->status);
        $this->assertTrue($tiket->fresh()->isAlihMediaSelesai());
        $this->assertNotNull($tiket->fresh()->tanggal_selesai);
    }
    // ------------------------------------------------------------------
    // Gelombang 1 — T1 happy path (akun #1)
    // ------------------------------------------------------------------

    public function test_gelombang_1_t1_happy_path_akun_1_sampai_selesai(): void
    {
        $low = [
            'loket' => $this->user('loket'),
            'verif' => $this->user('verifikator'),
            'warkah' => $this->user('warkah'),
            'vbtel' => $this->user('validator_btel'),
            'vsuel' => $this->user('validator_suel'),
            'ambt' => $this->user('alih_media_btel'),
            'amsu' => $this->user('alih_media_suel'),
        ];

        $this->jalankanHappyPath($low['loket'], $low['verif'], $low['warkah'], $low['vbtel'], $low['vsuel'], $low['ambt'], $low['amsu'], 'DEMO/G1/T1', 1);

        $tiket = Tiket::where('kode_tiket', 'DEMO/G1/T1')->firstOrFail();
        $this->assertSame(6, $tiket->penugasans()->where('status', 'selesai')->count());
        $this->assertSame('P0', $tiket->status_pembetulan);
        $this->assertSame(0, $tiket->revisi_ke);
        // Timeline riwayat lengkap: 1 registrasi + 6 Add + 6 Selesai + 1 serah Warkah→Validator + 1 pengembalian.
        $this->assertDatabaseCount('riwayat_statuses', 15);
    }

    // ------------------------------------------------------------------
    // Gelombang 2 — T2 happy path (akun #2)
    // ------------------------------------------------------------------

    public function test_gelombang_2_t2_happy_path_akun_2_bukti_banyak_akun_per_jabatan(): void
    {
        $low = [
            'loket' => $this->user('loket', 2),
            'verif' => $this->user('verifikator', 2),
            'warkah' => $this->user('warkah', 2),
            'vbtel' => $this->user('validator_btel', 2),
            'vsuel' => $this->user('validator_suel', 2),
            'ambt' => $this->user('alih_media_btel', 2),
            'amsu' => $this->user('alih_media_suel', 2),
        ];

        $this->jalankanHappyPath($low['loket'], $low['verif'], $low['warkah'], $low['vbtel'], $low['vsuel'], $low['ambt'], $low['amsu'], 'DEMO/G2/T2', 2);

        $tiket = Tiket::where('kode_tiket', 'DEMO/G2/T2')->firstOrFail();
        // Setiap tahap dikerjakan akun #2 — tidak bertabrakan dengan akun #1.
        $this->assertTrue($tiket->penugasans()->where('user_id', $low['verif']->id)->where('status', 'selesai')->exists());
        $this->assertTrue($tiket->penugasans()->where('user_id', $low['amsu']->id)->where('status', 'selesai')->exists());
        $this->assertSame('selesai', $tiket->status);
    }
    // ------------------------------------------------------------------
    // Gelombang 3 — T3 revisi eksternal P1 (Verifikator → Loket → Resubmit)
    // ------------------------------------------------------------------

    public function test_gelombang_3_t3_revisi_eksternal_verifikator_ke_loket_lalu_resubmit(): void
    {
        $loket2 = $this->user('loket', 2);
        $verif1 = $this->user('verifikator');
        $warkah1 = $this->user('warkah');
        $vbtel1 = $this->user('validator_btel');
        $vsuel1 = $this->user('validator_suel');
        $ambt1 = $this->user('alih_media_btel');
        $amsu1 = $this->user('alih_media_suel');

        $tiket = $this->daftarkanTiket($loket2, 'DEMO/G3/T3', 'Pemohon Revisi Eksternal');
        $this->addTiket($verif1, $tiket, 'verifikasi');

        // Verifikator: status perbaikan → kemudian dikembalikan ke Loket via modal revisi.
        $this->simpanTahap($verif1, $tiket, 'verifikasi', ['status_verifikasi' => 'perbaikan', 'catatan' => 'Berkas kurang lengkap.']);
        $this->actingAs($verif1);
        $this->post(route('verifikator.revisi', $tiket->id), [
            'isi_revisi' => 'Berkas SHM asli dan KTP pemohon belum terlampir.',
            'ke_stage' => 'loket',
        ])->assertSessionHasNoErrors();

        $tiket->refresh();
        $this->assertSame('dikembalikan', $tiket->status);
        $this->assertSame(1, $tiket->revisi_ke);
        $this->assertDatabaseHas('catatan_revisi', [
            'tiket_id' => $tiket->id,
            'dari_stage' => 'verifikasi',
            'ke_stage' => 'loket',
            'revisi_ke' => 1,
            'sudah_diproses' => false,
        ]);

        // Visibilitas revisi: Loket melihat alert merah + isi revisi.
        $this->actingAs($loket2);
        $this->get(route('loket.show', $tiket->id))
            ->assertSee('Berkas Dikembalikan untuk Perbaikan Revisi')
            ->assertSee('Berkas SHM asli dan KTP pemohon belum terlampir.');

        // Admin melihat revisi di menu Revisi Perbaikan.
        $admin = $this->user('admin');
        $this->actingAs($admin);
        $this->get(route('admin.revisi'))->assertSee('DEMO/G3/T3');

        // Loket menerima perbaikan dari pemohon → resubmit → kembali ke tahap asal.
        $this->actingAs($loket2);
        $this->post(route('loket.resubmit', $tiket->id), ['catatan_perbaikan' => 'SHM asli dan KTP sudah dilampirkan.'])
            ->assertSessionHasNoErrors();

        $tiket->refresh();
        $this->assertSame('verifikasi', $tiket->status);
        $this->assertSame('P1', $tiket->status_pembetulan);
        $this->assertTrue((bool) CatatanRevisi::where('tiket_id', $tiket->id)->first()->sudah_diproses);

        // Verifikator meng-Add ulang tiket revisi, perbaiki, dan teruskan ke seluruh tahap.
        $this->addTiket($verif1, $tiket, 'verifikasi');
        $this->simpanTahap($verif1, $tiket, 'verifikasi', ['status_verifikasi' => 'lengkap', 'catatan' => 'Setelah perbaikan, berkas lengkap.']);
        $this->selesaiTahap($verif1, $tiket, 'verifikasi', 'Selesai verifikator 1 (setelah revisi P1).');

        $this->addTiket($warkah1, $tiket, 'warkah');
        $this->simpanTahap($warkah1, $tiket, 'warkah', $this->dataSimpanWarkah());
        $this->kirimBerkas($warkah1, $tiket);

        $this->addTiket($vbtel1, $tiket, 'validasi_btel');
        $this->addTiket($vsuel1, $tiket, 'validasi_suel');
        $this->simpanTahap($vbtel1, $tiket, 'validasi_btel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vbtel1, $tiket, 'validasi_btel', 'Selesai validator BT 1.');
        $this->simpanTahap($vsuel1, $tiket, 'validasi_suel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vsuel1, $tiket, 'validasi_suel', 'Selesai validator SU 1.');

        $this->addTiket($ambt1, $tiket, 'alih_media_btel');
        $this->addTiket($amsu1, $tiket, 'alih_media_suel');
        $this->simpanTahap($ambt1, $tiket, 'alih_media_btel', ['status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($ambt1, $tiket, 'alih_media_btel', 'Selesai alih media BT 1.');
        $this->simpanTahap($amsu1, $tiket, 'alih_media_suel', ['status_scan_surat_ukur' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($amsu1, $tiket, 'alih_media_suel', 'Selesai alih media SU 1.');

        // Warkah mencatat pengembalian berkas lalu Proses Selesai → tiket SELESAI.
        $this->catatPengembalian($warkah1, $tiket);
        $this->selesaiTahap($warkah1, $tiket, 'warkah', 'Selesai warkah 1 (berkas BT/SU sudah kembali).');

        $this->assertSame('selesai', $tiket->fresh()->status);
        $this->assertSame(1, $tiket->fresh()->revisi_ke);
        $this->assertSame('P1', $tiket->fresh()->status_pembetulan);
    }
    // ------------------------------------------------------------------
    // Gelombang 4 — T4 revisi internal + 3 kondisi error
    // ------------------------------------------------------------------

    public function test_gelombang_4_t4_revisi_internal_dan_tiga_kondisi_error(): void
    {
        $loket1 = $this->user('loket');
        $verif1 = $this->user('verifikator');
        $verif2 = $this->user('verifikator', 2);
        $warkah1 = $this->user('warkah');
        $vbtel1 = $this->user('validator_btel');
        $vbtel2 = $this->user('validator_btel', 2);
        $vsuel1 = $this->user('validator_suel');
        $ambt1 = $this->user('alih_media_btel');
        $amsu1 = $this->user('alih_media_suel');

        $tiket = $this->daftarkanTiket($loket1, 'DEMO/G4/T4', 'Pemohon Revisi Internal');

        // ERROR (a): Validator claim prematur — sebelum Warkah menandai diserahkan.
        $this->actingAs($vbtel2);
        $this->post(route('validator_btel.add', $tiket->id))
            ->assertSessionHas('error', 'Berkas DEMO/G4/T4 belum diserahkan oleh Warkah ke Validator (diserahkan_ke_validator belum aktif); belum dapat diproses di tahap validasi_btel.');

        // ERROR (b): Gate Alih Media terkunci — berkas belum diserahkan Warkah
        // (persyaratan pertama yang gagal: diserahkan_ke_validator belum aktif).
        $this->actingAs($ambt1);
        $this->post(route('alih_media_btel.add', $tiket->id))
            ->assertSessionHas('error', 'Berkas DEMO/G4/T4 belum diserahkan berkas BT/SU-nya oleh Warkah; belum dapat diproses di tahap alih_media_btel.')
            ->assertSessionMissing('success');

        // ERROR (c): Anti-duplikat — verifikator1 sudah Add, verifikator2 diblokir.
        $this->addTiket($verif1, $tiket, 'verifikasi');
        $this->actingAs($verif2);
        $this->post(route('verifikator.add', $tiket->id))
            ->assertSessionHas('error', 'Berkas DEMO/G4/T4 sedang diproses akun lain pada tahap ini.');

        // Lanjut: verifikasi selesai, warkah simpan + KIRIM berkas (gerbang Validator terbuka).
        $this->simpanTahap($verif1, $tiket, 'verifikasi', ['status_verifikasi' => 'lengkap']);
        $this->selesaiTahap($verif1, $tiket, 'verifikasi', 'Selesai verifikator 1.');
        $this->addTiket($warkah1, $tiket, 'warkah');
        $this->simpanTahap($warkah1, $tiket, 'warkah', $this->dataSimpanWarkah());
        $this->kirimBerkas($warkah1, $tiket);

        // Revisi INTERNAL: Validator BT mengembalikan ke Warkah.
        $this->addTiket($vbtel1, $tiket, 'validasi_btel');
        $this->simpanTahap($vbtel1, $tiket, 'validasi_btel', ['status_validasi' => 'ditolak', 'catatan' => 'Nomor sertipikat tidak cocok warkah.']);
        $this->actingAs($vbtel1);
        $this->post(route('validator_btel.revisi', $tiket->id), [
            'isi_revisi' => 'Nomor sertipikat pada validasi BT tidak sesuai data warkah; perbaiki data warkah.',
            'ke_stage' => 'warkah',
        ])->assertSessionHasNoErrors();

        $tiket->refresh();
        $this->assertSame('dikembalikan', $tiket->status);
        $this->assertSame(1, $tiket->revisi_ke);

        // Warkah melihat "Revisi Menunggu Saya" di index tahap.
        $this->actingAs($warkah1);
        $this->get(route('warkah.index'))->assertSee('Revisi Menunggu Saya')->assertSee('DEMO/G4/T4');

        // Warkah meng-Add ulang (dibuka oleh pending revisi), memperbaiki data warkah.
        $this->addTiket($warkah1, $tiket, 'warkah');
        $this->simpanTahap($warkah1, $tiket, 'warkah', $this->dataSimpanWarkah());

        // Validator BT meng-Add ulang setelah revisi internal, lulus.
        $this->addTiket($vbtel1, $tiket, 'validasi_btel');
        $this->simpanTahap($vbtel1, $tiket, 'validasi_btel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vbtel1, $tiket, 'validasi_btel', 'Selesai validator BT 1 (setelah revisi).');

        // Validator SU, Alih Media BT & SU.
        $this->addTiket($vsuel1, $tiket, 'validasi_suel');
        $this->simpanTahap($vsuel1, $tiket, 'validasi_suel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vsuel1, $tiket, 'validasi_suel', 'Selesai validator SU 1.');

        $this->addTiket($ambt1, $tiket, 'alih_media_btel');
        $this->addTiket($amsu1, $tiket, 'alih_media_suel');
        $this->simpanTahap($ambt1, $tiket, 'alih_media_btel', ['status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($ambt1, $tiket, 'alih_media_btel', 'Selesai alih media BT 1.');
        $this->simpanTahap($amsu1, $tiket, 'alih_media_suel', ['status_scan_surat_ukur' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($amsu1, $tiket, 'alih_media_suel', 'Selesai alih media SU 1.');

        // Warkah mencatat pengembalian lalu Proses Selesai → SELESAI total.
        $this->catatPengembalian($warkah1, $tiket);
        $this->selesaiTahap($warkah1, $tiket, 'warkah', 'Data warkah diperbaiki setelah revisi internal + berkas BT/SU dikembalikan.');

        $this->assertSame('selesai', $tiket->fresh()->status);
        $this->assertSame(1, $tiket->fresh()->revisi_ke);
        // Status pembetulan (P-code publik) TIDAK naik pada revisi internal —
        // hanya naik saat Loket menerima perbaikan dari pemohon (resubmit).
        $this->assertSame('P0', $tiket->fresh()->status_pembetulan);
    }
    // ------------------------------------------------------------------
    // Visibilitas catatan, privasi antrian, & timeline/tracking
    // ------------------------------------------------------------------

    public function test_visibilitas_catatan_privasi_antrian_dan_tracking(): void
    {
        $loket1 = $this->user('loket');
        $loket2 = $this->user('loket', 2);
        $verif1 = $this->user('verifikator');
        $warkah1 = $this->user('warkah');
        $vbtel1 = $this->user('validator_btel');
        $vsuel1 = $this->user('validator_suel');
        $ambt1 = $this->user('alih_media_btel');
        $amsu1 = $this->user('alih_media_suel');
        $admin = $this->user('admin');

        // Tiket T6 milik loket1; T7 milik loket2.
        $t6 = $this->daftarkanTiket($loket1, 'DEMO/V6/T6', 'Pemohon Visibilitas 6');
        $t7 = $this->daftarkanTiket($loket2, 'DEMO/V7/T7', 'Pemohon Visibilitas 7');

        // Bersihkan flash sukses registrasi agar tidak bocor ke halaman akun lain.
        $this->flushSession();

        // Privasi antrian per akun Loket.
        $this->actingAs($loket1);
        $this->get(route('loket.index'))->assertSee('DEMO/V6/T6')->assertDontSee('DEMO/V7/T7');
        $this->actingAs($loket2);
        $this->get(route('loket.index'))->assertSee('DEMO/V7/T7')->assertDontSee('DEMO/V6/T6');

        // Jalur paralel aktif untuk T6 — catatan lembar Warkah PRIVAT ke tahap Warkah.
        $this->addTiket($verif1, $t6, 'verifikasi');
        $this->addTiket($warkah1, $t6, 'warkah');
        $this->simpanTahap($warkah1, $t6, 'warkah', $this->dataSimpanWarkah() + [
            'catatan' => 'CATATAN_RAHASIA_WARKAH_12345',
        ]);
        $this->kirimBerkas($warkah1, $t6);

        $this->actingAs($warkah1);
        $this->get(route('warkah.show', $t6->id))->assertSee('CATATAN_RAHASIA_WARKAH_12345');

        $this->actingAs($verif1);
        $this->get(route('verifikator.show', $t6->id))->assertDontSee('CATATAN_RAHASIA_WARKAH_12345');

        // Timeline bersifat GLOBAL — terlihat di akun tahap lain.
        $this->actingAs($verif1);
        $this->get(route('verifikator.show', $t6->id))
            ->assertSee('Warkah menyerahkan berkas BT/SU ke Validator');

        // Catatan final (tiket_penugasan.catatan) hanya ditampilkan di Detail Admin.
        $this->selesaiTahap($verif1, $t6, 'verifikasi', 'CATATAN_FINAL_VERIFIKATOR_999');
        $this->actingAs($admin);
        $this->get(route('admin.show', $t6->id))->assertSee('CATATAN_FINAL_VERIFIKATOR_999');

        // Selesaikan T6 penuh agar tracking dapat menampilkan status selesai + timeline.
        $this->addTiket($vbtel1, $t6, 'validasi_btel');
        $this->addTiket($vsuel1, $t6, 'validasi_suel');
        $this->simpanTahap($vbtel1, $t6, 'validasi_btel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vbtel1, $t6, 'validasi_btel', 'Selesai validator BT 1.');
        $this->simpanTahap($vsuel1, $t6, 'validasi_suel', ['status_validasi' => 'lulus']);
        $this->selesaiTahap($vsuel1, $t6, 'validasi_suel', 'Selesai validator SU 1.');
        $this->addTiket($ambt1, $t6, 'alih_media_btel');
        $this->addTiket($amsu1, $t6, 'alih_media_suel');
        $this->simpanTahap($ambt1, $t6, 'alih_media_btel', ['status_scan_buku_tanah' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($ambt1, $t6, 'alih_media_btel', 'Selesai alih media BT 1.');
        $this->simpanTahap($amsu1, $t6, 'alih_media_suel', ['status_scan_surat_ukur' => 'sudah', 'status_upload_kkp' => 'sudah', 'status_ttd_elektronik' => 'sudah']);
        $this->selesaiTahap($amsu1, $t6, 'alih_media_suel', 'Selesai alih media SU 1.');

        // Warkah mencatat pengembalian lalu Proses Selesai → T6 SELESAI.
        $this->catatPengembalian($warkah1, $t6);
        $this->selesaiTahap($warkah1, $t6, 'warkah', 'Selesai warkah 1.');

        $this->assertSame('selesai', $t6->fresh()->status);

        // Tracking publik (tanpa login) menampilkan status, timeline, dan riwayat.
        $this->get(route('tracking.show', $t6->kode_tiket))
            ->assertSee('DEMO/V6/T6')
            ->assertSee('Selesai (Sertifikat El. Terbit)')
            ->assertSee('Berkas terdaftar di Loket')
            ->assertSee('Warkah menyerahkan berkas BT/SU ke Validator');

        // Catatan revisi model terisi → dasar tabele menu Admin Revisi & alert Loket.
        $this->assertDatabaseHas('tiket_penugasan', [
            'tiket_id' => $t6->id,
            'stage' => 'verifikasi',
            'status' => 'selesai',
            'catatan' => 'CATATAN_FINAL_VERIFIKATOR_999',
        ]);
    }
}
