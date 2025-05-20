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

    // Mengambil data statistik berdasarkan filter yang dipilih
    public function updateStats()
    {
        $dates = $this->getDateRange($this->filterType);
    
        // Query orders (untuk total count)
        $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']]);
    
        // Query transactions (untuk sales & quantity)
        $queryTransactions = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
            ->whereBetween('transaction_details.created_at', [$dates['start'], $dates['end']]);
    
        if (!Auth::user()->hasRole('admin|owner')) {
            $queryOrders->where('user_id', Auth::id());
            $queryTransactions->where('orders.user_id', Auth::id());
        }
    
        $this->totalOrders = $queryOrders->count();
    
        // Gunakan sum(subtotal) untuk total penjualan bersih (tanpa diskon & pajak)
        $this->totalSales = $queryTransactions->sum('transaction_details.subtotal');
    
        $this->totalProductsSold = $queryTransactions->sum('transaction_details.quantity');
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
