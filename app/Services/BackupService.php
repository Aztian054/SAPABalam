<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Pembuat dump SQL portabel untuk fitur Backup Super Admin.
 *
 * Mendukung MySQL/MariaDB dan SQLite tanpa bergantung pada binary
 * `mysqldump`, sehingga tetap berjalan di Windows/Laragon maupun server.
 * Hasil berupa file .sql untuk diunduh dan dipulihkan manual oleh operator.
 */
class BackupService
{
    public function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /** @return array<int, string> */
    public function daftarTabel(): array
    {
        return match ($this->driver()) {
            'sqlite' => collect(DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name"))
                ->pluck('name')
                ->all(),
            'mysql', 'mariadb' => collect(DB::select('SHOW TABLES'))
                ->map(fn (object $baris): string => (string) array_values((array) $baris)[0])
                ->all(),
            default => Schema::getTableListing(),
        };
    }

    public function namaFile(?Carbon $waktu = null): string
    {
        return 'sapa-'.($waktu ?? now())->format('Ymd-Hi').'.sql';
    }

    /**
     * Tulis dump SQL baris demi baris agar hemat memori.
     *
     * @param  callable(string): void  $tulis
     */
    public function tulisKe(callable $tulis): void
    {
        $tulis("-- Backup SAPA Balam\n");
        $tulis('-- Dibuat: '.now()->format('Y-m-d H:i:s')."\n");
        $tulis('-- Driver: '.$this->driver()."\n\n");

        if ($this->driver() === 'mysql' || $this->driver() === 'mariadb') {
            $tulis("SET FOREIGN_KEY_CHECKS=0;\n\n");
        }

        foreach ($this->daftarTabel() as $tabel) {
            $tulis("-- --------------------------------------------------\n");
            $tulis("-- Tabel: {$tabel}\n");
            $tulis("-- --------------------------------------------------\n");
            $tulis($this->sqlBuatTabel($tabel)."\n\n");
            $this->tulisBaris($tabel, $tulis);
            $tulis("\n");
        }

        if ($this->driver() === 'mysql' || $this->driver() === 'mariadb') {
            $tulis("SET FOREIGN_KEY_CHECKS=1;\n");
        }
    }

    private function sqlBuatTabel(string $tabel): string
    {
        $sql = match ($this->driver()) {
            'sqlite' => DB::selectOne(
                "select sql from sqlite_master where type = 'table' and name = ?",
                [$tabel],
            )?->sql,
            'mysql', 'mariadb' => array_values((array) DB::selectOne('SHOW CREATE TABLE '.$this->kutip($tabel)))[1] ?? null,
            default => null,
        };

        if (! is_string($sql) || trim($sql) === '') {
            throw new RuntimeException("Struktur tabel {$tabel} tidak dapat dibaca untuk backup.");
        }

        return rtrim(trim($sql), ';').';';
    }

    /** @param  callable(string): void  $tulis */
    private function tulisBaris(string $tabel, callable $tulis): void
    {
        $kolom = Schema::getColumnListing($tabel);

        if ($kolom === []) {
            return;
        }

        $daftarKolom = implode(', ', array_map(fn (string $k): string => $this->kutip($k), $kolom));

        foreach (DB::table($tabel)->cursor() as $baris) {
            $data = (array) $baris;
            $nilai = array_map(fn (string $k): string => $this->nilaiSql($data[$k] ?? null), $kolom);

            $tulis('INSERT INTO '.$this->kutip($tabel)." ({$daftarKolom}) VALUES (".implode(', ', $nilai).");\n");
        }
    }

    private function nilaiSql(mixed $nilai): string
    {
        return match (true) {
            $nilai === null => 'NULL',
            is_bool($nilai) => $nilai ? '1' : '0',
            is_int($nilai), is_float($nilai) => (string) $nilai,
            $nilai instanceof \DateTimeInterface => $this->kutipNilai($nilai->format('Y-m-d H:i:s')),
            is_array($nilai) => $this->kutipNilai((string) json_encode($nilai)),
            default => $this->kutipNilai((string) $nilai),
        };
    }

    private function kutipNilai(string $nilai): string
    {
        return DB::connection()->getPdo()->quote($nilai);
    }

    private function kutip(string $identifier): string
    {
        if ($this->driver() === 'sqlite') {
            return '"'.str_replace('"', '""', $identifier).'"';
        }

        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
