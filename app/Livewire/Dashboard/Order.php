<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Component
{
    public $search = '';
    public $products = [];
    public $limitProducts = 5;
    public $cart = [];
    public $customerMoney = 0;
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $change = 0;

    protected $listeners = [
        'refreshProductStock' => 'searchProduct',
    ];

    public function searchProduct()
    {
        $ttl = 31536000;
        $cacheKey = "products_{$this->search}_8";

        $this->products = Cache::remember($cacheKey, $ttl, function () {
            return Product::where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('price', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('sold_count') // 🔥 Urutkan berdasarkan produk terlaris
                ->take($this->limitProducts) // Batas produk yang akan ditampilkan
                ->get();
        });
    }



    // Tambahkan produk ke keranjang (Cache)
    public function addToCart($productId)
    {
        $product = Product::findOrFail($productId); 
        if ($product) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price, // Tambahkan subtotal di awal
            ];
            $this->calculateTotal();
        }
    }

    public function updatedCart($value, $key)
    {
        list($index, $field) = explode('.', $key);
    
        if ($field === 'quantity' && isset($this->cart[$index])) {
            $this->updateQuantity($index, $value);
        }
    }
    
    public function updateQuantity($index, $quantity)
    {
        if (isset($this->cart[$index])) {
            $quantity = max(1, (int) $quantity); // Pastikan quantity minimal 1
            $this->cart[$index]['quantity'] = $quantity;
            $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['price'];
            $this->calculateTotal();
        }
    }
    
    // Hapus produk dari keranjang
    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
            $this->calculateTotal();
            $this->customerMoney = 0;
            $this->change = 0;
        }
    }

    // Hitung subtotal, PPN, dan total
    public function calculateTotal()
    {
        $this->subtotal = array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $this->tax = $this->subtotal * 0.11;
        $this->total = $this->subtotal + $this->tax;
    }

    // Hitung kembalian
    public function calculateChange()
    {
        $this->customerMoney = (float) $this->customerMoney;

        if ($this->customerMoney > $this->total) {
            $this->change = $this->customerMoney - $this->total;
        } elseif ($this->customerMoney < $this->total) {
            $this->change = 0;
        } elseif ($this->customerMoney === 0) {
            $this->change = 0;
        }
    }

    // Proses Order
    public function processOrder()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $customerMoney = (float) $this->customerMoney;
        $total = (float) $this->total;

        if ($customerMoney < $total) {
            $shortage = $total - $customerMoney;
            $this->dispatch('insufficientPayment', $shortage);
            return;
        }

        DB::beginTransaction();
        try {
            $productUpdates = [];
            $insufficientProducts = [];

            // 1. Ambil semua produk dalam satu query
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::with('supplier')->whereIn('id', $productIds)->get()->keyBy('id');

            // 2. Periksa stok sebelum memproses order
            foreach ($this->cart as $item) {
                if (!isset($products[$item['id']]) || $products[$item['id']]->stock < $item['quantity']) {
                    $insufficientProducts[] = $products[$item['id']]->name ?? 'Produk Tidak Ditemukan';
                }
            }

            if (!empty($insufficientProducts)) {
                DB::rollBack();
                $this->dispatch('insufficientStock', $insufficientProducts);
                return;
            }

            // 3. Simpan data order (tanpa detail produk)
            $order = ModelsOrder::create([
                'tax' => $this->tax,
                'discount' => 0,
                'customer_money' => $this->customerMoney,
                'change' => $this->change,
                'grandtotal' => $this->total,
            ]);

            $transactionDetails = [];
                foreach ($this->cart as $item) {
                    $transactionDetails[] = [
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $productUpdates[$item['id']] = $item['quantity'];
                }

                // Gunakan batch insert
                TransactionDetail::insert($transactionDetails);


            $this->calculateChange();

            $updateQuery = "UPDATE products SET stock = CASE";
            $updateSoldCount = ", sold_count = CASE"; // Gabungkan update stock & sold_count
            
            foreach ($productUpdates as $productId => $quantity) {
                $updateQuery .= " WHEN id = $productId THEN stock - $quantity";
                $updateSoldCount .= " WHEN id = $productId THEN sold_count + $quantity"; 
            }
            $updateQuery .= " END" . $updateSoldCount . " END WHERE id IN (" . implode(',', array_keys($productUpdates)) . ")";
            
            DB::statement($updateQuery);
            


            DB::commit();
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();

            // 6. Dispatch event ke frontend
            $this->dispatch('refreshProductStock');
            $this->dispatch('successPayment');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('errorPayment');
        }
    }

    // Reset keranjang
    public function resetCart()
    {
        $this->cart = [];
        $this->subtotal = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->customerMoney = 0;
        $this->change = 0;
    }


    public function refreshCacheTransactionDetail()
    {
        // Ambil daftar semua cache key transaksi
        $cacheKeys = Cache::get('transaction_cache_keys', []);

        // Hapus cache transaksi terkait
        foreach ($cacheKeys as $cacheKey) {
            Cache::forget($cacheKey);
        }

        // Hapus cache total transaksi jika diperlukan
        Cache::forget('totalTransactions');

        // Untuk lebih memastikan cache total transaksi diperbarui, kamu bisa melakukan reset cache lainnya jika diperlukan
    }
    
    // Refresh Cache saat update stok produk
    protected function refreshCacheStock()
    {
          $ttl = 31536000; // TTL cache selama 1 tahun
  
          // Perbarui cache produk sesuai pencarian (opsional, jika perlu di-refresh seluruhnya)
          $cacheKey = "products_{$this->search}_8";
          Cache::put($cacheKey, Product::where(function ($query) {
                  $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('price', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
              })
              ->orderByDesc('sold_count')
              ->take($this->limitProducts)
              ->get(), $ttl);
    }

    public function render()
    {
        return view('livewire.dashboard.order');
    }
}
