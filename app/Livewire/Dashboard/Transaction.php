<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class Transaction extends Component
{
        // List Transaction
        public $transactions, $totalTransactions;

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
            $ttl = 31536000; // TTL cache selama 1 tahun
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
                        'orders.created_at as order_date',
                        'users.name as user_name',
                        'products.name as product_name'
                    )
                    ->when($this->search, function ($query) {
                        $query->where('orders.id', 'like', '%' . $this->search . '%')
                            ->orWhereDate('orders.created_at', '=', $this->search)
                            ->orWhere('users.name', 'like', '%' . $this->search . '%');
                    })
                    ->orderBy('orders.created_at', 'desc')
                    ->limit($this->limit)
                    ->get();
            });

            // Debug: Log query yang dieksekusi
            DB::listen(function ($query) {
                Log::info('SQL Query:', ['sql' => $query->sql, 'bindings' => $query->bindings]);
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

        public function render()
        {
            return view('livewire.dashboard.transaction');
        }
}
