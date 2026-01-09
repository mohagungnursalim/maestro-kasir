<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransactionExport;
use App\Jobs\GenerateTransactionReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class Transaction extends Component
{
        // List Transaction
        public $transactions, $totalTransactions;
        
        // Date Export
        public $startDate;
        public $endDate;


        // Search
        #[Url()]
        public $search = '';
        public $limit = 8; 
        public $loaded = false;
    
        public function mount()
        {
            $ttl = 31536000;
    
            $this->totalTransactions = Cache::remember('totalTransactions', $ttl, function () {
                return TransactionDetail::count();
            });
             
            $this->transactions = collect();
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
            $ttl = 31536000; // Cache selama 1 tahun
            $this->loaded = true;
        
            $user = Auth::user();
            $userId = $user->id;
            $isAdminOrOwner = $user->hasRole('admin|owner');
        
            // Cache key unik berdasarkan search, limit, dan user ID (kalau bukan admin/owner)
            $cacheKey = $isAdminOrOwner 
                ? "transactions_{$this->search}_{$this->limit}" 
                : "transactions_user_{$userId}_{$this->search}_{$this->limit}";
        
            // Simpan daftar cache key untuk refresh nanti
            $cacheKeys = Cache::get('transaction_cache_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('transaction_cache_keys', $cacheKeys, $ttl);
            }
        
            $transactions = Cache::remember($cacheKey, $ttl, function () use ($userId, $isAdminOrOwner) {
                return DB::table('transaction_details')
                    ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->join('products', 'transaction_details.product_id', '=', 'products.id')
                    ->select(
                        'transaction_details.*',
                        'orders.order_number as order_number',
                        'orders.payment_method as payment_method',
                        'orders.tax as tax',
                        'orders.customer_money as customer_money',
                        'orders.change as change',
                        'orders.grandtotal as grand_total',
                        'orders.created_at as order_date',
                        'users.name as user_name',
                        'products.name as product_name'
                    )
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
            
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            $date = now()->format('d-m-Y'); 
        
            return Excel::download(new TransactionExport($startDate,$endDate), "transactions_{$date}.xlsx");
        }
    
        // Export PDF
        public function exportPdf()
        {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);
        
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); // 23:59:59
            $date = now()->format('d-m-Y');
            $userName = auth()->user()->name;
            
            $transactions = TransactionDetail::with(['product', 'order.user'])
                ->whereHas('order', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->orderBy('order_id', 'desc')
                ->get()
                ->groupBy('order_id'); 
        
           
            $pdf = Pdf::loadView('exports.transactions', [
                'transactions' => $transactions,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'userName' => $userName,
            ]);
        
            return response()->streamDownload(fn() => print($pdf->output()), "transactions_{$date}.pdf");            
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
    
            GenerateTransactionReport::dispatch($startDate, $endDate, $type);

            $this->dispatch('notify');

        }

        public function render()
        {
            return view('livewire.dashboard.transaction');
        }
}
