<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MonthlyTurnoverChart extends Component
{
    public $monthlyTurnover = [];

    public function mount()
    {
        $this->generateMonthlyTurnover();
    }

    public function generateMonthlyTurnover()
    {
        $year = now()->year;
    
        // sum omzet murni per bulan "dengan" diskon & pajak
        $query = DB::table('transaction_details')
            ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
            ->selectRaw('MONTH(orders.created_at) as month, SUM(transaction_details.subtotal) as total')
            ->whereYear('orders.created_at', $year);
    
        if (!Auth::user()->hasRole('admin|owner')) {
            $query->where('orders.user_id', Auth::id());
        }
    
        $results = $query->groupByRaw('MONTH(orders.created_at)')
                        ->orderBy('month')
                        ->get();
    
        // Inisialisasi array dengan 12 bulan
        $this->monthlyTurnover = array_fill(0, 12, '0.00');
    
        foreach ($results as $result) {
            // Simpan omzet murni tiap bulan
            $this->monthlyTurnover[$result->month - 1] = (float) $result->total;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-turnover-chart');
    }
}
