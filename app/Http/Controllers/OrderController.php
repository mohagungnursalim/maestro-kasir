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

    public function kitchen(Request $request, \App\Services\PrintService $printService)
    {
        $kitchenData = $printService->getKitchenData(auth()->id());

        if (!$kitchenData) {
            abort(404, 'Data struk dapur tidak ditemukan');
        }

        return view('kitchen', compact('kitchenData'));
    }

    public function bill(Request $request, \App\Services\PrintService $printService)
    {
        // Multi-split preview (from cache)
        if ($request->has('multi')) {
            $multi = $printService->getMultiBillData(auth()->id());
            if (!$multi) {
                abort(404, 'Data bill tidak ditemukan');
            }

            $split = (int) $request->get('split', 1);
            if (!isset($multi[$split])) {
                abort(404, 'Split tidak ditemukan');
            }

            $billData = $multi[$split];
            // Provide meta info to view
            $billData['__multi'] = ['split' => $split, 'count' => count($multi)];
            return view('bill', compact('billData'));
        }

        // Fallback: single preview (existing behavior)
        $billData = $printService->getBillData(auth()->id());

        if (!$billData) {
            abort(404, 'Data bill tidak ditemukan');
        }

        return view('bill', compact('billData'));
    }

}
