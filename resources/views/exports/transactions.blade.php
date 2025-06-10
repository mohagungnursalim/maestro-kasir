<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            margin: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid black; 
            padding: 4px;
            text-align: left;
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 10px;
        }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        th:nth-child(1) { width: 15%; }
        th:nth-child(2) { width: 20%; }
        th:nth-child(3) { width: 10%; }
        th:nth-child(4) { width: 8%; }
        th:nth-child(5) { width: 8%; }
        th:nth-child(6) { width: 12%; }
        th:nth-child(7) { width: 10%; }
        th:nth-child(8) { width: 7%; }
        th:nth-child(9) { width: 10%; }

        .text-small-gray {
            font-size: 8px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Laporan Transaksi</h1>
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp
    <h2>{{ $settings->store_name }}</h2>
    <p>{{ $settings->store_address }}</p>

    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('l, d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('l, d F Y') }}</p>
    <p>Total Transaksi: {{ $transactions->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>Order/Kasir</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Metode</th>
                <th>Pajak</th>
                <th>Diskon</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalOmset = 0; // Total murni penjualan produk (tanpa Pajak & diskon)
            @endphp

            @foreach ($transactions as $order_id => $details)
                @php
                    $first = $details->first();

                    // Total murni dari harga produk (tanpa diskon dan pajak)
                    $subtotalProduk = $details->sum(fn($item) => $item->quantity * $item->product->price);

                    $grandTotalOmset += $subtotalProduk;

                    $totalPay = $first->order->grandtotal ?? 0;

                    $base = $totalPay - ($first->order->tax ?? 0);
                    $taxPercentage = $base > 0 ? round(($first->order->tax / $base) * 100) : 0;

                    $originalPrice = $base + ($first->order->discount ?? 0);
                    $discountPercentage = $originalPrice > 0 ? round(($first->order->discount / $originalPrice) * 100) : 0;
                @endphp
                <tr>
                    <td class="bold">
                        Order: {{ $first->order->order_number ?? '-' }}<br>
                        Kasir: {{ $first->order->user->name ?? '-' }}<br>
                        Tanggal: {{ $first->order->created_at->format('d-m-Y') }}
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="center">{{ $first->order->payment_method ?? '-' }}</td>
                    <td class="center">
                        @if ($taxPercentage)
                            <div class="text-small-gray">({{ number_format($taxPercentage) }}%)</div>
                        @endif
                        Rp{{ number_format($first->order->tax ?? 2, 0, ',', '.') }}
                    </td>
                    <td class="center">
                        @if ($discountPercentage)
                            <div class="text-small-gray">({{ number_format($discountPercentage) }}%)</div>
                        @endif
                        Rp{{ number_format($first->order->discount ?? 2, 0, ',', '.') }}
                    </td>
                </tr>

                @foreach ($details as $transaction)
                    <tr>
                        <td></td>
                        <td>{{ $transaction->product->name ?? '-' }}</td>
                        <td class="center">{{ $transaction->quantity }}</td>
                        <td class="center">
                            {{ $transaction->quantity }}x Rp{{ number_format($transaction->product->price, 2, ',', '.') }}<br>
                            <small>= Rp{{ number_format($transaction->product->price * $transaction->quantity, 2, ',', '.') }}</small>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach


                <tr>
                    <td colspan="7" class="bold center">Subtotal Produk: Rp{{ number_format($subtotalProduk, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Total Bayar (Setelah Diskon + Pajak): Rp{{ number_format($totalPay, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Uang Pelanggan: Rp{{ number_format($first->order->customer_money ?? 2, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Kembalian: Rp{{ number_format($first->order->change ?? 2, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-weight: bold; font-size: 18px;">
        <p style="margin: 0;">Total Omset (Tanpa Pajak & Diskon):</p>
        <p style="margin: 0; font-size: 18px;">Rp{{ number_format($grandTotalOmset, 2, ',', '.') }}</p>
    </div>
</body>
</html>
