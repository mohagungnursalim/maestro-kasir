<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DailyOmzetChart extends Component
{
    public array  $dailyOmzet   = [];   // ['01' => 0, '02' => 150000, ...]
    public array  $dailyExpense = [];
    public float  $avgOmzet     = 0;    // Rata-rata omzet per hari aktif
    public float  $topDayOmzet  = 0;   // Omzet tertinggi
    public string $topDayLabel  = '';   // Label tanggal terlaris
    public int    $activeDays   = 0;    // Hari yang ada transaksi
    
    public float  $totalExpense = 0;

    /**
     * Listener dari Dashboard saat filter diubah.
     * filterType bisa: 'today' | 'week' | 'month' | 'year'
     */
    public function loadDailyOmzet(string $filter = 'month')
    {
        $this->generateDailyOmzet($filter);
    }

    public function mount()
    {
        $this->generateDailyOmzet('month');
    }

    public function generateDailyOmzet(string $filter = 'month')
    {
        $userId   = Auth::id();
        $isAdmin  = Auth::user()->hasRole('admin|owner');

        // Tentukan rentang tanggal berdasarkan filter
        [$start, $end, $groupFmt, $labelFmt, $days] = $this->resolvePeriod($filter);

        $transactionVersion = Cache::get('transaction_cache_version', 1);
        $expenseVersion     = Cache::get('expense_cache_version', 1);
        $activeBranch       = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');

        $cacheKey = sprintf(
            'daily_omzet_v2:%s:%s:%s:tv%s:ev%s:br%s',
            $filter,
            $start,
            $end,
            $transactionVersion,
            $expenseVersion,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($start, $end, $groupFmt, $isAdmin, $userId) {
            
            // Omzet
            $queryOrders = DB::table('orders')
                ->selectRaw("DATE_FORMAT(created_at, '$groupFmt') as day_label, SUM(grandtotal) as total")
                ->whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $queryOrders->where('branch_id', session('active_branch_id'));
            }
            if (!$isAdmin) {
                $queryOrders->where('user_id', $userId);
            }
            $orders = $queryOrders->groupByRaw("DATE_FORMAT(created_at, '$groupFmt')")->get()->keyBy('day_label');

            // Pengeluaran
            $dateColForFormat = str_contains($groupFmt, '%H') ? 'created_at' : 'expense_date';
            
            $queryExpenses = DB::table('expenses')
                ->selectRaw("DATE_FORMAT($dateColForFormat, '$groupFmt') as day_label, SUM(amount) as total")
                ->where('type', 'out');

            if ($dateColForFormat === 'expense_date') {
                 // Format start & end as date for expense_date
                 $startDt = substr($start, 0, 10);
                 $endDt   = substr($end, 0, 10);
                 $queryExpenses->whereBetween('expense_date', [$startDt, $endDt]);
            } else {
                 $queryExpenses->whereBetween('created_at', [$start, $end]);
            }

            if (session()->has('active_branch_id')) {
                $queryExpenses->where('branch_id', session('active_branch_id'));
            }
            if (!$isAdmin) {
                $queryExpenses->where('user_id', $userId);
            }
            $expenses = $queryExpenses->groupByRaw("DATE_FORMAT($dateColForFormat, '$groupFmt')")->get()->keyBy('day_label');

            return ['orders' => $orders, 'expenses' => $expenses];
        });

        // Isi array dengan semua label periode (0 bila tidak ada transaksi)
        $dataOmzet = [];
        $dataExpense = [];
        foreach ($days as $label) {
            $dataOmzet[$label]   = (float) ($results['orders'][$label]->total ?? 0);
            $dataExpense[$label] = (float) ($results['expenses'][$label]->total ?? 0);
        }

        $nonZero = array_filter($dataOmzet, fn($v) => $v > 0);

        $this->dailyOmzet   = $dataOmzet;
        $this->dailyExpense = $dataExpense;
        $this->activeDays   = count($nonZero);
        $this->avgOmzet     = $this->activeDays > 0 ? array_sum($nonZero) / $this->activeDays : 0;
        $this->topDayOmzet  = $nonZero ? max($nonZero) : 0;
        $this->topDayLabel  = $nonZero ? array_search($this->topDayOmzet, $dataOmzet) : '';
        $this->totalExpense = array_sum($dataExpense);
    }

    /**
     * Kembalikan [start, end, groupFmt, labelFmt, labels[]]
     * groupFmt = format MySQL DATE_FORMAT
     */
    protected function resolvePeriod(string $filter): array
    {
        $now = now();

        if ($filter === 'today') {
            // Tampilkan per jam dalam hari ini
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
            // Tampilkan per hari dalam minggu ini (Sen-Min)
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
            // Tampilkan per bulan dalam tahun ini
            $start    = $now->copy()->startOfYear()->toDateTimeString();
            $end      = $now->copy()->endOfYear()->toDateTimeString();
            $groupFmt = '%Y-%m';
            $labels   = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $now->year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
            return [$start, $end, $groupFmt, 'M Y', $labels];
        }

        // Default: month — per hari dalam bulan ini
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
        return view('livewire.dashboard.daily-omzet-chart');
    }
}
