<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductSalesChart extends Component
{
    public $salesData = [];
    public $filterType = 'today';

    public function mount()
    {
        $this->loadData();
    }

    #[On('globalFilterUpdated')]
    public function updateFilter($filter)
    {
        $this->filterType = $filter;
        $this->loadData();
        $this->dispatch('productSalesDataUpdated', salesData: $this->salesData);
    }

    public function loadData()
    {
        $dates = $this->getDateRange($this->filterType);
        $this->salesData = $this->getSalesData($dates['start'], $dates['end']);
    }

    protected function getDateRange($type)
    {
        return match ($type) {
            'week'  => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()],
            'year'  => ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()],
            default => ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()],
        };
    }
    
    // Mengambil data penjualan produk berdasarkan rentang tanggal
    private function getSalesData($startDate, $endDate)
    {
        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
        $cacheKey = sprintf(
            'product_sales:%s:%s:%s:br%s',
            $startDate->format('YmdHis'),
            $endDate->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_'.$userId,
            $activeBranch
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
