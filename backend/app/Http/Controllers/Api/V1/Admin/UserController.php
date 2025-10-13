<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\User\DataExportService;
use App\Services\User\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected DataExportService $exportService,
        protected AccountDeletionService $deletionService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin')->except(['updateProfile', 'getConsents', 'exportData', 'requestDeletion', 'cancelDeletion']);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('profile');

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('with_deleted') && $request->with_deleted === 'true') {
            $query->withTrashed();
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return ApiResponse::paginated(
            $users,
            UserResource::class,
            'Users retrieved successfully'
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::success(
            new UserResource($user->load(['profile', 'bookings', 'testimonials', 'consents'])),
            'User retrieved successfully'
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^\+65[689]\d{7}$/'],
            'role' => ['sometimes', 'in:user,admin,super_admin'],
            'preferred_language' => ['sometimes', 'in:en,zh,ms,ta'],
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'role', 'preferred_language'
        ]));

        return ApiResponse::success(
            new UserResource($user->fresh()),
            'User updated successfully'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return ApiResponse::success(null, 'User deleted successfully');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^\+65[689]\d{7}$/'],
            'preferred_language' => ['sometimes', 'in:en,zh,ms,ta'],
            'profile' => ['sometimes', 'array'],
            'profile.bio' => ['sometimes', 'string', 'max:1000'],
            'profile.birth_date' => ['sometimes', 'date', 'before:today'],
            'profile.address' => ['sometimes', 'string', 'max:500'],
            'profile.city' => ['sometimes', 'string', 'max:100'],
            'profile.postal_code' => ['sometimes', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user();

        if ($request->has('name') || $request->has('phone') || $request->has('preferred_language')) {
            $user->update($request->only(['name', 'phone', 'preferred_language']));
        }

        if ($request->has('profile')) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $request->profile
            );
        }

        return ApiResponse::success(
            new UserResource($user->fresh('profile')),
            'Profile updated successfully'
        );
    }

    public function getConsents(Request $request): JsonResponse
    {
        $consents = $request->user()->consents()
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success(
            $consents,
            'Consents retrieved successfully'
        );
    }

    public function exportData(Request $request): JsonResponse
    {
        try {
            $export = $this->exportService->exportUserData($request->user()->id);

            return ApiResponse::success([
                'download_url' => $export['url'],
                'expires_at' => $export['expires_at'],
            ], 'Your data export is ready. The download link is valid for 1 hour.');
        } catch (\Exception $e) {
            \Log::error('Data export failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                'Data export failed. Please try again or contact support.',
                null,
                500
            );
        }
    }

    public function requestDeletion(Request $request): JsonResponse
    {
        try {
            $scheduledDate = $this->deletionService->requestDeletion($request->user()->id);

            return ApiResponse::success([
                'deletion_scheduled_at' => $scheduledDate->toIso8601String(),
                'grace_period_days' => 30,
            ], 'Your account deletion has been scheduled. You have 30 days to cancel if you change your mind.');
        } catch (\Exception $e) {
            return ApiResponse::error('Deletion request failed', null, 500);
        }
    }

    public function cancelDeletion(Request $request): JsonResponse
    {
        try {
            $user = $this->deletionService->cancelDeletion($request->user()->id);

            return ApiResponse::success(
                new UserResource($user),
                'Account deletion cancelled. Your account has been restored.'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to cancel deletion. Please contact support.',
                null,
                500
            );
        }
    }
}
