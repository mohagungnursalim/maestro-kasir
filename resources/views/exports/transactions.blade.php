<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Laporan Transaksi</h2>
    <p>Periode: {{ optional(\Carbon\Carbon::parse($startDate))->format('d/m/Y') }} - {{ optional(\Carbon\Carbon::parse($endDate))->format('d/m/Y') }}</p>

    <p>Total Transaksi: {{ optional($transactions)->count() ?? 0 }}</p>


    <table>
        <thead>
            <tr>
                <th>ID Order/Kasir</th>
                <th>Nama Produk</th>
                <th class="text-center">Jumlah</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Sub Total</th>
                <th class="text-center">Tanggal/Jam</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $orderId => $orderTransactions)
                @php
                    $orderTotal = $orderTransactions->sum(fn($transaction) => $transaction->quantity * $transaction->price);
                @endphp
    
                <tr style="background-color: #dcdcdc; font-weight: bold;">
                    <td colspan="6">
                        ID Order: {{ $orderId }} <br>
                        Kasir: {{ optional(optional($orderTransactions->first())->order)->user->name ?? '-' }}

                    </td>
                    <td class="text-right">Rp{{ number_format($orderTotal, 0, ',', '.') }}</td>
                </tr>
    
                @foreach ($orderTransactions as $transaction)
                    <tr>
                        <td></td>
                        <td>{{ $transaction->product->name ?? '-' }}</td>
                        <td class="text-center">{{ $transaction->quantity ?? '-' }}</td>
                        <td class="text-right">Rp{{ number_format($transaction->price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp{{ number_format($transaction->quantity * $transaction->price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            {{ optional(\Carbon\Carbon::parse($transaction->created_at))->format('d-m-Y H:i') }}
                        </td>
                        <td></td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data ditemukan!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
