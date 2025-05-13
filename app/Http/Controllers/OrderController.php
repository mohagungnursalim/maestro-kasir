<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function receipt($orderId)
    {
        $order = Order::with(['transactionDetails.product', 'user'])->findOrFail($orderId);
        
        return view('receipt', compact('order'));
    }

    public function bill()
    {
        // Ambil dari cache/session
        $billData = cache()->get('bill-preview:' . auth()->id());

        if (!$billData) {
            abort(404, 'Data bill tidak ditemukan');
        }

        return view('bill', compact('billData'));
    }

}
