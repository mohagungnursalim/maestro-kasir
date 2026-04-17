<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\TransactionDetail;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Livewire\Dashboard\Order as CashierOrder;

class CustomerOrder extends Component
{
    // Identitas meja (dari URL QR code)
    public string $deskNumber = '';
    public string $note = '';
    public ?int $branchId = null; // branch dari URL QR

    // Cart pelanggan
    public array $cart = [];

    // Filter & cari
    public string $search = '';
    public string $filterSku = '';

    // Produk
    public array $products = [];

    // Status
    public bool $submitted = false;
    public ?string $submittedOrderNumber = null;
    public ?string $errorMessage = null;

    // Store info
    public ?string $storeName = null;
    public ?string $storeLogo = null;

    public function mount(string $token)
    {
        // Cari meja berdasarkan token
        $table = \App\Models\Table::where('token', $token)->first();

        // Jika token tidak valid atau meja dinonaktifkan
        if (!$table || !$table->is_active) {
            abort(404, 'Menu tidak tersedia atau meja tidak aktif.');
        }

        // Set desk number dan branch dari database
        $this->deskNumber = $table->name;
        $this->branchId = $table->branch_id;

        $settings = StoreSetting::first();
        $this->storeName = $settings->store_name ?? 'Resto';
        $this->storeLogo = $settings->store_logo ?? null;

        $this->loadProducts();
    }

    public function loadProducts(): void
    {
        $version = Cache::get('product_cache_version', 1);
        $searchHash = md5($this->search . '_' . $this->filterSku);
        $cacheKey = "customer_products_v{$version}_{$searchHash}";

        $this->products = Cache::remember($cacheKey, 120, function () {
            $query = Product::query();

            if ($this->filterSku !== '') {
                $query->where('sku', $this->filterSku);
            }

            return $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->where(function ($q) {
                // Hanya tampilkan produk yang active / ada stok
                $q->where('use_stock', false)
                  ->orWhere('stock', '>', 0);
            })
            ->orderByDesc('sold_count')
            ->take(60)
            ->get(['id', 'name', 'sku', 'price', 'stock', 'use_stock', 'image', 'description'])
            ->toArray();
        });
    }

    public function setFilterSku(string $sku): void
    {
        $this->filterSku = $sku;
        $this->loadProducts();
    }

    public function searchProduct(): void
    {
        $this->loadProducts();
    }

    // Tambah ke cart
    public function addToCart(int $productId, string $note = ''): void
    {
        $product = collect($this->products)->firstWhere('id', $productId);

        if (!$product) {
            $product = Product::find($productId);
            if ($product) {
                $product = $product->toArray();
            }
        }

        if (!$product) return;

        // Cek stok
        $useStock = $product['use_stock'] ?? true;
        $stock = $product['stock'] ?? 0;
        if ($useStock && $stock < 1) return;

        // Cek apakah sudah ada di cart
        foreach ($this->cart as $index => $item) {
            if ($item['id'] === $productId) {
                $this->cart[$index]['quantity']++;
                $this->cart[$index]['product_note'] = $note ?: $this->cart[$index]['product_note'];
                return;
            }
        }

        $this->cart[] = [
            'id'           => $product['id'],
            'name'         => $product['name'],
            'sku'          => $product['sku'],
            'image'        => $product['image'],
            'price'        => $product['price'],
            'quantity'     => 1,
            'product_note' => $note,
        ];
    }

    // Kurangi qty / hapus dari cart
    public function decreaseQty(int $index): void
    {
        if (!isset($this->cart[$index])) return;

        if ($this->cart[$index]['quantity'] <= 1) {
            $this->removeFromCart($index);
        } else {
            $this->cart[$index]['quantity']--;
        }
    }

    // Tambah qty dari cart
    public function increaseQty(int $index): void
    {
        if (!isset($this->cart[$index])) return;
        $this->cart[$index]['quantity']++;
    }

    // Hapus item dari cart
    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart);
        }
    }

    // Update catatan item
    public function updateItemNote(int $index, string $note): void
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['product_note'] = trim($note);
        }
    }
    

    // Hitung subtotal
    public function getTotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    // Submit pesanan sebagai PAY_LATER
    public function submitOrder(): void
    {
        $this->errorMessage = null;

        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang masih kosong!';
            return;
        }

        if (empty(trim($this->deskNumber))) {
            $this->errorMessage = 'Nomor meja tidak valid.';
            return;
        }

        DB::beginTransaction();

        try {
            // Ambil user pertama (kasir default) atau null → pakai user_id = 1
            // Karena ini order publik, kita assign ke account system/owner
            $defaultUserId = \App\Models\User::where('branch_id', null)
                ->orWhereNotNull('id')
                ->orderBy('id')
                ->value('id') ?? 1;

            // Generate order number
            $today = now()->format('dmY');
            $last = Order::whereDate('created_at', now())
                ->where('order_number', 'like', "ORD-$today-%")
                ->orderBy('id', 'desc')
                ->first();
            $nextNumber = $last ? ((int) substr($last->order_number, -4)) + 1 : 1;
            $orderNumber = 'ORD-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Hitung total
            $subtotal = '0';
            foreach ($this->cart as $item) {
                $subtotal = bcadd($subtotal, bcmul((string)$item['price'], (string)$item['quantity'], 2), 2);
            }

            // Lock dan cek stok
            $productIds = collect($this->cart)->pluck('id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $insufficientProducts = [];
            foreach ($this->cart as $item) {
                $prod = $products[$item['id']] ?? null;
                if ($prod && ($prod->use_stock ?? true)) {
                    if ($prod->stock < $item['quantity']) {
                        $insufficientProducts[] = $prod->name;
                    }
                }
            }

            if (!empty($insufficientProducts)) {
                DB::rollBack();
                $this->errorMessage = 'Stok tidak cukup: ' . implode(', ', $insufficientProducts);
                return;
            }

            // Buat order PAY_LATER
            $order = Order::withoutGlobalScopes()->create([
                'user_id'        => $defaultUserId,
                'order_number'   => $orderNumber,
                'order_type'     => 'DINE_IN',
                'desk_number'    => $this->deskNumber,
                'note'           => trim($this->note) ?: null,
                'payment_method' => 'CASH',
                'discount'       => 0,
                'tax'            => 0,
                'shipping_cost'  => null,
                'customer_money' => null,
                'change'         => null,
                'grandtotal'     => $subtotal,
                'payment_status' => 'UNPAID',
                'payment_mode'   => 'PAY_LATER',
                'order_source'   => 'customer',
                'branch_id'      => $this->branchId, // null jika tidak ada branch
                'paid_at'        => null,
            ]);

            // Insert detail & kurangi stok
            foreach ($this->cart as $item) {
                $price = (string) $item['price'];
                $qty   = (int) $item['quantity'];
                $itemSubtotal = bcmul($price, (string)$qty, 2);

                TransactionDetail::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item['id'],
                    'quantity'     => $qty,
                    'price'        => $price,
                    'subtotal'     => $itemSubtotal,
                    'product_note' => $item['product_note'] ?? null,
                ]);

                $prod = $products[$item['id']] ?? null;
                if ($prod && ($prod->use_stock ?? true)) {
                    Product::where('id', $item['id'])->update([
                        'stock'      => DB::raw("stock - $qty"),
                        'sold_count' => DB::raw("sold_count + $qty"),
                    ]);
                } else {
                    Product::where('id', $item['id'])->update([
                        'sold_count' => DB::raw("sold_count + $qty"),
                    ]);
                }
            }

            DB::commit();

            // Naikkan versi cache (agar kasir reload produk & order)
            $newVer = Cache::get('product_cache_version', 1) + 1;
            Cache::put('product_cache_version', $newVer, now()->addDays(7));

            $newTxVer = Cache::get('transaction_cache_version', 1) + 1;
            Cache::put('transaction_cache_version', $newTxVer, now()->addDays(7));

            // Tandai sukses
            $this->submitted = true;
            $this->submittedOrderNumber = $orderNumber;
            $this->cart = [];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CustomerOrder error: ' . $e->getMessage());
            $this->errorMessage = 'Terjadi kesalahan, coba lagi.';
        }
    }

    public function render()
    {
        return view('livewire.customer-order', [
            'total' => $this->total,
        ])->layout('layouts.customer');
    }
}
