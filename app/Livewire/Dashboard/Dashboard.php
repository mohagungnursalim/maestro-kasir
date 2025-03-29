<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalOrders;
    public $totalSales;
    public $totalProductsSold;

    public $filterType = 'today'; // Default Hari Ini

    public function mount()
    {
        $this->updateStats();
    }

    public function updateStats()
    {
        $dates = $this->getDateRange($this->filterType);
        $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']]);
        $queryTransactions = TransactionDetail::whereBetween('created_at', [$dates['start'], $dates['end']]);

        // Jika bukan admin, hanya ambil transaksi kasir yang login
        if (!Auth::user()->hasRole('admin')) {
            $queryOrders->where('user_id', Auth::id());
            $queryTransactions->whereHas('order', function ($query) {
                $query->where('user_id', Auth::id());
            });
        }

        $this->totalOrders = $queryOrders->count();
        $this->totalSales = $queryOrders->sum('grandtotal');
        $this->totalProductsSold = $queryTransactions->sum('quantity');
    }

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

    public function updatedFilterType()
    {
        $this->updateStats();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
