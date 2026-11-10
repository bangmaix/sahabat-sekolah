<?php

use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaseController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PublicReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ==========================================
    // Public Mobile Endpoints (No Token Required)
    // ==========================================
    Route::prefix('public')->group(function () {
        Route::get('/schools', [PublicReportController::class, 'schools']);
        Route::get('/categories', [PublicReportController::class, 'categories']);
        Route::post('/reports', [PublicReportController::class, 'createReport']);
        Route::post('/reports/track', [PublicReportController::class, 'trackReport']);
        Route::get('/anonymous/{token}/messages', [PublicReportController::class, 'anonymousMessages']);
        Route::post('/anonymous/{token}/messages', [PublicReportController::class, 'sendAnonymousMessage']);
    });

    // ==========================================
    // Mobile Authentication Endpoints
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('api.token');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    });

    // ==========================================
    // Protected Mobile Endpoints (Require Bearer Token)
    // ==========================================
    Route::middleware('api.token')->group(function () {
        // User Profile
        Route::get('/me', [AuthController::class, 'me']);

        // Dashboard & Analytics
        Route::get('/dashboard', [DashboardController::class, 'overview']);
        Route::get('/statistics', [DashboardController::class, 'statistics']);

        // Case Management
        Route::prefix('cases')->group(function () {
            Route::get('/', [CaseController::class, 'index']);
            Route::get('/{caseNumber}', [CaseController::class, 'show']);
            Route::post('/{caseNumber}/actions', [CaseController::class, 'processAction']);
            Route::post('/{caseNumber}/notes', [CaseController::class, 'addNote']);
            Route::post('/{caseNumber}/escalate', [CaseController::class, 'escalate']);
            Route::post('/{caseNumber}/parent-involvement', [CaseController::class, 'updateParentInvolvement']);
            Route::post('/{caseNumber}/participants', [CaseController::class, 'addParticipant']);
            Route::post('/{caseNumber}/anonymous-reply', [CaseController::class, 'replyAnonymous']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/read-selected', [NotificationController::class, 'readSelected']);
            Route::post('/read-all', [NotificationController::class, 'readAll']);
            Route::post('/{id}/read', [NotificationController::class, 'read']);
        });

        // Student Achievements & Search
        Route::prefix('achievements')->group(function () {
            Route::get('/', [AchievementController::class, 'index']);
            Route::get('/{id}', [AchievementController::class, 'show']);
            Route::post('/', [AchievementController::class, 'store']);
        });
        Route::get('/students/search', [AchievementController::class, 'searchStudents']);
    });
});
