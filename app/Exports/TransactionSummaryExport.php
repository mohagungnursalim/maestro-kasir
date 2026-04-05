<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionSummaryExport implements FromArray, WithTitle, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $userName;

    public function __construct($startDate, $endDate, $branchId = null, $userName = 'Sistem')
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate   = Carbon::parse($endDate)->endOfDay();
        $this->branchId  = $branchId;
        $this->userName  = $userName;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        // Satu query agregasi mentah — No N+1
        $row = Order::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('payment_status', 'PAID')
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->selectRaw("
                COUNT(id)                                                           AS total_order,
                COALESCE(SUM(grandtotal), 0)                                        AS total_pendapatan,
                COALESCE(SUM(CASE WHEN upper(payment_method) = 'QRIS'
                    THEN grandtotal ELSE 0 END), 0)                                 AS total_qris,
                COALESCE(SUM(CASE WHEN upper(payment_method) IN ('CASH','TUNAI')
                    THEN grandtotal ELSE 0 END), 0)                                 AS total_tunai,
                COALESCE(SUM(discount), 0)                                          AS total_diskon,
                COALESCE(SUM(tax), 0)                                               AS total_pajak,
                COALESCE(SUM(COALESCE(shipping_cost, 0)), 0)                        AS total_ongkir,
                COALESCE(SUM(CASE WHEN payment_mode = 'PAY_LATER'
                    THEN 1 ELSE 0 END), 0)                                          AS total_pay_later
            ")
            ->first();

        $periode = Carbon::parse($this->startDate)->translatedFormat('d F Y')
                 . ' s/d '
                 . Carbon::parse($this->endDate)->translatedFormat('d F Y');

        return [
            ['RINGKASAN TRANSAKSI'],
            [''],
            ['Periode',        $periode],
            ['Dicetak Oleh',   $this->userName],
            ['Dicetak Pada',   Carbon::now()->translatedFormat('d F Y H:i')],
            [''],
            // Header kolom
            ['METRIK', 'NILAI'],
            ['Total Order',              (int)   $row->total_order],
            ['Total Pendapatan (Nett)',  'Rp ' . number_format($row->total_pendapatan, 0, ',', '.')],
            ['Total QRIS',              'Rp ' . number_format($row->total_qris,        0, ',', '.')],
            ['Total Tunai',             'Rp ' . number_format($row->total_tunai,       0, ',', '.')],
            ['Total Diskon',            'Rp ' . number_format($row->total_diskon,      0, ',', '.')],
            ['Total Pajak',             'Rp ' . number_format($row->total_pajak,       0, ',', '.')],
            ['Total Ongkir',            'Rp ' . number_format($row->total_ongkir,      0, ',', '.')],
            ['Order Bayar Nanti (PAY_LATER)', (int) $row->total_pay_later],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            7 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD9EAD3'],
            ]],
        ];
    }
}
