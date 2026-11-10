<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequireApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = (string) $request->bearerToken();
        $token = $plainToken ? DB::table('api_tokens')->where('token_hash', hash('sha256', $plainToken))->first() : null;
        if (! $token || ($token->expires_at && now()->greaterThan($token->expires_at))) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user = DB::table('users')->where('id', $token->user_id)->first();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->attributes->set('api_user', $user);

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'school_npsn' => $user->school_npsn,
        ]);

        DB::table('api_tokens')->where('id', $token->id)->update(['last_used_at' => now()]);

        return $next($request);
    }
}
