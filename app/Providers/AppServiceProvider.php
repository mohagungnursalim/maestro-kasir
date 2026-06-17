<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('viewPulse', function ($user) {
            return $user->hasRole('admin|owner');
        });

        View::composer('*', function ($view) {
            // Gunakan static variable agar querinya hanya dieksekusi 1x per request, 
            // mencegah masalah N+1 ketika puluhan view/komponen di-render.
            static $settings = null;
            static $isBranchActive = true;
            
            if ($settings === null) {
                $activeBranchId = \Illuminate\Support\Facades\Session::get('active_branch_id');
                $cacheKey = 'store_settings_br_' . ($activeBranchId ?: 'all');
                
                $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(1), function () use ($activeBranchId) {
                    try {
                        $s = null;
                        $active = true;
                        
                        if ($activeBranchId) {
                            $s = StoreSetting::where('branch_id', $activeBranchId)->first();
                            $branch = \App\Models\Branch::find($activeBranchId);
                            if ($branch && !$branch->is_active) {
                                $active = false;
                            }
                        }

                        if (! $s) {
                            $s = StoreSetting::first();
                        }
                        
                        if (! $s) {
                            $s = (object) [
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
                        
                        // Konversi ke plain object/array agar aman di-serialize
                        return ['settings' => $s, 'isBranchActive' => $active];
                    } catch (\Exception $e) {
                        return [
                            'settings' => (object) [
                                'store_name' => 'Maestro POS',
                                'store_address' => 'Jl. Contoh Alamat',
                                'store_phone' => '08123456789',
                                'store_footer' => 'Terima Kasih',
                                'store_logo' => null,
                                'is_tax' => false,
                                'tax' => 0,
                                'is_supplier' => false,
                            ],
                            'isBranchActive' => true
                        ];
                    }
                });

                $settings = $data['settings'];
                $isBranchActive = $data['isBranchActive'];
            }

            $view->with('settings', $settings)
                 ->with('isBranchActive', $isBranchActive);
        });
    }
}
