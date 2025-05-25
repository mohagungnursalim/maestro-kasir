<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 58mm;
            /* Ukuran standar struk */
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

        .powered-by {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #777;
        }

        .powered-by p {
            margin: 0;
        }

        .powered-by a {
            color: #444;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px dashed #aaa;
            transition: all 0.2s ease-in-out;
        }

        .powered-by a:hover {
            color: #000;
            border-bottom: 1px solid #444;
            text-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
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
            }, 500);
        };

    </script>

    <div class="receipt">
        @if ($settings->store_logo)
        <img src="{{ asset($settings->store_logo) }}" alt="{{ $settings->store_name }}" class="logo">
        @endif

        <h2>{{ $settings->store_name }}</h2>
        <p>{{ $settings->store_address }}</p>
        <p>Telp: {{ $settings->store_phone }}</p>
        <p>Tanggal: {{ now()->format('d-m-Y H:i') }}</p>
        <p>Kasir: {{ $order->user->name ?? 'Tidak Diketahui' }}</p>
        <p>Kode Order: {{ $order->order_number }}</p>

        <div class="line"></div>

        @php
            $subtotal = $order->transactionDetails->sum(function ($detail) {
            return $detail->price * $detail->quantity;
            });
        @endphp

        <table>
            <tbody>
                @foreach ($order->transactionDetails as $detail)
                <tr>
                    <td colspan="2">
                        {{ $detail->product->name }}<br>
                        <small class="text-gray-500">{{ $detail->quantity }}x Rp{{ number_format($detail->price, 2, ',', '.') }}</small>
                    </td>
                    <td class="text-right">
                        Rp{{ number_format($detail->price * $detail->quantity, 2, ',', '.') }}
                    </td>
                </tr>
                
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">{{ number_format($subtotal, 2, ',', '.') }}</td>
            </tr>

            @php
            $hargaAwal = $order->grandtotal + $order->discount - $order->tax;
            $hargaSetelahDiskon = $hargaAwal - $order->discount;

            $taxPercentage = $hargaSetelahDiskon > 0 ? ($order->tax / $hargaSetelahDiskon) * 100 : 0;
            $discountPercentage = $hargaAwal > 0 ? ($order->discount / $hargaAwal) * 100 : 0;
            @endphp


            @if ($taxPercentage)
            <tr>
                <td>PPN ({{ number_format($taxPercentage, 0) }}%)</td>
                <td class="right">{{ number_format($order->tax, 2, ',', '.') }}</td>
            </tr>
            @endif

            @if ($discountPercentage)
            <tr>
                <td>Diskon ({{ number_format($discountPercentage, 0) }}%)</td>
                <td class="right">{{ number_format($order->discount, 2, ',', '.') }}</td>
            </tr>
            @endif

            <tr class="total">
                <td>Total Bayar</td>
                <td class="right">{{ number_format($order->grandtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Uang Pelanggan</td>
                <td class="right">{{ number_format($order->customer_money, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian</td>
                <td class="right">{{ number_format($order->change, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="line"></div>
        <p>{{ $settings->store_footer }}</p>
    </div>
    <div class="powered-by">
        <p>Powered by <a href="" target="_blank">Maestro-POS</a></p>
    </div>

</body>

</html>
