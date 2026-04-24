<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DailyExpenseChart extends Component
{
    public array  $dailyExpense = [];
    public float  $avgExpense   = 0;
    public float  $topDayExpense= 0;
    public string $topDayLabel  = '';
    public int    $activeDays   = 0;

    public function loadDailyExpense(string $filter = 'month')
    {
        $this->generateDailyExpense($filter);
    }

    public function mount()
    {
        $this->generateDailyExpense('month');
    }

    public function generateDailyExpense(string $filter = 'month')
    {
        $userId   = Auth::id();
        $isAdmin  = Auth::user()->hasRole('admin|owner');

        [$start, $end, $groupFmt, $labelFmt, $days] = $this->resolvePeriod($filter);

        $expenseVersion = Cache::get('expense_cache_version', 1);
        $activeBranch   = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');

        $cacheKey = sprintf(
            'daily_expense:%s:%s:%s:ev%s:br%s',
            $filter,
            $start,
            $end,
            $expenseVersion,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($start, $end, $groupFmt, $isAdmin, $userId) {
            
            $dateColForFormat = str_contains($groupFmt, '%H') ? 'created_at' : 'expense_date';
            
            $query = DB::table('expenses')
                ->selectRaw("DATE_FORMAT($dateColForFormat, '$groupFmt') as day_label, SUM(amount) as total")
                ->where('type', 'out');

            if ($dateColForFormat === 'expense_date') {
                 // Format start & end as date for expense_date
                 $startDt = substr($start, 0, 10);
                 $endDt   = substr($end, 0, 10);
                 $query->whereBetween('expense_date', [$startDt, $endDt]);
            } else {
                 $query->whereBetween('created_at', [$start, $end]);
            }

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            return $query->groupByRaw("DATE_FORMAT($dateColForFormat, '$groupFmt')")
                ->orderBy('day_label')
                ->get()
                ->keyBy('day_label');
        });

        $data = [];
        foreach ($days as $label) {
            $data[$label] = (float) ($results[$label]->total ?? 0);
        }

        $nonZero = array_filter($data, fn($v) => $v > 0);

        $this->dailyExpense  = $data;
        $this->activeDays    = count($nonZero);
        $this->avgExpense    = $this->activeDays > 0 ? array_sum($nonZero) / $this->activeDays : 0;
        $this->topDayExpense = $nonZero ? max($nonZero) : 0;
        $this->topDayLabel   = $nonZero ? array_search($this->topDayExpense, $data) : '';
    }

    protected function resolvePeriod(string $filter): array
    {
        $now = now();

        if ($filter === 'today') {
            $start    = $now->copy()->startOfDay()->toDateTimeString();
            $end      = $now->copy()->endOfDay()->toDateTimeString();
            $groupFmt = '%H:00';
            $labels   = [];
            for ($h = 0; $h < 24; $h++) {
                $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            }
            return [$start, $end, $groupFmt, 'd', $labels];
        }

        if ($filter === 'week') {
            $start  = $now->copy()->startOfWeek()->toDateTimeString();
            $end    = $now->copy()->endOfWeek()->toDateTimeString();
            $groupFmt = '%Y-%m-%d';
            $labels = [];
            $cursor = $now->copy()->startOfWeek();
            while ($cursor <= $now->copy()->endOfWeek()) {
                $labels[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            return [$start, $end, $groupFmt, 'd M', $labels];
        }

        if ($filter === 'year') {
            $start    = $now->copy()->startOfYear()->toDateTimeString();
            $end      = $now->copy()->endOfYear()->toDateTimeString();
            $groupFmt = '%Y-%m';
            $labels   = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $now->year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
            return [$start, $end, $groupFmt, 'M Y', $labels];
        }

        $start    = $now->copy()->startOfMonth()->toDateTimeString();
        $end      = $now->copy()->endOfMonth()->toDateTimeString();
        $groupFmt = '%Y-%m-%d';
        $labels   = [];
        $cursor   = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        while ($cursor <= $monthEnd) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }
        return [$start, $end, $groupFmt, 'd M', $labels];
    }

    public function render()
    {
        return view('livewire.dashboard.daily-expense-chart');
    }
}
