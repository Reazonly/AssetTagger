<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Memaksa URL hanya saat aplikasi berjalan di mode 'local' (development)
        // dan memastikan APP_URL di .env sudah diisi dengan benar.
        if ($this->app->environment('local') && config('app.url') !== 'http://localhost') {
            
            // Ambil URL dari file .env MENGGUNAKAN KUNCI 'app.url'
            $ngrokUrl = config('app.url');
            
            // Paksa semua URL yang dibuat oleh Laravel untuk menggunakan URL ini
            URL::forceRootUrl($ngrokUrl);
        }
    }
}
