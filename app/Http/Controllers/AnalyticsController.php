<?php

namespace App\Http\Controllers;

use App\Models\ImportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /** Unit Value threshold: PAR = 50,000 and up, ICS = below 50,000. */
    public const PAR_ICS_THRESHOLD = 50000;

    public function index()
    {
        $totalCount = ImportRecord::count();
        $parCount = ImportRecord::whereRaw($this->unitValueRawCondition('par'))->count();
        $icsCount = ImportRecord::whereRaw($this->unitValueRawCondition('ics'))->count();

        // Calculate total value by summing raw unit values.
        $totalValuePath = $this->unitValueRawExpression();
        $totalValue = DB::table('import_records')->sum(DB::raw($totalValuePath)) ?? 0;
        
        $parValue = DB::table('import_records')->whereRaw($this->unitValueRawCondition('par'))->sum(DB::raw($totalValuePath)) ?? 0;
        $icsValue = DB::table('import_records')->whereRaw($this->unitValueRawCondition('ics'))->sum(DB::raw($totalValuePath)) ?? 0;

        // Top 10 Personnel by Count
        // Exclude empty names to prevent weird graphs
        $topPersonnel = DB::table('import_records')
            ->selectRaw('COALESCE(NULLIF(TRIM(json_extract(row_data, \'$."Person Responsible"\')), \'\'), \'Unassigned\') as name, count(*) as count')
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top 10 Categories by Count
        $topCategories = DB::table('import_records')
            ->selectRaw('COALESCE(NULLIF(TRIM(json_extract(row_data, \'$."Category"\')), \'\'), \'Unassigned\') as name, count(*) as count')
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('analytics.index', compact(
            'totalCount', 'parCount', 'icsCount', 
            'totalValue', 'parValue', 'icsValue',
            'topPersonnel', 'topCategories'
        ));
    }

    private function unitValueRawExpression(): string
    {
        $path = "'\$.\"Unit Value\"'";
        return "CAST(REPLACE(REPLACE(REPLACE(COALESCE(TRIM(CAST(json_extract(row_data, {$path}) AS TEXT)), '0'), '₱', ''), ',', ''), ' ', '') AS REAL)";
    }

    private function unitValueRawCondition(string $type): string
    {
        $op = $type === 'par' ? '>=' : '<';
        $th = self::PAR_ICS_THRESHOLD;
        $expr = $this->unitValueRawExpression();
        return "({$expr} {$op} {$th})";
    }
}
