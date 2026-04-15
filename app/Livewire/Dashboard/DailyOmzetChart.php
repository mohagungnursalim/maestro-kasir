<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DailyOmzetChart extends Component
{
    public array  $dailyOmzet   = [];   // ['01' => 0, '02' => 150000, ...]
    public float  $avgOmzet     = 0;    // Rata-rata omzet per hari aktif
    public float  $topDayOmzet  = 0;   // Omzet tertinggi
    public string $topDayLabel  = '';   // Label tanggal terlaris
    public int    $activeDays   = 0;    // Hari yang ada transaksi

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
        $activeBranch       = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');

        $cacheKey = sprintf(
            'daily_omzet:%s:%s:%s:tv%s:br%s',
            $filter,
            $start,
            $end,
            $transactionVersion,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($start, $end, $groupFmt, $isAdmin, $userId) {
            $query = DB::table('orders')
                ->selectRaw("DATE_FORMAT(created_at, '$groupFmt') as day_label, SUM(grandtotal) as total")
                ->whereBetween('created_at', [$start, $end])
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            return $query->groupByRaw("DATE_FORMAT(created_at, '$groupFmt')")
                ->orderBy('day_label')
                ->get()
                ->keyBy('day_label');
        });

        // Isi array dengan semua label periode (0 bila tidak ada transaksi)
        $data = [];
        foreach ($days as $label) {
            $data[$label] = (float) ($results[$label]->total ?? 0);
        }

        $nonZero = array_filter($data, fn($v) => $v > 0);

        $this->dailyOmzet  = $data;
        $this->activeDays  = count($nonZero);
        $this->avgOmzet    = $this->activeDays > 0 ? array_sum($nonZero) / $this->activeDays : 0;
        $this->topDayOmzet = $nonZero ? max($nonZero) : 0;
        $this->topDayLabel = $nonZero ? array_search($this->topDayOmzet, $data) : '';
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
