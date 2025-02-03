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
    public $customerMoney;
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $change = 0;

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

        $this->tax = $this->subtotal * 0.12; // PPN 12%
        $this->total = $this->subtotal + $this->tax;
        
    }

    // Hitung kembalian
    public function calculateChange()
    {
        $this->change = $this->customerMoney - $this->total;
    }

    public function processOrder()
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
                    'grandtotal' => $this->total,
                ]);
            }
            DB::commit();
            $this->calculateChange();
            $this->dispatch('successPayment');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage()); // Perbaikan di sini
            $this->dispatch('errorPayment');
            return redirect()->back();
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

    public function render()
    {
        return view('livewire.dashboard.order');
    }
}