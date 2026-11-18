<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportsExportController extends Controller
{
    public function export()
    {
        $filter = request()->get('filter', 'today');
        return Excel::download(new ReportsExport($filter), 'reports_' . now()->format('Y-m-d') . '.xlsx');
    }
}
