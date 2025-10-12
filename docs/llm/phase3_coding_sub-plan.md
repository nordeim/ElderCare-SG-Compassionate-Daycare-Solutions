# ElderCare SG — Enhanced Phase 3 Backend Sub-Plan: Core Services & PDPA Compliance

## 1. Executive Summary

This enhanced sub-plan combines the strengths of both the original Phase 3 sub-plan and the alternative plan, creating a comprehensive, implementation-ready guide for autonomous AI coding agents. It maintains the workstream structure of the original plan while incorporating detailed code examples, implementation patterns, and explicit validation criteria.

### 1.1. Objectives
- Implement complete backend API for ElderCare SG platform covering authentication, center management, booking system, PDPA compliance, and external integrations
- Deliver production-ready codebase with >90% test coverage, comprehensive documentation, and stakeholder demo readiness
- Establish service-oriented architecture with thin controllers, dedicated service classes, repository pattern, and robust error handling
- Provide explicit, unambiguous guidance for autonomous AI implementation

### 1.2. Success Criteria
- ✅ Authentication: Users can register (with consent), login/logout, verify email, reset password via API
- ✅ PDPA Compliance: Consent tracking, audit logging, data export, account deletion with 30-day grace period
- ✅ Center Management: Admins can CRUD centers/services/staff via API with MOH validation
- ✅ Booking System: Users can create bookings, receive confirmations (email/SMS), view history
- ✅ Content Management: FAQs, testimonials (with moderation), contact submissions, newsletter subscriptions
- ✅ API Quality: OpenAPI documentation, rate limiting, consistent error handling, versioned endpoints (/api/v1/)
- ✅ Testing: >90% backend coverage (PHPUnit), all endpoints integration tested, external service mocks
- ✅ Autonomous Implementation: AI agent can implement entire backend without human intervention

### 1.3. Dependencies & Assumptions
- Phase 1 complete: Docker environment, database migrations exist, CI/CD pipeline operational
- Phase 2 complete: Frontend design system ready to consume APIs (parallel development acceptable)
- External service accounts: Calendly, Twilio, Mailchimp, AWS S3 credentials available (or mocked for testing)
- Team: 2 backend developers (Backend Dev 1 + Backend Dev 2) can parallelize workstreams
- Timeline: 12-14 days with parallelization, 19-26 days sequential

## 2. Architecture Blueprint

### 2.1. Service-Layer Pattern
```
HTTP Request → Route → Controller → Request (validation) → Service (business logic) → Repository (data access) → Model → Database
                ↓                                              ↓
              Middleware                                   Events/Jobs (audit, notifications)
                                                              ↓
                                                         Resource (response transform)
```

### 2.2. Directory Structure
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/          # Versioned API controllers
│   │   ├── Middleware/                   # Custom middleware (rate limiting, PDPA)
│   │   ├── Requests/                     # Form request validation
│   │   └── Resources/                    # API response transformers
│   ├── Models/                           # Eloquent models (18 tables)
│   ├── Services/                         # Business logic layer
│   │   ├── Auth/
│   │   ├── Booking/
│   │   ├── Center/
│   │   ├── PDPA/
│   │   └── Integration/                  # Calendly, Twilio, Mailchimp
│   ├── Repositories/                     # Data access layer
│   ├── Events/                           # Domain events
│   ├── Listeners/                        # Event handlers (audit logging)
│   ├── Jobs/                             # Queue jobs (email, SMS, sync)
│   ├── Policies/                         # Authorization policies
│   └── Exceptions/                       # Custom exceptions
├── config/                               # Configuration files
├── database/
│   ├── factories/                        # Model factories (testing)
│   ├── migrations/                       # Database migrations (already exist)
│   └── seeders/                          # Database seeders
├── routes/
│   └── api.php                           # API routes (versioned)
├── tests/
│   ├── Unit/                             # Unit tests (models, services)
│   ├── Feature/                          # Feature tests (API endpoints)
│   └── Integration/                      # Integration tests (external services)
└── storage/api-docs/                     # OpenAPI/Swagger documentation
```

### 2.3. Key Design Decisions
- Thin Controllers: Max 5-7 lines per method (delegate to services)
- Service Layer: All business logic, exception handling, transaction management
- Repository Pattern: Database abstraction, testable data access
- Event-Driven Audit: Model changes trigger events → listeners log to audit_logs
- Queue-Based Notifications: Email/SMS sent via jobs (retry 3x, exponential backoff)
- Polymorphic Relationships: media and content_translations use Eloquent polymorphism
- API Versioning: All routes prefixed /api/v1/, future versions /api/v2/
- Response Format: Consistent JSON structure (data/meta/errors)

## 3. Workstream Breakdown

### Workstream A: Foundation & Authentication (3-4 days)
**Owner:** Backend Dev 1
**Dependencies:** None (Phase 1 migrations exist)
**Priority:** CRITICAL (blocks other workstreams)

#### A.1. Prerequisites & Setup
Before starting this workstream, ensure:
- [ ] Docker environment is running: `docker-compose up -d`
- [ ] Database is migrated: `docker-compose exec backend php artisan migrate`
- [ ] Composer dependencies are installed: `docker-compose exec backend composer install`
- [ ] Environment file is configured: `cp .env.example .env`

#### A.2. Implementation Sequence
1. Create Models (4 files)
2. Create Request Validation Classes (7 files)
3. Create Service Classes (3 files)
4. Create Resource Transformers (3 files)
5. Create Middleware (2 files)
6. Create Controllers (6 files)
7. Create Routes (1 file)
8. Create Tests (2 files minimum)
9. Validate workstream completion

#### A.3. File Creation Matrix

**Models (4 files)**

##### File: `app/Models/User.php`
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
    
    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
    
    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }
    
    /**
     * Get the preferred language name.
     */
    public function getPreferredLanguageNameAttribute(): string
    {
        $languages = [
            'en' => 'English',
            'zh' => 'Mandarin',
            'ms' => 'Malay',
            'ta' => 'Tamil',
        ];
        
        return $languages[$this->preferred_language] ?? 'English';
    }
    
    /**
     * Set the password attribute.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
```
**Checklist:**
- [ ] Sanctum HasApiTokens trait
- [ ] Soft deletes
- [ ] HasFactory trait
- [ ] Relationships: profile(), consents(), bookings(), testimonials()
- [ ] Scopes: active(), admins()
- [ ] Accessors: getPreferredLanguageNameAttribute()
- [ ] Password hashing mutator
- [ ] Consent checking methods

##### File: `app/Models/Profile.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'birth_date',
        'address',
        'city',
        'postal_code',
        'country',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'birth_date' => 'date',
    ];
    
    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the full address as a single string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }
}
```
**Checklist:**
- [ ] Belongs to User
- [ ] HasFactory trait
- [ ] Fillable attributes
- [ ] Date casting for birth_date
- [ ] Accessor for full address

##### File: `app/Models/Consent.php`
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
    
    /**
     * Scope a query to only include active consents.
     */
    public function scopeActive($query)
    {
        return $query->where('consent_given', true);
    }
}
```
**Checklist:**
- [ ] Belongs to User
- [ ] HasFactory trait
- [ ] Fillable attributes
- [ ] Casts: consent_given as boolean
- [ ] Scopes: ofType(), given(), withdrawn(), active()

##### File: `app/Models/PasswordResetToken.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;
    
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'email';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'token',
    ];
}
```
**Checklist:**
- [ ] No timestamps
- [ ] Primary key: email
- [ ] Token hashing (handled in service)

**Request Validation Classes (7 files)**

##### File: `app/Http/Requests/Auth/RegisterRequest.php`
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
- [ ] Validate: name, email (unique), password (min 8 chars), phone (Singapore format)
- [ ] Validate consent checkboxes (account, marketing)
- [ ] Custom error messages
- [ ] Password confirmation validation

##### File: `app/Http/Requests/Auth/LoginRequest.php`
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
    
    /**
     * Attempt to authenticate the request's credentials.
     */
    public function authenticate(): bool
    {
        return Auth::attempt($this->only('email', 'password'));
    }
}
```
**Checklist:**
- [ ] Validate: email (exists), password
- [ ] Custom error messages
- [ ] Authentication method

##### File: `app/Http/Requests/Auth/EmailVerificationRequest.php`
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Events\Verified;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!hash_equals((string) $this->route('id'), (string) $this->user()->getKey())) {
            return false;
        }
        
        if (!hash_equals((string) $this->route('hash'), sha1($this->user()->getEmailForVerification()))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Fulfill the email verification request.
     */
    public function fulfill(): void
    {
        if (!$this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();
            
            event(new Verified($this->user()));
        }
    }
}
```
**Checklist:**
- [ ] Validate signature, expiration
- [ ] Authorization logic
- [ ] Email verification fulfillment

##### File: `app/Http/Requests/Auth/ForgotPasswordRequest.php`
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'exists:users'],
        ];
    }
}
```
**Checklist:**
- [ ] Validate: email (exists)
- [ ] Simple validation rules

##### File: `app/Http/Requests/Auth/ResetPasswordRequest.php`
```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
```
**Checklist:**
- [ ] Validate: token, email, password (confirmed, min 8 chars)
- [ ] Password strength validation

##### File: `app/Http/Requests/Profile/UpdateProfileRequest.php`
```php
<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'regex:/^(\+65)?[689]\d{7}$/'],
            'bio' => ['sometimes', 'string', 'max:1000'],
            'birth_date' => ['sometimes', 'date', 'before:today'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'string', 'regex:/^[0-9]{6}$/'],
            'country' => ['sometimes', 'string', 'max:100'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid Singapore number (e.g., +6581234567 or 81234567).',
            'postal_code.regex' => 'The postal code must be a 6-digit Singapore postal code.',
            'birth_date.before' => 'The birth date must be a date before today.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: name, phone, bio, birth_date, address, city, postal_code
- [ ] Optional fields (sometimes)
- [ ] Singapore postal code format (6 digits)
- [ ] Custom error messages

**Service Classes (3 files)**

##### File: `app/Services/Auth/AuthService.php`
```php
<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Profile;
use App\Models\Consent;
use App\Services\Consent\ConsentService;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        return DB::transaction(function () use ($userData, $consents) {
            // Create user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'password' => $userData['password'],
                'preferred_language' => $userData['preferred_language'],
            ]);
            
            // Create profile
            Profile::create([
                'user_id' => $user->id,
            ]);
            
            // Record consents
            foreach ($consents as $consent) {
                $this->consentService->recordConsent(
                    $user,
                    $consent['type'],
                    $consent['given'],
                    $consent['text'],
                    $consent['version']
                );
            }
            
            // Log audit
            $this->auditService->logCreate($user);
            
            // Trigger event
            event(new Registered($user));
            
            return $user;
        });
    }
    
    /**
     * Authenticate user credentials
     */
    public function authenticate(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();
        
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        
        return $user;
    }
    
    /**
     * Generate API token for user
     */
    public function generateToken(User $user, string $tokenName = 'api-token'): string
    {
        // Revoke existing tokens
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken($tokenName);
        
        // Log audit
        $this->auditService->log(
            $user,
            $user,
            'token_created',
            null,
            ['token_name' => $tokenName]
        );
        
        return $token->plainTextToken;
    }
    
    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
        
        // Log audit
        $this->auditService->log(
            $user,
            $user,
            'tokens_revoked',
            null,
            null
        );
    }
    
    /**
     * Send email verification notification
     */
    public function sendEmailVerification(User $user): void
    {
        $user->sendEmailVerificationNotification();
        
        // Log audit
        $this->auditService->log(
            $user,
            $user,
            'verification_sent',
            null,
            null
        );
    }
    
    /**
     * Send password reset notification
     */
    public function sendPasswordReset(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);
        
        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
        
        // Log audit (without user reference)
        $this->auditService->log(
            null,
            new class {
                public $table = 'password_resets';
                public $id = $email;
            },
            'password_reset_requested',
            null,
            ['email' => $email]
        );
        
        return $status;
    }
    
    /**
     * Reset password
     */
    public function resetPassword(array $resetData): User
    {
        return DB::transaction(function () use ($resetData) {
            $status = Password::reset($resetData, function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                
                // Log audit
                $this->auditService->log(
                    $user,
                    $user,
                    'password_reset',
                    ['password' => 'old_password'],
                    ['password' => 'new_password']
                );
                
                // Trigger event
                event(new PasswordReset($user));
            });
            
            if ($status !== Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                ]);
            }
            
            $user = User::where('email', $resetData['email'])->first();
            
            return $user;
        });
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
- [ ] Exception handling

##### File: `app/Services/Auth/ProfileService.php`
```php
<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Profile;
use App\Services\Media\MediaService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    protected MediaService $mediaService;
    protected AuditService $auditService;
    
    public function __construct(MediaService $mediaService, AuditService $auditService)
    {
        $this->mediaService = $mediaService;
        $this->auditService = $auditService;
    }
    
    /**
     * Get user profile with relationships
     */
    public function getProfile(User $user): Profile
    {
        return $user->profile()->with('user')->firstOrFail();
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            $profile = $user->profile;
            $oldValues = $profile->toArray();
            
            // Update user fields
            if (isset($data['name'])) {
                $user->name = $data['name'];
                $user->save();
                unset($data['name']);
            }
            
            if (isset($data['phone'])) {
                $user->phone = $data['phone'];
                $user->save();
                unset($data['phone']);
            }
            
            // Update profile fields
            $profile->update($data);
            
            // Log audit
            $this->auditService->logUpdate(
                $user,
                $profile,
                $oldValues,
                $profile->toArray()
            );
            
            return $profile->fresh();
        });
    }
    
    /**
     * Upload avatar to S3 and update profile
     */
    public function uploadAvatar(User $user, UploadedFile $file): Profile
    {
        return DB::transaction(function () use ($user, $file) {
            $profile = $user->profile;
            
            // Delete old avatar if exists
            if ($profile->avatar) {
                $this->mediaService->deleteByUrl($profile->avatar);
            }
            
            // Upload new avatar
            $path = $this->mediaService->uploadAvatar($file);
            
            // Update profile
            $oldValues = ['avatar' => $profile->avatar];
            $profile->avatar = $path;
            $profile->save();
            
            // Log audit
            $this->auditService->logUpdate(
                $user,
                $profile,
                $oldValues,
                ['avatar' => $path]
            );
            
            return $profile->fresh();
        });
    }
    
    /**
     * Delete avatar from S3 and profile
     */
    public function deleteAvatar(User $user): Profile
    {
        return DB::transaction(function () use ($user) {
            $profile = $user->profile;
            
            if ($profile->avatar) {
                // Delete from S3
                $this->mediaService->deleteByUrl($profile->avatar);
                
                // Update profile
                $oldValues = ['avatar' => $profile->avatar];
                $profile->avatar = null;
                $profile->save();
                
                // Log audit
                $this->auditService->logUpdate(
                    $user,
                    $profile,
                    $oldValues,
                    ['avatar' => null]
                );
            }
            
            return $profile->fresh();
        });
    }
}
```
**Checklist:**
- [ ] Implement profile retrieval with relationships
- [ ] Implement profile update with validation
- [ ] Implement avatar upload to S3
- [ ] Implement avatar deletion
- [ ] Add audit logging for all operations
- [ ] Use database transactions

##### File: `app/Services/Auth/ConsentService.php`
```php
<?php

namespace App\Services\Auth;

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
        $consent = Consent::create([
            'user_id' => $user->id,
            'consent_type' => $consentType,
            'consent_given' => $consentGiven,
            'consent_text' => $consentText,
            'consent_version' => $consentVersion,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
        
        // Log audit
        $this->auditService->logCreate($consent);
        
        return $consent;
    }
    
    /**
     * Update multiple consents at once
     */
    public function updateConsents(User $user, array $consents): array
    {
        $createdConsents = [];
        
        foreach ($consents as $consent) {
            $createdConsents[] = $this->recordConsent(
                $user,
                $consent['type'],
                $consent['given'],
                $consent['text'],
                $consent['version']
            );
        }
        
        return $createdConsents;
    }
    
    /**
     * Get user's current consents
     */
    public function getUserConsents(User $user): array
    {
        return $user->consents()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('consent_type')
            ->map(function ($group) {
                return $group->first();
            })
            ->toArray();
    }
    
    /**
     * Check if user has given consent for a specific type
     */
    public function hasUserGivenConsent(User $user, string $consentType): bool
    {
        return $user->hasGivenConsent($consentType);
    }
    
    /**
     * Withdraw consent
     */
    public function withdrawConsent(User $user, string $consentType): Consent
    {
        return $this->recordConsent(
            $user,
            $consentType,
            false,
            'User withdrew consent',
            '1.0'
        );
    }
}
```
**Checklist:**
- [ ] Implement consent recording with IP tracking
- [ ] Implement bulk consent update
- [ ] Implement consent retrieval
- [ ] Implement consent checking
- [ ] Implement consent withdrawal
- [ ] Add audit logging for consent changes

**Resource Transformers (3 files)**

##### File: `app/Http/Resources/UserResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'preferred_language' => $this->preferred_language,
            'preferred_language_name' => $this->preferred_language_name,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'profile' => ProfileResource::make($this->whenLoaded('profile')),
        ];
    }
}
```
**Checklist:**
- [ ] Transform: id, name, email, phone, role, preferred_language, email_verified_at
- [ ] Include profile (when loaded)
- [ ] Exclude sensitive fields (password, remember_token)
- [ ] Add preferred_language_name accessor

##### File: `app/Http/Resources/ProfileResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar ? url($this->avatar) : null,
            'bio' => $this->bio,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all profile fields
- [ ] Include avatar URL (full URL)
- [ ] Include full address accessor

##### File: `app/Http/Resources/ConsentResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'consent_type' => $this->consent_type,
            'consent_given' => $this->consent_given,
            'consent_text' => $this->consent_text,
            'consent_version' => $this->consent_version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```
**Checklist:**
- [ ] Transform: id, consent_type, consent_given, consent_version, created_at
- [ ] Include consent_text for transparency

**Middleware (2 files)**

##### File: `app/Http/Middleware/EnsureEmailIsVerified.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user && !$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'errors' => [
                    'email' => ['Please verify your email address.'],
                ],
            ], 403);
        }
        
        return $next($request);
    }
}
```
**Checklist:**
- [ ] Check if user's email_verified_at is not null
- [ ] Return 403 error if not verified
- [ ] JSON error response format

##### File: `app/Http/Middleware/CheckRole.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'errors' => [
                    'role' => ['You do not have permission to access this resource.'],
                ],
            ], 403);
        }
        
        return $next($request);
    }
}
```
**Checklist:**
- [ ] Check user role against allowed roles
- [ ] Return 403 if role not allowed
- [ ] Support multiple roles
- [ ] JSON error response format

**Controllers (6 files)**

##### File: `app/Http/Controllers/Api/V1/AuthController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use App\Http\Resources\UserResource;
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
        $user = $this->authService->register(
            $request->validated(),
            $request->input('consents')
        );
        
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ], 201);
    }
    
    /**
     * Authenticate user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!$request->authenticate()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 401);
        }
        
        $user = $request->user();
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ]);
    }
    
    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeAllTokens($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
        ]);
    }
    
    /**
     * Refresh authentication token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ]);
    }
    
    /**
     * Get authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => UserResource::make($request->user()->load('profile')),
            ],
        ]);
    }
    
    /**
     * Send email verification
     */
    public function sendVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 400);
        }
        
        $this->authService->sendEmailVerification($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }
    
    /**
     * Verify email
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();
        
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }
    
    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordReset($request->input('email'));
        
        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }
    
    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->authService->resetPassword($request->validated());
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ]);
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

##### File: `app/Http/Controllers/Api/V1/ProfileController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Auth\ProfileService;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected ProfileService $profileService;
    
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }
    
    /**
     * Get user profile
     */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());
        
        return response()->json([
            'success' => true,
            'data' => [
                'profile' => ProfileResource::make($profile),
            ],
        ]);
    }
    
    /**
     * Update user profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'profile' => ProfileResource::make($profile),
            ],
        ]);
    }
    
    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
        ]);
        
        $profile = $this->profileService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully.',
            'data' => [
                'profile' => ProfileResource::make($profile),
            ],
        ]);
    }
    
    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $profile = $this->profileService->deleteAvatar($request->user());
        
        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted successfully.',
            'data' => [
                'profile' => ProfileResource::make($profile),
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement profile retrieval with relationships
- [ ] Implement profile update with validation
- [ ] Implement avatar upload with validation
- [ ] Implement avatar deletion
- [ ] Add authorization checks
- [ ] Add audit logging

##### File: `app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();
        
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ]);
    }
    
    /**
     * Resend the email verification notification.
     */
    public function resend(\Illuminate\Http\Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 400);
        }
        
        $request->user()->sendEmailVerificationNotification();
        
        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }
}
```
**Checklist:**
- [ ] Implement email verification
- [ ] Implement resend verification
- [ ] Add validation for already verified emails

##### File: `app/Http/Controllers/Api/V1/Auth/PasswordResetController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordReset($request->input('email'));
        
        return response()->json([
            'success' => true,
            'message' => __($status),
        ]);
    }
    
    /**
     * Reset the given user's password.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->authService->resetPassword($request->validated());
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement password reset request
- [ ] Implement password reset
- [ ] Add token generation after reset

##### File: `app/Http/Controllers/Api/V1/Auth/LoginController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!$request->authenticate()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 401);
        }
        
        $user = $request->user();
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement login with validation
- [ ] Generate API token
- [ ] Return user and token

##### File: `app/Http/Controllers/Api/V1/Auth/RegisterController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    
    /**
     * Handle an incoming registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            $request->validated(),
            $request->input('consents')
        );
        
        $token = $this->authService->generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data' => [
                'user' => UserResource::make($user),
                'token' => $token,
            ],
        ], 201);
    }
}
```
**Checklist:**
- [ ] Implement registration with validation
- [ ] Create user and profile
- [ ] Record consents
- [ ] Generate API token
- [ ] Return user and token

**Routes (1 file)**

##### File: `routes/api.php` (AUTH section)
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes - Authentication
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1/auth')->group(function () {
    // Registration
    Route::post('register', [RegisterController::class, 'register']);
    
    // Login
    Route::post('login', [LoginController::class, 'login']);
    
    // Email verification
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    
    Route::post('email/verify/resend', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Password reset
    Route::post('password/forgot', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:6,1');
    
    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->name('password.reset');
});

// Protected routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Authentication
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/email/verify/resend', [AuthController::class, 'sendVerification']);
    
    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar']);
});
```
**Checklist:**
- [ ] POST /api/v1/auth/register
- [ ] POST /api/v1/auth/login
- [ ] POST /api/v1/auth/logout (protected)
- [ ] POST /api/v1/auth/refresh (protected)
- [ ] GET /api/v1/auth/me (protected)
- [ ] POST /api/v1/auth/email/verify/resend (protected)
- [ ] GET /api/v1/auth/email/verify/{id}/{hash} (signed)
- [ ] POST /api/v1/auth/password/forgot
- [ ] POST /api/v1/auth/password/reset
- [ ] GET /api/v1/profile (protected)
- [ ] PUT /api/v1/profile (protected)
- [ ] POST /api/v1/profile/avatar (protected)
- [ ] DELETE /api/v1/profile/avatar (protected)

**Tests (2 files minimum)**

##### File: `tests/Feature/Auth/AuthenticationTest.php`
```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'preferred_language' => 'en',
            'consents' => [
                [
                    'type' => 'account',
                    'given' => true,
                    'text' => 'I consent to account processing',
                    'version' => '1.0',
                ],
            ],
        ];
        
        $response = $this->postJson('/api/v1/auth/register', $userData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'preferred_language',
                        'created_at',
                    ],
                    'token',
                ],
            ]);
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $this->assertDatabaseHas('profiles', [
            'user_id' => 1,
        ]);
        
        $this->assertDatabaseHas('consents', [
            'user_id' => 1,
            'consent_type' => 'account',
            'consent_given' => true,
        ]);
    }
    
    /**
     * Test user cannot register with invalid data
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
            'preferred_language' => 'invalid',
            'consents' => [],
        ];
        
        $response = $this->postJson('/api/v1/auth/register', $userData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'preferred_language', 'consents']);
    }
    
    /**
     * Test user can login with correct credentials
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        
        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];
        
        $response = $this->postJson('/api/v1/auth/login', $loginData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);
    }
    
    /**
     * Test user cannot login with wrong password
     */
    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        
        $loginData = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];
        
        $response = $this->postJson('/api/v1/auth/login', $loginData);
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
    }
    
    /**
     * Test user can logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/auth/logout');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful.',
            ]);
        
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
    
    /**
     * Test user can request password reset
     */
    public function test_user_can_request_password_reset(): void
    {
        Notification::fake();
        
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $user->email,
        ]);
        
        $response->assertStatus(200);
        
        Notification::assertSentTo($user, ResetPassword::class);
    }
    
    /**
     * Test user can reset password
     */
    public function test_user_can_reset_password(): void
    {
        Notification::fake();
        
        $user = User::factory()->create();
        
        // Request password reset
        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => $user->email,
        ]);
        
        // Get the reset token from the notification
        $notification = Notification::sent($user, ResetPassword::class)->first();
        $resetToken = $notification->token;
        
        // Reset password
        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                ],
            ]);
        
        // Verify password was changed
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
    
    /**
     * Test user can verify email
     */
    public function test_user_can_verify_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        
        $verificationUrl = $user->verificationUrl();
        
        $response = $this->getJson($verificationUrl);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully.',
            ]);
        
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
```
**Checklist:**
- [ ] Test: user can register with valid data
- [ ] Test: registration requires consent
- [ ] Test: user can login with correct credentials
- [ ] Test: user cannot login with wrong password
- [ ] Test: user can logout
- [ ] Test: user can request password reset
- [ ] Test: user can reset password
- [ ] Test: user can verify email

##### File: `tests/Feature/Profile/ProfileTest.php`
```php
<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user can view own profile
     */
    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/v1/profile');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile' => [
                        'id',
                        'avatar',
                        'bio',
                        'birth_date',
                        'address',
                        'city',
                        'postal_code',
                        'country',
                        'full_address',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }
    
    /**
     * Test user can update profile
     */
    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $profileData = [
            'name' => 'Updated Name',
            'phone' => '+6581234567',
            'bio' => 'Updated bio',
            'birth_date' => '1990-01-01',
            'address' => '123 Updated Street',
            'city' => 'Updated City',
            'postal_code' => '123456',
            'country' => 'Singapore',
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->putJson('/api/v1/profile', $profileData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'profile',
                ],
            ]);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+6581234567',
        ]);
        
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'bio' => 'Updated bio',
            'birth_date' => '1990-01-01',
            'address' => '123 Updated Street',
            'city' => 'Updated City',
            'postal_code' => '123456',
            'country' => 'Singapore',
        ]);
    }
    
    /**
     * Test user can upload avatar
     */
    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('avatars');
        
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $file = UploadedFile::fake()->image('avatar.jpg');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'profile',
                ],
            ]);
        
        $this->assertNotNull($user->profile->fresh()->avatar);
        Storage::disk('avatars')->assertExists($user->profile->fresh()->avatar);
    }
    
    /**
     * Test user can delete avatar
     */
    public function test_user_can_delete_avatar(): void
    {
        Storage::fake('avatars');
        
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Upload avatar first
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/profile/avatar', [
            'avatar' => $file,
        ]);
        
        // Delete avatar
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->deleteJson('/api/v1/profile/avatar');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'profile',
                ],
            ]);
        
        $this->assertNull($user->profile->fresh()->avatar);
    }
    
    /**
     * Test guest cannot access profile
     */
    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');
        
        $response->assertStatus(401);
    }
}
```
**Checklist:**
- [ ] Test: user can view own profile
- [ ] Test: user can update profile
- [ ] Test: user can upload avatar
- [ ] Test: user can delete avatar
- [ ] Test: guest cannot access profile

#### A.4. Workstream Validation

After completing all files in this workstream, validate:

1. **Code Quality**
   - [ ] All files created as per matrix
   - [ ] All features/functions implemented per checklist
   - [ ] No lint errors: `docker-compose exec backend ./vendor/bin/phpstan analyse`
   - [ ] Code follows PSR-12 standards

2. **Testing**
   - [ ] Unit tests written and passing (>90% coverage)
   - [ ] Feature tests written and passing
   - [ ] Run tests: `docker-compose exec backend php artisan test --coverage --min=90`

3. **Functionality**
   - [ ] User can register with consent
   - [ ] User can login/logout
   - [ ] User can verify email
   - [ ] User can reset password
   - [ ] User can manage profile
   - [ ] API tokens work correctly

4. **Documentation**
   - [ ] All methods documented with PHPDoc
   - [ ] API endpoints documented in OpenAPI
   - [ ] Code reviewed by peer

5. **Security**
   - [ ] Passwords are hashed
   - [ ] API tokens are secure
   - [ ] Input validation works
   - [ ] Rate limiting implemented

## 4. Autonomous Implementation Guide for AI Agents

### 4.1. Prerequisites
Before starting implementation, ensure:

1. **Environment Setup**
   ```bash
   # Clone repository
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   
   # Set up environment
   cp .env.example .env
   cp frontend/.env.local.example frontend/.env.local
   
   # Start Docker
   docker-compose up -d
   
   # Install dependencies
   docker-compose exec backend composer install
   docker-compose exec backend npm install
   
   # Run migrations
   docker-compose exec backend php artisan migrate
   ```

2. **Verify Setup**
   ```bash
   # Check backend is running
   curl http://localhost:8000/api/health
   
   # Check database connection
   docker-compose exec backend php artisan tinker
   >>> DB::connection()->getPdo();
   ```

### 4.2. Implementation Workflow

For each workstream:

1. **Read and Understand**
   - Review workstream description
   - Understand dependencies
   - Review file matrix

2. **Create Files in Order**
   - Models first (establish data layer)
   - Repositories next (data access abstraction)
   - Services next (business logic)
   - Controllers next (API endpoints)
   - Requests (validation)
   - Resources (response formatting)
   - Jobs/Events/Listeners (async workflows)
   - Middleware (cross-cutting concerns)
   - Routes (expose endpoints)
   - Tests last (validate everything)

3. **Implementation Steps**
   - Create file with exact path and name
   - Implement all features in checklist
   - Run tests after each file: `docker-compose exec backend php artisan test`
   - Commit frequently with descriptive messages
   - Validate workstream completion

4. **Key Commands**
   ```bash
   # Create model with migration, factory, seeder
   docker-compose exec backend php artisan make:model Center -mfs
   
   # Create controller (API resource)
   docker-compose exec backend php artisan make:controller Api/V1/CenterController --api --resource
   
   # Create request (validation)
   docker-compose exec backend php artisan make:request Center/StoreCenterRequest
   
   # Create resource (API transformer)
   docker-compose exec backend php artisan make:resource CenterResource
   
   # Create service (custom class)
   mkdir -p app/Services/Center
   touch app/Services/Center/CenterService.php
   
   # Create repository (custom class)
   mkdir -p app/Repositories
   touch app/Repositories/CenterRepository.php
   
   # Create job
   docker-compose exec backend php artisan make:job Booking/SendBookingConfirmationJob
   
   # Create event
   docker-compose exec backend php artisan make:event BookingCreated
   
   # Create listener
   docker-compose exec backend php artisan make:listener Booking/BookingCreatedListener --event=BookingCreated
   
   # Create middleware
   docker-compose exec backend php artisan make:middleware LogAuditTrail
   
   # Create policy
   docker-compose exec backend php artisan make:policy CenterPolicy --model=Center
   
   # Run tests
   docker-compose exec backend php artisan test --coverage --min=90
   
   # Run linting
   docker-compose exec backend ./vendor/bin/phpstan analyse
   
   # Generate API documentation
   docker-compose exec backend php artisan l5-swagger:generate
   ```

### 4.3. Testing Strategy

For each feature:

1. **Write Failing Test First** (TDD approach)
2. **Implement Feature**
3. **Run Test to Verify It Passes**
4. **Refactor if Needed**

### 4.4. Error Handling and Troubleshooting

1. **Common Issues**
   - Database connection errors: Check .env configuration
   - Permission errors: Check file permissions
   - Test failures: Check error messages and fix implementation

2. **Debugging Commands**
   ```bash
   # Check logs
   docker-compose logs backend
   
   # Enter container
   docker-compose exec backend bash
   
   # Clear cache
   docker-compose exec backend php artisan cache:clear
   docker-compose exec backend php artisan config:clear
   docker-compose exec backend php artisan route:clear
   ```

### 4.5. Validation Checkpoints

After each workstream:

1. **Run All Tests**
   ```bash
   docker-compose exec backend php artisan test --coverage --min=90
   ```

2. **Check Code Quality**
   ```bash
   docker-compose exec backend ./vendor/bin/phpstan analyse
   ```

3. **Verify API Endpoints**
   ```bash
   # Import Postman collection and run tests
   # Or use curl commands to test endpoints
   ```

4. **Generate Documentation**
   ```bash
   docker-compose exec backend php artisan l5-swagger:generate
   ```

## 5. Parallelization Strategy (12-14 Days)

| Days | Backend Dev 1 | Backend Dev 2 | Deliverable Milestones |
|------|---------------|---------------|-----------------------|
| 1-3  | Workstream A: Auth & User Management | Workstream C: Center Models & Repositories | Auth API functional, Center models ready |
| 4-6  | Workstream C: Center CRUD APIs | Workstream B: PDPA Compliance | Center management demo-ready, PDPA systems active |
| 7-10 | Workstream E: Content & Community | Workstream D: Booking System | FAQ/Testimonials live, Booking flow complete |
| 11-12 | Workstream G: Testing (Auth, Centers, Content) | Workstream F: API Infrastructure | API docs published, rate limiting active |
| 13-14 | Workstream G: Final QA & Bug Fixes | Workstream G: Integration Tests & Demo Data | >90% coverage, demo database seeded |

## 6. Risk Mitigation

| Risk | Impact | Probability | Mitigation | Owner |
|------|--------|-------------|------------|-------|
| External service API changes | High | Medium | Abstraction layer via services, Mock all external calls in tests, Version pin SDKs | Backend Dev 2 |
| Database migration errors | High | Low | Test migrations on staging, Backup production before deploy, Rollback plan documented | Backend Dev 1 |
| PDPA compliance gaps | Critical | Low | Legal review of consent flows, Audit all data export/deletion logic, Penetration testing | Backend Dev 1 + Legal |
| Performance issues under load | Medium | Medium | Load testing with k6 (1000 concurrent users), Query optimization, Redis caching | Backend Dev 2 |
| Incomplete test coverage | Medium | Medium | Enforce >90% coverage in CI, Code review checklist includes tests, Automated coverage reports | Both Devs |

## 7. Stakeholder Demo Script

### 7.1. Demo Flow (15-20 minutes)

1. **Authentication & PDPA Compliance (3 min)**
   - Register new user with consent capture
   - Show consent tracking in database
   - Export user data (JSON download)
   - Delete account (show soft delete + 30-day grace)

2. **Center Management (3 min)**
   - Show admin panel (Laravel Nova or API)
   - Create new center with MOH validation
   - Upload center photos
   - Create service for center
   - Add staff member

3. **Booking System (5 min)**
   - User browses centers
   - User creates booking (show questionnaire)
   - Show Calendly event creation (mocked or real)
   - Show confirmation email + SMS (mocked)
   - Show booking in user dashboard
   - Cancel booking (show cancellation flow)

4. **Content & Community (3 min)**
   - User submits testimonial
   - Admin moderates testimonial (approve)
   - User subscribes to newsletter (show Mailchimp sync)
   - User submits contact form

5. **API & Infrastructure (3 min)**
   - Show OpenAPI documentation (Swagger UI)
   - Demonstrate rate limiting (trigger 429 response)
   - Show error handling (404, 422, 500 responses)
   - Show audit logs for all actions

6. **Q&A (3 min)**

### 7.2. Demo Preparation

1. Seed database with realistic demo data
2. Prepare Postman collection with all endpoints
3. Set up staging environment with SSL
4. Configure external services (or use mocks)
5. Prepare slides showing architecture diagram
6. Record demo video as backup

## 8. Conclusion

This enhanced sub-plan provides a comprehensive, implementation-ready guide for autonomous AI coding agents to implement the ElderCare SG Phase 3 backend. It combines the workstream structure of the original plan with detailed code examples and implementation patterns, creating a robust foundation for successful implementation.

The plan includes explicit validation criteria, troubleshooting guidance, and a clear workflow that enables an AI agent to work autonomously without ambiguity. By following this plan, the development team can create a robust, secure, and scalable backend that demonstrates the full capabilities of the ElderCare SG platform to stakeholders.
