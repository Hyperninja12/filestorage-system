<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$path = "'\$.\"Unit Value\"'";
$expr = "CAST(REPLACE(REPLACE(REPLACE(COALESCE(TRIM(CAST(json_extract(row_data, {$path}) AS TEXT)), '0'), '₱', ''), ',', ''), ' ', '') AS REAL)";
$records = DB::table('import_records')
    ->selectRaw("{$expr} as parsed_val, json_extract(row_data, {$path}) as raw_val")
    ->whereNotNull('row_data')
    ->limit(10)
    ->get();

foreach ($records as $r) {
    echo "Raw: " . $r->raw_val . " | Parsed: " . $r->parsed_val . "\n";
}

$sum = DB::table('import_records')->sum(DB::raw($expr));
echo "\nSum: " . $sum . "\n";
