<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user, scoped to school and ordered by created_at desc.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $notifications = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) use ($schoolNpsn) {
                $q->where('cases.school_npsn', $schoolNpsn)
                    ->orWhereNull('notifications.case_number');
            })
            ->select('notifications.*')
            ->orderBy('notifications.created_at', 'desc')
            ->get();

        $unreadCount = $notifications->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'meta' => [
                'unread_count' => $unreadCount,
                'total_count' => $notifications->count(),
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function read(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $notification = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where('notifications.id', $id)
            ->where(function ($q) use ($schoolNpsn) {
                $q->where('cases.school_npsn', $schoolNpsn)
                    ->orWhereNull('notifications.case_number');
            })
            ->select('notifications.*')
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if (is_null($notification->read_at)) {
            DB::table('notifications')->where('id', $id)->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
            'data' => [
                'id' => $id,
                'read_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Mark multiple selected notifications as read.
     */
    public function readSelected(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $data = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['integer'],
        ]);

        $validIds = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) use ($schoolNpsn) {
                $q->where('cases.school_npsn', $schoolNpsn)
                    ->orWhereNull('notifications.case_number');
            })
            ->whereIn('notifications.id', $data['notification_ids'])
            ->whereNull('notifications.read_at')
            ->pluck('notifications.id');

        if ($validIds->isNotEmpty()) {
            DB::table('notifications')
                ->whereIn('id', $validIds)
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => $validIds->count().' notifikasi berhasil ditandai sebagai dibaca.',
            'data' => [
                'marked_count' => $validIds->count(),
            ],
        ]);
    }

    /**
     * Mark all unread notifications for the school as read.
     */
    public function readAll(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');
        $schoolNpsn = $user->school_npsn;

        $validIds = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) use ($schoolNpsn) {
                $q->where('cases.school_npsn', $schoolNpsn)
                    ->orWhereNull('notifications.case_number');
            })
            ->whereNull('notifications.read_at')
            ->pluck('notifications.id');

        if ($validIds->isNotEmpty()) {
            DB::table('notifications')
                ->whereIn('id', $validIds)
                ->update([
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sebagai dibaca.',
            'data' => [
                'marked_count' => $validIds->count(),
            ],
        ]);
    }
}
