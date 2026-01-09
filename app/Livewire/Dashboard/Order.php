<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use App\Models\StoreSetting;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order extends Component
{
    public $search = ''; // Pencarian produk
    public $products = []; // Daftar produk
    public $limitProducts = 8; // Batas produk yang ditampilkan

    public $payment_method = 'cash'; // Metode pembayaran default
    public $customerMoney = 0; // Uang pelanggan
    
    public $is_tax; // Apakah ada pajak
    public $tax = 0;   // Pajak dalam Rupiah
    public $tax_percentage; // Pajak dalam persen
    
    
    public $cart = []; // Keranjang belanja
    public $cartNotEmpty = false; // Status keranjang belanja
    public $subtotal = 0; // Subtotal belanja
    public $total = 0; // Total belanja
    public $change = 0; // Kembalian

    public $ttl = 31536000; // Cache selama 1 tahun

    protected $listeners = [
        'refreshProductStock' => 'searchProduct',
    ];


    public function mount()
    {
        $this->is_tax = StoreSetting::value('is_tax') ?? false; // Ambil setting pajak dari tabel store_settings
        $this->tax_percentage = StoreSetting::value('tax') ?? 0; // Ambil persentase pajak dari tabel store_settings
    }

    public function searchProduct()
    {
        $cacheKey = "products_{$this->search}_{$this->limitProducts}";

        // Simpan semua cache key yang pernah digunakan
        $usedKeys = Cache::get('product_cache_keys', []);
        if (!in_array($cacheKey, $usedKeys)) {
            $usedKeys[] = $cacheKey;
            Cache::put('product_cache_keys', $usedKeys, $this->ttl);
        }

        $this->products = Cache::remember($cacheKey, $this->ttl, function () {
            return Product::where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('price', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('sold_count')
                ->take($this->limitProducts)
                ->get();
        });
    }


    // Saat tambah produk ke cart, langsung update cartNotEmpty
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
            $this->cartNotEmpty = true; // Set true
            $this->calculateTotal();
        }
    }

    // Auto-update cartNotEmpty saat cart diubah
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

        // Pajak hanya dihitung jika is_tax true
        $tax = 0;
        if ($this->is_tax) {
            $tax = ($subtotal * $this->tax_percentage) / 100;
        }

        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = $subtotal + $tax;
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

    // Proses Bill
    public function billPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $billData = [
            'tanggal' => now()->format('d-m-Y H:i'),
            'kasir' => Auth::user()->name ?? 'Owner',
            'order_number' => 'ORD/' . now()->format('dmY') . '/' . Str::random(6),
            'items' => [],
            'subtotal' => 0,
            'tax' => $this->tax,
            'total' => $this->total,
        ];

        $subtotal = 0;
        foreach ($this->cart as $item) {
            $total = $item['price'] * $item['quantity'];
            $subtotal += $total;

            $billData['items'][] = [
                'name' => $item['name'],
                'qty' => $item['quantity'],
                'price' => $item['price'],
                'total' => $total,
            ];
        }

        $billData['subtotal'] = $subtotal;

        // Simpan sementara di cache (5 menit)
        cache()->put('bill-preview:' . Auth::id(), $billData, now()->addMinutes(5));

        // Dispatch JS untuk buka tab baru
        $this->dispatch('showBillPrintPopup', route('order.bill'));
        
    }

    // Proses Order 
    public function processOrder()
    {
        $userKey = 'order-process:user-' . Auth::id(); 
        $maxAttempts = 5; 
        $decaySeconds = 2; 

        if (RateLimiter::tooManyAttempts($userKey, $maxAttempts)) {
            $this->dispatch('errorPayment', 'Terlalu banyak permintaan, coba lagi dalam 2 detik.');
            return;
        }
        RateLimiter::hit($userKey, $decaySeconds);

        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $customerMoney = decimal($this->customerMoney);
        $total = decimal($this->total);

        if ($this->payment_method === 'cash') {
            if (bccomp($customerMoney, $total, 2) === -1) {
                $shortage = bcsub($total, $customerMoney, 2);
                $this->dispatch('insufficientPayment', $shortage);
                return;
            }
        } else {
            $this->customerMoney = $total;
            $customerMoney = $total;
        }

        DB::beginTransaction();
        try {
            $productUpdates = [];
            $insufficientProducts = [];

            // Lock produk biar aman dari race condition
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

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

            // =========================
            // CREATE ORDER
            // =========================
            $order = ModelsOrder::create([
                'user_id' => Auth::id(),
                'order_number' => 'ORD/' . now()->format('dmY') . '/' . Str::random(6),
                'payment_method' => $this->payment_method,
                'tax' => decimal($this->tax),
                'customer_money' => $customerMoney,
                'change' => '0', // nanti diupdate
                'grandtotal' => $total,
                'status' => 'paid', // atau 'completed'
            ]);

            // =========================
            // INSERT TRANSACTION DETAILS
            // =========================
            $transactionDetails = [];

            foreach ($this->cart as $item) {
                $itemPrice = decimal($item['price']);
                $itemQuantity = (string) $item['quantity'];
                $itemSubtotal = bcmul($itemPrice, $itemQuantity, 2);

                $transactionDetails[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $itemQuantity,
                    'price' => $itemPrice,
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $productUpdates[$item['id']] = $item['quantity'];
            }

            TransactionDetail::insert($transactionDetails);

            // =========================
            // UPDATE STOCK & SOLD COUNT
            // =========================
            foreach ($productUpdates as $productId => $qty) {
                Product::where('id', $productId)->update([
                    'stock' => DB::raw("stock - $qty"),
                    'sold_count' => DB::raw("sold_count + $qty"),
                ]);
            }

            // =========================
            // UPDATE CHANGE
            // =========================
            $change = bcsub($customerMoney, $total, 2);
            $order->change = $change;
            $order->save();

            DB::commit();

            // =========================
            // RESET & REFRESH
            // =========================
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();

            $this->dispatch('refreshProductStock');
            $this->dispatch('successPayment');

            $this->resetCart();

            $this->dispatch('printReceipt', $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('errorPayment', $e->getMessage());
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
        // Ambil daftar semua cache key produk
        $cacheKeys = Cache::get('product_cache_keys', []);

        foreach ($cacheKeys as $key) {
            // Hapus cache untuk setiap produk yang ada di cache
            Cache::forget($key);
        }

        // Hapus cache yang menyimpan daftar semua cache key produk
        Cache::forget('product_cache_keys');

        // Muat ulang cache untuk current state pencarian
        $this->searchProduct();

        // Pastikan UI terupdate
        $this->dispatch('refreshProductStock');
    }


    public function render()
    {
        return view('livewire.dashboard.order');
    }
}
