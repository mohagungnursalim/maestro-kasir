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
            View::composer('*', function ($view) {
                // Gunakan static variable agar querinya hanya dieksekusi 1x per request, 
                // mencegah masalah N+1 ketika puluhan view/komponen di-render.
                static $settings = null;
                static $isBranchActive = true;
                
                if ($settings === null) {
                    $activeBranchId = \Illuminate\Support\Facades\Session::get('active_branch_id');
                    
                    if ($activeBranchId) {
                        $settings = StoreSetting::where('branch_id', $activeBranchId)->first();
                        $branch = \App\Models\Branch::find($activeBranchId);
                        if ($branch && !$branch->is_active) {
                            $isBranchActive = false;
                        }
                    }

                    if (! $settings) {
                        $settings = StoreSetting::first();
                    }
                    
                    if (! $settings) {
                        $settings = (object) [
                            'store_name' => 'Maestro POS',
                            'store_address' => 'Jl. Contoh Alamat',
                            'store_phone' => '08123456789',
                            'store_footer' => 'Terima Kasih',
                            'store_logo' => null,
                            'is_tax' => false,
                            'tax' => 0,
                            'is_supplier' => false,
                        ];
                    }
                }

                $view->with('settings', $settings)
                     ->with('isBranchActive', $isBranchActive);
            });
        }
    }
}
