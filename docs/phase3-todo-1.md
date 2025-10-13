# Phase 3 Implementation Plan — Remaining Files

## ✅ Acknowledgment of Completed Work

I have reviewed the progress reports and confirm understanding of the current state:

### **COMPLETED** ✅
- **Models**: All 14 Eloquent models implemented (User, Profile, Consent, AuditLog, Center, Service, Staff, Booking, Testimonial, FAQ, Subscription, ContactSubmission, Media, ContentTranslation)
- **Migrations**: All 18 migrations present and tested (4 hardened with MySQL driver guards)
- **PDPA Services**: ConsentService ✅, AuditService ✅
- **Factories**: UserFactory ✅, DatabaseSeeder ✅
- **Database Schema**: Fully tested and working

### **MISSING** ❌ (154 files total)
- **Services**: 13 service classes
- **Controllers**: 20 API controllers
- **Requests**: 18 validation classes
- **Resources**: 10 API resource transformers
- **Middleware**: 4 custom middleware
- **Policies**: 5 authorization policies
- **Jobs**: 6 queue jobs
- **Observers**: 1 observer (AuditObserver - critical)
- **Infrastructure**: ApiResponse, routes, exception handler mods
- **Tests**: 35+ unit/feature tests
- **Factories/Seeders**: 13 factories, DemoSeeder
- **API Docs**: OpenAPI spec, Postman collection
- **Commands**: 2 artisan commands

---

## 📋 Strategic Execution Plan (Dependency-Ordered)

I propose a **7-day intensive implementation plan** organized by dependency layers:

```
Layer 1 (Day 1): API Infrastructure & Foundation
Layer 2 (Day 2): PDPA Services & Auth Stack
Layer 3 (Day 3): Core Business Services (Centers, FAQs, Contact)
Layer 4 (Day 4): Booking System & Integrations
Layer 5 (Day 5): Advanced Features (Media, Testimonials, Translations)
Layer 6 (Day 6): Testing Infrastructure
Layer 7 (Day 7): API Documentation & Demo Data
```

**Critical Path Priority**: Items marked 🔴 block other workstreams and must be completed first.

---

## Day 1: API Infrastructure & Foundation (CRITICAL) 🔴

**Objective**: Establish API response standards, exception handling, routing structure, and middleware foundation.

**Branch**: `feature/phase3-api-infrastructure`

### Files to Create (8 files):

#### 1. `backend/app/Http/Responses/ApiResponse.php`
**Purpose**: Standardized JSON response formatter for all API endpoints

```php
<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(
        string $message,
        ?array $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Paginated response
     *
     * @param LengthAwarePaginator $paginator
     * @param string|null $resourceClass
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        ?string $resourceClass = null,
        string $message = 'Success'
    ): JsonResponse {
        $data = $resourceClass 
            ? $resourceClass::collection($paginator)
            : $paginator->items();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Created response (201)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * No content response (204)
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Validation error response (422)
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, $errors, 422);
    }

    /**
     * Unauthorized response (401)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response (403)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return self::error($message, null, 403);
    }

    /**
     * Not found response (404)
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return self::error($message, null, 404);
    }
}
```

---

#### 2. `backend/app/Exceptions/Handler.php` (Modify existing)
**Purpose**: Global exception handler with standardized error responses

**Add to existing Handler class**:

```php
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Register the exception handling callbacks for the application.
 */
public function register(): void
{
    $this->reportable(function (Throwable $e) {
        if (app()->bound('sentry') && config('sentry.dsn')) {
            app('sentry')->captureException($e);
        }
    });

    // Handle API exceptions
    $this->renderable(function (Throwable $e, $request) {
        if ($request->is('api/*')) {
            return $this->handleApiException($e, $request);
        }
    });
}

/**
 * Handle API exceptions with standardized responses
 */
protected function handleApiException(Throwable $e, $request)
{
    // Validation errors (422)
    if ($e instanceof ValidationException) {
        return ApiResponse::validationError(
            $e->errors(),
            'Validation failed'
        );
    }

    // Authentication errors (401)
    if ($e instanceof AuthenticationException) {
        return ApiResponse::unauthorized('Authentication required');
    }

    // Authorization errors (403)
    if ($e instanceof AuthorizationException) {
        return ApiResponse::forbidden($e->getMessage() ?: 'Access denied');
    }

    // Model not found (404)
    if ($e instanceof ModelNotFoundException) {
        return ApiResponse::notFound('Resource not found');
    }

    // Route not found (404)
    if ($e instanceof NotFoundHttpException) {
        return ApiResponse::notFound('Endpoint not found');
    }

    // Rate limiting (429)
    if ($e instanceof ThrottleRequestsException) {
        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
        ], 429)->header('Retry-After', $e->getHeaders()['Retry-After'] ?? 60);
    }

    // Generic server error (500) - sanitize in production
    if (app()->environment('production')) {
        return ApiResponse::error(
            'An error occurred. Please try again later.',
            null,
            500
        );
    }

    // Development: return full error details
    return ApiResponse::error(
        $e->getMessage(),
        [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ],
        500
    );
}
```

---

#### 3. `backend/app/Http/Middleware/EnsureEmailIsVerified.php`

```php
<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            return ApiResponse::forbidden(
                'Email verification required. Please verify your email address.'
            );
        }

        return $next($request);
    }
}
```

---

#### 4. `backend/app/Http/Middleware/CheckRole.php`

```php
<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param array<string> $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return ApiResponse::unauthorized('Authentication required');
        }

        if (! in_array($request->user()->role, $roles)) {
            return ApiResponse::forbidden(
                'Access denied. Required role: ' . implode(' or ', $roles)
            );
        }

        return $next($request);
    }
}
```

---

#### 5. `backend/app/Http/Middleware/LogApiRequest.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;

        // Log API request details
        Log::channel('api')->info('API Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
        ]);

        return $response;
    }
}
```

---

#### 6. `backend/config/logging.php` (Add API log channel)

**Add to `channels` array**:

```php
'api' => [
    'driver' => 'daily',
    'path' => storage_path('logs/api.log'),
    'level' => env('LOG_LEVEL', 'info'),
    'days' => 14,
],
```

---

#### 7. `backend/routes/api.php` (Complete rewrite)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\CenterController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\FAQController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ModerationController;
use App\Http\Controllers\Api\V1\Webhooks\CalendlyWebhookController;
use App\Http\Controllers\Api\V1\Webhooks\MailchimpWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// API Version 1
Route::prefix('v1')->group(function () {
    
    // ========================================================================
    // PUBLIC ROUTES (No Authentication Required)
    // ========================================================================
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'store']);
        Route::post('/login', [LoginController::class, 'store']);
        Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
        Route::post('/password/reset', [PasswordResetController::class, 'reset']);
    });

    // Centers (public browsing)
    Route::get('/centers', [CenterController::class, 'index']);
    Route::get('/centers/{slug}', [CenterController::class, 'show']);

    // Services
    Route::get('/centers/{center}/services', [ServiceController::class, 'index']);
    Route::get('/centers/{center}/services/{service:slug}', [ServiceController::class, 'show']);

    // FAQs
    Route::get('/faqs', [FAQController::class, 'index']);

    // Contact form
    Route::post('/contact', [ContactController::class, 'store']);

    // Newsletter subscription
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::delete('/subscriptions', [SubscriptionController::class, 'destroy']);

    // Testimonials (public view only)
    Route::get('/centers/{center}/testimonials', [TestimonialController::class, 'index']);

    // Webhooks (no auth, verified by signature)
    Route::post('/webhooks/calendly', [CalendlyWebhookController::class, 'handle']);
    Route::post('/webhooks/mailchimp', [MailchimpWebhookController::class, 'handle']);

    // ========================================================================
    // AUTHENTICATED ROUTES (Sanctum Token Required)
    // ========================================================================
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth - Logout & Email Verification
        Route::post('/auth/logout', [LogoutController::class, 'destroy']);
        Route::post('/auth/email/verify', [EmailVerificationController::class, 'verify']);
        Route::post('/auth/email/resend', [EmailVerificationController::class, 'resend']);

        // User Profile
        Route::get('/user', function () {
            return response()->json(['data' => auth()->user()->load('profile')]);
        });
        Route::put('/user', [AdminUserController::class, 'updateProfile']);

        // PDPA - Consent & Data Export
        Route::prefix('user')->group(function () {
            Route::get('/consents', [AdminUserController::class, 'getConsents']);
            Route::post('/export-data', [AdminUserController::class, 'exportData']);
            Route::post('/request-deletion', [AdminUserController::class, 'requestDeletion']);
            Route::post('/cancel-deletion', [AdminUserController::class, 'cancelDeletion']);
        });

        // Bookings (users can only access own bookings)
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{booking:booking_number}', [BookingController::class, 'show']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::put('/bookings/{booking}', [BookingController::class, 'update']);
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

        // Testimonials (submission)
        Route::post('/centers/{center}/testimonials', [TestimonialController::class, 'store']);

        // ====================================================================
        // ADMIN ROUTES (Role-based access)
        // ====================================================================
        
        Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index']);

            // User Management (super_admin only)
            Route::middleware('role:super_admin')->group(function () {
                Route::get('/users', [AdminUserController::class, 'index']);
                Route::get('/users/{user}', [AdminUserController::class, 'show']);
                Route::put('/users/{user}', [AdminUserController::class, 'update']);
                Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
            });

            // Center Management
            Route::post('/centers', [CenterController::class, 'store']);
            Route::put('/centers/{center}', [CenterController::class, 'update']);
            Route::delete('/centers/{center}', [CenterController::class, 'destroy']);

            // Service Management
            Route::post('/centers/{center}/services', [ServiceController::class, 'store']);
            Route::put('/services/{service}', [ServiceController::class, 'update']);
            Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

            // FAQ Management
            Route::post('/faqs', [FAQController::class, 'store']);
            Route::put('/faqs/{faq}', [FAQController::class, 'update']);
            Route::delete('/faqs/{faq}', [FAQController::class, 'destroy']);

            // Moderation
            Route::get('/testimonials/pending', [ModerationController::class, 'pendingTestimonials']);
            Route::post('/testimonials/{testimonial}/approve', [ModerationController::class, 'approveTestimonial']);
            Route::post('/testimonials/{testimonial}/reject', [ModerationController::class, 'rejectTestimonial']);

            Route::get('/contact-submissions', [ModerationController::class, 'contactSubmissions']);
            Route::put('/contact-submissions/{submission}', [ModerationController::class, 'updateSubmissionStatus']);

            // Media Management
            Route::post('/media', [MediaController::class, 'store']);
            Route::delete('/media/{media}', [MediaController::class, 'destroy']);
            Route::post('/media/reorder', [MediaController::class, 'reorder']);

            // Booking Management (all bookings)
            Route::get('/bookings', [BookingController::class, 'adminIndex']);
        });
    });
});
```

---

#### 8. `backend/bootstrap/app.php` (Register middleware aliases)

**Add to middleware aliases** (or `app/Http/Kernel.php` if using traditional structure):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
    ]);
})
```

---

### Validation for Day 1:

```bash
# Start containers
docker-compose up -d

# Test health endpoint
curl http://localhost:8000/api/health

# Test 404 handling
curl http://localhost:8000/api/v1/nonexistent

# Check middleware registration
php artisan route:list | grep "api/v1"

# Run tests (should still pass)
php artisan test
```

**Expected**:
- ✅ Health endpoint returns `{"status": "ok", "timestamp": "..."}`
- ✅ 404 returns standardized error JSON
- ✅ Routes visible in `route:list`
- ✅ All middleware aliases registered

---

## Day 2: PDPA Services & Auth Stack

**Branch**: `feature/phase3-pdpa-auth`

**Files to Create**: 15 files (Services, Controllers, Requests, Resources, Observer, Jobs, Policies)

### Critical Services:

#### 1. `backend/app/Observers/AuditObserver.php` 🔴 **CRITICAL**

```php
<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAudit($model, 'created', null, $model->getAttributes());
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAudit(
            $model,
            'updated',
            $model->getOriginal(),
            $model->getChanges()
        );
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAudit($model, 'deleted', $model->getAttributes(), null);
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->logAudit($model, 'restored', null, $model->getAttributes());
    }

    /**
     * Create audit log entry
     */
    protected function logAudit(
        Model $model,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {
        // Skip if this is an AuditLog itself (prevent infinite loop)
        if ($model instanceof AuditLog) {
            return;
        }

        // Get current request context
        $request = request();
        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues ? $this->sanitizeValues($oldValues) : null,
            'new_values' => $newValues ? $this->sanitizeValues($newValues) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
        ]);
    }

    /**
     * Sanitize sensitive data from audit logs
     */
    protected function sanitizeValues(array $values): array
    {
        $sensitiveFields = ['password', 'remember_token', 'api_token'];

        foreach ($sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }
}
```

---

#### 2. `backend/app/Providers/AppServiceProvider.php` (Modify to register observer)

**Add to `boot()` method**:

```php
use App\Models\User;
use App\Models\Center;
use App\Models\Booking;
use App\Models\Consent;
use App\Models\Testimonial;
use App\Observers\AuditObserver;

public function boot(): void
{
    // Register audit observer for models
    User::observe(AuditObserver::class);
    Center::observe(AuditObserver::class);
    Booking::observe(AuditObserver::class);
    Consent::observe(AuditObserver::class);
    Testimonial::observe(AuditObserver::class);
    // Add other models as needed
}
```

---

#### 3. `backend/app/Services/User/DataExportService.php`

```php
<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DataExportService
{
    /**
     * Export all user data as JSON (PDPA right to access)
     *
     * @param int $userId
     * @return array ['path' => string, 'url' => string, 'expires_at' => string]
     */
    public function exportUserData(int $userId): array
    {
        $user = User::with([
            'profile',
            'bookings.center',
            'bookings.service',
            'testimonials.center',
            'consents',
            'auditLogs',
        ])->findOrFail($userId);

        $exportData = [
            'export_date' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'preferred_language' => $user->preferred_language,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'profile' => $user->profile ? [
                'bio' => $user->profile->bio,
                'birth_date' => $user->profile->birth_date?->toDateString(),
                'address' => $user->profile->address,
                'city' => $user->profile->city,
                'postal_code' => $user->profile->postal_code,
            ] : null,
            'bookings' => $user->bookings->map(fn($booking) => [
                'booking_number' => $booking->booking_number,
                'center_name' => $booking->center->name,
                'service_name' => $booking->service?->name,
                'booking_date' => $booking->booking_date->toDateString(),
                'booking_time' => $booking->booking_time->toTimeString(),
                'status' => $booking->status,
                'questionnaire_responses' => $booking->questionnaire_responses,
                'created_at' => $booking->created_at->toIso8601String(),
            ]),
            'testimonials' => $user->testimonials->map(fn($testimonial) => [
                'center_name' => $testimonial->center->name,
                'title' => $testimonial->title,
                'content' => $testimonial->content,
                'rating' => $testimonial->rating,
                'status' => $testimonial->status,
                'created_at' => $testimonial->created_at->toIso8601String(),
            ]),
            'consents' => $user->consents->map(fn($consent) => [
                'type' => $consent->consent_type,
                'given' => $consent->consent_given,
                'version' => $consent->consent_version,
                'created_at' => $consent->created_at->toIso8601String(),
            ]),
            'audit_logs' => $user->auditLogs->map(fn($log) => [
                'action' => $log->action,
                'model' => $log->auditable_type,
                'created_at' => $log->created_at->toIso8601String(),
            ]),
        ];

        // Store JSON file in private S3 or local storage
        $filename = "user-data-export-{$userId}-" . now()->format('YmdHis') . '.json';
        $path = "exports/{$filename}";

        Storage::disk('private')->put(
            $path,
            json_encode($exportData, JSON_PRETTY_PRINT)
        );

        // Generate signed URL (valid for 1 hour)
        $url = Storage::disk('private')->temporaryUrl(
            $path,
            now()->addHour()
        );

        return [
            'path' => $path,
            'url' => $url,
            'expires_at' => now()->addHour()->toIso8601String(),
        ];
    }
}
```

---

#### 4. `backend/app/Services/User/AccountDeletionService.php`

```php
<?php

namespace App\Services\User;

use App\Models\User;
use App\Jobs\PermanentAccountDeletionJob;
use Carbon\Carbon;

class AccountDeletionService
{
    /**
     * Request account deletion (soft delete with 30-day grace period)
     *
     * @param int $userId
     * @return Carbon Deletion scheduled date
     */
    public function requestDeletion(int $userId): Carbon
    {
        $user = User::findOrFail($userId);

        // Soft delete the user
        $user->delete();

        // Schedule permanent deletion job (30 days from now)
        $scheduledDate = now()->addDays(30);
        PermanentAccountDeletionJob::dispatch($userId)
            ->delay($scheduledDate);

        return $scheduledDate;
    }

    /**
     * Cancel account deletion (restore from soft delete)
     *
     * @param int $userId
     * @return User
     */
    public function cancelDeletion(int $userId): User
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore();

        return $user;
    }

    /**
     * Permanently delete account (called by job after 30 days)
     *
     * @param int $userId
     * @return void
     */
    public function permanentlyDelete(int $userId): void
    {
        $user = User::onlyTrashed()->find($userId);

        if (!$user) {
            // Already deleted or restored
            return;
        }

        // Anonymize related data
        $this->anonymizeUserData($user);

        // Force delete user
        $user->forceDelete();
    }

    /**
     * Anonymize user data in related records
     */
    protected function anonymizeUserData(User $user): void
    {
        // Anonymize bookings (keep for statistical purposes)
        $user->bookings()->update([
            'questionnaire_responses' => null,
        ]);

        // Keep testimonials but anonymize user info (handled at display layer)
        // Audit logs are kept for compliance (7-year retention)
    }
}
```

---

#### 5. `backend/app/Jobs/PermanentAccountDeletionJob.php`

```php
<?php

namespace App\Jobs;

use App\Services\User\AccountDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PermanentAccountDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(AccountDeletionService $deletionService): void
    {
        Log::info("Processing permanent account deletion for user {$this->userId}");

        $deletionService->permanentlyDelete($this->userId);

        Log::info("User {$this->userId} permanently deleted");
    }
}
```

---

## Remaining Days — File Manifest

### **Day 3: Core Business Services** (28 files)
- **Services**: CenterService, ServiceManagementService, StaffService, FAQService, ContactService, MailchimpService
- **Controllers**: CenterController, ServiceController, FAQController, ContactController, SubscriptionController
- **Requests**: StoreCenterRequest, UpdateCenterRequest, StoreServiceRequest, ContactRequest, SubscribeRequest, StoreFAQRequest
- **Resources**: CenterResource, ServiceResource, FAQResource
- **Jobs**: SyncMailchimpSubscriptionJob
- **Policies**: CenterPolicy

### **Day 4: Booking System** (22 files)
- **Services**: BookingService, CalendlyService, NotificationService, TwilioService
- **Controllers**: BookingController, CalendlyWebhookController
- **Requests**: StoreBookingRequest, CancelBookingRequest
- **Resources**: BookingResource
- **Jobs**: SendBookingConfirmationJob, SendBookingReminderJob
- **Commands**: SendBookingRemindersCommand
- **Views**: Email templates (booking confirmation, reminder, cancellation)
- **Policies**: BookingPolicy

### **Day 5: Advanced Features** (18 files)
- **Services**: TestimonialService, MediaService, ImageOptimizationService, TranslationService
- **Controllers**: TestimonialController, MediaController, TranslationController
- **Requests**: StoreTestimonialRequest, UploadMediaRequest, StoreTranslationRequest
- **Resources**: TestimonialResource, MediaResource, TranslationResource
- **Jobs**: OptimizeImageJob
- **Policies**: TestimonialPolicy

### **Day 6: Testing** (43 files)
- **Unit Tests**: 20 test files (models, services)
- **Feature Tests**: 15 test files (API endpoints)
- **Factories**: 13 factories
- **Seeders**: DemoSeeder

### **Day 7: API Documentation** (8 files)
- **OpenAPI**: openapi.yaml
- **Postman**: Collection + Environment
- **Admin Controllers**: UserController, DashboardController, ModerationController
- **Commands**: GenerateApiDocsCommand

---

## 🎯 Execution Strategy Recommendation

Given the scope (154 files), I recommend a **phased PR approach**:

### **Phase 1 PR** (Day 1): `feature/phase3-api-infrastructure`
- 8 files: ApiResponse, Exception Handler, Middleware, Routes
- **Merge Criteria**: All routes return proper 501/404 responses, middleware works

### **Phase 2 PR** (Day 2): `feature/phase3-pdpa-auth`
- 15 files: AuditObserver, DataExportService, AccountDeletionService, Auth controllers/requests
- **Merge Criteria**: Registration works, audit logs capture model changes, data export generates JSON

### **Phase 3-7 PRs**: Continue with remaining workstreams
