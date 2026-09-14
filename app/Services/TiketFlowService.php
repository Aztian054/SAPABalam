<?php

namespace App\Services;

use App\Models\CatatanRevisi;
use App\Models\RiwayatStatus;
use App\Models\Tiket;
use App\Models\TiketPenugasan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * TiketFlowService — inti pola kerja V2.0:
 *   Smart Search → Add (Ambil dari DB Admin) → Proses → Selesai → Kembali ke DB Admin.
 *
 * Semua transisi status/penugasan dilakukan melalui service ini agar logika
 * anti-duplikat dan gate antar-tahap konsisten di seluruh controller.
 */
class TiketFlowService
{
    /** Pemetaan role petugas ke kode tahap. */
    public const ROLE_STAGE = [
        'verifikator' => 'verifikasi',
        'warkah' => 'warkah',
        'validator_btel' => 'validasi_btel',
        'validator_suel' => 'validasi_suel',
        'alih_media_btel' => 'alih_media_btel',
        'alih_media_suel' => 'alih_media_suel',
    ];

    /**
     * Tahap prasyarat yang WAJIB selesai sebelum sebuah tahap boleh meng-Add tiket.
     *
     * Konfigurasi Alur Paralel (dokumen Alur_00 + PRD 2.2):
     *  - Verifikator ║ Warkah TIDAK saling menunggu (warkah berkas []).
     *  - Validator BT/SU dibuka oleh flag `diserahkan_ke_validator` dari Warkah
     *    (dicek terpisah di assertCanClaim) — tanpa menunggu Verifikator.
     *  - Alih Media BT/SU membutuhkan SEMUA tahap selesai (gate AND penuh).
     */
    public const STAGE_GATES = [
        'verifikasi' => [], // tiket dari Loket; syarat: status diterima (belum pernah diverifikasi)
        'warkah' => [], // paralel dengan Verifikator — tidak saling menunggu
        'validasi_btel' => [], // dibuka flag diserahkan_ke_validator (custom)
        'validasi_suel' => [], // dibuka flag diserahkan_ke_validator (custom)
        'alih_media_btel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
        'alih_media_suel' => ['verifikasi', 'warkah', 'validasi_btel', 'validasi_suel'],
    ];

    /**
     * Gate TAMPILAN untuk Smart Search (dokumen Final-Rizki: Alih Media tetap
     * MELIHAT tiket Validator yang belum selesai — ditandai "terkunci/locked",
     * sedangkan tombol Add tetap diblokir sampai gate sesungguhnya terpenuhi).
     */
    public const STAGE_GATES_VISIBLE = [
        'verifikasi' => [],
        'warkah' => [], // paralel: langsung terlihat begitu masuk DB Admin
        'validasi_btel' => [], // khusus: filter diserahkan_ke_validator = true
        'validasi_suel' => [], // khusus: filter diserahkan_ke_validator = true
        'alih_media_btel' => ['verifikasi', 'warkah'],
        'alih_media_suel' => ['verifikasi', 'warkah'],
    ];

    /** Kode status tiket yang merepresentasikan tahap tertentu saat sedang di-Add. */
    public const STAGE_STATUS = [
        'verifikasi' => 'verifikasi',
        'warkah' => 'warkah',
        'validasi_btel' => 'validasi_btel',
        'validasi_suel' => 'validasi_suel',
        'alih_media_btel' => 'alih_media_btel',
        'alih_media_suel' => 'alih_media_suel',
    ];

    /**
     * Daftar tiket yang tersedia untuk di-Add oleh sebuah tahap.
     * Anti-duplikat: tiket yang sedang diproses akun lain pada tahap ini TIDAK muncul.
     */
    public function availableTikets(string $stage, ?string $search = null): Builder
    {
        $query = Tiket::query()
            ->with(['jenisPermohonan', 'bidangTanahs'])
            ->whereNotIn('status', ['selesai', 'batal', 'dikembalikan']);

        // Gate: semua tahap prasyarat (yang memengaruhi TAMPILAN) harus memiliki penugasan selesai.
        $visibleGates = self::STAGE_GATES_VISIBLE[$stage] ?? self::STAGE_GATES[$stage];
        foreach ($visibleGates as $needStage) {
            $doneTiketIds = TiketPenugasan::where('stage', $needStage)
                ->where('status', 'selesai')
                ->distinct()
                ->pluck('tiket_id');

            if ($doneTiketIds->isEmpty()) {
                return $query->whereRaw('1 = 0'); // prasyarat belum ada sama sekali
            }

            $query->whereIn('id', $doneTiketIds);
        }

        // Validator BT/SU khusus: hanya tiket yang telah Warkah SERAHKAN
        // (diserahkan_ke_validator = true) — tanpa menunggu Verifikator.
        if (in_array($stage, ['validasi_btel', 'validasi_suel'], true)) {
            $query->where('diserahkan_ke_validator', true);
        }

        // Tahap yang sudah pernah selesai pada tiket ini tidak di-Add ulang,
        // kecuali ada revisi yang masih menunggu tahap yang sama (ke_stage).
        $pendingRevisiIds = CatatanRevisi::where('ke_stage', $stage)
            ->where('sudah_diproses', false)
            ->pluck('tiket_id');

        $doneStageIds = TiketPenugasan::where('stage', $stage)
            ->where('status', 'selesai')
            ->pluck('tiket_id');

        $alreadyDoneWithoutRevisi = $doneStageIds->diff($pendingRevisiIds);
        if ($alreadyDoneWithoutRevisi->isNotEmpty()) {
            $query->whereNotIn('id', $alreadyDoneWithoutRevisi);
        }

        // Anti-duplikat: tiket yang sedang diproses di tahap ini tidak boleh di-Add lagi.
        $claimedIds = TiketPenugasan::where('stage', $stage)
            ->where('status', 'proses')
            ->pluck('tiket_id');
        $query->whereNotIn('id', $claimedIds);

        // Pencarian cepat (kode tiket / nama pemohon / NIK / no HP).
        if ($search && trim($search) !== '') {
            $s = trim($search);
            $query->where(function (Builder $q) use ($s) {
                $q->where('kode_tiket', 'like', "%{$s}%")
                    ->orWhere('nama_pemohon', 'like', "%{$s}%")
                    ->orWhere('nik_pemohon', 'like', "%{$s}%")
                    ->orWhere('nomor_telepon', 'like', "%{$s}%");
            });
        }

        return $query->orderByDesc('tanggal_masuk')->orderByDesc('id');
    }

    /**
     * Tiket yang sedang aktif (sudah di-Add) oleh user saat ini pada role/tahap-nya.
     */
    public function activeTikets(User $user): Builder
    {
        $stage = self::ROLE_STAGE[$user->role] ?? null;

        if ($stage === null) {
            return Tiket::query()->whereRaw('1 = 0');
        }

        return Tiket::whereHas('penugasans', function (Builder $q) use ($stage, $user) {
            $q->where('stage', $stage)
                ->where('user_id', $user->id)
                ->where('status', 'proses');
        })->with(['jenisPermohonan', 'bidangTanahs'])->latest();
    }

    /**
     * Tiket yang pernah diproses user (semua penugasan user).
     */
    public function historyTikets(User $user): Builder
    {
        $stage = self::ROLE_STAGE[$user->role] ?? null;

        if ($stage === null) {
            return Tiket::query()->whereRaw('1 = 0');
        }

        return Tiket::whereHas('penugasans', function (Builder $q) use ($stage, $user) {
            $q->where('stage', $stage)->where('user_id', $user->id);
        })->with(['jenisPermohonan', 'bidangTanahs'])->latest();
    }

    /**
     * Tiket revisi yang MENUNGGU akun user (menu "Revisi" per akun — dokumen Final-Rizki).
     * Revisi dicatat dengan ke_stage = tahap yang harus memperbaiki; ticket tetap
     * berstatus 'dikembalikan' sampai diperbaiki via flow pull-based (Search → Add).
     */
    public function revisiForMe(User $user): Builder
    {
        $stage = self::ROLE_STAGE[$user->role] ?? null;

        if ($stage === null) {
            return Tiket::query()->whereRaw('1 = 0');
        }

        return Tiket::where('status', 'dikembalikan')
            ->whereHas('catatanRevisis', function (Builder $q) use ($stage) {
                $q->where('sudah_diproses', false)
                    ->where('ke_stage', $stage);
            })
            ->with(['jenisPermohonan', 'bidangTanahs', 'catatanRevisis'])
            ->latest();
    }

    /**
     * Ambil (Add) tiket dari Database Admin sampai ke antrian kerja user.
     *
     * @throws \RuntimeException bila gagal melewati gate / anti-duplikat.
     */
    public function claim(User $user, Tiket $tiket): TiketPenugasan
    {
        $stage = $this->stageForUser($user);

        return DB::transaction(function () use ($user, $tiket, $stage) {
            // 1. Gate prasyarat.
            $this->assertCanClaim($tiket, $stage);

            // 2. Anti-duplikat.
            $existing = $tiket->penugasanAktif($stage);
            if ($existing) {
                throw new \RuntimeException("Tiket {$tiket->kode_tiket} sedang diproses akun lain pada tahap ini.");
            }

            // 3. Buat penugasan baru.
            $penugasan = TiketPenugasan::create([
                'tiket_id' => $tiket->id,
                'user_id' => $user->id,
                'stage' => $stage,
                'status' => 'proses',
                'tanggal_add' => now(),
            ]);

            // 4. Perbarui status tiket ke tahap yang bersangkutan.
            $update = ['status' => self::STAGE_STATUS[$stage]];
            if ($stage === 'validasi_btel') {
                $update['status_pra_btel'] = 'proses';
            }
            if ($stage === 'validasi_suel') {
                $update['status_pra_suel'] = 'proses';
            }
            $tiket->update($update);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'DB Admin',
                'stage_ke' => $stage,
                'changed_by' => $user->id,
                'keterangan' => "Tiket di-Add oleh {$user->name} (tahap {$stage}).",
            ]);

            return $penugasan;
        });
    }

    /**
     * Selesai memproses — tiket dikembalikan ke DB Admin (atau ditandai selesai total).
     */
    public function done(User $user, Tiket $tiket, ?string $catatan = null): void
    {
        $stage = $this->stageForUser($user);

        DB::transaction(function () use ($user, $tiket, $stage, $catatan) {
            $penugasan = $tiket->penugasanAktif($stage);

            if (! $penugasan) {
                throw new \RuntimeException("Tiket {$tiket->kode_tiket} tidak sedang dalam antrian Anda.");
            }
            if ($penugasan->user_id !== $user->id) {
                throw new \RuntimeException('Anda tidak berhak menyelesaikan penugasan milik akun lain.');
            }

            $penugasan->update([
                'status' => 'selesai',
                'tanggal_selesai' => now(),
                'catatan' => $catatan,
            ]);

            $this->resolveStatusAfterDone($tiket, $stage);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => $stage,
                'stage_ke' => $tiket->status === 'selesai' ? 'selesai' : 'DB Admin',
                'changed_by' => $user->id,
                'keterangan' => "Tiket {$tiket->kode_tiket} selesai pada tahap {$stage}.".($catatan ? " Catatan: {$catatan}" : ''),
            ]);
        });
    }

    /**
     * Kirim tiket kembali untuk revisi (dikembalikan) → loket tahap terkait.
     */
    public function returnForRevisi(User $user, Tiket $tiket, string $isiRevisi, string $keStage = 'admin', ?int $penerimaId = null): void
    {
        $stage = $this->stageForUser($user);

        DB::transaction(function () use ($user, $tiket, $stage, $isiRevisi, $keStage, $penerimaId) {
            $penugasan = $tiket->penugasanAktif($stage);

            if (! $penugasan) {
                throw new \RuntimeException("Tiket {$tiket->kode_tiket} tidak sedang dalam antrian Anda.");
            }
            if ($penugasan->user_id !== $user->id) {
                throw new \RuntimeException('Anda tidak berhak mengembalikan penugasan milik akun lain.');
            }

            $revisiKe = ($tiket->revisi_ke ?? 0) + 1;

            CatatanRevisi::create([
                'tiket_id' => $tiket->id,
                'pengirim_id' => $user->id,
                'penerima_id' => $penerimaId,
                'dari_stage' => $stage,
                'ke_stage' => $keStage,
                'revisi_ke' => $revisiKe,
                'isi_revisi' => $isiRevisi,
                'sudah_diproses' => false,
                'tanggal_masuk' => now(),
            ]);

            $penugasan->update(['status' => 'dikembalikan']);

            $tiket->update([
                'status' => 'dikembalikan',
                'revisi_ke' => $revisiKe,
                'keterangan' => $isiRevisi,
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => $stage,
                'stage_ke' => 'dikembalikan',
                'changed_by' => $user->id,
                'keterangan' => "Tiket dikembalikan untuk revisi ke-{$revisiKe}: {$isiRevisi}",
            ]);
        });
    }

    /**
     * Batalkan tiket karena suatu alasan.
     */
    public function cancel(User $user, Tiket $tiket, string $alasan): void
    {
        DB::transaction(function () use ($user, $tiket, $alasan) {
            $oldStatus = $tiket->status;
            $tiket->penugasans()->where('status', 'proses')->update(['status' => 'batal']);
            $tiket->update([
                'status' => 'batal',
                'keterangan' => $alasan,
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => $oldStatus,
                'stage_ke' => 'batal',
                'changed_by' => $user->id,
                'keterangan' => 'Permohonan dibatalkan. Alasan: '.$alasan,
            ]);
        });
    }

    /** Penerimaan perbaikan revisi dari Loket → tiket kembali ke tahap asal revisi. */
    public function resubmitAfterRevisi(User $user, Tiket $tiket, string $catatan = ''): void
    {
        $revisi = $tiket->catatanRevisis()->where('sudah_diproses', false)->latest('id')->first();

        DB::transaction(function () use ($user, $tiket, $revisi, $catatan) {
            $tujuan = 'verifikasi';
            if ($revisi && isset(self::STAGE_STATUS[$revisi->dari_stage])) {
                $tujuan = $revisi->dari_stage;
            }

            if ($revisi) {
                $revisi->update(['sudah_diproses' => true, 'tanggal_selesai' => now(), 'penerima_id' => $user->id]);
            }

            $tiket->update([
                'status' => self::STAGE_STATUS[$tujuan],
                'status_pembetulan' => $this->nextPembetulan($tiket->status_pembetulan),
            ]);

            RiwayatStatus::create([
                'tiket_id' => $tiket->id,
                'stage_dari' => 'Loket (Revisi diperbaiki)',
                'stage_ke' => $tujuan,
                'changed_by' => $user->id,
                'keterangan' => 'Perbaikan revisi diterima dari pemohon.'.($catatan ? " Catatan: {$catatan}" : ''),
            ]);
        });
    }

    public function stageForUser(User $user): string
    {
        if (! isset(self::ROLE_STAGE[$user->role])) {
            throw new \RuntimeException('Role saat ini tidak memiliki tahap kerja.');
        }

        return self::ROLE_STAGE[$user->role];
    }

    protected function nextPembetulan(?string $current): string
    {
        // Dokumen Final-Rizki: kode revisi P1, P2, P3, … TIDAK terbatas.
        $n = (int) str_replace('P', '', $current ?? 'P0');

        return 'P'.($n + 1);
    }

    /**
     * Validasi gate + anti-duplikat untuk penambahan tiket.
     */
    protected function assertCanClaim(Tiket $tiket, string $stage): void
    {
        if (in_array($tiket->status, ['selesai', 'batal'], true)) {
            throw new \RuntimeException('Tiket sudah selesai/dibatalkan dan tidak dapat diambil.');
        }

        // Validator BT/SU dibuka oleh flag diserahkan_ke_validator dari Warkah
        // (paralel, TANPA menunggu Verifikator).
        if (in_array($stage, ['validasi_btel', 'validasi_suel'], true) && ! $tiket->isDiserahkanKeValidator()) {
            throw new \RuntimeException(
                "Tiket {$tiket->kode_tiket} belum diserahkan oleh Warkah ke Validator (diserahkan_ke_validator belum aktif); belum dapat diproses di tahap {$stage}."
            );
        }

        // Tahap yang sudah selesai tidak boleh diproses ulang, kecuali ada revisi
        // yang masih menunggu tahap yang sama (ke_stage = tahap ini).
        if ($stage !== 'verifikasi' && $tiket->isStageSelesai($stage) && ! $this->hasPendingRevisiForStage($tiket, $stage)) {
            throw new \RuntimeException("Tiket {$tiket->kode_tiket} sudah selesai pada tahap {$stage} dan tidak dapat di-Add ulang.");
        }

        foreach (self::STAGE_GATES[$stage] as $needStage) {
            if (! $tiket->isStageSelesai($needStage)) {
                throw new \RuntimeException("Tiket {$tiket->kode_tiket} belum selesai pada tahap {$needStage}; belum dapat diproses di tahap {$stage}.");
            }
        }

        if ($stage === 'verifikasi' && $tiket->isStageSelesai('verifikasi') && ! $this->hasPendingRevisiForStage($tiket, 'verifikasi')) {
            throw new \RuntimeException("Tiket {$tiket->kode_tiket} sudah pernah diverifikasi.");
        }
    }

    /** Apakah ada revisi yang masih menunggu perbaikan pada tahap tertentu. */
    protected function hasPendingRevisiForStage(Tiket $tiket, string $stage): bool
    {
        return $tiket->catatanRevisis()
            ->where('ke_stage', $stage)
            ->where('sudah_diproses', false)
            ->exists();
    }

    /**
     * Status tiket yang mewakili tahap yang masih aktif (penugasan berstatus proses).
     * Digunakan agar penyelesaian satu tahap paralel tidak menimpa status tahap lain
     * yang masih dikerjakan (mis. Verifikator selesai saat Warkah/Validator masih proses).
     */
    protected function activeStageStatus(Tiket $tiket): ?string
    {
        foreach (self::STAGE_STATUS as $stage => $status) {
            if ($tiket->penugasanAktif($stage)) {
                return $status;
            }
        }

        return null;
    }

    /**
     * Atur status tiket setelah sebuah tahap dinyatakan selesai.
     * Alur paralel: status menyesuaikan tahap paralel yang masih aktif,
     * atau kembali ke DB Admin, atau Selesai total saat BT & SU beres.
     */
    protected function resolveStatusAfterDone(Tiket $tiket, string $stage): void
    {
        $update = [];
        $next = $this->activeStageStatus($tiket);

        if (in_array($stage, ['verifikasi', 'warkah'], true)) {
            // Paralel: jangan timpa status tahap lain yang masih diproses.
            $update['status'] = $next ?? 'diterima';
        } elseif ($stage === 'validasi_btel') {
            $update['status_pra_btel'] = 'selesai';
            $update['status'] = $next ?? 'diterima';
        } elseif ($stage === 'validasi_suel') {
            $update['status_pra_suel'] = 'selesai';
            $update['status'] = $next ?? 'diterima';
        } elseif ($stage === 'alih_media_btel') {
            $update['status'] = $next ?? ($tiket->isStageSelesai('alih_media_suel') ? 'selesai' : 'diterima');
            $update['tanggal_selesai'] = $update['status'] === 'selesai' ? now()->toDateString() : $tiket->tanggal_selesai;
        } elseif ($stage === 'alih_media_suel') {
            $update['status'] = $next ?? ($tiket->isStageSelesai('alih_media_btel') ? 'selesai' : 'diterima');
            $update['tanggal_selesai'] = $update['status'] === 'selesai' ? now()->toDateString() : $tiket->tanggal_selesai;
        }

        $tiket->update($update);
    }
}
