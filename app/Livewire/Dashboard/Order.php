<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order as ModelsOrder;
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
    ];

    // Pencarian produk
    public function searchProduct()
    {
        $this->products = Product::where('name', 'like', '%' . $this->search . '%')->take($this->limitProducts)->get();
    }

    // Tambahkan produk ke keranjang
    public function addToCart($productId)
    {
        $product = Product::find($productId);
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
        // Jika keranjang kosong, tampilkan pesan error
        if (empty($this->cart)) {
            $this->dispatch('nullPaymentSelected');
            return;
        }

        // Jika uang pelanggan kurang dari total harga atau null, tampilkan pesan error dan total kekurangannya
        if ($this->customerMoney < $this->total) {
            $shortage = $this->total - $this->customerMoney;
            $this->dispatch('insufficientPayment', $shortage);
            return; // Hentikan proses lebih lanjut
        }

        // Hitung kembalian setelah pengecekan
        $this->calculateChange();

        // Mulai transaksi database
        DB::beginTransaction();
        try {
            foreach ($this->cart as $item) {
                ModelsOrder::create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'tax' => $this->tax,
                    'discount' => 0,
                    'customer_money' => $this->customerMoney,
                    'change' => $this->change,
                    'grandtotal' => $this->total,
                ]);
            }

            DB::commit();

            // Tampilkan pesan sukses
            $this->dispatch('successPayment');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('errorPayment');
            return redirect()->back();
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
            foreach ($this->cart as $item) {
                ModelsOrder::create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'tax' => $this->tax,
                    'discount' => 0,
                    'customer_money' => $this->customerMoney,
                    'change' => $this->change,
                    'grandtotal' => $this->total,
                ]);
            }
            DB::commit();
            $this->dispatch('successPayment');
        } catch (\Exception $e) {
            DB::rollBack();
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

    public function render()
    {
        return view('livewire.dashboard.order');
    }
}