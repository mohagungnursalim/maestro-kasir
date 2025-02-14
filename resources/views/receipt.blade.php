<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            width: 58mm; /* Ukuran standar thermal printer */
            margin: 0;
            padding: 0;
        }
        .receipt {
            padding: 10px;
            text-align: center;
        }
        .receipt h2 {
            margin: 0;
            font-size: 14px;
        }
        .receipt p {
            margin: 2px 0;
        }
        .receipt .line {
            border-top: 1px dashed black;
            margin: 5px 0;
        }
        .receipt img {
        max-height: 50px; /* Logo lebih kecil */
        display: block;
        margin: 0 auto 5px;
        }
        table {
            width: 100%;
            font-size: 12px;
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
            font-size: 10px;
            padding-right: 2px;
        }
    </style>
</head>
<body onload="startPrint();">
    <script>
        function startPrint() {
            window.print();
        }

        window.onafterprint = function () {
            setTimeout(() => {
                window.close();
            }, 500); // Delay untuk memastikan print selesai sebelum close
        };
    </script>

    <div class="receipt">
        @if ($settings->store_logo)
            <img src="{{ asset($settings->store_logo) }}" 
                alt="{{ $settings->store_name }}" 
                class="h-16 mx-auto mb-2">
        @endif
        
        <h2 class="text-center">{{ $settings->store_name }}</h2>
        <p class="text-center">{{ $settings->store_address }}</p>
        <p class="text-center">Telp: {{ $settings->store_phone }}</p>
        <p class="text-center">Tanggal: {{ now()->format('d-m-Y H:i') }}</p>
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
            <tr>
                <td>Pajak (11%)</td>
                <td class="right">{{ number_format($order->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td class="right">Rp{{ number_format($order->grandtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Uang Pelanggan</td>
                <td class="right">{{ number_format($order->customer_money, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="right">Rp{{ number_format($order->change, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="line"></div>
        <p>{{ $settings->store_footer }}</p>
    </div>
</body>


</html>
