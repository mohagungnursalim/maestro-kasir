<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
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
        if (Schema::hasTable('store_settings')) {
            $settings = StoreSetting::first();
            View::share('settings', $settings);
        }
    }
}
