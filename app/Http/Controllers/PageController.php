<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Models\StudentAchievement;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function dashboard(): View
    {
        $schoolsMap = (new AConnect)->getSekolahMap();
        $schoolNpsn = session('school_npsn');

        $cases = DB::table('cases')
            ->join('reports', 'cases.report_id', '=', 'reports.id')
            ->leftJoin('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->select(
                'cases.*',
                'reports.reporter_role',
                'reports.description',
                'reports.submitted_at',
                'case_slas.status as sla_status',
                'case_slas.resolution_status',
                'case_slas.response_deadline',
                'case_slas.initial_response_at',
                'case_slas.warning_at',
                'case_slas.overdue_at',
                'case_slas.resolution_deadline'
            )
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('cases.school_npsn', $schoolNpsn);
            })
            ->orderByDesc('cases.created_at')
            ->get()
            ->map(function ($case) use ($schoolsMap) {
                $case->school_name = $schoolsMap[$case->school_npsn] ?? '-';

                return $case;
            });

        $now = now();
        $warningLimit = $now->copy()->addHours(6);

        // Fetch case IDs that already have any counselor actions or notes recorded
        $interactedCaseIds = DB::table('case_actions')->pluck('case_id')
            ->merge(DB::table('case_notes')->pluck('case_id'))
            ->unique()
            ->flip();

        $slaAlertCases = collect();
        $slaUpdates = [];

        // STRICT FILTER: Only detect genuinely unresponded reports (PENDING_RESPONSE).
        // Cases that have already been responded to (UNDER_VERIFICATION, IN_HANDLING)
        // or finished (RESOLVED, CLOSED) must NEVER appear in the modal.
        foreach ($cases as $case) {
            $isNotPending = ($case->status !== 'PENDING_RESPONSE');
            $hasInitialResponse = ! empty($case->initial_response_at) || ($case->sla_status === 'COMPLETED');
            $isFinished = in_array($case->status, ['RESOLVED', 'CLOSED'], true)
                || ! empty($case->resolved_at)
                || ! empty($case->closed_at);
            $hasInteractions = isset($interactedCaseIds[$case->id]);

            // Exclude cases that are already responded, in handling, finished, or have counselor interactions
            if ($isNotPending || $hasInitialResponse || $isFinished || $hasInteractions) {
                // If an already-responded case doesn't have initial_response_at recorded yet, populate it
                if ($isNotPending && empty($case->initial_response_at)) {
                    DB::table('case_slas')->where('case_id', $case->id)->update([
                        'initial_response_at' => $case->opened_at ?? $now,
                        'updated_at' => $now,
                    ]);
                    $case->initial_response_at = $case->opened_at ?? $now;
                }

                continue;
            }

            // At this point, the report is strictly unresponded (PENDING_RESPONSE with no response recorded)
            $deadline = $case->response_deadline ? Carbon::parse($case->response_deadline) : null;
            $effectiveResponseStatus = $case->sla_status ?? 'ON_TIME';

            if ($deadline && $deadline->lte($now)) {
                $effectiveResponseStatus = 'OVERDUE';
            } elseif ($deadline && $deadline->lte($warningLimit)) {
                $effectiveResponseStatus = 'WARNING';
            }

            if (in_array($effectiveResponseStatus, ['OVERDUE', 'WARNING'], true)) {
                if ($case->sla_status !== $effectiveResponseStatus) {
                    $slaUpdates[$case->id] = $effectiveResponseStatus;
                    $case->sla_status = $effectiveResponseStatus;
                }

                $case->effective_sla_status = $effectiveResponseStatus;
                $case->is_overdue = ($effectiveResponseStatus === 'OVERDUE');

                if ($deadline) {
                    if ($deadline->isPast()) {
                        $diff = $deadline->diffForHumans($now, [
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                            'parts' => 2,
                        ]);
                        $case->deadline_human = 'Terlewat '.$diff.' yang lalu';
                    } else {
                        $diff = $now->diffForHumans($deadline, [
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                            'parts' => 2,
                        ]);
                        $case->deadline_human = 'Tersisa '.$diff;
                    }
                    $case->formatted_deadline = $deadline->translatedFormat('d M Y, H:i');
                } else {
                    $case->deadline_human = $case->is_overdue ? 'Batas waktu terlewat' : 'Mendekati batas waktu';
                    $case->formatted_deadline = '-';
                }

                $slaAlertCases->push($case);
            }
        }

        // Keep database in sync if any statuses changed for genuinely unresponded cases
        if (! empty($slaUpdates)) {
            foreach ($slaUpdates as $caseId => $newStatus) {
                DB::table('case_slas')->where('case_id', $caseId)->update([
                    'status' => $newStatus,
                    'warning_at' => $newStatus === 'WARNING' ? $now : DB::raw('warning_at'),
                    'overdue_at' => $newStatus === 'OVERDUE' ? $now : DB::raw('overdue_at'),
                    'updated_at' => $now,
                ]);
            }
        }

        // Sort alert cases: Overdue first, higher risk first, earliest deadline first
        $riskOrder = ['CRITICAL' => 1, 'HIGH' => 2, 'MEDIUM' => 3, 'LOW' => 4];
        $slaAlertCases = $slaAlertCases->sort(function ($a, $b) use ($riskOrder) {
            if ($a->effective_sla_status !== $b->effective_sla_status) {
                return $a->effective_sla_status === 'OVERDUE' ? -1 : 1;
            }

            $riskA = $riskOrder[strtoupper($a->risk_level)] ?? 99;
            $riskB = $riskOrder[strtoupper($b->risk_level)] ?? 99;
            if ($riskA !== $riskB) {
                return $riskA <=> $riskB;
            }

            return strcmp($a->response_deadline ?? '', $b->response_deadline ?? '');
        })->values();

        $slaAlertSummary = [
            'total' => $slaAlertCases->count(),
            'overdue' => $slaAlertCases->where('effective_sla_status', 'OVERDUE')->count(),
            'near_overdue' => $slaAlertCases->where('effective_sla_status', 'WARNING')->count(),
        ];

        $slaStats = [
            'CRITICAL' => ['on_time' => 0, 'overdue' => 0],
            'HIGH' => ['on_time' => 0, 'overdue' => 0],
            'MEDIUM' => ['on_time' => 0, 'overdue' => 0],
            'LOW' => ['on_time' => 0, 'overdue' => 0],
        ];

        foreach ($cases as $case) {
            $risk = $case->risk_level;
            if (isset($slaStats[$risk])) {
                // If the response is overdue OR the resolution is overdue, count as overdue
                if ($case->sla_status === 'OVERDUE' || $case->resolution_status === 'OVERDUE') {
                    $slaStats[$risk]['overdue']++;
                } else {
                    $slaStats[$risk]['on_time']++;
                }
            }
        }

        // Dynamic stats calculations
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $reportsThisMonth = DB::table('reports')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $reportsLastMonth = DB::table('reports')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $reportsTrend = $reportsLastMonth > 0 ? round((($reportsThisMonth - $reportsLastMonth) / $reportsLastMonth) * 100) : ($reportsThisMonth > 0 ? 100 : 0);
        $reportsTrendSign = $reportsTrend >= 0 ? '↑' : '↓';

        $handlingThisMonth = DB::table('cases')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->where('status', 'IN_HANDLING')
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $handlingLastMonth = DB::table('cases')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->where('status', 'IN_HANDLING')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $handlingTrend = $handlingLastMonth > 0 ? round((($handlingThisMonth - $handlingLastMonth) / $handlingLastMonth) * 100) : ($handlingThisMonth > 0 ? 100 : 0);
        $handlingTrendSign = $handlingTrend >= 0 ? '↑' : '↓';

        $highRiskNewThisWeek = DB::table('cases')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->whereIn('risk_level', ['HIGH', 'CRITICAL'])
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        // Trend Data (Last 6 months)
        $trendMonths = collect();
        $trendData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $trendMonths->push($monthStart->translatedFormat('M'));
            $count = DB::table('reports')
                ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                    $q->where('school_npsn', $schoolNpsn);
                })
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $trendData->push($count);
        }
        $maxTrend = max(20, $trendData->max() + 5);

        // Category Data
        $categories = DB::table('reports')
            ->when($schoolNpsn, function ($q) use ($schoolNpsn) {
                $q->where('school_npsn', $schoolNpsn);
            })
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();

        $totalReportsCount = $categories->sum('total') ?: 1;
        $categoryData = $categories->map(function ($cat) use ($totalReportsCount) {
            $cat->percentage = round(($cat->total / $totalReportsCount) * 100);

            return $cat;
        })->sortByDesc('total')->values();

        // Colors for donut
        $colors = ['#35aee6', '#277de2', '#ffc85a', '#f47e78', '#e9a0ba', '#a0aec0'];
        $donutGradient = [];
        $currentPercent = 0;
        foreach ($categoryData as $idx => $cat) {
            $color = $colors[$idx % count($colors)];
            $cat->color = $color;
            $nextPercent = $currentPercent + $cat->percentage;
            $donutGradient[] = "{$color} {$currentPercent}% {$nextPercent}%";
            $currentPercent = $nextPercent;
        }
        $donutStyle = 'conic-gradient('.implode(',', $donutGradient).')';
        if (empty($donutGradient)) {
            $donutStyle = 'conic-gradient(#e6eef7 0% 100%)';
        }

        $overdueCount = $cases->filter(function ($c) {
            $isActive = ! in_array($c->status, ['RESOLVED', 'CLOSED'], true);

            return $isActive && ($c->sla_status === 'OVERDUE' || $c->resolution_status === 'OVERDUE');
        })->count();

        // Data capaian prestasi siswa untuk dashboard Guru BK
        $achievementCount = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->count();
        $studentAchievementCount = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->distinct('nisn')->count('nisn');
        $recentAchievements = StudentAchievement::with('category')
            ->when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))
            ->orderByDesc('achievement_date')
            ->limit(4)
            ->get();
        $highestLevel = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))
            ->orderByRaw("CASE achievement_level 
                WHEN 'INTERNASIONAL' THEN 6 
                WHEN 'NASIONAL' THEN 5 
                WHEN 'PROVINSI' THEN 4 
                WHEN 'KOTA' THEN 3 
                WHEN 'KECAMATAN' THEN 2 
                ELSE 1 END DESC")
            ->value('achievement_level');

        return view('dashboard', [
            'cases' => $cases,
            'slaStats' => $slaStats,
            'slaAlertCases' => $slaAlertCases,
            'slaAlertSummary' => $slaAlertSummary,
            'achievementStats' => [
                'total' => $achievementCount,
                'students' => $studentAchievementCount,
                'highestLevel' => $highestLevel,
            ],
            'recentAchievements' => $recentAchievements,
            'stats' => [
                'reports' => DB::table('reports')->when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->count(),
                'handling' => DB::table('cases')->when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->where('status', 'IN_HANDLING')->count(),
                'highRisk' => DB::table('cases')->when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => $overdueCount,

                'reportsTrend' => $reportsTrend,
                'reportsTrendSign' => $reportsTrendSign,
                'handlingTrend' => $handlingTrend,
                'handlingTrendSign' => $handlingTrendSign,
                'highRiskNewThisWeek' => $highRiskNewThisWeek,

                'trendMonths' => $trendMonths,
                'trendData' => $trendData,
                'maxTrend' => $maxTrend,
                'categoryData' => $categoryData,
                'donutStyle' => $donutStyle,
            ],
        ]);
    }

    public function principal()
    {
        $schools = (new AConnect)->getSekolahList();
        $schoolNpsn = session('school_npsn');
        $selectedSchool = $schools->firstWhere('npsn', $schoolNpsn);

        $cases = DB::table('cases')
            ->join('case_slas', 'cases.id', '=', 'case_slas.case_id')
            ->where('cases.school_npsn', $schoolNpsn)
            ->select('cases.*', 'case_slas.status as sla_status')
            ->orderByDesc('cases.updated_at')
            ->get();

        // Data capaian prestasi siswa untuk monitoring Kepala Sekolah
        $totalAchievements = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->count();
        $totalStudentsAchieved = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))->distinct('nisn')->count('nisn');
        $recentAchievements = StudentAchievement::with('category')
            ->when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))
            ->orderByDesc('achievement_date')
            ->limit(4)
            ->get();
        $highestLevel = StudentAchievement::when($schoolNpsn, fn ($q) => $q->where('school_npsn', $schoolNpsn))
            ->orderByRaw("CASE achievement_level 
                WHEN 'INTERNASIONAL' THEN 6 
                WHEN 'NASIONAL' THEN 5 
                WHEN 'PROVINSI' THEN 4 
                WHEN 'KOTA' THEN 3 
                WHEN 'KECAMATAN' THEN 2 
                ELSE 1 END DESC")
            ->value('achievement_level');

        return view('principal.index', [
            'schools' => $schools,
            'selectedSchool' => $selectedSchool,
            'cases' => $cases,
            'achievementStats' => [
                'total' => $totalAchievements,
                'students' => $totalStudentsAchieved,
                'highestLevel' => $highestLevel,
            ],
            'recentAchievements' => $recentAchievements,
            'stats' => [
                'total' => $cases->count(),
                'active' => $cases->whereNotIn('status', ['CLOSED', 'RESOLVED'])->count(),
                'highRisk' => $cases->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count(),
                'overdue' => $cases->where('sla_status', 'OVERDUE')->count(),
            ],
        ]);
    }
}
