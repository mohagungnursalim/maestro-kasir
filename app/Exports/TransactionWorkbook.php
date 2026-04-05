<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransactionWorkbook implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $branchId;
    protected $userName;

    public function __construct($startDate, $endDate, $branchId = null, $userName = 'Sistem')
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->branchId  = $branchId;
        $this->userName  = $userName;
    }

    public function sheets(): array
    {
        return [
            new TransactionDetailSheetExport($this->startDate, $this->endDate, $this->branchId),
            new TransactionSummaryExport($this->startDate, $this->endDate, $this->branchId, $this->userName),
        ];
    }
}
