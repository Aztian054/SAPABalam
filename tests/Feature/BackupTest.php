<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Backup basis data (Super Admin).
 *
 * Dump dibuat aplikasi sendiri sehingga dapat diuji tanpa binary eksternal;
 * setiap unduhan wajib tercatat di Log.
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'super_admin'): User
    {
        return User::factory()->create([
            'name' => ucwords(str_replace('_', ' ', $role)).' User',
            'username' => $role.'_'.fake()->unique()->numberBetween(1, 9999),
            'email' => fake()->unique()->safeEmail(),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_admin_biasa_tidak_dapat_membuka_atau_mengunduh_backup(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('super-admin.backup.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('super-admin.backup.unduh'))->assertForbidden();
    }

    public function test_pengguna_biasa_tidak_dapat_membuka_backup(): void
    {
        $loket = $this->user('loket_pendaftaran');

        $this->actingAs($loket)->get(route('super-admin.backup.index'))->assertForbidden();
    }

    public function test_super_admin_dapat_membuka_halaman_backup(): void
    {
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.backup.index'))
            ->assertOk()
            ->assertSee('Backup Basis Data')
            ->assertSee('Unduh Backup (.sql)')
            ->assertSee('loket-');
    }

    public function test_unduhan_berupa_dump_sql_berisi_struktur_dan_data(): void
    {
        $superAdmin = $this->user();

        $response = $this->actingAs($superAdmin)->post(route('super-admin.backup.unduh'));

        $response->assertOk();
        $this->assertStringContainsString('attachment; filename=', (string) $response->headers->get('content-disposition'));
        $this->assertMatchesRegularExpression('/loket-\d{8}-\d{4}\.sql/', (string) $response->headers->get('content-disposition'));

        $sql = $response->streamedContent();

        $this->assertStringContainsString('CREATE TABLE', $sql);
        $this->assertStringContainsString('INSERT INTO', $sql);
        $this->assertStringContainsString('-- Tabel: users', $sql);
        $this->assertStringContainsString($superAdmin->username, $sql);
    }

    public function test_unduhan_backup_super_admin_tidak_tercatat_di_log(): void
    {
        $superAdmin = $this->user();

        $this->actingAs($superAdmin)->post(route('super-admin.backup.unduh'));

        // Super Admin bergerak tanpa jejak, termasuk saat mengunduh backup.
        $this->assertDatabaseMissing('log_aktivitas', ['aksi' => 'UNDUH BACKUP']);
    }

    public function test_nama_file_backup_mengikuti_format_loket_tanggal_jam(): void
    {
        $superAdmin = $this->user();

        $response = $this->actingAs($superAdmin)->post(route('super-admin.backup.unduh'));

        $nama = str_replace(['"', 'attachment; filename='], '', (string) $response->headers->get('content-disposition'));

        $this->assertMatchesRegularExpression('/^loket-\d{8}-\d{4}\.sql$/', trim($nama));
    }
}
