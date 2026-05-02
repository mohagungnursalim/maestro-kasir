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

        $subtotalProduk  = $details->sum(fn($i) => $i->quantity * $i->price);
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

    $branchIdQuery = $firstOrder?->branch_id ?? null;
    $totalExpense = \App\Models\Expense::whereBetween('expense_date', [
        \Carbon\Carbon::parse($startDate)->format('Y-m-d'),
        \Carbon\Carbon::parse($endDate)->format('Y-m-d')
    ])
    ->when($branchIdQuery, fn($q) => $q->where('branch_id', $branchIdQuery))
    ->where('type', 'out')
    ->sum('amount');

    $totalKeuntungan = $grandTotalNett - $totalExpense;
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
            <td>Pendapatan (Grand Total = Omzet Produk - Diskon + Ongkir + Pajak (jika ada))</td>
            <td class="val green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</td>
        </tr>
        <tr class="">
            <td>Total Kas Keluar</td>
            <td class="val red">Rp{{ number_format($totalExpense, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Keuntungan (Pendapatan - Kas Keluar)</td>
            <td class="val green bold">Rp{{ number_format($totalKeuntungan, 0, ',', '.') }}</td>
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
            <td>Total Omzet Produk (Harga Produk x Quantity)</td>
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
            <td>TOTAL PENDAPATAN (GRAND TOTAL)</td>
            <td class="val green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top:8px; font-size:9px; color:#555;">
    <strong>Catatan:</strong>
    <ul style="margin:4px 0 0 18px; padding:0;">
        <li>Pendapatan = jumlah <em>grandtotal</em> tiap order (termasuk pajak & ongkir).</li>
        <li>Keuntungan yang dicetak = Pendapatan - Total Kas Keluar (pengeluaran bertipe `out`). Nilai ini <strong>belum</strong> mengurangkan HPP (biaya bahan) atau biaya yang belum dicatat di `Expense`.</li>
        <li>Total Omzet Produk = jumlah (kuantitas × harga) sebelum diskon dan pajak.</li>
    </ul>
</div>



</body>
</html>
