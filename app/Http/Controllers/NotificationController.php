<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) {
                $q->where('cases.school_npsn', session('school_npsn'))
                    ->orWhereNull('notifications.case_number');
            })
            ->select('notifications.*')
            ->orderBy('notifications.created_at', 'desc')
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function read(Request $request, int $id): RedirectResponse
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if (! $notification) {
            return back();
        }

        if (is_null($notification->read_at)) {
            DB::table('notifications')->where('id', $id)->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($request->isMethod('post')) {
            return back();
        }

        if ($notification->case_number) {
            return redirect()->route('cases.show', $notification->case_number);
        }

        return redirect()->route('dashboard');
    }

    public function readSelected(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['integer'],
        ]);

        $validIds = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) {
                $q->where('cases.school_npsn', session('school_npsn'))
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

            return back()->with('success', $validIds->count().' notifikasi terpilih berhasil ditandai sebagai dibaca.');
        }

        return back()->with('info', 'Tidak ada notifikasi baru yang perlu diperbarui.');
    }

    public function readAll(): RedirectResponse
    {
        $validIds = DB::table('notifications')
            ->leftJoin('cases', 'notifications.case_number', '=', 'cases.case_number')
            ->where(function ($q) {
                $q->where('cases.school_npsn', session('school_npsn'))
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

            return back()->with('success', 'Semua notifikasi ('.$validIds->count().') berhasil ditandai sebagai dibaca.');
        }

        return back()->with('info', 'Semua notifikasi sudah dibaca.');
    }
}
