<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductSalesChart extends Component
{
    public $dailySales = []; // Data penjualan hari ini
    public $weeklySales = []; // Data penjualan minggu ini
    public $monthlySales = []; // Data penjualan bulan ini
    public $yearlySales = []; // Data penjualan tahun ini

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
    
    // Mengambil data penjualan produk berdasarkan rentang tanggal
    private function getSalesData($startDate, $endDate)
    {
        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        // Key cache unik per range tanggal + role/user
        $cacheKey = sprintf(
            'product_sales:%s:%s:%s',
            $startDate->format('YmdHis'),
            $endDate->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_'.$userId
        );

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($startDate, $endDate) {

            // Mengambil data penjualan produk dari TransactionDetail
            $query = TransactionDetail::selectRaw('products.name, SUM(transaction_details.quantity) as total_sold')
                ->join('products', 'transaction_details.product_id', '=', 'products.id')
                ->whereBetween('transaction_details.created_at', [$startDate, $endDate])
                ->whereHas('order', function ($q) {
                    $q->where('payment_status', 'PAID');
                });

            // Jika bukan admin/owner, filter berdasarkan kasir yang login
            if (!Auth::user()->hasRole('admin|owner')) {
                $query->whereHas('order', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }

            // Mengelompokkan data berdasarkan nama produk dan menghitung total penjualan
            return $query->groupBy('products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get()
                ->map(fn ($sale) => [
                    'name'  => $sale->name,
                    'total' => (int) $sale->total_sold,
                ])
                ->toArray();
        });
    }

    public function render()
    {
        return view('livewire.dashboard.product-sales-chart');
    }
}
