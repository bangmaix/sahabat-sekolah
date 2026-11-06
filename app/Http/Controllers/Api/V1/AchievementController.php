<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\AConnect;
use App\Helpers\AppStorage;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\StudentAchievement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    /**
     * Get paginated achievements for authenticated user's school.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = StudentAchievement::with('category:id,name')
            ->where('school_npsn', $schoolNpsn);

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($level = $request->query('achievement_level')) {
            $query->where('achievement_level', $level);
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('student_name', $likeOp, "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('achievement_name', $likeOp, "%{$search}%");
            });
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $achievements = $query->orderByDesc('achievement_date')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $achievements->items(),
            'meta' => [
                'current_page' => $achievements->currentPage(),
                'last_page' => $achievements->lastPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
            ],
        ]);
    }

    /**
     * Get achievement detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $achievement = StudentAchievement::with('category:id,name')
            ->where('id', $id)
            ->where('school_npsn', $user->school_npsn)
            ->first();

        if (! $achievement) {
            return response()->json([
                'success' => false,
                'message' => 'Prestasi siswa tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $achievement,
        ]);
    }

    /**
     * Store new student achievement.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        $data = $request->validate([
            'nisn' => ['required', 'string', 'max:20'],
            'student_name' => ['required', 'string', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:50'],
            'semester_id' => ['nullable', 'string', 'max:10'],
            'category_id' => ['required', 'integer', 'exists:achievement_categories,id'],
            'achievement_name' => ['required', 'string', 'max:255'],
            'achievement_level' => ['required', 'in:SEKOLAH,KECAMATAN,KOTA,PROVINSI,NASIONAL,INTERNASIONAL'],
            'ranking' => ['required', 'string', 'max:100'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'achievement_date' => ['required', 'date'],
            'points' => ['nullable', 'integer', 'min:0'],
            'certificate_file' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'photo_file' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png'],
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate_file')) {
            $certificatePath = AppStorage::store($request->file('certificate_file'), 'achievements/certificates', 'private');
        }

        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $photoPath = AppStorage::store($request->file('photo_file'), 'achievements/photos', 'public');
        }

        $defaultPoints = [
            'SEKOLAH' => 5,
            'KECAMATAN' => 10,
            'KOTA' => 20,
            'PROVINSI' => 35,
            'NASIONAL' => 50,
            'INTERNASIONAL' => 75,
        ];

        $points = $data['points'] ?? ($defaultPoints[$data['achievement_level']] ?? 10);

        $achievement = StudentAchievement::create([
            'school_npsn' => $user->school_npsn,
            'created_by' => $user->id,
            'category_id' => $data['category_id'],
            'semester_id' => $data['semester_id'] ?? null,
            'nisn' => $data['nisn'],
            'student_name' => $data['student_name'],
            'class_name' => $data['class_name'] ?? null,
            'achievement_name' => $data['achievement_name'],
            'achievement_level' => $data['achievement_level'],
            'ranking' => $data['ranking'],
            'organizer' => $data['organizer'] ?? null,
            'achievement_date' => $data['achievement_date'],
            'points' => $points,
            'certificate_path' => $certificatePath,
            'photo_path' => $photoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prestasi siswa berhasil ditambahkan.',
            'data' => $achievement,
        ], 201);
    }

    /**
     * Search students via AConnect integration.
     */
    public function searchStudents(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $query = trim((string) $request->query('query'));

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $schoolNpsn = $user->school_npsn;
        $aConnect = new AConnect;
        $students = [];

        try {
            $activePeriod = AcademicPeriod::where('is_active', true)->first();
            $semesterId = $activePeriod ? $activePeriod->semester_id : '20242';
            $rawStudents = $aConnect->getSiswaSekolah($schoolNpsn, $semesterId);

            if (! empty($rawStudents)) {
                $students = collect($rawStudents)
                    ->map(fn ($s) => [
                        'nisn' => trim((string) ($s['nisn'] ?? '')),
                        'name' => trim((string) ($s['nm_siswa'] ?? '')),
                        'class_name' => trim((string) ($s['nm_kelas'] ?? '')),
                        'gender' => $s['jenis_kelamin'] ?? null,
                    ])
                    ->filter(function ($s) use ($query) {
                        return stripos($s['name'], $query) !== false || stripos($s['nisn'], $query) !== false;
                    })
                    ->values()
                    ->take(25)
                    ->all();
            }
        } catch (\Throwable $e) {
            // Fallback to local achievements student records
            $students = StudentAchievement::where('school_npsn', $schoolNpsn)
                ->where(function ($q) use ($query) {
                    $q->where('student_name', 'like', "%{$query}%")
                        ->orWhere('nisn', 'like', "%{$query}%");
                })
                ->select('nisn', 'student_name as name', 'class_name')
                ->distinct()
                ->take(25)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }
}
