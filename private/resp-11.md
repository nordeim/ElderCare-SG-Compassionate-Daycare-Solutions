# Day 5: Advanced Features — Continuation (Remaining 11 Files)

Continuing from Controller #7...

---

## Admin Controllers (3 files)

### 8. `backend/app/Http/Controllers/Api/V1/Admin/UserController.php`

```php
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

    /**
     * List all users (admin only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('profile');

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Include soft deleted
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

    /**
     * Get single user (admin only)
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::success(
            new UserResource($user->load(['profile', 'bookings', 'testimonials', 'consents'])),
            'User retrieved successfully'
        );
    }

    /**
     * Update user (admin only)
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
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

    /**
     * Delete user (admin only)
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return ApiResponse::success(null, 'User deleted successfully');
    }

    /**
     * Update own profile
     *
     * @param Request $request
     * @return JsonResponse
     */
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

        // Update user fields
        if ($request->has('name') || $request->has('phone') || $request->has('preferred_language')) {
            $user->update($request->only(['name', 'phone', 'preferred_language']));
        }

        // Update profile fields
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

    /**
     * Get user's consents
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Export user data (PDPA)
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Request account deletion (PDPA)
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Cancel account deletion (within grace period)
     *
     * @param Request $request
     * @return JsonResponse
     */
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
```

---

### 9. `backend/app/Http/Controllers/Api/V1/Admin/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Center;
use App\Models\ContactSubmission;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Get dashboard statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Date range filter (default: last 30 days)
        $startDate = $request->has('start_date') 
            ? \Carbon\Carbon::parse($request->start_date)
            : now()->subDays(30);
        
        $endDate = $request->has('end_date')
            ? \Carbon\Carbon::parse($request->end_date)
            : now();

        $statistics = [
            'users' => $this->getUserStatistics($startDate, $endDate),
            'centers' => $this->getCenterStatistics(),
            'bookings' => $this->getBookingStatistics($startDate, $endDate),
            'testimonials' => $this->getTestimonialStatistics(),
            'contact_submissions' => $this->getContactStatistics($startDate, $endDate),
            'overview' => $this->getOverviewStatistics($startDate, $endDate),
        ];

        return ApiResponse::success($statistics, 'Dashboard statistics retrieved successfully');
    }

    /**
     * Get user statistics
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    protected function getUserStatistics($startDate, $endDate): array
    {
        return [
            'total' => User::count(),
            'new' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'by_role' => User::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
        ];
    }

    /**
     * Get center statistics
     *
     * @return array
     */
    protected function getCenterStatistics(): array
    {
        $centers = Center::select('id', 'capacity', 'current_occupancy', 'status')
            ->get();

        return [
            'total' => $centers->count(),
            'published' => $centers->where('status', 'published')->count(),
            'draft' => $centers->where('status', 'draft')->count(),
            'total_capacity' => $centers->sum('capacity'),
            'total_occupancy' => $centers->sum('current_occupancy'),
            'average_occupancy_rate' => $centers->count() > 0
                ? round(($centers->sum('current_occupancy') / $centers->sum('capacity')) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get booking statistics
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    protected function getBookingStatistics($startDate, $endDate): array
    {
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])->get();
        $upcomingBookings = Booking::where('booking_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return [
            'total' => Booking::count(),
            'new' => $bookings->count(),
            'upcoming' => $upcomingBookings,
            'by_status' => Booking::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_type' => Booking::selectRaw('booking_type, COUNT(*) as count')
                ->groupBy('booking_type')
                ->pluck('count', 'booking_type')
                ->toArray(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];
    }

    /**
     * Get testimonial statistics
     *
     * @return array
     */
    protected function getTestimonialStatistics(): array
    {
        $approved = Testimonial::where('status', 'approved')->get();

        return [
            'total' => Testimonial::count(),
            'pending' => Testimonial::where('status', 'pending')->count(),
            'approved' => $approved->count(),
            'rejected' => Testimonial::where('status', 'rejected')->count(),
            'spam' => Testimonial::where('status', 'spam')->count(),
            'average_rating' => $approved->count() > 0 
                ? round($approved->avg('rating'), 2)
                : null,
            'rating_distribution' => Testimonial::where('status', 'approved')
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];
    }

    /**
     * Get contact submission statistics
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    protected function getContactStatistics($startDate, $endDate): array
    {
        return [
            'total' => ContactSubmission::count(),
            'new' => ContactSubmission::whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_status' => ContactSubmission::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    /**
     * Get overview statistics
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    protected function getOverviewStatistics($startDate, $endDate): array
    {
        return [
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate),
            ],
            'recent_activity' => [
                'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_testimonials' => Testimonial::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_contacts' => ContactSubmission::whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
        ];
    }

    /**
     * Calculate booking completion rate
     *
     * @return float
     */
    protected function calculateCompletionRate(): float
    {
        $totalBookings = Booking::whereIn('status', ['completed', 'cancelled', 'no_show'])->count();
        
        if ($totalBookings === 0) {
            return 0;
        }

        $completedBookings = Booking::where('status', 'completed')->count();

        return round(($completedBookings / $totalBookings) * 100, 2);
    }
}
```

---

### 10. `backend/app/Http/Controllers/Api/V1/Admin/ModerationController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactSubmissionResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContactSubmission;
use App\Models\Testimonial;
use App\Services\Contact\ContactService;
use App\Services\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService,
        protected ContactService $contactService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Get pending testimonials
     *
     * @return JsonResponse
     */
    public function pendingTestimonials(): JsonResponse
    {
        $testimonials = $this->testimonialService->getModerationQueue('pending');

        return ApiResponse::paginated(
            $testimonials,
            TestimonialResource::class,
            'Pending testimonials retrieved successfully'
        );
    }

    /**
     * Approve testimonial
     *
     * @param Testimonial $testimonial
     * @param Request $request
     * @return JsonResponse
     */
    public function approveTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        try {
            $approved = $this->testimonialService->approve(
                $testimonial->id,
                $request->user()->id
            );

            return ApiResponse::success(
                new TestimonialResource($approved),
                'Testimonial approved successfully'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Reject testimonial
     *
     * @param Testimonial $testimonial
     * @param Request $request
     * @return JsonResponse
     */
    public function rejectTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $rejected = $this->testimonialService->reject(
                $testimonial->id,
                $request->user()->id,
                $request->reason
            );

            return ApiResponse::success(
                new TestimonialResource($rejected),
                'Testimonial rejected'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Mark testimonial as spam
     *
     * @param Testimonial $testimonial
     * @param Request $request
     * @return JsonResponse
     */
    public function spamTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        $spam = $this->testimonialService->markAsSpam(
            $testimonial->id,
            $request->user()->id
        );

        return ApiResponse::success(
            new TestimonialResource($spam),
            'Testimonial marked as spam'
        );
    }

    /**
     * Get contact submissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function contactSubmissions(Request $request): JsonResponse
    {
        $query = ContactSubmission::with(['user', 'center']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return ApiResponse::paginated(
            $submissions,
            ContactSubmissionResource::class,
            'Contact submissions retrieved successfully'
        );
    }

    /**
     * Update contact submission status
     *
     * @param ContactSubmission $submission
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSubmissionStatus(ContactSubmission $submission, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved,spam'],
        ]);

        $updated = $this->contactService->updateStatus(
            $submission->id,
            $request->status
        );

        return ApiResponse::success(
            new ContactSubmissionResource($updated),
            'Submission status updated successfully'
        );
    }

    /**
     * Get moderation statistics
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'testimonials' => [
                'pending' => Testimonial::where('status', 'pending')->count(),
                'total_today' => Testimonial::whereDate('created_at', today())->count(),
            ],
            'contact_submissions' => [
                'new' => ContactSubmission::where('status', 'new')->count(),
                'in_progress' => ContactSubmission::where('status', 'in_progress')->count(),
                'total_today' => ContactSubmission::whereDate('created_at', today())->count(),
            ],
        ];

        return ApiResponse::success($stats, 'Moderation statistics retrieved');
    }
}
```

---

## Request Validators (3 files)

### 11. `backend/app/Http/Requests/Testimonial/StoreTestimonialRequest.php`

```php
<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:20', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your testimonial',
            'content.required' => 'Please share your experience',
            'content.min' => 'Your testimonial must be at least 20 characters',
            'rating.required' => 'Please provide a rating',
            'rating.min' => 'Rating must be between 1 and 5',
            'rating.max' => 'Rating must be between 1 and 5',
        ];
    }
}
```

---

### 12. `backend/app/Http/Requests/Media/UploadMediaRequest.php`

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'mediable_type' => ['required', 'string', 'in:App\\Models\\Center,App\\Models\\Service,App\\Models\\Staff,App\\Models\\User'],
            'mediable_id' => ['required', 'integer'],
            'type' => ['required', 'in:image,video,document'],
            'caption' => ['nullable', 'string', 'max:500'],
            'alt_text' => ['required_if:type,image', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload',
            'file.max' => 'File size must not exceed 10MB',
            'alt_text.required_if' => 'Alt text is required for images (accessibility)',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                $type = $this->input('type');

                // Validate MIME type based on type
                $allowedMimes = [
                    'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                    'video' => ['video/mp4', 'video/quicktime'],
                    'document' => ['application/pdf'],
                ];

                if (isset($allowedMimes[$type]) && !in_array($file->getMimeType(), $allowedMimes[$type])) {
                    $validator->errors()->add('file', 'Invalid file type for ' . $type);
                }
            }
        });
    }
}
```

---

### 13. `backend/app/Http/Requests/Translation/StoreTranslationRequest.php`

```php
<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'translatable_type' => ['required', 'string', 'in:App\\Models\\Center,App\\Models\\Service,App\\Models\\FAQ'],
            'translatable_id' => ['required', 'integer'],
            'locale' => ['required', 'in:en,zh,ms,ta'],
            'field' => ['required', 'string', 'max:100'],
            'value' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'translatable_type.in' => 'Invalid model type for translation',
            'locale.in' => 'Locale must be one of: en (English), zh (Mandarin), ms (Malay), ta (Tamil)',
        ];
    }
}
```

---

### 14. `backend/app/Http/Requests/Translation/UpdateTranslationRequest.php`

```php
<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'string'],
        ];
    }
}
```

---

## API Resources (2 files)

### 15. `backend/app/Http/Resources/TestimonialResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'rating' => $this->rating,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // User info (name only for approved testimonials, full for admin)
            'user' => $this->when(
                $this->status === 'approved' || $request->user()?->role === 'admin' || $request->user()?->role === 'super_admin',
                function () use ($request) {
                    if ($request->user()?->role === 'admin' || $request->user()?->role === 'super_admin') {
                        // Full user info for admins
                        return [
                            'id' => $this->user->id,
                            'name' => $this->user->name,
                            'email' => $this->user->email,
                        ];
                    } else {
                        // Only name for public
                        return [
                            'name' => $this->user->name,
                            'initials' => $this->getUserInitials(),
                        ];
                    }
                }
            ),
            
            // Center info
            'center' => $this->when($request->route()->getName() !== 'centers.testimonials.index', function () {
                return [
                    'id' => $this->center->id,
                    'name' => $this->center->name,
                    'slug' => $this->center->slug,
                ];
            }),
            
            // Moderation info (admin only)
            'moderation' => $this->when(
                in_array($request->user()?->role, ['admin', 'super_admin']),
                function () {
                    return [
                        'moderated_by' => $this->moderatedBy ? [
                            'id' => $this->moderatedBy->id,
                            'name' => $this->moderatedBy->name,
                        ] : null,
                        'moderated_at' => $this->moderated_at?->toIso8601String(),
                        'moderation_notes' => $this->moderation_notes,
                    ];
                }
            ),
        ];
    }

    /**
     * Get user initials for anonymization
     *
     * @return string
     */
    protected function getUserInitials(): string
    {
        $nameParts = explode(' ', $this->user->name);
        
        if (count($nameParts) >= 2) {
            return strtoupper($nameParts[0][0] . $nameParts[1][0]);
        }
        
        return strtoupper(substr($this->user->name, 0, 2));
    }
}
```

---

### 16. `backend/app/Http/Resources/MediaResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_formatted' => $this->formatFileSize($this->size),
            'duration' => $this->when($this->type === 'video', $this->duration),
            'caption' => $this->caption,
            'alt_text' => $this->alt_text,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Cloudflare Stream info (for videos)
            'cloudflare_stream_id' => $this->when($this->type === 'video', $this->cloudflare_stream_id),
        ];
    }

    /**
     * Format file size to human-readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }
        
        return round($bytes, 2) . ' ' . $units[$index];
    }
}
```

---

### 17. `backend/app/Http/Resources/TranslationResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'field' => $this->field,
            'value' => $this->value,
            'translation_status' => $this->translation_status,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Translator info
            'translated_by' => $this->when($this->translator, function () {
                return [
                    'id' => $this->translator->id,
                    'name' => $this->translator->name,
                ];
            }),
            
            // Reviewer info
            'reviewed_by' => $this->when($this->reviewer, function () {
                return [
                    'id' => $this->reviewer->id,
                    'name' => $this->reviewer->name,
                ];
            }),
            
            // Original content (for reference)
            'translatable' => $this->when($request->route()->getName() === 'translations.show', function () {
                return [
                    'type' => class_basename($this->translatable_type),
                    'id' => $this->translatable_id,
                ];
            }),
        ];
    }
}
```

---

## Queue Jobs (1 file)

### 18. `backend/app/Jobs/OptimizeImageJob.php`

```php
<?php

namespace App\Jobs;

use App\Services\Media\ImageOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $mediaId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageOptimizationService $optimizationService): void
    {
        Log::info('Starting image optimization', ['media_id' => $this->mediaId]);

        try {
            $optimizationService->optimize($this->mediaId);

            Log::info('Image optimization completed', ['media_id' => $this->mediaId]);

        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'media_id' => $this->mediaId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Image optimization job failed permanently', [
            'media_id' => $this->mediaId,
            'error' => $exception->getMessage(),
        ]);

        // TODO: Send alert to admin
        // TODO: Update media record to indicate optimization failed
    }
}
```

---

## Policies (1 file)

### 19. `backend/app/Policies/TestimonialPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TestimonialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any testimonials.
     */
    public function viewAny(?User $user): bool
    {
        // Public can view approved testimonials
        return true;
    }

    /**
     * Determine if the user can view the testimonial.
     */
    public function view(?User $user, Testimonial $testimonial): bool
    {
        // Public can view approved testimonials
        if ($testimonial->status === 'approved') {
            return true;
        }

        // Users can view their own testimonials
        if ($user && $user->id === $testimonial->user_id) {
            return true;
        }

        // Admins can view any testimonial
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create testimonials.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create testimonials
        return true;
    }

    /**
     * Determine if the user can update the testimonial.
     */
    public function update(User $user, Testimonial $testimonial): bool
    {
        // Users can update their own pending testimonials
        if ($user->id === $testimonial->user_id && $testimonial->status === 'pending') {
            return true;
        }

        // Admins can update any testimonial
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the testimonial.
     */
    public function delete(User $user, Testimonial $testimonial): bool
    {
        // Users can delete their own pending testimonials
        if ($user->id === $testimonial->user_id && $testimonial->status === 'pending') {
            return true;
        }

        // Admins can delete any testimonial
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can approve testimonials.
     */
    public function approve(User $user, Testimonial $testimonial): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can reject testimonials.
     */
    public function reject(User $user, Testimonial $testimonial): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can mark testimonials as spam.
     */
    public function markAsSpam(User $user, Testimonial $testimonial): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
```

---

## Configuration & Additional Setup

### 20. `backend/app/Providers/AuthServiceProvider.php` (Register Testimonial Policy)

**Add to the `$policies` array**:

```php
use App\Models\Testimonial;
use App\Policies\TestimonialPolicy;

protected $policies = [
    User::class => UserPolicy::class,
    Center::class => CenterPolicy::class,
    Service::class => ServicePolicy::class,
    Booking::class => BookingPolicy::class,
    Testimonial::class => TestimonialPolicy::class,
];
```

---

### 21. `backend/config/filesystems.php` (Configure S3 disk)

**Ensure S3 disk is configured** (should already exist):

```php
'disks' => [
    // ... other disks

    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
    ],

    'private' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
        'bucket' => env('AWS_PRIVATE_BUCKET', env('AWS_BUCKET')),
        'visibility' => 'private',
    ],
],
```

---

### 22. `backend/composer.json` (Add Intervention Image package)

**Note**: This package should be installed via Composer:

```bash
docker-compose exec backend composer require intervention/image
```

**Or add to `composer.json` require section**:

```json
{
    "require": {
        "intervention/image": "^2.7"
    }
}
```

---

### 23. `backend/.env.example` (Add S3 and Image configuration)

**Add these lines**:

```env
# AWS S3 Configuration
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=
AWS_PRIVATE_BUCKET=
AWS_URL=

# Image Optimization
IMAGE_OPTIMIZATION_ENABLED=true
IMAGE_MAX_WIDTH=1920
IMAGE_QUALITY=85
```

---

## Validation & Testing Instructions for Day 5

### Complete Day 5 Validation Checklist:

```bash
# ============================================================================
# STEP 1: Install Required Packages
# ============================================================================
docker-compose exec backend composer require intervention/image

# Verify installation
docker-compose exec backend php artisan tinker
>>> class_exists('Intervention\Image\Facades\Image');
# Should return true

# ============================================================================
# STEP 2: Configure S3 (Local Testing with MinIO)
# ============================================================================
# For local development, you can use MinIO (S3-compatible storage)
# Or configure actual AWS S3 credentials in .env

# Update backend/.env with S3 config
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=eldercare-media
AWS_URL=https://your-bucket.s3.ap-southeast-1.amazonaws.com

# ============================================================================
# STEP 3: Test Testimonial Submission (User)
# ============================================================================

# Login as regular user
export USER_TOKEN="your_user_token"

# Get center ID
curl http://localhost:8000/api/v1/centers | jq '.data[0].id'
export CENTER_ID=1

# Submit testimonial
curl -X POST http://localhost:8000/api/v1/centers/$CENTER_ID/testimonials \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Excellent Care and Service",
    "content": "My mother has been attending this center for the past 6 months and we could not be happier with the care she receives. The staff are professional, caring, and truly dedicated to the wellbeing of the elderly in their care.",
    "rating": 5
  }'

# Expected Response (201):
# {
#   "success": true,
#   "message": "Thank you for your testimonial! It will be reviewed before publication.",
#   "data": { "id": 1, "title": "...", "status": "pending", ... }
# }

# Verify testimonial created
docker-compose exec backend php artisan tinker
>>> \App\Models\Testimonial::latest()->first()->toArray();
# Should show status 'pending'

# ============================================================================
# STEP 4: Test Testimonial Moderation (Admin)
# ============================================================================

# Login as admin
export ADMIN_TOKEN="your_admin_token"

# Get pending testimonials
curl -X GET http://localhost:8000/api/v1/admin/testimonials/pending \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "data": [ { "id": 1, "status": "pending", ... } ]
# }

# Approve testimonial
curl -X POST http://localhost:8000/api/v1/admin/testimonials/1/approve \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "message": "Testimonial approved successfully",
#   "data": { "id": 1, "status": "approved", ... }
# }

# Verify now visible in public listing
curl http://localhost:8000/api/v1/centers/$CENTER_ID/testimonials

# Expected Response (200):
# {
#   "data": [ { "id": 1, "title": "Excellent Care and Service", ... } ]
# }

# Test rejection workflow
# Create another testimonial first, then:
curl -X POST http://localhost:8000/api/v1/admin/testimonials/2/reject \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Content does not meet our community guidelines. Please revise and resubmit."
  }'

# ============================================================================
# STEP 5: Test Media Upload (Admin)
# ============================================================================

# Create a test image (or use existing)
# For testing, you can download a sample image:
curl -o test-image.jpg https://picsum.photos/1200/800

# Upload image
curl -X POST http://localhost:8000/api/v1/admin/media \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -F "file=@test-image.jpg" \
  -F "mediable_type=App\\Models\\Center" \
  -F "mediable_id=1" \
  -F "type=image" \
  -F "alt_text=Front entrance of the care center" \
  -F "caption=Main building exterior"

# Expected Response (201):
# {
#   "success": true,
#   "message": "Media uploaded successfully. Optimization is in progress.",
#   "data": { "id": 1, "url": "https://...", "type": "image", ... }
# }

# ============================================================================
# STEP 6: Verify Image Optimization Job Queued
# ============================================================================

docker-compose exec backend php artisan tinker
>>> \App\Models\Job::where('queue', 'default')->latest()->first();
# Should contain OptimizeImageJob

# Process the queue
docker-compose exec backend php artisan queue:work --once

# Check media record updated with thumbnail
>>> $media = \App\Models\Media::find(1);
>>> $media->thumbnail_url;
# Should not be null after optimization

# ============================================================================
# STEP 7: Test Media Management
# ============================================================================

# Update media metadata
curl -X PUT http://localhost:8000/api/v1/admin/media/1 \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "alt_text": "Updated alt text for accessibility",
    "caption": "Updated caption",
    "display_order": 1
  }'

# Reorder media
curl -X POST http://localhost:8000/api/v1/admin/media/reorder \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "mediable_type": "App\\\\Models\\\\Center",
    "mediable_id": 1,
    "order": [3, 1, 2]
  }'

# Delete media
curl -X DELETE http://localhost:8000/api/v1/admin/media/1 \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response (200):
# { "success": true, "message": "Media deleted successfully" }

# ============================================================================
# STEP 8: Test Translation Management (Admin)
# ============================================================================

# Create translation for center name
curl -X POST http://localhost:8000/api/v1/admin/translations \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "translatable_type": "App\\\\Models\\\\Center",
    "translatable_id": 1,
    "locale": "zh",
    "field": "name",
    "value": "金色年华护理中心"
  }'

# Expected Response (201):
# {
#   "success": true,
#   "message": "Translation created successfully",
#   "data": { "id": 1, "locale": "zh", "field": "name", "translation_status": "draft", ... }
# }

# Mark as translated
curl -X POST http://localhost:8000/api/v1/admin/translations/1/mark-translated \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Mark as reviewed
curl -X POST http://localhost:8000/api/v1/admin/translations/1/mark-reviewed \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Publish translation
curl -X POST http://localhost:8000/api/v1/admin/translations/1/publish \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Get translation coverage
curl -X GET "http://localhost:8000/api/v1/admin/translations/coverage?translatable_type=App\\\\Models\\\\Center&translatable_id=1" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response:
# {
#   "success": true,
#   "data": {
#     "en": { "total_fields": 3, "translated_fields": 3, "percentage": 100 },
#     "zh": { "total_fields": 3, "translated_fields": 1, "percentage": 33.33 },
#     ...
#   }
# }

# ============================================================================
# STEP 9: Test Admin Dashboard
# ============================================================================

# Get dashboard statistics
curl -X GET http://localhost:8000/api/v1/admin/dashboard \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "data": {
#     "users": { "total": 10, "new": 3, "verified": 8, ... },
#     "centers": { "total": 5, "published": 4, ... },
#     "bookings": { "total": 25, "upcoming": 10, ... },
#     "testimonials": { "pending": 2, "approved": 15, ... },
#     ...
#   }
# }

# Get dashboard with date range
curl -X GET "http://localhost:8000/api/v1/admin/dashboard?start_date=2025-01-01&end_date=2025-01-31" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# ============================================================================
# STEP 10: Test PDPA User Data Export
# ============================================================================

# Request data export (as user)
curl -X POST http://localhost:8000/api/v1/user/export-data \
  -H "Authorization: Bearer $USER_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "message": "Your data export is ready. The download link is valid for 1 hour.",
#   "data": {
#     "download_url": "https://s3.../exports/user-data-export-1-20250115143020.json?X-Amz-...",
#     "expires_at": "2025-01-15T15:30:20+00:00"
#   }
# }

# Download the export file
curl "DOWNLOAD_URL_FROM_ABOVE" -o my-data.json

# Verify JSON structure
cat my-data.json | jq '.user.email'
# Should show your email

# ============================================================================
# STEP 11: Test Account Deletion Request
# ============================================================================

# Request account deletion (as user)
curl -X POST http://localhost:8000/api/v1/user/request-deletion \
  -H "Authorization: Bearer $USER_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "message": "Your account deletion has been scheduled. You have 30 days to cancel if you change your mind.",
#   "data": {
#     "deletion_scheduled_at": "2025-02-14T10:00:00+00:00",
#     "grace_period_days": 30
#   }
# }

# Verify user soft deleted
docker-compose exec backend php artisan tinker
>>> $user = \App\Models\User::withTrashed()->find(1);
>>> $user->deleted_at;
# Should not be null

# Cancel deletion
curl -X POST http://localhost:8000/api/v1/user/cancel-deletion \
  -H "Authorization: Bearer $USER_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "message": "Account deletion cancelled. Your account has been restored.",
#   "data": { "id": 1, "name": "...", "deleted_at": null }
# }

# ============================================================================
# STEP 12: Run Automated Tests
# ============================================================================

# Run all Day 5 tests
docker-compose exec backend php artisan test --filter=Testimonial
docker-compose exec backend php artisan test --filter=Media
docker-compose exec backend php artisan test --filter=Translation
docker-compose exec backend php artisan test --filter=Admin

# Run with coverage
docker-compose exec backend php artisan test --coverage --min=90

# ============================================================================
# STEP 13: Test Testimonial Duplicate Prevention
# ============================================================================

# Try to submit another testimonial for same center
curl -X POST http://localhost:8000/api/v1/centers/$CENTER_ID/testimonials \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Another testimonial",
    "content": "This should fail because I already submitted one",
    "rating": 5
  }'

# Expected Response (400):
# {
#   "success": false,
#   "message": "You have already submitted a testimonial for this center"
# }

# ============================================================================
# STEP 14: Test Moderation Statistics
# ============================================================================

curl -X GET http://localhost:8000/api/v1/admin/moderation/statistics \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# Expected Response (200):
# {
#   "success": true,
#   "data": {
#     "testimonials": { "pending": 1, "total_today": 2 },
#     "contact_submissions": { "new": 3, "in_progress": 1, "total_today": 5 }
#   }
# }

# ============================================================================
# STEP 15: Check Audit Logs
# ============================================================================

docker-compose exec backend php artisan tinker

# View audit logs for testimonials
>>> \App\Models\AuditLog::where('auditable_type', 'App\\Models\\Testimonial')
...   ->latest()
...   ->take(5)
...   ->get()
...   ->pluck('action', 'auditable_id');

# Should show 'created', 'updated' for testimonial moderation

# View audit logs for media
>>> \App\Models\AuditLog::where('auditable_type', 'App\\Models\\Media')->count();
# Should be > 0

# ============================================================================
# FINAL VERIFICATION SUMMARY
# ============================================================================

echo "Day 5 Validation Complete! ✅"
echo ""
echo "Files Created: 18"
echo "Services: 4 (TestimonialService, MediaService, ImageOptimizationService, TranslationService)"
echo "Controllers: 6 (Testimonial, Media, Translation, Admin User/Dashboard/Moderation)"
echo "Requests: 3 (StoreTestimonial, UploadMedia, Store/UpdateTranslation)"
echo "Resources: 3 (Testimonial, Media, Translation)"
echo "Jobs: 1 (OptimizeImageJob)"
echo "Policies: 1 (TestimonialPolicy)"
echo ""
echo "Run comprehensive tests:"
echo "docker-compose exec backend php artisan test --coverage"
```

---

## Summary of Day 5 Completion

### ✅ All 18 Files Created:

**Services (4)**:
1. ✅ TestimonialService.php (moderation workflow)
2. ✅ MediaService.php (S3 upload management)
3. ✅ ImageOptimizationService.php (WebP conversion, thumbnails)
4. ✅ TranslationService.php (multilingual workflow)

**Controllers (6)**:
5. ✅ TestimonialController.php (public testimonials)
6. ✅ MediaController.php (admin media management)
7. ✅ TranslationController.php (admin translation management)
8. ✅ Admin/UserController.php (user management + PDPA)
9. ✅ Admin/DashboardController.php (statistics)
10. ✅ Admin/ModerationController.php (content moderation)

**Requests (3)**:
11. ✅ StoreTestimonialRequest.php
12. ✅ UploadMediaRequest.php
13. ✅ StoreTranslationRequest.php + UpdateTranslationRequest.php

**Resources (3)**:
14. ✅ TestimonialResource.php
15. ✅ MediaResource.php
16. ✅ TranslationResource.php

**Jobs (1)**:
17. ✅ OptimizeImageJob.php

**Policies (1)**:
18. ✅ TestimonialPolicy.php

---

## Features Implemented:

### ✅ Testimonial System
- User submission with rating (1-5 stars)
- Moderation workflow (pending → approved/rejected/spam)
- Duplicate prevention (one per center per user)
- Average rating calculation
- Rating distribution statistics

### ✅ Media Management
- S3 upload with validation
- Image optimization (compression, WebP conversion)
- Thumbnail generation (300x300)
- Metadata management (alt text, captions)
- Reordering support
- File size validation (10MB max)

### ✅ Translation System
- Multi-language support (en, zh, ms, ta)
- Translation workflow (draft → translated → reviewed → published)
- Coverage tracking per model
- Translator/reviewer attribution

### ✅ Admin Dashboard
- User statistics (total, new, verified, by role)
- Center statistics (capacity, occupancy rates)
- Booking statistics (by status, completion rates)
- Testimonial statistics (pending, approved, rating distribution)
- Contact submission statistics
- Date range filtering

### ✅ PDPA Features
- Data export (JSON with all user data)
- Account deletion with 30-day grace period
- Consent history retrieval
- Audit logging for all operations

---

## 🎯 Progress Summary (Days 1-5 Complete)

**Total Files Implemented**: 106 files
- Day 1 (API Infrastructure): 8 files ✅
- Day 2 (PDPA & Auth): 20 files ✅
- Day 3 (Business Logic): 28 files ✅
- Day 4 (Booking System): 22 files ✅
- Day 5 (Advanced Features): 18 files ✅

**Remaining**: 48 files (Days 6-7)
- Day 6 (Testing): ~35 files (comprehensive test suite)
- Day 7 (API Docs & Final QA): ~13 files

---

## 🎯 Day 5 Complete — Ready for Day 6

**Next Up**: Comprehensive Testing Suite (Day 6)
- Complete unit test coverage for all services
- Feature tests for all endpoints
- Factories for all models
- DemoSeeder for stakeholder presentation
- Integration tests
- Edge case coverage
