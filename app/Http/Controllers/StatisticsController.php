<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $schoolNpsn = session('school_npsn');
        $base = DB::table('cases')->where('school_npsn', $schoolNpsn);
        $cases = $base->get();
        $sla = DB::table('case_slas')->join('cases', 'case_slas.case_id', '=', 'cases.id')->where('cases.school_npsn', $schoolNpsn)->get();

        return view('statistics.index', [
            'stats' => [
                'total' => $cases->count(),
                'resolved' => $cases->whereIn('status', ['RESOLVED', 'CLOSED'])->count(),
                'active' => $cases->whereNotIn('status', ['RESOLVED', 'CLOSED'])->count(),
                'highRisk' => $cases->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => $sla->where('status', 'OVERDUE')->count(),
            ],
            'categories' => $cases->groupBy('category')->map->count()->sortDesc(),
            'statuses' => $cases->groupBy('status')->map->count(),
            'risks' => $cases->groupBy('risk_level')->map->count(),
            'slas' => $sla->groupBy('status')->map->count(),
        ]);
    }

    public function export()
    {
        $schoolNpsn = session('school_npsn');
        $rows = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select('cases.case_number', 'cases.category', 'cases.risk_level', 'cases.status', 'case_slas.status as sla_status', 'cases.opened_at', 'cases.resolved_at', 'cases.closed_at')
            ->orderBy('cases.opened_at')
            ->get();

        auditAction('EXPORT_STATISTICS', 'REPORT', null, ['row_count' => $rows->count()]);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Case Number', 'Category', 'Risk Level', 'Case Status', 'SLA Status', 'Opened At', 'Resolved At', 'Closed At']);
            foreach ($rows as $row) {
                fputcsv($handle, (array) $row);
            }
            fclose($handle);
        }, 'sahabat-sekolah-statistics.csv', ['Content-Type' => 'text/csv']);
    }
}
