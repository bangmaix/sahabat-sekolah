<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', function () {
        $data = request()->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        $user = DB::table('users')->where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) return response()->json(['message' => 'Email atau password tidak sesuai.'], 422);
        $plainToken = Str::random(64);
        DB::table('api_tokens')->insert(['user_id' => $user->id, 'token_hash' => hash('sha256', $plainToken), 'expires_at' => now()->addDays(30), 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['token' => $plainToken, 'user' => ['name' => $user->name, 'role' => $user->role, 'school_npsn' => $user->school_npsn]]);
    })->middleware('throttle:5,1');

    Route::middleware('api.token')->group(function () {
        Route::get('/me', fn (\Illuminate\Http\Request $request) => response()->json(['data' => $request->attributes->get('api_user')]));
        Route::get('/cases', function (\Illuminate\Http\Request $request) {
            $user = $request->attributes->get('api_user');
            $cases = DB::table('cases')->where('school_npsn', $user->school_npsn)->orderByDesc('updated_at')->get();
            return response()->json(['data' => $cases]);
        });
        Route::get('/cases/{caseNumber}', function (\Illuminate\Http\Request $request, string $caseNumber) {
            $user = $request->attributes->get('api_user');
            $case = DB::table('cases')->where('school_npsn', $user->school_npsn)->where('case_number', $caseNumber)->firstOrFail();
            return response()->json(['data' => $case]);
        });
        Route::get('/notifications', function (\Illuminate\Http\Request $request) {
            $user = $request->attributes->get('api_user');
            $recipient = $user->role === 'PRINCIPAL' ? 'Kepala Sekolah' : 'Bu Ratna Sari';
            return response()->json(['data' => DB::table('notifications')->where('recipient_name', $recipient)->latest()->get()]);
        });
    });
});
