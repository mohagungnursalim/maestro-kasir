<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 100%; /* Fluid */
            max-width: 58mm; /* Pembatasan maksimal */
            margin: 0 auto;
            padding: 0;
            color: #000;
        }

        @media print {
            @page {
                size: portrait; /* Jangan limit 'auto' yang bikin Android Spooler ngelag mikir page-break */
                margin: 0;
            }
            html, body {
                width: 100% !important; 
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background-color: transparent !important;
            }
            .bill {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
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
        const isAndroid = /android/i.test(navigator.userAgent);
        if (isAndroid) {
            let rawText = extractTextFromHtml();
            let encodedText = encodeURIComponent(rawText);
            let intentUrl = "intent:" + encodedText + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            
            window.location.href = intentUrl;
            setTimeout(() => window.close(), 1000); 
        } else {
            window.print();
        }
    }

    function extractTextFromHtml() {
        const storeName = document.querySelector('.store-name').innerText;
        const storeAddress = document.querySelector('.store-address').innerText;
        const storePhone = document.querySelector('.meta tr:nth-child(1) td:nth-child(3)').innerText;
        const orderDate = document.querySelector('.meta tr:nth-child(2) td:nth-child(3)').innerText;
        const cashier = document.querySelector('.meta tr:nth-child(3) td:nth-child(3)').innerText;
        const orderNum = document.querySelector('.meta tr:nth-child(4) td:nth-child(3)').innerText;
        
        let tx = "\n" + centerText("=== BILL ===") + "\n";
        tx += centerText(storeName) + "\n";
        tx += centerText(storeAddress) + "\n";
        tx += centerText("Telp: " + storePhone) + "\n";
        tx += "--------------------------------\n";
        
        tx += "Tgl  : " + orderDate + "\n";
        tx += "Kasir: " + cashier + "\n";
        tx += "Order: " + orderNum + "\n";
        
        // cek if split data exist (tr 5)
        const trSplit = document.querySelector('.meta tr:nth-child(5)');
        if(trSplit) {
            tx += "Split: " + trSplit.querySelector('td:nth-child(3)').innerText + "\n";
        }
        
        tx += "--------------------------------\n";

        const items = document.querySelectorAll('.items tr');
        items.forEach(tr => {
            const nameEl = tr.querySelector('.item-name');
            let qtyPrice = "";
            let name = "";
            if(nameEl){
                 name = nameEl.childNodes[0].nodeValue.trim();
                 qtyPrice = nameEl.querySelector('.item-sub').innerText;
            }
            const total = tr.querySelector('.price').innerText;

            tx += name + "\n";
            tx += padSpace(qtyPrice, total) + "\n";
        });

        tx += "--------------------------------\n";

        const summaries = document.querySelectorAll('.summary tr');
        summaries.forEach(tr => {
            const label = tr.querySelector('.label').innerText;
            const val = tr.querySelector('.value').innerText;
            tx += padSpace(label, val) + "\n";
        });

        tx += "--------------------------------\n";
        tx += centerText(document.querySelector('.footer').innerText) + "\n";
        tx += "\n\n"; // Beri jarak 2 spasi kosong (enter) kebawah
        tx += centerText("Powered by Maestro-Kasir") + "\n";
        tx += "\n\n";

        return tx;
    }

    function centerText(text) {
        if(text.length >= 32) return text;
        const padding = Math.floor((32 - text.length) / 2);
        return " ".repeat(padding) + text;
    }
    
    function padSpace(leftStr, rightStr) {
        let totalSpace = 32 - leftStr.length - rightStr.length;
        if(totalSpace < 1) totalSpace = 1;
        return leftStr + " ".repeat(totalSpace) + rightStr;
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
            $discountPercentage = $billData['subtotal'] > 0
                ? ($billData['discount'] / $billData['subtotal']) * 100
                : 0;
            $taxPercentage = ($billData['subtotal'] - $billData['discount']) > 0
                ? ($billData['tax'] / ($billData['subtotal'] - $billData['discount'])) * 100
                : 0;
        @endphp

        @if ($discountPercentage > 0)
        <tr>
            <td class="label">Diskon ({{ number_format($discountPercentage, 0) }}%)</td>
            <td class="value">-Rp{{ number_format($billData['discount'], 0, ',', '.') }}</td>
        </tr>
        @endif

        @if ($taxPercentage > 0)
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
       <div class="powered-by">
            Powered by <a href="#" target="_blank">Maestro-Kasir</a>
        </div>
    </div>

</div>



</body>
</html>