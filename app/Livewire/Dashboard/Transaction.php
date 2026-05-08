<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransactionWorkbook;
use App\Jobs\GenerateTransactionReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class Transaction extends Component
{
        // List Transaction
        public $transactions, $totalTransactions;
        
        // Date Export
        public $startDate;
        public $endDate;
        // Validity: date range within 1 year
        public $isDateRangeWithinYear = true;


        // Search
        #[Url()]
        public $search = '';
        public $limit = 8; 
        public $loaded = false;

        public $ttl;
    
        public function mount()
        {
            $this->ttl = 3600;   // 1 jam (3600 detik integer format)
    
            $version = Cache::get('transaction_cache_version', 1);
            $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
            
            $this->totalTransactions = Cache::remember("totalTransactions_paid_br{$activeBranch}_v{$version}", $this->ttl, function () {
                return TransactionDetail::whereHas('order', fn ($q) => $q->where('payment_status', 'PAID'))->count();
            });
             
            $this->transactions = collect();
            $this->updateDateRangeValidity();
        }

        public function updatedStartDate()
        {
            $this->updateDateRangeValidity();
        }

        public function updatedEndDate()
        {
            $this->updateDateRangeValidity();
        }

        protected function updateDateRangeValidity()
        {
            if (!$this->startDate || !$this->endDate) {
                $this->isDateRangeWithinYear = true;
                return;
            }

            try {
                $start = Carbon::parse($this->startDate);
                $end = Carbon::parse($this->endDate);
                // Allow up to 365 days (approximately one year)
                $this->isDateRangeWithinYear = $start->diffInDays($end) <= 365;
            } catch (\Exception $e) {
                $this->isDateRangeWithinYear = false;
            }
        }
    
        // Update Search
        public function updatingSearch()
        {
            $this->limit = 8;
        }
    
        // Update Search
        public function updatedSearch()
        {
            $this->loadInitialTransactions();
        }
        
        // Load Initial Transactions
        public function loadInitialTransactions()
        {
            $ttl = $this->ttl;
            $this->loaded = true;
        
            $user = Auth::user();
            $userId = $user->id;
            $isAdminOrOwner = $user->hasRole('admin|owner');
        
            // Cache key unik berdasarkan versioning, search hash, limit, dan user ID (kalau bukan admin/owner)
            $version = Cache::get('transaction_cache_version', 1);
            $searchHash = md5($this->search);
            $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
            
            $cacheKey = $isAdminOrOwner 
                ? "transactions_br{$activeBranch}_v{$version}_{$searchHash}_{$this->limit}" 
                : "transactions_user_br{$activeBranch}_v{$version}_{$userId}_{$searchHash}_{$this->limit}";
        
            $transactions = Cache::remember($cacheKey, $ttl, function () use ($userId, $isAdminOrOwner) {
                return DB::table('transaction_details')
                    ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->join('products', 'transaction_details.product_id', '=', 'products.id')
                    ->select(
                        'transaction_details.*',
                        'orders.order_number as order_number',
                        'orders.payment_method as payment_method',
                        'orders.discount as discount',
                        'orders.tax as tax',
                        'orders.customer_money as customer_money',
                        'orders.change as change',
                        'orders.grandtotal as grand_total',
                        'orders.created_at as order_date',
                        'users.name as user_name',
                        'products.name as product_name',
                        'products.price as product_price'
                    )
                    ->where('orders.payment_status', 'PAID')
                    ->when(session()->has('active_branch_id'), function ($query) {
                        $query->where('orders.branch_id', session('active_branch_id'));
                    })
                    ->when(!$isAdminOrOwner, function ($query) use ($userId) {
                        $query->where('orders.user_id', $userId);
                    })
                    ->when($this->search, function ($query) {
                        $query->where(function ($q) {
                            $q->where('orders.order_number', 'like', '%' . $this->search . '%')
                              ->orWhereDate('orders.created_at', '=', $this->search)
                              ->orWhere('users.name', 'like', '%' . $this->search . '%')
                              ->orWhere('orders.payment_method', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orderBy('orders.created_at', 'desc')
                    ->limit($this->limit)
                    ->get();
            });
        
            $this->transactions = collect($transactions)
                ->groupBy('order_id')
                ->map(fn ($group) => $group->all());
        }
        
        

        // Load More
        public function loadMore()
        {
            $oldCount = $this->transactions->flatten()->count();
            $this->limit += 8;
            $this->loadInitialTransactions();
        
            if ($this->transactions->flatten()->count() <= $oldCount) {
                Log::info("No more data to load");
            }
        }
    

        // Export Excel
        public function exportExcel()
        {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);

            if (!$this->isDateRangeWithinYear) {
                $this->addError('dateRange', 'Rentang tanggal tidak boleh lebih dari 1 tahun.');
                return;
            }
            
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            $date = now()->format('d-m-Y');
            $branchSlug = 'global';
            $activeBranchId = session('active_branch_id');
            if ($activeBranchId) {
                $branch = Branch::find($activeBranchId);
                $branchSlug = $branch ? Str::slug($branch->name) : 'cabang';
            }
        
            return Excel::download(new TransactionWorkbook($startDate, $endDate, $activeBranchId, auth()->user()->name), "transaksi_{$branchSlug}_{$date}.xlsx");
        }
    
        // Export PDF
        public function exportPdf()
        {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);

            if (!$this->isDateRangeWithinYear) {
                $this->addError('dateRange', 'Rentang tanggal tidak boleh lebih dari 1 tahun.');
                return;
            }
        
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); // 23:59:59
            $date = now()->format('d-m-Y');
            $branchSlug = 'global';
            $activeBranchId = session('active_branch_id');
            if ($activeBranchId) {
                $branch = Branch::find($activeBranchId);
                $branchSlug = $branch ? Str::slug($branch->name) : 'cabang';
            }
            $userName = auth()->user()->name;
            
            $transactions = TransactionDetail::with(['product', 'order.user', 'order.branch'])
                ->whereHas('order', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                          ->where('payment_status', 'PAID');
                })
                ->orderBy('order_id', 'desc')
                ->get()
                ->groupBy('order_id'); 

            // ── Dashboard-quality metrics ──────────────────────────────────────
            $orderQuery = \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId));

            $ordersAgg = (clone $orderQuery)->selectRaw("
                COUNT(id) as total_orders,
                COALESCE(SUM(grandtotal), 0) as total_sales,
                COALESCE(AVG(grandtotal), 0) as avg_order_value,
                COALESCE(AVG(discount), 0) as avg_discount
            ")->first();

            // Total Produk Terjual
            $totalProductsSold = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('orders.branch_id', $activeBranchId))
                ->sum('transaction_details.quantity');

            // Top Up Kas & Pengeluaran
            $expensesAgg = \App\Models\Expense::whereBetween('expense_date', [
                    $startDate->format('Y-m-d'), $endDate->format('Y-m-d')
                ])
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                    COALESCE(SUM(CASE WHEN type = 'in'  THEN amount ELSE 0 END), 0) as total_in
                ")->first();

            // Rasio Tipe Order
            $orderTypeSplitRaw = (clone $orderQuery)->selectRaw("
                UPPER(order_type) as order_type,
                COUNT(id) as total_count,
                COALESCE(SUM(grandtotal), 0) as total_omzet
            ")->groupBy('order_type')->get();

            $totalOrdersForSplit = max((int) ($ordersAgg->total_orders ?? 0), 1);
            $orderTypeSplit = [];
            foreach ($orderTypeSplitRaw as $row) {
                if (empty($row->order_type)) continue;
                $key = strtoupper($row->order_type);
                $orderTypeSplit[$key] = [
                    'count'      => (int) $row->total_count,
                    'omzet'      => (float) $row->total_omzet,
                    'percentage' => round(($row->total_count / $totalOrdersForSplit) * 100, 1),
                ];
            }

            // Piutang (unpaid orders — global, bukan per periode)
            $unpaidStats = \App\Models\Order::where('payment_status', 'UNPAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->selectRaw('COUNT(id) as total_count, COALESCE(SUM(grandtotal), 0) as total_amount')
                ->first();

            // Previous period comparison (growth)
            $periodDays = $startDate->diffInDays($endDate);
            $prevStart  = $startDate->copy()->subDays($periodDays + 1)->startOfDay();
            $prevEnd    = $startDate->copy()->subSecond();
            $prevOmzet  = (float) \App\Models\Order::whereBetween('created_at', [$prevStart, $prevEnd])
                ->where('payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->sum('grandtotal');

            $salesOmzet  = (float) ($ordersAgg->total_sales ?? 0);
            $omzetGrowth = $prevOmzet > 0
                ? round((($salesOmzet - $prevOmzet) / $prevOmzet) * 100, 1)
                : ($salesOmzet > 0 ? 100.0 : null);

            $dashboardStats = [
                'totalOrders'       => (int) ($ordersAgg->total_orders ?? 0),
                'totalProductsSold' => (int) $totalProductsSold,
                'avgOrderValue'     => (float) ($ordersAgg->avg_order_value ?? 0),
                'avgDiscount'       => (float) ($ordersAgg->avg_discount ?? 0),
                'totalTopUps'       => (float) ($expensesAgg->total_in ?? 0),
                'totalExpenseOut'   => (float) ($expensesAgg->total_out ?? 0),
                'orderTypeSplit'    => $orderTypeSplit,
                'unpaidOrders'      => (int) ($unpaidStats->total_count ?? 0),
                'unpaidAmount'      => (float) ($unpaidStats->total_amount ?? 0),
                'prevOmzet'         => $prevOmzet,
                'omzetGrowth'       => $omzetGrowth,
            ];

            $pdf = Pdf::loadView('exports.transactions', [
                'transactions'   => $transactions,
                'startDate'      => $this->startDate,
                'endDate'        => $this->endDate,
                'userName'       => $userName,
                'dashboardStats' => $dashboardStats,
            ]);
        
            return response()->streamDownload(fn() => print($pdf->output()), "transaksi_{$branchSlug}_{$date}.pdf");            
        }
         
        // Queue Report
        public function queueReport($type)
        {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);

            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            $userName = auth()->user()->name ?? 'Sistem';
    
            GenerateTransactionReport::dispatch($startDate, $endDate, $type, $userName, session('active_branch_id'));

            if (!$this->isDateRangeWithinYear) {
                $this->addError('dateRange', 'Rentang tanggal tidak boleh lebih dari 1 tahun.');
                return;
            }

            $this->dispatch('notify');

        }

        public function render()
        {
            return view('livewire.dashboard.transaction');
        }
}
