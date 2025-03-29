<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductSalesChart extends Component
{
    public $dailySales = [];
    public $weeklySales = [];
    public $monthlySales = [];
    public $yearlySales = [];

    public function mount()
    {
        $this->loadSalesData();
    }

    public function loadSalesData()
    {
        // Hari ini (00:00:00 - 23:59:59)
        $this->dailySales = $this->getSalesData(Carbon::today()->startOfDay(), Carbon::today()->endOfDay());
    
        // Minggu ini
        $this->weeklySales = $this->getSalesData(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());
    
        // Bulan ini
        $this->monthlySales = $this->getSalesData(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
    
        // Tahun ini
        $this->yearlySales = $this->getSalesData(Carbon::now()->startOfYear(), Carbon::now()->endOfYear());
   
    }
    

    private function getSalesData($startDate, $endDate)
    {
        $query = TransactionDetail::selectRaw('products.name, SUM(transaction_details.quantity) as total_sold')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->whereBetween('transaction_details.created_at', [$startDate, $endDate]);

        // Jika bukan admin, filter berdasarkan kasir yang login
        if (!Auth::user()->hasRole('admin')) {
            $query->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        return $query->groupBy('products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get()
            ->map(fn($sale) => [
                'name' => $sale->name,
                'total' => $sale->total_sold
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.product-sales-chart');
    }
}
