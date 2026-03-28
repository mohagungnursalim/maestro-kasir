<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StockWarning extends Component
{
    public $lowStockProducts = []; // Menyimpan produk dengan stok rendah
    public $productExists = false; // Menandakan apakah ada produk yang ada di database


    public function mount()
    {
        $this->loadLowStockProducts();
    }

    // Memuat produk dengan stok rendah
    public function loadLowStockProducts()
    {
        // Gunakan product_cache_version agar otomatis stale saat ada perubahan produk
        $version  = Cache::get('product_cache_version', 1);
        $cacheKey = "stock_warning_v{$version}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            // Hanya produk yang menggunakan stok (use_stock = true)
            $exists = Product::where('use_stock', true)->exists();

            $low = collect();
            if ($exists) {
                $low = Product::where('use_stock', true)
                    ->where('stock', '<', 10)
                    ->orderBy('stock', 'asc')
                    ->take(5)
                    ->get(['id', 'name', 'stock']);
            }

            return [
                'productExists'    => $exists,
                'lowStockProducts' => $low,
            ];
        });

        $this->productExists    = $data['productExists'];
        $this->lowStockProducts = $data['lowStockProducts'];
    }
    

    public function render()
    {
        return view('livewire.dashboard.stock-warning');
    }
}
