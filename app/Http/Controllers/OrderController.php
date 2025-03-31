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
}
