<?php

namespace App\Livewire\Dashboard;

use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Cache;
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

            // Ambil transaksi dari cache, jika tidak ada maka query database
            $transactions = Cache::remember($cacheKey, $ttl, function () {
                $data = TransactionDetail::with(['product','order']) // Pastikan relasi product di-load
                    ->where(function ($query) {
                        $query->where('order_id', 'like', '%' . $this->search . '%')
                            ->orWhere('created_at', 'like', '%' . $this->search . '%');
                    })
                    ->latest()
                    ->take($this->limit)
                    ->get();

                // Debug: Pastikan hasil dari query bukan boolean
                if (!is_a($data, \Illuminate\Support\Collection::class)) {
                    return collect(); // Hindari menyimpan nilai boolean atau data salah
                }

                return $data;
            });

            // Debug: Pastikan hasil transaksi valid sebelum digunakan
            if ($transactions->isEmpty()) {
                Log::warning("Transactions are empty after cache retrieval: {$cacheKey}");
            }

            // Pastikan data dikelompokkan dengan benar
            $this->transactions = $transactions->groupBy('order_id')->map(function ($group) {
                return $group->all(); // Pastikan tetap dalam bentuk array yang bisa diakses
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
