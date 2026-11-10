<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

try {
    $casesByMonth = DB::table('cases')
        ->selectRaw("TO_CHAR(created_at, 'Mon') as month_label, EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month_num, COUNT(*) as total")
        ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
        ->groupByRaw('month_label, year, month_num')
        ->orderByRaw('year, month_num')
        ->get();
    echo "casesByMonth:\n";
    print_r($casesByMonth->toArray());

    $slaStats = DB::table('case_slas')
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->get()
        ->keyBy('status');
    echo "slaStats:\n";
    print_r($slaStats->toArray());

    $casesByStatus = DB::table('cases')
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->get()
        ->keyBy('status');
    echo "casesByStatus:\n";
    print_r($casesByStatus->toArray());

} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
