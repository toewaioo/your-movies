<?php
// app/Http/Middleware/CheckAccessTokenExpiry.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckAccessTokenExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->name === 'access_token') {
                if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                    return response()->json([
                        'message' => 'Access token expired. Please refresh your token.',
                        'error' => 'access_token_expired',
                    ], 401);
                }
            }
        }
        return $next($request);
    }
}
