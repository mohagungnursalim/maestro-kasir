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

        $query = DB::table('orders')
            ->selectRaw('MONTH(created_at) as month, SUM(grandtotal) as total')
            ->whereYear('created_at', $year);

        if (!Auth::user()->hasRole('admin|owner')) {
            $query->where('user_id', Auth::id());
        }

        $results = $query->groupByRaw('MONTH(created_at)')
                         ->orderBy('month')
                         ->get();

        $this->monthlyTurnover = array_fill(0, 12, 0);

        foreach ($results as $result) {
            $this->monthlyTurnover[$result->month - 1] = (float) $result->total;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-turnover-chart');
    }
}
