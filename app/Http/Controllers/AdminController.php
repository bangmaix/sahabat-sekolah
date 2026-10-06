<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        // Cases by month (last 12 months) for bar chart
        $casesByMonth = DB::table('cases')
            ->selectRaw("TO_CHAR(created_at, 'Mon') as month_label, EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month_num, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupByRaw('month_label, year, month_num')
            ->orderByRaw('year, month_num')
            ->get();

        // SLA distribution for donut chart
        $slaStats = DB::table('case_slas')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Cases by status for donut
        $casesByStatus = DB::table('cases')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Recent 5 users
        $recentUsers = DB::table('users')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($user) use ($schoolsMap) {
                $user->school_name = $schoolsMap[$user->school_npsn] ?? '-';

                return $user;
            });

        return view('admin.index', [
            'totalUsers' => $totalUsers,
            'totalReports' => $totalReports,
            'totalCases' => $totalCases,
            'resolvedCases' => $resolvedCases,
            'inHandlingCases' => $inHandlingCases,
            'overdueSla' => $overdueSla,
            'highRiskCases' => $highRiskCases,
            'totalSchools' => $totalSchools,
            'casesByMonth' => $casesByMonth,
            'slaStats' => $slaStats,
            'casesByStatus' => $casesByStatus,
            'recentUsers' => $recentUsers,
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
            $user->school_name = $schoolsMap[$user->school_npsn] ?? '-';

            return $user;
        });

        return view('admin.users', ['users' => $users, 'schools' => (new AConnect)->getSekolahList()]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN'],
            'school_npsn' => ['nullable', 'string'],
        ]);

        DB::table('users')->insert($data + ['password' => Hash::make('password'), 'created_at' => now(), 'updated_at' => now()]);
        auditAction('CREATE_USER', 'USER', $data['email'], ['role' => $data['role'], 'school_npsn' => $data['school_npsn']]);

        return back()->with('success', 'Pengguna berhasil dibuat. Password awal: password.');
    }

    public function updateUser(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:COUNSELOR,PRINCIPAL,ADMIN'],
            'school_npsn' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ]);

        DB::table('users')->where('id', $id)->update($data + ['updated_at' => now()]);
        auditAction('UPDATE_USER', 'USER', $id, $data);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
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
}
