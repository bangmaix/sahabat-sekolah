<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        $schoolName = null;
        if ($user->school_npsn) {
            $schoolsMap = (new \App\Helpers\AConnect)->getSekolahMap();
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
            'ADMIN' => route('admin.index'),
            'PRINCIPAL' => route('principal.index'),
            default => route('dashboard'),
        };

        return redirect($destination);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
