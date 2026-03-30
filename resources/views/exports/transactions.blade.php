<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        @page {
            margin: 40px 30px;
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px;
            margin: 0px;
            color: #333;
        }
        
        /* Report Header Styles */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .report-header h2 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
            text-transform: uppercase;
            font-weight: bold;
        }
        .report-header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #555;
        }
        .report-header h1 {
            margin: 15px 0 0 0;
            font-size: 18px;
            letter-spacing: 1px;
            color: #333;
        }
        
        /* Report Meta Styles */
        .report-meta {
            width: 100%;
            margin-bottom: 20px;
        }
        .report-meta td {
            border: none;
            padding: 3px;
            font-size: 11px;
            vertical-align: top;
        }
        .report-meta .label {
            font-weight: bold;
            width: 90px;
        }
        .text-right {
            text-align: right;
        }

        /* Table Styles */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            page-break-inside: avoid;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        th, td { 
            border: 1px solid #7f8c8d; 
            padding: 5px 4px;
            text-align: left;
        }
        th { 
            background-color: #ecf0f1; 
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        .bold { font-weight: bold; }
        .center { text-align: center; }
        
        /* Adjust column width */
        th:nth-child(1) { width: 18%; }
        th:nth-child(2) { width: 24%; }
        th:nth-child(3) { width: 7%; }
        th:nth-child(4) { width: 15%; }
        th:nth-child(5) { width: 10%; }
        th:nth-child(6) { width: 13%; }
        th:nth-child(7) { width: 13%; }

        .text-small-gray {
            font-size: 8px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $firstOrder = collect($transactions)->first()?->first()?->order;
        $branchName = $firstOrder?->branch?->name ?? 'Laporan Semua Cabang';
        $branchAddress = $firstOrder?->branch?->address ?? '-';
    @endphp

    <div class="report-header">
        <h2>{{ $branchName }}</h2>
        <p>{{ $branchAddress }}</p>
        <h1>Laporan Transaksi</h1>
    </div>

    <table class="report-meta">
        <tr>
            <td class="label">Periode</td>
            <td style="width: 10px;">:</td>
            <td>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
            
            <td class="label text-right">Tanggal Cetak</td>
            <td style="width: 10px;">:</td>
            <td class="text-right" style="width: 130px;">{{ \Carbon\Carbon::now()->translatedFormat('d F Y  H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Total Transaksi</td>
            <td>:</td>
            <td>{{ $transactions->count() }} Transaksi</td>
            
            <td class="label text-right">Dicetak Oleh</td>
            <td>:</td>
            <td class="text-right">{{ $userName ?? 'Sistem' }}</td>
        </tr>
    </table>

    @php
        $grandTotalOmset = 0; // Total murni penjualan produk (tanpa Pajak & diskon)
        $grandTotalDiskon = 0;
        $grandTotalPajak = 0;
        $grandTotalNett = 0;
    @endphp

    @foreach ($transactions as $order_id => $details)
    <table style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th>Order/Kasir</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Metode</th>
                <th>Diskon</th>
                <th>Pajak</th>
            </tr>
        </thead>
        <tbody>
                @php
                    $first = $details->first();

                    // Total murni dari harga produk (tanpa diskon dan pajak)
                    $subtotalProduk = $details->sum(fn($item) => $item->quantity * $item->product->price);

                    $grandTotalOmset += $subtotalProduk;

                    $totalPay = $first->order->grandtotal ?? 0;
                    $taxAmount = $first->order->tax ?? 0;
                    $discountAmount = $first->order->discount ?? 0;

                    $grandTotalDiskon += $discountAmount;
                    $grandTotalPajak += $taxAmount;
                    $grandTotalNett += $totalPay;

                    $base = $totalPay - $taxAmount;
                    $taxPercentage = $base > 0 ? round(($taxAmount / $base) * 100) : 0;

                    $originalPrice = $subtotalProduk;
                    $discountPercentage = $originalPrice > 0 ? round(($discountAmount / $originalPrice) * 100) : 0;
                @endphp
                <tr>
                    
                    <td class="bold">
                        Order: {{ $first->order->order_number ?? '-' }}<br>
                        Kasir: {{ $first->order->user->name ?? '-' }}<br>
                        Cabang: {{ $first->order->branch->name ?? 'Semua Cabang' }}<br>
                        Tanggal: {{ $first->order->created_at->format('d-m-Y') }}
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="center">{{ $first->order->payment_method ?? '-' }}</td>
                    <td class="center">
                        @if ((float)$discountAmount > 0)
                            @if ($discountPercentage)
                                <div class="text-small-gray">({{ number_format($discountPercentage) }}%)</div>
                            @endif
                            Rp{{ number_format($discountAmount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="center">
                        @if ((float)$taxAmount > 0)
                            @if ($taxPercentage)
                                <div class="text-small-gray">({{ number_format($taxPercentage) }}%)</div>
                            @endif
                            Rp{{ number_format($taxAmount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>

                @foreach ($details as $transaction)
                    <tr>
                        <td></td>
                        <td>{{ $transaction->product->name ?? '-' }}</td>
                        <td class="center">{{ $transaction->quantity }}</td>
                        <td class="center">
                            {{ $transaction->quantity }}x Rp{{ number_format($transaction->product->price, 0, ',', '.') }}<br>
                            <small>= Rp{{ number_format($transaction->product->price * $transaction->quantity, 0, ',', '.') }}</small>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach


                <tr>
                    <td colspan="7" class="bold center">Subtotal Produk: Rp{{ number_format($subtotalProduk, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Diskon: 
                        @if((float)$discountAmount > 0)
                            <a class="text-small-gray">({{ number_format($discountPercentage) }}%)</a>
                            Rp{{ number_format($discountAmount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Pajak: 
                        @if((float)$taxAmount > 0)
                        Rp{{ number_format($taxAmount, 0, ',', '.') }}
                        @else
                        -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Total Bayar (Setelah Diskon + Pajak): Rp{{ number_format($totalPay, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">
                        @if(($first->order->payment_method ?? '') === 'QRIS')
                            QRIS
                        @elseif(($first->order->payment_method ?? '') === 'CASH')
                            Uang Pelanggan
                        @else
                            {{ $first->order->payment_method ?? 'Uang Pelanggan' }}
                        @endif
                        : Rp{{ number_format($first->order->customer_money ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="bold center">Kembalian: 
                        @if(($first->order->payment_method ?? '') === 'CASH')
                            @if((float)($first->order->change ?? 0) > 0)
                                Rp{{ number_format($first->order->change ?? 0, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div style="margin-top: 20px; text-align: right; font-weight: bold; font-size: 16px;">
        <p style="margin: 0; padding-top: 4px;">Total Omset (Murni Produk): Rp{{ number_format($grandTotalOmset, 0, ',', '.') }}</p>
        <p style="margin: 0; padding-top: 4px;">Total Diskon: 
            @if((float)$grandTotalDiskon > 0)
                Rp{{ number_format($grandTotalDiskon, 0, ',', '.') }}
            @else
                -
            @endif
        </p>
        <p style="margin: 0; padding-top: 4px;">Total Pajak: 
            @if((float)$grandTotalPajak > 0)
                Rp{{ number_format($grandTotalPajak, 0, ',', '.') }}
            @else
                -
            @endif
        </p>
        <hr style="width: 300px; margin-left: auto; margin-right: 0;">
        <p style="margin: 0; font-size: 18px;">Total Pendapatan Bersih: Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</p>
    </div>
</body>
</html>
