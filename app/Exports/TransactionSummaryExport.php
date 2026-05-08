<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Expense;
use App\Models\TransactionDetail;
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
                COALESCE(AVG(grandtotal), 0)                                        AS avg_order_value,
                COALESCE(AVG(discount), 0)                                          AS avg_discount
            ")
            ->first();

        $periode = Carbon::parse($this->startDate)->translatedFormat('d F Y')
                 . ' s/d '
                 . Carbon::parse($this->endDate)->translatedFormat('d F Y');

        // Expenses
        $expensesAgg = Expense::whereBetween('expense_date', [
            Carbon::parse($this->startDate)->format('Y-m-d'),
            Carbon::parse($this->endDate)->format('Y-m-d')
        ])
        ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
        ->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
            COALESCE(SUM(CASE WHEN type = 'in'  THEN amount ELSE 0 END), 0) as total_in
        ")->first();

        $totalExpense   = (float) ($expensesAgg->total_out ?? 0);
        $totalTopUps    = (float) ($expensesAgg->total_in ?? 0);
        $totalKeuntungan = $row->total_pendapatan - $totalExpense;

        // Total Produk Terjual
        $totalProductsSold = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->where('orders.payment_status', 'PAID')
            ->when($this->branchId, fn($q) => $q->where('orders.branch_id', $this->branchId))
            ->sum('transaction_details.quantity');

        // Rasio Tipe Order
        $orderTypeSplitRaw = Order::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('payment_status', 'PAID')
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->selectRaw("
                UPPER(order_type) as order_type,
                COUNT(id) as total_count,
                COALESCE(SUM(grandtotal), 0) as total_omzet
            ")->groupBy('order_type')->get();

        $totalOrdersForSplit = max((int) ($row->total_order ?? 0), 1);

        // Piutang
        $unpaidStats = Order::where('payment_status', 'UNPAID')
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->selectRaw('COUNT(id) as total_count, COALESCE(SUM(grandtotal), 0) as total_amount')
            ->first();

        // Previous period comparison
        $periodDays = $this->startDate->diffInDays($this->endDate);
        $prevStart  = $this->startDate->copy()->subDays($periodDays + 1)->startOfDay();
        $prevEnd    = $this->startDate->copy()->subSecond();
        $prevOmzet  = (float) Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('payment_status', 'PAID')
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->sum('grandtotal');
        $salesOmzet  = (float) $row->total_pendapatan;
        $omzetGrowth = $prevOmzet > 0
            ? round((($salesOmzet - $prevOmzet) / $prevOmzet) * 100, 1) . '%'
            : ($salesOmzet > 0 ? '100%' : 'N/A');

        // Build rows
        $rows = [
            ['RINGKASAN TRANSAKSI — DASHBOARD REPORT'],
            [''],
            ['Periode',        $periode],
            ['Dicetak Oleh',   $this->userName],
            ['Dicetak Pada',   Carbon::now()->translatedFormat('d F Y H:i')],
            [''],
            // Header kolom
            ['METRIK', 'NILAI'],
            ['Total Order',              (int) $row->total_order],
            ['Total Pendapatan (Nett)',  'Rp ' . number_format($row->total_pendapatan, 0, ',', '.')],
            ['Total Kas Keluar',         'Rp ' . number_format($totalExpense,          0, ',', '.')],
            ['Total Keuntungan',         'Rp ' . number_format($totalKeuntungan,       0, ',', '.')],
            ['Total QRIS',              'Rp ' . number_format($row->total_qris,        0, ',', '.')],
            ['Total Tunai',             'Rp ' . number_format($row->total_tunai,       0, ',', '.')],
            ['Total Diskon',            'Rp ' . number_format($row->total_diskon,      0, ',', '.')],
            ['Total Pajak',             'Rp ' . number_format($row->total_pajak,       0, ',', '.')],
            ['Total Ongkir',            'Rp ' . number_format($row->total_ongkir,      0, ',', '.')],
            [''],
            // ── Dashboard Metrics ──
            ['METRIK DASHBOARD', 'NILAI'],
            ['Total Produk Terjual',     (int) $totalProductsSold . ' pcs'],
            ['Top Up Kas',               'Rp ' . number_format($totalTopUps, 0, ',', '.')],
            ['Rata-rata / Transaksi (AOV)', 'Rp ' . number_format($row->avg_order_value, 0, ',', '.')],
            ['Rata-rata Diskon / Order', 'Rp ' . number_format($row->avg_discount, 0, ',', '.')],
            ['Pertumbuhan vs Periode Sebelumnya', $omzetGrowth],
            ['Omzet Periode Sebelumnya', 'Rp ' . number_format($prevOmzet, 0, ',', '.')],
            [''],
            // ── Piutang ──
            ['PIUTANG (BELUM LUNAS)', 'NILAI'],
            ['Jumlah Order Belum Lunas', (int) ($unpaidStats->total_count ?? 0) . ' order'],
            ['Total Nominal Piutang',   'Rp ' . number_format($unpaidStats->total_amount ?? 0, 0, ',', '.')],
            [''],
        ];

        // ── Rasio Tipe Order ──
        if ($orderTypeSplitRaw->isNotEmpty()) {
            $rows[] = ['RASIO TIPE PESANAN', '', '', ''];
            $rows[] = ['Tipe', 'Jumlah Order', 'Persentase', 'Omzet'];
            $typeLabels = [
                'DINE_IN'   => 'Dine-In',
                'TAKE_AWAY' => 'Take-Away',
                'GOFOOD'    => 'GoFood',
                'GRABFOOD'  => 'GrabFood',
                'MAXIM'     => 'Maxim',
            ];
            foreach ($orderTypeSplitRaw as $otRow) {
                if (empty($otRow->order_type)) continue;
                $key   = strtoupper($otRow->order_type);
                $label = $typeLabels[$key] ?? $key;
                $pct   = round(($otRow->total_count / $totalOrdersForSplit) * 100, 1);
                $rows[] = [
                    $label,
                    (int) $otRow->total_count . ' order',
                    $pct . '%',
                    'Rp ' . number_format($otRow->total_omzet, 0, ',', '.'),
                ];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 14]],
            7  => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD9EAD3'],
            ]],
            19 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFD9EAD3'],
            ]],
            27 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFCE4D6'],
            ]],
        ];
    }
}
