<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        @page { size: 58mm auto; margin: 0; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 58mm;
            margin: 0 auto;
            padding: 0;
            color: #000;
        }

        .receipt { padding: 6px 5px 10px; }

        .header { text-align: center; }
        .logo { max-height: 36px; margin: 0 auto 4px; display: block; }

        .store-name { font-size: 12px; font-weight: bold; letter-spacing: .3px; }
        .store-address { font-size: 9px; }

        .meta, .items, .summary {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td { padding: 1px 0; vertical-align: top; }
        .meta .label { width: 35%; }
        .meta .colon { width: 5%; }

        .line { border-top: 1px dashed #000; margin: 6px 0; }

        .items td { padding: 2px 0; vertical-align: top; }
        .item-name { font-size: 10px; }
        .item-sub { font-size: 9px; }
        .price { text-align: right; white-space: nowrap; }

        .summary td { padding: 2px 0; }
        .summary .label { text-align: left; }
        .summary .value { text-align: right; white-space: nowrap; }

        .total {
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        .footer {
            text-align: center;
            margin-top: 6px;
            font-size: 9px;
        }

        .powered-by {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #777;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }

        .powered-by a {
            color: #444;
            text-decoration: none;
            border-bottom: 1px dashed #aaa;
        }
    </style>
</head>

<body onload="startPrint();">
<script>
    function startPrint() { window.print(); }
    window.onafterprint = function () { setTimeout(() => window.close(), 500); };
</script>

<div class="receipt">

    <div class="header">
        @if ($settings->store_logo)
            <img src="{{ asset($settings->store_logo) }}" alt="{{ $settings->store_name }}" class="logo">
        @endif
        <div class="store-name">{{ $settings->store_name }}</div>
        <div class="store-address">{{ $settings->store_address }}</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Telp</td>
            <td class="colon">:</td>
            <td>{{ $settings->store_phone }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal/Jam</td>
            <td class="colon">:</td>
            <td>{{ now()->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Kasir</td>
            <td class="colon">:</td>
            <td>{{ $order->user->name ?? 'Tidak Diketahui' }}</td>
        </tr>
        <tr>
            <td class="label">Order</td>
            <td class="colon">:</td>
            <td>{{ $order->order_number }}</td>
        </tr>
    </table>

    <div class="line"></div>

    @php
        $subtotal = $order->transactionDetails->sum(fn ($d) => $d->price * $d->quantity);
        $hargaAwal = $order->grandtotal + $order->discount - $order->tax;
        $hargaSetelahDiskon = $hargaAwal - $order->discount;

        $taxPercentage = $hargaSetelahDiskon > 0 ? ($order->tax / $hargaSetelahDiskon) * 100 : 0;
        $discountPercentage = $hargaAwal > 0 ? ($order->discount / $hargaAwal) * 100 : 0;
    @endphp

    <table class="items">
        @foreach ($order->transactionDetails as $detail)
        <tr>
            <td colspan="2" class="item-name">
                {{ $detail->product->name }}
                <div class="item-sub">
                    {{ $detail->quantity }} x Rp{{ number_format($detail->price, 0, ',', '.') }}
                </div>
            </td>
            <td class="price">
                Rp{{ number_format($detail->price * $detail->quantity, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table class="summary">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>

        @if ($taxPercentage)
        <tr>
            <td class="label">Pajak PB1 ({{ number_format($taxPercentage, 0) }}%)</td>
            <td class="value">Rp{{ number_format($order->tax, 0, ',', '.') }}</td>
        </tr>
        @endif

        @if ($discountPercentage)
        <tr>
            <td class="label">Diskon ({{ number_format($discountPercentage, 0) }}%)</td>
            <td class="value">Rp{{ number_format($order->discount, 0, ',', '.') }}</td>
        </tr>
        @endif

        <tr class="total">
            <td class="label">TOTAL</td>
            <td class="value">Rp{{ number_format($order->grandtotal, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="label">Uang</td>
            <td class="value">Rp{{ number_format($order->customer_money, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kembali</td>
            <td class="value">Rp{{ number_format($order->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->store_footer }}
    </div>

</div>

<div class="powered-by">
    Powered by <a href="#" target="_blank">Maestro-Kasir</a>
</div>

</body>
</html>