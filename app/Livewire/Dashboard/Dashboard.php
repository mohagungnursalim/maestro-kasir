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
    public $totalOmzet;      // Pendapatan murni dari penjualan
    public $totalNetCash;   // Saldo kas bersih = Omzet + Top Up - Pengeluaran
    public $totalProductsSold;
    public $totalExpenses;
    public $totalTopUps;

    // Visitor stats
    public $totalPageViews;
    public $totalUniqueVisitors;

    public $filterType = 'today'; // Default Hari Ini
    public $loaded = false;

    public function mount()
    {
        // Tunggu wire:init untuk mulai loading
    }

    public function loadInitialStats()
    {
        $this->updateStats();
        $this->loaded = true;
    }

    // Mengambil data statistik berdasarkan filter yang dipilih
    public function updateStats()
    {
        $dates = $this->getDateRange($this->filterType);

        // Simpan sekali, pakai berkali-kali (hindari panggilan Auth berulang)
        $userId  = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin|owner');

        // Sertakan product_cache_version agar cache stale otomatis saat ada update produk (misal: toggle use_stock)
        $productVersion = Cache::get('product_cache_version', 1);

        // Key cache unik per filter + range + role/user + versi produk
        $cacheKey = sprintf(
            'dashboard_stats:%s:%s:%s:%s:pv%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_' . $userId,
            $productVersion
        );

        // Semua stats (termasuk visitor jika admin) dalam 1 cache block
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dates, $isAdmin, $userId) {

            // Query 1: Total orders (single count) & Total Pendapatan Bersih (Grandtotal = dengan diskon & pajak)
            $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            if (!$isAdmin) {
                $queryOrders->where('user_id', $userId);
            }
            
            $ordersAggregates = (clone $queryOrders)->selectRaw('COUNT(id) as total_orders, COALESCE(SUM(grandtotal), 0) as total_sales')->first();

            // Query 2: Quantity produk terjual
            $queryTransactions = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$dates['start'], $dates['end']])
                ->where('orders.payment_status', 'PAID');

            if (!$isAdmin) {
                $queryTransactions->where('orders.user_id', $userId);
            }

            $salesAggregates = $queryTransactions
                ->selectRaw('COALESCE(SUM(transaction_details.quantity), 0) as total_qty')
                ->first();

            // Query 3: Pengeluaran & Top Up Kas
            $queryExpenses = \App\Models\Expense::whereBetween('expense_date', [$dates['start']->format('Y-m-d'), $dates['end']->format('Y-m-d')]);
            if (!$isAdmin) {
                $queryExpenses->where('user_id', $userId);
            }
            $expensesAggregates = $queryExpenses->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                COALESCE(SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END), 0) as total_in
            ")->first();

            $total_out   = (float) ($expensesAggregates->total_out ?? 0);
            $total_in    = (float) ($expensesAggregates->total_in ?? 0);
            $sales_omzet = (float) ($ordersAggregates->total_sales ?? 0);

            $result = [
                'totalOrders'       => (int) ($ordersAggregates->total_orders ?? 0),
                'totalOmzet'        => $sales_omzet,                              // Pendapatan murni penjualan
                'totalNetCash'      => $sales_omzet + $total_in - $total_out,     // Saldo kas bersih
                'totalExpenses'     => $total_out,
                'totalTopUps'       => $total_in,
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
        $this->totalOmzet        = $stats['totalOmzet'];
        $this->totalNetCash      = $stats['totalNetCash'];
        $this->totalExpenses     = $stats['totalExpenses'];
        $this->totalTopUps       = $stats['totalTopUps'];
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
