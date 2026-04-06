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
    public $totalQris;       // Pendapatan QRIS
    public $totalTunai;      // Pendapatan Tunai (CASH)
    public $uangKeuntungan;  // Profit kotor = Omzet - Pengeluaran
    public $uangDiLaci;      // Uang fisik di kasir = Tunai + Top Up - Pengeluaran
    public $totalProductsSold;
    public $totalExpenses;
    public $totalTopUps;

    // New metrics
    public $avgOrderValue  = 0;   // AOV: rata-rata belanja per transaksi
    public $avgDiscount    = 0;   // Rata-rata diskon per order
    public $yesterdayOmzet = 0;   // Omzet hari kemarin (hanya untuk filter 'today')
    public $omzetGrowth    = null; // Persentase pertumbuhan vs periode sebelumnya (null = N/A)

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

        // Sertakan versi cache dari produk, transaksi, dan expense agar dashboard otomatis direfresh
        $productVersion     = Cache::get('product_cache_version', 1);
        $transactionVersion = Cache::get('transaction_cache_version', 1);
        $expenseVersion     = Cache::get('expense_cache_version', 1);
        $activeBranch       = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');

        // Key cache unik per filter + range + role/user + versi + branch active
        $cacheKey = sprintf(
            'dashboard_stats:%s:%s:%s:%s:pv%s:tv%s:ev%s:br%s',
            $this->filterType,
            $dates['start']->format('YmdHis'),
            $dates['end']->format('YmdHis'),
            $isAdmin ? 'admin' : 'user_' . $userId,
            $productVersion,
            $transactionVersion,
            $expenseVersion,
            $activeBranch
        );

        // Semua stats (termasuk visitor jika admin) dalam 1 cache block
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dates, $isAdmin, $userId) {

            // Query 1: Total orders (single count) & Total Pendapatan Bersih (Grandtotal = dengan diskon & pajak)
            $queryOrders = Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                ->where('payment_status', 'PAID');

            if (!$isAdmin) {
                $queryOrders->where('user_id', $userId);
            }
            
            $ordersAggregates = (clone $queryOrders)->selectRaw("
                COUNT(id) as total_orders,
                COALESCE(SUM(grandtotal), 0) as total_sales,
                COALESCE(SUM(CASE WHEN upper(payment_method) = 'QRIS' THEN grandtotal ELSE 0 END), 0) as total_qris,
                COALESCE(SUM(CASE WHEN upper(payment_method) IN ('CASH', 'TUNAI') THEN grandtotal ELSE 0 END), 0) as total_cash,
                COALESCE(AVG(grandtotal), 0) as avg_order_value,
                COALESCE(AVG(discount), 0) as avg_discount
            ")->first();

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
                'totalOmzet'        => $sales_omzet,
                'totalQris'         => (float) ($ordersAggregates->total_qris ?? 0),
                'totalTunai'        => (float) ($ordersAggregates->total_cash ?? 0),
                'uangKeuntungan'    => $sales_omzet - $total_out,
                'uangDiLaci'        => (float) ($ordersAggregates->total_cash ?? 0) + $total_in - $total_out,
                'totalExpenses'     => $total_out,
                'totalTopUps'       => $total_in,
                'totalProductsSold' => (int) ($salesAggregates->total_qty ?? 0),
                'avgOrderValue'     => (float) ($ordersAggregates->avg_order_value ?? 0),
                'avgDiscount'       => (float) ($ordersAggregates->avg_discount ?? 0),
            ];

            // Query 4: Yesterday / previous period comparison
            $prevDates = $this->getPreviousPeriodRange($this->filterType, $dates['start'], $dates['end']);
            $prevQuery = Order::whereBetween('created_at', [$prevDates['start'], $prevDates['end']])
                ->where('payment_status', 'PAID');
            if (!$isAdmin) {
                $prevQuery->where('user_id', $userId);
            }
            if (session()->has('active_branch_id')) {
                $prevQuery->where('branch_id', session('active_branch_id'));
            }
            $prevOmzet = (float) ($prevQuery->sum('grandtotal') ?? 0);
            $result['yesterdayOmzet'] = $prevOmzet;
            $result['omzetGrowth']    = $prevOmzet > 0
                ? round((($sales_omzet - $prevOmzet) / $prevOmzet) * 100, 1)
                : ($sales_omzet > 0 ? 100.0 : null);

            // Query 5 (admin only): Stats Pengunjung
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
        $this->totalQris         = $stats['totalQris'] ?? 0;
        $this->totalTunai        = $stats['totalTunai'] ?? 0;
        $this->uangKeuntungan    = $stats['uangKeuntungan'];
        $this->uangDiLaci        = $stats['uangDiLaci'];
        $this->totalExpenses     = $stats['totalExpenses'];
        $this->totalTopUps       = $stats['totalTopUps'];
        $this->totalProductsSold = $stats['totalProductsSold'];
        $this->avgOrderValue     = $stats['avgOrderValue'] ?? 0;
        $this->avgDiscount       = $stats['avgDiscount'] ?? 0;
        $this->yesterdayOmzet    = $stats['yesterdayOmzet'] ?? 0;
        $this->omzetGrowth       = $stats['omzetGrowth'] ?? null;

        if ($isAdmin) {
            $this->totalPageViews      = $stats['totalPageViews'] ?? 0;
            $this->totalUniqueVisitors = $stats['totalUniqueVisitors'] ?? 0;
        }
    }

    // Mengambil rentang tanggal berdasarkan filter yang dipilih
    public function getDateRange($type)
    {
        return match ($type) {
            'week'  => ['start' => Carbon::now()->startOfWeek(),  'end' => Carbon::now()->endOfWeek()],
            'month' => ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()],
            'year'  => ['start' => Carbon::now()->startOfYear(),  'end' => Carbon::now()->endOfYear()],
            default => ['start' => Carbon::today(),               'end' => Carbon::today()->endOfDay()],
        };
    }

    /**
     * Mengembalikan rentang periode sebelumnya untuk perbandingan (kemarin, minggu lalu, dst.)
     */
    protected function getPreviousPeriodRange(string $type, $currentStart, $currentEnd): array
    {
        $diff = $currentStart->diffInSeconds($currentEnd);
        return [
            'start' => $currentStart->copy()->subSeconds($diff + 1)->startOfDay(),
            'end'   => $currentStart->copy()->subSecond(),
        ];
    }

    // Mengupdate statistik ketika filter diubah
    public function updatedFilterType()
    {
        $this->updateStats();
        $this->dispatch('globalFilterUpdated', filter: $this->filterType);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
