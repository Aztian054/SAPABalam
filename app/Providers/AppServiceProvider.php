<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Jumlah notifikasi belum dibaca disiapkan saat render agar lonceng
        // langsung benar sebelum polling JavaScript pertama selesai.
        View::composer('layouts.app', function ($view): void {
            $pengguna = auth()->user();

            $view->with(
                'jumlahNotifikasi',
                $pengguna === null
                    ? 0
                    : Notifikasi::untukUser($pengguna)->belumDibaca()->count(),
            );
        });
    }
}
