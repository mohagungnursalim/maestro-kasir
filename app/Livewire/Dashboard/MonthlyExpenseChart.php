<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MonthlyExpenseChart extends Component
{
    public $monthlyExpense = [];

    public function mount()
    {
        $this->generateMonthlyExpense();
    }

    public function generateMonthlyExpense()
    {
        $year = now()->year;

        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
        $cacheKey = sprintf(
            'monthly_expense:%s:%s:br%s',
            $year,
            $isAdmin ? 'admin' : 'user_'.$userId,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($year, $isAdmin, $userId) {

            // sum pengeluaran per bulan menggunakan expenses.amount dimana type = 'out'
            $query = DB::table('expenses')
                ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
                ->whereYear('expense_date', $year)
                ->where('type', 'out');

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            return $query->groupByRaw('MONTH(expense_date)')
                ->orderBy('month')
                ->get();
        });

        // Inisialisasi array dengan 12 bulan
        $this->monthlyExpense = array_fill(0, 12, '0.00');

        foreach ($results as $result) {
            $this->monthlyExpense[$result->month - 1] = (float) $result->total;
        }
    }

    public function render()
    {
        return view('livewire.dashboard.monthly-expense-chart');
    }
}
