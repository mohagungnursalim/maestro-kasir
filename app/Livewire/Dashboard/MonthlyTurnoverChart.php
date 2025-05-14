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

    // Omset Perbulan
    public function generateMonthlyTurnover()
    {
        $year = now()->year;
    
        $query = DB::table('orders')
            ->selectRaw('MONTH(created_at) as month, SUM(grandtotal) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderBy('month');
    
        // Kalau user bukan admin/owner
        if (!Auth::user()->hasRole('admin|owner')) {
            $query->where('user_id', Auth::id());
        }
    
        $results = $query->get();
    
        // Buat array 12 bulan default 0
        $this->monthlyTurnover = array_fill(0, 12, 0);
    
        foreach ($results as $result) {
            $index = $result->month - 1;
            $this->monthlyTurnover[$index] = (float) $result->total;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-turnover-chart');
    }
}
