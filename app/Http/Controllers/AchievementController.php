<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Models\AcademicPeriod;
use App\Models\AchievementCategory;
use App\Models\StudentAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    /**
     * Daftar prestasi siswa di sekolah Guru BK, dikelompokkan berdasarkan NISN siswa.
     */
    public function index(Request $request)
    {
        $schoolNpsn = session('school_npsn');
        $categories = AchievementCategory::active()->orderBy('name')->get();
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = StudentAchievement::with('category', 'creator')
            ->where('school_npsn', $schoolNpsn);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($level = $request->input('achievement_level')) {
            $query->where('achievement_level', $level);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search, $likeOp) {
                $builder->where('student_name', $likeOp, "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('achievement_name', $likeOp, "%{$search}%");
            });
        }

        $allAchievements = $query->orderByDesc('achievement_date')->get();

        $levelWeights = [
            'INTERNASIONAL' => 6,
            'NASIONAL' => 5,
            'PROVINSI' => 4,
            'KOTA' => 3,
            'KECAMATAN' => 2,
            'SEKOLAH' => 1,
        ];

        // Kelompokkan data prestasi berdasarkan NISN siswa
        $studentsGrouped = $allAchievements->groupBy('nisn')->map(function ($items, $nisn) use ($levelWeights) {
            $latest = $items->first();
            $highest = $items->sortByDesc(fn ($a) => $levelWeights[$a->achievement_level] ?? 0)->first();

            return (object) [
                'nisn' => (string) $nisn,
                'student_name' => $latest->student_name,
                'class_name' => $latest->class_name,
                'total_achievements' => $items->count(),
                'highest_level' => $highest?->achievement_level ?? 'SEKOLAH',
                'highest_ranking' => $highest?->ranking ?? '',
                'achievements' => $items,
            ];
        })->values();

        return view('achievements.index', [
            'studentsGrouped' => $studentsGrouped,
            'totalStudents' => $studentsGrouped->count(),
            'totalAchievements' => $allAchievements->count(),
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'achievement_level', 'search']),
        ]);
    }

    /**
     * Portofolio lengkap prestasi siswa berdasarkan NISN.
     */
    public function studentProfile(string $nisn)
    {
        $schoolNpsn = session('school_npsn');

        $achievements = StudentAchievement::with('category', 'creator')
            ->where('school_npsn', $schoolNpsn)
            ->where('nisn', $nisn)
            ->orderByDesc('achievement_date')
            ->get();

        abort_if($achievements->isEmpty(), 404, 'Data prestasi siswa tidak ditemukan.');

        $levelWeights = [
            'INTERNASIONAL' => 6,
            'NASIONAL' => 5,
            'PROVINSI' => 4,
            'KOTA' => 3,
            'KECAMATAN' => 2,
            'SEKOLAH' => 1,
        ];
        $highest = $achievements->sortByDesc(fn ($a) => $levelWeights[$a->achievement_level] ?? 0)->first();

        $student = (object) [
            'nisn' => $nisn,
            'student_name' => $achievements->first()->student_name,
            'class_name' => $achievements->first()->class_name,
            'total_achievements' => $achievements->count(),
            'highest_level' => $highest?->achievement_level ?? 'SEKOLAH',
            'highest_achievement' => $highest,
        ];

        return view('achievements.student', [
            'student' => $student,
            'achievements' => $achievements,
        ]);
    }

    /**
     * Form tambah prestasi.
     */
    public function create(Request $request)
    {
        $schoolNpsn = (string) session('school_npsn');
        $categories = AchievementCategory::active()->orderBy('name')->get();
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $fallbackPeriod = AcademicPeriod::where('is_fallback', true)->orderBy('order_no')->first();
        $periods = AcademicPeriod::orderedList();

        $aConnect = new AConnect;
        $semesterId = $activePeriod?->semester_id ?? $fallbackPeriod?->semester_id ?? '20252';
        $isFallback = false;

        // Coba semester aktif
        $students = $schoolNpsn ? $aConnect->getSiswaSekolah($schoolNpsn, $semesterId) : [];

        // Jika kosong dan ada periode cadangan yang berbeda, coba cadangan
        if (
            empty($students) &&
            $fallbackPeriod &&
            $fallbackPeriod->semester_id !== $semesterId &&
            $schoolNpsn
        ) {
            $fallbackStudents = $aConnect->getSiswaSekolah($schoolNpsn, $fallbackPeriod->semester_id);
            if (! empty($fallbackStudents)) {
                $students = $fallbackStudents;
                $semesterId = $fallbackPeriod->semester_id;
                $isFallback = true;
            }
        }

        // Prefill jika ada parameter nisn di URL (contoh dari tombol + Tambah Prestasi di card siswa)
        $prefillNisn = $request->query('nisn');
        $prefillStudent = null;
        if ($prefillNisn) {
            $foundInApi = collect($students)->firstWhere('nisn', $prefillNisn);
            if ($foundInApi) {
                $prefillStudent = $foundInApi;
            } else {
                $prevRecord = StudentAchievement::where('school_npsn', $schoolNpsn)->where('nisn', $prefillNisn)->first();
                if ($prevRecord) {
                    $prefillStudent = [
                        'nisn' => $prevRecord->nisn,
                        'nm_siswa' => $prevRecord->student_name,
                        'nm_kelas' => $prevRecord->class_name,
                    ];
                }
            }
        }

        return view('achievements.create', [
            'categories' => $categories,
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'fallbackPeriod' => $fallbackPeriod,
            'students' => $students,
            'prefillStudent' => $prefillStudent,
            'prefillNisn' => $prefillNisn,
            'selectedSemesterId' => $semesterId,
            'isFallback' => $isFallback,
            'levels' => $this->achievementLevels(),
            'rankings' => $this->rankingOptions(),
        ]);
    }

    /**
     * Simpan data prestasi baru.
     */
    public function store(Request $request)
    {
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
            'description' => ['nullable', 'string', 'max:1000'],
            'certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'nisn.required' => 'NISN siswa wajib diisi.',
            'student_name.required' => 'Nama siswa wajib diisi. Tarik data dari AConnect terlebih dahulu.',
            'category_id.required' => 'Pilih kategori prestasi.',
            'achievement_name.required' => 'Nama lomba/penghargaan wajib diisi.',
            'achievement_level.required' => 'Pilih tingkat prestasi.',
            'ranking.required' => 'Pilih peringkat/juara.',
            'achievement_date.required' => 'Tanggal perolehan prestasi wajib diisi.',
            'certificate.mimes' => 'Berkas bukti harus berformat JPG, PNG, atau PDF.',
            'certificate.max' => 'Ukuran berkas maksimal 4 MB.',
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store('achievements/certificates', 'local');
        }

        StudentAchievement::create([
            'school_npsn' => session('school_npsn'),
            'nisn' => $data['nisn'],
            'student_name' => $data['student_name'],
            'class_name' => $data['class_name'] ?? null,
            'semester_id' => $data['semester_id'] ?? null,
            'category_id' => $data['category_id'],
            'achievement_name' => $data['achievement_name'],
            'achievement_level' => $data['achievement_level'],
            'ranking' => $data['ranking'],
            'organizer' => $data['organizer'] ?? null,
            'achievement_date' => $data['achievement_date'],
            'description' => $data['description'] ?? null,
            'certificate_path' => $certificatePath,
            'created_by' => session('user_id'),
        ]);

        auditAction('CREATE_ACHIEVEMENT', 'STUDENT_ACHIEVEMENT', null, [
            'nisn' => $data['nisn'],
            'student_name' => $data['student_name'],
            'achievement_name' => $data['achievement_name'],
            'achievement_level' => $data['achievement_level'],
        ]);

        return redirect()->route('achievements.index')
            ->with('success', 'Prestasi siswa berhasil dicatat.');
    }

    /**
     * Detail prestasi.
     */
    public function show(int $id)
    {
        $achievement = StudentAchievement::with('category', 'creator')
            ->where('school_npsn', session('school_npsn'))
            ->findOrFail($id);

        return view('achievements.show', [
            'achievement' => $achievement,
        ]);
    }

    /**
     * Form edit prestasi.
     */
    public function edit(int $id)
    {
        $achievement = StudentAchievement::where('school_npsn', session('school_npsn'))
            ->findOrFail($id);

        $categories = AchievementCategory::active()->orderBy('name')->get();
        $periods = AcademicPeriod::orderedList();

        return view('achievements.edit', [
            'achievement' => $achievement,
            'categories' => $categories,
            'periods' => $periods,
            'levels' => $this->achievementLevels(),
            'rankings' => $this->rankingOptions(),
        ]);
    }

    /**
     * Simpan perubahan data prestasi.
     */
    public function update(Request $request, int $id)
    {
        $achievement = StudentAchievement::where('school_npsn', session('school_npsn'))
            ->findOrFail($id);

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:achievement_categories,id'],
            'achievement_name' => ['required', 'string', 'max:255'],
            'achievement_level' => ['required', 'in:SEKOLAH,KECAMATAN,KOTA,PROVINSI,NASIONAL,INTERNASIONAL'],
            'ranking' => ['required', 'string', 'max:100'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'achievement_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $certificatePath = $achievement->certificate_path;
        if ($request->hasFile('certificate')) {
            if ($certificatePath) {
                Storage::disk('local')->delete($certificatePath);
            }
            $certificatePath = $request->file('certificate')->store('achievements/certificates', 'local');
        }

        $achievement->update([
            'category_id' => $data['category_id'],
            'achievement_name' => $data['achievement_name'],
            'achievement_level' => $data['achievement_level'],
            'ranking' => $data['ranking'],
            'organizer' => $data['organizer'] ?? null,
            'achievement_date' => $data['achievement_date'],
            'description' => $data['description'] ?? null,
            'certificate_path' => $certificatePath,
        ]);

        auditAction('UPDATE_ACHIEVEMENT', 'STUDENT_ACHIEVEMENT', $id, $data);

        return redirect()->route('achievements.show', $id)
            ->with('success', 'Data prestasi berhasil diperbarui.');
    }

    /**
     * Hapus data prestasi.
     */
    public function destroy(int $id)
    {
        $achievement = StudentAchievement::where('school_npsn', session('school_npsn'))
            ->findOrFail($id);

        if ($achievement->certificate_path) {
            Storage::disk('local')->delete($achievement->certificate_path);
        }

        auditAction('DELETE_ACHIEVEMENT', 'STUDENT_ACHIEVEMENT', $id, [
            'nisn' => $achievement->nisn,
            'student_name' => $achievement->student_name,
            'achievement_name' => $achievement->achievement_name,
        ]);

        $achievement->delete();

        return redirect()->route('achievements.index')
            ->with('success', 'Data prestasi berhasil dihapus.');
    }

    /**
     * Download/tampilkan berkas sertifikat.
     */
    public function downloadCertificate(int $id)
    {
        $achievement = StudentAchievement::where('school_npsn', session('school_npsn'))
            ->findOrFail($id);

        abort_unless($achievement->certificate_path && Storage::disk('local')->exists($achievement->certificate_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download(
            $achievement->certificate_path,
            'Sertifikat-'.$achievement->student_name.'.'.pathinfo($achievement->certificate_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Endpoint AJAX: tarik data siswa dari AConnect menggunakan NISN.
     * Strategi: coba semester aktif dahulu, fallback ke semester sebelumnya jika gagal.
     */
    public function fetchStudent(Request $request)
    {
        $request->validate([
            'nisn' => ['required', 'string', 'max:20'],
        ]);

        $schoolNpsn = session('school_npsn');
        $nisn = trim($request->input('nisn'));
        $aConnect = new AConnect;

        // Dapatkan semester aktif dan fallback
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $fallbackPeriod = AcademicPeriod::where('is_fallback', true)->orderBy('order_no')->first();

        $semesterId = $activePeriod?->semester_id ?? $fallbackPeriod?->semester_id ?? '20252';
        $usedPeriod = $activePeriod;

        // Coba semester aktif
        $response = $aConnect->getDataSiswa($schoolNpsn, $semesterId, $nisn);

        // Jika gagal dan ada fallback berbeda, coba fallback
        if (
            empty($response['data']) &&
            $fallbackPeriod &&
            $fallbackPeriod->semester_id !== $semesterId
        ) {
            $response = $aConnect->getDataSiswa($schoolNpsn, $fallbackPeriod->semester_id, $nisn);
            $usedPeriod = $fallbackPeriod;
            $semesterId = $fallbackPeriod->semester_id;
        }

        if (empty($response['data'])) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa dengan NISN tersebut tidak ditemukan di AConnect. Pastikan NISN benar dan siswa terdaftar di sekolah ini.',
            ], 404);
        }

        $student = $response['data'];

        return response()->json([
            'success' => true,
            'data' => [
                'nisn' => $student['nisn'],
                'student_name' => $student['nm_siswa'],
                'class_name' => $student['nm_kelas'] ?? null,
                'semester_id' => $semesterId,
                'semester_name' => $usedPeriod?->name ?? $semesterId,
                'is_fallback' => $usedPeriod?->is_fallback ?? false,
            ],
        ]);
    }

    /**
     * Daftar opsi tingkat prestasi.
     *
     * @return array<string, string>
     */
    private function achievementLevels(): array
    {
        return [
            'SEKOLAH' => 'Tingkat Sekolah',
            'KECAMATAN' => 'Tingkat Kecamatan',
            'KOTA' => 'Tingkat Kota / Kabupaten',
            'PROVINSI' => 'Tingkat Provinsi',
            'NASIONAL' => 'Tingkat Nasional',
            'INTERNASIONAL' => 'Tingkat Internasional',
        ];
    }

    /**
     * Daftar opsi peringkat / juara.
     *
     * @return list<string>
     */
    private function rankingOptions(): array
    {
        return [
            'Juara 1',
            'Juara 2',
            'Juara 3',
            'Harapan 1',
            'Harapan 2',
            'Harapan 3',
            'Medali Emas',
            'Medali Perak',
            'Medali Perunggu',
            'Peserta Terbaik',
            'Finalis',
            'Penghargaan Khusus',
        ];
    }
}
