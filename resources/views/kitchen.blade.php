<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Dapur</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 100%;
            max-width: 58mm;
            margin: 0 auto;
            padding: 0;
            color: #000;
        }

        @media print {
            @page {
                size: portrait;
                margin: 0;
            }
            html, body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background-color: transparent !important;
            }
            .kitchen {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }

        .kitchen {
            padding: 6px 5px 10px;
        }

        .header {
            text-align: center;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            font-weight: bold;
        }

        .meta .colon {
            width: 5%;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .line-solid {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .item-row td {
            padding: 3px 0;
            vertical-align: top;
        }

        .item-qty {
            font-size: 13px;
            font-weight: bold;
            width: 14%;
            text-align: center;
            vertical-align: middle;
        }

        .item-detail {
            width: 86%;
            padding-left: 4px;
        }

        .item-name {
            font-size: 12px;
            font-weight: bold;
        }

        .item-note {
            font-size: 10px;
            font-style: italic;
            color: #333;
            margin-top: 1px;
        }

        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .order-type-badge {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
            padding: 2px 4px;
            display: inline-block;
            width: 90%;
            margin: 3px 0;
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
            window.top.location.href = intentUrl;
        } else {
            window.print();
        }
    }

    function extractTextFromHtml() {
        const orderNum   = document.querySelector('.meta .order-num').innerText;
        const orderDate  = document.querySelector('.meta .order-date').innerText;
        const deskNum    = document.querySelector('.meta .desk-num').innerText;
        const orderType  = document.querySelector('.meta .order-type').innerText;

        let tx = "\n" + centerText("*** STRUK DAPUR ***") + "\n";
        tx += "================================\n";
        tx += "Order  : " + orderNum.trim() + "\n";
        tx += "Tgl    : " + orderDate.trim() + "\n";
        tx += "Meja   : " + deskNum.trim() + "\n";
        tx += "Tipe   : " + orderType.trim() + "\n";

        // Note pesanan
        const noteEl = document.querySelector('.order-note');
        if (noteEl && noteEl.innerText.trim()) {
            tx += "Catatan: " + noteEl.innerText.trim() + "\n";
        }

        tx += "================================\n";

        const rows = document.querySelectorAll('.item-row');
        rows.forEach(tr => {
            const qty  = tr.querySelector('.item-qty').innerText.trim();
            const name = tr.querySelector('.item-name').innerText.trim();
            const noteEl2 = tr.querySelector('.item-note');
            tx += qty + "x " + name + "\n";
            if (noteEl2 && noteEl2.innerText.trim()) {
                tx += "   >> " + noteEl2.innerText.trim() + "\n";
            }
        });

        tx += "================================\n";

        const footerEl = document.querySelector('.footer');
        if (footerEl) {
            tx += centerText(footerEl.innerText.trim()) + "\n";
        }

        tx += "\n\n";
        return tx;
    }

    function centerText(text) {
        text = text.trim();
        if (text.length >= 32) return text;
        const padding = Math.floor((32 - text.length) / 2);
        return " ".repeat(padding) + text;
    }

    window.onafterprint = function () {
        setTimeout(() => window.close(), 500);
    };
</script>

<div class="kitchen">

    <div class="header">
        <div class="title">*** DAPUR ***</div>
        <div class="subtitle">Struk Pesanan Masuk</div>
    </div>

    <div class="line-solid"></div>

    <table class="meta">
        <tr>
            <td class="label">Order</td>
            <td class="colon">:</td>
            <td class="order-num">{{ $kitchenData['order_number'] }}</td>
        </tr>
        <tr>
            <td class="label">Tgl/Jam</td>
            <td class="colon">:</td>
            <td class="order-date">{{ $kitchenData['tanggal'] }}</td>
        </tr>
        <tr>
            <td class="label">Meja</td>
            <td class="colon">:</td>
            <td class="desk-num">{{ $kitchenData['desk_number'] ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tipe</td>
            <td class="colon">:</td>
            <td class="order-type">
                @if($kitchenData['order_type'] === 'DINE_IN')
                    Makan di Tempat
                @else
                    Bungkus
                @endif
            </td>
        </tr>
        @if(!empty($kitchenData['note']))
        <tr>
            <td class="label">Catatan</td>
            <td class="colon">:</td>
            <td class="order-note">{{ $kitchenData['note'] }}</td>
        </tr>
        @endif
    </table>

    <div class="line-solid"></div>

    <table class="items">
        @foreach ($kitchenData['items'] as $item)
        <tr class="item-row">
            <td class="item-qty">{{ $item['qty'] }}</td>
            <td class="item-detail">
                <div class="item-name">{{ $item['name'] }}</div>
                @if (!empty($item['note']))
                    <div class="item-note">{{ $item['note'] }}</div>
                @endif
            </td>
        </tr>
        @if (!$loop->last)
        <tr><td colspan="2"><div class="line"></div></td></tr>
        @endif
        @endforeach
    </table>

    <div class="line-solid"></div>

    <div class="footer">
        ~~ SEGERA DIPROSES ~~
    </div>

</div>

</body>
</html>
