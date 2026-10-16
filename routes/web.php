<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnonymousController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ==========================================
// Global Helper Functions
// ==========================================
if (! function_exists('notifyCounselor')) {
    function notifyCounselor(string $type, string $title, string $message, ?string $caseNumber = null, string $priority = 'NORMAL'): void
    {
        DB::table('notifications')->insert([
            'recipient_name' => 'Bu Ratna Sari', 'type' => $type, 'title' => $title,
            'message' => $message, 'priority' => $priority, 'case_number' => $caseNumber,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

if (! function_exists('auditAction')) {
    function auditAction(string $action, string $resourceType, string|int|null $resourceId = null, ?array $newValues = null, ?array $oldValues = null): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => session('user_id'), 'user_name' => session('user_name'), 'action' => $action,
            'resource_type' => $resourceType, 'resource_id' => $resourceId === null ? null : (string) $resourceId,
            'old_values' => $oldValues ? json_encode($oldValues) : null, 'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

if (! function_exists('scopedCase')) {
    function scopedCase(string $caseNumber): object
    {
        return DB::table('cases')
            ->where('case_number', $caseNumber)
            ->where('school_npsn', session('school_npsn'))
            ->firstOrFail();
    }
}

// ==========================================
// Public Routes
// ==========================================
Route::get('/', [PageController::class, 'landing'])->name('landing');

// System Health & API
Route::get('/health/details', [SystemController::class, 'healthDetails'])->name('health.details');
Route::get('/api-test', [SystemController::class, 'apiTest'])->name('api-test');

// Reports (Public)
Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('reports.store');
Route::get('/reports/receipt', [ReportController::class, 'receipt'])->name('reports.receipt');

// Anonymous Channel
Route::get('/anonymous-channel', [AnonymousController::class, 'channel'])->name('anonymous.channel');
Route::post('/anonymous-channel/message', [AnonymousController::class, 'sendMessage'])
    ->middleware('throttle:5,1')
    ->name('anonymous.message');

// ==========================================
// Guest Routes
// ==========================================
Route::middleware('guest.session')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});

// ==========================================
// Authenticated Routes
// ==========================================
Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [PageController::class, 'dashboard'])
        ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:REPORT_VIEW'])
        ->name('dashboard');
    Route::get('/principal', [PageController::class, 'principal'])
        ->middleware(['role:PRINCIPAL', 'permission:MONITORING_VIEW'])
        ->name('principal.index');

    // ==========================================
    // Admin
    // ==========================================
    Route::prefix('admin')->middleware(['role:ADMIN', 'permission:USER_MANAGE'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/schools', [AdminController::class, 'schools'])->name('admin.schools');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

        // SLA Configuration
        Route::get('/sla-configurations', [AdminController::class, 'slaConfigurations'])->name('admin.sla-configurations');
        Route::post('/sla-configurations/{id}', [AdminController::class, 'updateSlaConfiguration'])->name('admin.sla-configurations.update');
    });

    // Admin Master Data
    Route::prefix('admin/master-data')->middleware(['role:ADMIN', 'permission:MASTER_DATA_MANAGE'])->group(function () {
        Route::get('/', [AdminController::class, 'masterData'])->name('admin.master-data');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
        Route::post('/subcategories', [AdminController::class, 'storeSubcategory'])->name('admin.subcategories.store');

        // Achievement Categories
        Route::post('/achievement-categories', [AdminController::class, 'storeAchievementCategory'])->name('admin.achievement-categories.store');
        Route::post('/achievement-categories/{id}/toggle', [AdminController::class, 'toggleAchievementCategory'])->name('admin.achievement-categories.toggle');

        // Academic Periods
        Route::post('/academic-periods', [AdminController::class, 'storeAcademicPeriod'])->name('admin.academic-periods.store');
        Route::post('/academic-periods/{id}/set-active', [AdminController::class, 'setActivePeriod'])->name('admin.academic-periods.set-active');
        Route::post('/academic-periods/{id}/set-fallback', [AdminController::class, 'setFallbackPeriod'])->name('admin.academic-periods.set-fallback');
    });

    // ==========================================
    // Reports Inbox
    // ==========================================
    Route::get('/reports/inbox', [ReportController::class, 'inbox'])
        ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:REPORT_VIEW'])
        ->name('reports.inbox');

    // ==========================================
    // Notifications
    // ==========================================
    Route::middleware(['role:COUNSELOR,PRINCIPAL', 'permission:NOTIFICATION_VIEW'])->group(function () {
        Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });

    // ==========================================
    // Statistics
    // ==========================================
    Route::middleware(['role:COUNSELOR,PRINCIPAL', 'permission:STATISTICS_VIEW'])->group(function () {
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
    });

    // ==========================================
    // Cases
    // ==========================================
    Route::prefix('cases')->group(function () {
        // View
        Route::get('/{caseNumber}', [CaseController::class, 'show'])
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:CASE_VIEW'])
            ->name('cases.show');

        Route::get('/{caseNumber}/print', [CaseController::class, 'printReport'])
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:CASE_VIEW'])
            ->name('cases.print');

        // Update endpoints (Counselor only)
        Route::middleware(['role:COUNSELOR', 'permission:CASE_UPDATE'])->group(function () {
            Route::post('/{caseNumber}/parent-involvement', [CaseController::class, 'updateParentInvolvement'])->name('cases.parent-involvement');
            Route::post('/{caseNumber}/participants', [CaseController::class, 'addParticipant'])->name('cases.participants');
            Route::post('/{caseNumber}/status', [CaseController::class, 'updateStatus'])->name('cases.status');
            Route::post('/{caseNumber}/action', [CaseController::class, 'processAction'])->name('cases.action');
        });

        // Escalate
        Route::post('/{caseNumber}/escalate', [CaseController::class, 'escalate'])
            ->middleware(['role:COUNSELOR', 'permission:CASE_ESCALATE'])
            ->name('cases.escalate');

        // Notes
        Route::post('/{caseNumber}/notes', [CaseController::class, 'addNote'])
            ->middleware(['role:COUNSELOR', 'permission:CASE_NOTE_CREATE'])
            ->name('cases.notes');

        // Risk Assessment
        Route::post('/{caseNumber}/risk', [CaseController::class, 'assessRisk'])
            ->middleware(['role:COUNSELOR', 'permission:RISK_ASSESS'])
            ->name('cases.risk');

        // Documents
        Route::middleware(['role:COUNSELOR', 'permission:EVIDENCE_UPLOAD'])->group(function () {
            Route::post('/{caseNumber}/documents', [CaseController::class, 'uploadDocument'])->name('cases.documents');
            Route::get('/{caseNumber}/documents/{documentType}/{id}', [CaseController::class, 'downloadDocument'])->name('cases.documents.download');
        });
    });

    // ==========================================
    // Achievements
    // ==========================================
    Route::prefix('achievements')->group(function () {
        // AConnect AJAX lookup (COUNSELOR only)
        Route::get('/fetch-student', [AchievementController::class, 'fetchStudent'])
            ->middleware(['role:COUNSELOR', 'permission:ACHIEVEMENT_MANAGE'])
            ->name('achievements.fetch-student');

        // Manage Create & Store (COUNSELOR only) - must be before /{id} wildcard
        Route::middleware(['role:COUNSELOR', 'permission:ACHIEVEMENT_MANAGE'])->group(function () {
            Route::get('/create', [AchievementController::class, 'create'])->name('achievements.create');
            Route::post('/', [AchievementController::class, 'store'])->name('achievements.store');
        });

        // View Index (COUNSELOR + PRINCIPAL)
        Route::get('/', [AchievementController::class, 'index'])
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.index');

        // View Student History by NISN (COUNSELOR + PRINCIPAL)
        Route::get('/student/{nisn}', [AchievementController::class, 'studentProfile'])
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.student');

        // Manage Edit, Update, Destroy (COUNSELOR only)
        Route::middleware(['role:COUNSELOR', 'permission:ACHIEVEMENT_MANAGE'])->group(function () {
            Route::get('/{id}/edit', [AchievementController::class, 'edit'])->whereNumber('id')->name('achievements.edit');
            Route::post('/{id}', [AchievementController::class, 'update'])->whereNumber('id')->name('achievements.update');
            Route::post('/{id}/delete', [AchievementController::class, 'destroy'])->whereNumber('id')->name('achievements.destroy');
        });

        // View Detail & Download Certificate (COUNSELOR + PRINCIPAL)
        Route::get('/{id}', [AchievementController::class, 'show'])
            ->whereNumber('id')
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.show');

        Route::get('/{id}/certificate', [AchievementController::class, 'downloadCertificate'])
            ->whereNumber('id')
            ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.certificate');
    });
});
