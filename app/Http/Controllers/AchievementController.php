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
     * Daftar prestasi siswa di sekolah Guru BK.
     */
    public function index(Request $request)
    {
        $schoolNpsn = session('school_npsn');
        $categories = AchievementCategory::active()->orderBy('name')->get();

        $query = DB::table('student_achievements')
            ->join('achievement_categories', 'student_achievements.category_id', '=', 'achievement_categories.id')
            ->where('student_achievements.school_npsn', $schoolNpsn)
            ->select(
                'student_achievements.*',
                'achievement_categories.name as category_name',
                'achievement_categories.code as category_code'
            );

        if ($categoryId = $request->input('category_id')) {
            $query->where('student_achievements.category_id', $categoryId);
        }

        if ($level = $request->input('achievement_level')) {
            $query->where('student_achievements.achievement_level', $level);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('student_achievements.student_name', 'ilike', "%{$search}%")
                    ->orWhere('student_achievements.nisn', 'like', "%{$search}%")
                    ->orWhere('student_achievements.achievement_name', 'ilike', "%{$search}%");
            });
        }

        $achievements = $query->orderByDesc('student_achievements.achievement_date')->get();

        return view('achievements.index', [
            'achievements' => $achievements,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'achievement_level', 'search']),
        ]);
    }

    /**
     * Form tambah prestasi.
     */
    public function create()
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

        return view('achievements.create', [
            'categories' => $categories,
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'fallbackPeriod' => $fallbackPeriod,
            'students' => $students,
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
