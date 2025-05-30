<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    public function collection()
    {
        return TransactionDetail::with(['product', 'order.user'])
            ->whereHas('order', function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Order',
            'Kasir',
            'Tanggal',
            'Nama Produk',
            'Jumlah',
            'Harga Satuan',
            'Subtotal Produk',
            'Metode Pembayaran',
            'Pajak',
            'Diskon',
            'Total Bayar',
            'Uang Pelanggan',
            'Kembalian',
        ];
    }

    public function map($transaction): array
    {
        $order = $transaction->order;

        return [
            $order->order_number ?? '-',
            $order->user->name ?? '-',
            Carbon::parse($order->created_at)->format('d/m/Y H:i:s'),
            $transaction->product->name ?? '-',
            $transaction->quantity,
            number_format($transaction->product->price ?? 0, 0, ',', '.'),
            number_format($transaction->quantity * ($transaction->product->price ?? 0), 0, ',', '.'),
            $order->payment_method ?? '-',
            number_format($order->tax ?? 0, 0, ',', '.'),
            number_format($order->discount ?? 0, 0, ',', '.'),
            number_format($order->grandtotal ?? 0, 0, ',', '.'),
            number_format($order->customer_money ?? 0, 0, ',', '.'),
            number_format($order->change ?? 0, 0, ',', '.'),
        ];
    }
}
