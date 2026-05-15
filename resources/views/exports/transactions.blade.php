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

        /* ── KPI Cards Grid ────────────────────────────────────────────────── */
        .kpi-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .kpi-grid td {
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            vertical-align: top;
            width: 25%;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
        }
        .kpi-sub {
            font-size: 8px;
            margin-top: 3px;
        }
        .bg-green   { background: #f0fdf4; }
        .bg-red     { background: #fef2f2; }
        .bg-blue    { background: #eff6ff; }
        .bg-purple  { background: #faf5ff; }
        .bg-amber   { background: #fffbeb; }
        .bg-orange  { background: #fff7ed; }
        .bg-emerald { background: #ecfdf5; }
        .bg-gray    { background: #f9fafb; }
        .text-green   { color: #16a34a; }
        .text-red     { color: #dc2626; }
        .text-blue    { color: #2563eb; }
        .text-purple  { color: #7c3aed; }
        .text-amber   { color: #d97706; }
        .text-orange  { color: #ea580c; }
        .text-emerald { color: #059669; }
        .text-gray    { color: #6b7280; }

        /* ── Growth Badge ──────────────────────────────────────────────────── */
        .badge-growth {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 8px;
        }
        .badge-up   { background: #dcfce7; color: #16a34a; }
        .badge-down { background: #fee2e2; color: #dc2626; }

        /* ── Order Type Split ──────────────────────────────────────────────── */
        .type-split-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .type-split-table th {
            background: #4f46e5;
            color: #fff;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }
        .type-split-table td {
            padding: 5px 8px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
        }
        .type-split-table .bar-cell {
            padding: 5px 8px;
        }
        .bar-bg {
            background: #e5e7eb;
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        .bar-fill {
            height: 10px;
            border-radius: 5px;
            background: #6366f1;
        }

        /* ── Section title ─────────────────────────────────────────────────── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 4px;
            margin: 18px 0 8px 0;
        }
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

        if ($method === 'QRIS') {
            $totalQris  += $totalPay;
        } else {
            $totalTunai += $totalPay;
        }
    }

    // Use dashboardStats passed from controller, with fallbacks
    $ds = $dashboardStats ?? [];
    $totalProductsSold = $ds['totalProductsSold'] ?? 0;
    $totalTopUps       = $ds['totalTopUps']       ?? 0;
    $totalExpenseOut   = $ds['totalExpenseOut']    ?? 0;
    $avgOrderValue     = $ds['avgOrderValue']      ?? 0;
    $avgDiscount       = $ds['avgDiscount']        ?? 0;
    $orderTypeSplit    = $ds['orderTypeSplit']      ?? [];
    $unpaidOrders      = $ds['unpaidOrders']        ?? 0;
    $unpaidAmount      = $ds['unpaidAmount']        ?? 0;
    $prevOmzet         = $ds['prevOmzet']           ?? 0;
    $omzetGrowth       = $ds['omzetGrowth']         ?? null;

    $totalKeuntungan = $grandTotalNett - $totalExpenseOut;

    $typeLabels = [
        'DINE_IN'   => 'Dine-In',
        'TAKE_AWAY' => 'Take-Away',
        'GOFOOD'    => 'GoFood',
        'GRABFOOD'  => 'GrabFood',
        'MAXIM'     => 'Maxim',
    ];
@endphp

{{-- =====================================================================
     HALAMAN 1 — RINGKASAN DASHBOARD
     ===================================================================== --}}
<div class="report-header">
    <h2>{{ $branchName }}</h2>
    <p>{{ $branchAddress }}</p>
    <h1>Laporan Transaksi & Ringkasan Dashboard</h1>
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

{{-- ── KPI Cards (4 columns like dashboard) ────────────────────────────── --}}
<div class="section-title">Statistik Utama</div>

<table class="kpi-grid">
    <tr>
        <td class="bg-gray">
            <div class="kpi-label text-gray">Total Order</div>
            <div class="kpi-value" style="color:#374151;">{{ number_format($transactions->count(), 0, ',', '.') }}</div>
        </td>
        <td class="bg-green">
            <div class="kpi-label text-green">
                Omzet Penjualan
                @if ($omzetGrowth !== null)
                    <span class="badge-growth {{ $omzetGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                        {{ $omzetGrowth >= 0 ? 'up' : 'down' }} {{ abs($omzetGrowth) }}%
                    </span>
                @endif
            </div>
            <div class="kpi-value text-green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</div>
            @if ($prevOmzet > 0)
                <div class="kpi-sub text-green">vs Rp{{ number_format($prevOmzet, 0, ',', '.') }} periode sebelumnya</div>
            @endif
        </td>
        <td class="bg-emerald">
            <div class="kpi-label text-emerald">Keuntungan</div>
            <div class="kpi-value text-emerald">Rp{{ number_format($totalKeuntungan, 0, ',', '.') }}</div>
            <div class="kpi-sub text-emerald">Omzet - Kas Keluar</div>
        </td>
        <td class="bg-red">
            <div class="kpi-label text-red">Kas Keluar</div>
            <div class="kpi-value text-red">Rp{{ number_format($totalExpenseOut, 0, ',', '.') }}</div>
            <div class="kpi-sub text-red">Pengeluaran & Belanja</div>
        </td>
    </tr>
    <tr>
        <td class="bg-blue">
            <div class="kpi-label text-blue">Produk Terjual</div>
            <div class="kpi-value text-blue">{{ number_format($totalProductsSold, 0, ',', '.') }}</div>
        </td>
        <td class="bg-gray">
            <div class="kpi-label text-gray">Top Up Kas</div>
            <div class="kpi-value" style="color:#22c55e;">Rp{{ number_format($totalTopUps, 0, ',', '.') }}</div>
            <div class="kpi-sub text-gray">Pemasukan non-penjualan</div>
        </td>
        <td class="bg-purple">
            <div class="kpi-label text-purple">Rata-rata / Transaksi (AOV)</div>
            <div class="kpi-value text-purple">Rp{{ number_format($avgOrderValue, 0, ',', '.') }}</div>
        </td>
        <td class="bg-amber">
            <div class="kpi-label text-amber">Rata-rata Diskon / Order</div>
            <div class="kpi-value text-amber">Rp{{ number_format($avgDiscount, 0, ',', '.') }}</div>
        </td>
    </tr>
    <tr>
        <td class="bg-orange">
            <div class="kpi-label text-orange">Piutang (Belum Lunas)</div>
            <div class="kpi-value text-orange">Rp{{ number_format($unpaidAmount, 0, ',', '.') }}</div>
            <div class="kpi-sub text-orange">{{ $unpaidOrders }} order belum lunas</div>
        </td>
        <td>
            <div class="kpi-label text-gray">Bayar via Tunai</div>
            <div class="kpi-value" style="color:#374151;">Rp{{ number_format($totalTunai, 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="kpi-label text-blue">Bayar via QRIS</div>
            <div class="kpi-value text-blue">Rp{{ number_format($totalQris, 0, ',', '.') }}</div>
        </td>
        <td></td>
    </tr>
</table>

{{-- ── Rasio Tipe Order ────────────────────────────────────────────────── --}}
@if (!empty($orderTypeSplit))
<div class="section-title">Rasio Tipe Pesanan</div>

<table class="type-split-table">
    <thead>
        <tr>
            <th style="width:20%;">Tipe</th>
            <th style="width:10%; text-align:center;">Order</th>
            <th style="width:10%; text-align:center;">%</th>
            <th style="width:30%;">Proporsi</th>
            <th style="width:30%; text-align:right;">Omzet</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orderTypeSplit as $type => $data)
            @php $label = $typeLabels[$type] ?? $type; @endphp
            <tr>
                <td style="font-weight:bold;">{{ $label }}</td>
                <td style="text-align:center;">{{ $data['count'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $data['percentage'] }}%</td>
                <td class="bar-cell">
                    <div class="bar-bg">
                        <div class="bar-fill" style="width: {{ $data['percentage'] }}%;"></div>
                    </div>
                </td>
                <td style="text-align:right; font-weight:bold;">Rp{{ number_format($data['omzet'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── Detail Keuangan ─────────────────────────────────────────────────── --}}
<div class="section-title">Rincian Keuangan</div>

<table class="summary-table" style="margin-top:8px;">
    <thead>
        <tr>
            <th style="text-align:left;">Metrik</th>
            <th style="text-align:right;">Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Omzet Produk (Harga × Quantity)</td>
            <td class="val">Rp{{ number_format($grandTotalOmset, 0, ',', '.') }}</td>
        </tr>
        <tr class="stripe">
            <td>Total Diskon Diberikan</td>
            <td class="val red">
                @if ((float)$grandTotalDiskon > 0)
                    – Rp{{ number_format($grandTotalDiskon, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr>
            <td>Total Pajak Dipungut</td>
            <td class="val">
                @if ((float)$grandTotalPajak > 0)
                    Rp{{ number_format($grandTotalPajak, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr class="stripe">
            <td>Total Ongkir</td>
            <td class="val">
                @if ((float)$grandTotalOngkir > 0)
                    Rp{{ number_format($grandTotalOngkir, 0, ',', '.') }}
                @else
                    –
                @endif
            </td>
        </tr>
        <tr>
            <td>Pendapatan Nett (Grand Total = Omzet - Diskon + Ongkir + Pajak)</td>
            <td class="val green">Rp{{ number_format($grandTotalNett, 0, ',', '.') }}</td>
        </tr>
        <tr class="stripe">
            <td>Total Kas Keluar</td>
            <td class="val red">Rp{{ number_format($totalExpenseOut, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Top Up Kas (Pemasukan Non-Penjualan)</td>
            <td class="val green">Rp{{ number_format($totalTopUps, 0, ',', '.') }}</td>
        </tr>
    </tbody>

    <tfoot>
        <tr>
            <td>KEUNTUNGAN (Pendapatan - Kas Keluar)</td>
            <td class="val green">Rp{{ number_format($totalKeuntungan, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top:8px; font-size:9px; color:#555;">
    <strong>Catatan:</strong>
    <ul style="margin:4px 0 0 18px; padding:0;">
        <li>Pendapatan = jumlah <em>grandtotal</em> tiap order (termasuk pajak & ongkir).</li>
        <li>Keuntungan yang dicetak = Pendapatan - Total Kas Keluar (pengeluaran bertipe `out`).
        <li>Total Omzet Produk = jumlah (kuantitas × harga) sebelum diskon dan pajak.</li>
        <li>Data Piutang bersifat global (seluruh order belum lunas, tidak terbatas periode).</li>
    </ul>
</div>

{{-- ── Penjualan Produk ─────────────────────────────────────────────────── --}}
<div style="page-break-before: always;"></div>
<div class="report-header">
    <h2>Daftar Produk Terjual</h2>
    <p>Rincian Kuantitas dan Omzet per Produk</p>
</div>

<table class="summary-table" style="margin-top:8px;">
    <thead>
        <tr>
            <th style="width: 5%; text-align:center;">No</th>
            <th style="text-align:left;">Nama Produk</th>
            <th style="width: 15%; text-align:center;">Terjual</th>
            <th style="width: 30%; text-align:right;">Subtotal Omzet</th>
        </tr>
    </thead>
    <tbody>
        @php
            $productSales = [];
            foreach ($transactions as $details) {
                foreach ($details as $item) {
                    $prodName = $item->product ? $item->product->name : ($item->product_name ?? 'Produk Dihapus');
                    if (!isset($productSales[$prodName])) {
                        $productSales[$prodName] = [
                            'qty' => 0,
                            'omzet' => 0
                        ];
                    }
                    $productSales[$prodName]['qty'] += $item->quantity;
                    $productSales[$prodName]['omzet'] += ($item->quantity * $item->price);
                }
            }
            $productSales = collect($productSales)->sortByDesc('qty');
            $no = 1;
        @endphp
        
        @forelse ($productSales as $name => $data)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'stripe' : '' }}">
                <td style="text-align:center;">{{ $no++ }}</td>
                <td>{{ $name }}</td>
                <td style="text-align:center; font-weight:bold;">{{ number_format($data['qty'], 0, ',', '.') }}</td>
                <td class="val">Rp{{ number_format($data['omzet'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center;">Tidak ada data produk yang terjual</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="text-align:right;">TOTAL KESELURUHAN</td>
            <td style="text-align:center;" class="val">{{ number_format($productSales->sum('qty'), 0, ',', '.') }}</td>
            <td class="val green">Rp{{ number_format($productSales->sum('omzet'), 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>
