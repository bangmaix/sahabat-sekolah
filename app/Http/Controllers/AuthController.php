<?php

namespace App\Http\Controllers;

use App\Helpers\AConnect;
use App\Mail\PasswordChangedMail;
use App\Mail\ResetPasswordMail;
use App\Mail\SuspectedBreachAdminAlertMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = DB::table('users')->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withInput()->with('error', 'Email atau password tidak sesuai.');
        }

        if (isset($user->status) && $user->status === 'INACTIVE') {
            return back()->withInput()->with('error', 'Akun Anda dinonaktifkan sementara demi keamanan. Silakan hubungi Administrator.');
        }

        $schoolName = null;
        if ($user->school_npsn) {
            $schoolsMap = (new AConnect)->getSekolahMap();
            $schoolName = $schoolsMap[$user->school_npsn] ?? $user->school_npsn;
        }

        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'school_npsn' => $user->school_npsn,
            'school_name' => $schoolName,
        ]);

        $destination = match ($user->role) {
            'ADMIN', 'CITY_ADMIN' => route('admin.index'),
            'PRINCIPAL' => route('principal.index'),
            default => route('dashboard'),
        };

        return redirect($destination);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the forgot password request form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link email.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
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
                return back()->withInput()->with('error', 'Gagal mengirim email reset password: '.$e->getMessage());
            }
        }

        return back()->with('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi telah dikirimkan ke kotak masuk email Anda.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Process password reset update.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

        if (! $resetRecord) {
            return back()->withInput()->with('error', 'Permintaan reset kata sandi tidak ditemukan atau telah digunakan.');
        }

        // Cek kedaluwarsa 60 menit
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

            return back()->withInput()->with('error', 'Tautan reset kata sandi telah kedaluwarsa (lebih dari 60 menit). Silakan ajukan ulang.');
        }

        if (! Hash::check($data['token'], $resetRecord->token)) {
            return back()->withInput()->with('error', 'Token reset kata sandi tidak valid.');
        }

        $user = DB::table('users')->where('email', $data['email'])->first();
        if (! $user) {
            return back()->withInput()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Perbarui kata sandi
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        // Hapus token reset yang sudah digunakan
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        // Cabut token API aktif pengguna untuk keamanan sesi
        DB::table('api_tokens')->where('user_id', $user->id)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }

    /**
     * User self-service change password.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $userId = session('user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        $user = DB::table('users')->where('id', $userId)->first();
        if (! $user || ! Hash::check($data['current_password'], $user->password)) {
            return back()->with('error', 'Kata sandi saat ini tidak cocok.');
        }

        DB::table('users')->where('id', $userId)->update([
            'password' => Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        // URL pengamanan bertanda tangan digital (Signed URL) berlaku 7 hari
        $unauthorizedUrl = URL::temporarySignedRoute(
            'security.report-unauthorized',
            now()->addDays(7),
            ['id' => $user->id]
        );

        $now = now();
        try {
            Mail::to($user->email)->queue(new PasswordChangedMail([
                'name' => $user->name,
                'email' => $user->email,
                'changed_at' => $now->locale('id')->isoFormat('D MMMM Y, HH:mm'),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->userAgent(),
                'unauthorized_url' => $unauthorizedUrl,
            ], $user->name));
        } catch (\Throwable $e) {
            Log::error('Gagal memasukkan notifikasi email perubahan password ke antrean: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        auditAction('CHANGE_PASSWORD', 'USER', $user->id);

        return back()->with('success', 'Kata sandi berhasil diperbarui. Notifikasi konfirmasi keamanan telah dikirimkan ke email Anda.');
    }

    /**
     * Handle unauthorized password change report from security email.
     */
    public function reportUnauthorizedPasswordChange(Request $request, int $id): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan keamanan tidak valid atau telah kedaluwarsa.');
        }

        $user = DB::table('users')->where('id', $id)->firstOrFail();

        // 1. Otomatis nonaktifkan akun
        DB::table('users')->where('id', $id)->update([
            'status' => 'INACTIVE',
            'updated_at' => now(),
        ]);

        // 2. Putus semua sesi login user ini
        DB::table('sessions')->where('user_id', $id)->delete();
        if (session('user_id') == $id) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // 3. Catat log audit darurat
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'action' => 'SUSPECTED_ACCOUNT_BREACH',
            'resource_type' => 'SECURITY_INCIDENT',
            'resource_id' => (string) $user->id,
            'new_values' => json_encode([
                'reason' => 'Pengguna melaporkan tidak melakukan perubahan kata sandi melalui tautan pengamanan email',
                'status_changed_to' => 'INACTIVE',
                'reported_ip' => $request->ip(),
                'reported_user_agent' => $request->userAgent(),
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Catat notifikasi in-app untuk Admin
        DB::table('notifications')->insert([
            'recipient_name' => 'Administrator',
            'type' => 'SECURITY_ALERT',
            'title' => 'PERINGATAN DARURAT: Dugaan Akun Diretas',
            'message' => "Pengguna {$user->name} ({$user->email}) melaporkan tidak melakukan perubahan kata sandi. Akun telah otomatis dinonaktifkan sistem demi keamanan.",
            'priority' => 'URGENT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Kirim email peringatan darurat ke semua akun ADMIN
        $now = now();
        $admins = DB::table('users')
            ->where('role', 'ADMIN')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->queue(new SuspectedBreachAdminAlertMail([
                    'victim_name' => $user->name,
                    'victim_email' => $user->email,
                    'victim_role' => $user->role,
                    'victim_school' => $user->school_name ?? '-',
                    'reported_at' => $now->locale('id')->isoFormat('D MMMM Y, HH:mm'),
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'user_agent' => $request->userAgent(),
                    'admin_users_url' => route('admin.users'),
                ], $admin->name));
            } catch (\Throwable $e) {
                Log::error('Gagal memasukkan email peringatan keamanan admin ke antrean: '.$e->getMessage(), [
                    'admin_email' => $admin->email,
                    'victim_email' => $user->email,
                ]);
            }
        }

        return view('auth.security_alert_resolved', [
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }
}
