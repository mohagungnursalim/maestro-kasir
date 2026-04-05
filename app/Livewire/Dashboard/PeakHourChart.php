<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class PeakHourChart extends Component
{
    public array $hourlyData = [];
    public string $filterType = 'today';

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedFilterType(): void
    {
        $this->loadData();
    }

    #[On('globalFilterUpdated')]
    public function updateFilter($filter)
    {
        $this->filterType = $filter;
        $this->loadData();
        $this->dispatch('update-peak-chart', hourlyData: $this->hourlyData);
    }

    public function loadData(): void
    {
        $dates = $this->getDateRange($this->filterType);

        $userId      = Auth::id();
        $isAdmin     = Auth::user()->hasRole('admin|owner');
        $activeBranch = session('active_branch_id', 'all');

        $version  = Cache::get('transaction_cache_version', 1);
        $cacheKey = sprintf(
            'peak_hour:%s:%s:%s:%s:v%s:br%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_' . $userId,
            $version,
            $activeBranch
        );

        $results = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dates, $isAdmin, $userId) {
            $query = DB::table('orders')
                ->selectRaw('HOUR(created_at) as hour, COUNT(id) as total_orders, COALESCE(SUM(grandtotal), 0) as total_revenue')
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            return $query->groupByRaw('HOUR(created_at)')
                ->orderBy('hour')
                ->get()
                ->keyBy('hour');
        });

        // Build full 24-hour array (08:00 – 23:00 + 00:00–07:00)
        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $row    = $results->get($h);
            $hours[] = [
                'hour'          => sprintf('%02d:00', $h),
                'total_orders'  => $row ? (int) $row->total_orders  : 0,
                'total_revenue' => $row ? (float) $row->total_revenue : 0.0,
            ];
        }

        $this->hourlyData = $hours;
    }

    protected function getDateRange(string $type): array
    {
        return match ($type) {
            'week'  => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()],
            default => ['start' => Carbon::today(),              'end' => Carbon::today()->endOfDay()],
        };
    }

    public function render()
    {
        return view('livewire.dashboard.peak-hour-chart');
    }
}
