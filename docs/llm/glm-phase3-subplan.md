# ElderCare SG — Phase 3 Backend Sub-Plan: Core Services & PDPA Compliance

## 1. Executive Summary

This sub-plan outlines the comprehensive implementation of Phase 3 of the ElderCare SG project, focusing on Core Backend Services & PDPA Compliance. The plan is designed to create a production-ready backend that demonstrates the platform's core functionality while ensuring full compliance with Singapore's Personal Data Protection Act (PDPA) and Ministry of Health (MOH) regulations.

The implementation will follow Laravel's service-oriented architecture pattern with thin controllers delegating business logic to dedicated service classes. All code will be thoroughly tested with PHPUnit, and the API will be fully documented with OpenAPI specifications.

## 2. Architecture Overview

### 2.1. Service Layer Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                    Controllers Layer                        │
│  (HTTP Request Handling, Validation, Response Formatting)   │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    Service Layer                           │
│  (Business Logic, Orchestration, External Integrations)    │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                   Repository Layer                         │
│      (Data Access, Query Building, Caching Strategy)       │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    Model Layer                             │
│        (Eloquent Models, Relationships, Mutators)          │
└─────────────────────────────────────────────────────────────┘
```

### 2.2. Key Design Principles
- **Service-Oriented Architecture**: Business logic encapsulated in service classes
- **Repository Pattern**: Data access abstracted through repositories
- **Policy-Based Authorization**: Fine-grained permissions using Laravel Policies
- **Event-Driven Architecture**: Important actions trigger events for audit logging
- **API-First Design**: All functionality exposed through well-documented REST APIs
- **PDPA by Design**: Privacy and consent handling built into core services

## 3. Implementation Plan

### 3.1. Authentication & Authorization System

#### File: `backend/app/Http/Controllers/Auth/AuthController.php`
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    /**
     * Register a new user account
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Authenticate user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement user registration with email verification
- [ ] Implement login with Laravel Sanctum token generation
- [ ] Implement logout with token revocation
- [ ] Implement token refresh functionality
- [ ] Return user profile with permissions
- [ ] Add rate limiting for auth endpoints
- [ ] Add request throttling for password attempts
- [ ] Log all authentication events

#### File: `backend/app/Services/Auth/AuthService.php`
```php
<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Consent\ConsentService;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthService
{
    protected ConsentService $consentService;
    protected AuditService $auditService;
    
    public function __construct(ConsentService $consentService, AuditService $auditService)
    {
        $this->consentService = $consentService;
        $this->auditService = $auditService;
    }
    
    /**
     * Create a new user account with required consents
     */
    public function register(array $userData, array $consents): User
    {
        // Implementation
    }
    
    /**
     * Authenticate user credentials
     */
    public function authenticate(string $email, string $password): ?User
    {
        // Implementation
    }
    
    /**
     * Generate API token for user
     */
    public function generateToken(User $user, string $tokenName = 'api-token'): string
    {
        // Implementation
    }
    
    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): void
    {
        // Implementation
    }
    
    /**
     * Send email verification notification
     */
    public function sendEmailVerification(User $user): void
    {
        // Implementation
    }
    
    /**
     * Send password reset notification
     */
    public function sendPasswordReset(User $user): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement user registration with transaction
- [ ] Hash passwords securely
- [ ] Generate unique verification tokens
- [ ] Create default user profile
- [ ] Record required consents
- [ ] Trigger audit events
- [ ] Send verification emails
- [ ] Handle email verification
- [ ] Implement password reset flow
- [ ] Generate and manage API tokens

#### File: `backend/app/Http/Requests/Auth/RegisterRequest.php`
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'regex:/^(\+65)?[689]\d{7}$/'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'preferred_language' => ['required', 'in:en,zh,ms,ta'],
            'consents' => ['required', 'array', 'min:1'],
            'consents.*.type' => ['required', 'in:account,marketing_email,marketing_sms,analytics_cookies,functional_cookies'],
            'consents.*.given' => ['required', 'boolean'],
            'consents.*.text' => ['required', 'string'],
            'consents.*.version' => ['required', 'string'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid Singapore number (e.g., +6581234567 or 81234567).',
            'consents.required' => 'You must provide at least one consent.',
            'consents.*.given.required' => 'Consent status is required for all consent types.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate required fields (name, email, password)
- [ ] Validate Singapore phone number format
- [ ] Validate password strength
- [ ] Validate consent array structure
- [ ] Validate consent types
- [ ] Add custom error messages
- [ ] Sanitize input data

#### File: `backend/app/Policies/UserPolicy.php`
```php
<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || 
               $user->isAdmin() || 
               $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Anyone can register
    }
    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || 
               $user->isAdmin() || 
               $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === $model->id || 
               $user->isAdmin() || 
               $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperAdmin();
    }
}
```
**Checklist:**
- [ ] Implement view permissions
- [ ] Implement create permissions
- [ ] Implement update permissions
- [ ] Implement delete permissions
- [ ] Implement restore permissions
- [ ] Implement force delete permissions
- [ ] Add role-based checks

### 3.2. User Management System

#### File: `backend/app/Http/Controllers/Api/V1/UserController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\ExportDataRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Update user password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Export user data (PDPA compliance)
     */
    public function exportData(ExportDataRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Request account deletion (PDPA compliance)
     */
    public function requestDeletion(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Cancel account deletion request
     */
    public function cancelDeletion(Request $request): JsonResponse
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement profile retrieval with relationships
- [ ] Implement profile update with validation
- [ ] Implement password update with verification
- [ ] Implement data export in JSON format
- [ ] Implement account deletion request
- [ ] Implement deletion request cancellation
- [ ] Add rate limiting for sensitive operations
- [ ] Log all profile changes

#### File: `backend/app/Services/User/UserService.php`
```php
<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\Profile;
use App\Services\Consent\ConsentService;
use App\Services\Audit\AuditService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserService
{
    protected ConsentService $consentService;
    protected AuditService $auditService;
    protected NotificationService $notificationService;
    
    public function __construct(
        ConsentService $consentService,
        AuditService $auditService,
        NotificationService $notificationService
    ) {
        $this->consentService = $consentService;
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Update user profile information
     */
    public function updateProfile(User $user, array $profileData): User
    {
        // Implementation
    }
    
    /**
     * Update user password
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Implementation
    }
    
    /**
     * Export all user data in JSON format (PDPA compliance)
     */
    public function exportUserData(User $user): array
    {
        // Implementation
    }
    
    /**
     * Request account deletion (PDPA compliance)
     */
    public function requestAccountDeletion(User $user, string $reason = null): bool
    {
        // Implementation
    }
    
    /**
     * Cancel account deletion request
     */
    public function cancelAccountDeletion(User $user): bool
    {
        // Implementation
    }
    
    /**
     * Permanently delete user account (after grace period)
     */
    public function permanentlyDeleteAccount(User $user): bool
    {
        // Implementation
    }
    
    /**
     * Get users pending deletion (for admin)
     */
    public function getUsersPendingDeletion(): \Illuminate\Support\Collection
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement profile update with validation
- [ ] Implement password update with verification
- [ ] Implement comprehensive data export
- [ ] Implement account deletion request with grace period
- [ ] Implement deletion request cancellation
- [ ] Implement permanent deletion after grace period
- [ ] Add notification for deletion requests
- [ ] Add audit logging for all operations

#### File: `backend/app/Models/User.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'preferred_language',
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
    
    /**
     * Get the user's consents.
     */
    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }
    
    /**
     * Get the user's audit logs.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Get the user's bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
    /**
     * Get the user's testimonials.
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
    
    /**
     * Get the user's contact submissions.
     */
    public function contactSubmissions(): HasMany
    {
        return $this->hasMany(ContactSubmission::class);
    }
    
    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    
    /**
     * Get the latest consent for a specific type.
     */
    public function getLatestConsent(string $consentType): ?Consent
    {
        return $this->consents()
            ->where('consent_type', $consentType)
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    /**
     * Check if user has given consent for a specific type.
     */
    public function hasGivenConsent(string $consentType): bool
    {
        $consent = $this->getLatestConsent($consentType);
        return $consent && $consent->consent_given;
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define hidden attributes
- [ ] Define attribute casts
- [ ] Implement relationships (profile, consents, auditLogs, etc.)
- [ ] Add role checking methods
- [ ] Add consent checking methods
- [ ] Add soft deletes support
- [ ] Add API tokens support

### 3.3. Consent Management System

#### File: `backend/app/Http/Controllers/Api/V1/ConsentController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consent\UpdateConsentRequest;
use App\Services\Consent\ConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    protected ConsentService $consentService;
    
    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }
    
    /**
     * Get user's current consents
     */
    public function index(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Update user's consent preferences
     */
    public function update(UpdateConsentRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get privacy policy version
     */
    public function privacyPolicy(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get cookie policy
     */
    public function cookiePolicy(Request $request): JsonResponse
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement consent retrieval
- [ ] Implement consent update with validation
- [ ] Implement privacy policy retrieval
- [ ] Implement cookie policy retrieval
- [ ] Add audit logging for consent changes
- [ ] Add IP address tracking for consents

#### File: `backend/app/Services/Consent/ConsentService.php`
```php
<?php

namespace App\Services\Consent;

use App\Models\User;
use App\Models\Consent;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\Request;

class ConsentService
{
    protected AuditService $auditService;
    
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    /**
     * Record user consent
     */
    public function recordConsent(
        User $user, 
        string $consentType, 
        bool $consentGiven, 
        string $consentText, 
        string $consentVersion
    ): Consent {
        // Implementation
    }
    
    /**
     * Update multiple consents at once
     */
    public function updateConsents(User $user, array $consents): array
    {
        // Implementation
    }
    
    /**
     * Get user's current consents
     */
    public function getUserConsents(User $user): array
    {
        // Implementation
    }
    
    /**
     * Check if user has given consent for a specific type
     */
    public function hasUserGivenConsent(User $user, string $consentType): bool
    {
        // Implementation
    }
    
    /**
     * Get privacy policy content
     */
    public function getPrivacyPolicy(string $locale = 'en'): array
    {
        // Implementation
    }
    
    /**
     * Get cookie policy content
     */
    public function getCookiePolicy(string $locale = 'en'): array
    {
        // Implementation
    }
    
    /**
     * Get current privacy policy version
     */
    public function getCurrentPrivacyPolicyVersion(): string
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement consent recording with IP tracking
- [ ] Implement bulk consent update
- [ ] Implement consent retrieval
- [ ] Implement consent checking
- [ ] Implement policy content retrieval
- [ ] Add audit logging for consent changes
- [ ] Add version tracking for policies

#### File: `backend/app/Models/Consent.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_given',
        'consent_text',
        'consent_version',
        'ip_address',
        'user_agent',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'consent_given' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the user that owns the consent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include consents of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }
    
    /**
     * Scope a query to only include given consents.
     */
    public function scopeGiven($query)
    {
        return $query->where('consent_given', true);
    }
    
    /**
     * Scope a query to only include withdrawn consents.
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('consent_given', false);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement user relationship
- [ ] Add scopes for consent type and status
- [ ] Add query methods for consent checking

### 3.4. Audit Logging System

#### File: `backend/app/Services/Audit/AuditService.php`
```php
<?php

namespace App\Services\Audit;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     */
    public function log(
        ?User $user,
        Model $auditable,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        // Implementation
    }
    
    /**
     * Log model creation
     */
    public function logCreated(?User $user, Model $model): AuditLog
    {
        // Implementation
    }
    
    /**
     * Log model update
     */
    public function logUpdated(?User $user, Model $model, array $oldValues, array $newValues): AuditLog
    {
        // Implementation
    }
    
    /**
     * Log model deletion
     */
    public function logDeleted(?User $user, Model $model): AuditLog
    {
        // Implementation
    }
    
    /**
     * Log model restoration
     */
    public function logRestored(?User $user, Model $model): AuditLog
    {
        // Implementation
    }
    
    /**
     * Get audit logs for a model
     */
    public function getAuditLogs(Model $model): \Illuminate\Database\Eloquent\Collection
    {
        // Implementation
    }
    
    /**
     * Get audit logs for a user
     */
    public function getUserAuditLogs(User $user): \Illuminate\Database\Eloquent\Collection
    {
        // Implementation
    }
    
    /**
     * Clean up old audit logs (retention policy)
     */
    public function cleanupOldLogs(int $retentionDays = 2555): int // 7 years
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement generic audit logging
- [ ] Implement specific logging methods for CRUD operations
- [ ] Implement audit log retrieval
- [ ] Implement log cleanup based on retention policy
- [ ] Add IP address and user agent tracking
- [ ] Add request URL tracking

#### File: `backend/app/Models/AuditLog.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];
    
    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Scope a query to only include logs of a given action.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Scope a query to only include logs for a specific date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement user relationship
- [ ] Implement polymorphic auditable relationship
- [ ] Add scopes for action and date range filtering

### 3.5. Center Management System

#### File: `backend/app/Http/Controllers/Api/V1/CenterController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\IndexCenterRequest;
use App\Http\Requests\Center\ShowCenterRequest;
use App\Services\Center\CenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    protected CenterService $centerService;
    
    public function __construct(CenterService $centerService)
    {
        $this->centerService = $centerService;
    }
    
    /**
     * Get a list of centers with filtering and pagination
     */
    public function index(IndexCenterRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get details of a specific center
     */
    public function show(ShowCenterRequest $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get centers near a location
     */
    public function nearby(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Search centers by text query
     */
    public function search(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get center services
     */
    public function services(Request $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get center staff
     */
    public function staff(Request $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get center testimonials
     */
    public function testimonials(Request $request, int $id): JsonResponse
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering
- [ ] Implement center details retrieval
- [ ] Implement nearby centers search
- [ ] Implement text-based search
- [ ] Implement center services retrieval
- [ ] Implement center staff retrieval
- [ ] Implement center testimonials retrieval
- [ ] Add caching for frequently accessed data

#### File: `backend/app/Services/Center/CenterService.php`
```php
<?php

namespace App\Services\Center;

use App\Models\Center;
use App\Repositories\Center\CenterRepository;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Cache;

class CenterService
{
    protected CenterRepository $centerRepository;
    protected MediaService $mediaService;
    
    public function __construct(
        CenterRepository $centerRepository,
        MediaService $mediaService
    ) {
        $this->centerRepository = $centerRepository;
        $this->mediaService = $mediaService;
    }
    
    /**
     * Get centers with filtering and pagination
     */
    public function getCenters(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Implementation
    }
    
    /**
     * Get center by ID with relationships
     */
    public function getCenterById(int $id): ?Center
    {
        // Implementation
    }
    
    /**
     * Get centers near a location
     */
    public function getNearbyCenters(float $latitude, float $longitude, float $radiusKm = 10): \Illuminate\Support\Collection
    {
        // Implementation
    }
    
    /**
     * Search centers by text query
     */
    public function searchCenters(string $query, int $limit = 20): \Illuminate\Support\Collection
    {
        // Implementation
    }
    
    /**
     * Get center services
     */
    public function getCenterServices(int $centerId): \Illuminate\Support\Collection
    {
        // Implementation
    }
    
    /**
     * Get center staff
     */
    public function getCenterStaff(int $centerId): \Illuminate\Support\Collection
    {
        // Implementation
    }
    
    /**
     * Get center testimonials
     */
    public function getCenterTestimonials(int $centerId, int $limit = 10): \Illuminate\Support\Collection
    {
        // Implementation
    }
    
    /**
     * Get center with translated content
     */
    public function getCenterWithTranslations(int $centerId, string $locale = 'en'): ?Center
    {
        // Implementation
    }
    
    /**
     * Get center statistics
     */
    public function getCenterStatistics(int $centerId): array
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering
- [ ] Implement center details retrieval
- [ ] Implement nearby centers calculation
- [ ] Implement text-based search
- [ ] Implement center services retrieval
- [ ] Implement center staff retrieval
- [ ] Implement center testimonials retrieval
- [ ] Implement multilingual content support
- [ ] Implement statistics calculation
- [ ] Add caching for performance

#### File: `backend/app/Repositories/Center/CenterRepository.php`
```php
<?php

namespace App\Repositories\Center;

use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;

class CenterRepository
{
    /**
     * Get centers with filtering and pagination
     */
    public function getCenters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Implementation
    }
    
    /**
     * Get center by ID with relationships
     */
    public function findById(int $id): ?Center
    {
        // Implementation
    }
    
    /**
     * Get centers near a location using Haversine formula
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        // Implementation
    }
    
    /**
     * Search centers by text query
     */
    public function search(string $query, int $limit = 20): Collection
    {
        // Implementation
    }
    
    /**
     * Get centers by city
     */
    public function getByCity(string $city): Collection
    {
        // Implementation
    }
    
    /**
     * Get centers by accreditation status
     */
    public function getByAccreditationStatus(string $status): Collection
    {
        // Implementation
    }
    
    /**
     * Get centers with available capacity
     */
    public function getWithAvailableCapacity(): Collection
    {
        // Implementation
    }
    
    /**
     * Get center statistics
     */
    public function getStatistics(int $centerId): array
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering
- [ ] Implement center retrieval by ID
- [ ] Implement nearby centers query using Haversine formula
- [ ] Implement full-text search
- [ ] Implement filtering by city
- [ ] Implement filtering by accreditation status
- [ ] Implement filtering by capacity
- [ ] Implement statistics query

#### File: `backend/app/Models/Center.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Center extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'moh_license_number',
        'license_expiry_date',
        'accreditation_status',
        'capacity',
        'current_occupancy',
        'staff_count',
        'staff_patient_ratio',
        'operating_hours',
        'medical_facilities',
        'amenities',
        'transport_info',
        'languages_supported',
        'government_subsidies',
        'latitude',
        'longitude',
        'status',
        'meta_title',
        'meta_description',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'license_expiry_date' => 'date',
        'operating_hours' => 'array',
        'medical_facilities' => 'array',
        'amenities' => 'array',
        'transport_info' => 'array',
        'languages_supported' => 'array',
        'government_subsidies' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the services for the center.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
    
    /**
     * Get the staff for the center.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
    
    /**
     * Get the bookings for the center.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
    /**
     * Get the testimonials for the center.
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
    
    /**
     * Get the media for the center.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
    
    /**
     * Get the content translations for the center.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }
    
    /**
     * Get the audit logs for the center.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include published centers.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    
    /**
     * Scope a query to only include centers in a specific city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }
    
    /**
     * Scope a query to only include centers with a specific accreditation status.
     */
    public function scopeWithAccreditationStatus($query, string $status)
    {
        return $query->where('accreditation_status', $status);
    }
    
    /**
     * Get the occupancy rate as a percentage.
     */
    public function getOccupancyRateAttribute(): float
    {
        if ($this->capacity === 0) {
            return 0;
        }
        
        return round(($this->current_occupancy / $this->capacity) * 100, 2);
    }
    
    /**
     * Get the translated name for a specific locale.
     */
    public function getTranslatedName(string $locale = 'en'): string
    {
        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('field', 'name')
            ->where('translation_status', 'published')
            ->first();
            
        return $translation ? $translation->value : $this->name;
    }
    
    /**
     * Get the translated description for a specific locale.
     */
    public function getTranslatedDescription(string $locale = 'en'): string
    {
        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('field', 'description')
            ->where('translation_status', 'published')
            ->first();
            
        return $translation ? $translation->value : $this->description;
    }
    
    /**
     * Check if the center has available capacity.
     */
    public function hasAvailableCapacity(): bool
    {
        return $this->current_occupancy < $this->capacity;
    }
    
    /**
     * Get the available capacity count.
     */
    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (services, staff, bookings, etc.)
- [ ] Add scopes for status, city, and accreditation
- [ ] Add computed attributes (occupancy rate, available capacity)
- [ ] Add translation methods
- [ ] Add capacity checking methods
- [ ] Add soft deletes support

### 3.6. Booking Management System

#### File: `backend/app/Http/Controllers/Api/V1/BookingController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    
    /**
     * Get user's bookings
     */
    public function index(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get a specific booking
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Create a new booking
     */
    public function create(CreateBookingRequest $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Update a booking
     */
    public function update(UpdateBookingRequest $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Cancel a booking
     */
    public function cancel(CancelBookingRequest $request, int $id): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get available time slots for a center
     */
    public function availableSlots(Request $request): JsonResponse
    {
        // Implementation
    }
    
    /**
     * Get booking questionnaire
     */
    public function questionnaire(Request $request): JsonResponse
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement booking listing for user
- [ ] Implement booking details retrieval
- [ ] Implement booking creation with validation
- [ ] Implement booking update
- [ ] Implement booking cancellation
- [ ] Implement available time slots retrieval
- [ ] Implement questionnaire retrieval
- [ ] Add authorization checks
- [ ] Add audit logging

#### File: `backend/app/Services/Booking/BookingService.php`
```php
<?php

namespace App\Services\Booking;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use App\Services\Calendly\CalendlyService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    protected CalendlyService $calendlyService;
    protected NotificationService $notificationService;
    protected AuditService $auditService;
    
    public function __construct(
        CalendlyService $calendlyService,
        NotificationService $notificationService,
        AuditService $auditService
    ) {
        $this->calendlyService = $calendlyService;
        $this->notificationService = $notificationService;
        $this->auditService = $auditService;
    }
    
    /**
     * Create a new booking
     */
    public function createBooking(
        User $user,
        Center $center,
        ?Service $service,
        Carbon $bookingDate,
        Carbon $bookingTime,
        string $bookingType,
        array $questionnaireResponses = []
    ): Booking {
        // Implementation
    }
    
    /**
     * Update a booking
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        // Implementation
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, string $reason = null): Booking
    {
        // Implementation
    }
    
    /**
     * Confirm a booking
     */
    public function confirmBooking(Booking $booking): Booking
    {
        // Implementation
    }
    
    /**
     * Get available time slots for a center
     */
    public function getAvailableSlots(Center $center, Carbon $date): array
    {
        // Implementation
    }
    
    /**
     * Get booking questionnaire schema
     */
    public function getQuestionnaireSchema(): array
    {
        // Implementation
    }
    
    /**
     * Process Calendly webhook
     */
    public function processCalendlyWebhook(array $payload): bool
    {
        // Implementation
    }
    
    /**
     * Send booking confirmation
     */
    public function sendConfirmation(Booking $booking): void
    {
        // Implementation
    }
    
    /**
     * Send booking reminder
     */
    public function sendReminder(Booking $booking): void
    {
        // Implementation
    }
    
    /**
     * Get user's bookings
     */
    public function getUserBookings(User $user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Implementation
    }
    
    /**
     * Get center's bookings
     */
    public function getCenterBookings(Center $center, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement booking creation with Calendly integration
- [ ] Implement booking update
- [ ] Implement booking cancellation
- [ ] Implement booking confirmation
- [ ] Implement available time slots calculation
- [ ] Implement questionnaire schema
- [ ] Implement Calendly webhook processing
- [ ] Implement notification sending
- [ ] Add audit logging
- [ ] Add transaction support

#### File: `backend/app/Services/Calendly/CalendlyService.php`
```php
<?php

namespace App\Services\Calendly;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendlyService
{
    protected string $apiKey;
    protected string $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.calendly.api_key');
        $this->baseUrl = config('services.calendly.base_url', 'https://api.calendly.com');
    }
    
    /**
     * Create a Calendly event
     */
    public function createEvent(array $eventData): array
    {
        // Implementation
    }
    
    /**
     * Get event details
     */
    public function getEvent(string $eventId): array
    {
        // Implementation
    }
    
    /**
     * Cancel an event
     */
    public function cancelEvent(string $eventId, string $reason = null): bool
    {
        // Implementation
    }
    
    /**
     * Reschedule an event
     */
    public function rescheduleEvent(string $eventId, string $newStartTime): array
    {
        // Implementation
    }
    
    /**
     * Get available time slots
     */
    public function getAvailableSlots(string $eventTypeUri, string $startDate, string $endDate): array
    {
        // Implementation
    }
    
    /**
     * Get event type details
     */
    public function getEventType(string $eventTypeId): array
    {
        // Implementation
    }
    
    /**
     * Create a booking invitee
     */
    public function createInvitee(string $eventUri, array $inviteeData): array
    {
        // Implementation
    }
    
    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        // Implementation
    }
    
    /**
     * Process webhook payload
     */
    public function processWebhook(array $payload): ?array
    {
        // Implementation
    }
    
    /**
     * Sync booking with Calendly event
     */
    public function syncBookingWithEvent(Booking $booking, array $eventData): Booking
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement event creation
- [ ] Implement event retrieval
- [ ] Implement event cancellation
- [ ] Implement event rescheduling
- [ ] Implement available time slots retrieval
- [ ] Implement event type retrieval
- [ ] Implement invitee creation
- [ ] Implement webhook validation
- [ ] Implement webhook processing
- [ ] Implement booking synchronization

#### File: `backend/app/Models/Booking.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Booking extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'booking_number',
        'user_id',
        'center_id',
        'service_id',
        'booking_date',
        'booking_time',
        'booking_type',
        'calendly_event_id',
        'calendly_event_uri',
        'calendly_cancel_url',
        'calendly_reschedule_url',
        'questionnaire_responses',
        'status',
        'cancellation_reason',
        'notes',
        'confirmation_sent_at',
        'reminder_sent_at',
        'sms_sent',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i:s',
        'questionnaire_responses' => 'array',
        'confirmation_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'sms_sent' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the center for the booking.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
    
    /**
     * Get the service for the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    
    /**
     * Get the audit logs for the booking.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include bookings for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('booking_date', $date);
    }
    
    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('booking_date', '>=', now())
                     ->where('status', '!=', 'cancelled');
    }
    
    /**
     * Scope a query to only include past bookings.
     */
    public function scopePast($query)
    {
        return $query->whereDate('booking_date', '<', now())
                     ->orWhere('status', 'cancelled');
    }
    
    /**
     * Check if the booking is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    
    /**
     * Check if the booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Check if the booking is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    /**
     * Check if the booking is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->booking_date >= now()->toDateString() && !$this->isCancelled();
    }
    
    /**
     * Check if the booking is in the past.
     */
    public function isPast(): bool
    {
        return $this->booking_date < now()->toDateString() || $this->isCompleted();
    }
    
    /**
     * Get the formatted booking date and time.
     */
    public function getFormattedDateTimeAttribute(): string
    {
        return $this->booking_date->format('M j, Y') . ' at ' . $this->booking_time->format('g:i A');
    }
    
    /**
     * Get the cancellation URL (Calendly or fallback).
     */
    public function getCancellationUrlAttribute(): ?string
    {
        return $this->calendly_cancel_url;
    }
    
    /**
     * Get the reschedule URL (Calendly or fallback).
     */
    public function getRescheduleUrlAttribute(): ?string
    {
        return $this->calendly_reschedule_url;
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (user, center, service)
- [ ] Add scopes for status, date, and time filtering
- [ ] Add status checking methods
- [ ] Add formatted date/time method
- [ ] Add URL getters for Calendly integration
- [ ] Add soft deletes support

### 3.7. API Documentation & OpenAPI Specification

#### File: `backend/routes/api.php`
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ConsentController;
use App\Http\Controllers\Api\V1\CenterController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\FAQController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\SubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('auth/verify-email', [AuthController::class, 'verifyEmail']);
    
    // Centers
    Route::get('centers', [CenterController::class, 'index']);
    Route::get('centers/search', [CenterController::class, 'search']);
    Route::get('centers/nearby', [CenterController::class, 'nearby']);
    Route::get('centers/{id}', [CenterController::class, 'show']);
    Route::get('centers/{id}/services', [CenterController::class, 'services']);
    Route::get('centers/{id}/staff', [CenterController::class, 'staff']);
    Route::get('centers/{id}/testimonials', [CenterController::class, 'testimonials']);
    
    // Services
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{id}', [ServiceController::class, 'show']);
    
    // Testimonials
    Route::get('testimonials', [TestimonialController::class, 'index']);
    Route::get('testimonials/{id}', [TestimonialController::class, 'show']);
    
    // FAQs
    Route::get('faqs', [FAQController::class, 'index']);
    Route::get('faqs/{id}', [FAQController::class, 'show']);
    
    // Contact
    Route::post('contact', [ContactController::class, 'submit']);
    
    // Newsletter
    Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('unsubscribe', [SubscriptionController::class, 'unsubscribe']);
    
    // Consents (public policies)
    Route::get('consents/privacy-policy', [ConsentController::class, 'privacyPolicy']);
    Route::get('consents/cookie-policy', [ConsentController::class, 'cookiePolicy']);
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me', [AuthController::class, 'me']);
    
    // User
    Route::get('user/profile', [UserController::class, 'profile']);
    Route::put('user/profile', [UserController::class, 'updateProfile']);
    Route::put('user/password', [UserController::class, 'updatePassword']);
    Route::get('user/export-data', [UserController::class, 'exportData']);
    Route::post('user/request-deletion', [UserController::class, 'requestDeletion']);
    Route::post('user/cancel-deletion', [UserController::class, 'cancelDeletion']);
    
    // Consents
    Route::get('user/consents', [ConsentController::class, 'index']);
    Route::put('user/consents', [ConsentController::class, 'update']);
    
    // Bookings
    Route::get('bookings', [BookingController::class, 'index']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::post('bookings', [BookingController::class, 'create']);
    Route::put('bookings/{id}', [BookingController::class, 'update']);
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('bookings/available-slots', [BookingController::class, 'availableSlots']);
    Route::get('bookings/questionnaire', [BookingController::class, 'questionnaire']);
    
    // Testimonials
    Route::post('testimonials', [TestimonialController::class, 'create']);
    Route::put('testimonials/{id}', [TestimonialController::class, 'update']);
    Route::delete('testimonials/{id}', [TestimonialController::class, 'delete']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('v1/admin')->group(function () {
    // Centers management
    Route::apiResource('centers', App\Http\Controllers\Api\V1\Admin\CenterController::class);
    
    // Services management
    Route::apiResource('services', App\Http\Controllers\Api\V1\Admin\ServiceController::class);
    
    // Bookings management
    Route::get('bookings', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'index']);
    Route::get('bookings/{id}', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'show']);
    Route::put('bookings/{id}/confirm', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'confirm']);
    Route::put('bookings/{id}/complete', [App\Http\Controllers\Api\V1\Admin\BookingController::class, 'complete']);
    
    // Users management
    Route::get('users', [App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
    Route::get('users/{id}', [App\Http\Controllers\Api\V1\Admin\UserController::class, 'show']);
    Route::put('users/{id}/role', [App\Http\Controllers\Api\V1\Admin\UserController::class, 'updateRole']);
    Route::get('users/pending-deletion', [App\Http\Controllers\Api\V1\Admin\UserController::class, 'pendingDeletion']);
    Route::post('users/{id}/process-deletion', [App\Http\Controllers\Api\V1\Admin\UserController::class, 'processDeletion']);
    
    // Testimonials management
    Route::get('testimonials', [App\Http\Controllers\Api\V1\Admin\TestimonialController::class, 'index']);
    Route::put('testimonials/{id}/approve', [App\Http\Controllers\Api\V1\Admin\TestimonialController::class, 'approve']);
    Route::put('testimonials/{id}/reject', [App\Http\Controllers\Api\V1\Admin\TestimonialController::class, 'reject']);
    
    // Contact submissions
    Route::get('contact', [App\Http\Controllers\Api\V1\Admin\ContactController::class, 'index']);
    Route::put('contact/{id}/resolve', [App\Http\Controllers\Api\V1\Admin\ContactController::class, 'resolve']);
    
    // Audit logs
    Route::get('audit-logs', [App\Http\Controllers\Api\V1\Admin\AuditLogController::class, 'index']);
    Route::get('audit-logs/{id}', [App\Http\Controllers\Api\V1\Admin\AuditLogController::class, 'show']);
});
```
**Checklist:**
- [ ] Define public routes for authentication
- [ ] Define public routes for centers, services, testimonials, FAQs
- [ ] Define protected routes for user management
- [ ] Define protected routes for bookings
- [ ] Define admin routes for content management
- [ ] Add middleware for authentication and authorization
- [ ] Add rate limiting for sensitive routes

#### File: `backend/storage/api-docs/openapi.yaml`
```yaml
openapi: 3.0.3
info:
  title: ElderCare SG API
  description: API for ElderCare SG platform - connecting Singaporean families with trustworthy elderly daycare services
  version: 1.0.0
  contact:
    name: ElderCare SG Support
    email: support@eldercare-sg.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: https://api.eldercare-sg.com/v1
    description: Production server
  - url: https://staging-api.eldercare-sg.com/v1
    description: Staging server
  - url: http://localhost:8000/api/v1
    description: Development server

security:
  - bearerAuth: []

paths:
  # Authentication endpoints
  /auth/register:
    post:
      tags:
        - Authentication
      summary: Register a new user
      description: Create a new user account with required consents
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/RegisterRequest'
      responses:
        '201':
          description: User registered successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthResponse'
        '422':
          $ref: '#/components/responses/ValidationError'

  /auth/login:
    post:
      tags:
        - Authentication
      summary: Authenticate user
      description: Authenticate user credentials and return token
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/LoginRequest'
      responses:
        '200':
          description: Authentication successful
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthResponse'
        '401':
          $ref: '#/components/responses/UnauthorizedError'

  /auth/logout:
    post:
      tags:
        - Authentication
      summary: Logout user
      description: Logout user and revoke token
      security:
        - bearerAuth: []
      responses:
        '200':
          description: Logout successful
        '401':
          $ref: '#/components/responses/UnauthorizedError'

  # Centers endpoints
  /centers:
    get:
      tags:
        - Centers
      summary: Get list of centers
      description: Get a paginated list of centers with optional filtering
      parameters:
        - name: page
          in: query
          description: Page number
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          description: Number of items per page
          schema:
            type: integer
            default: 15
        - name: city
          in: query
          description: Filter by city
          schema:
            type: string
        - name: accreditation_status
          in: query
          description: Filter by accreditation status
          schema:
            type: string
            enum: [pending, accredited, not_accredited, expired]
      responses:
        '200':
          description: List of centers
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CentersResponse'

  /centers/{id}:
    get:
      tags:
        - Centers
      summary: Get center details
      description: Get detailed information about a specific center
      parameters:
        - name: id
          in: path
          required: true
          description: Center ID
          schema:
            type: integer
      responses:
        '200':
          description: Center details
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CenterResponse'
        '404':
          $ref: '#/components/responses/NotFoundError'

  # Bookings endpoints
  /bookings:
    get:
      tags:
        - Bookings
      summary: Get user bookings
      description: Get a paginated list of user's bookings
      security:
        - bearerAuth: []
      parameters:
        - name: page
          in: query
          description: Page number
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          description: Number of items per page
          schema:
            type: integer
            default: 15
        - name: status
          in: query
          description: Filter by booking status
          schema:
            type: string
            enum: [pending, confirmed, completed, cancelled, no_show]
      responses:
        '200':
          description: List of bookings
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/BookingsResponse'
        '401':
          $ref: '#/components/responses/UnauthorizedError'

    post:
      tags:
        - Bookings
      summary: Create a new booking
      description: Create a new booking for a center visit or service
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateBookingRequest'
      responses:
        '201':
          description: Booking created successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/BookingResponse'
        '422':
          $ref: '#/components/responses/ValidationError'
        '401':
          $ref: '#/components/responses/UnauthorizedError'

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    RegisterRequest:
      type: object
      required:
        - name
        - email
        - password
        - preferred_language
        - consents
      properties:
        name:
          type: string
          description: Full name
          example: John Doe
        email:
          type: string
          format: email
          description: Email address
          example: john.doe@example.com
        phone:
          type: string
          description: Singapore phone number
          example: "+6581234567"
        password:
          type: string
          format: password
          description: Password
          example: password123
        preferred_language:
          type: string
          enum: [en, zh, ms, ta]
          description: Preferred language
          example: en
        consents:
          type: array
          description: Array of consents
          items:
            $ref: '#/components/schemas/ConsentData'

    ConsentData:
      type: object
      required:
        - type
        - given
        - text
        - version
      properties:
        type:
          type: string
          enum: [account, marketing_email, marketing_sms, analytics_cookies, functional_cookies]
          description: Type of consent
        given:
          type: boolean
          description: Whether consent is given
        text:
          type: string
          description: Consent text
        version:
          type: string
          description: Privacy policy version

    LoginRequest:
      type: object
      required:
        - email
        - password
      properties:
        email:
          type: string
          format: email
          description: Email address
          example: john.doe@example.com
        password:
          type: string
          format: password
          description: Password
          example: password123

    AuthResponse:
      type: object
      properties:
        success:
          type: boolean
          example: true
        data:
          type: object
          properties:
            user:
              $ref: '#/components/schemas/User'
            token:
              type: string
              description: API token
              example: "1|abc123def456..."

    User:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: John Doe
        email:
          type: string
          format: email
          example: john.doe@example.com
        phone:
          type: string
          example: "+6581234567"
        role:
          type: string
          enum: [user, admin, super_admin]
          example: user
        preferred_language:
          type: string
          enum: [en, zh, ms, ta]
          example: en
        email_verified_at:
          type: string
          format: date-time
          nullable: true
        created_at:
          type: string
          format: date-time
        updated_at:
          type: string
          format: date-time

    CenterResponse:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: Sunshine Elderly Care Center
        slug:
          type: string
          example: sunshine-elderly-care-center
        short_description:
          type: string
          example: A caring environment for seniors
        description:
          type: string
          example: We provide comprehensive care for elderly...
        address:
          type: string
          example: 123 Orchard Road, Singapore
        city:
          type: string
          example: Singapore
        postal_code:
          type: string
          example: 238874
        phone:
          type: string
          example: "+6562345678"
        email:
          type: string
          format: email
          example: info@sunshine-care.com
        moh_license_number:
          type: string
          example: MOH/2023/12345
        license_expiry_date:
          type: string
          format: date
          example: "2024-12-31"
        accreditation_status:
          type: string
          enum: [pending, accredited, not_accredited, expired]
          example: accredited
        capacity:
          type: integer
          example: 100
        current_occupancy:
          type: integer
          example: 75
        occupancy_rate:
          type: number
          format: float
          example: 75.0
        operating_hours:
          type: object
          example:
            monday:
              open: "08:00"
              close: "18:00"
            tuesday:
              open: "08:00"
              close: "18:00"
        medical_facilities:
          type: array
          items:
            type: string
          example: ["examination_room", "medication_storage"]
        amenities:
          type: array
          items:
            type: string
          example: ["wheelchair_accessible", "air_conditioned"]
        transport_info:
          type: object
          example:
            mrt: ["Orchard"]
            bus: ["123", "456"]
            parking: true
        languages_supported:
          type: array
          items:
            type: string
          example: ["en", "zh", "ms"]
        government_subsidies:
          type: array
          items:
            type: string
          example: ["pioneer_generation", "merdeka_generation"]
        latitude:
          type: number
          format: float
          example: 1.3048
        longitude:
          type: number
          format: float
          example: 103.8318
        services:
          type: array
          items:
            $ref: '#/components/schemas/Service'
        staff:
          type: array
          items:
            $ref: '#/components/schemas/Staff'
        testimonials:
          type: array
          items:
            $ref: '#/components/schemas/Testimonial'
        media:
          type: array
          items:
            $ref: '#/components/schemas/Media'

    Service:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: Day Care Service
        slug:
          type: string
          example: day-care-service
        description:
          type: string
          example: Full day care service for elderly...
        price:
          type: number
          format: float
          nullable: true
          example: 50.00
        price_unit:
          type: string
          enum: [hour, day, week, month]
          nullable: true
          example: day
        duration:
          type: string
          nullable: true
          example: "Full day"
        features:
          type: array
          items:
            type: string
          example: ["meals_included", "medication_management"]

    Staff:
      type: object
      properties:
        id:
          type: integer
          example: 1
        name:
          type: string
          example: Jane Smith
        position:
          type: string
          example: Registered Nurse
        qualifications:
          type: array
          items:
            type: string
          example: ["RN", "CPR Certified"]
        years_of_experience:
          type: integer
          example: 10
        bio:
          type: string
          nullable: true
          example: Jane has been working in elderly care...
        photo:
          type: string
          nullable: true
          example: "https://example.com/photos/jane.jpg"

    Testimonial:
      type: object
      properties:
        id:
          type: integer
          example: 1
        title:
          type: string
          example: Excellent care for my mother
        content:
          type: string
          example: The staff at Sunshine Center are very caring...
        rating:
          type: integer
          minimum: 1
          maximum: 5
          example: 5
        user:
          $ref: '#/components/schemas/User'
        created_at:
          type: string
          format: date-time

    Media:
      type: object
      properties:
        id:
          type: integer
          example: 1
        type:
          type: string
          enum: [image, video, document]
          example: image
        url:
          type: string
          example: "https://example.com/images/center1.jpg"
        thumbnail_url:
          type: string
          nullable: true
          example: "https://example.com/thumbnails/center1.jpg"
        filename:
          type: string
          example: "center1.jpg"
        mime_type:
          type: string
          example: "image/jpeg"
        size:
          type: integer
          example: 1024000
        caption:
          type: string
          nullable: true
          example: "Main entrance of the center"
        alt_text:
          type: string
          nullable: true
          example: "Sunshine Elderly Care Center entrance"

    CreateBookingRequest:
      type: object
      required:
        - center_id
        - booking_date
        - booking_time
        - booking_type
      properties:
        center_id:
          type: integer
          example: 1
        service_id:
          type: integer
          nullable: true
          example: 1
        booking_date:
          type: string
          format: date
          example: "2023-12-25"
        booking_time:
          type: string
          format: time
          example: "10:00:00"
        booking_type:
          type: string
          enum: [visit, consultation, trial_day]
          example: visit
        questionnaire_responses:
          type: object
          nullable: true
          example:
            elderly_age: 75
            medical_conditions: ["diabetes"]
            mobility: "wheelchair"

    BookingResponse:
      type: object
      properties:
        id:
          type: integer
          example: 1
        booking_number:
          type: string
          example: "BK-20231225-0001"
        user:
          $ref: '#/components/schemas/User'
        center:
          $ref: '#/components/schemas/Center'
        service:
          $ref: '#/components/schemas/Service'
          nullable: true
        booking_date:
          type: string
          format: date
          example: "2023-12-25"
        booking_time:
          type: string
          format: time
          example: "10:00:00"
        booking_type:
          type: string
          enum: [visit, consultation, trial_day]
          example: visit
        status:
          type: string
          enum: [pending, confirmed, completed, cancelled, no_show]
          example: pending
        calendly_event_uri:
          type: string
          nullable: true
          example: "https://calendly.com/events/abc123"
        calendly_cancel_url:
          type: string
          nullable: true
          example: "https://calendly.com/cancel/abc123"
        calendly_reschedule_url:
          type: string
          nullable: true
          example: "https://calendly.com/reschedule/abc123"
        questionnaire_responses:
          type: object
          nullable: true
        created_at:
          type: string
          format: date-time

    CentersResponse:
      type: object
      properties:
        data:
          type: array
          items:
            $ref: '#/components/schemas/CenterResponse'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'

    BookingsResponse:
      type: object
      properties:
        data:
          type: array
          items:
            $ref: '#/components/schemas/BookingResponse'
        links:
          $ref: '#/components/schemas/PaginationLinks'
        meta:
          $ref: '#/components/schemas/PaginationMeta'

    PaginationLinks:
      type: object
      properties:
        first:
          type: string
          example: "https://api.example.com/v1/centers?page=1"
        last:
          type: string
          example: "https://api.example.com/v1/centers?page=10"
        prev:
          type: string
          nullable: true
          example: "https://api.example.com/v1/centers?page=4"
        next:
          type: string
          nullable: true
          example: "https://api.example.com/v1/centers?page=6"

    PaginationMeta:
      type: object
      properties:
        current_page:
          type: integer
          example: 5
        from:
          type: integer
          example: 61
        last_page:
          type: integer
          example: 10
        per_page:
          type: integer
          example: 15
        to:
          type: integer
          example: 75
        total:
          type: integer
          example: 150

  responses:
    UnauthorizedError:
      description: Unauthorized
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Unauthorized"
              errors:
                type: object

    NotFoundError:
      description: Resource not found
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Resource not found"
              errors:
                type: object

    ValidationError:
      description: Validation error
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "The given data was invalid."
              errors:
                type: object
                example:
                  email:
                    - "The email field is required."
                  password:
                    - "The password must be at least 8 characters."
```
**Checklist:**
- [ ] Define OpenAPI specification for all endpoints
- [ ] Define request/response schemas
- [ ] Define authentication scheme
- [ ] Define error responses
- [ ] Add examples for all schemas
- [ ] Add pagination schemas
- [ ] Document all query parameters

### 3.8. Queue System & Background Jobs

#### File: `backend/app/Jobs/SendEmailNotification.php`
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericEmail;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 60;
    
    /**
     * The maximum number of exceptions allowed before failing.
     */
    public int $maxExceptions = 3;
    
    protected string $to;
    protected string $subject;
    protected string $view;
    protected array $data;
    
    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $subject, string $view, array $data = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }
    
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Implementation
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement email notification job
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for failures

#### File: `backend/app/Jobs/SendSMSNotification.php`
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Twilio\TwilioService;

class SendSMSNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 60;
    
    /**
     * The maximum number of exceptions allowed before failing.
     */
    public int $maxExceptions = 3;
    
    protected string $to;
    protected string $message;
    
    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $message)
    {
        $this->to = $to;
        $this->message = $message;
    }
    
    /**
     * Execute the job.
     */
    public function handle(TwilioService $twilioService): void
    {
        // Implementation
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement SMS notification job
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for failures

#### File: `backend/app/Jobs/SyncWithMailchimp.php`
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Mailchimp\MailchimpService;
use App\Models\Subscription;

class SyncWithMailchimp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 300; // 5 minutes
    
    /**
     * The maximum number of exceptions allowed before failing.
     */
    public int $maxExceptions = 3;
    
    protected Subscription $subscription;
    protected string $action; // subscribe, unsubscribe, update
    
    /**
     * Create a new job instance.
     */
    public function __construct(Subscription $subscription, string $action = 'subscribe')
    {
        $this->subscription = $subscription;
        $this->action = $action;
    }
    
    /**
     * Execute the job.
     */
    public function handle(MailchimpService $mailchimpService): void
    {
        // Implementation
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement Mailchimp sync job
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for failures

#### File: `backend/app/Jobs/ProcessAccountDeletion.php`
```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\User\UserService;
use App\Models\User;

class ProcessAccountDeletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1; // Only try once
    
    protected User $user;
    
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    /**
     * Execute the job.
     */
    public function handle(UserService $userService): void
    {
        // Implementation
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement account deletion job
- [ ] Add timeout configuration
- [ ] Add failure handling
- [ ] Add logging for failures

### 3.9. External Service Integrations

#### File: `backend/app/Services/Twilio/TwilioService.php`
```php
<?php

namespace App\Services\Twilio;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioService
{
    protected Client $client;
    protected string $from;
    
    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');
        
        $this->client = new Client($sid, $token);
    }
    
    /**
     * Send an SMS message
     */
    public function sendSMS(string $to, string $message): bool
    {
        // Implementation
    }
    
    /**
     * Send booking confirmation SMS
     */
    public function sendBookingConfirmation(string $to, array $bookingData): bool
    {
        // Implementation
    }
    
    /**
     * Send booking reminder SMS
     */
    public function sendBookingReminder(string $to, array $bookingData): bool
    {
        // Implementation
    }
    
    /**
     * Send booking cancellation SMS
     */
    public function sendBookingCancellation(string $to, array $bookingData): bool
    {
        // Implementation
    }
    
    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Implementation
    }
    
    /**
     * Format phone number to E.164 format
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement SMS sending
- [ ] Implement booking confirmation SMS
- [ ] Implement booking reminder SMS
- [ ] Implement booking cancellation SMS
- [ ] Add phone number validation
- [ ] Add phone number formatting
- [ ] Add error handling and logging

#### File: `backend/app/Services/Mailchimp/MailchimpService.php`
```php
<?php

namespace App\Services\Mailchimp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;

class MailchimpService
{
    protected string $apiKey;
    protected string $serverPrefix;
    protected string $listId;
    
    public function __construct()
    {
        $this->apiKey = config('services.mailchimp.api_key');
        $this->serverPrefix = config('services.mailchimp.server_prefix');
        $this->listId = config('services.mailchimp.list_id');
    }
    
    /**
     * Subscribe a user to the newsletter
     */
    public function subscribe(string $email, array $mergeFields = [], array $tags = []): array
    {
        // Implementation
    }
    
    /**
     * Unsubscribe a user from the newsletter
     */
    public function unsubscribe(string $email): bool
    {
        // Implementation
    }
    
    /**
     * Update subscriber information
     */
    public function updateSubscriber(string $email, array $mergeFields = [], array $tags = []): array
    {
        // Implementation
    }
    
    /**
     * Get subscriber information
     */
    public function getSubscriber(string $email): ?array
    {
        // Implementation
    }
    
    /**
     * Get campaign reports
     */
    public function getCampaignReports(int $limit = 10): array
    {
        // Implementation
    }
    
    /**
     * Create a new campaign
     */
    public function createCampaign(array $campaignData): array
    {
        // Implementation
    }
    
    /**
     * Send a campaign
     */
    public function sendCampaign(string $campaignId): bool
    {
        // Implementation
    }
    
    /**
     * Sync local subscription with Mailchimp
     */
    public function syncSubscription(Subscription $subscription): bool
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Implement newsletter subscription
- [ ] Implement newsletter unsubscription
- [ ] Implement subscriber update
- [ ] Implement subscriber retrieval
- [ ] Implement campaign reporting
- [ ] Implement campaign creation and sending
- [ ] Implement local subscription sync
- [ ] Add error handling and logging

### 3.10. Testing Suite

#### File: `backend/tests/Feature/Auth/AuthTest.php`
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user registration
     */
    public function test_user_can_register(): void
    {
        // Implementation
    }
    
    /**
     * Test user registration with invalid data
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        // Implementation
    }
    
    /**
     * Test user login
     */
    public function test_user_can_login(): void
    {
        // Implementation
    }
    
    /**
     * Test user login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Implementation
    }
    
    /**
     * Test user logout
     */
    public function test_user_can_logout(): void
    {
        // Implementation
    }
    
    /**
     * Test password reset request
     */
    public function test_user_can_request_password_reset(): void
    {
        // Implementation
    }
    
    /**
     * Test password reset
     */
    public function test_user_can_reset_password(): void
    {
        // Implementation
    }
    
    /**
     * Test email verification
     */
    public function test_user_can_verify_email(): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Test user registration
- [ ] Test user registration validation
- [ ] Test user login
- [ ] Test user login validation
- [ ] Test user logout
- [ ] Test password reset request
- [ ] Test password reset
- [ ] Test email verification

#### File: `backend/tests/Feature/Booking/BookingTest.php`
```php
<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $user;
    protected Center $center;
    protected Service $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->center = Center::factory()->create();
        $this->service = Service::factory()->create(['center_id' => $this->center->id]);
    }
    
    /**
     * Test user can view their bookings
     */
    public function test_user_can_view_their_bookings(): void
    {
        // Implementation
    }
    
    /**
     * Test user can create a booking
     */
    public function test_user_can_create_a_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test user cannot create a booking with invalid data
     */
    public function test_user_cannot_create_a_booking_with_invalid_data(): void
    {
        // Implementation
    }
    
    /**
     * Test user can update their booking
     */
    public function test_user_can_update_their_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test user can cancel their booking
     */
    public function test_user_can_cancel_their_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test user cannot view other users' bookings
     */
    public function test_user_cannot_view_other_users_bookings(): void
    {
        // Implementation
    }
    
    /**
     * Test user cannot update other users' bookings
     */
    public function test_user_cannot_update_other_users_bookings(): void
    {
        // Implementation
    }
    
    /**
     * Test user cannot cancel other users' bookings
     */
    public function test_user_cannot_cancel_other_users_bookings(): void
    {
        // Implementation
    }
}
```
**Checklist:**
- [ ] Test booking listing
- [ ] Test booking creation
- [ ] Test booking creation validation
- [ ] Test booking update
- [ ] Test booking cancellation
- [ ] Test authorization for viewing bookings
- [ ] Test authorization for updating bookings
- [ ] Test authorization for cancelling bookings

#### File: `backend/tests/Unit/Services/BookingServiceTest.php`
```php
<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use App\Services\Calendly\CalendlyService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected BookingService $bookingService;
    protected $calendlyServiceMock;
    protected $notificationServiceMock;
    protected $auditServiceMock;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calendlyServiceMock = Mockery::mock(CalendlyService::class);
        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->auditServiceMock = Mockery::mock(AuditService::class);
        
        $this->bookingService = new BookingService(
            $this->calendlyServiceMock,
            $this->notificationServiceMock,
            $this->auditServiceMock
        );
    }
    
    /**
     * Test booking creation
     */
    public function test_can_create_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test booking update
     */
    public function test_can_update_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test booking cancellation
     */
    public function test_can_cancel_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test booking confirmation
     */
    public function test_can_confirm_booking(): void
    {
        // Implementation
    }
    
    /**
     * Test getting available slots
     */
    public function test_can_get_available_slots(): void
    {
        // Implementation
    }
    
    /**
     * Test processing Calendly webhook
     */
    public function test_can_process_calendly_webhook(): void
    {
        // Implementation
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
```
**Checklist:**
- [ ] Test booking creation
- [ ] Test booking update
- [ ] Test booking cancellation
- [ ] Test booking confirmation
- [ ] Test available slots calculation
- [ ] Test Calendly webhook processing
- [ ] Add mock setup for external services

## 4. Implementation Timeline

### Week 1: Foundation & Authentication
- Day 1-2: Set up project structure, models, and relationships
- Day 3-4: Implement authentication system with Sanctum
- Day 5: Implement consent management system
- Day 6-7: Implement audit logging system

### Week 2: Core Services
- Day 1-2: Implement user management system
- Day 3-4: Implement center management system
- Day 5-6: Implement service management system
- Day 7: Implement booking management system

### Week 3: Integrations & Testing
- Day 1-2: Implement Calendly integration
- Day 3: Implement Twilio integration
- Day 4: Implement Mailchimp integration
- Day 5-6: Write comprehensive tests
- Day 7: Code review and optimization

## 5. Success Metrics

1. **Code Quality**
   - All services follow SOLID principles
   - Code coverage > 90%
   - All tests passing
   - No code smells or security vulnerabilities

2. **API Quality**
   - All endpoints documented with OpenAPI
   - Consistent response format
   - Proper error handling
   - Rate limiting implemented

3. **PDPA Compliance**
   - All user data changes logged
   - Consent tracking implemented
   - Data export functionality
   - Account deletion with grace period

4. **Performance**
   - API response time < 500ms
   - Database queries optimized
   - Caching implemented where appropriate
   - Queue system for background jobs

## 6. Risk Mitigation

1. **External Service Dependencies**
   - Implement fallback mechanisms
   - Add retry logic with exponential backoff
   - Mock external services in tests

2. **Data Security**
   - Encrypt sensitive data
   - Implement proper authentication
   - Add rate limiting
   - Validate all inputs

3. **Performance Issues**
   - Implement caching strategies
   - Optimize database queries
   - Use queues for heavy operations
   - Monitor performance metrics

## 7. Conclusion

This comprehensive sub-plan provides a detailed roadmap for implementing the Phase 3 backend services for the ElderCare SG platform. The plan follows best practices for Laravel development, ensures PDPA compliance, and provides a solid foundation for the production demo.

The modular architecture allows for independent development and testing of each component, while the comprehensive testing suite ensures reliability and maintainability. The API-first approach with OpenAPI documentation facilitates frontend integration and future development.

By following this plan, the development team can create a robust, secure, and scalable backend that demonstrates the full capabilities of the ElderCare SG platform to stakeholders.
