<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class TransactionExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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

    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 itu heading
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
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
            $transaction->product->price ?? 0,
            $transaction->quantity * ($transaction->product->price ?? 0),
            $order->payment_method ?? '-',
            $order->tax ?? 0,
            $order->discount ?? 0,
            $order->grandtotal ?? 0,
            $order->customer_money ?? 0,
            $order->change ?? 0,
        ];
    }

}
