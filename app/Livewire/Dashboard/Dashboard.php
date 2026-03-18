<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\TransactionDetail;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalOrders;
    public $totalActualSales;
    public $totalProductsSold;

    // Visitor stats
    public $totalPageViews;
    public $totalUniqueVisitors;

    public $filterType = 'today'; // Default Hari Ini

    public function mount()
    {
        $this->updateStats();
    }

    // Mengambil data statistik berdasarkan filter yang dipilih
    public function updateStats()
    {
        $dates = $this->getDateRange($this->filterType);

        // Simpan sekali, pakai berkali-kali (hindari panggilan Auth berulang)
        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        // Key cache unik per filter + range + role/user
        $cacheKey = sprintf(
            'dashboard_stats:%s:%s:%s:%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_' . $userId
        );

        // Semua stats (termasuk visitor jika admin) dalam 1 cache block
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dates, $isAdmin, $userId) {

            // Query 1: Total orders (single count)
            $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            if (!$isAdmin) {
                $queryOrders->where('user_id', $userId);
            }

            // Query 2: Sales & quantity digabung jadi 1 query dengan selectRaw
            $queryTransactions = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('transaction_details.created_at', [$dates['start'], $dates['end']])
                ->where('orders.payment_status', 'PAID');

            if (!$isAdmin) {
                $queryTransactions->where('orders.user_id', $userId);
            }

            $salesAggregates = $queryTransactions
                ->selectRaw('COALESCE(SUM(transaction_details.subtotal), 0) as total_sales, COALESCE(SUM(transaction_details.quantity), 0) as total_qty')
                ->first();

            $result = [
                'totalOrders'       => (int) $queryOrders->count(),
                'totalActualSales'  => (float) ($salesAggregates->total_sales ?? 0),
                'totalProductsSold' => (int) ($salesAggregates->total_qty ?? 0),
            ];

            // Query 3 (admin only): Visitor stats digabung jadi 1 query dengan selectRaw
            if ($isAdmin) {
                $visitorAggregates = Visitor::whereBetween('visited_at', [$dates['start'], $dates['end']])
                    ->selectRaw('COUNT(*) as total_views, COUNT(DISTINCT ip_address) as unique_visitors')
                    ->first();

                $result['totalPageViews']      = (int) ($visitorAggregates->total_views ?? 0);
                $result['totalUniqueVisitors'] = (int) ($visitorAggregates->unique_visitors ?? 0);
            }

            return $result;
        });

        // Assign ke state Livewire
        $this->totalOrders       = $stats['totalOrders'];
        $this->totalActualSales  = $stats['totalActualSales'];
        $this->totalProductsSold = $stats['totalProductsSold'];

        if ($isAdmin) {
            $this->totalPageViews      = $stats['totalPageViews'] ?? 0;
            $this->totalUniqueVisitors = $stats['totalUniqueVisitors'] ?? 0;
        }
    }

    // Mengambil rentang tanggal berdasarkan filter yang dipilih
    public function getDateRange($type)
    {
        return match ($type) {
            'week'  => ['start' => Carbon::now()->startOfWeek(), 'end' => Carbon::now()->endOfWeek()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()],
            'year'  => ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()],
            default => ['start' => Carbon::today(), 'end' => Carbon::today()->endOfDay()],
        };
    }

    // Mengupdate statistik ketika filter diubah
    public function updatedFilterType()
    {
        $this->updateStats();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
