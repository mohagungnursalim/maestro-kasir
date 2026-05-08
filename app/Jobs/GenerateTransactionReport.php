<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\TransactionDetail;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\TransactionWorkbook;
use Carbon\Carbon;

class GenerateTransactionReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;
    protected $endDate;
    protected $type;
    protected $userName;
    protected $branchId;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, $type, $userName = 'Sistem', $branchId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type; // 'pdf' atau 'excel'
        $this->userName = $userName;
        $this->branchId = $branchId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $date = now()->format('d-m-Y');
        
        // Tentukan folder dan prefix nama file berdasarkan branch
        $branchSlug = 'global';
        $branchFolder = 'reports/global';
        if ($this->branchId) {
            $branch = Branch::find($this->branchId);
            $branchSlug = $branch ? Str::slug($branch->name) : "branch-{$this->branchId}";
            $branchFolder = "reports/branch_{$this->branchId}";
        }

        if ($this->type === 'excel') {
            $filePath = "{$branchFolder}/transaksi_{$branchSlug}_{$date}.xlsx";
            Excel::store(new TransactionWorkbook($this->startDate, $this->endDate, $this->branchId, $this->userName), $filePath, 'local');
        } else {
            
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            $activeBranchId = $this->branchId;
            
            $transactions = TransactionDetail::with(['product', 'order.user', 'order.branch'])
                ->whereHas('order', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                          ->where('payment_status', 'PAID')
                          ->when($this->branchId, function($q) {
                              $q->where('branch_id', $this->branchId);
                          });
                })
                ->get()
                ->groupBy('order_id');

            // ── Dashboard-quality metrics (same as Transaction::exportPdf) ──
            $orderQuery = \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId));

            $ordersAgg = (clone $orderQuery)->selectRaw("
                COUNT(id) as total_orders,
                COALESCE(SUM(grandtotal), 0) as total_sales,
                COALESCE(AVG(grandtotal), 0) as avg_order_value,
                COALESCE(AVG(discount), 0) as avg_discount
            ")->first();

            $totalProductsSold = TransactionDetail::join('orders', 'transaction_details.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('orders.branch_id', $activeBranchId))
                ->sum('transaction_details.quantity');

            $expensesAgg = \App\Models\Expense::whereBetween('expense_date', [
                    $startDate->format('Y-m-d'), $endDate->format('Y-m-d')
                ])
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END), 0) as total_out,
                    COALESCE(SUM(CASE WHEN type = 'in'  THEN amount ELSE 0 END), 0) as total_in
                ")->first();

            $orderTypeSplitRaw = (clone $orderQuery)->selectRaw("
                UPPER(order_type) as order_type,
                COUNT(id) as total_count,
                COALESCE(SUM(grandtotal), 0) as total_omzet
            ")->groupBy('order_type')->get();

            $totalOrdersForSplit = max((int) ($ordersAgg->total_orders ?? 0), 1);
            $orderTypeSplit = [];
            foreach ($orderTypeSplitRaw as $row) {
                if (empty($row->order_type)) continue;
                $key = strtoupper($row->order_type);
                $orderTypeSplit[$key] = [
                    'count'      => (int) $row->total_count,
                    'omzet'      => (float) $row->total_omzet,
                    'percentage' => round(($row->total_count / $totalOrdersForSplit) * 100, 1),
                ];
            }

            $unpaidStats = \App\Models\Order::where('payment_status', 'UNPAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->selectRaw('COUNT(id) as total_count, COALESCE(SUM(grandtotal), 0) as total_amount')
                ->first();

            $periodDays = $startDate->diffInDays($endDate);
            $prevStart  = $startDate->copy()->subDays($periodDays + 1)->startOfDay();
            $prevEnd    = $startDate->copy()->subSecond();
            $prevOmzet  = (float) \App\Models\Order::whereBetween('created_at', [$prevStart, $prevEnd])
                ->where('payment_status', 'PAID')
                ->when($activeBranchId, fn($q) => $q->where('branch_id', $activeBranchId))
                ->sum('grandtotal');

            $salesOmzet  = (float) ($ordersAgg->total_sales ?? 0);
            $omzetGrowth = $prevOmzet > 0
                ? round((($salesOmzet - $prevOmzet) / $prevOmzet) * 100, 1)
                : ($salesOmzet > 0 ? 100.0 : null);

            $dashboardStats = [
                'totalOrders'       => (int) ($ordersAgg->total_orders ?? 0),
                'totalProductsSold' => (int) $totalProductsSold,
                'avgOrderValue'     => (float) ($ordersAgg->avg_order_value ?? 0),
                'avgDiscount'       => (float) ($ordersAgg->avg_discount ?? 0),
                'totalTopUps'       => (float) ($expensesAgg->total_in ?? 0),
                'totalExpenseOut'   => (float) ($expensesAgg->total_out ?? 0),
                'orderTypeSplit'    => $orderTypeSplit,
                'unpaidOrders'      => (int) ($unpaidStats->total_count ?? 0),
                'unpaidAmount'      => (float) ($unpaidStats->total_amount ?? 0),
                'prevOmzet'         => $prevOmzet,
                'omzetGrowth'       => $omzetGrowth,
            ];
            
            $pdf = Pdf::loadView('exports.transactions', [
                'transactions'   => $transactions,
                'startDate'      => $startDate,
                'endDate'        => $endDate,
                'userName'       => $this->userName,
                'dashboardStats' => $dashboardStats,
            ]);
            
            $filePath = "{$branchFolder}/transaksi_{$branchSlug}_{$date}.pdf";
            
            Storage::put($filePath, $pdf->output());
        }

    }

    


}
