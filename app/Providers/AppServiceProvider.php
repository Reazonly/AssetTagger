<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon; // <-- 1. Import library Carbon

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
        // Logika untuk URL ngrok (jika diperlukan)
        if ($this->app->environment('local') && config('app.url') !== 'http://localhost') {
            URL::forceRootUrl(config('app.url'));
        }
        
        // Menggunakan style paginasi dari Tailwind CSS
        Paginator::useTailwind();

        // 2. Mengatur bahasa default untuk semua format tanggal ke Bahasa Indonesia
        Carbon::setLocale('id');
    }
}
