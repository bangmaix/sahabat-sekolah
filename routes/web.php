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
use App\Mail\TestQueueMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// ==========================================
// Global Helper Functions
// ==========================================
if (! function_exists('notifyCounselor')) {
    function notifyCounselor(string $type, string $title, string $message, ?string $caseNumber = null, string $priority = 'NORMAL'): void
    {
        DB::table('notifications')->insert([
            'recipient_name' => session('user_name', 'Guru BK'), 'type' => $type, 'title' => $title,
            'message' => $message, 'priority' => $priority, 'case_number' => $caseNumber,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

if (! function_exists('auditAction')) {
    function auditAction(string $action, string $resourceType, string|int|null $resourceId = null, ?array $newValues = null, ?array $oldValues = null, bool $anonymous = false): void
    {
        $ip = $anonymous ? null : request()->ip();
        $userAgent = $anonymous ? null : request()->userAgent();

        DB::table('audit_logs')->insert([
            'user_id' => $anonymous ? null : session('user_id'),
            'user_name' => $anonymous ? 'Pelapor Anonim' : session('user_name'),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId === null ? null : (string) $resourceId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

if (! function_exists('scopedCase')) {
    function scopedCase(string $caseNumber): object
    {
        $query = DB::table('cases')->where('case_number', $caseNumber);
        if (session('school_npsn')) {
            $query->where('school_npsn', session('school_npsn'));
        }

        return $query->firstOrFail();
    }
}

// ==========================================
// Public Routes
// ==========================================
Route::get('/', [PageController::class, 'landing'])->name('landing');

// System Health & API
Route::get('/health/details', [SystemController::class, 'healthDetails'])->name('health.details');
Route::get('/api-test', [SystemController::class, 'apiTest'])->name('api-test');
Route::get('/test-email', function (Request $request): JsonResponse {
    $recipient = (string) ($request->query('to') ?: (config('mail.from.address_test') ?: config('mail.from.address')));
    $host = (string) ($request->query('host') ?: config('mail.mailers.smtp.host'));
    $port = (int) ($request->query('port') ?: config('mail.mailers.smtp.port', 465));
    $scheme = $request->has('scheme') ? $request->query('scheme') : config('mail.mailers.smtp.scheme', 'smtps');
    $timeout = (int) ($request->query('timeout') ?: max((int) config('mail.mailers.smtp.timeout', 30), 30));

    $runDiagnostics = $request->boolean('diagnostics');
    $diagnostics = [];
    $totalStart = microtime(true);

    if ($runDiagnostics) {
        $ip = gethostbyname($host);
        $diagnostics['dns_resolved_ip'] = $ip !== $host ? $ip : 'Gagal resolve DNS';

        $checkPort = function (string $targetHost, int $targetPort): array {
            $start = microtime(true);
            $errno = 0;
            $errstr = '';
            $fp = @fsockopen($targetHost, $targetPort, $errno, $errstr, 2);
            $duration = round((microtime(true) - $start) * 1000, 2);

            if (is_resource($fp)) {
                fclose($fp);

                return ['status' => 'connected', 'latency_ms' => $duration];
            }

            return ['status' => 'failed', 'latency_ms' => $duration, 'error' => $errstr ?: 'Connection failed'];
        };

        $diagnostics['test_port_587'] = $checkPort($host, 587);
        $diagnostics['test_port_465'] = $checkPort($host, 465);
    }

    // Apply config overrides if query params passed
    Config::set('mail.mailers.smtp.host', $host);
    Config::set('mail.mailers.smtp.port', $port);
    Config::set('mail.mailers.smtp.scheme', $scheme === 'null' || $scheme === '' ? null : $scheme);
    Config::set('mail.mailers.smtp.timeout', $timeout);
    Mail::purge('smtp');

    if ($request->boolean('queue')) {
        Mail::to($recipient)->queue(new TestQueueMail(
            'Email ini dikirim melalui antrean (Laravel Queue) pada '.now()->toDateTimeString().'.'
        ));

        $workerOutput = null;

        if ($request->boolean('process') || $request->boolean('work')) {
            Artisan::call('queue:work', [
                '--once' => true,
                '--stop-when-empty' => true,
            ]);
            $workerOutput = trim(Artisan::output());
        }

        $pendingJobs = DB::table('jobs')->count();

        return response()->json([
            'status' => 'queued',
            'message' => "Email pengujian berhasil dimasukkan ke dalam antrean (queue) untuk: {$recipient}",
            'queue_driver' => config('queue.default'),
            'pending_jobs_count' => $pendingJobs,
            'processed_immediately' => $workerOutput !== null,
            'worker_output' => $workerOutput ?: 'Tugas berada di tabel jobs database. Jalankan "php artisan queue:work" di terminal lokal Anda, atau tambahkan parameter &process=1 pada URL ini untuk memprosesnya langsung.',
            'retry_policy' => [
                'tries' => 5,
                'backoff_seconds' => 60,
                'note' => 'Jika SMTP menolak (denied) atau gagal, worker akan menunggu jeda 60 detik sebelum mencoba lagi.',
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    try {
        $mailStart = microtime(true);

        Mail::raw(
            'Halo, ini adalah email uji coba untuk memverifikasi koneksi SMTP dari '.config('app.name').' pada '.now()->toDateTimeString().'.',
            function ($message) use ($recipient): void {
                $message->to($recipient)
                    ->subject('Test Pengiriman Email SMTP - '.config('app.name'));
            }
        );

        $mailDuration = round(microtime(true) - $mailStart, 2);
        $totalDuration = round(microtime(true) - $totalStart, 2);

        return response()->json([
            'status' => 'success',
            'message' => "Email pengujian berhasil dikirim ke: {$recipient}",
            'duration' => [
                'smtp_send_seconds' => $mailDuration,
                'total_request_seconds' => $totalDuration,
            ],
            'active_config' => [
                'mailer' => config('mail.default'),
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'timeout' => $timeout,
                'from' => config('mail.from.address'),
            ],
            'diagnostics' => $diagnostics ?: 'Soket diagnostik dilewati untuk mempercepat respons. Tambahkan ?diagnostics=1 jika ingin melihatnya.',
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (Throwable $e) {
        $totalDuration = round(microtime(true) - $totalStart, 2);

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengirim email: '.$e->getMessage(),
            'error_type' => get_class($e),
            'total_request_seconds' => $totalDuration,
            'active_config' => [
                'mailer' => config('mail.default'),
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'timeout' => $timeout,
                'from' => config('mail.from.address'),
            ],
            'diagnostics' => $diagnostics,
        ], 500);
    }
})->name('test.email');

// Reports (Public)
Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('reports.store');
Route::get('/reports/receipt', [ReportController::class, 'receipt'])->name('reports.receipt');
Route::get('/track', [ReportController::class, 'track'])->name('reports.track');
Route::get('/reports/track', fn () => redirect()->route('reports.track'));
Route::post('/track/message', [AnonymousController::class, 'sendMessage'])
    ->middleware('throttle:5,1')
    ->name('reports.track.message');

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

    // Password Reset Flow
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');

    // Security incident: report unauthorized password change (signed URL)
    Route::get('/security/unauthorized-password-change/{id}', [AuthController::class, 'reportUnauthorizedPasswordChange'])
        ->name('security.report-unauthorized');
});

// ==========================================
// Authenticated Routes
// ==========================================
Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,1')->name('password.change');

    Route::get('/dashboard', [PageController::class, 'dashboard'])
        ->middleware(['role:COUNSELOR,PRINCIPAL', 'permission:REPORT_VIEW'])
        ->name('dashboard');
    Route::get('/principal', [PageController::class, 'principal'])
        ->middleware(['role:PRINCIPAL', 'permission:MONITORING_VIEW'])
        ->name('principal.index');

    // ==========================================
    // Admin & City Admin
    // ==========================================
    Route::prefix('admin')->middleware(['role:ADMIN,CITY_ADMIN', 'permission:USER_MANAGE'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/schools', [AdminController::class, 'schools'])->name('admin.schools');

        // User Management
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::match(['post', 'put'], '/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('admin.users.reset-password');
        Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.users.delete');

        // SLA Configuration
        Route::get('/sla-configurations', [AdminController::class, 'slaConfigurations'])->name('admin.sla-configurations');
        Route::post('/sla-configurations/{id}', [AdminController::class, 'updateSlaConfiguration'])->name('admin.sla-configurations.update');
    });

    // Admin Master Data
    Route::prefix('admin/master-data')->middleware(['role:ADMIN,CITY_ADMIN', 'permission:MASTER_DATA_MANAGE'])->group(function () {
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
    Route::middleware(['role:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN', 'permission:NOTIFICATION_VIEW'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-selected', [NotificationController::class, 'readSelected'])->name('notifications.read-selected');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::match(['get', 'post'], '/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });

    // ==========================================
    // Statistics
    // ==========================================
    Route::middleware(['role:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN', 'permission:STATISTICS_VIEW'])->group(function () {
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
    });

    // ==========================================
    // Cases
    // ==========================================
    Route::prefix('cases')->group(function () {
        // View
        Route::get('/{caseNumber}', [CaseController::class, 'show'])
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN', 'permission:CASE_VIEW'])
            ->name('cases.show');

        Route::get('/{caseNumber}/print', [CaseController::class, 'printReport'])
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN,CITY_ADMIN', 'permission:CASE_VIEW'])
            ->name('cases.print');

        // Update endpoints (Counselor only)
        Route::middleware(['role:COUNSELOR', 'permission:CASE_UPDATE'])->group(function () {
            Route::post('/{caseNumber}/parent-involvement', [CaseController::class, 'updateParentInvolvement'])->name('cases.parent-involvement');
            Route::post('/{caseNumber}/participants', [CaseController::class, 'addParticipant'])->name('cases.participants');
            Route::post('/{caseNumber}/status', [CaseController::class, 'updateStatus'])->name('cases.status');
            Route::post('/{caseNumber}/action', [CaseController::class, 'processAction'])->name('cases.action');
        });

        // Reassign Case Handler
        Route::post('/{caseNumber}/reassign', [CaseController::class, 'reassign'])
            ->middleware('role:COUNSELOR,PRINCIPAL')
            ->name('cases.reassign');

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

        // View Index (COUNSELOR + PRINCIPAL + ADMIN)
        Route::get('/', [AchievementController::class, 'index'])
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.index');

        // View Student History by NISN (COUNSELOR + PRINCIPAL + ADMIN)
        Route::get('/student/{nisn}', [AchievementController::class, 'studentProfile'])
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.student');

        // Manage Edit, Update, Destroy (COUNSELOR only)
        Route::middleware(['role:COUNSELOR', 'permission:ACHIEVEMENT_MANAGE'])->group(function () {
            Route::get('/{id}/edit', [AchievementController::class, 'edit'])->whereNumber('id')->name('achievements.edit');
            Route::post('/{id}', [AchievementController::class, 'update'])->whereNumber('id')->name('achievements.update');
            Route::post('/{id}/delete', [AchievementController::class, 'destroy'])->whereNumber('id')->name('achievements.destroy');
        });

        // View Detail & Download Certificate (COUNSELOR + PRINCIPAL + ADMIN)
        Route::get('/{id}', [AchievementController::class, 'show'])
            ->whereNumber('id')
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.show');

        Route::get('/{id}/certificate', [AchievementController::class, 'downloadCertificate'])
            ->whereNumber('id')
            ->middleware(['role:COUNSELOR,PRINCIPAL,ADMIN', 'permission:ACHIEVEMENT_VIEW'])
            ->name('achievements.certificate');
    });
});
