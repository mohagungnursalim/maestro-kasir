<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Ambil data transaksi berdasarkan tanggal yang dipilih.
     */
    public function collection()
    {
        return TransactionDetail::with(['product', 'order.user'])->whereBetween('created_at', [$this->startDate, $this->endDate])->get();
    }

    /**
     * Tambahkan heading untuk file Excel.
     */
    public function headings(): array
    {
        return [
            'ID Order',
            'Kasir',
            'Nama Produk',
            'Jumlah',
            'Harga',
            'Sub Total',
            'Tanggal Transaksi'
        ];
    }

    /**
     * Format data yang diexport.
     */
    public function map($transaction): array
    {
        return [
            $transaction->order->id,
            $transaction->order->user->name ?? '-',
            $transaction->product->name ?? '-',
            $transaction->quantity,
            number_format($transaction->price, 2, ',', '.'),
            number_format($transaction->subtotal, 2, ',', '.'),
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
