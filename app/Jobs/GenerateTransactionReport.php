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
            
            $pdf = Pdf::loadView('exports.transactions', [
                'transactions' => $transactions,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'userName' => $this->userName,
            ]);
            
            $filePath = "{$branchFolder}/transaksi_{$branchSlug}_{$date}.pdf";
            
            Storage::put($filePath, $pdf->output());
        }

    }

    


}
