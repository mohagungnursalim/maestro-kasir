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
    protected $branchId;
    private $lastOrderId = null;

    public function __construct($startDate, $endDate, $branchId = null)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->branchId = $branchId;
    }

    public function collection()
    {
        return TransactionDetail::with(['product', 'order.user', 'order.branch'])
            ->whereHas('order', function ($query) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                      ->where('payment_status', 'PAID')
                      ->when($this->branchId, function($q) {
                          $q->where('branch_id', $this->branchId);
                      });
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Order',
            'Kasir',
            'Cabang',
            'Tanggal',
            'Nama Produk',
            'Jumlah',
            'Harga Satuan',
            'Subtotal Produk',
            'Metode Pembayaran',
            'Diskon',
            'Pajak',
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
        $isFirst = $this->lastOrderId !== $order->id;
        $this->lastOrderId = $order->id;

        return [
            $order->order_number ?? '-',
            $order->user->name ?? '-',
            $order->branch->name ?? 'Semua Cabang',
            Carbon::parse($order->created_at)->format('d/m/Y H:i:s'),
            $transaction->product->name ?? '-',
            $transaction->quantity,
            $transaction->price ?? 0,
            $transaction->quantity * ($transaction->price ?? 0),
            $order->payment_method ?? '-',
            $isFirst ? ($order->discount ?? 0) : 0,
            $isFirst ? ($order->tax ?? 0) : 0,
            $isFirst ? ($order->grandtotal ?? 0) : 0,
            $isFirst ? ($order->customer_money ?? 0) : 0,
            $isFirst ? ($order->change ?? 0) : 0,
        ];
    }

}
