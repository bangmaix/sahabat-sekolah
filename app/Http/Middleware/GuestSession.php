<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('user_id')) {
            $role = $request->session()->get('user_role');
            $destination = match ($role) {
                'ADMIN' => route('admin.index'),
                'PRINCIPAL' => route('principal.index'),
                default => route('dashboard'),
            };
            return redirect($destination);
        }

        return $next($request);
    }
}
