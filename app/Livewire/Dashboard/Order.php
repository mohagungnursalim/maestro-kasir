<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
use App\Models\StoreSetting;
use App\Models\TransactionDetail;
use App\Models\Expense;
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
    public $limitProducts = 50; // Batas produk yang ditampilkan
    public $filterSku = ''; // Filter SKU (MAKANAN / MINUMAN)
    public $order_type = 'DINE_IN';
    public $desk_number = '';
    public $note = null;
    public $payment_mode = 'PAY_NOW'; // PAY_NOW | PAY_LATER

    public $unpaidOrders = [];
    public $selectedUnpaidOrderId = null;

    public $tempProductNote = '';

    public $payment_method = 'CASH'; // Metode pembayaran default
    public $customerMoney = null; // Uang pelanggan
    
    public $is_tax; // Apakah ada pajak
    public $tax = 0;   // Pajak dalam Rupiah
    public $tax_percentage; // Pajak dalam persen
    
    public $familyDiscount = false; // Diskon 100% family
    public $friendDiscount = false; // Diskon 20% teman

    public $shippingEnabled = false; // Toggle ongkir
    public $shippingCost = 0;        // Biaya ongkir
    
    public $platformFeeEnabled = false; // Toggle komisi platform
    public $platformFee = 0;            // Komisi aplikasi (Gojek dll)

    public $cart = []; // Keranjang belanja
    public $cartNotEmpty = false; // Status keranjang belanja
    public $subtotal = 0; // Subtotal belanja
    public $total = 0; // Total belanja
    public $change = 0; // Kembalian
    // Split bill support
    public $splitCount = 2;
    public $preparedSplitCount = 0;
    public $splitEnabled = false;

    public $ttl; // Cache TTL (diinisialisasi di mount)

    protected $listeners = [
        'refreshProductStock' => 'searchProduct',
    ];

    

    // Inisialisasi komponen
    public function mount()
    {
        $this->ttl = 300;
        
        // Caching Store Settings to reduce early heavy database loads
        $setting = Cache::remember('store_settings_flags', $this->ttl, function () {
            return StoreSetting::first(['is_tax', 'tax']);
        });

        $this->is_tax = $setting->is_tax ?? false;
        $this->tax_percentage = $setting->tax ?? 0;
        
        $this->loadUnpaidOrders();
    }

    // Load unpaid orders
    public function loadUnpaidOrders()
    {
        // Gunakan deferred fetch pattern
        $ids = ModelsOrder::where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->pluck('id');

        if ($ids->isEmpty()) {
            $this->unpaidOrders = collect();
            return;
        }

        $this->unpaidOrders = ModelsOrder::whereIn('id', $ids)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    // Cari produk dengan caching
    public function searchProduct()
    {
        // 1. Ambil versi cache global (Menghindari Race Condition O(1))
        $version = Cache::get('product_cache_version', 1);
        
        // 2. Hash keyword pencarian (Mencegah nama file cache kepanjangan / karakter terlarang)
        $searchHash = md5($this->search . '_' . $this->filterSku);
        
        // 3. Gabungkan menjadi 1 unique key berbasis versi
        $activeBranch = \Illuminate\Support\Facades\Session::get('active_branch_id', 'all');
        $cacheKey = "products_br{$activeBranch}_v{$version}_{$searchHash}_{$this->limitProducts}";

        $this->products = Cache::remember($cacheKey, $this->ttl, function () {
            $query = Product::query();

            if ($this->filterSku !== '') {
                $query->where('sku', $this->filterSku);
            }

            // 1. Ambil list ID terlebih dahulu untuk operasi sort/filter yang optimal
            $ids = $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('price', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->orderByDesc('sold_count')
                ->take($this->limitProducts)
                ->pluck('id');

            if ($ids->isEmpty()) {
                return collect();
            }

            // 2. Ambil data asli berdasar kumpulan ID 
            return Product::whereIn('id', $ids)
                ->orderByDesc('sold_count')
                ->get(['id', 'name', 'sku', 'price', 'stock', 'use_stock', 'image', 'description', 'is_discounted', 'discount_type', 'discount_value', 'discount_start', 'discount_end']);
        });
    }

    // Ubah filter SKU
    public function setFilterSku($sku)
    {
        $this->filterSku = $sku;
        $this->searchProduct();
    }

    // Tambahkan produk ke keranjang dengan catatan
    public function addToCartWithNote($productId, $note)
    {
        $this->processAddToCart($productId, $note);
    }

    // Tambahkan produk ke keranjang
    public function addToCart($productId)
    {
        $this->processAddToCart($productId);
    }

    private function processAddToCart($productId, $note = null)
    {
        $product = collect($this->products)->firstWhere('id', $productId) ?? Product::findOrFail($productId);
        
        if ($product) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image' => $product->image,
                'price' => $product->final_price,
                'quantity' => 1,
                'subtotal' => $product->final_price,
                'product_note' => $note ?? $product->product_note,
                'assigned_to' => 1,
            ];
            $this->cartNotEmpty = true;
            $this->calculateTotal();
        }
    }

    // Perbarui catatan produk di keranjang
    public function updateItemNote($index, $note)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['product_note'] = trim($note);
        }
    }

    // Perbarui harga produk di keranjang
    public function updateItemPrice($index, $price)
    {
        if (isset($this->cart[$index])) {
            $price = max(0, (float) str_replace(['Rp', '.', ',', ' '], '', $price));
            $this->cart[$index]['price'] = $price;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['quantity'] * $price;
            $this->calculateTotal();
            // recalculate change if necessary
            $this->calculateChange();
        }
    }

    // Auto-update cartNotEmpty saat cart diubah
    public function updatedCart($value, $key)
    {
        $parts = explode('.', $key);
        // get last two segments: index and field (works for "0.qty" or "cart.0.qty")
        $field = array_pop($parts);
        $index = array_pop($parts);

        if ($field === 'quantity' && isset($this->cart[$index])) {
            $this->updateQuantity($index, $value);
        }

        // If assigned_to changed, invalidate prepared split preview
        if ($field === 'assigned_to') {
            Cache::forget('bill-preview-multi:' . Auth::id());
            $this->preparedSplitCount = 0;
        }

        $this->cartNotEmpty = !empty($this->cart);
    }

    // When split count changes, clear any prepared previews
    public function updatedSplitCount($value)
    {
        $n = (int) $value;
        if ($n < 1) $n = 1;
        if ($n > 20) $n = 20;
        $this->splitCount = $n;
        $this->preparedSplitCount = 0;
        Cache::forget('bill-preview-multi:' . Auth::id());
    }

    // When split toggle is changed, clear prepared previews
    public function updatedSplitEnabled($value)
    {
        if (!$value) {
            Cache::forget('bill-preview-multi:' . Auth::id());
            $this->preparedSplitCount = 0;
        }
    }
    

    // Perbarui metode pembayaran
    public function updatedPaymentMethod($value)
    {
        $this->payment_method = $value;

        if ($value === 'CASH') {
            // Cash: user input manual
            $this->customerMoney = null;
            $this->change = 0;
        } else {
            // Non-cash: auto pas total
            $this->customerMoney = $this->total;
            $this->change = 0;
        }
    }


    // Perbarui mode pembayaran
    public function updatedPaymentMode($value)
    {
        // setiap kali mode pembayaran berubah, kembalikan metode bayar ke 'CASH'
        $this->payment_method = 'CASH';

        // Jika pilih bayar nanti, pastikan uang pelanggan = 0 (tidak dibayarkan saat ini)
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

    // Toggle ongkir
    public function updatedShippingEnabled($value)
    {
        if (!$value) {
            $this->shippingCost = 0;
        }
        $this->calculateTotal();
    }

    // Saat nominal ongkir diubah
    public function updatedShippingCost($value)
    {
        $this->shippingCost = max(0, (float) $value);
        $this->calculateTotal();
    }

    // Hitung total belanja
    public function calculateTotal()
    {
        $this->subtotal = $this->calculateSubtotal();
        $discount = $this->calculateDiscount($this->subtotal);
        $subtotalAfterDiscount = $this->subtotal - $discount;
        
        $this->tax = $this->calculateTaxAmount($subtotalAfterDiscount);
        $shipping = $this->calculateShipping();

        $this->total = $subtotalAfterDiscount + $this->tax + $shipping;

        $this->adjustCustomerMoneyState();
        
        $this->change = max((float)$this->customerMoney - $this->total, 0);
    }

    private function calculateSubtotal(): float
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    private function calculateDiscount(float $subtotal): float
    {
        if ($this->familyDiscount) return $subtotal;
        if ($this->friendDiscount) return ($subtotal * 20) / 100;
        return 0;
    }

    private function calculateTaxAmount(float $subtotalAfterDiscount): float
    {
        return $this->is_tax ? ($subtotalAfterDiscount * $this->tax_percentage) / 100 : 0;
    }

    private function calculateShipping(): float
    {
        return $this->shippingEnabled ? (float) $this->shippingCost : 0;
    }

    private function adjustCustomerMoneyState(): void
    {
        if ($this->total == 0) {
            $this->customerMoney = 0;
        } elseif ($this->total > 0 && $this->customerMoney === 0 && $this->payment_method === 'CASH') {
            $this->customerMoney = null;
        }
    }


    // Hitung kembalian
    public function calculateChange()
    {
        $this->customerMoney = (float) $this->customerMoney;

        if ($this->total <= 0) {
            $this->customerMoney = null;
            $this->change = 0;
            return;
        }

        if ($this->customerMoney > $this->total) {
            $this->change = $this->customerMoney - $this->total;
        } elseif ($this->customerMoney < $this->total) {
            $this->change = 0;
        } elseif ($this->customerMoney === 0) {
            $this->change = 0;
        }
    }

    // Update total saat diskon family diubah
    public function updatedFamilyDiscount($value)
    {
        if ($value) $this->friendDiscount = false;
        $this->calculateTotal();
    }

    // Update total saat diskon teman diubah
    public function updatedFriendDiscount($value)
    {
        if ($value) $this->familyDiscount = false;
        $this->calculateTotal();
    }

    // Proses Bill
    public function billPayment()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        app(\App\Services\PrintService::class)->generateBillPreview([
            'cart' => $this->cart,
            'familyDiscount' => $this->familyDiscount,
            'friendDiscount' => $this->friendDiscount,
            'shippingEnabled' => $this->shippingEnabled,
            'shippingCost' => $this->shippingCost,
            'tax' => $this->tax,
            'total' => $this->total,
        ]);

        // Dispatch JS untuk buka tab baru (single full bill)
        $this->dispatch('showBillPrintPopup', route('order.bill'));
    }

    // Cetak Struk Dapur
    public function kitchenPrint()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        app(\App\Services\PrintService::class)->generateKitchenPreview([
            'cart' => $this->cart,
            'desk_number' => $this->desk_number,
            'order_type' => $this->order_type,
            'note' => $this->note,
        ]);

        // Buka popup struk dapur
        $this->dispatch('showKitchenPrintPopup', route('order.kitchen'));
    }

    // Prepare split previews (store multi-split in cache)
    public function prepareSplit()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $count = max(1, (int) $this->splitCount);

        app(\App\Services\PrintService::class)->generateMultiSplitPreview([
            'cart' => $this->cart,
            'splitCount' => $count,
            'familyDiscount' => $this->familyDiscount,
            'friendDiscount' => $this->friendDiscount,
            'is_tax' => $this->is_tax,
            'tax_percentage' => $this->tax_percentage,
        ]);

        $this->preparedSplitCount = $count;
    }


    // Generate nomor order unik
    public static function generateOrderNumber(): string
    {
        return app(\App\Services\OrderService::class)->generateOrderNumber();
    }

    // Pilih order unpaid untuk diload ke cart
    public function selectUnpaidOrder($orderId)
    {
        $order = ModelsOrder::with('transactionDetails.product')->findOrFail($orderId);

        $this->resetCart();
        $this->loadUnpaidOrderItemsIntoCart($order->transactionDetails);
        $this->loadUnpaidOrderMetadata($order);
        
        $this->calculateTotal();
        $this->dispatch('orderUnpaid');
    }

    private function loadUnpaidOrderItemsIntoCart($transactionDetails): void
    {
        foreach ($transactionDetails as $item) {
            $this->cart[] = [
                'id' => $item->product_id,
                'name' => $item->product->name ?? 'Unknown',
                'sku' => $item->product->sku ?? '',
                'image' => $item->product->image ?? '',
                'price' => $item->price,
                'quantity' => $item->quantity,
                'product_note' => $item->product_note,
                'assigned_to' => 1,
            ];
        }
    }

    private function loadUnpaidOrderMetadata($order): void
    {
        $this->selectedUnpaidOrderId = $order->id;
        $this->order_type = $order->order_type;
        $this->desk_number = $order->desk_number ?? '';
        $this->note = $order->note;
        $this->payment_mode = 'PAY_NOW';
        $this->payment_method = $order->payment_method ?? 'CASH';
        
        $this->loadPlatformFeeMetadata($order);
    }
    
    private function loadPlatformFeeMetadata($order): void
    {
        $expense = Expense::where('category', 'Komisi Aplikasi')
            ->where('description', 'Potongan Komisi ' . $order->order_type . ' ' . $order->order_number)->first();
            
        $this->platformFeeEnabled = (bool) $expense;
        $this->platformFee = $expense ? $expense->amount : 0;
    }


    // Proses order (baru atau edit unpaid)
        // Proses order (baru atau edit unpaid)
    public function processOrder()
    {
        $userKey = 'order-process:user-' . Auth::id();
        if (RateLimiter::tooManyAttempts($userKey, 15)) {
            $this->dispatch('errorPayment', 'Terlalu banyak request, tunggu sebentar.');
            return;
        }
        RateLimiter::hit($userKey, 1);

        if (empty($this->cart) && !$this->selectedUnpaidOrderId) {
            return;
        }

        // Cek cabang aktif
        $activeBranchId = \Illuminate\Support\Facades\Session::get('active_branch_id');
        if ($activeBranchId) {
            $branch = \App\Models\Branch::find($activeBranchId);
            if ($branch && !$branch->is_active) {
                $this->dispatch('errorPayment', 'Cabang ini sedang dalam status Non-Aktif (Read-Only). Transaksi diblokir.');
                return;
            }
        }
        
        try {
            $result = app(\App\Services\OrderService::class)->processOrder([
                'cart' => $this->cart,
                'selectedUnpaidOrderId' => $this->selectedUnpaidOrderId,
                'desk_number' => $this->desk_number,
                'order_type' => $this->order_type,
                'note' => $this->note,
                'familyDiscount' => $this->familyDiscount,
                'friendDiscount' => $this->friendDiscount,
                'tax' => $this->tax,
                'shippingEnabled' => $this->shippingEnabled,
                'shippingCost' => $this->shippingCost,
                'payment_mode' => $this->payment_mode,
                'payment_method' => $this->payment_method,
                'customerMoney' => $this->customerMoney,
                'platformFeeEnabled' => $this->platformFeeEnabled,
                'platformFee' => $this->platformFee,
                'is_tax' => $this->is_tax,
                'tax_percentage' => $this->tax_percentage,
            ]);

            if (isset($result['status']) && $result['status'] === 'empty') {
                return;
            }

            // ============ UI RESET =============
            $this->selectedUnpaidOrderId = null;
            $this->resetCart();

            // =========== NOTIFIKASI + PRINT LOGIC ================
            if (!empty($result['was_unpaid']) && $result['payment_status'] === 'PAID') {
                $this->dispatch('successPayment');
                $this->dispatch('printReceipt', $result['order_id']);
            } elseif ($result['payment_status'] === 'PAID') {
                $this->dispatch('successPayment');
                $this->dispatch('printReceipt', $result['order_id']);
            } else {
                $this->dispatch('successSaveOrder');
            }

            // REFRESH CACHE
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();

            // refresh stock di UI
            $this->dispatch('refreshProductStock');

            // reload daftar unpaid orders
            $this->loadUnpaidOrders();

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_starts_with($msg, 'INSUFFICIENT_STOCK:')) {
                $this->dispatch('insufficientStock', explode(', ', substr($msg, 19)));
            } elseif (str_starts_with($msg, 'INSUFFICIENT_PAYMENT:')) {
                $this->dispatch('insufficientPayment', substr($msg, 21));
            } elseif ($msg === 'ERROR_ORDER_TYPE') {
                $this->dispatch('errorOrderType');
            } else {
                \Illuminate\Support\Facades\Log::error($e->getMessage());
                $this->dispatch('errorPayment', $msg);
            }
        }
    }

    // Hapus pesanan bayar nanti (unpaid)
    public function deleteUnpaidOrder($orderId)
    {
        try {
            app(\App\Services\OrderService::class)->deleteUnpaidOrder($orderId);

            // Jika order yang sedang dipilih dihapus, reset keranjang
            if ($this->selectedUnpaidOrderId == $orderId) {
                $this->resetCart();
            }

            // Refresh cache stok
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();

            // Refresh daftar produk di UI
            $this->dispatch('refreshProductStock');

            // Muat ulang daftar order bayar nanti
            $this->loadUnpaidOrders();

            $this->dispatch('successDeleteOrder');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->dispatch('errorPayment', 'Gagal menghapus pesanan.');
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
        $this->desk_number = '';
        $this->note = null;
        $this->payment_mode = 'PAY_NOW';
        // reset payment method on both backend and frontend to CASH
        $this->payment_method = 'CASH';

        $this->cartNotEmpty = false;
        $this->splitEnabled = false;
        $this->preparedSplitCount = false;
        $this->splitCount = 2;
        $this->familyDiscount = false; // Reset diskon family
        $this->friendDiscount = false; // Reset diskon teman
        $this->shippingEnabled = false; // Reset ongkir
        $this->shippingCost = 0;
        
        $this->platformFeeEnabled = false;
        $this->platformFee = 0;

        $this->selectedUnpaidOrderId = null;
    }

    // Toggle komisi platform
    public function updatedPlatformFeeEnabled($value)
    {
        if (!$value) {
            $this->platformFee = 0;
        }
    }

    // Saat komisi platform diubah
    public function updatedPlatformFee($value)
    {
        $this->platformFee = max(0, (float) str_replace(['Rp', '.', ',', ' '], '', $value));
    }

    // Auto-hitung komisi platform (misal 20% + 1000)
    public function calculatePlatformFee($percentage, $fixedAmount = 0)
    {
        if ($this->subtotal > 0) {
            $fee = ($this->subtotal * $percentage / 100) + $fixedAmount;
            $this->platformFee = $fee;
        }
    }
    

    // Refresh Cache transaksi
    public function refreshCacheTransactionDetail()
    {
        // Cukup naikkan versi cache transaksi 1 tingkat (O(1) speed)
        $newVersion = Cache::get('transaction_cache_version', 1) + 1;
        Cache::put('transaction_cache_version', $newVersion, now()->addDays(7));
    }
    

    // Refresh Cache stok produk
    protected function refreshCacheStock()
    {
        // Cukup naikkan versi cache 1 tingkat (sangat cepat, O(1))
        $newVersion = Cache::get('product_cache_version', 1) + 1;
        Cache::put('product_cache_version', $newVersion, now()->addDays(7));

        // Muat ulang cache untuk current state pencarian (akan menggunakan versi baru secara otomatis)
        $this->searchProduct();

        // Pastikan UI terupdate
        $this->dispatch('refreshProductStock');
    }




    // Render komponen
    public function render()
    {
        return view('livewire.dashboard.order');
    }
}
