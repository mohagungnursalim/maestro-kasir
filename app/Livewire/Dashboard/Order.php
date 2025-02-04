<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Component
{
    public $search = '';
    public $products = [];
    public $limitProducts = 5;
    public $cart = [];
    public $customerMoney = null;
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $change = 0;

    protected $listeners = [
        'forceProcessOrder' => 'forceProcessOrder',
        'refreshProductStock' => 'searchProduct',
    ];

    // Pencarian produk (Cache)
    public function searchProduct()
    {
        $ttl = 31536000; // TTL cache selama 1 tahun
            
            // Ambil produk sesuai pencarian dan limit dari cache atau database
            $cacheKey = "products_{$this->search}_8";
            $this->products = Cache::remember($cacheKey, $ttl, function () {
                return Product::where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('sku', 'like', '%' . $this->search . '%')
                            ->orWhere('price', 'like', '%' . $this->search . '%')
                            ->orWhere('description', 'like', '%' . $this->search . '%');
                    })
                    ->latest()
                    ->take($this->limitProducts)
                    ->get();
            });
        
    }

    // Tambahkan produk ke keranjang (Cache)
    public function addToCart($productId)
    {
        $ttl = 31536000; // TTL cache selama 1 tahun

        // Kunci cache untuk menyimpan semua produk
        $cacheKey = "product_{$productId}";

        // Ambil produk dari cache atau database jika belum tersimpan
        $product = Cache::remember($cacheKey, $ttl, function () use ($productId) {
            return Product::findOrFail($productId);
        });

        // Pastikan produk ditemukan sebelum menambahkannya ke keranjang
        if ($product) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
            $this->calculateTotal();
        }
    }

    // Update quantity produk di keranjang
    public function updateQuantity($index, $quantity)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity'] = max(1, $quantity);
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
        }
    }

    // Hitung subtotal, PPN, dan total
    public function calculateTotal()
    {
        $this->subtotal = array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $this->tax = $this->subtotal * 0.11; // PPN 11%
        $this->total = $this->subtotal + $this->tax;
        
    }

    // Hitung kembalian
    public function calculateChange()
    {
        // Jika uang pelanggan lebih dari total, hitung kembalian
        if ($this->customerMoney > $this->total) {
            $this->change = $this->customerMoney - $this->total;
        }
        // Jika uang pelanggan kurang dari total, kembalian 0 dan hitung kekurangannya
        elseif ($this->customerMoney < $this->total) {
            $this->change = 0;
        }
        // Jika uang pelanggan null, set kembalian menjadi 0
        elseif ($this->customerMoney === null) {
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
            $orders = [];
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

            // 3. Proses order dalam satu batch
            foreach ($this->cart as $item) {
                $orders[] = [
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'tax' => $this->tax,
                    'discount' => 0,
                    'customer_money' => $this->customerMoney,
                    'change' => $this->change,
                    'grandtotal' => $this->total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $productUpdates[$item['id']] = $item['quantity'];
            }

            ModelsOrder::insert($orders);
            $this->calculateChange();

            //4. Kurangi stok dalam satu query
            $updateStockQuery = "UPDATE products SET stock = CASE";
            foreach ($productUpdates as $productId => $quantity) {
                $updateStockQuery .= " WHEN id = $productId THEN stock - $quantity";
            }
            $updateStockQuery .= " END WHERE id IN (" . implode(',', array_keys($productUpdates)) . ")";
            DB::statement($updateStockQuery);

            $this->refreshCache();
            DB::commit();

            //5. Dispatch event ke frontend
            $this->dispatch('refreshProductStock');
            $this->dispatch('successPayment');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('errorPayment');
        }
    }
    
    public function forceProcessOrder()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

    
        DB::beginTransaction();
        try {
            $orders = [];
            $productUpdates = [];
            $insufficientProducts = [];

            //Ambil semua produk dalam satu query
            $productIds = collect($this->cart)->pluck('id');

            //Proses order dalam satu batch
            foreach ($this->cart as $item) {
                $orders[] = [
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'tax' => $this->tax,
                    'discount' => 0,
                    'customer_money' => $this->customerMoney,
                    'change' => $this->change,
                    'grandtotal' => $this->total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $productUpdates[$item['id']] = $item['quantity'];
            }

            ModelsOrder::insert($orders);
            $this->calculateChange();

            //Kurangi stok dalam satu query
            $updateStockQuery = "UPDATE products SET stock = CASE";
            foreach ($productUpdates as $productId => $quantity) {
                $updateStockQuery .= " WHEN id = $productId THEN stock - $quantity";
            }
            $updateStockQuery .= " END WHERE id IN (" . implode(',', array_keys($productUpdates)) . ")";
            DB::statement($updateStockQuery);

            $this->refreshCache();
            DB::commit();

            //Dispatch event ke frontend
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
        $this->customerMoney = null;
        $this->change = 0;
    }

    // Refresh Cache saat update stok produk
    protected function refreshCache()
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
            ->latest()
            ->take($this->limitProducts)
            ->get(), $ttl);
    }

    public function render()
    {
        return view('livewire.dashboard.order');
    }
}