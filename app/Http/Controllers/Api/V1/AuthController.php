<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authenticate mobile user (Counselor / Principal / Staff) and issue API Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak sesuai.',
            ], 422);
        }

        $plainToken = Str::random(64);
        $expiresAt = now()->addDays(30);

        DB::table('api_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'last_used_at' => now(),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $plainToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_npsn' => $user->school_npsn,
                    'school_name' => $user->school_name ?? DB::table('users')->where('school_npsn', $user->school_npsn)->value('school_name'),
                ],
            ],
        ]);
    }

    /**
     * Invalidate the current Bearer token.
     */
    public function logout(Request $request): JsonResponse
    {
        $plainToken = (string) $request->bearerToken();

        if ($plainToken) {
            DB::table('api_tokens')->where('token_hash', hash('sha256', $plainToken))->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('api_user');

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $schoolName = $user->school_name ?? DB::table('users')->where('school_npsn', $user->school_npsn)->value('school_name');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'school_npsn' => $user->school_npsn,
                'school_name' => $schoolName,
            ],
        ]);
    }

    /**
     * Send password reset link email for mobile user.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if ($user) {
            $token = Str::random(64);
            $now = now();

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => $now,
                ]
            );

            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            try {
                Mail::to($user->email)->queue(new ResetPasswordMail([
                    'reset_url' => $resetUrl,
                    'expires_minutes' => 60,
                    'email' => $user->email,
                ], $user->name));
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim email reset password: '.$e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika alamat email terdaftar, tautan reset password telah dikirimkan ke email Anda.',
        ]);
    }

    /**
     * Reset password using token via API.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan reset password tidak ditemukan atau telah digunakan.',
            ], 422);
        }

        // Cek kedaluwarsa 60 menit
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tautan reset kata sandi telah kedaluwarsa (lebih dari 60 menit).',
            ], 422);
        }

        if (! Hash::check($data['token'], $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token reset kata sandi tidak valid.',
            ], 422);
        }

        $user = DB::table('users')->where('email', $data['email'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        DB::table('api_tokens')->where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui. Silakan login kembali dengan kata sandi baru Anda.',
        ]);
    }
}
