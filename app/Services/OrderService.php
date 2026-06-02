<?php

namespace App\Services;

use App\Models\Order as ModelsOrder;
use App\Models\Product;
use App\Models\TransactionDetail;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderService
{
    /**
     * Generate unique order number
     */
    public function generateOrderNumber(): string
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

    /**
     * Delete an unpaid order and restore its product stocks
     */
    public function deleteUnpaidOrder($orderId)
    {
        DB::beginTransaction();

        try {
            // Eager load product relationship (1 query instead of N)
            $order = ModelsOrder::with('transactionDetails.product')
                ->where('id', $orderId)
                ->where('payment_status', 'UNPAID')
                ->firstOrFail();

            // Batch kembalikan stok / kurangi sold_count (max 2 queries instead of N)
            $stockDiffs = [];
            foreach ($order->transactionDetails as $item) {
                if ($item->product) {
                    $stockDiffs[(int) $item->product_id] = [
                        'diff' => -((int) $item->quantity), // negative = restore stock
                        'use_stock' => $item->product->use_stock ?? true,
                    ];
                }
            }
            $this->batchUpdateProductStock($stockDiffs);

            // Hapus detail transaksi dan order
            $order->transactionDetails()->delete();
            $order->delete();

            DB::commit();

            return ['success' => true, 'order_id' => $orderId];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Main method to process order (either update unpaid or create new)
     */
    public function processOrder(array $data)
    {
        DB::beginTransaction();

        try {
            if (!empty($data['selectedUnpaidOrderId'])) {
                $result = $this->updateUnpaidOrder($data, $data['selectedUnpaidOrderId']);
            } else {
                $result = $this->createNewOrder($data);
            }
            
            DB::commit();
            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateUnpaidOrder(array $data, $orderId)
    {
        $order = ModelsOrder::lockForUpdate()->findOrFail($orderId);

        $wasUnpaid = $order->payment_status === 'UNPAID';

        if ($order->payment_status === 'PAID') {
            throw new Exception("Order ini sudah dibayar.");
        }

        $cart = $data['cart'] ?? [];

        // Ambil detail lama
        $oldDetails = $order->transactionDetails()->get()->keyBy('product_id');

        // Gabung semua product id lama + baru
        $productIds = collect($cart)->pluck('id')
            ->merge($oldDetails->keys())
            ->unique();

        // Lock semua product
        $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

        // Cart baru
        $newCart = collect($cart)->keyBy('id');

        // ========== CEK STOK UNTUK SELISIH QTY ==========
        $insufficientProducts = [];

        foreach ($newCart as $productId => $item) {
            $newQty = (int) $item['quantity'];
            $oldQty = (int) ($oldDetails[$productId]->quantity ?? 0);
            $diff   = $newQty - $oldQty;

            if ($diff > 0) {
                $prod = $products[$productId] ?? null;
                if ($prod && ($prod->use_stock ?? true)) {
                    if ($prod->stock < $diff) {
                        $insufficientProducts[] = $prod->name ?? 'Produk tidak diketahui';
                    }
                }
            }
        }

        if (!empty($insufficientProducts)) {
            throw new Exception("INSUFFICIENT_STOCK:" . implode(", ", $insufficientProducts));
        }

        // VALIDASI MEJA
        if (empty($data['desk_number'])) {
            throw new Exception("ERROR_ORDER_TYPE");
        }

        // =========== UPDATE / INSERT DETAIL ==============
        $stockDiffs = []; 
        $newDetails = []; 
        $now = now();

        foreach ($newCart as $productId => $item) {
            $price = decimal($item['price']);
            $newQty = (int) $item['quantity'];
            $oldQty = (int) ($oldDetails[$productId]->quantity ?? 0);
            $diff   = $newQty - $oldQty;
            $subtotal = bcmul($price, (string)$newQty, 2);

            if ($oldDetails->has($productId)) {
                // UPDATE existing detail
                TransactionDetail::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->update([
                        'quantity' => $newQty,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'product_note' => $item['product_note'] ?? null,
                    ]);
            } else {
                $newDetails[] = [
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $newQty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'product_note' => $item['product_note'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($diff !== 0) {
                $stockDiffs[(int) $productId] = [
                    'diff' => $diff,
                    'use_stock' => $products[$productId]->use_stock ?? true,
                ];
            }
        }

        if (!empty($newDetails)) {
            TransactionDetail::insert($newDetails);
        }

        // ========== HAPUS ITEM YANG DIBUANG ==========
        $removedIds = [];
        foreach ($oldDetails as $productId => $oldItem) {
            if (!$newCart->has($productId)) {
                $prod = $products[$productId] ?? null;
                $stockDiffs[(int) $productId] = [
                    'diff' => -((int) $oldItem->quantity),
                    'use_stock' => $prod ? ($prod->use_stock ?? true) : true,
                ];
                $removedIds[] = $oldItem->id;
            }
        }

        if (!empty($removedIds)) {
            TransactionDetail::whereIn('id', $removedIds)->delete();
        }

        // Batch update stock
        $this->batchUpdateProductStock($stockDiffs);

        // ========== HITUNG ULANG TOTAL ==========
        $subtotalAmount = '0';
        foreach ($newCart as $item) {
            $subtotal = bcmul(decimal($item['price']), (string)$item['quantity'], 2);
            $subtotalAmount = bcadd($subtotalAmount, $subtotal, 2);
        }

        $discount = '0';
        if ($data['familyDiscount'] ?? false) {
            $discount = $subtotalAmount; 
        } elseif ($data['friendDiscount'] ?? false) {
            $discount = bcdiv(bcmul($subtotalAmount, '20', 2), '100', 2); 
        }

        $totalVal = bcsub($subtotalAmount, $discount, 2);
        $tax = decimal($data['tax']);
        $totalVal = bcadd($totalVal, $tax, 2);

        if (bccomp($totalVal, '0', 2) === -1 && empty($data['familyDiscount'])) {
            throw new Exception("Total tidak valid");
        }

        $shippingCost = ($data['shippingEnabled'] ?? false) ? decimal($data['shippingCost']) : null;
        $totalWithShipping = $shippingCost ? bcadd($totalVal, $shippingCost, 2) : $totalVal;

        $orderBaseUpdate = [
            'order_type'    => $data['order_type'],
            'desk_number'   => $data['desk_number'],
            'note'          => $data['note'] ?? null,
            'payment_method'=> $data['payment_method'],
            'discount'      => $discount,
            'tax'           => $tax,
            'shipping_cost' => $shippingCost,
            'grandtotal'    => $totalWithShipping,
        ];

        $paymentStatus = 'UNPAID';
        if ($data['payment_mode'] === 'PAY_NOW') {
            $customerMoney = decimal($data['customerMoney']);

            if (bccomp($customerMoney, $totalVal, 2) === -1) {
                $shortage = bcsub($totalVal, $customerMoney, 2);
                throw new Exception("INSUFFICIENT_PAYMENT:" . $shortage);
            }

            $order->update(array_merge($orderBaseUpdate, [
                'payment_status' => 'PAID',
                'payment_mode' => 'PAY_NOW',
                'customer_money' => $customerMoney,
                'change' => bcsub($customerMoney, $totalVal, 2),
                'paid_at' => now(),
            ]));
            
            $paymentStatus = 'PAID';

        } else {
            $order->update(array_merge($orderBaseUpdate, [
                'payment_status' => 'UNPAID',
                'payment_mode' => 'PAY_LATER',
                'customer_money' => null,
                'change' => null,
                'paid_at' => null,
            ]));
        }

        $this->handlePlatformFee($data, $order);

        return [
            'status' => 'success',
            'order_id' => $order->id,
            'payment_status' => $paymentStatus,
            'was_unpaid' => $wasUnpaid,
        ];
    }

    private function createNewOrder(array $data)
    {
        $cart = $data['cart'] ?? [];
        if (empty($cart)) {
            return ['status' => 'empty'];
        }

        // LOCK PRODUCT
        $productIds = collect($cart)->pluck('id');
        $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

        $insufficientProducts = [];
        foreach ($cart as $item) {
            $prod = $products[$item['id']] ?? null;
            if ($prod && ($prod->use_stock ?? true)) {
                if ($prod->stock < $item['quantity']) {
                    $insufficientProducts[] = $prod->name ?? 'Produk tidak diketahui';
                }
            }
        }

        if (!empty($insufficientProducts)) {
            throw new Exception("INSUFFICIENT_STOCK:" . implode(", ", $insufficientProducts));
        }

        // VALIDASI MEJA
        if (empty($data['desk_number'])) {
            throw new Exception("ERROR_ORDER_TYPE");
        }

        // HITUNG TOTAL
        $subtotalAmount = '0';
        foreach ($cart as $item) {
            $subtotal = bcmul(decimal($item['price']), (string)$item['quantity'], 2);
            $subtotalAmount = bcadd($subtotalAmount, $subtotal, 2);
        }

        $discount = '0';
        if ($data['familyDiscount'] ?? false) {
            $discount = $subtotalAmount; 
        } elseif ($data['friendDiscount'] ?? false) {
            $discount = bcdiv(bcmul($subtotalAmount, '20', 2), '100', 2); 
        }

        $totalVal = bcsub($subtotalAmount, $discount, 2);
        $tax = decimal($data['tax']);
        $totalVal = bcadd($totalVal, $tax, 2);

        $shippingCost = ($data['shippingEnabled'] ?? false) ? decimal($data['shippingCost']) : null;
        if ($shippingCost) {
            $totalVal = bcadd($totalVal, $shippingCost, 2);
        }

        if (bccomp($totalVal, '0', 2) === -1 && empty($data['familyDiscount'])) {
            throw new Exception("Total tidak valid");
        }

        $customerMoney = null;
        $change = null;
        $paymentStatus = 'UNPAID';
        $paidAt = null;

        if ($data['payment_mode'] === 'PAY_NOW') {
            $customerMoney = decimal($data['customerMoney']);

            if (bccomp($customerMoney, $totalVal, 2) === -1) {
                $shortage = bcsub($totalVal, $customerMoney, 2);
                throw new Exception("INSUFFICIENT_PAYMENT:" . $shortage);
            }

            $change = bcsub($customerMoney, $totalVal, 2);
            $paymentStatus = 'PAID';
            $paidAt = now();
        }

        // CREATE ORDER
        $order = ModelsOrder::create([
            'user_id'        => Auth::id(),
            'order_number'   => $this->generateOrderNumber(),
            'order_type'     => $data['order_type'],
            'desk_number'    => $data['desk_number'],
            'note'           => $data['note'] ?? null,
            'payment_method' => $data['payment_method'],
            'discount'       => $discount,
            'tax'            => $tax,
            'shipping_cost'  => $shippingCost,
            'customer_money' => $customerMoney,
            'change'         => $change,
            'grandtotal'     => $totalVal,
            'payment_status' => $paymentStatus,
            'payment_mode'   => $data['payment_mode'],
            'paid_at'        => $paidAt,
        ]);

        $details = [];
        $stockDiffs = [];
        $now = now();
        foreach ($cart as $item) {
            $price = decimal($item['price']);
            $qty   = (int) $item['quantity'];
            $details[] = [
                'order_id'     => $order->id,
                'product_id'   => $item['id'],
                'quantity'     => $qty,
                'price'        => $price,
                'subtotal'     => bcmul($price, (string)$qty, 2),
                'product_note' => $item['product_note'] ?? null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $prod = $products[$item['id']] ?? null;
            $stockDiffs[(int) $item['id']] = [
                'diff'      => $qty,
                'use_stock' => $prod ? ($prod->use_stock ?? true) : true,
            ];
        }
        TransactionDetail::insert($details);

        $this->batchUpdateProductStock($stockDiffs);
        $this->handlePlatformFee($data, $order);

        return [
            'status' => 'success',
            'order_id' => $order->id,
            'payment_status' => $paymentStatus,
            'was_unpaid' => false,
        ];
    }

    private function handlePlatformFee(array $data, ModelsOrder $order)
    {
        if (!empty($data['platformFeeEnabled']) && !empty($data['platformFee']) && $data['platformFee'] > 0) {
            Expense::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'category' => 'Komisi Aplikasi',
                    'description' => 'Potongan Komisi ' . $order->order_type . ' ' . $order->order_number,
                ],
                [
                    'expense_date' => now()->format('Y-m-d'),
                    'type' => 'out',
                    'amount' => decimal($data['platformFee']),
                ]
            );
        } else {
            Expense::where('category', 'Komisi Aplikasi')
                ->where('description', 'Potongan Komisi ' . $order->order_type . ' ' . $order->order_number)
                ->delete();
        }
    }

    /**
     * Batch update product stock & sold_count
     */
    private function batchUpdateProductStock(array $diffs): void
    {
        if (empty($diffs)) return;

        $withStock = [];
        $withoutStock = [];
        foreach ($diffs as $id => $data) {
            if ($data['use_stock']) {
                $withStock[(int) $id] = (int) $data['diff'];
            } else {
                $withoutStock[(int) $id] = (int) $data['diff'];
            }
        }

        if (!empty($withStock)) {
            $stockCases = collect($withStock)->map(fn($diff, $id) =>
                "WHEN id = {$id} THEN stock - ({$diff})")->implode(' ');
            $soldCases = collect($withStock)->map(fn($diff, $id) =>
                "WHEN id = {$id} THEN sold_count + ({$diff})")->implode(' ');
            $ids = implode(',', array_keys($withStock));
            DB::update("UPDATE products SET stock = CASE {$stockCases} ELSE stock END, sold_count = CASE {$soldCases} ELSE sold_count END WHERE id IN ({$ids})");
        }

        if (!empty($withoutStock)) {
            $soldCases = collect($withoutStock)->map(fn($diff, $id) =>
                "WHEN id = {$id} THEN sold_count + ({$diff})")->implode(' ');
            $ids = implode(',', array_keys($withoutStock));
            DB::update("UPDATE products SET sold_count = CASE {$soldCases} ELSE sold_count END WHERE id IN ({$ids})");
        }
    }
}
