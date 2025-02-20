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
                return TransactionDetail::query()
                    ->with([
                        'product', // Pastikan product ikut dimuat
                        'order.user' // Pastikan order dan user dalam order ikut dimuat
                    ])
                    ->whereHas('order', function ($query) {
                        $query->where('id', 'like', '%' . $this->search . '%')
                            ->orWhereDate('created_at', '=', $this->search) // Menggunakan '=' agar sesuai format tanggal
                            ->orWhereHas('user', function ($userQuery) { // Cari berdasarkan nama user
                                $userQuery->where('name', 'like', '%' . $this->search . '%');
                            });
                    })
                    ->latest()
                    ->take($this->limit)
                    ->get();
            });
        
            // Debug: Pastikan hasil dari cache adalah Collection dan bukan array
            if (!($transactions instanceof \Illuminate\Support\Collection)) {
                Log::error("Cache returned unexpected data type: ", ['data' => $transactions]);
                $transactions = collect(); // Pastikan tetap Collection kosong jika terjadi kesalahan
            }
        
            // Debug: Pastikan setiap item tetap instance dari TransactionDetail
            $transactions = $transactions->map(function ($transaction) {
                return is_array($transaction) ? new TransactionDetail($transaction) : $transaction;
            });
        
            // Pastikan data dikelompokkan dengan benar
            $this->transactions = $transactions->groupBy('order_id')->map(fn ($group) => $group->all());
        
            // Debugging Query untuk memastikan tidak ada N+1
            DB::listen(function ($query) {
                Log::info('SQL Query:', ['sql' => $query->sql, 'bindings' => $query->bindings]);
            });
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
