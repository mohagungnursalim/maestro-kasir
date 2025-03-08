<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; /* Font lebih kecil */
            margin: 20px; /* Kurangi margin biar muat */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid black; 
            padding: 4px; /* Padding dikurangi */
            text-align: left;
        }
        th { 
            background-color: #f2f2f2; 
            font-size: 10px;
        }
        .bold { font-weight: bold; }
        .right { text-align: right; } /* Biar angka rata kanan */

        /* Atur lebar kolom */
        th:nth-child(1) { width: 15%; } /* ID Order/Kasir */
        th:nth-child(2) { width: 20%; } /* Nama Produk */
        th:nth-child(3) { width: 10%; } /* Pembayaran */
        th:nth-child(4) { width: 8%; }  /* Pajak */
        th:nth-child(5) { width: 8%; }  /* Diskon */
        th:nth-child(6) { width: 12%; } /* Uang Pelanggan */
        th:nth-child(7) { width: 10%; } /* Kembalian */
        th:nth-child(8) { width: 7%; }  /* Jumlah */
        th:nth-child(9) { width: 10%; } /* Harga */
    </style>
</head>
<body>
    <h1>Laporan Transaksi</h1>
        @php
            \Carbon\Carbon::setLocale('id');
        @endphp
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('l, d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('l, d F Y') }}</p>
    <p>Total Transaksi: {{ $transactions->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>ID Order/Kasir</th>
                <th>Nama Produk</th>
                <th>Pembayaran</th>
                <th>Pajak</th>
                <th>Diskon</th>
                <th>Uang Pelanggan</th>
                <th>Kembalian</th>
                <th>Jumlah</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0; // Simpan total keseluruhan
            @endphp

            @foreach ($transactions as $order_id => $details)
                @php
                    $first = $details->first(); // Ambil transaksi pertama untuk order
                    $subtotal = $details->sum(fn($item) => $item->quantity * $item->product->price); // Hitung subtotal
                    $grandTotal += $subtotal; // Tambahkan ke total keseluruhan
                @endphp
                <tr>
                    <td class="bold">
                        ID Order: {{ $order_id }}<br>
                        Kasir: {{ $first->order->user->name ?? '-' }}<br>
                        Tanggal: {{ $first->order->created_at->format('d-m-Y') }}
                    </td>
                    <td></td>
                    <td>{{ $first->order->payment_method ?? '-' }}</td>
                    <td class="right">Rp{{ number_format($first->order->tax ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($first->order->discount ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($first->order->customer_money ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp{{ number_format($first->order->change ?? 0, 0, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach ($details as $transaction)
                    <tr>
                        <td></td>
                        <td>{{ $transaction->product->name ?? '-' }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="right">{{ $transaction->quantity }}</td>
                        <td class="right">Rp{{ number_format($transaction->product->price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7" class="bold right">Subtotal:</td>
                    <td colspan="2" class="bold right">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="bold right">Total Keseluruhan:</td>
                <td colspan="2" class="bold right">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
