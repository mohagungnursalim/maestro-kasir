<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\TransactionDetail;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Concurrency;
use Livewire\Component;
use Livewire\Attributes\Url;

class Dashboard extends Component
{
    public $totalOrders;
    public $totalOmzet;      // Pendapatan murni dari penjualan
    public $totalQris;       // Pendapatan QRIS
    public $totalTunai;      // Pendapatan Tunai (CASH)
    public $uangKeuntungan;  // Profit kotor = Omzet - Pengeluaran
    public $uangLaciKasir;   // Uang fisik di laci (Omzet Tunai + Top Up - Pengeluaran)
    public $totalProductsSold;
    public $totalExpenses;
    public $totalTopUps;

    // New metrics
    public $avgOrderValue  = 0;   // AOV: rata-rata belanja per transaksi
    public $avgDiscount    = 0;   // Rata-rata diskon per order
    public $yesterdayOmzet = 0;   // Omzet hari kemarin (hanya untuk filter 'today')
    public $omzetGrowth    = null; // Persentase pertumbuhan vs periode sebelumnya (null = N/A)

    // Point 1: Piutang / Belum Lunas
    public $totalUnpaidOrders  = 0;  // Jumlah order belum lunas
    public $totalUnpaidAmount  = 0;  // Total nominal piutang

    // Point 2: Rasio Tipe Order
    public $orderTypeSplit = [];  // ['dine_in' => [...], 'take_away' => [...], ...]

    // Point 5: Transaksi Terbaru
    public $recentTransactions = [];  // 10 transaksi PAID terbaru

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
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($dates, $isAdmin, $userId, $activeBranch) {

            $prevDates = $this->getPreviousPeriodRange($this->filterType, $dates['start'], $dates['end']);

            $results = Concurrency::run([
                // 1. ordersAggregates
                function () use ($dates, $isAdmin, $userId) {
                    $query = \App\Models\Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                        ->where('payment_status', 'PAID');
                    if (!$isAdmin) $query->where('user_id', $userId);
                    return $query->selectRaw("
                        COUNT(id) as total_orders,
                        COALESCE(SUM(grandtotal), 0) as total_sales,
                        COALESCE(SUM(CASE WHEN upper(payment_method) = 'QRIS' THEN grandtotal ELSE 0 END), 0) as total_qris,
                        COALESCE(SUM(CASE WHEN upper(payment_method) IN ('CASH', 'TUNAI') THEN grandtotal ELSE 0 END), 0) as total_cash,
                        COALESCE(AVG(grandtotal), 0) as avg_order_value,
                        COALESCE(AVG(discount), 0) as avg_discount
                    ")->first();
                },
                
                // 2. salesAggregates
                function () use ($dates, $isAdmin, $userId) {
                    $query = \App\Models\TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                        ->whereBetween('orders.created_at', [$dates['start'], $dates['end']])
                        ->where('orders.payment_status', 'PAID');
                    if (!$isAdmin) $query->where('orders.user_id', $userId);
                    return $query->selectRaw('COALESCE(SUM(transaction_details.quantity), 0) as total_qty')->first();
                },

                // 3. expensesAggregates
                function () use ($dates, $isAdmin, $userId) {
                    $startStr = $dates['start']->format('Y-m-d');
                    $endStr   = $dates['end']->format('Y-m-d');
                    $query = \App\Models\Expense::whereBetween('expense_date', [$startStr, $endStr]);
                    if (!$isAdmin) $query->where('user_id', $userId);
                    return $query->selectRaw("
                        COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                        COALESCE(SUM(CASE WHEN type = 'out' AND category = 'Komisi Aplikasi' THEN amount ELSE 0 END), 0) as total_komisi,
                        COALESCE(SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END), 0) as total_in
                    ")->first();
                },

                // 4. orderTypeSplitRaw
                function () use ($dates, $isAdmin, $userId) {
                    $query = \App\Models\Order::whereBetween('created_at', [$dates['start'], $dates['end']])
                        ->where('payment_status', 'PAID');
                    if (!$isAdmin) $query->where('user_id', $userId);
                    return $query->selectRaw("
                        UPPER(order_type) as order_type,
                        COUNT(id) as total_count,
                        COALESCE(SUM(grandtotal), 0) as total_omzet
                    ")->groupBy('order_type')->get();
                },

                // 5. prevOmzet
                function () use ($prevDates, $isAdmin, $userId, $activeBranch) {
                    $query = \App\Models\Order::whereBetween('created_at', [$prevDates['start'], $prevDates['end']])
                        ->where('payment_status', 'PAID');
                    if (!$isAdmin) $query->where('user_id', $userId);
                    if ($activeBranch !== 'all' && $activeBranch !== null) {
                        $query->where('branch_id', $activeBranch);
                    }
                    return (float) ($query->sum('grandtotal') ?? 0);
                },

                // 6. recentTransactions
                function () use ($dates, $isAdmin, $userId) {
                    $query = \App\Models\Order::with(['user:id,name'])
                        ->whereBetween('created_at', [$dates['start'], $dates['end']])
                        ->where('payment_status', 'PAID')
                        ->orderByDesc('created_at')
                        ->limit(10);
                    if (!$isAdmin) $query->where('user_id', $userId);
                    return $query->get([
                        'id', 'order_number', 'grandtotal', 'payment_method',
                        'order_type', 'created_at', 'user_id', 'discount',
                    ])->toArray();
                },

                // 7. visitorAggregates
                function () use ($dates, $isAdmin) {
                    if (!$isAdmin) return null;
                    return \App\Models\Visitor::whereBetween('visited_at', [$dates['start'], $dates['end']])
                        ->selectRaw('COUNT(*) as total_views, COUNT(DISTINCT ip_address) as unique_visitors')
                        ->first();
                }
            ]);

            $ordersAggregates   = $results[0];
            $salesAggregates    = $results[1];
            $expensesAggregates = $results[2];
            $orderTypeSplitRaw  = $results[3];
            $prevOmzet          = $results[4];
            $recentTransactions = $results[5];
            $visitorAggregates  = $results[6];

            $total_out   = (float) ($expensesAggregates->total_out ?? 0);
            $total_in    = (float) ($expensesAggregates->total_in ?? 0);
            
            // Komisi Aplikasi is a digital deduction, so it shouldn't mathematically leave the physical cash drawer
            $real_cash_out = $total_out - (float) ($expensesAggregates->total_komisi ?? 0);
            
            $sales_omzet = (float) ($ordersAggregates->total_sales ?? 0);

            $orderTypeSplit = [];
            $totalOrdersForSplit = max((int) ($ordersAggregates->total_orders ?? 0), 1);
            foreach ($orderTypeSplitRaw as $row) {
                if (empty($row->order_type)) continue; // skip null/kosong, tidak ada key fallback
                $key = strtoupper($row->order_type);
                $orderTypeSplit[$key] = [
                    'count'      => (int) $row->total_count,
                    'omzet'      => (float) $row->total_omzet,
                    'percentage' => round(($row->total_count / $totalOrdersForSplit) * 100, 1),
                ];
            }

            $uang_laci_kasir = (float) ($ordersAggregates->total_cash ?? 0) + $total_in - $total_out;

            $result = [
                'totalOrders'       => (int) ($ordersAggregates->total_orders ?? 0),
                'totalOmzet'        => $sales_omzet,
                'totalQris'         => (float) ($ordersAggregates->total_qris ?? 0),
                'totalTunai'        => (float) ($ordersAggregates->total_cash ?? 0),
                'uangKeuntungan'    => $sales_omzet - $total_out,
                'uangLaciKasir'     => $uang_laci_kasir,
                'totalExpenses'     => $total_out,
                'totalTopUps'       => $total_in,
                'totalProductsSold' => (int) ($salesAggregates->total_qty ?? 0),
                'avgOrderValue'     => (float) ($ordersAggregates->avg_order_value ?? 0),
                'avgDiscount'       => (float) ($ordersAggregates->avg_discount ?? 0),
                'orderTypeSplit'    => $orderTypeSplit,
                'yesterdayOmzet'    => $prevOmzet,
                'omzetGrowth'       => $prevOmzet > 0
                    ? round((($sales_omzet - $prevOmzet) / $prevOmzet) * 100, 1)
                    : ($sales_omzet > 0 ? 100.0 : null),
                'recentTransactions'=> $recentTransactions,
            ];

            // Query 7 (admin only): Stats Pengunjung
            if ($isAdmin) {
                $result['totalPageViews']      = (int) ($visitorAggregates->total_views ?? 0);
                $result['totalUniqueVisitors'] = (int) ($visitorAggregates->unique_visitors ?? 0);
            }

            return $result;
        });

        // Assign ke state Livewire
        $this->totalOrders        = $stats['totalOrders'];
        $this->totalOmzet         = $stats['totalOmzet'];
        $this->totalQris          = $stats['totalQris'] ?? 0;
        $this->totalTunai         = $stats['totalTunai'] ?? 0;
        $this->uangKeuntungan     = $stats['uangKeuntungan'];
        $this->uangLaciKasir      = $stats['uangLaciKasir'] ?? 0;
        $this->totalExpenses      = $stats['totalExpenses'];
        $this->totalTopUps        = $stats['totalTopUps'];
        $this->totalProductsSold  = $stats['totalProductsSold'];
        $this->avgOrderValue      = $stats['avgOrderValue'] ?? 0;
        $this->avgDiscount        = $stats['avgDiscount'] ?? 0;
        $this->yesterdayOmzet     = $stats['yesterdayOmzet'] ?? 0;
        $this->omzetGrowth        = $stats['omzetGrowth'] ?? null;
        $this->orderTypeSplit     = $stats['orderTypeSplit'] ?? [];
        $this->recentTransactions = $stats['recentTransactions'] ?? [];

        if ($isAdmin) {
            $this->totalPageViews      = $stats['totalPageViews'] ?? 0;
            $this->totalUniqueVisitors = $stats['totalUniqueVisitors'] ?? 0;
        }

        // Point 1: Piutang — cache key terpisah (tidak terikat filter tanggal),
        // tapi ikut transaction_cache_version → invalidate otomatis saat ada perubahan transaksi.
        $unpaidCacheKey = sprintf(
            'dashboard_unpaid:%s:tv%s:br%s',
            $isAdmin ? 'admin' : 'user_' . $userId,
            $transactionVersion,
            $activeBranch
        );

        $unpaidStats = Cache::remember($unpaidCacheKey, now()->addMinutes(5), function () use ($isAdmin, $userId) {
            $unpaidQuery = Order::where('payment_status', 'UNPAID');
            if (!$isAdmin) {
                $unpaidQuery->where('user_id', $userId);
            }
            return $unpaidQuery->selectRaw(
                'COUNT(id) as total_count, COALESCE(SUM(grandtotal), 0) as total_amount'
            )->first();
        });

        $this->totalUnpaidOrders = (int) ($unpaidStats->total_count ?? 0);
        $this->totalUnpaidAmount = (float) ($unpaidStats->total_amount ?? 0);
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
        // Emits events to SPA update charts without reload
        $this->dispatch('globalFilterUpdated', $this->filterType);
        $this->dispatch('global-filter-updated', filter: $this->filterType);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard');
    }
}
