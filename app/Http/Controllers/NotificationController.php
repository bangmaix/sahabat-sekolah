<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function read(int $id)
    {
        $notification = DB::table('notifications')->where('id', $id)->first();

        if (!$notification) {
            return back();
        }

        if (is_null($notification->read_at)) {
            DB::table('notifications')->where('id', $id)->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($notification->case_number) {
            return redirect()->route('cases.show', $notification->case_number);
        }

        return redirect()->route('dashboard');
    }
}
