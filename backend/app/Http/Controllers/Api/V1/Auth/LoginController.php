<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Authenticate user and issue token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error(
                'The provided credentials are incorrect.',
                ['email' => ['Invalid email or password']],
                401
            );
        }

        if ($user->trashed()) {
            return ApiResponse::error(
                'This account is scheduled for deletion. Please contact support to restore your account.',
                null,
                403
            );
        }

        if (config('auth.email_verification_required', true) && !$user->hasVerifiedEmail()) {
            return ApiResponse::error(
                'Please verify your email address before logging in.',
                ['email' => ['Email not verified']],
                403
            );
        }

        if (!$request->remember) {
            $user->tokens()->delete();
        }

        $tokenExpiry = $request->remember ? now()->addDays(60) : now()->addDays(1);
        $token = $user->createToken('auth-token', ['*'], $tokenExpiry)->plainTextToken;

        \Log::info('User logged in', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return ApiResponse::success([
            'user' => new UserResource($user->load('profile')),
            'token' => $token,
            'expires_at' => $tokenExpiry->toIso8601String(),
        ], 'Login successful');
    }
}
