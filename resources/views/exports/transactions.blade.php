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
        .text-right { text-align: right; }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
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
        .bold   { font-weight: bold; }
        .center { text-align: center; }

        /* Detail table column widths */
        .detail-table th:nth-child(1) { width: 18%; }
        .detail-table th:nth-child(2) { width: 24%; }
        .detail-table th:nth-child(3) { width: 7%;  }
        .detail-table th:nth-child(4) { width: 15%; }
        .detail-table th:nth-child(5) { width: 10%; }
        .detail-table th:nth-child(6) { width: 13%; }
        .detail-table th:nth-child(7) { width: 13%; }

        .text-small-gray { font-size: 8px; color: #6b7280; }

        /* Summary table */
        .summary-table th {
            background: #2c3e50;
            color: #fff;
            padding: 8px;
            font-size: 11px;
        }
        .summary-table td {
            padding: 7px 8px;
            font-size: 11px;
            border: 1px solid #ccc;
        }
        .summary-table .val { text-align: right; }
        .summary-table tfoot td {
            padding: 10px 8px;
            font-size: 13px;
            font-weight: bold;
            border: 2px solid #2c3e50;
            background: #ecf0f1;
        }
        .summary-table tfoot .val {
            font-size: 14px;
            color: #16a34a;
        }
        .stripe { background: #f9f9f9; }
        .green  { color: #16a34a; font-weight: bold; }
        .red    { color: #dc2626; }
    </style>
</head>
<body>

@php
    \Carbon\Carbon::setLocale('id');
    $firstOrder    = collect($transactions)->first()?->first()?->order;
    $branchName    = $firstOrder?->branch?->name    ?? 'Laporan Semua Cabang';
    $branchAddress = $firstOrder?->branch?->address ?? '-';

    // ── Hitung semua akumulasi sekali (sebelum render) ──────────────────────
    $grandTotalOmset  = 0;
    $grandTotalDiskon = 0;
    $grandTotalPajak  = 0;
    $grandTotalOngkir = 0;
    $grandTotalNett   = 0;
    $totalQris        = 0;
    $totalTunai       = 0;
    $totalPayLater    = 0;

    foreach ($transactions as $details) {
        $f      = $details->first();
        $method = strtoupper($f->order->payment_method ?? '');
        $mode   = strtoupper($f->order->payment_mode   ?? '');

        $subtotalProduk  = $details->sum(fn($i) => $i->quantity * $i->product->price);
        $totalPay        = $f->order->grandtotal    ?? 0;
        $taxAmount       = $f->order->tax           ?? 0;
        $discountAmount  = $f->order->discount      ?? 0;
        $shippingAmount  = (float) ($f->order->shipping_cost ?? 0);

        $grandTotalOmset  += $subtotalProduk;
        $grandTotalDiskon += $discountAmount;
        $grandTotalPajak  += $taxAmount;
        $grandTotalOngkir += $shippingAmount;
        $grandTotalNett   += $totalPay;

        if ($mode === 'PAY_LATER') {
            $totalPayLater++;
        } elseif ($method === 'QRIS') {
            $totalQris  += $totalPay;
        } else {
            $totalTunai += $totalPay;
        }
    }
@endphp

{{-- =====================================================================
     HALAMAN 1 — RINGKASAN
     ===================================================================== --}}
<div class="report-header">
    <h2>{{ $branchName }}</h2>
    <p>{{ $branchAddress }}</p>
    <h1>Ringkasan Transaksi</h1>
</div>

<table class="report-meta">
    <tr>
        <td class="label">Periode</td>
        <td style="width:10px;">:</td>
        <td>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
        <td class="label text-right">Tanggal Cetak</td>
        <td style="width:10px;">:</td>
        <td class="text-right" style="width:130px;">{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</td>
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

<table class="summary-table" style="margin-top:20px;">
    <thead>
        <tr>
            <th style="text-align:left;">Metrik</th>
            <th style="text-align:right;">Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr class="">
            <td>Total Order</td>
            <td class="val bold">{{ $transactions->count() }} order</td>
        </tr>
        <tr>
            <td>Total Pendapatan (Nett)</td>
            <td class="val green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</td>
        </tr>
        <tr class="">
            <td>Bayar via QRIS</td>
            <td class="val">Rp{{ number_format($totalQris, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar via Tunai</td>
            <td class="val">Rp{{ number_format($totalTunai, 0, ',', '.') }}</td>
        </tr>
        <tr class="">
            <td>Total Omzet Produk (Murni)</td>
            <td class="val">Rp{{ number_format($grandTotalOmset, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Diskon Diberikan</td>
            <td class="val red">
                @if ((float)$grandTotalDiskon > 0)
                    – Rp{{ number_format($grandTotalDiskon, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr class="">
            <td>Total Pajak Dipungut</td>
            <td class="val">
                @if ((float)$grandTotalPajak > 0)
                    Rp{{ number_format($grandTotalPajak, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr>
            <td>Total Ongkir</td>
            <td class="val">
                @if ((float)$grandTotalOngkir > 0)
                    Rp{{ number_format($grandTotalOngkir, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr class="">
            <td>Order Bayar Nanti (PAY_LATER)</td>
            <td class="val">{{ $totalPayLater }} order</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL PENDAPATAN BERSIH</td>
            <td class="val green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- =====================================================================
     HALAMAN 2 — DETAIL TRANSAKSI
     ===================================================================== --}}
<div style="page-break-before: always;"></div>

<div class="report-header">
    <h2>{{ $branchName }}</h2>
    <p>{{ $branchAddress }}</p>
    <h1>Detail Transaksi</h1>
</div>

<table class="report-meta">
    <tr>
        <td class="label">Periode</td>
        <td style="width:10px;">:</td>
        <td>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
        <td class="label text-right">Tanggal Cetak</td>
        <td style="width:10px;">:</td>
        <td class="text-right" style="width:130px;">{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</td>
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

@foreach ($transactions as $order_id => $details)
@php
    $first          = $details->first();
    $subtotalProduk = $details->sum(fn($i) => $i->quantity * $i->product->price);
    $totalPay       = $first->order->grandtotal  ?? 0;
    $taxAmount      = $first->order->tax         ?? 0;
    $discountAmount = $first->order->discount    ?? 0;
    $shippingAmt    = (float) ($first->order->shipping_cost ?? 0);

    $base               = $totalPay - $taxAmount - $shippingAmt;
    $taxPercentage      = $base > 0 ? round(($taxAmount / $base) * 100) : 0;
    $discountPercentage = $subtotalProduk > 0 ? round(($discountAmount / $subtotalProduk) * 100) : 0;
@endphp

<table class="detail-table" style="margin-bottom:15px;">
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
        <tr>
            <td class="bold">
                Order: {{ $first->order->order_number ?? '-' }}<br>
                Kasir: {{ $first->order->user->name   ?? '-' }}<br>
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
                @if ((float)$discountAmount > 0)
                    <span class="text-small-gray">({{ number_format($discountPercentage) }}%)</span>
                    Rp{{ number_format($discountAmount, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="7" class="bold center">Pajak:
                @if ((float)$taxAmount > 0)
                    Rp{{ number_format($taxAmount, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>
        @if ($shippingAmt > 0)
        <tr>
            <td colspan="7" class="bold center">Ongkir: Rp{{ number_format($shippingAmt, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="7" class="bold center">Total Bayar: Rp{{ number_format($totalPay, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="7" class="bold center">
                @if (($first->order->payment_method ?? '') === 'QRIS')
                    QRIS
                @elseif (($first->order->payment_method ?? '') === 'CASH')
                    Uang Pelanggan
                @else
                    {{ $first->order->payment_method ?? 'Uang Pelanggan' }}
                @endif
                : Rp{{ number_format($first->order->customer_money ?? 0, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td colspan="7" class="bold center">Kembalian:
                @if (($first->order->payment_method ?? '') === 'CASH' && (float)($first->order->change ?? 0) > 0)
                    Rp{{ number_format($first->order->change, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>
    </tbody>
</table>
@endforeach

{{-- Footer ringkasan kecil di akhir detail --}}
<div style="margin-top:20px; text-align:right; font-weight:bold; font-size:13px;">
    <p style="margin:0; padding-top:4px;">Total Omzet Produk (Murni): Rp{{ number_format($grandTotalOmset, 0, ',', '.') }}</p>
    <p style="margin:0; padding-top:4px;">Total Diskon:
        @if ((float)$grandTotalDiskon > 0) Rp{{ number_format($grandTotalDiskon, 0, ',', '.') }} @else - @endif
    </p>
    <p style="margin:0; padding-top:4px;">Total Pajak:
        @if ((float)$grandTotalPajak > 0) Rp{{ number_format($grandTotalPajak, 0, ',', '.') }} @else - @endif
    </p>
    @if ((float)$grandTotalOngkir > 0)
    <p style="margin:0; padding-top:4px;">Total Ongkir: Rp{{ number_format($grandTotalOngkir, 0, ',', '.') }}</p>
    @endif
    <hr style="width:300px; margin-left:auto; margin-right:0;">
    <p style="margin:0; font-size:16px;">Total Pendapatan Bersih: Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</p>
</div>

</body>
</html>
