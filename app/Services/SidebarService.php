<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Penyusun menu sidebar dari tabel `menus`.
 *
 * - Pengguna biasa: hanya menu milik rolenya sendiri.
 * - Super Admin: seluruh menu, dikelompokkan per `role_owner` (role
 *   pemilik menu) sehingga role baru otomatis tampil tanpa mengubah Blade.
 */
class SidebarService
{
    /**
     * @return Collection<int, array{role: string, label: string, is_self: bool, menus: Collection<int, Menu>}>
     */
    public function groupsFor(User $user): Collection
    {
        $semuaMenu = Menu::query()
            ->aktif()
            ->orderBy('order_position')
            ->orderBy('id')
            ->get()
            ->filter(fn (Menu $menu): bool => Route::has($menu->route))
            ->filter(fn (Menu $menu): bool => ! $menu->butuh_demo || app(DemoService::class)->aktif())
            ->values();

        if (! $user->isSuperAdmin()) {
            $milikSendiri = $semuaMenu
                ->where('role_owner', $user->role)
                ->values();

            if ($milikSendiri->isEmpty()) {
                return collect();
            }

            return collect([$this->buatGrup($user->role, $milikSendiri, true)]);
        }

        $urutanJabatan = array_flip(array_keys(User::ROLES));

        return $semuaMenu
            ->groupBy('role_owner')
            ->sortBy(fn (Collection $menus, string $role): int => $urutanJabatan[$role] ?? PHP_INT_MAX)
            ->map(fn (Collection $menus, string $role): array => $this->buatGrup($role, $menus->values(), $role === $user->role))
            ->values();
    }

    /**
     * @param  Collection<int, Menu>  $menus
     * @return array{role: string, label: string, is_self: bool, menus: Collection<int, Menu>}
     */
    private function buatGrup(string $role, Collection $menus, bool $milikSendiri): array
    {
        return [
            'role' => $role,
            'label' => User::ROLES[$role] ?? Str::headline($role),
            'is_self' => $milikSendiri,
            'menus' => $menus,
        ];
    }
}
