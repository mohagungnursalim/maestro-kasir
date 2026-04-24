<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MonthlyTurnoverChart extends Component
{
    public $monthlyTurnover = [];
    public $monthlyExpense  = [];

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
            'monthly_turnover_v2:%s:%s:br%s',
            $year,
            $isAdmin ? 'admin' : 'user_'.$userId,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($year, $isAdmin, $userId) {

            // Omzet
            $queryOrders = DB::table('orders')
                ->selectRaw('MONTH(created_at) as month, SUM(grandtotal) as total')
                ->whereYear('created_at', $year)
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $queryOrders->where('branch_id', session('active_branch_id'));
            }

            if (!$isAdmin) {
                $queryOrders->where('user_id', $userId);
            }

            $orders = $queryOrders->groupByRaw('MONTH(created_at)')->orderBy('month')->get();

            // Pengeluaran
            $queryExpenses = DB::table('expenses')
                ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
                ->whereYear('expense_date', $year)
                ->where('type', 'out');

            if (session()->has('active_branch_id')) {
                $queryExpenses->where('branch_id', session('active_branch_id'));
            }

            if (!$isAdmin) {
                $queryExpenses->where('user_id', $userId);
            }

            $expenses = $queryExpenses->groupByRaw('MONTH(expense_date)')->orderBy('month')->get();

            return ['orders' => $orders, 'expenses' => $expenses];
        });

        // Inisialisasi array dengan 12 bulan
        $this->monthlyTurnover = array_fill(0, 12, '0.00');
        $this->monthlyExpense  = array_fill(0, 12, '0.00');

        foreach ($results['orders'] as $result) {
            $this->monthlyTurnover[$result->month - 1] = (float) $result->total;
        }

        foreach ($results['expenses'] as $result) {
            $this->monthlyExpense[$result->month - 1]  = (float) $result->total;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-turnover-chart');
    }
}
