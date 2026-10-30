<?php

namespace App\Providers;

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
        // Di produksi (di belakang proxy HTTPS), paksa URL memakai https
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Nama hari, bulan, dan "x menit yang lalu" ditampilkan dalam bahasa Indonesia
        \Carbon\Carbon::setLocale('id');

        // Tombol halaman (pagination) memakai markup Bootstrap 5,
        // karena seluruh tampilan RESQNET memakai Bootstrap, bukan Tailwind.
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}
