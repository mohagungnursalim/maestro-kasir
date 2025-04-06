<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use Livewire\Component;

class StockWarning extends Component
{
    public $lowStockProducts = [];
    public $productExists = false;


    public function mount()
    {
        $this->loadLowStockProducts();
    }

    public function loadLowStockProducts()
    {
        $this->productExists = Product::exists(); // cek apakah ada data produk
    
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
