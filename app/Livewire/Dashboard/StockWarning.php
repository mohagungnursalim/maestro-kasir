<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use Livewire\Component;

class StockWarning extends Component
{
    public $lowStockProducts = [];

    public function mount()
    {
        $this->loadLowStockProducts();
    }

    public function loadLowStockProducts()
    {
        $this->lowStockProducts = Product::where('stock', '<', 10)
            ->orderBy('stock', 'asc') // Urutkan dari stok terkecil
            ->take(5) // Ambil hanya 5 produk
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.stock-warning');
    }
}
