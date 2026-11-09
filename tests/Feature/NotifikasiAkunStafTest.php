<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Kasi Seksi menerima notifikasi in-app atas perubahan akun staf di seksinya. */
class NotifikasiAkunStafTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => $role.' '.str()->random(5),
            'username' => $role.'_'.str()->random(8),
            'email' => $role.'_'.str()->random(8).'@example.test',
            'role' => $role,
            'is_active' => true,
        ], $atribut));
    }

    public function test_kasi_mendapat_notifikasi_saat_staf_baru_ditambahkan(): void
    {
        $admin = $this->user('admin');
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Staf Baru',
            'username' => 'stafbaru',
            'email' => 'stafbaru@example.test',
            'password' => 'rahasia123',
            'role' => 'staf_seksi',
            'seksi' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasi->id,
            'tipe' => 'akun.staf_baru',
        ]);
    }

    public function test_kasi_mendapat_notifikasi_saat_staf_dinonaktifkan(): void
    {
        $admin = $this->user('admin');
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);
        $staf = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $staf->id))
            ->assertRedirect();

        $this->assertFalse($staf->fresh()->is_active);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasi->id,
            'tipe' => 'akun.staf_nonaktif',
        ]);
    }

    public function test_kasi_lama_dan_kasi_baru_mendapat_notifikasi_saat_staf_pindah_seksi(): void
    {
        $admin = $this->user('admin');
        $kasiSatu = $this->user('kasi_seksi', ['seksi' => '1']);
        $kasiDua = $this->user('kasi_seksi', ['seksi' => '2']);
        $staf = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($admin)->put(route('admin.users.update', $staf->id), [
            'name' => $staf->name,
            'username' => $staf->username,
            'email' => $staf->email,
            'seksi' => '2',
        ])->assertRedirect();

        $this->assertSame('2', $staf->fresh()->seksi);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasiSatu->id,
            'tipe' => 'akun.staf_pindah_seksi',
        ]);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasiDua->id,
            'tipe' => 'akun.staf_pindah_seksi',
        ]);
    }

    public function test_kasi_mendapat_notifikasi_saat_staf_dihapus(): void
    {
        $admin = $this->user('admin');
        $kasi = $this->user('kasi_seksi', ['seksi' => '1']);
        $staf = $this->user('staf_seksi', ['seksi' => '1']);

        $this->actingAs($admin)
            ->post(route('admin.users.hapus', $staf->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $staf->id]);
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $kasi->id,
            'tipe' => 'akun.staf_dihapus',
        ]);
    }
}
