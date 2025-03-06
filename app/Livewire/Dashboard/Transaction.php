<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransactionExport;
use App\Jobs\GenerateTransactionReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
    
        public function updatingSearch()
        {
            $this->limit = 8;
        }
    
        public function updatedSearch()
        {
            $this->loadInitialTransactions();
        }
        
        public function loadInitialTransactions()
        {
            sleep(5);
            $ttl = 31536000; // Cache selama 1 tahun
            $this->loaded = true;

            // Cache key unik berdasarkan search dan limit
            $cacheKey = "transactions_{$this->search}_{$this->limit}";

            // Simpan daftar cache key untuk memudahkan refresh nanti
            $cacheKeys = Cache::get('transaction_cache_keys', []);
            if (!in_array($cacheKey, $cacheKeys)) {
                $cacheKeys[] = $cacheKey;
                Cache::put('transaction_cache_keys', $cacheKeys, $ttl);
            }

            // Ambil transaksi dari cache atau query database jika tidak ada
            $transactions = Cache::remember($cacheKey, $ttl, function () {
                return DB::table('transaction_details')
                    ->join('orders', 'transaction_details.order_id', '=', 'orders.id')
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->join('products', 'transaction_details.product_id', '=', 'products.id')
                    ->select(
                        'transaction_details.*',
                        'orders.id as order_id',
                        'orders.payment_method as payment_method',
                        'orders.tax as tax',
                        'orders.discount as discount',
                        'orders.customer_money as customer_money',
                        'orders.change as change',
                        'orders.grandtotal as grand_total',
                        'orders.created_at as order_date',
                        'users.name as user_name',
                        'products.name as product_name'
                    )
                    ->when($this->search, function ($query) {
                        $query->where('orders.id', 'like', '%' . $this->search . '%')
                            ->orWhereDate('orders.created_at', '=', $this->search)
                            ->orWhere('users.name', 'like', '%' . $this->search . '%')
                            ->orWhere('orders.payment_method', 'like', '%' . $this->search . '%');
                    })
                    ->orderBy('orders.created_at', 'desc')
                    ->limit($this->limit)
                    ->get();
            });

            // Pastikan data dikelompokkan berdasarkan order_id
            $this->transactions = collect($transactions)->groupBy('order_id')->map(fn ($group) => $group->all());
        }
        

    
        public function loadMore()
        {
            $oldCount = $this->transactions->flatten()->count();
            $this->limit += 8;
            $this->loadInitialTransactions();
        
            if ($this->transactions->flatten()->count() <= $oldCount) {
                Log::info("No more data to load");
            }
        }
    
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
    
        public function exportPdf()
        {
            $this->validate([
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);

            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            $date = now()->format('d-m-Y'); 

            $transactions = TransactionDetail::with(['product', 'order.user'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->groupBy('order_id'); // Kelompokkan berdasarkan order_id

            $pdf = Pdf::loadView('exports.transactions', [
                'transactions' => $transactions,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ]);
            
            return response()->streamDownload(fn() => print($pdf->output()), "transactions_{$date}.pdf");
        }

        
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
