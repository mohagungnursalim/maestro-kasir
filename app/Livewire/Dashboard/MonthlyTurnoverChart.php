<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
        $cacheKey = sprintf(
            'monthly_turnover:%s:%s:br%s',
            $year,
            $isAdmin ? 'admin' : 'user_'.$userId,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($year, $isAdmin, $userId) {

            // sum pendapatan bersih per bulan (dengan diskon & pajak) menggunakan orders.grandtotal
            $query = DB::table('orders')
                ->selectRaw('MONTH(created_at) as month, SUM(grandtotal) as total')
                ->whereYear('created_at', $year)
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }

            if (!Auth::user()->hasRole('admin|owner')) {
                $query->where('user_id', Auth::id());
            }

            return $query->groupByRaw('MONTH(created_at)')
                ->orderBy('month')
                ->get();
        });

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
