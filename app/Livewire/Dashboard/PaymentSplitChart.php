<?php

namespace App\Livewire\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class PaymentSplitChart extends Component
{
    public float $qrisTotal   = 0;
    public float $tunaiTotal  = 0;
    public float $otherTotal  = 0;
    public int   $qrisPct     = 0;
    public int   $tunaiPct    = 0;
    public int   $otherPct    = 0;

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
        $this->dispatch('update-payment-split-chart', 
            qris: $this->qrisTotal, 
            tunai: $this->tunaiTotal, 
            other: $this->otherTotal
        );
    }

    public function loadData(): void
    {
        $dates        = $this->getDateRange($this->filterType);
        $userId       = Auth::id();
        $isAdmin      = Auth::user()->hasRole('admin|owner');
        $activeBranch = session('active_branch_id', 'all');
        $version      = Cache::get('transaction_cache_version', 1);

        $cacheKey = sprintf(
            'payment_split:%s:%s:%s:%s:v%s:br%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_' . $userId,
            $version,
            $activeBranch
        );

        $row = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dates, $isAdmin, $userId) {
            $query = DB::table('orders')
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN UPPER(payment_method) = 'QRIS'                   THEN grandtotal ELSE 0 END), 0) as qris,
                    COALESCE(SUM(CASE WHEN UPPER(payment_method) IN ('CASH','TUNAI')         THEN grandtotal ELSE 0 END), 0) as tunai,
                    COALESCE(SUM(CASE WHEN UPPER(payment_method) NOT IN ('QRIS','CASH','TUNAI') THEN grandtotal ELSE 0 END), 0) as other
                ")
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            if (session()->has('active_branch_id')) {
                $query->where('branch_id', session('active_branch_id'));
            }
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            return $query->first();
        });

        $this->qrisTotal  = (float) ($row->qris  ?? 0);
        $this->tunaiTotal = (float) ($row->tunai  ?? 0);
        $this->otherTotal = (float) ($row->other  ?? 0);

        $total = $this->qrisTotal + $this->tunaiTotal + $this->otherTotal;
        if ($total > 0) {
            $this->qrisPct  = (int) round(($this->qrisTotal  / $total) * 100);
            $this->tunaiPct = (int) round(($this->tunaiTotal / $total) * 100);
            $this->otherPct = (int) round(($this->otherTotal / $total) * 100);
        } else {
            $this->qrisPct = $this->tunaiPct = $this->otherPct = 0;
        }
    }

    protected function getDateRange(string $type): array
    {
        return match ($type) {
            'week'  => ['start' => Carbon::now()->startOfWeek(),  'end' => Carbon::now()->endOfWeek()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()],
            'year'  => ['start' => Carbon::now()->startOfYear(),  'end' => Carbon::now()->endOfYear()],
            default => ['start' => Carbon::today(),               'end' => Carbon::today()->endOfDay()],
        };
    }

    public function render()
    {
        return view('livewire.dashboard.payment-split-chart');
    }
}
