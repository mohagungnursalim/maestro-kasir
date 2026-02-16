<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalOrders;
    public $totalActualSales;
    public $totalProductsSold;

    public $filterType = 'today'; // Default Hari Ini

    public function mount()
    {
        $this->updateStats();
    }

    // Mengambil data statistik berdasarkan filter yang dipilih
    public function updateStats()
    {
        $dates = $this->getDateRange($this->filterType);

        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        // Key cache unik per filter + range + role/user
        $cacheKey = sprintf(
            'dashboard_stats:%s:%s:%s:%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_'.$userId
        );

        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dates, $isAdmin, $userId) {

            // Query orders (untuk total count)
            $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            // Query transactions (untuk sales & quantity)
            $queryTransactions = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('transaction_details.created_at', [$dates['start'], $dates['end']])
                ->where('orders.payment_status', 'PAID');

            if (!Auth::user()->hasRole('admin|owner')) {
                $queryOrders->where('user_id', Auth::id());
                $queryTransactions->where('orders.user_id', Auth::id());
            }

            return [
                'totalOrders'        => (int) $queryOrders->count(),
                'totalActualSales'  => (float) $queryTransactions->sum('transaction_details.subtotal'),
                'totalProductsSold' => (int) $queryTransactions->sum('transaction_details.quantity'),
            ];
        });

        // assign ke state Livewire
        $this->totalOrders        = $stats['totalOrders'];
        $this->totalActualSales  = $stats['totalActualSales'];
        $this->totalProductsSold = $stats['totalProductsSold'];
    }
    
    

    // Mengambil rentang tanggal berdasarkan filter yang dipilih
    public function getDateRange($type)
    {
        switch ($type) {
            case 'week':
                return ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()];
            case 'month':
                return ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()];
            case 'year':
                return ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()];
            default: // today
                return ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()];
        }
    }

    // Mengupdate statistik ketika filter diubah
    public function updatedFilterType()
    {
        $this->updateStats();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
