<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Profile;
use App\Services\Consent\ConsentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(
        protected ConsentService $consentService
    ) {}

    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'preferred_language' => $request->preferred_language ?? 'en',
                'role' => 'user', // Default role
            ]);

            // Create empty profile
            Profile::create([
                'user_id' => $user->id,
            ]);

            // Capture consent (account creation consent is mandatory)
            if ($request->consent_account) {
                $this->consentService->captureConsent(
                    userId: $user->id,
                    type: 'account',
                    consentText: 'I agree to create an account and accept the terms of service.',
                    version: config('app.terms_version', '1.0'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            // Capture marketing consents (optional)
            if ($request->consent_marketing_email) {
                $this->consentService->captureConsent(
                    userId: $user->id,
                    type: 'marketing_email',
                    consentText: 'I agree to receive marketing emails.',
                    version: config('app.privacy_version', '1.0'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            if ($request->consent_marketing_sms) {
                $this->consentService->captureConsent(
                    userId: $user->id,
                    type: 'marketing_sms',
                    consentText: 'I agree to receive marketing SMS.',
                    version: config('app.privacy_version', '1.0'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            // Capture analytics cookies consent (optional)
            if ($request->consent_analytics_cookies) {
                $this->consentService->captureConsent(
                    userId: $user->id,
                    type: 'analytics_cookies',
                    consentText: 'I agree to analytics cookies.',
                    version: config('app.privacy_version', '1.0'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            // Functional cookies consent (usually required for site functionality)
            if ($request->consent_functional_cookies ?? true) {
                $this->consentService->captureConsent(
                    userId: $user->id,
                    type: 'functional_cookies',
                    consentText: 'I agree to functional cookies.',
                    version: config('app.privacy_version', '1.0'),
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent()
                );
            }

            // Trigger email verification notification
            event(new Registered($user));

            // Create API token
            $token = $user->createToken('auth-token', ['*'], now()->addDays(60))->plainTextToken;

            DB::commit();

            return ApiResponse::created([
                'user' => new UserResource($user->load('profile')),
                'token' => $token,
            ], 'Registration successful. Please verify your email address.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                'Registration failed. Please try again.',
                null,
                500
            );
        }
    }
}
