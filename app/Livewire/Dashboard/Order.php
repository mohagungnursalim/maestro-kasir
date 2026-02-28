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

    public $showNoteModal = false;
    public $noteProductId = null;
    public $tempProductNote = '';

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
        $this->loadUnpaidOrders();
        $this->is_tax = StoreSetting::value('is_tax') ?? false; // Ambil setting pajak dari tabel store_settings
        $this->tax_percentage = StoreSetting::value('tax') ?? 0; // Ambil persentase pajak dari tabel store_settings
        $this->ttl = now()->addHours(1); // Cache selama 1 jam
    }

    // Load unpaid orders
    public function loadUnpaidOrders()
    {
        $this->unpaidOrders = ModelsOrder::where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
    }

    // Cari produk dengan caching
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

    // Buka modal penambahan catatan produk
    public function openAddToCartModal($productId)
    {
        $this->noteProductId = $productId;
        $this->tempProductNote = '';
        $this->showNoteModal = true;
    }

    // Tutup modal penambahan catatan produk
    public function closeNoteModal()
    {
        $this->showNoteModal = false;
        $this->noteProductId = null;
        $this->tempProductNote = '';
    }

    // Konfirmasi penambahan produk ke keranjang dengan catatan
    public function confirmAddToCart()
    {
        if (!$this->noteProductId) return;

        $product = collect($this->products)->firstWhere('id', $this->noteProductId);

        if (!$product){
            $product = Product::findOrFail($this->noteProductId);
        }

        if ($product) {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
                'product_note' => $this->tempProductNote,
                'assigned_to' => 1,
            ];
            $this->cartNotEmpty = true;
            $this->calculateTotal();
        }

        // reset modal
        $this->showNoteModal = false;
        $this->noteProductId = null;
        $this->tempProductNote = '';
    }

    // Tambahkan produk ke keranjang
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
                'product_note' => $product->product_note,
                'assigned_to' => 1,
            ];
            $this->cartNotEmpty = true; // Set true
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

        // Dispatch JS untuk buka tab baru (single full bill)
        $this->dispatch('showBillPrintPopup', route('order.bill'));
    }

    // Prepare split previews (store multi-split in cache)
    public function prepareSplit()
    {
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        $count = max(1, (int) $this->splitCount);
        $multi = [];

        for ($i = 1; $i <= $count; $i++) {
            $multi[$i] = [
                'tanggal' => now()->format('d-m-Y H:i'),
                'kasir' => Auth::user()->name ?? 'Owner',
                'order_number' => $this->generateOrderNumber() . "-S$i",
                'items' => [],
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
            ];
        }

        foreach ($this->cart as $item) {
            $group = isset($item['assigned_to']) ? (int) $item['assigned_to'] : 1;
            if ($group < 1 || $group > $count) $group = 1;

            $total = $item['price'] * $item['quantity'];

            $multi[$group]['items'][] = [
                'name' => $item['name'],
                'qty' => $item['quantity'],
                'price' => $item['price'],
                'total' => $total,
            ];

            $multi[$group]['subtotal'] += $total;
        }

        // compute tax/total per split
        foreach ($multi as $i => $md) {
            $tax = 0;
            if ($this->is_tax) {
                $tax = ($md['subtotal'] * $this->tax_percentage) / 100;
            }
            $multi[$i]['tax'] = $tax;
            $multi[$i]['total'] = $md['subtotal'] + $tax;
        }

        cache()->put('bill-preview-multi:' . Auth::id(), $multi, now()->addMinutes(5));
        $this->preparedSplitCount = $count;
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
                'product_note' => $item->product_note,
                'assigned_to' => 1,
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

        DB::beginTransaction();

        try {

            
            // ================= EDIT ORDER UNPAID =================
            if ($this->selectedUnpaidOrderId) {

                $order = ModelsOrder::lockForUpdate()->findOrFail($this->selectedUnpaidOrderId);

                // SIMPAN STATUS AWAL
                $wasUnpaid = $order->payment_status === 'UNPAID';

                if ($order->payment_status === 'PAID') {
                    throw new \Exception("Order ini sudah dibayar.");
                }

                // Ambil detail lama
                $oldDetails = $order->transactionDetails()->get()->keyBy('product_id');

                // Gabung semua product id lama + baru
                $productIds = collect($this->cart)->pluck('id')
                    ->merge($oldDetails->keys())
                    ->unique();

                // Lock semua product
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                // Cart baru
                $newCart = collect($this->cart)->keyBy('id');

                
                // ========== CEK STOK UNTUK SELISIH QTY ==========
                
                $insufficientProducts = [];

                foreach ($newCart as $productId => $item) {
                    $newQty = (int) $item['quantity'];
                    $oldQty = (int) ($oldDetails[$productId]->quantity ?? 0);
                    $diff   = $newQty - $oldQty;

                    if ($diff > 0) {
                        if (
                            !isset($products[$productId]) ||
                            $products[$productId]->stock < $diff
                        ) {
                            $insufficientProducts[] = $products[$productId]->name ?? 'Produk tidak diketahui';
                        }
                    }
                }

                // Jika ada produk dengan stok tidak cukup
                if (!empty($insufficientProducts)) {
                    DB::rollBack();
                    $this->dispatch('insufficientStock', $insufficientProducts);
                    return;
                }

                // VALIDASI MEJA
                if (empty($this->desk_number)) {
                    $this->dispatch('errorOrderType');
                    return;
                }

                
                // =========== UPDATE / INSERT DETAIL ==============
                
                foreach ($newCart as $productId => $item) {

                    $price = decimal($item['price']);
                    $newQty = (int) $item['quantity'];
                    $oldQty = (int) ($oldDetails[$productId]->quantity ?? 0);
                    $diff   = $newQty - $oldQty;

                    $subtotal = bcmul($price, (string)$newQty, 2);

                    if ($oldDetails->has($productId)) {
                        // UPDATE
                        TransactionDetail::where('order_id', $order->id)
                            ->where('product_id', $productId)
                            ->update([
                                'quantity' => $newQty,
                                'price' => $price,
                                'subtotal' => $subtotal,
                                'product_note' => $item['product_note'] ?? null,
                            ]);
                    } else {
                        // INSERT
                        TransactionDetail::create([
                            'order_id' => $order->id,
                            'product_id' => $productId,
                            'quantity' => $newQty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'product_note' => $item['product_note'] ?? null,
                        ]);
                    }

                    // Update stok berdasarkan selisih
                    if ($diff !== 0) {
                        Product::where('id', $productId)->update([
                            'stock' => DB::raw("stock - ($diff)"),
                            'sold_count' => DB::raw("sold_count + ($diff)"),
                        ]);
                    }
                }

                
                // ========== HAPUS ITEM YANG DIBUANG ==========
                
                foreach ($oldDetails as $productId => $oldItem) {
                    if (!$newCart->has($productId)) {

                        // balikin stok
                        Product::where('id', $productId)->update([
                            'stock' => DB::raw("stock + {$oldItem->quantity}"),
                            'sold_count' => DB::raw("sold_count - {$oldItem->quantity}"),
                        ]);

                        $oldItem->delete();
                    }
                }

                
                 // ========== HITUNG ULANG TOTAL ==========
                
                $total = '0';
                foreach ($newCart as $item) {
                    $subtotal = bcmul(decimal($item['price']), (string)$item['quantity'], 2);
                    $total = bcadd($total, $subtotal, 2);
                }

                $tax = decimal($this->tax);
                $total = bcadd($total, $tax, 2);

                if (bccomp($total, '0', 2) <= 0) {
                    throw new \Exception("Total tidak valid");
                }

                
                // =========== LOGIC BAYAR / SIMPAN ==========
                $orderBaseUpdate = [
                    'order_type' => $this->order_type,
                    'desk_number' => $this->order_type === 'DINE_IN' ? $this->desk_number : null,
                    'note' => $this->note,
                    'payment_method' => $this->payment_method,
                    'tax' => $tax,
                    'grandtotal' => $total,
                ];
                
                if ($this->payment_mode === 'PAY_NOW') {

                    $customerMoney = decimal($this->customerMoney);

                    if (bccomp($customerMoney, $total, 2) === -1) {
                        $shortage = bcsub($total, $customerMoney, 2);
                        DB::rollBack();
                        $this->dispatch('insufficientPayment', $shortage);
                        return;
                    }

                    $order->update(array_merge($orderBaseUpdate, [
                        'payment_status' => 'PAID',
                        'payment_mode' => 'PAY_NOW',
                        'customer_money' => $customerMoney,
                        'change' => bcsub($customerMoney, $total, 2),
                        'paid_at' => now(),
                    ]));

                } else {

                    // tetap UNPAID
                    $order->update(array_merge($orderBaseUpdate, [
                        'payment_status' => 'UNPAID',
                        'payment_mode' => 'PAY_LATER',
                        'customer_money' => null,
                        'change' => null,
                        'paid_at' => null,
                    ]));
                }


                DB::commit();

      
                // ============ UI RESET =============
               
                $this->selectedUnpaidOrderId = null;
                $this->resetCart();

                
                
                // =========== NOTIFIKASI + PRINT LOGIC ================
                
                if ($wasUnpaid && $order->payment_status === 'PAID') {
                    $this->dispatch('successPayment');
                    $this->dispatch('printReceipt', $order->id);
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
                return;
            }


            // ================== ORDER BARU ========================

            // LOCK PRODUCT
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $insufficientProducts = [];

            foreach ($this->cart as $item) {
                if (
                    !isset($products[$item['id']]) ||
                    $products[$item['id']]->stock < $item['quantity']
                ) {
                    $insufficientProducts[] = $products[$item['id']]->name ?? 'Produk tidak diketahui';
                }
            }

            if (!empty($insufficientProducts)) {
                DB::rollBack();
                $this->dispatch('insufficientStock', $insufficientProducts);
                return;
            }


            // VALIDASI MEJA
            if (empty($this->desk_number)) {
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

            // INSERT DETAIL + POTONG STOK
            foreach ($this->cart as $item) {

                $price = decimal($item['price']);
                $qty   = (int) $item['quantity'];
                $subtotal = bcmul($price, (string)$qty, 2);

                TransactionDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'product_note' => $item['product_note'] ?? null,
                ]);

                Product::where('id', $item['id'])->update([
                    'stock' => DB::raw("stock - $qty"),
                    'sold_count' => DB::raw("sold_count + $qty"),
                ]);
            }

            DB::commit();

            // UI RESET
            $this->resetCart();
            
            // REFRESH CACHE
            $this->refreshCacheStock();
            $this->refreshCacheTransactionDetail();

            // refresh stock di UI
            $this->dispatch('refreshProductStock');

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
        $this->splitEnabled = false;
        $this->preparedSplitCount = false;
        $this->splitCount = 2;

        $this->selectedUnpaidOrderId = null;
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


    // Render komponen
    public function render()
    {
        return view('livewire.dashboard.order');
    }
}
