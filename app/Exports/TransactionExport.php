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
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Ambil data transaksi berdasarkan tanggal yang dipilih.
     */
    public function collection()
    {
        return TransactionDetail::with(['product', 'order.user'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    /**
     * Tambahkan heading untuk file Excel.
     */
    public function headings(): array
    {
        return [
            'Nomor Order',
            'Kasir',
            'Nama Produk',
            'Pembayaran',
            'Pajak',
            'Diskon',
            'Uang Pelanggan',
            'Kembalian',
            'Jumlah',
            'Harga',
            'Subtotal',
            'Tanggal Transaksi'
        ];
    }

    /**
     * Format data yang diexport.
     */
    public function map($transaction): array
    {
        // Format angka ke ribuan (8.470)
        $formatRupiah = function ($number) {
            return number_format($number, 0, ',', '.');
        };

        return [
            $transaction->order->order_number ?? '-',
            $transaction->order->user->name ?? '-',
            $transaction->product->name ?? '-',
            $transaction->order->payment_method ?? '-',
            $formatRupiah($transaction->order->tax ?? 0), // Pajak
            $formatRupiah($transaction->order->discount ?? 0), // Diskon
            $formatRupiah($transaction->order->customer_money ?? 0), // Uang Pelanggan
            $formatRupiah($transaction->order->change ?? 0), // Kembalian
            $transaction->quantity,
            $formatRupiah($transaction->product->price), // Harga
            $formatRupiah($transaction->subtotal), // Subtotal
            Carbon::parse($transaction->created_at)->translatedFormat('d/m/Y H:i:s'), // Bahasa Indonesia
        ];
    }
}
