<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function receipt($orderId)
    {
        $order = Order::with(['transactionDetails.product', 'user'])->findOrFail($orderId);
        
        return view('receipt', compact('order'));
    }

    public function kitchen(Request $request)
    {
        $kitchenData = cache()->get('kitchen-preview:' . auth()->id());

        if (!$kitchenData) {
            abort(404, 'Data struk dapur tidak ditemukan');
        }

        return view('kitchen', compact('kitchenData'));
    }

    public function bill(Request $request)
    {
        // Multi-split preview (from cache)
        if ($request->has('multi')) {
            $multi = cache()->get('bill-preview-multi:' . auth()->id());
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
        $billData = cache()->get('bill-preview:' . auth()->id());

        if (!$billData) {
            abort(404, 'Data bill tidak ditemukan');
        }

        return view('bill', compact('billData'));
    }

    /**
     * Generate QR code page untuk meja tertentu (cetak & pajang di meja)
     * Menggunakan Token rahasia agar aman dari user yang coba tebak nama meja
     */
    public function generateQr(Request $request, string $desk)
    {
        $desk = urldecode($desk);
        $activeBranchId = session('active_branch_id');

        // Cari atau buat Record Meja
        $table = \App\Models\Table::firstOrCreate(
            ['name' => $desk, 'branch_id' => $activeBranchId],
            ['token' => \Illuminate\Support\Str::random(12), 'is_active' => true]
        );

        // Jika meja tidak aktif
        if (!$table->is_active) {
            abort(403, 'Meja ini dinonaktifkan.');
        }

        // Build URL dengan TOKEN
        $menuUrl = route('customer.order', ['token' => $table->token]);

        $settings = StoreSetting::first();
        $storeName = $settings->store_name ?? config('app.name');

        return view('qr-table', compact('desk', 'menuUrl', 'storeName'));
    }
}

