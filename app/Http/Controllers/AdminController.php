<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Mail\AdminResetPasswordMail;
use App\Mail\UserCreatedMail;
use App\Models\AcademicPeriod;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        $schoolsMap = (new AConnect)->getSekolahMap();

        // Stat counters
        $totalUsers = DB::table('users')->count();
        $totalReports = DB::table('reports')->count();
        $totalCases = DB::table('cases')->count();
        $resolvedCases = DB::table('cases')->where('status', 'RESOLVED')->count();
        $inHandlingCases = DB::table('cases')->where('status', 'IN_HANDLING')->count();
        $overdueSla = DB::table('case_slas')->where('status', 'OVERDUE')->count();
        $highRiskCases = DB::table('cases')->whereIn('risk_level', ['HIGH', 'CRITICAL'])->count();
        $totalSchools = count($schoolsMap);

        // Achievement stats
        $totalAchievements = StudentAchievement::count();
        $totalStudentsWithAchievements = StudentAchievement::distinct('nisn')->count('nisn');
        $recentAchievements = StudentAchievement::with('category')
            ->orderByDesc('achievement_date')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($schoolsMap) {
                $item->school_name = $schoolsMap[$item->school_npsn] ?? $item->school_npsn;

                return $item;
            });

        // Cases by month (last 12 months) - Database-agnostic using Carbon date windows
        $twelveMonthsAgo = now()->subMonths(11)->startOfMonth();
        $casesPastYear = DB::table('cases')
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->pluck('created_at');

        $monthlyCountsMap = [];
        foreach ($casesPastYear as $createdAt) {
            $key = Carbon::parse($createdAt)->format('Y-m');
            $monthlyCountsMap[$key] = ($monthlyCountsMap[$key] ?? 0) + 1;
        }

        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthlyLabels[] = $date->locale('id')->isoFormat('MMM');
            $monthlyCounts[] = $monthlyCountsMap[$key] ?? 0;
        }

        // SLA distribution for donut chart
        $slaConfig = [
            'ON_TRACK' => ['label' => 'On Track', 'color' => '#22c55e'],
            'ON_TIME' => ['label' => 'Tepat Waktu', 'color' => '#3b82f6'],
            'WARNING' => ['label' => 'Peringatan', 'color' => '#f59e0b'],
            'OVERDUE' => ['label' => 'Overdue', 'color' => '#ef4444'],
            'COMPLETED' => ['label' => 'Selesai', 'color' => '#8b5cf6'],
        ];

        $slaRaw = DB::table('case_slas')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $slaChartData = [
            'series' => [],
            'labels' => [],
            'colors' => [],
            'total' => 0,
        ];

        foreach ($slaRaw as $status => $count) {
            $count = (int) $count;
            if ($count > 0) {
                $slaChartData['series'][] = $count;
                $slaChartData['labels'][] = $slaConfig[$status]['label'] ?? $status;
                $slaChartData['colors'][] = $slaConfig[$status]['color'] ?? '#9aa9ba';
                $slaChartData['total'] += $count;
            }
        }

        // Cases by status for donut chart
        $statusConfig = [
            'PENDING_RESPONSE' => ['label' => 'Pending', 'color' => '#f59e0b'],
            'UNDER_VERIFICATION' => ['label' => 'Verifikasi', 'color' => '#3b82f6'],
            'IN_HANDLING' => ['label' => 'Ditangani', 'color' => '#f97316'],
            'RESOLVED' => ['label' => 'Selesai', 'color' => '#22c55e'],
            'CLOSED' => ['label' => 'Ditutup', 'color' => '#6b7280'],
        ];

        $statusRaw = DB::table('cases')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChartData = [
            'series' => [],
            'labels' => [],
            'colors' => [],
            'total' => 0,
        ];

        foreach ($statusRaw as $status => $count) {
            $count = (int) $count;
            if ($count > 0) {
                $statusChartData['series'][] = $count;
                $statusChartData['labels'][] = $statusConfig[$status]['label'] ?? $status;
                $statusChartData['colors'][] = $statusConfig[$status]['color'] ?? '#9aa9ba';
                $statusChartData['total'] += $count;
            }
        }

        // Recent 5 users
        $recentUsers = DB::table('users')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($user) use ($schoolsMap) {
                if ($user->role === 'CITY_ADMIN') {
                    $user->school_name = $user->school_name ?: 'Dinas Pendidikan Kota Bukittinggi';
                } elseif ($user->role === 'ADMIN') {
                    $user->school_name = $user->school_name ?: 'Administrator Sistem';
                } else {
                    $user->school_name = $schoolsMap[$user->school_npsn] ?? ($user->school_name ?? '-');
                }

                return $user;
            });

        // Escalated cases (Jenjang 2 & Jenjang 3 Dinas)
        $escalatedCases = DB::table('case_escalations')
            ->join('cases', 'case_escalations.case_id', '=', 'cases.id')
            ->leftJoin('reports', 'cases.report_id', '=', 'reports.id')
            ->select(
                'cases.case_number',
                'cases.school_npsn',
                'cases.category',
                'cases.risk_level',
                'cases.status as case_status',
                'case_escalations.escalation_type',
                'case_escalations.reason',
                'case_escalations.escalated_by',
                'case_escalations.notified_to',
                'case_escalations.created_at'
            )
            ->orderByDesc('case_escalations.created_at')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($schoolsMap) {
                $item->school_name = $schoolsMap[$item->school_npsn] ?? $item->school_npsn;

                return $item;
            });
        $totalEscalatedCases = DB::table('case_escalations')->count();

        return view('admin.index', [
            'totalUsers' => $totalUsers,
            'totalReports' => $totalReports,
            'totalCases' => $totalCases,
            'resolvedCases' => $resolvedCases,
            'inHandlingCases' => $inHandlingCases,
            'overdueSla' => $overdueSla,
            'highRiskCases' => $highRiskCases,
            'totalSchools' => $totalSchools,
            'totalAchievements' => $totalAchievements,
            'totalStudentsWithAchievements' => $totalStudentsWithAchievements,
            'recentAchievements' => $recentAchievements,
            'monthlyLabels' => $monthlyLabels,
            'monthlyCounts' => $monthlyCounts,
            'slaChartData' => $slaChartData,
            'statusChartData' => $statusChartData,
            'recentUsers' => $recentUsers,
            'escalatedCases' => $escalatedCases,
            'totalEscalatedCases' => $totalEscalatedCases,
        ]);
    }

    public function schools()
    {
        return view('admin.schools', [
            'schools' => (new AConnect)->getSekolahList(),
        ]);
    }

    public function users()
    {
        $schoolsMap = (new AConnect)->getSekolahMap();
        $users = DB::table('users')->orderBy('name')->get()->map(function ($user) use ($schoolsMap) {
            if ($user->role === 'CITY_ADMIN') {
                $user->school_name = $user->school_name ?: 'Dinas Pendidikan Kota Bukittinggi';
            } elseif ($user->role === 'ADMIN') {
                $user->school_name = $user->school_name ?: 'Administrator Sistem';
            } else {
                $user->school_name = $schoolsMap[$user->school_npsn] ?? ($user->school_name ?? '-');
            }

            return $user;
        });

        return view('admin.users', ['users' => $users, 'schools' => (new AConnect)->getSekolahList()]);
    }

    public function storeUser(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN'],
            'school_npsn' => ['nullable', 'string'],
            'city_agency' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email pengguna wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'role.required' => 'Pilih role pengguna.',
        ]);

        $schoolName = null;
        if (! empty($data['school_npsn'])) {
            $schoolsMap = (new AConnect)->getSekolahMap();
            $schoolName = $schoolsMap[$data['school_npsn']] ?? null;
        } elseif ($data['role'] === 'CITY_ADMIN') {
            $schoolName = $request->input('city_agency') ?: ($request->input('agency_name') ?: 'Dinas Pendidikan Kota Bukittinggi');
        } elseif ($data['role'] === 'ADMIN') {
            $schoolName = 'Administrator Sistem';
        }

        unset($data['city_agency']);

        $randomPassword = Str::random(8);

        try {
            DB::table('users')->insert($data + [
                'school_name' => $schoolName,
                'password' => Hash::make($randomPassword),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah terdaftar dalam sistem.',
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'users_email_unique')) {
                throw ValidationException::withMessages([
                    'email' => 'Email ini sudah terdaftar dalam sistem.',
                ]);
            }

            throw $e;
        }

        try {
            Mail::to($data['email'])->queue(new UserCreatedMail(
                userData: [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                    'school_name' => $schoolName,
                    'password' => $randomPassword,
                    'login_url' => route('login'),
                ],
                recipientName: $data['name'],
            ));
        } catch (\Throwable $e) {
            Log::error('Gagal memasukkan email akun baru ke antrean (queue): '.$e->getMessage(), [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);
        }

        auditAction('CREATE_USER', 'USER', $data['email'], ['role' => $data['role'], 'school_npsn' => $data['school_npsn']]);

        return back()->with('success', "Pengguna berhasil dibuat. Kata sandi acak 8 karakter telah dikirimkan ke email {$data['email']}.");
    }

    public function updateUser(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN'],
            'school_npsn' => ['nullable', 'string'],
            'city_agency' => ['nullable', 'string'],
            'status' => ['nullable', 'in:ACTIVE,INACTIVE'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'role.required' => 'Pilih role pengguna.',
        ]);

        $schoolName = null;
        if (! empty($data['school_npsn'])) {
            $schoolsMap = (new AConnect)->getSekolahMap();
            $schoolName = $schoolsMap[$data['school_npsn']] ?? null;
        } elseif ($data['role'] === 'CITY_ADMIN') {
            $schoolName = $request->input('city_agency') ?: ($request->input('agency_name') ?: 'Dinas Pendidikan Kota Bukittinggi');
        } elseif ($data['role'] === 'ADMIN') {
            $schoolName = 'Administrator Sistem';
        }

        $status = $data['status'] ?? 'ACTIVE';
        unset($data['status'], $data['city_agency']);

        DB::table('users')->where('id', $id)->update($data + [
            'school_name' => $schoolName,
            'status' => $status,
            'updated_at' => now(),
        ]);
        auditAction('UPDATE_USER', 'USER', $id, $data + ['status' => $status]);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function resetPassword(int $id)
    {
        $user = DB::table('users')->where('id', $id)->firstOrFail();

        $token = Str::random(64);
        $now = now();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => $now,
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $schoolsMap = (new AConnect)->getSekolahMap();
        $schoolName = $user->school_name ?? ($user->school_npsn ? ($schoolsMap[$user->school_npsn] ?? null) : null);

        try {
            Mail::to($user->email)->queue(new AdminResetPasswordMail(
                userData: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_name' => $schoolName,
                    'reset_url' => $resetUrl,
                    'expires_minutes' => 60,
                ],
                recipientName: $user->name,
            ));
        } catch (\Throwable $e) {
            Log::error('Gagal memasukkan email reset password oleh admin ke antrean: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        auditAction('RESET_USER_PASSWORD', 'USER', $id, ['email' => $user->email]);

        return back()->with('success', "Tautan reset kata sandi untuk {$user->name} ({$user->email}) berhasil dikirimkan ke email. Berlaku selama 60 menit.");
    }

    public function deleteUser(int $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        DB::table('users')->where('id', $id)->delete();
        auditAction('DELETE_USER', 'USER', $id, ['email' => $user->email ?? null]);

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function masterData()
    {
        return view('admin.master-data', [
            'categories' => DB::table('bullying_categories')->orderBy('name')->get()->map(function ($category) {
                $category->subcategories = DB::table('bullying_subcategories')->where('category_id', $category->id)->orderBy('name')->get();

                return $category;
            }),
            'achievementCategories' => AchievementCategory::orderBy('name')->get(),
            'academicPeriods' => AcademicPeriod::orderedList(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('bullying_categories')->insert([
            'code' => strtoupper(str_replace(' ', '_', $data['name'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        auditAction('CREATE_CATEGORY', 'BULLYING_CATEGORY', null, $data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function storeSubcategory(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:bullying_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'default_risk_level' => ['required', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'risk_score' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        DB::table('bullying_subcategories')->insert([
            'category_id' => $data['category_id'],
            'code' => strtoupper(str_replace(' ', '_', $data['name'])),
            'name' => $data['name'],
            'default_risk_level' => $data['default_risk_level'],
            'risk_score' => $data['risk_score'],
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        auditAction('CREATE_SUBCATEGORY', 'BULLYING_SUBCATEGORY', null, $data);

        return back()->with('success', 'Subkategori berhasil ditambahkan.');
    }

    public function slaConfigurations()
    {
        $configs = DB::table('sla_configurations')->orderByRaw("CASE risk_level WHEN 'CRITICAL' THEN 1 WHEN 'HIGH' THEN 2 WHEN 'MEDIUM' THEN 3 WHEN 'LOW' THEN 4 END")->get();

        return view('admin.sla-configurations', compact('configs'));
    }

    public function updateSlaConfiguration(Request $request, int $id)
    {
        $data = $request->validate([
            'response_time_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'resolution_time_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        DB::table('sla_configurations')->where('id', $id)->update([
            'response_time_hours' => $data['response_time_hours'],
            'resolution_time_days' => $data['resolution_time_days'],
            'updated_at' => now(),
            'updated_by' => session('user_id'),
        ]);

        auditAction('UPDATE_SLA_CONFIG', 'SLA_CONFIGURATION', $id, $data);

        return back()->with('success', 'Konfigurasi SLA berhasil diperbarui.');
    }

    // ------------------------------------------------------------------
    // Achievement Categories
    // ------------------------------------------------------------------

    public function storeAchievementCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:achievement_categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.unique' => 'Kategori prestasi dengan nama tersebut sudah ada.',
        ]);

        AchievementCategory::create([
            'code' => strtoupper(str_replace(' ', '_', $data['name'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => true,
        ]);

        auditAction('CREATE_ACHIEVEMENT_CATEGORY', 'ACHIEVEMENT_CATEGORY', null, $data);

        return back()->with('success', 'Kategori prestasi berhasil ditambahkan.');
    }

    public function toggleAchievementCategory(int $id)
    {
        $category = AchievementCategory::findOrFail($id);
        $category->update(['status' => ! $category->status]);
        auditAction('TOGGLE_ACHIEVEMENT_CATEGORY', 'ACHIEVEMENT_CATEGORY', $id, ['status' => $category->status]);

        $label = $category->status ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kategori prestasi berhasil {$label}.");
    }

    // ------------------------------------------------------------------
    // Academic Periods (Master Semester Dapodik)
    // ------------------------------------------------------------------

    public function storeAcademicPeriod(Request $request)
    {
        $data = $request->validate([
            'semester_id' => ['required', 'string', 'max:10', 'unique:academic_periods,semester_id', 'regex:/^\d{5}$/'],
            'name' => ['required', 'string', 'max:255'],
        ], [
            'semester_id.unique' => 'ID semester tersebut sudah ada.',
            'semester_id.regex' => 'ID semester harus 5 digit angka (contoh: 20261).',
        ]);

        $maxOrder = AcademicPeriod::max('order_no') ?? 0;

        AcademicPeriod::create([
            'semester_id' => $data['semester_id'],
            'name' => $data['name'],
            'is_active' => false,
            'is_fallback' => false,
            'order_no' => $maxOrder + 1,
        ]);

        auditAction('CREATE_ACADEMIC_PERIOD', 'ACADEMIC_PERIOD', null, $data);

        return back()->with('success', 'Periode akademik berhasil ditambahkan.');
    }

    public function setActivePeriod(int $id)
    {
        // Hanya boleh ada 1 periode aktif
        AcademicPeriod::query()->update(['is_active' => false]);
        $period = AcademicPeriod::findOrFail($id);
        $period->update(['is_active' => true]);
        auditAction('SET_ACTIVE_PERIOD', 'ACADEMIC_PERIOD', $id, ['semester_id' => $period->semester_id]);

        return back()->with('success', "Periode {$period->name} ditetapkan sebagai semester aktif.");
    }

    public function setFallbackPeriod(int $id)
    {
        // Hanya boleh ada 1 periode fallback
        AcademicPeriod::query()->update(['is_fallback' => false]);
        $period = AcademicPeriod::findOrFail($id);
        $period->update(['is_fallback' => true]);
        auditAction('SET_FALLBACK_PERIOD', 'ACADEMIC_PERIOD', $id, ['semester_id' => $period->semester_id]);

        return back()->with('success', "Periode {$period->name} ditetapkan sebagai semester cadangan (fallback).");
    }
}
