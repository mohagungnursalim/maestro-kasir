<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Pembayaran</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 58mm;
            margin: 0 auto;
            padding: 0;
            color: #000;
        }

        .bill {
            padding: 6px 5px 10px;
        }

        .header {
            text-align: center;
        }

        .logo {
            max-height: 36px;
            margin: 0 auto 4px;
            display: block;
        }

        .store-name {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .3px;
        }

        .store-address {
            font-size: 9px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .meta .label {
            width: 35%;
        }

        .meta .colon {
            width: 5%;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items td {
            padding: 2px 0;
            vertical-align: top;
        }

        .item-name {
            font-size: 10px;
        }

        .item-sub {
            font-size: 9px;
        }

        .price {
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .summary td {
            padding: 2px 0;
        }

        .summary .label {
            text-align: left;
        }

        .summary .value {
            text-align: right;
            white-space: nowrap;
        }

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
    function startPrint() {
        window.print();
    }
    window.onafterprint = function () {
        setTimeout(() => window.close(), 500);
    };
</script>

<div class="bill">

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
            <td>{{ $billData['tanggal'] }}</td>
        </tr>
        <tr>
            <td class="label">Kasir</td>
            <td class="colon">:</td>
            <td>{{ $billData['kasir'] }}</td>
        </tr>
        <tr>
            <td class="label">Order</td>
            <td class="colon">:</td>
            <td>{{ $billData['order_number'] }}</td>
        </tr>

        @if(isset($billData['__multi']))
        <tr>
            <td class="label">Split</td>
            <td class="colon">:</td>
            <td>{{ $billData['__multi']['split'] }} / {{ $billData['__multi']['count'] }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <table class="items">
        @foreach ($billData['items'] as $item)
        <tr>
            <td colspan="2" class="item-name">
                {{ $item['name'] }}
                <div class="item-sub">
                    {{ $item['qty'] }} x Rp{{ number_format($item['price'], 0, ',', '.') }}
                </div>
            </td>
            <td class="price">
                Rp{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table class="summary">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp{{ number_format($billData['subtotal'], 0, ',', '.') }}</td>
        </tr>

        @php
            $taxPercentage = $billData['subtotal'] > 0
                ? ($billData['tax'] / $billData['subtotal']) * 100
                : 0;
        @endphp

        @if ($taxPercentage)
        <tr>
            <td class="label">Pajak PB1 ({{ number_format($taxPercentage, 0) }}%)</td>
            <td class="value">Rp{{ number_format($billData['tax'], 0, ',', '.') }}</td>
        </tr>
        @endif

        <tr class="total">
            <td class="label">TOTAL</td>
            <td class="value">Rp{{ number_format($billData['total'], 0, ',', '.') }}</td>
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