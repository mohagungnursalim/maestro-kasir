<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PrintService
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function generateBillPreview(array $data)
    {
        $cart = $data['cart'] ?? [];
        if (empty($cart)) {
            return false;
        }

        $orderNumber = $this->orderService->generateOrderNumber();
        $subtotalBefore = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        $discountAmount = 0;
        if (!empty($data['familyDiscount'])) {
            $discountAmount = $subtotalBefore;
        } elseif (!empty($data['friendDiscount'])) {
            $discountAmount = ($subtotalBefore * 20) / 100;
        }

        $shipping = !empty($data['shippingEnabled']) ? (float) ($data['shippingCost'] ?? 0) : 0;

        $billData = [
            'tanggal'       => now()->format('d-m-Y H:i'),
            'kasir'         => Auth::user()->name ?? 'Owner',
            'order_number'  => $orderNumber,
            'items'         => [],
            'subtotal'      => 0,
            'discount'      => $discountAmount,
            'tax'           => $data['tax'] ?? 0,
            'shipping_cost' => $shipping,
            'total'         => $data['total'] ?? 0,
        ];

        $subtotal = 0;
        foreach ($cart as $item) {
            $total = $item['price'] * $item['quantity'];
            $subtotal += $total;

            $billData['items'][] = [
                'name'  => $item['name'],
                'qty'   => $item['quantity'],
                'price' => $item['price'],
                'total' => $total,
            ];
        }

        $billData['subtotal'] = $subtotal;

        Cache::put('bill-preview:' . Auth::id(), $billData, now()->addMinutes(5));
        return true;
    }

    public function generateKitchenPreview(array $data)
    {
        $cart = $data['cart'] ?? [];
        if (empty($cart)) {
            return false;
        }

        $orderNumber = $this->orderService->generateOrderNumber();
        $kitchenData = [
            'tanggal'      => now()->format('d-m-Y H:i'),
            'order_number' => $orderNumber,
            'desk_number'  => $data['desk_number'] ?? '',
            'order_type'   => $data['order_type'] ?? '',
            'note'         => $data['note'] ?? '',
            'items'        => [],
        ];

        foreach ($cart as $item) {
            $kitchenData['items'][] = [
                'name' => $item['name'],
                'qty'  => $item['quantity'],
                'note' => $item['product_note'] ?? null,
            ];
        }

        Cache::put('kitchen-preview:' . Auth::id(), $kitchenData, now()->addMinutes(5));
        return true;
    }

    public function generateMultiSplitPreview(array $data)
    {
        $cart = $data['cart'] ?? [];
        if (empty($cart)) {
            return false;
        }

        $count = max(1, (int) ($data['splitCount'] ?? 1));
        $multi = [];
        $orderNumberPrefix = $this->orderService->generateOrderNumber();

        for ($i = 1; $i <= $count; $i++) {
            $multi[$i] = [
                'tanggal'      => now()->format('d-m-Y H:i'),
                'kasir'        => Auth::user()->name ?? 'Owner',
                'order_number' => $orderNumberPrefix . "-S$i",
                'items'        => [],
                'subtotal'     => 0,
                'discount'     => 0,
                'tax'          => 0,
                'total'        => 0,
            ];
        }

        foreach ($cart as $item) {
            $group = isset($item['assigned_to']) ? (int) $item['assigned_to'] : 1;
            if ($group < 1 || $group > $count) $group = 1;

            $total = $item['price'] * $item['quantity'];
            $multi[$group]['items'][] = [
                'name'  => $item['name'],
                'qty'   => $item['quantity'],
                'price' => $item['price'],
                'total' => $total,
            ];
            $multi[$group]['subtotal'] += $total;
        }

        foreach ($multi as $i => $md) {
            $discount = 0;
            if (!empty($data['familyDiscount'])) {
                $discount = $md['subtotal'];
            } elseif (!empty($data['friendDiscount'])) {
                $discount = ($md['subtotal'] * 20) / 100;
            }
            
            $tax = 0;
            if (!empty($data['is_tax'])) {
                $subtotalAfterDiscount = $md['subtotal'] - $discount;
                $taxPercentage = $data['tax_percentage'] ?? 0;
                $tax = ($subtotalAfterDiscount * $taxPercentage) / 100;
            }
            
            $multi[$i]['discount'] = $discount;
            $multi[$i]['tax'] = $tax;
            $multi[$i]['total'] = ($md['subtotal'] - $discount) + $tax;
        }

        Cache::put('bill-preview-multi:' . Auth::id(), $multi, now()->addMinutes(5));
        return true;
    }

    public function getKitchenData($userId)
    {
        return Cache::get('kitchen-preview:' . $userId);
    }

    public function getBillData($userId)
    {
        return Cache::get('bill-preview:' . $userId);
    }

    public function getMultiBillData($userId)
    {
        return Cache::get('bill-preview-multi:' . $userId);
    }
}
