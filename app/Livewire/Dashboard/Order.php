<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Component
{
    public $search = '';
    public $products = [];
    public $limitProducts = 4;

    public $payment_method = 'cash'; // Metode pembayaran default
    public $customerMoney = 0;
    
    public $discount_type = 'percentage';   // Tipe diskon (percentage/nominal)
    public $discount_value = 0; // Nilai diskon
    public $discount = 0; // Total diskon
    
    public $tax = 0;
    public $tax_percentage = 0; // Pajak default dalam persen
    
    public $cart = [];
    public $cartNotEmpty = false;
    public $subtotal = 0;
    public $total = 0;
    public $change = 0;

    protected $listeners = [
        'refreshProductStock' => 'searchProduct',
    ];

    // Pencarian produk
    public function searchProduct()
    {
        $ttl = 31536000;
        $cacheKey = "products_{$this->search}_{$this->limitProducts}";

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

    // ✅ Saat tambah produk ke cart, langsung update cartNotEmpty
    public function addToCart($productId)
    {
        $product = collect($this->products)->firstWhere('id', $productId);

        if (!$product){
            $product = Product::findOrFail($productId);
        }

        if ($product) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price, // Tambahkan subtotal di awal
            ];
            $this->cartNotEmpty = true; // ✅ Langsung set true
            $this->calculateTotal();
        }
    }

    // ✅ Auto-update cartNotEmpty saat cart diubah
    public function updatedCart($value, $key)
    {
        list($index, $field) = explode('.', $key);
    
        if ($field === 'quantity' && isset($this->cart[$index])) {
            $this->updateQuantity($index, $value);
        }
    
        $this->cartNotEmpty = !empty($this->cart);
    }
    

    
    // Perbarui metode pembayaran
    public function updatedPaymentMethod($value)
    {
        $this->payment_method = $value;
    }

    // Reset diskon saat checkbox diskon diubah
    public function updateTotal()
    {
       $this->calculateTotal();
    }

    // Perbarui total saat uang pelanggan berubah
    public function updatedCustomerMoney($value)
    {
        $this->calculateChange();
    }

    // Perbarui total saat diskon berubah
    public function updatedDiscountValue($value)
    {
        // Pastikan nilai tetap 0 jika input kosong
        $this->discount_value = $value === '' ? 0 : (float) $value;
        $this->calculateTotal();
    }

    // Tambahkan jumlah produk
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

            $this->cartNotEmpty = false;
        }
    }


    // Perbarui pajak saat persentase pajak berubah
    public function updatedTaxPercentage($value)
    {
        $this->tax_percentage = $value === '' ? 0 : (float) $value;
        $this->calculateTotal();
    }
    
    // Hitung total belanja
    public function calculateTotal()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    
        // Hitung diskon
        if ($this->discount_type === 'percentage') {
            $discount = ($subtotal * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }
    
        // Hitung pajak berdasarkan persentase yang bisa diedit user
        $taxRate = $this->tax_percentage / 100;
        $tax = ($subtotal - $discount) * $taxRate;
    
        // Hitung total akhir
        $this->subtotal = $subtotal;
        $this->discount = $discount;
        $this->tax = $tax; // Pajak dalam Rupiah
        $this->total = $subtotal - $discount + $tax;
    
        // Hitung kembalian jika pelanggan sudah memasukkan uang
        $this->change = max($this->customerMoney - $this->total, 0);
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
        $userKey = 'order-process:user-' . Auth::id(); // Key unik per user
        $maxAttempts = 5; // Maksimal 5 request dalam 2 detik
        $decaySeconds = 2; // Reset setelah 2 detik

        if (RateLimiter::tooManyAttempts($userKey, $maxAttempts)) {
            $this->dispatch('errorPayment', 'Terlalu banyak permintaan, coba lagi dalam 2 detik.');
            return;
        }

        RateLimiter::hit($userKey, $decaySeconds);


        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $customerMoney = (float) $this->customerMoney;
        $total = (float) $this->total;

        if ($this->payment_method === 'cash') {
            if ($customerMoney < $total) {
                $shortage = $total - $customerMoney;
                $this->dispatch('insufficientPayment', $shortage);
                return;
            }
        } else {
            $this->customerMoney = $total;
        }


        DB::beginTransaction();
        try {
            $productUpdates = [];
            $insufficientProducts = [];

            //Ambil semua produk dalam satu query
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::with('supplier')->whereIn('id', $productIds)->get()->keyBy('id');

            //Periksa stok sebelum memproses order
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

            $this->calculateChange();

            //Simpan data order (tanpa detail produk)
            $order = ModelsOrder::create([
                'user_id' => Auth::user()->id,
                'order_number' => 'ORD/' . now()->format('Y-m-d') . '/' . Str::random(6),
                'payment_method' => $this->payment_method,
                'tax' => $this->tax,
                'discount' => $this->discount,
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



            $updateQuery = "UPDATE products SET stock = CASE";
            $updateSoldCount = ", sold_count = CASE"; // Gabungkan update stock & sold_count
            
            foreach ($productUpdates as $productId => $quantity) {
                $updateQuery .= " WHEN id = $productId THEN stock - $quantity";
                $updateSoldCount .= " WHEN id = $productId THEN sold_count + $quantity"; 
            }
            $updateQuery .= " END" . $updateSoldCount . " END WHERE id IN (" . implode(',', array_keys($productUpdates)) . ")";
            
            DB::statement($updateQuery);
            


            DB::commit();
            $this->refreshCacheStock(); //Refresh Cache stok produk
            $this->refreshCacheTransactionDetail(); // Refresh Cache transaksi

            //Dispatch event ke frontend
            $this->dispatch('refreshProductStock');
            $this->dispatch('successPayment');

            // Reset keranjang & uang customer
            $this->cart = null;
            $this->customerMoney = 0;
            
            $this->dispatch('printReceipt', $order->id); // Print struk pembayaran

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
        $this->discount_value = 0;
        $this->discount_type = 'percentage';
        $this->tax_percentage = 0;

        $this->cartNotEmpty = false;
    }

    // Refresh Cache transaksi
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
    }
    

    // Refresh Cache stok produk
    protected function refreshCacheStock()
    {
        $ttl = 31536000; // TTL cache selama 1 tahun

        // Cache key unik berdasarkan pencarian & limit
        $cacheKey = "products_{$this->search}_{$this->limitProducts}";

        // Hapus cache lama sebelum memperbarui
        Cache::forget($cacheKey);

        // Simpan ulang data terbaru ke dalam cache
        $this->products = Cache::remember($cacheKey, $ttl, function () {
            return DB::table('products')
                ->leftJoin('suppliers', 'products.supplier_id', '=', 'suppliers.id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    'products.description',
                    'products.stock',
                    'products.unit',
                    'products.image',
                    'products.created_at',
                    'products.updated_at',
                    'suppliers.name as supplier_name'
                )
                ->where(function ($query) {
                    $query->where('products.name', 'like', '%' . $this->search . '%')
                        ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                        ->orWhere('products.price', 'like', '%' . $this->search . '%')
                        ->orWhere('products.description', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('products.sold_count')
                ->take($this->limitProducts)
                ->get();
        });

        // Dispatch event ke frontend untuk memastikan UI juga terupdate
        $this->dispatch('refreshProductStock');
    }


    public function render()
    {
        return view('livewire.dashboard.order');
    }
}
