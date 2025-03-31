<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 58mm; /* Ukuran standar struk */
            margin: 0 auto;
            padding: 0;
        }
        .receipt {
            padding: 5px;
            text-align: center;
        }
        .receipt h2 {
            margin: 0;
            font-size: 13px;
        }
        .receipt p {
            margin: 2px 0;
        }
        .line {
            border-top: 1px dashed black;
            margin: 5px 0;
        }
        .logo {
            max-height: 40px;
            margin: 0 auto 5px;
        }
        table {
            width: 100%;
            font-size: 10px;
            text-align: left;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
        }
        .total {
            font-weight: bold;
        }
        .right {
            text-align: right;
            padding-right: 5px;
        }
    </style>
</head>
<body onload="startPrint();">
    <script>
        function startPrint() {
            window.print();
        }
        window.onafterprint = function () {
            setTimeout(() => { window.close(); }, 500);
        };
    </script>

    <div class="receipt">
        @if ($settings->store_logo)
            <img src="{{ asset($settings->store_logo) }}" 
                alt="{{ $settings->store_name }}" 
                class="logo">
        @endif
        
        <h2>{{ $settings->store_name }}</h2>
        <p>{{ $settings->store_address }}</p>
        <p>Telp: {{ $settings->store_phone }}</p>
        <p>Tanggal: {{ now()->format('d-m-Y H:i') }}</p>
        <p>Kasir: {{ $order->user->name ?? 'Tidak Diketahui' }}</p>
        
        <div class="line"></div>

        <table>
            <tbody>
                @foreach ($order->transactionDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="right">{{ $detail->quantity }}x</td>
                    <td class="right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">{{ number_format($order->grandtotal - $order->tax, 0, ',', '.') }}</td>
            </tr>
            @php
                $subtotal = $order->grandtotal - $order->tax;
                $taxPercentage = $subtotal > 0 ? ($order->tax / $subtotal) * 100 : 0;

                $discount = $order->grandtotal - $order->discount;
                $discountPercentage = $subtotal > 0 ? ($order->discount / $subtotal) * 100 : 0;
            @endphp

            @if ($taxPercentage)
            <tr>
                <td>Pajak ({{ number_format($taxPercentage, 0) }}%)</td>
                <td class="right">{{ number_format($order->tax, 0, ',', '.') }}</td>
            </tr>
            @endif

            @if ($discountPercentage)
            <tr>
                <td>Diskon ({{ number_format($discountPercentage, 0) }}%)</td>
                <td class="right">{{ number_format($order->discount, 0, ',', '.') }}</td>
            </tr>
            @endif

            <tr class="total">
                <td>Total</td>
                <td class="right">{{ number_format($order->grandtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Uang Pelanggan</td>
                <td class="right">{{ number_format($order->customer_money, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="right">{{ number_format($order->change, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="line"></div>
        <p>{{ $settings->store_footer }}</p>
    </div>
</body>
</html>
