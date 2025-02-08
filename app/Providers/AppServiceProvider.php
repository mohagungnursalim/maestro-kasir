<?php

namespace App\Providers;

use App\Models\StoreSetting;
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
        $settings = StoreSetting::first(); // Ambil pengaturan aplikasi pertama

        // Share pengaturan ke seluruh tampilan
        View::share('settings', $settings);
    }
}
