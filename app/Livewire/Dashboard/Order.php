<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use App\Models\StoreSetting;
use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\AssignOp\Mod;

class Order extends Component
{
    public $search = ''; // Pencarian produk
    public $products = []; // Daftar produk
    public $limitProducts = 8; // Batas produk yang ditampilkan
    public $order_type = 'DINE_IN';
    public $desk_number = null;
    public $note = null;
    public $payment_mode = 'PAY_NOW'; // PAY_NOW | PAY_LATER

    public $unpaidOrders = [];
    public $selectedUnpaidOrderId = null;



    public $payment_method = 'CASH'; // Metode pembayaran default
    public $customerMoney = null; // Uang pelanggan
    
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
        $this->loadUnpaidOrders();
        $this->is_tax = StoreSetting::value('is_tax') ?? false; // Ambil setting pajak dari tabel store_settings
        $this->tax_percentage = StoreSetting::value('tax') ?? 0; // Ambil persentase pajak dari tabel store_settings
    }

    public function loadUnpaidOrders()
    {
        $this->unpaidOrders = ModelsOrder::where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
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

    public function updatedPaymentMode($value)
    {
        if ($value === 'PAY_LATER') {
            $this->customerMoney = 0;
        }
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
            $this->customerMoney = null;
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

        $orderNumber = $this->generateOrderNumber();

        $billData = [
            'tanggal' => now()->format('d-m-Y H:i'),
            'kasir' => Auth::user()->name ?? 'Owner',
            'order_number' => $orderNumber,
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

    // Generate nomor order unik
    public static function generateOrderNumber(): string
    {
        $today = now()->format('dmY');

        $last = ModelsOrder::whereDate('created_at', now())
            ->where('order_number', 'like', "ORD-$today-%")
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) substr($last->order_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return 'ORD-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Pilih order unpaid untuk diload ke cart
    public function selectUnpaidOrder($orderId)
    {
        $order = ModelsOrder::with('transactionDetails.product')->findOrFail($orderId);

        // Reset cart
        $this->resetCart();

        // Load ke cart
        foreach ($order->transactionDetails as $item) {
            $this->cart[] = [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
            ];
        }

        // Load metadata
        $this->selectedUnpaidOrderId = $order->id;
        $this->order_type = $order->order_type;
        $this->desk_number = $order->desk_number;
        $this->note = $order->note;
        $this->payment_mode = 'PAY_NOW';
        $this->calculateTotal();

        // Notifikasi ke UI saat load ke cart
        $this->dispatch('orderUnpaid');
    }


    // Proses pembayaran
    public function processOrder()
    {
        $userKey = 'order-process:user-' . Auth::id();
        if (RateLimiter::tooManyAttempts($userKey, 5)) {
            $this->dispatch('errorPayment', 'Terlalu banyak request, tunggu sebentar.');
            return;
        }
        RateLimiter::hit($userKey, 2);

        if (empty($this->cart) && !$this->selectedUnpaidOrderId) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        DB::beginTransaction();

        try {

            // =====================================================
            // BAYAR ORDER UNPAID YANG SUDAH ADA
            // =====================================================
            if ($this->selectedUnpaidOrderId) {

                $order = ModelsOrder::lockForUpdate()->findOrFail($this->selectedUnpaidOrderId);

                if ($order->payment_status === 'PAID') {
                    throw new \Exception("Order ini sudah dibayar.");
                }

                $total = decimal($order->grandtotal);
                $customerMoney = decimal($this->customerMoney);

                if (bccomp($customerMoney, $total, 2) === -1) {
                    $shortage = bcsub($total, $customerMoney, 2);
                    DB::rollBack();
                    $this->dispatch('insufficientPayment', $shortage);
                    return;
                }

                $order->update([
                    'payment_status' => 'PAID',
                    'payment_mode' => 'PAY_NOW',
                    'payment_method' => $this->payment_method,
                    'customer_money' => $customerMoney,
                    'change' => bcsub($customerMoney, $total, 2),
                    'paid_at' => now(),
                ]);

                DB::commit();

                // UI Reset
                $this->selectedUnpaidOrderId = null;
                $this->resetCart();

                $this->dispatch('successPayment');
                $this->dispatch('printReceipt', $order->id);
                $this->loadUnpaidOrders();

                return;
            }

            // =====================================================
            // BUAT ORDER BARU (PAY_NOW / PAY_LATER)
            // =====================================================

            // LOCK PRODUCT
            $productUpdates = [];
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($this->cart as $item) {
                if (!isset($products[$item['id']]) || $products[$item['id']]->stock < $item['quantity']) {
                    throw new \Exception("Stock tidak cukup: " . ($products[$item['id']]->name ?? 'Unknown'));
                }
            }

            // VALIDASI MEJA
            if ($this->order_type === 'DINE_IN' && empty($this->desk_number)) {
                $this->dispatch('errorOrderType');
                return;
            }

            // HITUNG TOTAL
            $total = '0';
            foreach ($this->cart as $item) {
                $subtotal = bcmul(decimal($item['price']), (string)$item['quantity'], 2);
                $total = bcadd($total, $subtotal, 2);
            }

            $tax = decimal($this->tax);
            $total = bcadd($total, $tax, 2);

            if (bccomp($total, '0', 2) <= 0) {
                throw new \Exception("Total tidak valid");
            }

            // LOGIC PAYMENT
            $customerMoney = null;
            $change = null;
            $paymentStatus = 'UNPAID';
            $paidAt = null;

            if ($this->payment_mode === 'PAY_NOW') {
                $customerMoney = decimal($this->customerMoney);

                if (bccomp($customerMoney, $total, 2) === -1) {
                    $shortage = bcsub($total, $customerMoney, 2);
                    DB::rollBack();
                    $this->dispatch('insufficientPayment', $shortage);
                    return;
                }

                $change = bcsub($customerMoney, $total, 2);
                $paymentStatus = 'PAID';
                $paidAt = now();
            }

            // CREATE ORDER
            $order = ModelsOrder::create([
                'user_id' => Auth::id(),
                'order_number' => $this->generateOrderNumber(),
                'order_type' => $this->order_type,
                'desk_number' => $this->order_type === 'DINE_IN' ? $this->desk_number : null,
                'note' => $this->note,
                'payment_method' => $this->payment_method,
                'tax' => $tax,
                'customer_money' => $customerMoney,
                'change' => $change,
                'grandtotal' => $total,
                'payment_status' => $paymentStatus,
                'payment_mode' => $this->payment_mode,
                'paid_at' => $paidAt,
            ]);

            // INSERT DETAILS + POTONG STOK
            $details = [];

            foreach ($this->cart as $item) {
                $price = decimal($item['price']);
                $qty   = (string) $item['quantity'];
                $subtotal = bcmul($price, $qty, 2);

                $details[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $productUpdates[$item['id']] = $qty;
            }

            TransactionDetail::insert($details);

            foreach ($productUpdates as $productId => $qty) {
                Product::where('id', $productId)->update([
                    'stock' => DB::raw("stock - $qty"),
                    'sold_count' => DB::raw("sold_count + $qty"),
                ]);
            }

            DB::commit();

            // REFRESH CACHE
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();
            
            // UI RESET
            $this->resetCart();
            $this->dispatch('refreshProductStock');

            // NOTIFIKASI BERHASIL
            if ($paymentStatus === 'PAID') {
                $this->dispatch('successPayment');
                $this->dispatch('printReceipt', $order->id);
            } else {
                $this->dispatch('successSaveOrder');
            }

            $this->loadUnpaidOrders();

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
        $this->customerMoney = null;
        $this->change = 0;

        $this->order_type = 'DINE_IN';
        $this->desk_number = null;
        $this->note = null;
        $this->payment_mode = 'PAY_NOW';

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
