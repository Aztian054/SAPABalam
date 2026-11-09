<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Menu sidebar berbasis database (Blueprint V2 — Super Admin).
 *
 * Satu baris = satu tautan menu milik satu role (`role_owner`).
 * Super Admin melihat seluruh baris, dikelompokkan per `role_owner`;
 * pengguna biasa hanya melihat baris milik rolenya sendiri.
 *
 * @property int $id
 * @property string $label
 * @property string $route
 * @property string|null $icon
 * @property string $role_owner
 * @property int $order_position
 * @property bool $is_active
 * @property bool $butuh_demo
 * @property array<int, string>|null $active_patterns
 */
class Menu extends Model
{
    protected $fillable = [
        'label',
        'route',
        'icon',
        'role_owner',
        'order_position',
        'is_active',
        'butuh_demo',
        'active_patterns',
    ];

    protected function casts(): array
    {
        return [
            'order_position' => 'integer',
            'is_active' => 'boolean',
            'butuh_demo' => 'boolean',
            'active_patterns' => 'array',
        ];
    }

    /** @param  Builder<Menu>  $query */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Menu menyala bila route saat ini cocok dengan pola miliknya.
     * Pola kosong = pakai nama route itu sendiri.
     */
    public function sedangAktif(): bool
    {
        $pola = $this->active_patterns ?: [$this->route];

        return request()->routeIs(...$pola);
    }
}
