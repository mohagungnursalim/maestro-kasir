<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; /* Font lebih kecil */
            margin: 20px; /* Kurangi margin biar muat */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid black; 
            padding: 4px; /* Padding dikurangi */
            text-align: left;
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 10px;
        }
        .bold { font-weight: bold; }
        .center { text-align: center; } /* Biar angka rata kanan */

        /* Atur lebar kolom */
        th:nth-child(1) { width: 15%; } /* ID Order/Kasir */
        th:nth-child(2) { width: 20%; } /* Nama Produk */
        th:nth-child(3) { width: 10%; } /* Pembayaran */
        th:nth-child(4) { width: 8%; }  /* Pajak */
        th:nth-child(5) { width: 8%; }  /* Diskon */
        th:nth-child(6) { width: 12%; } /* Uang Pelanggan */
        th:nth-child(7) { width: 10%; } /* Kembalian */
        th:nth-child(8) { width: 7%; }  /* Jumlah */
        th:nth-child(9) { width: 10%; } /* Harga */

        .text-small-gray {
        font-size: 8px;
        color: #6b7280; /* abu-abu ke arah Tailwind gray-500 */
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
                $grandTotal = 0;
            @endphp
    
            @foreach ($transactions as $order_id => $details)
                @php
                    $first = $details->first();
                    $subtotal = $details->sum(fn($item) => $item->quantity * $item->product->price);
                    $totalPay = $first->order->grandtotal ?? 0;
                    $grandTotal += $totalPay;
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
                    @php
                        
                        $base = $first->order->grandtotal - $first->order->tax;
                        $taxPercentage = $base > 0 ? round(($first->order->tax / $base) * 100) : 0;

                        $originalPrice = $base + $first->order->discount;
                        $discountPercentage = $originalPrice > 0 ? round(($first->order->discount / $originalPrice) * 100) : 0;
                    @endphp
                   
                   <td class="center">
                        @if ($taxPercentage)
                            <div class="text-small-gray">({{ number_format($taxPercentage) }}%)</div>
                        @endif
                        Rp{{ number_format($first->order->tax ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="center">
                        @if ($discountPercentage)
                            <div class="text-small-gray">({{ number_format($discountPercentage) }}%)</div>
                        @endif
                        Rp{{ number_format($first->order->discount ?? 0, 0, ',', '.') }}
                    </td>
                
                </tr>
    
                @foreach ($details as $transaction)
                    <tr>
                        <td></td>
                        <td>{{ $transaction->product->name ?? '-' }}</td>
                        <td class="center">{{ $transaction->quantity }}</td>
                        <td class="center">Rp{{ number_format($transaction->product->price, 2, ',', '.') }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
    
                <tr>
                    <td colspan="7" class="bold center">Subtotal: Rp{{ number_format($subtotal, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Total Bayar: Rp{{ number_format($totalPay, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Uang Pelanggan: Rp{{ number_format($first->order->customer_money ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Kembalian: Rp{{ number_format($first->order->change ?? 2, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    
    
    <div style="margin-top: 20px; text-align: right; font-weight: bold; font-size: 18px;">
        <p style="margin: 0;">Total Omset:</p>
        <p style="margin: 0; font-size: 18px;">Rp{{ number_format($grandTotal, 2, ',', '.') }}</p>
    </div>
    
</body>
</html>
