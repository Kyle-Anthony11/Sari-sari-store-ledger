<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements WithMultipleSheets
{
    protected string $dateFilter;

    public function __construct(string $dateFilter)
    {
        $this->dateFilter = $dateFilter;
    }

    public function sheets(): array
    {
        return [
            new OutstandingReceivablesSheet($this->dateFilter),
            new AgingReceivablesSheet($this->dateFilter),
        ];
    }
}
