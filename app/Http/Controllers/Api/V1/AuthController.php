<?php
// app/Http/Controllers/Api/V1/AuthController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


class AuthController extends Controller
{
    /**
     * Exchange a valid refresh token for a new access token.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->bearerToken();
        if (!$refreshToken) {
            return $this->error('Refresh token required', 401);
        }

        // Find the token
        $tokenModel = $request->user() ? $request->user()->tokens()->where('name', 'refresh_token')->where('token', hash('sha256', $refreshToken))->first() : null;
        if (!$tokenModel) {
            return $this->error('Invalid refresh token', 401);
        }

        // Check expiry
        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            $tokenModel->delete();
            return $this->error('Refresh token expired', 401);
        }

        $user = $tokenModel->tokenable;
        // Issue new access token (1 day)
        $accessToken = $user->createToken('access_token', [], now()->addDay());

        return $this->success([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Access token refreshed');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Access token: expires in 1 day
        $accessToken = $user->createToken('access_token', [], now()->addDay());

        // Refresh token: expires in 1 week
        $refreshToken = $user->createToken('refresh_token', [], now()->addWeek());

        return $this->success([
            'user' => $user,
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }


    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        // Access token: expires in 1 day
        $accessToken = $user->createToken('access_token', [], now()->addDay());

        // Refresh token: expires in 1 week
        $refreshToken = $user->createToken('refresh_token', [], now()->addWeek());

        return $this->success([
            'user' => $user,
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'User info retrieved');
    }
}
