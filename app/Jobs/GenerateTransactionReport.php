<?php

namespace App\Jobs;

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
use App\Exports\TransactionExport;
use Carbon\Carbon;

class GenerateTransactionReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;
    protected $endDate;
    protected $type;

    /**
     * Create a new job instance.
     */
    public function __construct($startDate, $endDate, $type)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type; // 'pdf' atau 'excel'
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $date = now()->format('d-m-Y');
        if ($this->type === 'excel') {
            $filePath = "reports/transactions_{$date}.xlsx";
            Excel::store(new TransactionExport($this->startDate, $this->endDate), $filePath, 'local');
        } else {
            
            $startDate = Carbon::parse($this->startDate)->startOfDay(); // 00:00:00
            $endDate = Carbon::parse($this->endDate)->endOfDay(); //23:59:59
            
            $transactions = TransactionDetail::with(['product', 'order.user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()->groupBy('order_id');
            
            $pdf = Pdf::loadView('exports.transactions', [
                'transactions' => $transactions,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
            
            $filePath = "reports/transactions_{$date}.pdf";
            
            Storage::put($filePath, $pdf->output());
        }

    }

    


}
