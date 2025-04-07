<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
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
        // Cek apakah ada produk di database
        $this->productExists = Product::exists(); // cek apakah ada data produk
    
        // Jika ada produk, ambil produk dengan stok kurang dari 10
        if ($this->productExists) {
            $this->lowStockProducts = Product::where('stock', '<', 10)
                ->orderBy('stock', 'asc')
                ->take(5)
                ->get();
        } else {
            $this->lowStockProducts = collect(); // kosongkan dengan collection kosong
        }
    }
    

    public function render()
    {
        return view('livewire.dashboard.stock-warning');
    }
}
