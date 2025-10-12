

# ElderCare SG — Enhanced Phase 3 Backend Sub-Plan: Workstreams B-G

## Workstream B: PDPA Compliance Core (2-3 days)

**Owner:** Backend Dev 2
**Dependencies:** Workstream A (user authentication)
**Priority:** CRITICAL (legal requirement)

### B.1. Prerequisites & Setup
Before starting this workstream, ensure:
- [ ] Workstream A is complete and all tests pass
- [ ] User authentication system is functional
- [ ] Database is migrated with all tables
- [ ] Environment file is configured with audit settings

### B.2. Implementation Sequence
1. Create Models (2 files)
2. Create Service Classes (4 files)
3. Create Jobs (2 files)
4. Create Middleware (2 files)
5. Create Listeners (3 files)
6. Create Controllers (2 files)
7. Create Routes (1 file)
8. Create Tests (2 files minimum)
9. Validate workstream completion

### B.3. File Creation Matrix

**Models (2 files)**

##### File: `app/Models/AuditLog.php`
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
    
    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement user relationship
- [ ] Implement polymorphic auditable relationship
- [ ] Add scopes for action, date range, and user filtering

##### File: `app/Models/DataExportRequest.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExportRequest extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'status',
        'expires_at',
        'download_url',
        'requested_at',
        'completed_at',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    /**
     * Get the user that requested the export.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Scope a query to only include expired requests.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
    
    /**
     * Check if the export is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
    
    /**
     * Check if the export is ready for download.
     */
    public function isReady(): bool
    {
        return $this->status === 'completed' && !$this->isExpired();
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement user relationship
- [ ] Add scopes for status filtering
- [ ] Add helper methods for status checks

**Service Classes (4 files)**

##### File: `app/Services/PDPA/AuditService.php`
```php
<?php

namespace App\Services\PDPA;

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
        return AuditLog::create([
            'user_id' => $user?->id,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }
    
    /**
     * Log model creation
     */
    public function logCreated(?User $user, Model $model): AuditLog
    {
        return $this->log($user, $model, 'created', null, $model->toArray());
    }
    
    /**
     * Log model update
     */
    public function logUpdated(?User $user, Model $model, array $oldValues, array $newValues): AuditLog
    {
        return $this->log($user, $model, 'updated', $oldValues, $newValues);
    }
    
    /**
     * Log model deletion
     */
    public function logDeleted(?User $user, Model $model): AuditLog
    {
        return $this->log($user, $model, 'deleted', $model->toArray(), null);
    }
    
    /**
     * Log model restoration
     */
    public function logRestored(?User $user, Model $model): AuditLog
    {
        return $this->log($user, $model, 'restored', null, $model->toArray());
    }
    
    /**
     * Get audit logs for a model
     */
    public function getAuditLogs(Model $model, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get audit logs for a user
     */
    public function getUserAuditLogs(User $user, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('user_id', $user->id)
            ->with('auditable')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Clean up old audit logs (retention policy)
     */
    public function cleanupOldLogs(int $retentionDays = 2555): int // 7 years
    {
        $cutoffDate = now()->subDays($retentionDays);
        
        return AuditLog::where('created_at', '<', $cutoffDate)->delete();
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

##### File: `app/Services/PDPA/DataExportService.php`
```php
<?php

namespace App\Services\PDPA;

use App\Models\User;
use App\Models\DataExportRequest;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DataExportService
{
    protected MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Request data export for a user
     */
    public function requestExport(User $user): DataExportRequest
    {
        return DataExportRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'requested_at' => now(),
        ]);
    }
    
    /**
     * Process data export request
     */
    public function processExport(DataExportRequest $exportRequest): DataExportRequest
    {
        $user = $exportRequest->user;
        
        // Collect all user data
        $userData = $this->collectUserData($user);
        
        // Generate JSON file
        $filename = "user-data-export-{$user->id}-" . time() . ".json";
        $filePath = "exports/{$filename}";
        
        // Store file
        Storage::disk('private')->put($filePath, json_encode($userData, JSON_PRETTY_PRINT));
        
        // Generate download URL
        $downloadUrl = $this->mediaService->generateTemporaryUrl($filePath, now()->addDays(7));
        
        // Update export request
        $exportRequest->update([
            'status' => 'completed',
            'download_url' => $downloadUrl,
            'completed_at' => now(),
        ]);
        
        return $exportRequest;
    }
    
    /**
     * Collect all user data for export
     */
    protected function collectUserData(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'preferred_language' => $user->preferred_language,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at,
            ],
            'profile' => $user->profile ? [
                'id' => $user->profile->id,
                'avatar' => $user->profile->avatar,
                'bio' => $user->profile->bio,
                'birth_date' => $user->profile->birth_date,
                'address' => $user->profile->address,
                'city' => $user->profile->city,
                'postal_code' => $user->profile->postal_code,
                'country' => $user->profile->country,
                'created_at' => $user->profile->created_at,
                'updated_at' => $user->profile->updated_at,
            ] : null,
            'consents' => $user->consents->map(function ($consent) {
                return [
                    'id' => $consent->id,
                    'consent_type' => $consent->consent_type,
                    'consent_given' => $consent->consent_given,
                    'consent_text' => $consent->consent_text,
                    'consent_version' => $consent->consent_version,
                    'ip_address' => $consent->ip_address,
                    'created_at' => $consent->created_at,
                ];
            })->toArray(),
            'bookings' => $user->bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'center_id' => $booking->center_id,
                    'service_id' => $booking->service_id,
                    'booking_date' => $booking->booking_date,
                    'booking_time' => $booking->booking_time,
                    'booking_type' => $booking->booking_type,
                    'status' => $booking->status,
                    'questionnaire_responses' => $booking->questionnaire_responses,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                ];
            })->toArray(),
            'testimonials' => $user->testimonials->map(function ($testimonial) {
                return [
                    'id' => $testimonial->id,
                    'center_id' => $testimonial->center_id,
                    'title' => $testimonial->title,
                    'content' => $testimonial->content,
                    'rating' => $testimonial->rating,
                    'status' => $testimonial->status,
                    'created_at' => $testimonial->created_at,
                ];
            })->toArray(),
            'contact_submissions' => $user->contactSubmissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'center_id' => $submission->center_id,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'phone' => $submission->phone,
                    'subject' => $submission->subject,
                    'message' => $submission->message,
                    'status' => $submission->status,
                    'created_at' => $submission->created_at,
                ];
            })->toArray(),
            'audit_logs' => $user->auditLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'auditable_type' => $log->auditable_type,
                    'auditable_id' => $log->auditable_id,
                    'action' => $log->action,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at,
                ];
            })->toArray(),
            'export_date' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Get user's export requests
     */
    public function getUserExports(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return DataExportRequest::where('user_id', $user->id)
            ->orderBy('requested_at', 'desc')
            ->get();
    }
    
    /**
     * Clean up expired exports
     */
    public function cleanupExpiredExports(): int
    {
        $expiredExports = DataExportRequest::expired()->get();
        $count = 0;
        
        foreach ($expiredExports as $export) {
            if ($export->download_url) {
                // Extract file path from URL
                $filePath = parse_url($export->download_url, PHP_URL_PATH);
                $filePath = ltrim($filePath, '/');
                
                // Delete file
                Storage::disk('private')->delete($filePath);
            }
            
            $export->delete();
            $count++;
        }
        
        return $count;
    }
}
```
**Checklist:**
- [ ] Implement export request creation
- [ ] Implement export processing with data collection
- [ ] Implement comprehensive data collection for all user-related tables
- [ ] Implement file storage and temporary URL generation
- [ ] Implement export retrieval
- [ ] Implement cleanup of expired exports
- [ ] Exclude sensitive fields (passwords, tokens)

##### File: `app/Services/PDPA/AccountDeletionService.php`
```php
<?php

namespace App\Services\PDPA;

use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountDeletionService
{
    protected AuditService $auditService;
    
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    /**
     * Request account deletion (soft delete)
     */
    public function requestDeletion(User $user, string $reason = null): bool
    {
        return DB::transaction(function () use ($user, $reason) {
            // Soft delete user
            $user->delete();
            
            // Log audit
            $this->auditService->log(
                $user,
                $user,
                'deletion_requested',
                null,
                ['reason' => $reason]
            );
            
            // Schedule permanent deletion job
            ProcessAccountDeletionJob::dispatch($user)
                ->delay(now()->addDays(30));
            
            return true;
        });
    }
    
    /**
     * Cancel account deletion request
     */
    public function cancelDeletion(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Restore user
            $user->restore();
            
            // Log audit
            $this->auditService->log(
                $user,
                $user,
                'deletion_cancelled',
                null,
                null
            );
            
            return true;
        });
    }
    
    /**
     * Permanently delete user account
     */
    public function permanentlyDelete(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Anonymize related data
            $this->anonymizeUserData($user);
            
            // Delete related records
            $user->profile()->delete();
            $user->consents()->delete();
            $user->auditLogs()->delete();
            
            // Permanently delete user
            $user->forceDelete();
            
            return true;
        });
    }
    
    /**
     * Anonymize user data in related tables
     */
    protected function anonymizeUserData(User $user): void
    {
        // Anonymize bookings
        $user->bookings()->update([
            'questionnaire_responses' => null,
        ]);
        
        // Anonymize testimonials
        $user->testimonials()->update([
            'content' => '[Content removed due to account deletion]',
        ]);
        
        // Anonymize contact submissions
        $user->contactSubmissions()->update([
            'name' => '[Name removed due to account deletion]',
            'email' => 'deleted-' . $user->id . '@deleted.local',
            'phone' => null,
            'message' => '[Message removed due to account deletion]',
        ]);
    }
    
    /**
     * Get users pending deletion
     */
    public function getUsersPendingDeletion(): \Illuminate\Database\Eloquent\Collection
    {
        return User::onlyTrashed()
            ->where('deleted_at', '>=', Carbon::now()->subDays(30))
            ->get();
    }
    
    /**
     * Process users ready for permanent deletion
     */
    public function processDeletionQueue(): int
    {
        $usersToDelete = User::onlyTrashed()
            ->where('deleted_at', '<', Carbon::now()->subDays(30))
            ->get();
        
        $count = 0;
        foreach ($usersToDelete as $user) {
            $this->permanentlyDelete($user);
            $count++;
        }
        
        return $count;
    }
}
```
**Checklist:**
- [ ] Implement account deletion request with soft delete
- [ ] Implement deletion request cancellation
- [ ] Implement permanent deletion after grace period
- [ ] Implement data anonymization before deletion
- [ ] Add audit logging for all operations
- [ ] Add transaction support
- [ ] Implement deletion queue processing

##### File: `app/Services/PDPA/ConsentService.php`
```php
<?php

namespace App\Services\PDPA;

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
    
    /**
     * Get privacy policy content
     */
    public function getPrivacyPolicy(string $locale = 'en'): array
    {
        $policies = [
            'en' => [
                'title' => 'Privacy Policy',
                'version' => '1.0',
                'content' => 'We collect and use your personal data in accordance with Singapore PDPA...',
                'last_updated' => '2023-12-01',
            ],
            'zh' => [
                'title' => '隐私政策',
                'version' => '1.0',
                'content' => '我们根据新加坡个人数据保护法收集和使用您的个人数据...',
                'last_updated' => '2023-12-01',
            ],
        ];
        
        return $policies[$locale] ?? $policies['en'];
    }
    
    /**
     * Get cookie policy content
     */
    public function getCookiePolicy(string $locale = 'en'): array
    {
        $policies = [
            'en' => [
                'title' => 'Cookie Policy',
                'version' => '1.0',
                'content' => 'We use cookies to enhance your experience on our platform...',
                'last_updated' => '2023-12-01',
            ],
            'zh' => [
                'title' => 'Cookie 政策',
                'version' => '1.0',
                'content' => '我们使用 Cookie 来增强您在我们平台上的体验...',
                'last_updated' => '2023-12-01',
            ],
        ];
        
        return $policies[$locale] ?? $policies['en'];
    }
    
    /**
     * Get current privacy policy version
     */
    public function getCurrentPrivacyPolicyVersion(): string
    {
        return '1.0';
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
- [ ] Add policy content retrieval
- [ ] Add version tracking for policies

**Jobs (2 files)**

##### File: `app/Jobs/PDPA/ProcessAccountDeletionJob.php`
```php
<?php

namespace App\Jobs\PDPA;

use App\Models\User;
use App\Services\PDPA\AccountDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAccountDeletionJob implements ShouldQueue
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
    public function handle(AccountDeletionService $accountDeletionService): void
    {
        try {
            $accountDeletionService->permanentlyDelete($this->user);
            Log::info('User account permanently deleted', ['user_id' => $this->user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete user account', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
            
            // Re-queue for manual review
            $this->release(3600); // Try again in 1 hour
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Account deletion job failed permanently', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
        
        // Notify admin for manual intervention
        // This could send an email or create a notification
    }
}
```
**Checklist:**
- [ ] Implement account deletion job
- [ ] Add timeout configuration
- [ ] Add failure handling
- [ ] Add logging for success and failure
- [ ] Add retry logic for manual review

##### File: `app/Jobs/PDPA/ProcessDataExportJob.php`
```php
<?php

namespace App\Jobs\PDPA;

use App\Models\DataExportRequest;
use App\Services\PDPA\DataExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 300; // 5 minutes
    
    protected DataExportRequest $exportRequest;
    
    /**
     * Create a new job instance.
     */
    public function __construct(DataExportRequest $exportRequest)
    {
        $this->exportRequest = $exportRequest;
    }
    
    /**
     * Execute the job.
     */
    public function handle(DataExportService $dataExportService): void
    {
        try {
            $dataExportService->processExport($this->exportRequest);
            Log::info('Data export completed', ['export_id' => $this->exportRequest->id]);
        } catch (\Exception $e) {
            Log::error('Failed to process data export', [
                'export_id' => $this->exportRequest->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Re-queue for retry
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Data export job failed permanently', [
            'export_id' => $this->exportRequest->id,
            'error' => $exception->getMessage(),
        ]);
        
        // Update export request status
        $this->exportRequest->update([
            'status' => 'failed',
        ]);
        
        // Notify user of failure
        // This could send an email or notification
    }
}
```
**Checklist:**
- [ ] Implement data export job
- [ ] Add timeout configuration
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for success and failure
- [ ] Update export request status on failure

**Middleware (2 files)**

##### File: `app/Http/Middleware/LogAuditTrail.php`
```php
<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LogAuditTrail
{
    protected AuditService $auditService;
    
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only log for authenticated users making state-changing requests
        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Store audit service in container for later use
            App::instance(AuditService::class, $this->auditService);
        }
        
        return $response;
    }
}
```
**Checklist:**
- [ ] Implement audit trail middleware
- [ ] Only log for authenticated users
- [ ] Only log for state-changing requests
- [ ] Store audit service in container

##### File: `app/Http/Middleware/RequireConsent.php`
```php
<?php

namespace App\Http\Middleware;

use App\Services\PDPA\ConsentService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireConsent
{
    protected ConsentService $consentService;
    
    public function __construct(ConsentService $consentService)
    {
        $this->consentService = $consentService;
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $consentType)
    {
        $user = Auth::user();
        
        if (!$user || !$this->consentService->hasUserGivenConsent($user, $consentType)) {
            return response()->json([
                'success' => false,
                'message' => 'Consent required.',
                'errors' => [
                    'consent' => ["You must provide consent for {$consentType} to continue."],
                ],
            ], 403);
        }
        
        return $next($request);
    }
}
```
**Checklist:**
- [ ] Implement consent requirement middleware
- [ ] Check if user has given specific consent
- [ ] Return 403 error if consent not given
- [ ] Skip for admins

**Listeners (3 files)**

##### File: `app/Listeners/LogModelCreated.php`
```php
<?php

namespace App\Listeners;

use App\Services\Audit\AuditService;
use Illuminate\Database\Events\ModelCreated;
use Illuminate\Support\Facades\App;

class LogModelCreated
{
    /**
     * Handle the event.
     */
    public function handle(ModelCreated $event): void
    {
        $model = $event->model;
        $user = auth()->user();
        
        // Skip audit logs to prevent recursion
        if (get_class($model) === 'App\Models\AuditLog') {
            return;
        }
        
        // Skip if no authenticated user
        if (!$user) {
            return;
        }
        
        $auditService = App::make(AuditService::class);
        $auditService->logCreated($user, $model);
    }
}
```
**Checklist:**
- [ ] Implement model creation listener
- [ ] Skip audit logs to prevent recursion
- [ ] Skip if no authenticated user
- [ ] Log creation event

##### File: `app/Listeners/LogModelUpdated.php`
```php
<?php

namespace App\Listeners;

use App\Services\Audit\AuditService;
use Illuminate\Database\Events\ModelUpdated;
use Illuminate\Support\Facades\App;

class LogModelUpdated
{
    /**
     * Handle the event.
     */
    public function handle(ModelUpdated $event): void
    {
        $model = $event->model;
        $user = auth()->user();
        
        // Skip audit logs to prevent recursion
        if (get_class($model) === 'App\Models\AuditLog') {
            return;
        }
        
        // Skip if no authenticated user
        if (!$user) {
            return;
        }
        
        // Skip if no changes
        if (empty($model->getDirty())) {
            return;
        }
        
        $auditService = App::make(AuditService::class);
        $auditService->logUpdated(
            $user,
            $model,
            $model->getOriginal(),
            $model->getDirty()
        );
    }
}
```
**Checklist:**
- [ ] Implement model update listener
- [ ] Skip audit logs to prevent recursion
- [ ] Skip if no authenticated user
- [ ] Skip if no changes
- [ ] Log update event with old and new values

##### File: `app/Listeners/LogModelDeleted.php`
```php
<?php

namespace App\Listeners;

use App\Services\Audit\AuditService;
use Illuminate\Database\Events\ModelDeleted;
use Illuminate\Support\Facades\App;

class LogModelDeleted
{
    /**
     * Handle the event.
     */
    public function handle(ModelDeleted $event): void
    {
        $model = $event->model;
        $user = auth()->user();
        
        // Skip audit logs to prevent recursion
        if (get_class($model) === 'App\Models\AuditLog') {
            return;
        }
        
        // Skip if no authenticated user
        if (!$user) {
            return;
        }
        
        $auditService = App::make(AuditService::class);
        $auditService->logDeleted($user, $model);
    }
}
```
**Checklist:**
- [ ] Implement model deletion listener
- [ ] Skip audit logs to prevent recursion
- [ ] Skip if no authenticated user
- [ ] Log deletion event

**Controllers (2 files)**

##### File: `app/Http/Controllers/Api/V1/PDPA/DataExportController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\PDPA;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use App\Services\PDPA\DataExportService;
use App\Http\Resources\DataExportResource;
use App\Jobs\PDPA\ProcessDataExportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataExportController extends Controller
{
    protected DataExportService $dataExportService;
    
    public function __construct(DataExportService $dataExportService)
    {
        $this->dataExportService = $dataExportService;
    }
    
    /**
     * Request data export
     */
    public function request(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user already has a pending export
        $pendingExport = DataExportRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();
        
        if ($pendingExport) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending export request.',
                'data' => [
                    'export' => DataExportResource::make($pendingExport),
                ],
            ], 400);
        }
        
        // Create export request
        $exportRequest = $this->dataExportService->requestExport($user);
        
        // Dispatch job to process export
        ProcessDataExportJob::dispatch($exportRequest);
        
        return response()->json([
            'success' => true,
            'message' => 'Data export request submitted. You will receive an email when it\'s ready.',
            'data' => [
                'export' => DataExportResource::make($exportRequest),
            ],
        ], 201);
    }
    
    /**
     * Get user's export requests
     */
    public function index(Request $request): JsonResponse
    {
        $exports = $this->dataExportService->getUserExports($request->user());
        
        return response()->json([
            'success' => true,
            'data' => [
                'exports' => DataExportResource::collection($exports),
            ],
        ]);
    }
    
    /**
     * Download exported data
     */
    public function download(Request $request, DataExportRequest $export): JsonResponse
    {
        // Verify ownership
        if ($export->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        // Check if export is ready
        if (!$export->isReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Export is not ready for download.',
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'download_url' => $export->download_url,
                'expires_at' => $export->expires_at,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement export request creation
- [ ] Check for pending exports
- [ ] Dispatch export processing job
- [ ] Implement export listing
- [ ] Implement export download
- [ ] Add ownership verification
- [ ] Add readiness verification

##### File: `app/Http/Controllers/Api/V1/PDPA/AccountDeletionController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\PDPA;

use App\Http\Controllers\Controller;
use App\Http\Requests\PDPA\RequestDeletionRequest;
use App\Services\PDPA\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountDeletionController extends Controller
{
    protected AccountDeletionService $accountDeletionService;
    
    public function __construct(AccountDeletionService $accountDeletionService)
    {
        $this->accountDeletionService = $accountDeletionService;
    }
    
    /**
     * Request account deletion
     */
    public function request(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user already has a pending deletion
        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is already scheduled for deletion.',
            ], 400);
        }
        
        // Request deletion
        $this->accountDeletionService->requestDeletion($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Your account has been scheduled for deletion. You have 30 days to cancel this request.',
        ]);
    }
    
    /**
     * Cancel account deletion
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has a pending deletion
        if (!$user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a pending deletion request.',
            ], 400);
        }
        
        // Check if grace period has passed
        if ($user->deleted_at->addDays(30)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'The grace period has passed. Your account cannot be restored.',
            ], 400);
        }
        
        // Cancel deletion
        $this->accountDeletionService->cancelDeletion($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Your account deletion request has been cancelled.',
        ]);
    }
    
    /**
     * Get deletion status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $isScheduled = $user->trashed();
        $gracePeriodEnds = $isScheduled ? $user->deleted_at->addDays(30) : null;
        $canCancel = $isScheduled && $gracePeriodEnds->isFuture();
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_scheduled_for_deletion' => $isScheduled,
                'grace_period_ends_at' => $gracePeriodEnds,
                'can_cancel' => $canCancel,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement deletion request
- [ ] Check for existing deletion requests
- [ ] Implement deletion cancellation
- [ ] Check grace period status
- [ ] Implement status retrieval
- [ ] Add appropriate error messages

**Routes (1 file)**

##### File: `routes/api.php` (PDPA section)
```php
<?php

use App\Http\Controllers\Api\V1\PDPA\DataExportController;
use App\Http\Controllers\Api\V1\PDPA\AccountDeletionController;

/*
|--------------------------------------------------------------------------
| API Routes - PDPA Compliance
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('v1/pdpa')->group(function () {
    // Data Export
    Route::get('exports', [DataExportController::class, 'index']);
    Route::post('exports/request', [DataExportController::class, 'request']);
    Route::get('exports/{export}/download', [DataExportController::class, 'download']);
    
    // Account Deletion
    Route::get('account/deletion/status', [AccountDeletionController::class, 'status']);
    Route::post('account/deletion/request', [AccountDeletionController::class, 'request']);
    Route::post('account/deletion/cancel', [AccountDeletionController::class, 'cancel']);
});
```
**Checklist:**
- [ ] GET /api/v1/pdpa/exports (protected)
- [ ] POST /api/v1/pdpa/exports/request (protected)
- [ ] GET /api/v1/pdpa/exports/{export}/download (protected)
- [ ] GET /api/v1/pdpa/account/deletion/status (protected)
- [ ] POST /api/v1/pdpa/account/deletion/request (protected)
- [ ] POST /api/v1/pdpa/account/deletion/cancel (protected)

**Tests (2 files minimum)**

##### File: `tests/Feature/PDPA/DataExportTest.php`
```php
<?php

namespace Tests\Feature\PDPA;

use App\Models\User;
use App\Models\DataExportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataExportTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user can request data export
     */
    public function test_user_can_request_data_export(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/exports/request');
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'export' => [
                        'id',
                        'status',
                        'expires_at',
                        'requested_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('data_export_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }
    
    /**
     * Test user cannot request multiple pending exports
     */
    public function test_user_cannot_request_multiple_pending_exports(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Create existing pending export
        DataExportRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/exports/request');
        
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You already have a pending export request.',
            ]);
    }
    
    /**
     * Test user can view their export requests
     */
    public function test_user_can_view_their_export_requests(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Create export requests
        DataExportRequest::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/v1/pdpa/exports');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'exports' => [
                        '*' => [
                            'id',
                            'status',
                            'expires_at',
                            'requested_at',
                        ],
                    ],
                ],
            ]);
        
        $this->assertEquals(3, count($response->json('data.exports')));
    }
    
    /**
     * Test user can download completed export
     */
    public function test_user_can_download_completed_export(): void
    {
        Storage::fake('private');
        
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Create completed export
        $export = DataExportRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'download_url' => 'https://example.com/download/export.json',
            'expires_at' => now()->addDays(7),
        ]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson("/api/v1/pdpa/exports/{$export->id}/download");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'download_url',
                    'expires_at',
                ],
            ]);
    }
    
    /**
     * Test user cannot download another user's export
     */
    public function test_user_cannot_download_another_users_export(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Create export for other user
        $export = DataExportRequest::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'completed',
        ]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson("/api/v1/pdpa/exports/{$export->id}/download");
        
        $response->assertStatus(403);
    }
}
```
**Checklist:**
- [ ] Test: user can request data export
- [ ] Test: user cannot request multiple pending exports
- [ ] Test: user can view their export requests
- [ ] Test: user can download completed export
- [ ] Test: user cannot download another user's export

##### File: `tests/Feature/PDPA/AccountDeletionTest.php`
```php
<?php

namespace Tests\Feature\PDPA;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test user can request account deletion
     */
    public function test_user_can_request_account_deletion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/account/deletion/request');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your account has been scheduled for deletion. You have 30 days to cancel this request.',
            ]);
        
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
    
    /**
     * Test user cannot request deletion if already scheduled
     */
    public function test_user_cannot_request_deletion_if_already_scheduled(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Soft delete user
        $user->delete();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/account/deletion/request');
        
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Your account is already scheduled for deletion.',
            ]);
    }
    
    /**
     * Test user can cancel account deletion within grace period
     */
    public function test_user_can_cancel_account_deletion_within_grace_period(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Request deletion
        $user->delete();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/account/deletion/cancel');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your account deletion request has been cancelled.',
            ]);
        
        $this->assertNotSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }
    
    /**
     * Test user cannot cancel deletion after grace period
     */
    public function test_user_cannot_cancel_deletion_after_grace_period(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Soft delete user 31 days ago
        $user->deleted_at = Carbon::now()->subDays(31);
        $user->save();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/pdpa/account/deletion/cancel');
        
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'The grace period has passed. Your account cannot be restored.',
            ]);
    }
    
    /**
     * Test user can view deletion status
     */
    public function test_user_can_view_deletion_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');
        
        // Check status before deletion
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/v1/pdpa/account/deletion/status');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_scheduled_for_deletion' => false,
                    'can_cancel' => false,
                ],
            ]);
        
        // Request deletion
        $user->delete();
        
        // Check status after deletion
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/v1/pdpa/account/deletion/status');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_scheduled_for_deletion',
                    'grace_period_ends_at',
                    'can_cancel',
                ],
            ]);
        
        $this->assertTrue($response->json('data.is_scheduled_for_deletion'));
        $this->assertTrue($response->json('data.can_cancel'));
    }
}
```
**Checklist:**
- [ ] Test: user can request account deletion
- [ ] Test: user cannot request deletion if already scheduled
- [ ] Test: user can cancel deletion within grace period
- [ ] Test: user cannot cancel deletion after grace period
- [ ] Test: user can view deletion status

### B.4. Workstream Validation

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
   - [ ] User can request data export
   - [ ] User can download exported data
   - [ ] User can request account deletion
   - [ ] User can cancel account deletion within grace period
   - [ ] Audit logging works for all model changes

4. **PDPA Compliance**
   - [ ] All data changes are logged
   - [ ] Data export includes all user records
   - [ ] Account deletion has 30-day grace period
   - [ ] Consent tracking is functional

5. **Documentation**
   - [ ] All methods documented with PHPDoc
   - [ ] API endpoints documented in OpenAPI
   - [ ] Code reviewed by peer

## Workstream C: Center & Service Management (3-4 days)

**Owner:** Backend Dev 1
**Dependencies:** Workstream A (authentication for admin access)
**Priority:** HIGH (demo requirement)

### C.1. Prerequisites & Setup
Before starting this workstream, ensure:
- [ ] Workstream A is complete and all tests pass
- [ ] User authentication and authorization system is functional
- [ ] Admin roles are properly configured
- [ ] Database is migrated with all tables

### C.2. Implementation Sequence
1. Create Models (6 files)
2. Create Repositories (4 files)
3. Create Service Classes (5 files)
4. Create Request Validation Classes (12 files)
5. Create Resource Transformers (6 files)
6. Create Controllers (6 files)
7. Create Routes (1 file)
8. Create Tests (4 files minimum)
9. Validate workstream completion

### C.3. File Creation Matrix

**Models (6 files)**

##### File: `app/Models/Center.php`
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
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'license_expiry_date',
        'created_at',
        'updated_at',
        'deleted_at',
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
     * Scope a query to search centers by name or description.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('short_description', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
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
     * Get the average rating from testimonials.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->testimonials()
            ->where('status', 'approved')
            ->avg('rating') ?? 0;
    }
    
    /**
     * Get the available capacity count.
     */
    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }
    
    /**
     * Check if the center has available capacity.
     */
    public function hasAvailableCapacity(): bool
    {
        return $this->current_occupancy < $this->capacity;
    }
    
    /**
     * Check if the center's license is expired.
     */
    public function isLicenseExpired(): bool
    {
        return $this->license_expiry_date->isPast();
    }
    
    /**
     * Check if the center's license is expiring soon (within 30 days).
     */
    public function isLicenseExpiringSoon(): bool
    {
        return $this->license_expiry_date->diffInDays(now()) <= 30;
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
     * Set the slug attribute.
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ?: str_slug($this->name);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (services, staff, media, etc.)
- [ ] Add scopes for status, city, accreditation, and search
- [ ] Add computed attributes (occupancy rate, average rating, available capacity)
- [ ] Add helper methods (capacity checks, license checks)
- [ ] Add translation methods
- [ ] Add slug mutator
- [ ] Add soft deletes support

##### File: `app/Models/Service.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'center_id',
        'name',
        'slug',
        'description',
        'price',
        'price_unit',
        'duration',
        'features',
        'status',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the center that owns the service.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
    
    /**
     * Get the bookings for the service.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
    /**
     * Get the media for the service.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
    
    /**
     * Get the content translations for the service.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }
    
    /**
     * Get the audit logs for the service.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include published services.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    
    /**
     * Scope a query to only include services for a specific center.
     */
    public function scopeForCenter($query, int $centerId)
    {
        return $query->where('center_id', $centerId);
    }
    
    /**
     * Scope a query to search services by name or description.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
    
    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return 'Price on Application';
        }
        
        return '$' . number_format($this->price, 2) . ' / ' . $this->price_unit;
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
     * Set the slug attribute.
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ?: str_slug($this->name);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (center, bookings, media, etc.)
- [ ] Add scopes for status, center, and search
- [ ] Add computed attributes (formatted price)
- [ ] Add translation methods
- [ ] Add slug mutator
- [ ] Add soft deletes support

##### File: `app/Models/Staff.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Staff extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'center_id',
        'name',
        'position',
        'qualifications',
        'years_of_experience',
        'bio',
        'photo',
        'display_order',
        'status',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'qualifications' => 'array',
        'years_of_experience' => 'integer',
        'display_order' => 'integer',
    ];
    
    /**
     * Get the center that owns the staff member.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
    
    /**
     * Get the media for the staff member.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
    
    /**
     * Get the audit logs for the staff member.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope a query to only include staff for a specific center.
     */
    public function scopeForCenter($query, int $centerId)
    {
        return $query->where('center_id', $centerId);
    }
    
    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
    
    /**
     * Get the display name with position.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} - {$this->position}";
    }
    
    /**
     * Get the photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }
        
        return url($this->photo);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (center, media, etc.)
- [ ] Add scopes for status, center, and ordering
- [ ] Add computed attributes (display name, photo URL)
- [ ] Add photo URL getter

##### File: `app/Models/Media.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'url',
        'thumbnail_url',
        'filename',
        'mime_type',
        'size',
        'duration',
        'caption',
        'alt_text',
        'cloudflare_stream_id',
        'display_order',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'size' => 'integer',
        'duration' => 'integer',
        'display_order' => 'integer',
    ];
    
    /**
     * Get the parent mediable model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Scope a query to only include images.
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }
    
    /**
     * Scope a query to only include videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }
    
    /**
     * Scope a query to only include media of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
    
    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get the full URL for the media.
     */
    public function getFullUrlAttribute(): string
    {
        return url($this->url);
    }
    
    /**
     * Get the full URL for the thumbnail.
     */
    public function getFullThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_url) {
            return null;
        }
        
        return url($this->thumbnail_url);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement polymorphic relationship
- [ ] Add scopes for type and ordering
- [ ] Add computed attributes (formatted size, full URLs)
- [ ] Add URL getters

##### File: `app/Models/ContentTranslation.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentTranslation extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'value',
        'translation_status',
        'translated_by',
        'reviewed_by',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the translatable model.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
    
    /**
     * Get the user who translated the content.
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'translated_by');
    }
    
    /**
     * Get the user who reviewed the content.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    
    /**
     * Scope a query to only include translations for a specific locale.
     */
    public function scopeForLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
    
    /**
     * Scope a query to only include published translations.
     */
    public function scopePublished($query)
    {
        return $query->where('translation_status', 'published');
    }
    
    /**
     * Scope a query to only include pending translations.
     */
    public function scopePending($query)
    {
        return $query->where('translation_status', 'pending');
    }
    
    /**
     * Scope a query to only include translations for a specific field.
     */
    public function scopeForField($query, string $field)
    {
        return $query->where('field', $field);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement polymorphic relationship
- [ ] Implement user relationships
- [ ] Add scopes for locale, status, and field
- [ ] Add translation status filtering

##### File: `app/Models/FAQ.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FAQ extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category',
        'question',
        'answer',
        'display_order',
        'status',
    ];
    
    /**
     * Get the content translations for the FAQ.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }
    
    /**
     * Get the audit logs for the FAQ.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include published FAQs.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    
    /**
     * Scope a query to only include FAQs in a specific category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
    
    /**
     * Get the translated question for a specific locale.
     */
    public function getTranslatedQuestion(string $locale = 'en'): string
    {
        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('field', 'question')
            ->where('translation_status', 'published')
            ->first();
            
        return $translation ? $translation->value : $this->question;
    }
    
    /**
     * Get the translated answer for a specific locale.
     */
    public function getTranslatedAnswer(string $locale = 'en'): string
    {
        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('field', 'answer')
            ->where('translation_status', 'published')
            ->first();
            
        return $translation ? $translation->value : $this->answer;
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Implement relationships (translations, audit logs)
- [ ] Add scopes for status, category, and ordering
- [ ] Add translation methods
- [ ] Add translation status filtering

**Repositories (4 files)**

##### File: `app/Repositories/CenterRepository.php`
```php
<?php

namespace App\Repositories;

use App\Models\Center;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CenterRepository
{
    /**
     * Get centers with filtering and pagination
     */
    public function getCenters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Center::query();
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        
        if (isset($filters['accreditation_status'])) {
            $query->where('accreditation_status', $filters['accreditation_status']);
        }
        
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }
        
        if (isset($filters['has_capacity']) && $filters['has_capacity']) {
            $query->whereRaw('current_occupancy < capacity');
        }
        
        return $query->with(['media' => function ($query) {
            $query->images()->ordered()->limit(3);
        }])
        ->orderBy('name', 'asc')
        ->paginate($perPage);
    }
    
    /**
     * Get center by ID with relationships
     */
    public function findById(int $id): ?Center
    {
        return Center::with([
            'services' => function ($query) {
                $query->published()->ordered();
            },
            'staff' => function ($query) {
                $query->active()->ordered();
            },
            'media' => function ($query) {
                $query->ordered();
            },
            'testimonials' => function ($query) {
                $query->where('status', 'approved')->latest()->limit(5);
            }
        ])->find($id);
    }
    
    /**
     * Get center by slug with relationships
     */
    public function findBySlug(string $slug): ?Center
    {
        return Center::with([
            'services' => function ($query) {
                $query->published()->ordered();
            },
            'staff' => function ($query) {
                $query->active()->ordered();
            },
            'media' => function ($query) {
                $query->ordered();
            },
            'testimonials' => function ($query) {
                $query->where('status', 'approved')->latest()->limit(5);
            }
        ])->where('slug', $slug)->first();
    }
    
    /**
     * Create center
     */
    public function create(array $data): Center
    {
        return Center::create($data);
    }
    
    /**
     * Update center
     */
    public function update(Center $center, array $data): Center
    {
        $center->update($data);
        return $center->fresh();
    }
    
    /**
     * Delete center (soft delete)
     */
    public function delete(Center $center): bool
    {
        return $center->delete();
    }
    
    /**
     * Search centers with MeiliSearch
     */
    public function search(string $query, int $limit = 20): Collection
    {
        // This would integrate with MeiliSearch
        // For now, fallback to database search
        return Center::search($query)
            ->published()
            ->with(['media' => function ($query) {
                $query->images()->ordered()->limit(1);
            }])
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get centers near a location using Haversine formula
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        // Haversine formula
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
        
        return Center::select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->whereRaw("{$haversine} < ?", [$radiusKm])
            ->published()
            ->orderBy('distance', 'asc')
            ->with(['media' => function ($query) {
                $query->images()->ordered()->limit(1);
            }])
            ->get();
    }
    
    /**
     * Get unique cities from centers
     */
    public function getUniqueCities(): Collection
    {
        return Center::published()
            ->distinct()
            ->orderBy('city', 'asc')
            ->pluck('city');
    }
    
    /**
     * Get centers with expiring licenses
     */
    public function getCentersWithExpiringLicenses(int $days = 30): Collection
    {
        return Center::whereDate('license_expiry_date', '<=', now()->addDays($days))
            ->whereDate('license_expiry_date', '>=', now())
            ->orderBy('license_expiry_date', 'asc')
            ->get();
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering
- [ ] Implement center retrieval by ID
- [ ] Implement center retrieval by slug
- [ ] Implement center creation
- [ ] Implement center update
- [ ] Implement center deletion
- [ ] Implement search functionality
- [ ] Implement nearby centers calculation
- [ ] Implement unique cities retrieval
- [ ] Implement expiring licenses check

##### File: `app/Repositories/ServiceRepository.php`
```php
<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    /**
     * Get services with filtering and pagination
     */
    public function getServices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::query();
        
        // Apply filters
        if (isset($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }
        
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        return $query->with('center')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Get service by ID with relationships
     */
    public function findById(int $id): ?Service
    {
        return Service::with([
            'center',
            'media' => function ($query) {
                $query->ordered();
            }
        ])->find($id);
    }
    
    /**
     * Get services for a specific center
     */
    public function getServicesByCenterId(int $centerId): Collection
    {
        return Service::where('center_id', $centerId)
            ->published()
            ->with(['media' => function ($query) {
                $query->ordered()->limit(3);
            }])
            ->ordered()
            ->get();
    }
    
    /**
     * Create service
     */
    public function create(array $data): Service
    {
        return Service::create($data);
    }
    
    /**
     * Update service
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }
    
    /**
     * Delete service (soft delete)
     */
    public function delete(Service $service): bool
    {
        return $service->delete();
    }
    
    /**
     * Search services
     */
    public function search(string $query, int $limit = 20): Collection
    {
        return Service::search($query)
            ->published()
            ->with('center')
            ->limit($limit)
            ->get();
    }
}
```
**Checklist:**
- [ ] Implement service listing with filtering
- [ ] Implement service retrieval by ID
- [ ] Implement service retrieval by center
- [ ] Implement service creation
- [ ] Implement service update
- [ ] Implement service deletion
- [ ] Implement search functionality

##### File: `app/Repositories/StaffRepository.php`
```php
<?php

namespace App\Repositories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

class StaffRepository
{
    /**
     * Get staff with filtering
     */
    public function getStaff(array $filters = []): Collection
    {
        $query = Staff::query();
        
        // Apply filters
        if (isset($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['position'])) {
            $query->where('position', 'like', "%{$filters['position']}%");
        }
        
        return $query->ordered()->get();
    }
    
    /**
     * Get staff member by ID
     */
    public function findById(int $id): ?Staff
    {
        return Staff::with('center')->find($id);
    }
    
    /**
     * Get staff for a specific center
     */
    public function getStaffByCenterId(int $centerId): Collection
    {
        return Staff::where('center_id', $centerId)
            ->active()
            ->ordered()
            ->get();
    }
    
    /**
     * Create staff member
     */
    public function create(array $data): Staff
    {
        return Staff::create($data);
    }
    
    /**
     * Update staff member
     */
    public function update(Staff $staff, array $data): Staff
    {
        $staff->update($data);
        return $staff->fresh();
    }
    
    /**
     * Delete staff member
     */
    public function delete(Staff $staff): bool
    {
        return $staff->delete();
    }
    
    /**
     * Reorder staff display order
     */
    public function reorder(int $centerId, array $staffIds): bool
    {
        foreach ($staffIds as $order => $staffId) {
            Staff::where('center_id', $centerId)
                ->where('id', $staffId)
                ->update(['display_order' => $order + 1]);
        }
        
        return true;
    }
}
```
**Checklist:**
- [ ] Implement staff listing with filtering
- [ ] Implement staff retrieval by ID
- [ ] Implement staff retrieval by center
- [ ] Implement staff creation
- [ ] Implement staff update
- [ ] Implement staff deletion
- [ ] Implement staff reordering

##### File: `app/Repositories/MediaRepository.php`
```php
<?php

namespace App\Repositories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;

class MediaRepository
{
    /**
     * Get media with filtering
     */
    public function getMedia(array $filters = []): Collection
    {
        $query = Media::query();
        
        // Apply filters
        if (isset($filters['mediable_type'])) {
            $query->where('mediable_type', $filters['mediable_type']);
        }
        
        if (isset($filters['mediable_id'])) {
            $query->where('mediable_id', $filters['mediable_id']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        return $query->ordered()->get();
    }
    
    /**
     * Get media by ID
     */
    public function findById(int $id): ?Media
    {
        return Media::find($id);
    }
    
    /**
     * Get media for a model
     */
    public function getMediaForModel(string $modelType, int $modelId, string $type = null): Collection
    {
        $query = Media::where('mediable_type', $modelType)
            ->where('mediable_id', $modelId);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->ordered()->get();
    }
    
    /**
     * Create media record
     */
    public function create(array $data): Media
    {
        return Media::create($data);
    }
    
    /**
     * Update media record
     */
    public function update(Media $media, array $data): Media
    {
        $media->update($data);
        return $media->fresh();
    }
    
    /**
     * Delete media record and file
     */
    public function delete(Media $media): bool
    {
        // Delete file from storage
        if ($media->url) {
            \Storage::disk('public')->delete($media->url);
        }
        
        if ($media->thumbnail_url) {
            \Storage::disk('public')->delete($media->thumbnail_url);
        }
        
        return $media->delete();
    }
    
    /**
     * Reorder media display order
     */
    public function reorder(string $modelType, int $modelId, array $mediaIds): bool
    {
        foreach ($mediaIds as $order => $mediaId) {
            Media::where('mediable_type', $modelType)
                ->where('mediable_id', $modelId)
                ->where('id', $mediaId)
                ->update(['display_order' => $order + 1]);
        }
        
        return true;
    }
}
```
**Checklist:**
- [ ] Implement media listing with filtering
- [ ] Implement media retrieval by ID
- [ ] Implement media retrieval by model
- [ ] Implement media creation
- [ ] Implement media update
- [ ] Implement media deletion with file cleanup
- [ ] Implement media reordering

**Service Classes (5 files)**

##### File: `app/Services/Center/CenterService.php`
```php
<?php

namespace App\Services\Center;

use App\Models\Center;
use App\Repositories\Center\CenterRepository;
use App\Services\Media\MediaService;
use App\Services\Translation\TranslationService;
use App\Services\Search\SearchService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class CenterService
{
    protected CenterRepository $centerRepository;
    protected MediaService $mediaService;
    protected TranslationService $translationService;
    protected SearchService $searchService;
    protected AuditService $auditService;
    
    public function __construct(
        CenterRepository $centerRepository,
        MediaService $mediaService,
        TranslationService $translationService,
        SearchService $searchService,
        AuditService $auditService
    ) {
        $this->centerRepository = $centerRepository;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
        $this->searchService = $searchService;
        $this->auditService = $auditService;
    }
    
    /**
     * Get centers with filtering and pagination
     */
    public function getCenters(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->centerRepository->getCenters($filters, $perPage);
    }
    
    /**
     * Get center by ID with relationships
     */
    public function getCenterById(int $id): ?Center
    {
        return $this->centerRepository->findById($id);
    }
    
    /**
     * Get center by slug with relationships
     */
    public function getCenterBySlug(string $slug): ?Center
    {
        return $this->centerRepository->findBySlug($slug);
    }
    
    /**
     * Create center with MOH validation
     */
    public function createCenter(array $data, array $mediaFiles = []): Center
    {
        return DB::transaction(function () use ($data, $mediaFiles) {
            // Validate MOH license uniqueness
            if ($this->centerRepository->findByMohLicense($data['moh_license_number'])) {
                throw new \InvalidArgumentException('MOH license number already exists');
            }
            
            // Create center
            $center = $this->centerRepository->create($data);
            
            // Upload media files
            foreach ($mediaFiles as $index => $file) {
                $this->mediaService->uploadMedia($center, $file, [
                    'type' => 'image',
                    'display_order' => $index + 1,
                ]);
            }
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $center);
            
            // Sync to search index
            $this->searchService->syncCenterToSearchIndex($center);
            
            return $center;
        });
    }
    
    /**
     * Update center
     */
    public function updateCenter(Center $center, array $data, array $mediaFiles = []): Center
    {
        return DB::transaction(function () use ($center, $data, $mediaFiles) {
            $oldValues = $center->toArray();
            
            // Check if MOH license is being changed
            if (isset($data['moh_license_number']) && 
                $data['moh_license_number'] !== $center->moh_license_number) {
                
                // Validate uniqueness
                if ($this->centerRepository->findByMohLicense($data['moh_license_number'])) {
                    throw new \InvalidArgumentException('MOH license number already exists');
                }
            }
            
            // Update center
            $center = $this->centerRepository->update($center, $data);
            
            // Upload new media files
            foreach ($mediaFiles as $index => $file) {
                $this->mediaService->uploadMedia($center, $file, [
                    'type' => 'image',
                    'display_order' => $index + 1,
                ]);
            }
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $center,
                $oldValues,
                $center->toArray()
            );
            
            // Sync to search index
            $this->searchService->syncCenterToSearchIndex($center);
            
            return $center;
        });
    }
    
    /**
     * Delete center (soft delete)
     */
    public function deleteCenter(Center $center): bool
    {
        return DB::transaction(function () use ($center) {
            // Log audit
            $this->auditService->logDeleted(auth()->user(), $center);
            
            // Remove from search index
            $this->searchService->removeCenterFromSearchIndex($center);
            
            return $this->centerRepository->delete($center);
        });
    }
    
    /**
     * Search centers
     */
    public function searchCenters(string $query, int $limit = 20): \Illuminate\Support\Collection
    {
        return $this->centerRepository->search($query, $limit);
    }
    
    /**
     * Get centers near a location
     */
    public function getNearbyCenters(float $latitude, float $longitude, float $radiusKm = 10): \Illuminate\Support\Collection
    {
        return $this->centerRepository->findNearby($latitude, $longitude, $radiusKm);
    }
    
    /**
     * Get unique cities
     */
    public function getUniqueCities(): \Illuminate\Support\Collection
    {
        return $this->centerRepository->getUniqueCities();
    }
    
    /**
     * Get centers with expiring licenses
     */
    public function getCentersWithExpiringLicenses(int $days = 30): \Illuminate\Support\Collection
    {
        return $this->centerRepository->getCentersWithExpiringLicenses($days);
    }
    
    /**
     * Get center statistics
     */
    public function getCenterStatistics(int $centerId): array
    {
        $center = $this->centerRepository->findById($centerId);
        
        if (!$center) {
            return [];
        }
        
        return [
            'total_services' => $center->services()->count(),
            'published_services' => $center->services()->published()->count(),
            'total_staff' => $center->staff()->count(),
            'active_staff' => $center->staff()->active()->count(),
            'total_bookings' => $center->bookings()->count(),
            'confirmed_bookings' => $center->bookings()->where('status', 'confirmed')->count(),
            'completed_bookings' => $center->bookings()->where('status', 'completed')->count(),
            'total_testimonials' => $center->testimonials()->count(),
            'approved_testimonials' => $center->testimonials()->where('status', 'approved')->count(),
            'average_rating' => $center->average_rating,
            'occupancy_rate' => $center->occupancy_rate,
        ];
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering
- [ ] Implement center retrieval by ID and slug
- [ ] Implement center creation with MOH validation
- [ ] Implement center update
- [ ] Implement center deletion
- [ ] Implement search functionality
- [ ] Implement nearby centers calculation
- [ ] Implement unique cities retrieval
- [ ] Implement expiring licenses check
- [ ] Implement statistics calculation
- [ ] Add audit logging
- [ ] Add search index synchronization

##### File: `app/Services/Center/ServiceManagementService.php`
```php
<?php

namespace App\Services\Center;

use App\Models\Center;
use App\Models\Service;
use App\Repositories\Center\ServiceRepository;
use App\Services\Media\MediaService;
use App\Services\Translation\TranslationService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class ServiceManagementService
{
    protected ServiceRepository $serviceRepository;
    protected MediaService $mediaService;
    protected TranslationService $translationService;
    protected AuditService $auditService;
    
    public function __construct(
        ServiceRepository $serviceRepository,
        MediaService $mediaService,
        TranslationService $translationService,
        AuditService $auditService
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->mediaService = $mediaService;
        $this->translationService = $translationService;
        $this->auditService = $auditService;
    }
    
    /**
     * Get services for a center
     */
    public function getServicesByCenterId(int $centerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->serviceRepository->getServicesByCenterId($centerId);
    }
    
    /**
     * Create service for a center
     */
    public function createService(Center $center, array $data, array $mediaFiles = []): Service
    {
        return DB::transaction(function () use ($center, $data, $mediaFiles) {
            // Set center_id
            $data['center_id'] = $center->id;
            
            // Create service
            $service = $this->serviceRepository->create($data);
            
            // Upload media files
            foreach ($mediaFiles as $index => $file) {
                $this->mediaService->uploadMedia($service, $file, [
                    'type' => 'image',
                    'display_order' => $index + 1,
                ]);
            }
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $service);
            
            return $service;
        });
    }
    
    /**
     * Update service
     */
    public function updateService(Service $service, array $data, array $mediaFiles = []): Service
    {
        return DB::transaction(function () use ($service, $data, $mediaFiles) {
            $oldValues = $service->toArray();
            
            // Update service
            $service = $this->serviceRepository->update($service, $data);
            
            // Upload new media files
            foreach ($mediaFiles as $index => $file) {
                $this->mediaService->uploadMedia($service, $file, [
                    'type' => 'image',
                    'display_order' => $index + 1,
                ]);
            }
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $service,
                $oldValues,
                $service->toArray()
            );
            
            return $service;
        });
    }
    
    /**
     * Delete service (soft delete)
     */
    public function deleteService(Service $service): bool
    {
        return DB::transaction(function () use ($service) {
            // Log audit
            $this->auditService->logDeleted(auth()->user(), $service);
            
            return $this->serviceRepository->delete($service);
        });
    }
    
    /**
     * Get service statistics
     */
    public function getServiceStatistics(int $serviceId): array
    {
        $service = $this->serviceRepository->findById($serviceId);
        
        if (!$service) {
            return [];
        }
        
        return [
            'total_bookings' => $service->bookings()->count(),
            'confirmed_bookings' => $service->bookings()->where('status', 'confirmed')->count(),
            'completed_bookings' => $service->bookings()->where('status', 'completed')->count(),
            'upcoming_bookings' => $service->bookings()
                ->where('status', 'confirmed')
                ->where('booking_date', '>=', now())
                ->count(),
        ];
    }
}
```
**Checklist:**
- [ ] Implement service retrieval by center
- [ ] Implement service creation
- [ ] Implement service update
- [ ] Implement service deletion
- [ ] Implement statistics calculation
- [ ] Add audit logging
- [ ] Add media file handling

##### File: `app/Services/Center/StaffService.php`
```php
<?php

namespace App\Services\Center;

use App\Models\Center;
use App\Models\Staff;
use App\Repositories\Center\StaffRepository;
use App\Services\Media\MediaService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class StaffService
{
    protected StaffRepository $staffRepository;
    protected MediaService $mediaService;
    protected AuditService $auditService;
    
    public function __construct(
        StaffRepository $staffRepository,
        MediaService $mediaService,
        AuditService $auditService
    ) {
        $this->staffRepository = $staffRepository;
        $this->mediaService = $mediaService;
        $this->auditService = $auditService;
    }
    
    /**
     * Get staff for a center
     */
    public function getStaffByCenterId(int $centerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->staffRepository->getStaffByCenterId($centerId);
    }
    
    /**
     * Create staff member for a center
     */
    public function createStaff(Center $center, array $data, ?UploadedFile $photo = null): Staff
    {
        return DB::transaction(function () use ($center, $data, $photo) {
            // Set center_id
            $data['center_id'] = $center->id;
            
            // Create staff
            $staff = $this->staffRepository->create($data);
            
            // Upload photo if provided
            if ($photo) {
                $this->mediaService->uploadMedia($staff, $photo, [
                    'type' => 'image',
                    'caption' => $staff->name,
                    'alt_text' => "Photo of {$staff->name}, {$staff->position} at {$center->name}",
                ]);
            }
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $staff);
            
            return $staff;
        });
    }
    
    /**
     * Update staff member
     */
    public function updateStaff(Staff $staff, array $data, ?UploadedFile $photo = null): Staff
    {
        return DB::transaction(function () use ($staff, $data, $photo) {
            $oldValues = $staff->toArray();
            
            // Update staff
            $staff = $this->staffRepository->update($staff, $data);
            
            // Upload new photo if provided
            if ($photo) {
                // Delete old photo
                $oldPhoto = $staff->media()->where('type', 'image')->first();
                if ($oldPhoto) {
                    $this->mediaService->deleteMedia($oldPhoto);
                }
                
                // Upload new photo
                $this->mediaService->uploadMedia($staff, $photo, [
                    'type' => 'image',
                    'caption' => $staff->name,
                    'alt_text' => "Photo of {$staff->name}, {$staff->position}",
                ]);
            }
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $staff,
                $oldValues,
                $staff->toArray()
            );
            
            return $staff;
        });
    }
    
    /**
     * Delete staff member
     */
    public function deleteStaff(Staff $staff): bool
    {
        return DB::transaction(function () use ($staff) {
            // Delete photo
            $photo = $staff->media()->where('type', 'image')->first();
            if ($photo) {
                $this->mediaService->deleteMedia($photo);
            }
            
            // Log audit
            $this->auditService->logDeleted(auth()->user(), $staff);
            
            return $this->staffRepository->delete($staff);
        });
    }
    
    /**
     * Reorder staff display order
     */
    public function reorderStaff(int $centerId, array $staffIds): bool
    {
        return $this->staffRepository->reorder($centerId, $staffIds);
    }
}
```
**Checklist:**
- [ ] Implement staff retrieval by center
- [ ] Implement staff creation with photo upload
- [ ] Implement staff update with photo replacement
- [ ] Implement staff deletion with photo cleanup
- [ ] Implement staff reordering
- [ ] Add audit logging
- [ ] Add media file handling

##### File: `app/Services/Media/MediaService.php`
```php
<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Model;
use App\Repositories\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaService
{
    protected MediaRepository $mediaRepository;
    
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }
    
    /**
     * Upload media for a model
     */
    public function uploadMedia(Model $model, UploadedFile $file, array $options = []): Media
    {
        // Generate filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Determine storage path
        $path = $this->getStoragePath($model, $file);
        
        // Store file
        $filePath = $file->storeAs($path, $filename, 'public');
        
        // Create thumbnail for images
        $thumbnailPath = null;
        if ($file->getClientOriginalExtension() !== 'pdf' && 
            $file->getClientOriginalExtension() !== 'svg') {
            $thumbnailPath = $this->createThumbnail($file, $path, $filename);
        }
        
        // Create media record
        $mediaData = [
            'mediable_type' => get_class($model),
            'mediable_id' => $model->id,
            'type' => $this->getMediaType($file),
            'url' => $filePath,
            'thumbnail_url' => $thumbnailPath,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'caption' => $options['caption'] ?? null,
            'alt_text' => $options['alt_text'] ?? null,
            'display_order' => $options['display_order'] ?? 0,
        ];
        
        // Add video duration if applicable
        if ($mediaData['type'] === 'video' && isset($options['duration'])) {
            $mediaData['duration'] = $options['duration'];
        }
        
        // Add Cloudflare Stream ID if applicable
        if (isset($options['cloudflare_stream_id'])) {
            $mediaData['cloudflare_stream_id'] = $options['cloudflare_stream_id'];
        }
        
        return $this->mediaRepository->create($mediaData);
    }
    
    /**
     * Update media metadata
     */
    public function updateMedia(Media $media, array $data): Media
    {
        return $this->mediaRepository->update($media, $data);
    }
    
    /**
     * Delete media
     */
    public function deleteMedia(Media $media): bool
    {
        return $this->mediaRepository->delete($media);
    }
    
    /**
     * Reorder media
     */
    public function reorderMedia(Model $model, array $mediaIds): bool
    {
        return $this->mediaRepository->reorder(
            get_class($model),
            $model->id,
            $mediaIds
        );
    }
    
    /**
     * Get media for a model
     */
    public function getMediaForModel(Model $model, string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->mediaRepository->getMediaForModel(
            get_class($model),
            $model->id,
            $type
        );
    }
    
    /**
     * Generate storage path for media
     */
    protected function getStoragePath(Model $model, UploadedFile $file): string
    {
        $modelType = class_basename($model);
        $mediaType = $this->getMediaType($file);
        
        return "media/{$modelType}/{$mediaType}";
    }
    
    /**
     * Determine media type from file
     */
    protected function getMediaType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        return 'document';
    }
    
    /**
     * Create thumbnail for image
     */
    protected function createThumbnail(UploadedFile $file, string $path, string $filename): ?string
    {
        try {
            $image = Image::make($file);
            
            // Resize to thumbnail
            $image->resize(300, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Generate thumbnail filename
            $thumbnailFilename = 'thumb_' . $filename;
            $thumbnailPath = "{$path}/thumbnails";
            
            // Ensure directory exists
            Storage::disk('public')->makeDirectory($thumbnailPath);
            
            // Store thumbnail
            $fullThumbnailPath = $thumbnailPath . '/' . $thumbnailFilename;
            Storage::disk('public')->put($fullThumbnailPath, $image->encode());
            
            return $fullThumbnailPath;
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create thumbnail', [
                'error' => $e->getMessage(),
                'file' => $filename,
            ]);
            
            return null;
        }
    }
    
    /**
     * Delete media by URL
     */
    public function deleteByUrl(string $url): bool
    {
        if (Storage::disk('public')->exists($url)) {
            return Storage::disk('public')->delete($url);
        }
        
        return false;
    }
    
    /**
     * Upload avatar specifically
     */
    public function uploadAvatar(UploadedFile $file): string
    {
        // Generate filename
        $filename = 'avatar_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Resize and optimize avatar
        $image = Image::make($file);
        $image->fit(200, 200, function ($constraint) {
            $constraint->upsize();
        });
        
        // Store avatar
        $path = 'avatars/' . $filename;
        Storage::disk('public')->put($path, $image->encode());
        
        return $path;
    }
    
    /**
     * Generate temporary URL for private files
     */
    public function generateTemporaryUrl(string $path, \DateTime $expiration): string
    {
        return Storage::disk('private')->temporaryUrl($path, $expiration);
    }
}
```
**Checklist:**
- [ ] Implement media upload with file processing
- [ ] Implement media metadata update
- [ ] Implement media deletion with file cleanup
- [ ] Implement media reordering
- [ ] Implement media retrieval
- [ ] Implement thumbnail generation
- [ ] Implement avatar upload
- [ ] Implement temporary URL generation

##### File: `app/Services/Translation/TranslationService.php`
```php
<?php

namespace App\Services\Translation;

use App\Models\Model;
use App\Models\ContentTranslation;
use App\Repositories\Translation\TranslationRepository;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    protected TranslationRepository $translationRepository;
    protected AuditService $auditService;
    
    public function __construct(
        TranslationRepository $translationRepository,
        AuditService $auditService
    ) {
        $this->translationRepository = $translationRepository;
        $this->auditService = $auditService;
    }
    
    /**
     * Get translation for a model field
     */
    public function getTranslation(Model $model, string $field, string $locale): ?ContentTranslation
    {
        return $this->translationRepository->findTranslation(
            get_class($model),
            $model->id,
            $locale,
            $field
        );
    }
    
    /**
     * Create translation for a model field
     */
    public function createTranslation(
        Model $model,
        string $field,
        string $locale,
        string $value,
        int $translatedBy = null
    ): ContentTranslation {
        return DB::transaction(function () use ($model, $field, $locale, $value, $translatedBy) {
            $translation = $this->translationRepository->create([
                'translatable_type' => get_class($model),
                'translatable_id' => $model->id,
                'locale' => $locale,
                'field' => $field,
                'value' => $value,
                'translation_status' => 'draft',
                'translated_by' => $translatedBy ?? auth()->id(),
            ]);
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $translation);
            
            return $translation;
        });
    }
    
    /**
     * Update translation
     */
    public function updateTranslation(
        ContentTranslation $translation,
        array $data
    ): ContentTranslation {
        return DB::transaction(function () use ($translation, $data) {
            $oldValues = $translation->toArray();
            
            $translation = $this->translationRepository->update($translation, $data);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $translation,
                $oldValues,
                $translation->toArray()
            );
            
            return $translation;
        });
    }
    
    /**
     * Publish translation
     */
    public function publishTranslation(ContentTranslation $translation, int $reviewedBy = null): ContentTranslation
    {
        return DB::transaction(function () use ($translation, $reviewedBy) {
            $oldValues = $translation->toArray();
            
            $translation = $this->translationRepository->update($translation, [
                'translation_status' => 'published',
                'reviewed_by' => $reviewedBy ?? auth()->id(),
            ]);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $translation,
                $oldValues,
                $translation->toArray()
            );
            
            return $translation;
        });
    }
    
    /**
     * Get pending translations
     */
    public function getPendingTranslations(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->translationRepository->getPendingTranslations();
    }
    
    /**
     * Get translations for a model
     */
    public function getTranslationsForModel(
        Model $model,
        string $locale = null,
        string $status = null
    ): \Illuminate\Database\Eloquent\Collection {
        return $this->translationRepository->getTranslationsForModel(
            get_class($model),
            $model->id,
            $locale,
            $status
        );
    }
    
    /**
     * Get translated content for a model
     */
    public function getTranslatedContent(Model $model, string $locale = 'en'): array
    {
        $translations = $this->getTranslationsForModel($model, $locale, 'published');
        
        $result = [];
        
        foreach ($translations as $translation) {
            $result[$translation->field] = $translation->value;
        }
        
        return $result;
    }
    
    /**
     * Check if translation exists for a model field
     */
    public function hasTranslation(Model $model, string $field, string $locale): bool
    {
        return $this->translationRepository->findTranslation(
            get_class($model),
            $model->id,
            $locale,
            $field
        ) !== null;
    }
    
    /**
     * Get translation status for a model
     */
    public function getTranslationStatus(Model $model, string $locale = 'en'): array
    {
        $translations = $this->getTranslationsForModel($model, $locale);
        
        $status = [
            'total' => $translations->count(),
            'draft' => 0,
            'translated' => 0,
            'reviewed' => 0,
            'published' => 0,
        ];
        
        foreach ($translations as $translation) {
            $status[$translation->translation_status]++;
        }
        
        return $status;
    }
}
```
**Checklist:**
- [ ] Implement translation retrieval
- [ ] Implement translation creation
- [ ] Implement translation update
- [ ] Implement translation publishing
- [ ] Implement pending translations retrieval
- [ ] Implement translated content retrieval
- [ ] Implement translation existence check
- [ ] Implement translation status calculation
- [ ] Add audit logging

**Request Validation Classes (12 files)**

##### File: `app/Http/Requests/Center/StoreCenterRequest.php`
```php
<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCenterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:centers,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'phone' => ['required', 'string', 'regex:/^(\+65)?[689]\d{7}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'moh_license_number' => ['required', 'string', 'max:50', 'unique:centers,moh_license_number'],
            'license_expiry_date' => ['required', 'date', 'after:today'],
            'accreditation_status' => ['required', 'in:pending,accredited,not_accredited,expired'],
            'capacity' => ['required', 'integer', 'min:1'],
            'current_occupancy' => ['required', 'integer', 'min:0'],
            'staff_count' => ['required', 'integer', 'min:0'],
            'staff_patient_ratio' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
            'operating_hours' => ['nullable', 'array'],
            'operating_hours.*.open' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}$/'],
            'operating_hours.*.close' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}$/'],
            'medical_facilities' => ['nullable', 'array'],
            'medical_facilities.*' => ['string'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'transport_info' => ['nullable', 'array'],
            'transport_info.mrt' => ['nullable', 'array'],
            'transport_info.mrt.*' => ['string'],
            'transport_info.bus' => ['nullable', 'array'],
            'transport_info.bus.*' => ['string'],
            'transport_info.parking' => ['nullable', 'boolean'],
            'languages_supported' => ['nullable', 'array'],
            'languages_supported.*' => ['string', 'in:en,zh,ms,ta'],
            'government_subsidies' => ['nullable', 'array'],
            'government_subsidies.*' => ['string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => 'The postal code must be a 6-digit Singapore postal code.',
            'phone.regex' => 'The phone number must be a valid Singapore number (e.g., +6581234567 or 81234567).',
            'moh_license_number.unique' => 'The MOH license number has already been taken.',
            'license_expiry_date.after' => 'The license expiry date must be a date after today.',
            'capacity.min' => 'The capacity must be at least 1.',
            'current_occupancy.min' => 'The current occupancy must be at least 0.',
            'staff_count.min' => 'The staff count must be at least 0.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: name, address, city, postal_code, phone, email
- [ ] Validate: MOH license number (unique)
- [ ] Validate: license expiry date (future)
- [ ] Validate: capacity, occupancy, staff count
- [ ] Validate: JSON fields (operating_hours, amenities, etc.)
- [ ] Validate: coordinates (latitude, longitude)
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Center/UpdateCenterRequest.php`
```php
<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCenterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $centerId = $this->route('center')->id;
        
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:centers,slug,' . $centerId],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'string', 'regex:/^[0-9]{6}$/'],
            'phone' => ['sometimes', 'string', 'regex:/^(\+65)?[689]\d{7}$/'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'moh_license_number' => ['sometimes', 'string', 'max:50', 'unique:centers,moh_license_number,' . $centerId],
            'license_expiry_date' => ['sometimes', 'date', 'after:today'],
            'accreditation_status' => ['sometimes', 'in:pending,accredited,not_accredited,expired'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'current_occupancy' => ['sometimes', 'integer', 'min:0'],
            'staff_count' => ['sometimes', 'integer', 'min:0'],
            'staff_patient_ratio' => ['nullable', 'numeric', 'min:0.1', 'max:10'],
            'operating_hours' => ['nullable', 'array'],
            'operating_hours.*.open' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}$/'],
            'operating_hours.*.close' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}$/'],
            'medical_facilities' => ['nullable', 'array'],
            'medical_facilities.*' => ['string'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string'],
            'transport_info' => ['nullable', 'array'],
            'transport_info.mrt' => ['nullable', 'array'],
            'transport_info.mrt.*' => ['string'],
            'transport_info.bus' => ['nullable', 'array'],
            'transport_info.bus.*' => ['string'],
            'transport_info.parking' => ['nullable', 'boolean'],
            'languages_supported' => ['nullable', 'array'],
            'languages_supported.*' => ['string', 'in:en,zh,ms,ta'],
            'government_subsidies' => ['nullable', 'array'],
            'government_subsidies.*' => ['string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => 'The postal code must be a 6-digit Singapore postal code.',
            'phone.regex' => 'The phone number must be a valid Singapore number (e.g., +6581234567 or 81234567).',
            'moh_license_number.unique' => 'The MOH license number has already been taken.',
            'license_expiry_date.after' => 'The license expiry date must be a date after today.',
            'capacity.min' => 'The capacity must be at least 1.',
            'current_occupancy.min' => 'The current occupancy must be at least 0.',
            'staff_count.min' => 'The staff count must be at least 0.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
        ];
    }
}
```
**Checklist:**
- [ ] Same validation as StoreCenterRequest but with 'sometimes' rule
- [ ] Add unique rule exclusion for current center
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Service/StoreServiceRequest.php`
```php
<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['nullable', 'in:hour,day,week,month'],
            'duration' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'status' => ['required', 'in:draft,published,archived'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'center_id.exists' => 'The selected center is invalid.',
            'price.min' => 'The price must be at least 0.',
            'price_unit.in' => 'The price unit must be one of: hour, day, week, month.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: center_id (exists), name, description
- [ ] Validate: price (numeric, min 0)
- [ ] Validate: price_unit (enum)
- [ ] Validate: features (array)
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Service/UpdateServiceRequest.php`
```php
<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_unit' => ['nullable', 'in:hour,day,week,month'],
            'duration' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'status' => ['sometimes', 'in:draft,published,archived'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'price.min' => 'The price must be at least 0.',
            'price_unit.in' => 'The price unit must be one of: hour, day, week, month.',
        ];
    }
}
```
**Checklist:**
- [ ] Same validation as StoreServiceRequest but with 'sometimes' rule
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Staff/StoreStaffRequest.php`
```php
<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'qualifications' => ['nullable', 'array'],
            'qualifications.*' => ['string'],
            'years_of_experience' => ['required', 'integer', 'min:0', 'max:50'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'center_id.exists' => 'The selected center is invalid.',
            'years_of_experience.min' => 'The years of experience must be at least 0.',
            'years_of_experience.max' => 'The years of experience may not be greater than 50.',
            'photo.image' => 'The photo must be an image.',
            'photo.max' => 'The photo may not be greater than 2MB.',
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif, webp.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: center_id (exists), name, position
- [ ] Validate: qualifications (array)
- [ ] Validate: years_of_experience (min 0, max 50)
- [ ] Validate: photo (image, max 2MB)
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Staff/UpdateStaffRequest.php`
```php
<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', 'string', 'max:255'],
            'qualifications' => ['nullable', 'array'],
            'qualifications.*' => ['string'],
            'years_of_experience' => ['sometimes', 'integer', 'min:0', 'max:50'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'years_of_experience.min' => 'The years of experience must be at least 0.',
            'years_of_experience.max' => 'The years of experience may not be greater than 50.',
            'photo.image' => 'The photo must be an image.',
            'photo.max' => 'The photo may not be greater than 2MB.',
            'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif, webp.',
        ];
    }
}
```
**Checklist:**
- [ ] Same validation as StoreStaffRequest but with 'sometimes' rule
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Media/UploadMediaRequest.php`
```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mediable_type' => ['required', 'string'],
            'mediable_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'caption' => ['nullable', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.max' => 'The file may not be greater than 10MB.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: mediable_type, mediable_id
- [ ] Validate: file (required, max 10MB)
- [ ] Validate: caption, alt_text, display_order
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/Media/UpdateMediaRequest.php`
```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
```
**Checklist:**
- [ ] Validate: caption, alt_text, display_order
- [ ] Add authorization check

##### File: `app/Http/Requests/Media/ReorderMediaRequest.php`
```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'media_ids.required' => 'Please provide at least one media ID.',
            'media_ids.*.exists' => 'One or more media IDs are invalid.',
        ];
    }
}
```
**Checklist:**
- [ ] Validate: media_ids (array, min 1)
- [ ] Validate: each media_id exists
- [ ] Add custom error messages
- [ ] Add authorization check

##### File: `app/Http/Requests/FAQ/StoreFAQRequest.php`
```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class StoreFAQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}
```
**Checklist:**
- [ ] Validate: category (enum), question, answer
- [ ] Validate: display_order, status
- [ ] Add authorization check

##### File: `app/Http/Requests/FAQ/UpdateFAQRequest.php`
```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFAQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:draft,published'],
        ];
    }
}
```
**Checklist:**
- [ ] Same validation as StoreFAQRequest but with 'sometimes' rule
- [ ] Add authorization check

**Resource Transformers (6 files)**

##### File: `app/Http/Resources/CenterResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'moh_license_number' => $this->moh_license_number,
            'license_expiry_date' => $this->license_expiry_date,
            'accreditation_status' => $this->accreditation_status,
            'capacity' => $this->capacity,
            'current_occupancy' => $this->current_occupancy,
            'occupancy_rate' => $this->occupancy_rate,
            'available_capacity' => $this->available_capacity,
            'staff_count' => $this->staff_count,
            'staff_patient_ratio' => $this->staff_patient_ratio,
            'operating_hours' => $this->operating_hours,
            'medical_facilities' => $this->medical_facilities,
            'amenities' => $this->amenities,
            'transport_info' => $this->transport_info,
            'languages_supported' => $this->languages_supported,
            'government_subsidies' => $this->government_subsidies,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'average_rating' => $this->average_rating,
            'is_license_expired' => $this->isLicenseExpired(),
            'is_license_expiring_soon' => $this->isLicenseExpiringSoon(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'staff' => StaffResource::collection($this->whenLoaded('staff')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'testimonials' => TestimonialResource::collection($this->whenLoaded('testimonials')),
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all center fields
- [ ] Include: services, staff, media, testimonials (when loaded)
- [ ] Calculate: occupancy_rate, available_capacity, average_rating
- [ ] Add: license status checks
- [ ] Add: timestamps

##### File: `app/Http/Resources/ServiceResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'formatted_price' => $this->formatted_price,
            'duration' => $this->duration,
            'features' => $this->features,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'center' => CenterResource::make($this->whenLoaded('center')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all service fields
- [ ] Include: center (when loaded)
- [ ] Include: media (when loaded)
- [ ] Add: formatted_price accessor
- [ ] Add: timestamps

##### File: `app/Http/Resources/StaffResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'center_id' => $this->center_id,
            'name' => $this->name,
            'position' => $this->position,
            'qualifications' => $this->qualifications,
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'photo_url' => $this->photo_url,
            'display_name' => $this->display_name,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all staff fields
- [ ] Include: media (when loaded)
- [ ] Add: photo_url, display_name accessors
- [ ] Add: timestamps

##### File: `app/Http/Resources/MediaResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'mediable_type' => $this->mediable_type,
            'mediable_id' => $this->mediable_id,
            'type' => $this->type,
            'url' => $this->full_url,
            'thumbnail_url' => $this->full_thumbnail_url,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'duration' => $this->duration,
            'caption' => $this->caption,
            'alt_text' => $this->alt_text,
            'cloudflare_stream_id' => $this->cloudflare_stream_id,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all media fields
- [ ] Add: full_url, full_thumbnail_url, formatted_size accessors
- [ ] Add: timestamps

##### File: `app/Http/Resources/FAQResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FAQResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'question' => $this->question,
            'answer' => $this->answer,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all FAQ fields
- [ ] Add: timestamps

##### File: `app/Http/Resources/ContentTranslationResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'translatable_type' => $this->translatable_type,
            'translatable_id' => $this->translatable_id,
            'locale' => $this->locale,
            'field' => $this->field,
            'value' => $this->value,
            'translation_status' => $this->translation_status,
            'translated_by' => $this->translated_by,
            'reviewed_by' => $this->reviewed_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'translator' => UserResource::make($this->whenLoaded('translator')),
            'reviewer' => UserResource::make($this->whenLoaded('reviewer')),
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all translation fields
- [ ] Include: translator, reviewer (when loaded)
- [ ] Add: timestamps

**Controllers (6 files)**

##### File: `app/Http/Controllers/Api/V1/CenterController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Services\Center\CenterService;
use App\Http\Resources\CenterResource;
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
     * Display a listing of centers.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 'city', 'accreditation_status', 'search', 'has_capacity'
        ]);
        
        $perPage = $request->get('per_page', 15);
        $centers = $this->centerService->getCenters($filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => [
                'centers' => CenterResource::collection($centers),
                'pagination' => [
                    'current_page' => $centers->currentPage(),
                    'last_page' => $centers->lastPage(),
                    'per_page' => $centers->perPage(),
                    'total' => $centers->total(),
                    'from' => $centers->firstItem(),
                    'to' => $centers->lastItem(),
                ],
            ],
        ]);
    }
    
    /**
     * Store a newly created center in storage.
     */
    public function store(StoreCenterRequest $request): JsonResponse
    {
        $mediaFiles = $request->file('media_files', []);
        $center = $this->centerService->createCenter($request->validated(), $mediaFiles);
        
        return response()->json([
            'success' => true,
            'message' => 'Center created successfully.',
            'data' => [
                'center' => CenterResource::make($center),
            ],
        ], 201);
    }
    
    /**
     * Display the specified center.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $center = $this->centerService->getCenterById($id);
        
        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'Center not found.',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'center' => CenterResource::make($center),
            ],
        ]);
    }
    
    /**
     * Update the specified center in storage.
     */
    public function update(UpdateCenterRequest $request, $id): JsonResponse
    {
        $center = $this->centerService->getCenterById($id);
        
        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'Center not found.',
            ], 404);
        }
        
        $mediaFiles = $request->file('media_files', []);
        $center = $this->centerService->updateCenter($center, $request->validated(), $mediaFiles);
        
        return response()->json([
            'success' => true,
            'message' => 'Center updated successfully.',
            'data' => [
                'center' => CenterResource::make($center),
            ],
        ]);
    }
    
    /**
     * Remove the specified center from storage.
     */
    public function destroy($id): JsonResponse
    {
        $center = $this->centerService->getCenterById($id);
        
        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'Center not found.',
            ], 404);
        }
        
        $this->centerService->deleteCenter($center);
        
        return response()->json([
            'success' => true,
            'message' => 'Center deleted successfully.',
        ]);
    }
    
    /**
     * Search centers.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $limit = $request->get('limit', 20);
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required.',
            ], 400);
        }
        
        $centers = $this->centerService->searchCenters($query, $limit);
        
        return response()->json([
            'success' => true,
            'data' => [
                'centers' => CenterResource::collection($centers),
            ],
        ]);
    }
    
    /**
     * Get centers near a location.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);
        
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $radius = $request->get('radius', 10);
        
        $centers = $this->centerService->getNearbyCenters($latitude, $longitude, $radius);
        
        return response()->json([
            'success' => true,
            'data' => [
                'centers' => CenterResource::collection($centers),
            ],
        ]);
    }
    
    /**
     * Get unique cities.
     */
    public function cities(): JsonResponse
    {
        $cities = $this->centerService->getUniqueCities();
        
        return response()->json([
            'success' => true,
            'data' => [
                'cities' => $cities,
            ],
        ]);
    }
    
    /**
     * Get center statistics.
     */
    public function statistics(Request $request, $id): JsonResponse
    {
        $center = $this->centerService->getCenterById($id);
        
        if (!$center) {
            return response()->json([
                'success' => false,
                'message' => 'Center not found.',
            ], 404);
        }
        
        $statistics = $this->centerService->getCenterStatistics($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement center listing with filtering and pagination
- [ ] Implement center creation with media upload
- [ ] Implement center details retrieval
- [ ] Implement center update with media upload
- [ ] Implement center deletion
- [ ] Implement center search
- [ ] Implement nearby centers
- [ ] Implement unique cities
- [ ] Implement center statistics
- [ ] Add proper error handling
- [ ] Add authorization checks

##### File: `app/Http/Controllers/Api/V1/Center/ServiceController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Services\Center\ServiceManagementService;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    protected ServiceManagementService $serviceManagementService;
    
    public function __construct(ServiceManagementService $serviceManagementService)
    {
        $this->serviceManagementService = $serviceManagementService;
    }
    
    /**
     * Display a listing of services for a center.
     */
    public function index($centerId): JsonResponse
    {
        $services = $this->serviceManagementService->getServicesByCenterId($centerId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'services' => ServiceResource::collection($services),
            ],
        ]);
    }
    
    /**
     * Store a newly created service in storage.
     */
    public function store(StoreServiceRequest $request, $centerId): JsonResponse
    {
        $center = \App\Models\Center::findOrFail($centerId);
        $mediaFiles = $request->file('media_files', []);
        $service = $this->serviceManagementService->createService($center, $request->validated(), $mediaFiles);
        
        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => [
                'service' => ServiceResource::make($service),
            ],
        ], 201);
    }
    
    /**
     * Display the specified service.
     */
    public function show($centerId, $id): JsonResponse
    {
        $service = $this->serviceManagementService->getServiceById($id);
        
        if (!$service || $service->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'service' => ServiceResource::make($service),
            ],
        ]);
    }
    
    /**
     * Update the specified service in storage.
     */
    public function update(UpdateServiceRequest $request, $centerId, $id): JsonResponse
    {
        $service = $this->serviceManagementService->getServiceById($id);
        
        if (!$service || $service->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }
        
        $mediaFiles = $request->file('media_files', []);
        $service = $this->serviceManagementService->updateService($service, $request->validated(), $mediaFiles);
        
        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => [
                'service' => ServiceResource::make($service),
            ],
        ]);
    }
    
    /**
     * Remove the specified service from storage.
     */
    public function destroy($centerId, $id): JsonResponse
    {
        $service = $this->serviceManagementService->getServiceById($id);
        
        if (!$service || $service->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }
        
        $this->serviceManagementService->deleteService($service);
        
        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }
    
    /**
     * Get service statistics.
     */
    public function statistics($centerId, $id): JsonResponse
    {
        $service = $this->serviceManagementService->getServiceById($id);
        
        if (!$service || $service->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found.',
            ], 404);
        }
        
        $statistics = $this->serviceManagementService->getServiceStatistics($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $statistics,
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement service listing for a center
- [ ] Implement service creation with media upload
- [ ] Implement service details retrieval
- [ ] Implement service update with media upload
- [ ] Implement service deletion
- [ ] Implement service statistics
- [ ] Add proper error handling
- [ ] Add authorization checks

##### File: `app/Http/Controllers/Api/V1/Center/StaffController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Services\Center\StaffService;
use App\Http\Resources\StaffResource;
use Illuminate\Http\JsonResponse;

class StaffController extends Controller
{
    protected StaffService $staffService;
    
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }
    
    /**
     * Display a listing of staff for a center.
     */
    public function index($centerId): JsonResponse
    {
        $staff = $this->staffService->getStaffByCenterId($centerId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'staff' => StaffResource::collection($staff),
            ],
        ]);
    }
    
    /**
     * Store a newly created staff member in storage.
     */
    public function store(StoreStaffRequest $request, $centerId): JsonResponse
    {
        $center = \App\Models\Center::findOrFail($centerId);
        $photo = $request->file('photo');
        $staff = $this->staffService->createStaff($center, $request->validated(), $photo);
        
        return response()->json([
            'success' => true,
            'message' => 'Staff member created successfully.',
            'data' => [
                'staff' => StaffResource::make($staff),
            ],
        ], 201);
    }
    
    /**
     * Display the specified staff member.
     */
    public function show($centerId, $id): JsonResponse
    {
        $staff = $this->staffService->getStaffById($id);
        
        if (!$staff || $staff->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found.',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'staff' => StaffResource::make($staff),
            ],
        ]);
    }
    
    /**
     * Update the specified staff member in storage.
     */
    public function update(UpdateStaffRequest $request, $centerId, $id): JsonResponse
    {
        $staff = $this->staffService->getStaffById($id);
        
        if (!$staff || $staff->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found.',
            ], 404);
        }
        
        $photo = $request->file('photo');
        $staff = $this->staffService->updateStaff($staff, $request->validated(), $photo);
        
        return response()->json([
            'success' => true,
            'message' => 'Staff member updated successfully.',
            'data' => [
                'staff' => StaffResource::make($staff),
            ],
        ]);
    }
    
    /**
     * Remove the specified staff member from storage.
     */
    public function destroy($centerId, $id): JsonResponse
    {
        $staff = $this->staffService->getStaffById($id);
        
        if (!$staff || $staff->center_id != $centerId) {
            return response()->json([
                'success' => false,
                'message' => 'Staff member not found.',
            ], 404);
        }
        
        $this->staffService->deleteStaff($staff);
        
        return response()->json([
            'success' => true,
            'message' => 'Staff member deleted successfully.',
        ]);
    }
    
    /**
     * Reorder staff display order.
     */
    public function reorder(Request $request, $centerId): JsonResponse
    {
        $request->validate([
            'staff_ids' => ['required', 'array', 'min:1'],
            'staff_ids.*' => ['integer', 'exists:staff,id'],
        ]);
        
        $this->staffService->reorderStaff($centerId, $request->get('staff_ids'));
        
        return response()->json([
            'success' => true,
            'message' => 'Staff order updated successfully.',
        ]);
    }
}
```
**Checklist:**
- [ ] Implement staff listing for a center
- [ ] Implement staff creation with photo upload
- [ ] Implement staff details retrieval
- [ ] Implement staff update with photo replacement
- [ ] Implement staff deletion
- [ ] Implement staff reordering
- [ ] Add proper error handling
- [ ] Add authorization checks

##### File: `app/Http/Controllers/Api/V1/MediaController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Requests\Media\ReorderMediaRequest;
use App\Services\Media\MediaService;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    protected MediaService $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }
    
    /**
     * Display a listing of media.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'mediable_type', 'mediable_id', 'type'
        ]);
        
        $media = $this->mediaService->getMedia($filters);
        
        return response()->json([
            'success' => true,
            'data' => [
                'media' => MediaResource::collection($media),
            ],
        ]);
    }
    
    /**
     * Store a newly created media in storage.
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $mediableType = $request->get('mediable_type');
        $mediableId = $request->get('mediable_id');
        
        // Find the model
        $modelClass = 'App\\Models\\' . $mediableType;
        $model = $modelClass::findOrFail($mediableId);
        
        $file = $request->file('file');
        $options = $request->only(['caption', 'alt_text', 'display_order']);
        
        $media = $this->mediaService->uploadMedia($model, $file, $options);
        
        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully.',
            'data' => [
                'media' => MediaResource::make($media),
            ],
        ], 201);
    }
    
    /**
     * Display the specified media.
     */
    public function show(Media $media): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'media' => MediaResource::make($media),
            ],
        ]);
    }
    
    /**
     * Update the specified media in storage.
     */
    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        $media = $this->mediaService->updateMedia($media, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully.',
            'data' => [
                'media' => MediaResource::make($media),
            ],
        ]);
    }
    
    /**
     * Remove the specified media from storage.
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->mediaService->deleteMedia($media);
        
        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully.',
        ]);
    }
    
    /**
     * Reorder media display order.
     */
    public function reorder(ReorderMediaRequest $request): JsonResponse
    {
        $mediaIds = $request->get('media_ids');
        
        // Get the first media to determine the model
        $firstMedia = Media::findOrFail($mediaIds[0]);
        
        $this->mediaService->reorderMedia(
            $firstMedia->mediable_type,
            $firstMedia->mediable_id,
            $mediaIds
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Media order updated successfully.',
        ]);
    }
}
```
**Checklist:**
- [ ] Implement media listing with filtering
- [ ] Implement media upload
- [ ] Implement media details retrieval
- [ ] Implement media update
- [ ] Implement media deletion
- [ ] Implement media reordering
- [ ] Add proper error handling
- [ ] Add authorization checks

##### File: `app/Http/Controllers/Api/V1/FAQController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FAQ\StoreFAQRequest;
use App\Http\Requests\FAQ\UpdateFAQRequest;
use App\Models\FAQ;
use App\Http\Resources\FAQResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    /**
     * Display a listing of FAQs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FAQ::query();
        
        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        $faqs = $query->ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'faqs' => FAQResource::collection($faqs),
            ],
        ]);
    }
    
    /**
     * Store a newly created FAQ in storage.
     */
    public function store(StoreFAQRequest $request): JsonResponse
    {
        $faq = FAQ::create($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'data' => [
                'faq' => FAQResource::make($faq),
            ],
        ], 201);
    }
    
    /**
     * Display the specified FAQ.
     */
    public function show(FAQ $faq): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'faq' => FAQResource::make($faq),
            ],
        ]);
    }
    
    /**
     * Update the specified FAQ in storage.
     */
    public function update(UpdateFAQRequest $request, FAQ $faq): JsonResponse
    {
        $faq->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully.',
            'data' => [
                'faq' => FAQResource::make($faq),
            ],
        ]);
    }
    
    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(FAQ $faq): JsonResponse
    {
        $faq->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully.',
        ]);
    }
}
```
**Checklist:**
- [ ] Implement FAQ listing with filtering
- [ ] Implement FAQ creation
- [ ] Implement FAQ details retrieval
- [ ] Implement FAQ update
- [ ] Implement FAQ deletion
- [ ] Add proper error handling
- [ ] Add authorization checks

**Routes (1 file)**

##### File: `routes/api.php` (CENTER section)
```php
<?php

use App\Http\Controllers\Api\V1\CenterController;
use App\Http\Controllers\Api\V1\Center\ServiceController;
use App\Http\Controllers\Api\V1\Center\StaffController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\FAQController;

/*
|--------------------------------------------------------------------------
| API Routes - Centers & Services
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Centers
    Route::get('centers', [CenterController::class, 'index']);
    Route::get('centers/search', [CenterController::class, 'search']);
    Route::get('centers/nearby', [CenterController::class, 'nearby']);
    Route::get('centers/cities', [CenterController::class, 'cities']);
    Route::get('centers/{id}', [CenterController::class, 'show']);
    Route::get('centers/{id}/statistics', [CenterController::class, 'statistics']);
    
    // Center Services
    Route::get('centers/{center_id}/services', [ServiceController::class, 'index']);
    Route::get('centers/{center_id}/services/{id}', [ServiceController::class, 'show']);
    Route::get('centers/{center_id}/services/{id}/statistics', [ServiceController::class, 'statistics']);
    
    // Center Staff
    Route::get('centers/{center_id}/staff', [StaffController::class, 'index']);
    Route::get('centers/{center_id}/staff/{id}', [StaffController::class, 'show']);
    
    // FAQs
    Route::get('faqs', [FAQController::class, 'index']);
    Route::get('faqs/{id}', [FAQController::class, 'show']);
});

// Protected routes (admin only)
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('v1')->group(function () {
    // Centers
    Route::post('centers', [CenterController::class, 'store']);
    Route::put('centers/{id}', [CenterController::class, 'update']);
    Route::delete('centers/{id}', [CenterController::class, 'destroy']);
    
    // Center Services
    Route::post('centers/{center_id}/services', [ServiceController::class, 'store']);
    Route::put('centers/{center_id}/services/{id}', [ServiceController::class, 'update']);
    Route::delete('centers/{center_id}/services/{id}', [ServiceController::class, 'destroy']);
    
    // Center Staff
    Route::post('centers/{center_id}/staff', [StaffController::class, 'store']);
    Route::put('centers/{center_id}/staff/{id}', [StaffController::class, 'update']);
    Route::delete('centers/{center_id}/staff/{id}', [StaffController::class, 'destroy']);
    Route::post('centers/{center_id}/staff/reorder', [StaffController::class, 'reorder']);
    
    // Media
    Route::post('media', [MediaController::class, 'store']);
    Route::put('media/{id}', [MediaController::class, 'update']);
    Route::delete('media/{id}', [MediaController::class, 'destroy']);
    Route::post('media/reorder', [MediaController::class, 'reorder']);
    
    // FAQs
    Route::post('faqs', [FAQController::class, 'store']);
    Route::put('faqs/{id}', [FAQController::class, 'update']);
    Route::delete('faqs/{id}', [FAQController::class, 'destroy']);
});
```
**Checklist:**
- [ ] Define public routes for centers, services, staff, FAQs
- [ ] Define protected routes for admin operations
- [ ] Add proper route grouping
- [ ] Add route parameters

**Tests (4 files minimum)**

##### File: `tests/Feature/Center/CenterManagementTest.php`
```php
<?php

namespace Tests\Feature\Center;

use App\Models\User;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CenterManagementTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $admin;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }
    
    /**
     * Test admin can create center
     */
    public function test_admin_can_create_center(): void
    {
        $centerData = [
            'name' => 'Test Center',
            'description' => 'Test description',
            'address' => '123 Test Street',
            'city' => 'Singapore',
            'postal_code' => '123456',
            'phone' => '+6581234567',
            'email' => 'test@example.com',
            'moh_license_number' => 'MOH/2023/12345',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'accreditation_status' => 'accredited',
            'capacity' => 100,
            'current_occupancy' => 50,
            'staff_count' => 10,
            'status' => 'published',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/centers', $centerData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'center' => [
                        'id',
                        'name',
                        'description',
                        'address',
                        'city',
                        'postal_code',
                        'phone',
                        'email',
                        'moh_license_number',
                        'license_expiry_date',
                        'accreditation_status',
                        'capacity',
                        'current_occupancy',
                        'staff_count',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('centers', [
            'name' => 'Test Center',
            'moh_license_number' => 'MOH/2023/12345',
        ]);
    }
    
    /**
     * Test admin can update center
     */
    public function test_admin_can_update_center(): void
    {
        $center = Center::factory()->create();
        
        $updateData = [
            'name' => 'Updated Center',
            'description' => 'Updated description',
            'capacity' => 150,
        ];
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/centers/{$center->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'center' => [
                        'id',
                        'name',
                        'description',
                        'capacity',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('centers', [
            'id' => $center->id,
            'name' => 'Updated Center',
            'description' => 'Updated description',
            'capacity' => 150,
        ]);
    }
    
    /**
     * Test admin can delete center
     */
    public function test_admin_can_delete_center(): void
    {
        $center = Center::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/centers/{$center->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Center deleted successfully.',
            ]);
        
        $this->assertSoftDeleted('centers', [
            'id' => $center->id,
        ]);
    }
    
    /**
     * Test user cannot create center
     */
    public function test_user_cannot_create_center(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $centerData = [
            'name' => 'Test Center',
            'description' => 'Test description',
            'address' => '123 Test Street',
            'city' => 'Singapore',
            'postal_code' => '123456',
            'phone' => '+6581234567',
            'email' => 'test@example.com',
            'moh_license_number' => 'MOH/2023/12345',
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'accreditation_status' => 'accredited',
            'capacity' => 100,
            'current_occupancy' => 50,
            'staff_count' => 10,
            'status' => 'published',
        ];
        
        $response = $this->actingAs($user)
            ->postJson('/api/v1/centers', $centerData);
        
        $response->assertStatus(403);
    }
    
    /**
     * Test center validation works
     */
    public function test_center_validation_works(): void
    {
        $invalidData = [
            'name' => '',
            'description' => '',
            'address' => '',
            'city' => '',
            'postal_code' => 'invalid',
            'phone' => 'invalid',
            'email' => 'invalid',
            'moh_license_number' => '',
            'license_expiry_date' => '2020-01-01', // Past date
            'accreditation_status' => 'invalid',
            'capacity' => 0, // Must be at least 1
            'current_occupancy' => -1, // Must be at least 0
            'staff_count' => -1, // Must be at least 0
            'status' => 'invalid',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/centers', $invalidData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'description',
                'address',
                'city',
                'postal_code',
                'phone',
                'email',
                'moh_license_number',
                'license_expiry_date',
                'accreditation_status',
                'capacity',
                'current_occupancy',
                'staff_count',
                'status',
            ]);
    }
    
    /**
     * Test MOH license uniqueness validation
     */
    public function test_moh_license_uniqueness_validation(): void
    {
        // Create center with MOH license
        $center = Center::factory()->create([
            'moh_license_number' => 'MOH/2023/12345',
        ]);
        
        // Try to create another center with same MOH license
        $centerData = [
            'name' => 'Test Center 2',
            'description' => 'Test description',
            'address' => '123 Test Street',
            'city' => 'Singapore',
            'postal_code' => '123456',
            'phone' => '+6581234567',
            'email' => 'test2@example.com',
            'moh_license_number' => 'MOH/2023/12345', // Same license
            'license_expiry_date' => now()->addYear()->format('Y-m-d'),
            'accreditation_status' => 'accredited',
            'capacity' => 100,
            'current_occupancy' => 50,
            'staff_count' => 10,
            'status' => 'published',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/centers', $centerData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'moh_license_number',
            ]);
    }
}
```
**Checklist:**
- [ ] Test: admin can create center
- [ ] Test: admin can update center
- [ ] Test: admin can delete center
- [ ] Test: user cannot create center
- [ ] Test: center validation works
- [ ] Test: MOH license uniqueness validation

##### File: `tests/Feature/Center/ServiceManagementTest.php`
```php
<?php

namespace Tests\Feature\Center;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $admin;
    protected Center $center;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->center = Center::factory()->create();
    }
    
    /**
     * Test admin can create service for center
     */
    public function test_admin_can_create_service_for_center(): void
    {
        $serviceData = [
            'name' => 'Test Service',
            'description' => 'Test service description',
            'price' => 50.00,
            'price_unit' => 'day',
            'duration' => 'Full day',
            'features' => ['meals_included', 'medication_management'],
            'status' => 'published',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/centers/{$this->center->id}/services", $serviceData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'service' => [
                        'id',
                        'center_id',
                        'name',
                        'description',
                        'price',
                        'price_unit',
                        'duration',
                        'features',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('services', [
            'center_id' => $this->center->id,
            'name' => 'Test Service',
            'price' => 50.00,
            'price_unit' => 'day',
        ]);
    }
    
    /**
     * Test admin can update service
     */
    public function test_admin_can_update_service(): void
    {
        $service = Service::factory()->create(['center_id' => $this->center->id]);
        
        $updateData = [
            'name' => 'Updated Service',
            'description' => 'Updated description',
            'price' => 75.00,
        ];
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/centers/{$this->center->id}/services/{$service->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'service' => [
                        'id',
                        'name',
                        'description',
                        'price',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
            'description' => 'Updated description',
            'price' => 75.00,
        ]);
    }
    
    /**
     * Test admin can delete service
     */
    public function test_admin_can_delete_service(): void
    {
        $service = Service::factory()->create(['center_id' => $this->center->id]);
        
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/centers/{$this->center->id}/services/{$service->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Service deleted successfully.',
            ]);
        
        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }
    
    /**
     * Test user can view services for center
     */
    public function test_user_can_view_services_for_center(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        // Create services for center
        Service::factory()->count(3)->create(['center_id' => $this->center->id]);
        
        $response = $this->actingAs($user)
            ->getJson("/api/v1/centers/{$this->center->id}/services");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'services' => [
                        '*' => [
                            'id',
                            'center_id',
                            'name',
                            'description',
                            'price',
                            'price_unit',
                            'status',
                        ],
                    ],
                ],
            ]);
        
        $this->assertEquals(3, count($response->json('data.services')));
    }
    
    /**
     * Test service validation works
     */
    public function test_service_validation_works(): void
    {
        $invalidData = [
            'name' => '',
            'description' => '',
            'price' => -10, // Must be at least 0
            'price_unit' => 'invalid',
            'status' => 'invalid',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/centers/{$this->center->id}/services", $invalidData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'description',
                'price',
                'price_unit',
                'status',
            ]);
    }
}
```
**Checklist:**
- [ ] Test: admin can create service for center
- [ ] Test: admin can update service
- [ ] Test: admin can delete service
- [ ] Test: user can view services for center
- [ ] Test: service validation works

##### File: `tests/Feature/Center/StaffManagementTest.php`
```php
<?php

namespace Tests\Feature\Center;

use App\Models\User;
use App\Models\Center;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $admin;
    protected Center $center;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->center = Center::factory()->create();
        Storage::fake('public');
    }
    
    /**
     * Test admin can add staff to center
     */
    public function test_admin_can_add_staff_to_center(): void
    {
        $staffData = [
            'name' => 'John Doe',
            'position' => 'Registered Nurse',
            'qualifications' => ['RN', 'CPR Certified'],
            'years_of_experience' => 5,
            'bio' => 'Experienced nurse with 5 years of experience',
            'status' => 'active',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/centers/{$this->center->id}/staff", $staffData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'staff' => [
                        'id',
                        'center_id',
                        'name',
                        'position',
                        'qualifications',
                        'years_of_experience',
                        'bio',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('staff', [
            'center_id' => $this->center->id,
            'name' => 'John Doe',
            'position' => 'Registered Nurse',
        ]);
    }
    
    /**
     * Test admin can update staff
     */
    public function test_admin_can_update_staff(): void
    {
        $staff = Staff::factory()->create(['center_id' => $this->center->id]);
        
        $updateData = [
            'name' => 'Jane Smith',
            'position' => 'Senior Nurse',
            'years_of_experience' => 10,
        ];
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/centers/{$this->center->id}/staff/{$staff->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'staff' => [
                        'id',
                        'name',
                        'position',
                        'years_of_experience',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'name' => 'Jane Smith',
            'position' => 'Senior Nurse',
            'years_of_experience' => 10,
        ]);
    }
    
    /**
     * Test admin can delete staff
     */
    public function test_admin_can_delete_staff(): void
    {
        $staff = Staff::factory()->create(['center_id' => $this->center->id]);
        
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/centers/{$this->center->id}/staff/{$staff->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Staff member deleted successfully.',
            ]);
        
        $this->assertSoftDeleted('staff', [
            'id' => $staff->id,
        ]);
    }
    
    /**
     * Test admin can reorder staff
     */
    public function test_admin_can_reorder_staff(): void
    {
        // Create staff members
        $staff1 = Staff::factory()->create(['center_id' => $this->center->id, 'display_order' => 1]);
        $staff2 = Staff::factory()->create(['center_id' => $this->center->id, 'display_order' => 2]);
        $staff3 = Staff::factory()->create(['center_id' => $this->center->id, 'display_order' => 3]);
        
        // Reorder: staff3 (1), staff1 (2), staff2 (3)
        $reorderData = [
            'staff_ids' => [$staff3->id, $staff1->id, $staff2->id],
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/centers/{$this->center->id}/staff/reorder", $reorderData);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Staff order updated successfully.',
            ]);
        
        // Verify new order
        $this->assertEquals(1, $staff3->fresh()->display_order);
        $this->assertEquals(2, $staff1->fresh()->display_order);
        $this->assertEquals(3, $staff2->fresh()->display_order);
    }
    
    /**
     * Test user can view staff for center
     */
    public function test_user_can_view_staff_for_center(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        
        // Create staff for center
        Staff::factory()->count(3)->create(['center_id' => $this->center->id]);
        
        $response = $this->actingAs($user)
            ->getJson("/api/v1/centers/{$this->center->id}/staff");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'staff' => [
                        '*' => [
                            'id',
                            'center_id',
                            'name',
                            'position',
                            'status',
                        ],
                    ],
                ],
            ]);
        
        $this->assertEquals(3, count($response->json('data.staff')));
    }
}
```
**Checklist:**
- [ ] Test: admin can add staff to center
- [ ] Test: admin can update staff
- [ ] Test: admin can delete staff
- [ ] Test: admin can reorder staff
- [ ] Test: user can view staff for center

##### File: `tests/Feature/Media/MediaUploadTest.php`
```php
<?php

namespace Tests\Feature\Media;

use App\Models\User;
use App\Models\Center;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $admin;
    protected Center $center;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->center = Center::factory()->create();
        Storage::fake('public');
    }
    
    /**
     * Test admin can upload image
     */
    public function test_admin_can_upload_image(): void
    {
        $file = UploadedFile::fake()->image('test-image.jpg');
        
        $mediaData = [
            'mediable_type' => 'Center',
            'mediable_id' => $this->center->id,
            'caption' => 'Test image caption',
            'alt_text' => 'Test image alt text',
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/media', array_merge($mediaData, [
                'file' => $file,
            ]));
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'media' => [
                        'id',
                        'mediable_type',
                        'mediable_id',
                        'type',
                        'url',
                        'filename',
                        'mime_type',
                        'size',
                        'caption',
                        'alt_text',
                        'created_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('media', [
            'mediable_type' => 'Center',
            'mediable_id' => $this->center->id,
            'type' => 'image',
            'caption' => 'Test image caption',
            'alt_text' => 'Test image alt text',
        ]);
        
        // Verify file was stored
        Storage::disk('public')->assertExists($response->json('data.media.url'));
    }
    
    /**
     * Test admin can update media metadata
     */
    public function test_admin_can_update_media_metadata(): void
    {
        // Create media
        $media = Media::factory()->create([
            'mediable_type' => 'Center',
            'mediable_id' => $this->center->id,
            'type' => 'image',
        ]);
        
        $updateData = [
            'caption' => 'Updated caption',
            'alt_text' => 'Updated alt text',
        ];
        
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/media/{$media->id}", $updateData);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'media' => [
                        'id',
                        'caption',
                        'alt_text',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'caption' => 'Updated caption',
            'alt_text' => 'Updated alt text',
        ]);
    }
    
    /**
     * Test admin can delete media
     */
    public function test_admin_can_delete_media(): void
    {
        // Create media with file
        $file = UploadedFile::fake()->image('test-image.jpg');
        $path = $file->store('test', 'public');
        
        $media = Media::factory()->create([
            'mediable_type' => 'Center',
            'mediable_id' => $this->center->id,
            'type' => 'image',
            'url' => $path,
        ]);
        
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/media/{$media->id}");
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Media deleted successfully.',
            ]);
        
        $this->assertDatabaseMissing('media', [
            'id' => $media->id,
        ]);
        
        // Verify file was deleted
        Storage::disk('public')->assertMissing($path);
    }
    
    /**
     * Test media validation works
     */
    public function test_media_validation_works(): void
    {
        $file = UploadedFile::fake()->create('test-file.txt', 100); // 100 bytes
        
        $mediaData = [
            'mediable_type' => '',
            'mediable_id' => '',
            'caption' => str_repeat('a', 501), // Too long
            'alt_text' => str_repeat('a', 256), // Too long
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/media', array_merge($mediaData, [
                'file' => $file,
            ]));
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'mediable_type',
                'mediable_id',
                'caption',
                'alt_text',
            ]);
    }
    
    /**
     * Test file size validation
     */
    public function test_file_size_validation(): void
    {
        // Create a file larger than 10MB
        $file = UploadedFile::fake()->create('large-file.jpg', 15000); // 15MB
        
        $mediaData = [
            'mediable_type' => 'Center',
            'mediable_id' => $this->center->id,
        ];
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/media', array_merge($mediaData, [
                'file' => $file,
            ]));
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'file',
            ]);
    }
}
```
**Checklist:**
- [ ] Test: admin can upload image
- [ ] Test: admin can update media metadata
- [ ] Test: admin can delete media
- [ ] Test: media validation works
- [ ] Test: file size validation

### C.4. Workstream Validation

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
   - [ ] Admin can CRUD centers
   - [ ] Admin can CRUD services
   - [ ] Admin can CRUD staff
   - [ ] Media upload and management works
   - [ ] Search functionality works
   - [ ] MOH validation works

4. **Documentation**
   - [ ] All methods documented with PHPDoc
   - [ ] API endpoints documented in OpenAPI
   - [ ] Code reviewed by peer

5. **Security**
   - [ ] Authorization checks work correctly
   - [ ] Input validation works
   - [ ] File upload security measures in place

## Workstream D: Booking System (4-5 days)

**Owner:** Backend Dev 2
**Dependencies:** Workstream C (centers/services must exist)
**Priority:** HIGH (demo requirement)

### D.1. Prerequisites & Setup
Before starting this workstream, ensure:
- [ ] Workstream C is complete and all tests pass
- [ ] Center and Service management is functional
- [ ] Calendly API credentials are available
- [ ] Twilio API credentials are available

### D.2. Implementation Sequence
1. Create Models (1 file)
2. Create Repositories (1 file)
3. Create Service Classes (5 files)
4. Create Request Validation Classes (3 files)
5. Create Resource Transformers (1 file)
6. Create Controllers (2 files)
7. Create Jobs (4 files)
8. Create Events (3 files)
9. Create Listeners (3 files)
10. Create Mail Templates (3 files)
11. Create Routes (1 file)
12. Create Tests (3 files minimum)
13. Validate workstream completion

### D.3. File Creation Matrix

**Models (1 file)**

##### File: `app/Models/Booking.php`
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
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'booking_date',
        'confirmation_sent_at',
        'reminder_sent_at',
        'created_at',
        'updated_at',
        'deleted_at',
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
     * Scope a query to only include bookings for a specific date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('booking_date', [$startDate, $endDate]);
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
     * Scope a query to only include bookings for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Scope a query to only include bookings for a specific center.
     */
    public function scopeForCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
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
    
    /**
     * Set the booking number attribute.
     */
    public function setBookingNumberAttribute($value)
    {
        if (empty($value)) {
            $date = now()->format('Ymd');
            $sequence = static::whereDate('created_at', now()->toDateString())->count() + 1;
            $this->attributes['booking_number'] = "BK-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        } else {
            $this->attributes['booking_number'] = $value;
        }
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (user, center, service)
- [ ] Add scopes for status, date, user, center filtering
- [ ] Add status checking methods
- [ ] Add formatted date/time method
- [ ] Add URL getters for Calendly integration
- [ ] Add booking number mutator
- [ ] Add soft deletes support

**Repositories (1 file)**

##### File: `app/Repositories/Booking/BookingRepository.php`
```php
<?php

namespace App\Repositories\Booking;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    /**
     * Get bookings with filtering and pagination
     */
    public function getBookings(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::query();
        
        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (isset($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
        
        if (isset($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('booking_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('booking_date', '<=', $filters['date_to']);
        }
        
        return $query->with(['user', 'center', 'service'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->paginate($perPage);
    }
    
    /**
     * Get booking by ID with relationships
     */
    public function findById(int $id): ?Booking
    {
        return Booking::with(['user', 'center', 'service'])->find($id);
    }
    
    /**
     * Get booking by booking number
     */
    public function findByBookingNumber(string $bookingNumber): ?Booking
    {
        return Booking::with(['user', 'center', 'service'])
            ->where('booking_number', $bookingNumber)
            ->first();
    }
    
    /**
     * Create booking
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }
    
    /**
     * Update booking
     */
    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);
        return $booking->fresh();
    }
    
    /**
     * Delete booking (soft delete)
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }
    
    /**
     * Get upcoming bookings for a user
     */
    public function getUpcomingBookings(int $userId): Collection
    {
        return Booking::where('user_id', $userId)
            ->upcoming()
            ->with(['center', 'service'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();
    }
    
    /**
     * Get booking history for a user
     */
    public function getBookingHistory(int $userId): Collection
    {
        return Booking::where('user_id', $userId)
            ->past()
            ->with(['center', 'service'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->get();
    }
    
    /**
     * Get bookings for a center
     */
    public function getBookingsForCenter(int $centerId, array $filters = []): Collection
    {
        $query = Booking::where('center_id', $centerId);
        
        // Apply additional filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('booking_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('booking_date', '<=', $filters['date_to']);
        }
        
        return $query->with(['user', 'service'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();
    }
    
    /**
     * Get bookings that need reminders (24h before)
     */
    public function getBookingsNeedingReminders(): Collection
    {
        return Booking::where('status', 'confirmed')
            ->where('reminder_sent_at', null)
            ->whereRaw("CONCAT(booking_date, ' ', TIME(booking_time)) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)")
            ->with(['user', 'center', 'service'])
            ->get();
    }
    
    /**
     * Get booking statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Booking::query();
        
        // Apply filters
        if (isset($filters['center_id'])) {
            $query->where('center_id', $filters['center_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('booking_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('booking_date', '<=', $filters['date_to']);
        }
        
        $total = $query->count();
        $pending = $query->clone()->where('status', 'pending')->count();
        $confirmed = $query->clone()->where('status', 'confirmed')->count();
        $completed = $query->clone()->where('status', 'completed')->count();
        $cancelled = $query->clone()->where('status', 'cancelled')->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'confirmed' => $confirmed,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ];
    }
}
```
**Checklist:**
- [ ] Implement booking listing with filtering
- [ ] Implement booking retrieval by ID and booking number
- [ ] Implement booking creation
- [ ] Implement booking update
- [ ] Implement booking deletion
- [ ] Implement upcoming bookings retrieval
- [ ] Implement booking history retrieval
- [ ] Implement center bookings retrieval
- [ ] Implement reminder bookings retrieval
- [ ] Implement statistics calculation

**Service Classes (5 files)**

##### File: `app/Services/Booking/BookingService.php`
```php
<?php

namespace App\Services\Booking;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use App\Repositories\Booking\BookingRepository;
use App\Services\Integration\CalendlyService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    protected BookingRepository $bookingRepository;
    protected CalendlyService $calendlyService;
    protected NotificationService $notificationService;
    protected AuditService $auditService;
    
    public function __construct(
        BookingRepository $bookingRepository,
        CalendlyService $calendlyService,
        NotificationService $notificationService,
        AuditService $auditService
    ) {
        $this->bookingRepository = $bookingRepository;
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
        return DB::transaction(function () use ($user, $center, $service, $bookingDate, $bookingTime, $bookingType, $questionnaireResponses) {
            // Create booking
            $booking = $this->bookingRepository->create([
                'user_id' => $user->id,
                'center_id' => $center->id,
                'service_id' => $service?->id,
                'booking_date' => $bookingDate->toDateString(),
                'booking_time' => $bookingTime->toTimeString(),
                'booking_type' => $bookingType,
                'questionnaire_responses' => $questionnaireResponses,
                'status' => 'pending',
            ]);
            
            // Create Calendly event
            $calendlyEvent = $this->calendlyService->createEvent([
                'start_time' => $bookingDate->setTimeFromTimeString($bookingTime->toTimeString()),
                'duration_minutes' => 60, // Default 1 hour
                'name' => "Visit to {$center->name}",
                'description' => $service ? "Service: {$service->name}" : "General visit",
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone,
            ]);
            
            // Update booking with Calendly details
            $this->bookingRepository->update($booking, [
                'calendly_event_id' => $calendlyEvent['id'],
                'calendly_event_uri' => $calendlyEvent['uri'],
                'calendly_cancel_url' => $calendlyEvent['cancel_url'],
                'calendly_reschedule_url' => $calendlyEvent['reschedule_url'],
            ]);
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $booking);
            
            // Trigger event
            event(new \App\Events\BookingCreated($booking));
            
            return $booking;
        });
    }
    
    /**
     * Get booking by ID
     */
    public function getBookingById(int $id): ?Booking
    {
        return $this->bookingRepository->findById($id);
    }
    
    /**
     * Get user's bookings
     */
    public function getUserBookings(User $user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['user_id'] = $user->id;
        return $this->bookingRepository->getBookings($filters);
    }
    
    /**
     * Update a booking
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $oldValues = $booking->toArray();
            
            $booking = $this->bookingRepository->update($booking, $data);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $booking,
                $oldValues,
                $booking->toArray()
            );
            
            return $booking;
        });
    }
    
    /**
     * Cancel a booking
     */
    public function cancelBooking(Booking $booking, string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            // Cancel Calendly event
            if ($booking->calendly_event_id) {
                $this->calendlyService->cancelEvent($booking->calendly_event_id);
            }
            
            // Update booking
            $booking = $this->bookingRepository->update($booking, [
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $booking,
                ['status' => 'pending'],
                ['status' => 'cancelled', 'cancellation_reason' => $reason]
            );
            
            // Trigger event
            event(new \App\Events\BookingCancelled($booking));
            
            return $booking;
        });
    }
    
    /**
     * Confirm a booking
     */
    public function confirmBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = $this->bookingRepository->update($booking, [
                'status' => 'confirmed',
            ]);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $booking,
                ['status' => 'pending'],
                ['status' => 'confirmed']
            );
            
            // Trigger event
            event(new \App\Events\BookingConfirmed($booking));
            
            return $booking;
        });
    }
    
    /**
     * Complete a booking
     */
    public function completeBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = $this->bookingRepository->update($booking, [
                'status' => 'completed',
            ]);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $booking,
                ['status' => 'confirmed'],
                ['status' => 'completed']
            );
            
            return $booking;
        });
    }
    
    /**
     * Get available time slots for a center
     */
    public function getAvailableSlots(Center $center, Carbon $date): array
    {
        // Get existing bookings for the date
        $existingBookings = $this->bookingRepository->getBookingsForCenter($center->id, [
            'date_from' => $date->toDateString(),
            'date_to' => $date->toDateString(),
            'status' => 'confirmed',
        ]);
        
        // Extract booked time slots
        $bookedSlots = $existingBookings->map(function ($booking) {
            return $booking->booking_time->format('H:i');
        })->toArray();
        
        // Generate available slots (every hour from 9am to 5pm)
        $availableSlots = [];
        $startTime = Carbon::parse('09:00');
        $endTime = Carbon::parse('17:00');
        
        while ($startTime <= $endTime) {
            $timeSlot = $startTime->format('H:i');
            
            if (!in_array($timeSlot, $bookedSlots)) {
                $availableSlots[] = [
                    'time' => $timeSlot,
                    'available' => true,
                ];
            } else {
                $availableSlots[] = [
                    'time' => $timeSlot,
                    'available' => false,
                ];
            }
            
            $startTime->addHour();
        }
        
        return $availableSlots;
    }
    
    /**
     * Get booking questionnaire schema
     */
    public function getQuestionnaireSchema(): array
    {
        return [
            'elderly_age' => [
                'type' => 'number',
                'label' => 'Age of elderly person',
                'required' => true,
                'min' => 60,
                'max' => 120,
            ],
            'medical_conditions' => [
                'type' => 'array',
                'label' => 'Medical conditions',
                'required' => false,
                'options' => [
                    'diabetes',
                    'hypertension',
                    'heart_disease',
                    'arthritis',
                    'dementia',
                    'mobility_issues',
                    'other',
                ],
            ],
            'mobility' => [
                'type' => 'string',
                'label' => 'Mobility status',
                'required' => true,
                'options' => [
                    'independent',
                    'walker',
                    'wheelchair',
                    'bedridden',
                ],
            ],
            'special_requirements' => [
                'type' => 'text',
                'label' => 'Special requirements',
                'required' => false,
            ],
        ];
    }
    
    /**
     * Process Calendly webhook
     */
    public function processCalendlyWebhook(array $payload): bool
    {
        try {
            $event = $payload['payload'];
            
            // Find booking by Calendly event ID
            $booking = Booking::where('calendly_event_id', $event['id'])->first();
            
            if (!$booking) {
                return false;
            }
            
            // Process different event types
            switch ($payload['event']) {
                case 'invitee.created':
                    // Confirm booking
                    $this->confirmBooking($booking);
                    break;
                    
                case 'invitee.canceled':
                    // Cancel booking
                    $this->cancelBooking($booking, 'Cancelled via Calendly');
                    break;
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error processing Calendly webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send booking confirmation
     */
    public function sendConfirmation(Booking $booking): void
    {
        $this->notificationService->sendBookingConfirmation($booking);
        
        // Update confirmation sent timestamp
        $this->bookingRepository->update($booking, [
            'confirmation_sent_at' => now(),
        ]);
    }
    
    /**
     * Send booking reminder
     */
    public function sendReminder(Booking $booking): void
    {
        $this->notificationService->sendBookingReminder($booking);
        
        // Update reminder sent timestamp
        $this->bookingRepository->update($booking, [
            'reminder_sent_at' => now(),
        ]);
    }
    
    /**
     * Get center's bookings
     */
    public function getCenterBookings(Center $center, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $filters['center_id'] = $center->id;
        return $this->bookingRepository->getBookingsForCenter($center->id, $filters);
    }
}
```
**Checklist:**
- [ ] Implement booking creation with Calendly integration
- [ ] Implement booking update
- [ ] Implement booking cancellation
- [ ] Implement booking confirmation
- [ ] Implement booking completion
- [ ] Implement available time slots calculation
- [ ] Implement questionnaire schema
- [ ] Implement Calendly webhook processing
- [ ] Implement notification sending
- [ ] Add audit logging
- [ ] Add transaction support

##### File: `app/Services/Integration/CalendlyService.php`
```php
<?php

namespace App\Services\Integration;

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
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/scheduled_events', [
                'start_time' => $eventData['start_time'],
                'duration_minutes' => $eventData['duration_minutes'],
                'name' => $eventData['name'],
                'description' => $eventData['description'],
                'location' => [
                    'type' => 'physical',
                ],
                'invitees' => [
                    [
                        'email' => $eventData['email'],
                        'name' => $eventData['name'],
                        'phone' => $eventData['phone'] ?? null,
                    ],
                ],
            ]);
            
            if ($response->successful()) {
                return $response->json()['resource'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            throw new \Exception('Failed to create Calendly event');
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_data' => $eventData,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get event details
     */
    public function getEvent(string $eventId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/scheduled_events/' . $eventId);
            
            if ($response->successful()) {
                return $response->json()['resource'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            throw new \Exception('Failed to get Calendly event');
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Cancel an event
     */
    public function cancelEvent(string $eventId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/scheduled_events/' . $eventId . '/cancellation');
            
            if ($response->successful()) {
                return true;
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
            ]);
            
            return false;
        }
    }
    
    /**
     * Reschedule an event
     */
    public function rescheduleEvent(string $eventId, array $newTime): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/scheduled_events/' . $eventId . '/reschedule', [
                'start_time' => $newTime['start_time'],
            ]);
            
            if ($response->successful()) {
                return $response->json()['resource'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            throw new \Exception('Failed to reschedule Calendly event');
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'new_time' => $newTime,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get available time slots
     */
    public function getAvailableSlots(string $eventTypeUri, string $startDate, string $endDate): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/event_types/' . basename($eventTypeUri) . '/available_times', [
                'start_time' => $startDate,
                'end_time' => $endDate,
            ]);
            
            if ($response->successful()) {
                return $response->json()['collection'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return [];
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_type_uri' => $eventTypeUri,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            
            return [];
        }
    }
    
    /**
     * Get event type details
     */
    public function getEventType(string $eventTypeId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/event_types/' . $eventTypeId);
            
            if ($response->successful()) {
                return $response->json()['resource'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            throw new \Exception('Failed to get Calendly event type');
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_type_id' => $eventTypeId,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Create a booking invitee
     */
    public function createInvitee(string $eventUri, array $inviteeData): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($eventUri . '/invitees', [
                'email' => $inviteeData['email'],
                'name' => $inviteeData['name'],
                'phone' => $inviteeData['phone'] ?? null,
            ]);
            
            if ($response->successful()) {
                return $response->json()['resource'];
            }
            
            Log::error('Calendly API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            throw new \Exception('Failed to create Calendly invitee');
        } catch (\Exception $e) {
            Log::error('Calendly service error', [
                'error' => $e->getMessage(),
                'event_uri' => $eventUri,
                'invitee_data' => $inviteeData,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSigningKey = config('services.calendly.webhook_signing_key');
        
        if (empty($webhookSigningKey)) {
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSigningKey);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Process webhook payload
     */
    public function processWebhook(array $payload): ?array
    {
        try {
            $event = $payload['event'];
            $payloadData = $payload['payload'];
            
            // Extract relevant data based on event type
            switch ($event) {
                case 'invitee.created':
                return [
                    'event' => $event,
                    'invitee_email' => $payloadData['email'] ?? null,
                    'event_uri' => $payloadData['event'] ?? null,
                    'event_id' => basename($payloadData['event'] ?? ''),
                ];
                    
                case 'invitee.canceled':
                    return [
                        'event' => $event,
                        'invitee_email' => $payloadData['email'] ?? null,
                        'event_uri' => $payloadData['event'] ?? null,
                        'event_id' => basename($payloadData['event'] ?? ''),
                    ];
                    
                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error('Error processing Calendly webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            
            return null;
        }
    }
    
    /**
     * Sync booking with Calendly event
     */
    public function syncBookingWithEvent(Booking $booking, array $eventData): Booking
    {
        try {
            $booking->update([
                'calendly_event_id' => $eventData['id'],
                'calendly_event_uri' => $eventData['uri'],
                'calendly_cancel_url' => $eventData['cancel_url'],
                'calendly_reschedule_url' => $eventData['reschedule_url'],
            ]);
            
            return $booking;
        } catch (\Exception $e) {
            Log::error('Error syncing booking with Calendly event', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'event_data' => $eventData,
            ]);
            
            throw $e;
        }
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
- [ ] Add error handling and logging

##### File: `app/Services/Notification/NotificationService.php`
```php
<?php

namespace App\Services\Notification;

use App\Models\Booking;
use App\Services\Integration\TwilioService;
use App\Services\Integration\EmailService;
use App\Jobs\Booking\SendBookingConfirmationJob;
use App\Jobs\Booking\SendBookingReminderJob;
use App\Jobs\Booking\SendBookingCancellationJob;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected TwilioService $twilioService;
    protected EmailService $emailService;
    
    public function __construct(
        TwilioService $twilioService,
        EmailService $emailService
    ) {
        $this->twilioService = $twilioService;
        $this->emailService = $emailService;
    }
    
    /**
     * Send booking confirmation (email + SMS)
     */
    public function sendBookingConfirmation(Booking $booking): void
    {
        try {
            // Send email
            $this->emailService->sendBookingConfirmationEmail($booking);
            
            // Send SMS
            if ($booking->user->phone) {
                $this->twilioService->sendBookingConfirmationSMS($booking);
            }
            
            // Update booking
            $booking->update([
                'confirmation_sent_at' => now(),
                'sms_sent' => true,
            ]);
            
            Log::info('Booking confirmation sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);
            
            // Queue for retry
            SendBookingConfirmationJob::dispatch($booking)->delay(now()->addMinutes(5));
        }
    }
    
    /**
     * Send booking reminder (24h before)
     */
    public function sendBookingReminder(Booking $booking): void
    {
        try {
            // Send email
            $this->emailService->sendReminderEmail($booking);
            
            // Send SMS
            if ($booking->user->phone) {
                $this->twilioService->sendReminderSMS($booking);
            }
            
            // Update booking
            $booking->update([
                'reminder_sent_at' => now(),
            ]);
            
            Log::info('Booking reminder sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking reminder', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);
            
            // Queue for retry
            SendBookingReminderJob::dispatch($booking)->delay(now()->addMinutes(5));
        }
    }
    
    /**
     * Send booking cancellation notice
     */
    public function sendBookingCancellation(Booking $booking): void
    {
        try {
            // Send email
            $this->emailService->sendCancellationEmail($booking);
            
            // Send SMS
            if ($booking->user->phone) {
                $this->twilioService->sendCancellationSMS($booking);
            }
            
            Log::info('Booking cancellation sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking cancellation', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);
            
            // Queue for retry
            SendBookingCancellationJob::dispatch($booking)->delay(now()->addMinutes(5));
        }
    }
    
    /**
     * Send custom notification
     */
    public function sendCustomNotification(Booking $booking, string $subject, string $message): void
    {
        try {
            // Send email
            $this->emailService->sendCustomEmail($booking, $subject, $message);
            
            // Send SMS
            if ($booking->user->phone) {
                $this->twilioService->sendCustomSMS($booking, $message);
            }
            
            Log::info('Custom notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'subject' => $subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send custom notification', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Implement booking confirmation (email + SMS)
- [ ] Implement booking reminder (24h before)
- [ ] Implement booking cancellation notice
- [ ] Implement custom notification
- [ ] Add error handling and retry logic
- [ ] Add logging

##### File: `app/Services/Integration/TwilioService.php`
```php
<?php

namespace App\Services\Integration;

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
        try {
            $message = $this->client->messages->create($to, $this->from, $message);
            
            Log::info('SMS sent', [
                'to' => $to,
                'from' => $this->from,
                'sid' => $message->sid,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'error' => $e->getMessage(),
                'to' => $to,
                'from' => $this->from,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send booking confirmation SMS
     */
    public function sendBookingConfirmationSMS(Booking $booking): bool
    {
        $message = "Your visit to {$booking->center->name} on {$booking->formatted_date_time} has been confirmed. " .
                   "Details: {$booking->center->address}, {$booking->center->phone}. " .
                   "Cancel: {$booking->cancellation_url}";
        
        return $this->sendSMS($booking->user->phone, $message);
    }
    
    /**
     * Send booking reminder SMS
     */
    public function sendReminderSMS(Booking $booking): bool
    {
        $message = "Reminder: You have a visit to {$booking->center->name} tomorrow at {$booking->booking_time->format('g:i A')}. " .
                   "Address: {$booking->center->address}. " .
                   "Cancel or reschedule: {$booking->cancellation_url}";
        
        return $this->sendSMS($booking->user->phone, $message);
    }
    
    /**
     * Send booking cancellation SMS
     */
    public function sendCancellationSMS(Booking $booking): bool
    {
        $message = "Your visit to {$booking->center->name} on {$booking->formatted_date_time} has been cancelled. " .
                   "If this was a mistake, please contact us at {$booking->center->phone}.";
        
        return $this->sendSMS($booking->user->phone, $message);
    }
    
    /**
     * Send custom SMS
     */
    public function sendCustomSMS(Booking $booking, string $message): bool
    {
        return $this->sendSMS($booking->user->phone, $message);
    }
    
    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Singapore phone number format: +6581234567 or 81234567
        return preg_match('/^(\+65)?[689]\d{7}$/', $phoneNumber);
    }
    
    /**
     * Format phone number to E.164 format
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        
        // Add Singapore country code if missing
        if (strlen($phoneNumber) === 8) {
            $phoneNumber = '65' . $phoneNumber;
        }
        
        return '+' . $phoneNumber;
    }
}
```
**Checklist:**
- [ ] Implement SMS sending
- [ ] Implement booking confirmation SMS
- [ ] Implement booking reminder SMS
- [ ] Implement booking cancellation SMS
- [ ] Implement custom SMS
- [ ] Add phone number validation
- [ ] Add phone number formatting
- [ ] Add error handling and logging

##### File: `app/Services/Integration/EmailService.php`
```php
<?php

namespace App\Services\Integration;

use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingReminderMail;
use App\Mail\BookingCancellationMail;
use App\Mail\CustomNotificationMail;

class EmailService
{
    /**
     * Send email using Laravel Mail
     */
    public function sendEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        try {
            Mail::to($to)->send(new $template($data));
            
            Log::info('Email sent', [
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmationEmail(Booking $booking): bool
    {
        try {
            Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking));
            
            Log::info('Booking confirmation email sent', [
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send booking reminder email
     */
    public function sendReminderEmail(Booking $booking): bool
    {
        try {
            Mail::to($booking->user->email)->send(new BookingReminderMail($booking));
            
            Log::info('Booking reminder email sent', [
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send booking reminder email', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send booking cancellation email
     */
    public function sendCancellationEmail(Booking $booking): bool
    {
        try {
            Mail::to($booking->user->email)->send(new BookingCancellationMail($booking));
            
            Log::info('Booking cancellation email sent', [
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send booking cancellation email', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
            ]);
            
            return false;
        }
    }
    
    /**
     * Send custom notification email
     */
    public function sendCustomEmail(Booking $booking, string $subject, string $message): bool
    {
        try {
            Mail::to($booking->user->email)->send(new CustomNotificationMail($booking, $subject, $message));
            
            Log::info('Custom notification email sent', [
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
                'subject' => $subject,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send custom notification email', [
                'error' => $e->getMessage(),
                'booking_id' => $booking->id,
                'user_email' => $booking->user->email,
                'subject' => $subject,
            ]);
            
            return false;
        }
    }
}
```
**Checklist:**
- [ ] Implement email sending
- [ ] Implement booking confirmation email
- [ ] Implement booking reminder email
- [ ] Implement booking cancellation email
- [ ] Implement custom notification email
- [ ] Add error handling and logging

**Request Validation Classes (3 files)**

##### File: `app/Http/Requests/Booking/StoreBookingRequest.php`
```php
<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can create bookings
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['required', 'in:visit,consultation,trial_day'],
            'questionnaire_responses' => ['nullable', 'array'],
            'questionnaire_responses.elderly_age' => ['required', 'integer', 'min:60', 'max:120'],
            'questionnaire_responses.medical_conditions' => ['nullable', 'array'],
            'questionnaire_responses.medical_conditions.*' => ['string'],
            'questionnaire_responses.mobility' => ['required', 'in:independent,walker,wheelchair,bedridden'],
            'questionnaire_responses.special_requirements' => ['nullable', 'string', 'max:500'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'center_id.exists' => 'The selected center is invalid.',
            'service_id.exists' => 'The selected service is invalid.',
            'booking_date.after_or_equal' => 'The booking date must be today or a future date.',
            'booking_time.date_format' => 'The booking time must be in HH:MM format.',
            'booking_type.in' => 'The booking type must be one of: visit, consultation, trial_day.',
            'questionnaire_responses.elderly_age.min' => 'The elderly age must be at least 60.',
            'questionnaire_responses.elderly_age.max' => 'The elderly age may not be greater than 120.',
            'questionnaire_responses.mobility.required' => 'The mobility status is required.',
            'questionnaire_responses.mobility.in' => 'The selected mobility status is invalid.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validator->after(function ($validator) {
            // Check if the selected time slot is available
            if ($validator->errors()->isEmpty()) {
                $centerId = $this->input('center_id');
                $bookingDate = $this->input('booking_date');
                $bookingTime = $this->input('booking_time');
                
                $bookingDateTime = \Carbon\Carbon::parse($bookingDate . ' ' . $bookingTime);
                
                // Check if the time is during business hours (9am-5pm)
                $startTime = \Carbon\Carbon::parse($bookingDate . ' 09:00');
                $endTime = \Carbon\Carbon::parse($bookingDate . ' 17:00');
                
                if ($bookingDateTime->lt($startTime) || $bookingDateTime->gt($endTime)) {
                    $validator->errors()->add('booking_time', 'The selected time is outside business hours (9am-5pm).');
                }
                
                // Check if the time slot is already booked
                $existingBooking = \App\Models\Booking::where('center_id', $centerId)
                    ->where('booking_date', $bookingDate)
                    ->where('booking_time', $bookingTime)
                    ->where('status', 'confirmed')
                    ->first();
                
                if ($existingBooking) {
                    $validator->errors()->add('booking_time', 'The selected time slot is already booked.');
                }
            }
        });
    }
}
```
**Checklist:**
- [ ] Validate: center_id (exists), service_id (optional, exists)
- [ ] Validate: booking_date (future), booking_time (format)
- [ ] Validate: booking_type (enum)
- [ ] Validate: questionnaire_responses (array with specific fields)
- [ ] Add custom error messages
- [ ] Add business hours validation
- [ ] Add time slot availability validation

##### File: `app/Http/Requests/Booking/CancelBookingRequest.php`
```php
<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('booking')->user_id;
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'A cancellation reason is required.',
            'cancellation_reason.min' => 'The cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'The cancellation reason may not be greater than 500 characters.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validator->after(function ($validator) {
            // Check if booking can be cancelled
            $booking = $this->route('booking');
            
            if ($booking->status === 'completed') {
                $validator->errors()->add('status', 'Completed bookings cannot be cancelled.');
            }
            
            if ($booking->status === 'cancelled') {
                $validator->errors()->add('status', 'This booking is already cancelled.');
            }
            
            if ($booking->isPast()) {
                $validator->errors()->add('status', 'Past bookings cannot be cancelled.');
            }
        });
    }
}
```
**Checklist:**
- [ ] Validate: cancellation_reason (required, min/max length)
- [ ] Add custom error messages
- [ ] Add authorization check
- [ ] Add booking status validation

##### File: `app/Http/Requests/Booking/UpdateBookingRequest.php`
```php
<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('booking')->user_id;
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'booking_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'booking_time' => ['sometimes', 'date_format:H:i'],
            'questionnaire_responses' => ['sometimes', 'array'],
            'questionnaire_responses.elderly_age' => ['sometimes', 'integer', 'min:60', 'max:120'],
            'questionnaire_responses.medical_conditions' => ['sometimes', 'array'],
            'questionnaire_responses.medical_conditions.*' => ['string'],
            'questionnaire_responses.mobility' => ['sometimes', 'in:independent,walker,wheelchair,bedridden'],
            'questionnaire_responses.special_requirements' => ['sometimes', 'string', 'max:500'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'booking_date.after_or_equal' => 'The booking date must be today or a future date.',
            'booking_time.date_format' => 'The booking time must be in HH:MM format.',
            'questionnaire_responses.elderly_age.min' => 'The elderly age must be at least 60.',
            'questionnaire_responses.elderly_age.max' => 'The elderly age may not be greater than 120.',
            'questionnaire_responses.mobility.in' => 'The selected mobility status is invalid.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validator->after(function ($validator) {
            // Check if booking can be updated
            $booking = $this->route('booking');
            
            if ($booking->status === 'completed') {
                $validator->errors()->add('status', 'Completed bookings cannot be updated.');
            }
            
            if ($booking->status === 'cancelled') {
                $validator->errors()->add('status', 'Cancelled bookings cannot be updated.');
            }
            
            if ($booking->isPast()) {
                $validator->errors()->add('status', 'Past bookings cannot be updated.');
            }
            
            // Check if the new time slot is available
            if ($validator->errors()->isEmpty() && ($this->has('booking_date') || $this->has('booking_time'))) {
                $centerId = $booking->center_id;
                $bookingDate = $this->input('booking_date', $booking->booking_date);
                $bookingTime = $this->input('booking_time', $booking->booking_time);
                
                $bookingDateTime = \Carbon\Carbon::parse($bookingDate . ' ' . $bookingTime);
                
                // Check if the time is during business hours (9am-5pm)
                $startTime = \Carbon\Carbon::parse($bookingDate . ' 09:00');
                $endTime = \Carbon\Carbon::parse($bookingDate . ' 17:00');
                
                if ($bookingDateTime->lt($startTime) || $bookingDateTime->gt($endTime)) {
                    $validator->errors()->add('booking_time', 'The selected time is outside business hours (9am-5pm).');
                }
                
                // Check if the time slot is already booked
                $existingBooking = \App\Models\Booking::where('center_id', $centerId)
                    ->where('booking_date', $bookingDate)
                    ->where('booking_time', $bookingTime)
                    ->where('status', 'confirmed')
                    ->where('id', '!=', $booking->id)
                    ->first();
                
                if ($existingBooking) {
                    $validator->errors()->add('booking_time', 'The selected time slot is already booked.');
                }
            }
        });
    }
}
```
**Checklist:**
- [ ] Validate: booking_date, booking_time, questionnaire_responses
- [ ] Add custom error messages
- [ ] Add authorization check
- [ ] Add booking status validation
- [ ] Add time slot availability validation

**Resource Transformers (1 file)**

##### File: `app/Http/Resources/BookingResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'user' => UserResource::make($this->whenLoaded('user')),
            'center' => CenterResource::make($this->whenLoaded('center')),
            'service' => ServiceResource::make($this->whenLoaded('service')),
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'formatted_date_time' => $this->formatted_date_time,
            'booking_type' => $this->booking_type,
            'status' => $this->status,
            'calendly_event_uri' => $this->calendly_event_uri,
            'cancellation_url' => $this->cancellation_url,
            'reschedule_url' => $this->reschedule_url,
            'questionnaire_responses' => $this->questionnaire_responses,
            'cancellation_reason' => $this->cancellation_reason,
            'notes' => $this->notes,
            'confirmation_sent_at' => $this->confirmation_sent_at,
            'reminder_sent_at' => $this->reminder_sent_at,
            'sms_sent' => $this->sms_sent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```
**Checklist:**
- [ ] Transform: all booking fields
- [ ] Include: user, center, service (when loaded)
- [ ] Add: formatted_date_time accessor
- [ ] Add: timestamps

**Controllers (2 files)**

##### File: `app/Http/Controllers/Api/V1/BookingController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Requests\Booking\CancelBookingRequest;
use App\Services\Booking\BookingService;
use App\Http\Resources\BookingResource;
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
     * Display a listing of the user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 'date_from', 'date_to'
        ]);
        
        $perPage = $request->get('per_page', 15);
        $bookings = $this->bookingService->getUserBookings($request->user(), $filters, $perPage);
        
        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => BookingResource::collection($bookings),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem(),
                ],
            ],
        ]);
    }
    
    /**
     * Store a newly created booking in storage.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $user = $request->user();
        $center = \App\Models\Center::findOrFail($request->get('center_id'));
        $service = $request->get('service_id') ? 
            \App\Models\Service::findOrFail($request->get('service_id')) : null;
        
        $bookingDate = \Carbon\Carbon::parse($request->get('booking_date'));
        $bookingTime = \Carbon\Carbon::parse($request->get('booking_time'));
        $bookingType = $request->get('booking_type');
        $questionnaireResponses = $request->get('questionnaire_responses', []);
        
        $booking = $this->bookingService->createBooking(
            $user,
            $center,
            $service,
            $bookingDate,
            $bookingTime,
            $bookingType,
            $questionnaireResponses
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully.',
            'data' => [
                'booking' => BookingResource::make($booking),
            ],
        ], 201);
    }
    
    /**
     * Display the specified booking.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }
        
        // Check if user owns the booking
        if ($request->user()->id !== $booking->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'booking' => BookingResource::make($booking),
            ],
        ]);
    }
    
    /**
     * Update the specified booking in storage.
     */
    public function update(UpdateBookingRequest $request, $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }
        
        // Check if user owns the booking
        if ($request->user()->id !== $booking->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        $booking = $this->bookingService->updateBooking($booking, $request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully.',
            'data' => [
                'booking' => BookingResource::make($booking),
            ],
        ]);
    }
    
    /**
     * Cancel the specified booking.
     */
    public function cancel(CancelBookingRequest $request, $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingById($id);
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.',
            ], 404);
        }
        
        // Check if user owns the booking
        if ($request->user()->id !== $booking->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        $booking = $this->bookingService->cancelBooking(
            $booking,
            $request->get('cancellation_reason')
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
            'data' => [
                'booking' => BookingResource::make($booking),
            ],
        ]);
    }
    
    /**
     * Get available time slots for a center.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'center_id' => ['required', 'exists:centers,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);
        
        $centerId = $request->get('center_id');
        $date = \Carbon\Carbon::parse($request->get('date'));
        
        $slots = $this->bookingService->getAvailableSlots(
            \App\Models\Center::findOrFail($centerId),
            $date
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'center_id' => $centerId,
                'date' => $date->toDateString(),
                'slots' => $slots,
            ],
        ]);
    }
    
    /**
     * Get booking questionnaire schema.
     */
    public function questionnaire(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'schema' => $this->bookingService->getQuestionnaireSchema(),
            ],
        ]);
    }
}
```
**Checklist:**
- [ ] Implement booking listing for user
- [ ] Implement booking creation
- [ ] Implement booking details retrieval
- [ ] Implement booking update
- [ ] Implement booking cancellation
- [ ] Implement available time slots
- [ ] Implement questionnaire schema
- [ ] Add proper error handling
- [ ] Add authorization checks

##### File: `app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php`
```php
<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendlyWebhookController extends Controller
{
    protected BookingService $bookingService;
    
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    
    /**
     * Handle Calendly webhook
     */
    public function handle(Request $request): JsonResponse
    {
        // Get webhook signature
        $signature = $request->header('Calendly-Webhook-Signature');
        $payload = $request->getContent();
        
        // Validate signature
        if (!$this->bookingService->calendlyService->validateWebhookSignature($payload, $signature)) {
            Log::warning('Invalid Calendly webhook signature', [
                'signature' => $signature,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }
        
        // Process webhook
        $payloadData = json_decode($payload, true);
        $processedData = $this->bookingService->calendlyService->processWebhook($payloadData);
        
        if (!$processedData) {
            Log::error('Failed to process Calendly webhook', [
                'payload' => $payloadData,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process webhook.',
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully.',
        ]);
    }
}
```
**Checklist:**
- [ ] Implement webhook signature validation
- [ ] Implement webhook processing
- [ ] Add proper error handling
- [ ] Add logging

**Jobs (4 files)**

##### File: `app/Jobs/Booking/SendBookingConfirmationJob.php`
```php
<?php

namespace App\Jobs\Booking;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public int
**Jobs (4 files)**

##### File: `app/Jobs/Booking/SendBookingConfirmationJob.php`
```php
<?php

namespace App\Jobs\Booking;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 60; // 1 minute
    
    protected Booking $booking;
    
    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
    
    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $notificationService->sendBookingConfirmation($this->booking);
            Log::info('Booking confirmation job completed', [
                'booking_id' => $this->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Booking confirmation job failed', [
            'booking_id' => $this->booking->id,
                'error' => $exception->getMessage(),
            ]);
    }
}
```
**Checklist:**
- [ ] Implement booking confirmation job
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for success and failure

##### File: `app/Jobs/Booking/SendBookingReminderJob.php`
```php
<?php

namespace App\Jobs\Booking;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 300; // 5 minutes
    
    protected Booking $booking;
    
    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
    
    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $notificationService->sendBookingReminder($this->booking);
            Log::info('Booking reminder job completed', [
                'booking_id' => $this->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking reminder', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Booking reminder job failed', [
            'booking_id' => $this->booking->id,
                'error' => $exception->getMessage(),
            ]);
    }
}
```
**Checklist:**
- [ ] Implement booking reminder job
- [ ] Add timeout configuration
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for success and failure

##### File: `app/Jobs/Booking/SyncCalendlyEventJob.php`
```php
<?php

namespace App\Jobs\Booking;

use App\Models\Booking;
use App\Services\Integration\CalendlyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCalendlyEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $retryAfter = 60; // 1 minute
    
    protected Booking $booking;
    protected array $eventData;
    
    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking, array $eventData)
    {
        $this->booking = $booking;
        $this->eventData = $eventData;
    }
    
    /**
     * Execute the job.
     */
    public function handle(CalendlyService $calendlyService): void
    {
        try {
            $calendlyService->syncBookingWithEvent($this->booking, $this->eventData);
            Log::info('Calendly event sync job completed', [
                'booking_id' => $this->booking->id,
                'calendly_event_id' => $this->eventData['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync Calendly event', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
                'event_data' => $this->eventData,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Calendly event sync job failed', [
            'booking_id' => $this->booking->id,
                'error' => $exception->getMessage(),
                'event_data' => $this->eventData,
            ]);
    }
}
```
**Checklist:**
- [ ] Implement Calendly event sync job
- [ ] Add timeout configuration
- [ ] Add retry configuration
- [ ] Add failure handling
- [ ] Add logging for success and failure

##### File: `app/Jobs/Booking/ProcessBookingRemindersBatchJob.php`
```php
<?php

namespace App\Jobs\Booking;

use App\Services\Booking\BookingService;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessBookingRemindersBatchJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, Queueable, SerializesModels;
    
    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes
    
    /**
     * The number of seconds to wait before the job is available to process.
     */
    public int $delay = 0;
    
    /**
     * The number of seconds to wait before a retry (backoff).
     */
    public int $retryAfter = 300; // 5 minutes
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;
    
    /**
     * Execute the job.
     */
    public function handle(BookingService $bookingService, NotificationService $notificationService): void
    {
        try {
            // Get bookings that need reminders (24h before)
            $bookings = $bookingService->bookingRepository->getBookingsNeedingReminders();
            
            foreach ($bookings as $booking) {
                // Dispatch individual reminder jobs
                SendBookingReminderJob::dispatch($booking);
            }
            
            Log::info('Booking reminders batch job completed', [
                'bookings_count' => $bookings->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process booking reminders batch', [
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'booking-reminders-batch-' . date('Y-m-d-H:i');
    }
}
```
**Checklist:**
- [ ] Implement batch reminder processing job
- [ ] Add timeout configuration
- [ ] Add retry configuration
- [ ] Add unique job ID
- [ ] Add logging for success and failure

**Events (3 files)**

##### File: `app/Events/BookingCreated.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated extends Dispatchable implements InteractsWithSockets
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('booking.' . $this->booking->id);
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.created';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'status' => $this->booking->status,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingCreated event
- [ ] Implement broadcasting
- [ ] Add serialization

##### File: `app/Events/BookingCancelled.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled extends Dispatchable implements InteractsWithSockets
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('booking.' . $this->booking->id);
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.cancelled';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'status' => $this->booking->status,
            'cancellation_reason' => $this->booking->cancellation_reason,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingCancelled event
- [ ] Implement broadcasting
- [ ] Add serialization

##### File: `app/Events/BookingConfirmed.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Dispatchable implements InteractsWithSockets
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('booking.' . $this->booking->id);
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.confirmed';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'status' => $this->booking->status,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingConfirmed event
- [ ] Implement broadcasting
- [ ] Add serialization

**Listeners (3 files)**

##### File: `app/Listeners/Booking/BookingCreatedListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingCreated;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BookingCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        try {
            // Send confirmation notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingConfirmation($event->booking);
            
            // Schedule reminder (24h before booking)
            $bookingDateTime = $event->booking->booking_date->setTimeFromTimeString($event->booking_time->toTimeString());
            $reminderTime = $bookingDateTime->subDay()->subHour();
            
            if ($reminderTime->isFuture()) {
                SendBookingReminderJob::dispatch($event->booking)->delay($reminderTime);
            }
            
            Log::info('Booking created listener executed', [
                'booking_id' => $event->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BookingCreatedListener', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingCreated event
- [ ] Send confirmation notification
- [ ] Schedule reminder job
- [ ] Add error handling and logging

##### File: `app/Listeners/Booking/BookingCancelledListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingCancelled;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BookingCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event): void
    {
        try {
            // Send cancellation notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingCancellation($event->booking);
            
            Log::info('Booking cancelled listener executed', [
                'booking_id' => $event->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BookingCancelledListener', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingCancelled event
- [ ] Send cancellation notification
- [ ] Add error handling and logging

##### File: `app/Listeners/Booking/BookingConfirmedListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingConfirmed;
use Illuminate\Support\Facades\Log;

class BookingConfirmedListener
{
    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        try {
            // Update center occupancy if applicable
            $booking = $event->booking;
            $center = $booking->center;
            
            if ($booking->status === 'confirmed' && !$booking->isPast()) {
                $center->increment('current_occupancy');
            }
            
            Log::info('Booking confirmed listener executed', [
                'booking_id' => $event->booking->id,
                'center_id' => $center->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in BookingConfirmedListener', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingConfirmed event
- [ ] Update center occupancy
- [ ] Add error handling and logging

**Mail Templates (3 files)**

##### File: `app/Mail/BookingConfirmationMail.php`
```php
<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return $this->subject(
            'Booking Confirmation - ' . $this->booking->center->name
        )->view('emails.booking.confirmation');
    }
    
    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
    
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.booking.confirmation', [
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'center' => $this->booking->center,
            'service' => $this->booking->service,
        ]);
    }
}
```
**Checklist:**
- [ ] Define booking confirmation email
- [ ] Set subject and view
- [ ] Pass booking data to view
- [ ] Add attachments support

##### File: `app/Mail/BookingReminderMail.php`
```php
<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return $this->subject(
            'Reminder: Your visit to ' . $this->booking->center->name . ' tomorrow'
        )->view('emails.booking.reminder');
    }
    
    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
    
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.booking.reminder', [
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'center' => $this->booking->center,
            'service' => $this->booking->service,
        ]);
    }
}
```
**Checklist:**
- [ ] Define booking reminder email
- [ ] Set subject and view
- [ ] Pass booking data to view
- [ ] Add attachments support

##### File: `app/Mail/BookingCancellationMail.php`
```php
<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancellationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return $this->subject(
            'Booking Cancelled - ' . $this->booking->center->name
        )->view('emails.booking.cancellation');
    }
    
    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }
    
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->view('emails.booking.cancellation', [
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'center' => $this->booking->center,
            'service' => $this->booking->service,
            'cancellation_reason' => $this->booking->cancellation_reason,
        ]);
    }
}
```
**Checklist:**
- [ ] Define booking cancellation email
- [ ] Set subject and view
- [ ] Pass booking data to view
- [ ] Add attachments support

**Routes (1 file)**

##### File: `routes/api.php` (BOOKING section)
```php
<?php

use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\Webhooks\CalendlyWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes - Bookings
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Bookings
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::put('bookings/{id}', [BookingController::class, 'update']);
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::get('bookings/available-slots', [BookingController::class, 'availableSlots']);
    Route::get('bookings/questionnaire', [BookingController::class, 'questionnaire']);
});

// Webhooks
Route::post('webhooks/calendly', [CalendlyWebhookController::class, 'handle']);
```
**Checklist:**
- [ ] Define protected booking routes
- [ ] Define webhook route
- [ ] Add proper route grouping

**Tests (3 files minimum)**

##### File: `tests/Feature/Booking/BookingWorkflowTest.php`
```php
<?php

namespace Tests\Feature\Booking;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
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
     * Test user can create booking
     */
    public function test_user_can_create_booking(): void
    {
        $token = $this->user->createToken('test-token');
        
        $bookingData = [
            'center_id' => $this->center->id,
            'service_id' => $this->service->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'booking_type' => 'visit',
            'questionnaire_responses' => [
                'elderly_age' => 75,
                'medical_conditions' => ['diabetes'],
                'mobility' => 'walker',
                'special_requirements' => 'Needs wheelchair access',
            ],
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/v1/bookings', $bookingData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'booking' => [
                        'id',
                        'booking_number',
                        'user_id',
                        'center_id',
                        'service_id',
                        'booking_date',
                        'booking_time',
                        'booking_type',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
        
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'center_id' => $this->center->id,
            'service_id' => $this->service->id,
            'status' => 'pending',
        ]);
    }
    
    /**
     * Test user can view their bookings
     */
    public function test_user_can_view_their_bookings(): void
    {
        // Create some bookings
        Booking::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        $token = $this->user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->getJson('/api/v1/bookings');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'bookings' => [
                        '*' => [
                            'id',
                            'booking_number',
                            'status',
                            'created_at',
                        ],
                    ],
                ],
            ]);
        
        $this->assertEquals(3, count($response->json('data.bookings')));
    }
    
    /**
     * Test user can cancel booking
     */
    public function test_user_can_cancel_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'center_id' => $this->center->id,
            'status' => 'confirmed',
        ]);
        
        $token = $this->user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson("/api/v1/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Change of plans',
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
            ]);
        
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Change of plans',
        ]);
    }
    
    /**
     * Test user cannot cancel completed booking
     */
    public function test_user_cannot_cancel_completed_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'center_id' => $this->center->id,
            'status' => 'completed',
            'booking_date' => now()->subDays(1), // Past booking
        ]);
        
        $token = $this->user->createToken('test-token');
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson("/api/v1/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Change of plans',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
            ]);
    }
    
    /**
     * Test booking validation works
     */
    public function test_booking_validation_works(): void
    {
        $token = $this->user->createToken('test-token');
        
        // Test invalid center_id
        $response = $this->withHeaders([
            'Authorization' => 'BookingConfirmationMail',
        ])->postJson('/api/v1/bookings', [
            'center_id' => 999, // Invalid center_id
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'booking_type' => 'visit',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'center_id',
            ]);
        
        // Test past date
        $response = $this->withHeaders([
            'Authorization' => 'BookingConfirmationMail',
        ])->postJson('/api/v1/bookings', [
            'center_id' => $this->center->id,
            'booking_date' => now()->subDays(1)->format('Y-m-d'), // Past date
            'booking_time' => '10:00',
            'booking_type' => 'visit',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'booking_date',
            ]);
        
        // Test invalid time format
        $response = $this->withHeaders([
            'Authorization' => 'BookingConfirmationMail',
        ])->postJson('/api/v1/bookings', [
            'center_id' => $this->center->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '25:00', // Outside business hours
            'booking_type' => 'visit',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'booking_time',
            ]);
        
        // Test missing questionnaire data
        $response = $this->withHeaders([
            'Authorization' => 'BookingConfirmationMail',
        ])->postJson('/api/v1/bookings', [
            'center_id' => $this->center->id,
            'booking_date' => now()->addDay()->format('Y-m-d'),
            'booking_time' => '10:00',
            'booking_type' => 'visit',
            'questionnaire_responses' => [
                'elderly_age' => 50, // Below minimum age
            ],
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'questionnaire_responses.elderly_age',
            ]);
    }
    
    /**
     * Test available time slots endpoint
     */
    public function test_available_time_slots_endpoint(): void
    {
        $response = Http::get('/api/v1/bookings/available-slots?' . http_build_query([
            'center_id' => $this->center->id,
            'date' => now()->addDay()->format('Y-m-d'),
        ]));
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'center_id',
                    'date',
                    'slots' => [
                        '*' => [
                            'time',
                            'available',
                        ],
                    ],
                ],
            ]);
        
        // Verify time slots are from 9am to 5pm
        $slots = $response->json('data.slots');
        $this->assertEquals('09:00', $slots[0]['time']);
        $this->assertEquals('17:00', $slots[count($slots) - 1]['time']);
        $this->assertEquals(10, count($slots)); // 9am-5pm = 10 slots
    }
}
```
**Checklist:**
- [ ] Test: user can create booking
- [ ] Test: user can view their bookings
- [ ] Test: user can cancel booking
- [ ] Test: user cannot cancel completed booking
- [ ] Test: booking validation works
- [ ] Test: available time slots endpoint

##### File: `tests/Feature/Booking/CalendlyWebhookTest.php`
```php
<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CalendlyWebhookTest extends TestCase
{
    use RefreshDatabase;
    
    protected Booking $booking;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->booking = Booking::factory()->create([
            'calendly_event_id' => 'test-event-id',
            'status' => 'pending',
        ]);
    }
    
    /**
     * Test webhook signature validation
     */
    public function test_webhook_signature_validation(): void
    {
        // Create invalid signature
        $payload = json_encode([
            'event' => 'invitee.created',
            'payload' => [
                'email' => 'test@example.com',
                'event' => [
                    'id' => 'test-event-id',
                    'uri' => 'https://calendly.com/event/test-event-id',
                ],
            ],
        ]);
        
        $signature = 'invalid-signature';
        
        $response = Http::post('/api/v1/webhooks/calendly', [
            'Calendly-Webhook-Signature' => $signature,
        ], [
                'Content-Type' => 'application/json',
            ], $payload);
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid webhook signature.',
            ]);
    }
    
    /**
     * Test webhook processing for invitee.created event
     */
    public function test_webhook_processing_invitee_created(): void
    {
        $payload = json_encode([
            'event' => 'invitee.created',
            'payload' => [
                'email' => $this->booking->user->email,
                'event' => [
                    'id' => $this->booking->calendly_event_id,
                    'uri' => 'https://calendly.com/event/' . $this->booking->calendly_event_id,
                ],
            ],
        ]);
        
        $signature = hash_hmac('sha256', $payload, config('services.calendly.webhook_signing_key'));
        
        $response = Http::post('/api/v1/webhooks/calendly', [
            'Calendly-Webhook-Signature' => $signature,
        ], [
                'Content-Type' => 'application/json',
            ], $payload);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Webhook processed successfully.',
            ]);
        
        // Verify booking status changed to confirmed
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'confirmed',
        ]);
    }
    
    /**
     * Test webhook processing for invitee.canceled event
     */
    public function test_webhook_processing_invitee_cancelled(): void
    {
        $payload = json_encode([
            'event' => 'invitee.canceled',
            'payload' => [
                'email' => $this->booking->user->email,
                'event' => [
                    'id' => $this->booking->calendly_event_id,
                    'uri' => 'https://calendly.com/event/' . $this->booking->calendly_event_id,
                ],
            ],
        ]);
        
        $signature = hash_hmac('sha256', $payload, config('services.calendly.webhook_signing_key'));
        
        $response = Http::post('/api/webhooks/calendly', [
            'Calendly-Webhook-Signature' => $signature,
        ], [
                'Content-Type' => 'application/json',
            ], $payload);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Webhook processed successfully.',
            ]);
        
        // Verify booking status changed to cancelled
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'cancelled',
        ]);
    }
    
    /**
     * Test webhook processing with invalid payload
     */
    public function test_webhook_processing_with_invalid_payload(): void
    {
        $payload = json_encode([
            'event' => 'invalid.event',
            'payload' => [
                'invalid' => 'data',
            ],
        ]);
        
        $signature = hash_hmac('sha256', $payload, config('services.calendly.webhook_signing_key'));
        
        $response = Http::post('/api/v1/webhooks/calendly', [
            'Calendly-Webhook-Signature' => $signature,
        ], [
                'Content-Type' => 'application/json',
            ], $payload);
        
        $response->assertStatus(400)
            ->assertJson([
                'success' => 'false',
                'message' => 'Failed to process webhook.',
            ]);
    }
}
```
**Checklist:**
- [ ] Test webhook signature validation
- [ ] Test webhook processing for invitee.created
- [ ] Test webhook processing for invitee.canceled
- [ ] Test webhook processing with invalid payload

##### File: `tests/Unit/Services/BookingServiceTest.php`
```php
<?php

namespace Tests\Unit\Services\Booking;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use App\Services\Integration\CalendlyService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

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
    public function test_create_booking(): void
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();
        $service = Service::factory()->create(['center_id' => $center->id]);
        $bookingDate = now()->addDay();
        $bookingTime = now()->setTime(10, 0);
        
        $booking = $this->bookingService->createBooking(
            $user,
            $center,
            $service,
            $bookingDate,
            $bookingTime,
            'visit',
            ['elderly_age' => 75]
        );
        
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals($center->id, $booking->center_id);
        $this->assertEquals($service->id, $booking->service_id);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals($bookingDate->toDateString(), $booking->booking_date);
        $this->assertEquals('10:00', $booking->booking_time);
        $this->assertEquals('visit', $booking->booking_type);
        $this->assertEquals(['elderly_age' => 75], $booking->questionnaire_responses);
    }
    
    /**
     * Test booking creation with Calendly integration
     */
    public function test_create_booking_with_calendly_integration(): void
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();
        $service = Service::factory()->create(['center_id' => $center->id]);
        $bookingDate = now()->addDay();
        $bookingTime = now()->setTime(10, 0);
        
        // Mock Calendly service
        $this->calendlyServiceMock
            ->shouldReceive('createEvent')
            ->once()
            ->andReturn([
                'id' => 'calendly-event-123',
                'uri' => 'https://calendly.com/events/calendly-event-123',
                'cancel_url' => 'https://calendly.com/cancel/calendly-event-123',
                'reschedule_url' => 'https://calendly.com/reschedule/calendly-123',
            ]);
        
        // Mock notification service
        $this->notificationServiceMock
            ->shouldReceive('sendBookingConfirmation')
            ->once();
        
        // Mock audit service
        $this->auditServiceMock
            ->shouldReceive('logCreated')
            ->once();
        
        $booking = $this->bookingService->createBooking(
            $user,
            $center,
            $this->service,
            $bookingDate,
            $bookingTime,
            'visit',
            ['elderly_age' => 75]
        );
        
        // Verify Calendly integration
        $this->assertEquals('calendly-event-123', $booking->calendly_event_id);
        $this->assertEquals('https://calendly.com/events/calendly-event-123', $booking->calendly_event_uri);
        $this->assertEquals('https://calendly.com/cancel/calendly-123', $booking->calendly_cancel_url);
        $this->assertEquals('https://calendly.com/reschedule/calendly-123', $booking->calendly_reschedule_url);
        
        // Verify notification and audit logging
        $this->notificationServiceMock->shouldHaveReceived('sendBookingConfirmation', [$booking]);
        $this->auditServiceMock->shouldHaveReceived('logCreated', [$booking]);
    }
    
    /**
     * Test booking cancellation
     */
    public function test_cancel_booking(): void
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'calendly_event_id' => 'calendly-event-123',
        ]);
        
        // Mock Calendly service
        $this->calendlyServiceMock
            ->shouldReceive('cancelEvent')
            ->once()
            ->andReturn(true);
        
        // Mock notification service
        $this->notificationServiceMock
            ->shouldReceive('sendBookingCancellation')
            ->once();
        
        // Mock audit service
        $this->auditServiceMock
            ->shouldReceive('logUpdated')
            ->once();
        
        $result = $this->bookingService->cancelBooking($booking, 'Change of plans');
        
        $this->assertTrue($result);
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('Change of plans', $booking->cancellation_reason);
        
        // Verify Calendly integration
        $this->calendlyServiceMock->shouldHaveReceived('cancelEvent', ['calendly-event-123']);
        $this->notificationServiceMock->shouldHaveReceived('sendBookingCancellation', [$booking]);
        $this->auditServiceMock->shouldHaveReceived('logUpdated', [$booking, ['status' => 'confirmed'], ['status' => 'cancelled', 'cancellation_reason' => 'Change of plans']]);
    }
    
    /**
     * Test getting available time slots
     */
    public function test_get_available_slots(): void
    {
        $center = Center::factory()->create();
        $date = now()->addDay();
        
        // Create some existing bookings
        Booking::factory()->create([
            'center_id' => $center->id,
            'booking_date' => $date->toDateString(),
            'booking_time' => '10:00',
            'status' => 'confirmed',
        ]);
        
        Booking::factory()->create([
            'center_id' => $center->id,
            'booking_date' => $date->toDateString(),
            'booking_time' => '11:00',
            'status' => 'confirmed',
        ]);
        
        $slots = $this->bookingService->getAvailableSlots($center, $date);
        
        // Should have 10 slots (9am-5pm)
        $this->assertCount(10, $slots);
        
        // 10:00 and 11:00 should be unavailable
        $this->assertFalse($slots[1]['available']); // 10:00
        $this->assertFalse($slots[2]['available']); // 11:00
        
        // Other slots should be available
        $this->assertTrue($slots[0]['available']); // 09:00
        $this->assertTrue($slots[3]['available']); // 12:00
    }
    
    /**
     * Test questionnaire schema
     */
    public function test_get_questionnaire_schema(): void
    {
        $schema = $this->bookingService->getQuestionnaireSchema();
        
        $this->assertArrayHasKey('elderly_age', $schema);
        $this->assertArrayHasKey('medical_conditions', $schema);
        $this->assertArrayHasKey('mobility', $schema);
        $this->assertArrayHasKey('special_requirements', $schema);
        
        $this->assertEquals('number', $schema['elderly_age']['type']);
        $this->assertEquals(60, $schema['elderly_age']['min']);
        $this->assertEquals(120, $schema['elderly_age']['max']);
        $this->assertEquals(['independent', 'walker', 'wheelchair', 'bedridden'], $schema['mobility']['options']);
    }
    
    /**
     * Test webhook processing
     */
    public function test_process_calendly_webhook(): void
    {
        $booking = Booking::factory()->create([
            'calendly_event_id' => 'calendly-event-123',
            'status' => 'pending',
        ]);
        
        // Mock webhook processing
        $this->calendlyServiceMock
            ->shouldReceive('processWebhook')
            ->once()
            ->with([
                'event' => 'invitee.created',
                'payload' => [
                    'id' => 'calendly-event-123',
                    'uri' => 'https://calendly.com/events/calendly-event-123',
                ],
            ])
            ->andReturn([
                'event' => 'invitee.created',
                'invitee_email' => 'test@example.com',
                'event_uri' => 'https://calendly.com/events/calendly-123',
                'event_id' => 'calendly-event-123',
            ]);
        
        $result = $this->bookingService->processCalendlyWebhook([
            'event' => 'invitee.created',
            'payload' => [
                'id' => 'calendly-event-123',
                'uri' => 'https://calendly.com/events/calendly-123',
            ],
        ]);
        
        $this->assertTrue($result);
        
        // Verify booking status changed
        $this->assertEquals('confirmed', $booking->fresh()->status);
    }
    
    /**
     * Test webhook processing with invalid event
     */
    public function test_process_calendly_webhook_invalid_event(): void
    {
        $booking = Booking::factory()->create([
            'calendly_event_id' => 'calendly-event-123',
            'status' => 'pending',
        ]);
        
        // Mock webhook processing
        $this->calendlyServiceMock
            ->shouldReceive('processWebhook')
            ->once()
            ->with([
                'event' => 'invalid.event',
                'payload' => [],
            ])
            ->andReturn(null);
        
        $result = $this->bookingService->processCalendlyWebhook([
            'event' => 'invalid.event',
            'payload' => [],
        ]);
        
        $this->assertFalse($result);
        
        // Verify booking status unchanged
        $this->assertEquals('pending', $booking->fresh()->status);
    }
}
```
**Checklist:**
- [ ] Test booking creation
- [ ] Test booking creation with Calendly integration
- [ ] Test booking cancellation
- [ ] Test available time slots
- [ ] Test questionnaire schema
- [ ] Test webhook processing
- [ ] Test webhook error handling

### D.4. Workstream Validation

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
   - [ ] User can create bookings
   - [ ] Calendly integration works
   - [ ] Email and SMS notifications work
   - [ ] Webhook processing works
   - [ ] Time slot validation works

4. **Integration**
   - [ ] Calendly API integration tested
   - [ ] Twilio SMS integration tested
   - [ ] Email templates tested
   - [ ] Webhook endpoints tested

5. **Documentation**
   - [ ] All methods documented with PHPDoc
   - [ ] API endpoints documented in OpenAPI
   - [   [ ] Code reviewed by peer

6. **Performance**
   - [ ] Booking creation < 2 seconds
   - [ ] Notification delivery < 5 seconds
   - [ ] Webhook processing < 1 second

7. **Security**
   - [ ] Authorization checks work correctly
   - [ ] Input validation works
   - [ ] Webhook signature validation works
   - [ ] PDPA compliance maintained

## Workstream E: Content & Community (2-3 days)

**Owner:** Backend Dev 1
**Dependencies:** Workstream A (authentication)
**Priority:** MEDIUM

### E.1. Prerequisites & Setup
Before starting this workstream, ensure:
- [ ] Workstream A is complete and all tests pass
- [ ] User authentication system is functional
- [ ] Database is migrated with all tables

### E.2. Implementation Sequence
1. Create Models (3 files)
2. Create Service Classes (4 files)
3. Create Request Validation Classes (8 files)
4. Create Resource Transformers (3 files)
5. Create Controllers (4 files)
6. Create Routes (1 file)
7. Create Tests (3 files minimum)
8. Validate workstream completion

### E.3. File Creation Matrix

**Models (3 files)**

##### File: `app/Models/Testimonial.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'center_id',
        'title',
        'content',
        'rating',
        'status',
        'moderation_notes',
        'moderated_by',
        'moderated_at',
        'deleted_at',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the user who wrote the testimonial.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the center being reviewed.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
    
    /**
     * Get the moderator who approved/rejected the testimonial.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
    
    /**
     * Get the audit logs for the testimonial.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include approved testimonials.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only include pending testimonials.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include testimonials for a specific center.
     */
    public function scopeForCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }
    
    /**
     * Scope a query to only include testimonials with a specific rating.
     */
    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
    
    /**
     * Get the display name with rating.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->title} - {$this->rating}/5";
    }
    
    /**
     * Check if the testimonial is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if the testimonial is pending moderation.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if the testimonial is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'spam' => 'red',
            default => 'gray',
        };
    }
    
    /**
     * Get the rating as stars.
     */
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating);
    }
    
    /**
     * Get the status as a human-readable string.
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (user, center, moderator)
- [ ] Add scopes for status, center, rating filtering
- [ ] Add computed attributes (display name, status badge, stars)
- [ ] Add status checking methods
- [ ] Add soft deletes support

##### File: `app/Models/Subscription.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Subscription extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'mailchimp_subscriber_id',
        'mailchimp_status' => 'pending',
        'preferences' => 'json',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'preferences' => 'array',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
    
    /**
     * Get the user associated with the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include subscribed subscribers.
     */
    public function scopeSubscribed($query)
    {
        return $query->where('mailchimp_status', 'subscribed');
    }
    
    /**
     * Scope a query to only include unsubscribed users.
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('mailchimp_status', 'unsubscribed');
    }
    
    /**
     * Get the subscription status as a human-readable string.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->mailchimp_status) {
            'subscribed' => 'Subscribed',
            'pending' => 'Pending Confirmation',
            'unsubscribed' => 'Unsubscribed',
            'cleaned' => 'Cleaned',
            default => 'Unknown',
        };
    }
    
    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->mailchimp_status === 'subscribed';
    }
    
    /**
     * Get the preferences as an array.
     */
    public function getPreferencesAttribute(): array
    {
        return $this->preferences ?? [];
    }
    
    /**
     * Set preferences attribute.
     */
    public function setPreferencesAttribute($value): void
    {
        $this->attributes['preferences'] = json_encode($value);
    }
    
    /**
     * Get the subscription status.
     */
    public function getSubscriptionStatus(): string
    {
        return $this->mailchimp_status;
    }
    
    /**
     * Check if user has consent for marketing emails.
     */
    public function hasEmailConsent(): bool
    {
        return $this->user->hasGivenConsent('marketing_email');
    }
    
    /**
     * Get the formatted preferences.
     */
    public function getFormattedPreferencesAttribute(): array
    {
        $preferences = $this->preferences;
        
        return [
            'topics' => $preferences['topics'] ?? [],
            'frequency' => $preferences['frequency'] ?? 'weekly',
        ];
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement user relationship
- [ ] Add scopes for status filtering
- [ ] Add computed attributes (status label, formatted preferences)
- [ ] Add consent checking method
- [ ] Add preferences handling

##### File: `app/Models/ContactSubmission.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ContactSubmission extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are spam detection
     */
    protected $fillable = [
        'user_id',
        'center_id',
        'name',
        'email',
        'spam_score',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $cast = [
        'spam_score' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'spam_score' => 'datetime',
    ];
    
    /**
     * Get the user who made the submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the center the submission is about.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
    
    /**
     * Get the audit logs for the submission.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
    
    /**
     * Scope a query to only include new submissions.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
    
    /**
     * Scope a query to only include in-progress submissions.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
    
    /**
     * Scope a query to only include resolved submissions.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
    
    /**
     * Scope a query to only include spam submissions.
     */
    public function scopeSpam($query)
    {
        return $query->where('spam_score', '>', 0.8);
    }
    
    /**
     * Get the status as a human-readable string.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new' => 'New',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'spam' => 'Spam',
            default => 'New',
        };
    }
    
    /**
     * Check if the submission is spam.
     */
    public function isSpam(): bool
    {
        return $this->spam_score > 0.8;
    }
    
    form submissions (spam detection)
    public function getSpamIndicators(): array
    {
        return [
            'high_risk_keywords' => [
                'lottery', 'winner', 'free', 'click here', 'download now',
                'congratulations', 'viagra', 'cialis', 'pharmaceutical', 'casino'
            ],
            'suspicious_patterns' => [
                'bit.ly/', 'tinyurl.com/', 'short.io/',
                't.co/', 'bit.ly/',
                'shady',
                'test@test.com',
            ],
            'email_patterns' => [
                'tempmail', '10minutemail',
                'guaranteed',
                'noreply@',
                'noreply@',
            ],
            'repeated_submissions' => [
                $this->getRecentSubmissionsCount() > 3,
            ],
        ];
    }
    
    /**
     * Get recent submissions count for spam detection.
     */
    protected function getRecentSubmissionsCount(): int
    {
        return ContactSubmission::where('email', $this->email)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
    }
    
    /**
     * Get the spam score.
     */
    public function calculateSpamScore(): float
    {
        $score = 0;
        
        // Check for high-risk keywords
        foreach ($this->getSpamIndicators()['high_risk_keywords'] as $keyword) {
            if (stripos(strtolower($this->message), $keyword) !== false) {
                $score += 0.3;
            }
        }
        
        // Check for suspicious patterns
        foreach ($this->getSpamIndicators()['suspicious_patterns'] as $pattern) {
            if (stripos($this->message, $pattern) !== false) {
                $score += 0.2;
            }
        }
        
        // Check for email patterns
        foreach ($this->getSpamIndicators()['email_patterns'] as $pattern) {
            if (str_contains($this->email, $pattern) !== false) {
                $score += 0.2;
            }
        }
        
        // Check for repeated submissions
        if ($this->getRecentSubmissionsCount() > 3) {
            $score += 0.1;
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Update spam score automatically.
     */
    public function updateSpamScore(): void
    {
        $this->update(['spam_score' => $this->calculateSpamScore()]);
    }
    
    /**
     * Mark submission as spam
     */
    public function markAsSpam(): void
    {
        $this->update([
            'status' => 'spam',
            'spam_score' => 1.0,
        ]);
    }
    
    /**
     * Mark submission as resolved
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'spam_score' => 0.0,
        ]);
    }
}
```
**Checklist:**
- [ ] Define mass assignable attributes
- [ ] Define attribute casts
- [ ] Implement relationships (user, center, audit logs)
- [ ] Add scopes for status filtering
- [ ] Add spam detection logic
- [ ] Add spam scoring calculation
- [ ] Add spam marking methods

**Service Classes (4 files)**

##### File: `app/Services/Testimonial/TestimonialService.php`
```php
<?php

namespace App\Services\Testimonial;

use App\Models\Testimonial;
use App\Repositories\Testimonial\TestimonialRepository;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class TestimonialService
{
    protected TestimonialRepository $testimonialRepository;
    protected AuditService $auditService;
    
    public function __construct(
        TestimonialRepository $testimonialRepository,
        AuditService $auditService
    ) {
        $this->testimonialRepository = $testimonialRepository;
        $this->auditService = $this->auditService;
    }
    
    /**
     * Create testimonial submission
     */
    public function createTestimonial(
        User $user,
        Center $center,
        array $testimonialData
    ): Testimonial {
        return DB::transaction(function () use ($user, $center, $testimonialData) {
            // Create testimonial with pending status
            $testimonial = $this->createTestimonial($user, $center, $testimonialData);
            
            // Log audit
            $this->auditService->logCreated(auth()->user(), $testimonial);
            
            // Trigger spam detection
            $this->detectAndHandleSpam($testimonial);
            
            return $testimonial;
        });
    }
    
    /**
     * Create testimonial with spam detection
     */
    protected function createTestimonial(
        User $user,
        Center $center,
        array $testimonialData
    ): Testimonial {
        $testimonial = Testimonial::create([
            'user_id' => $user->id,
            'center_id' => $this->getCenterId($center),
            'title' => $testimonialData['title'],
            'content' => $testimonialData['content'],
            'rating' => $testimonialData['rating'],
            'status' => 'pending',
        ]);
        
        return $testimonial;
    }
    
    /**
     * Get testimonials for a center
     */
    public function getTestimonialsByCenterId(int $centerId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->testimonialRepository->getApprovedTestimonialsByCenterId($centerId, $limit);
    }
    
    /**
     * Moderate testimonial (approve/reject)
     */
    public function moderateTestimonial(
        Testimonial $testimonial,
        string $status,
        string $moderationNotes = null,
        int $moderatedBy = null
    ): Testimonial {
        return DB::transaction(function () use ($testimonial, $status, $moderationNotes, $moderatedBy) {
            $oldValues = $testimonial->toArray();
            
            // Update testimonial
            $testimonial->update([
                'status' => $status,
                'moderation_notes' => $moderationNotes,
                'moderated_by' => $moderatedBy,
                'moderated_at' => now(),
            ]);
            
            // Log audit
            $this->auditService->logUpdated(
                auth()->user(),
                $testimonial,
                $oldValues,
                $testimonial->toArray()
            );
            
            return $testimonial;
        });
    }
    
    /**
     * Delete testimonial (soft delete)
     */
    public function deleteTestimonial(Testimonial $testimonial): bool
    {
        return DB::transaction(function () use ($testimonial) {
            // Log audit
            $this->auditService->logDeleted(auth()->user(), $testimonial);
            
            // Soft delete testimonial
            $testimonial->delete();
            
            return true;
        });
    }
    
    /**
     * Get pending testimonials for moderation
     */
    public function getPendingTestimonials(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->testimonialRepository->getPendingTestimonials();
    }
    
    /**
     * Get approved testimonials for a center
     */
    public function getApprovedTestimonialsByCenterId(int $centerId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->testimonialRepository->getApprovedTestimonialsByCenterId($centerId, $limit);
    }
    
    /**
     * Get testimonial statistics
     */
    public function getTestimonialStatistics(int $centerId): array
    {
        return [
            'total' => Testimonial::where('center_id', $centerId)->count(),
            'pending' => Testimonial::where('center_id', $centerId)->where('status', 'pending')->count(),
            'approved' => Testimonial::where('center_id', $centerId)->where('status', 'approved')->count(),
            'rejected' => Testimonial::where('center_id', $centerId)->where('status', 'rejected')->count(),
            'average_rating' => Testimonial::where('center_id', $centerId)->where('status', 'approved')->avg('rating'),
        ];
    }
    
    /**
     * Detect and handle spam
     */
    protected function detectAndHandleSpam(Testimonial $testimonial): void
    {
        $spamScore = $this->calculateSpamScore($testimonial);
        
        if ($spamScore > 0.8) {
            $testimonial->markAsSpam();
            
            Log::warning('Testimonial marked as spam', [
                'testimonial_id' => $testimonial->id,
                'spam_score' => $spamScore,
            ]);
        }
    }
    
    /**
     * Calculate spam score for testimonial
     */
    protected function calculateSpamScore(Testimonial $testimonial): float
    {
        $score = 0;
        
        // Check for spam indicators in content
        $content = strtolower($testimonial->content);
        
        // High-risk keywords
        $highRiskKeywords = [
            'lottery', 'winner', 'free', 'click here', 'download now',
            'congratulations', 'viagra', 'pharmaceutical', 'casino',
        ];
        
        foreach ($highRiskKeywords as $keyword) {
            if (str_contains($content, $keyword) !== false) {
                $score += 0.3;
            }
        }
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            'bit.ly/', 'tinyurl.com/', 'short.io/',
            't.co/', 'bit.ly/',
            'shady',
            'test@test.com',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($content, $pattern) !== false) {
                $score += 0.2;
            }
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Get center ID from center object or ID
     */
    protected function getCenterId($center): int
    {
        return is_object($center) ? $center->id : $center;
    }
}
```
**Checklist:**
- [ ] Implement testimonial creation with spam detection
- [ ] Implement testimonial moderation
- [ ] Implement testimonial deletion
- [ ] Implement testimonial retrieval
- [ ] Add spam detection logic
- [ ] Add audit logging
- [ ] Add statistics calculation

##### File: `app/Services/Newsletter/NewsletterService.php`
```php
<?php

namespace App\Services\Newsletter;

use App\Models\User;
use App\Models\Subscription;
use App\Services\Integration\MailchimpService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsletterService
{
    protected MailchimpService $mailchimpService;
    protected NotificationService $notificationService;
    
    public function __construct(
        MailchimpService $mailchimpService,
        NotificationService $notificationService
    ) {
        $this->mailchimpService = $mailchimpService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Subscribe user to newsletter
     */
    public function subscribe(string $email, array $preferences = []): Subscription
    {
        return DB::transaction(function () use ($email, $preferences) {
            // Check if already subscribed
            $existingSubscription = Subscription::where('email', $email)->first();
            
            if ($existingSubscription && $existingSubscription->isActive()) {
                return $existingSubscription;
            }
            
            // Create subscription
            $subscription = Subscription::create([
                'email' => $email,
                'mailchimp_status' => 'pending',
                'preferences' => $preferences,
                'subscribed_at' => now(),
            ]);
            
            // Queue sync job
            SyncSubscriberToMailchimpJob::dispatch($subscription);
            
            // Send confirmation email
            $this->notificationService->sendNewsletterConfirmation($subscription);
            
            Log::info('Newsletter subscription created', [
                'email' => $email,
                'preferences' => $preferences,
            ]);
            
            return $subscription;
        });
    }
    
    /**
     * Unsubscribe user from newsletter
     */
    public function unsubscribe(string $email): bool
    {
        $subscription = Subscription::where('email', $email)->first();
        
        if (!$subscription) {
            return false;
        }
        
        return DB::transaction(function () use ($subscription) {
            // Update local status
            $subscription->update([
                'mailchimp_status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            
            // Unsubscribe from Mailchimp
            $this->mailchimpService->removeSubscriber($email);
            
            // Send confirmation email
            $this->notificationService->sendNewsletterUnsubscribeConfirmation($subscription);
            
            Log::info('Newsletter unsubscribed', [
                'email' => $email,
            ]);
            
            return true;
        });
    }
    
    /**
     * Update subscription preferences
     */
    public function updatePreferences(Subscription $subscription, array $preferences): Subscription
    {
        return DB::transaction(function () use ($subscription, $preferences) {
            $oldPreferences = $subscription->preferences;
            
            $subscription->update([
                'preferences' => array_merge($oldPreferences, $preferences),
                'last_synced_at' => now(),
            ]);
            
            // Queue sync job
            SyncSubscriberToMailchimpJob::dispatch($subscription);
            
            Log::info('Newsletter preferences updated', [
                'subscription_id' => $subscription->id,
                'email' => $subscription->email,
            ]);
            
            return $subscription;
        });
    }
    
    /**
     * Get user's subscription status
     */
    public function getUserSubscription(User $user): ?Subscription
    {
        return Subscription::where('email', $user->email)->first();
    }
    
    /**
     * Sync subscription to Mailchimp
     */
    public function syncToMailchimp(Subscription $subscription): bool
    {
        try {
            $result = $this->mailchimpService->updateSubscriber(
                $subscription->email,
                [
                    'email' => $subscription->email,
                    'status' => $subscription->mailchimp_status,
                    'merge_fields' => [
                        'FNAME' => $subscription->user->name,
                        'PHONE' => $subscription->user->phone,
                    ],
                ]
            );
            
            // Update local status
            $subscription->update([
                'mailchimp_status' => $result['status'],
                'last_synced_at' => now(),
            ]);
            
            return $result['status'] === 'subscribed';
        } catch (\Exception $e) {
            Log::error('Failed to sync with Mailchimp', [
                'subscription_id' => $subscription->id,
                'email' => $subscription->email,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Get subscribers by status
     */
    public function getSubscribersByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return Subscription::where('mailchimp_status', $status)->get();
    }
    
    /**
     * Get subscription statistics
     */
    public function getSubscriptionStatistics(): array
    {
        return [
            'total' => Subscription::count(),
            'subscribed' => Subscription::subscribed()->count(),
            'pending' => Subscription::pending()->count(),
            'unsubscribed' => Subscription::unsubscribed()->count(),
            'cleaned' => Subscription::cleaned()->count(),
        ];
    }
    
    /**
     * Process batch sync with Mailchimp
     */
    public function processBatchSync(): int
    {
        $pendingSubscriptions = $this->getSubscribersByStatus('pending');
        $syncCount = 0;
        
        foreach ($pendingSubscriptions as $subscription) {
            if ($this->syncToMailchimp($subscription)) {
                $syncCount++;
            }
        }
        
        Log::info('Batch Mailchimp sync completed', [
            'synced_count' => $syncCount,
            'pending_count' => $pendingSubscriptions->count(),
        ]);
        
        return $syncCount;
    }
    
    /**
     * Clean up cleaned subscriptions
     */
    public function cleanupCleanedSubscriptions(): int
    {
        $cleanedSubscriptions = Subscription::where('mailchimp_status', 'cleaned')
            ->where('updated_at', '<', now()->subDays(30))
            ->get();
        
        foreach ($cleanedSubscriptions as $subscription) {
            $subscription->delete();
        }
        
        Log::info('Cleaned ' . count($cleanedSubscriptions) . ' subscriptions');
        
        return count($cleanedSubscriptions);
    }
}
```
**Checklist:]
- [ ] Implement newsletter subscription
- [ ] Implement subscription unsubscription
- [ ] Implement preference updates
- [ ] Implement Mailchimp synchronization
- [ ] Add batch processing
- [ ] Add cleanup for cleaned subscriptions
- [ ] Add comprehensive logging

##### File: `app/Services/Contact/ContactService.php`
```php
<?php

namespace App\Services\Contact;

use App\Models\ContactSubmission;
use App\Repositories\Contact\ContactRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactService
{
    protected ContactRepository $contactRepository;
    protected NotificationService $notificationService;
    
    public function __construct(
        ContactRepository $contactRepository,
        NotificationService $notificationService
    ) {
        $this->contactRepository = $contactSubmissionRepository;
        $emailService = $notificationService;
    }
    
    /**
     * Submit contact form submission
     */
    public function submitContactForm(array $contactData): ContactSubmission
    {
        return DB::transaction(function () use ($contactData) {
            // Create contact submission
            $submission = $this->contactRepository->create($contactData);
            
            // Log audit
            Log::info('Contact form submitted', [
                'submission_id' => $submission->id,
                'email' => $submission->email,
                'subject' => $submission->status,
            ]);
            
            // Send notification to admin
            $this->notificationService->sendContactNotification($submission);
            
            return $submission;
        });
    }
    
    /**
     * Detect spam in contact submission
     */
    public function detectSpam(ContactSubmission $submission): bool
    {
        $score = $this->calculateSpamScore($submission);
        
        if ($score > 0.8) {
            $submission->status = 'spam';
            $submission->update(['spam_score' => $score]);
            
            Log::warning('Contact submission marked as spam', [
                'submission_id' => $submission->id,
                'spam_score' => $score,
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate spam score for contact submission
     */
    protected function calculateSpamScore(ContactSubmission $submission): float
    {
        $score = 0;
        
        // Check message content
        $message = strtolower($submission->message);
        
        // High-risk keywords
        $highRiskKeywords = [
            'lottery', 'winner', 'free', 'click here', 'download now',
            'congratulations', 'viagra', 'pharmaceutical', 'casino',
        ];
        
        foreach ($highRiskKeywords as $keyword) {
            if (str_contains($message, $keyword) !== false) {
                $score += 0.3;
            }
        }
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            'bit.ly/', 'tinyurl.com/', 'short.io/',
            't.co/', 'bit.ly/',
            'shady',
            'test@test.com',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($message, $pattern) !== false) {
                $score += 0.2;
            }
        }
        
        // Check email patterns
        $emailPatterns = [
            'tempmail', '10minutemail',
            'guaranteed', 'noreply@',
            'noreply@',
        ];
        
        foreach ($emailPatterns as $pattern) {
            if (str_contains($submission->email, $pattern) !== false) {
                $score += 0.2;
            }
        }
        
        // Check repeated submissions
        $recentSubmissions = ContactSubmission::where('email', $submission->email)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        
        if ($recentSubmissions > 3) {
            $score += 0.1;
        }
        
        return min($score, 1.0);
    }
    
    /**
     * Mark submission as spam
     */
    public function markAsSpam(ContactSubmission $submission): void
    {
        $submission->update([
            'status' => 'spam',
            'spam_score' => 1.0,
        ]);
        
        Log::warning('Contact submission marked as spam', [
            'submission_id' => $submission->id,
            'email' => $submission->email,
            'spam_score' => 1.0,
        ]);
    }
    
    /**
     * Mark submission as resolved
     */
    public function markAsResolved(ContactSubmission $submission): void
    {
        $submission->update([
            'status' => 'resolved',
            'spam_score' => 0.0,
        ]);
        
        Log::info('Contact submission resolved', [
            'submission_id' => $submission->id,
            'email' => $submission->email,
        ]);
    }
    
    /**
     * Get new submissions
     */
    public function getNewSubmissions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->contactRepository->getNewSubmissions();
    }
    
    /**
     * Get in-progress submissions
     */
    public function getInProgressSubmissions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->contactRepository->getInProgressSubmissions();
    }
    
    /**
     * Get resolved submissions
     */
    public function getResolvedSubmissions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->contactRepository->getResolvedSubmissions();
    }
    
    /**
     * Get submission statistics
     */
    public function getSubmissionStatistics(): array
    {
        return [
            'total' => ContactSubmission::count(),
            'new' => ContactSubmission::new()->count(),
            'in_progress' => ContactSubmission::in_progress()->count(),
            'resolved' => ContactSubmission::resolved()->count(),
            'spam' => ContactSubmission::spam()->count(),
        ];
    }
    
    /**
     * Clean up old submissions
     */
    public function cleanupOldSubmissions(): int
    {
        $oldSubmissions = ContactSubmission::where('created_at', '<', now()->subDays(30))
            ->get();
        
        foreach ($oldSubmissions as $submission) {
            $submission->delete();
        }
        
        Log::info('Cleaned ' . count($oldSubmissions) . ' old contact submissions');
        
        return count($oldSubmissions);
    }
}
```
**Checklist:**
- [ ] Implement contact form submission
- [ ] Implement spam detection
- [ ] Implement spam handling
- [ ] Add notification sending
- [ ] Add statistics calculation
- [ ] Add cleanup functionality

**Request Validation Classes (8 files)**

##### File: `app/Http/Requests/Testimonial/StoreTestimonialRequest.php`
```php
<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestimonialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can submit testimonials
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:50', 'max:1000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'status' => ['required', 'in:pending,approved,rejected,spam'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A testimonial title is required.',
            'content.min' => 'The testimonial content must be at least 50 characters.',
            'content.max' => 'The testimonial content may not be greater than 1000 characters.',
            'rating.between' => 'The rating must be between 1 and 5.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validateUserCanSubmitTestimonial();
    }
    
    /**
     * Validate user can submit testimonial for center
     */
    public function validateUserCanSubmitTestimonial(): void
    {
        $user = $this->user();
        $centerId = $this->input('center_id');
        
        // Check if user has a booking with the center
        $hasBooking = $user->bookings()
            ->where('center_id', $centerId)
            ->exists();
        
        if (!$hasBooking) {
            $this->validator->errors()->add('center_id', 'You must have a booking with the center before submitting a testimonial.');
        }
    }
}
```
**Checklist:**
- [ ] Validate: center_id (exists), title, content, rating
- [ ] Add custom error messages
- [ ] Add user validation for testimonial submission
- [ ] Add authorization check

##### File: `app/Http/Requests/Newsletter/SubscribeRequest.php`
```php
<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:subscriptions,email'],
            'preferences' => ['nullable', 'array'],
            'preferences.topics' => ['array', 'string', 'in:updates,events,promotions'],
            'preferences.frequency' => ['string', 'in:weekly,monthly']
            'preferences.language' => ['string', 'in:en,zh,ms,ta'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already subscribed.',
            'preferences.topics.in' => 'Invalid topic selections.',
            'preferences.frequency.in' => 'Invalid frequency selection.',
            'preferences.language.in' => 'Invalid language selection.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validateUserHasConsent('marketing_email');
    }
    
    /**
     * Validate user has consent for marketing emails
     */
    protected function validateUserHasConsent(string $consentType): void
    {
        $user = $this->user();
        
        if (!$user->hasGivenConsent($consentType)) {
            $this->validator->errors()->add('consents', [
                'marketing_email' => 'You must consent to marketing emails to subscribe.',
            ]);
        }
    }
    
    /**
     * Validate email format
     */
    protected function validateEmailFormat(string $attribute): void
    {
        $email = $this->input($attribute);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->validator->errors()->add($attribute, 'Please provide a valid email address.');
        }
    }
    
    /**
     * Validate preferences structure
     */
    protected function validatePreferencesStructure(array $preferences): void
    {
        $allowedTopics = ['updates', 'events', 'promotions'];
        $allowedFrequencies = ['weekly', 'monthly'];
        $allowedLanguages = ['en', 'zh', 'ms', 'ta'];
        
        // Validate topics
        if (isset($preferences['topics'])) {
            $invalidTopics = array_diff($allowedTopics, $preferences['topics']);
            if (!empty($invalidTopics)) {
                $this->validator->errors()->add('preferences.topics', 'Invalid topic selections: ' . implode(', ', $invalidTopics));
            }
        }
        
        // Validate frequency
        if (isset($preferences['frequency']) {
            if (!in_array($preferences['frequency'], $allowedFrequencies)) {
                $this->validator->errors()->add('preferences.frequency', 'Invalid frequency selection: ' . implode(', ', $allowedFrequencies));
            }
        }
        
        // Validate language preferences
        if (isset($preferences['language'])) {
            if (!in_array($preferences['language'], $allowedLanguages)) {
                $this->validator->errors()->add('preferences.language', 'Invalid language selection: ' . implode(', $allowedLanguages));
            }
        }
    }
}
```
**Checklist:**
- [ ] Validate: email (unique), preferences (array)
- [ ] Add custom error messages
- [ ] Add user consent validation
- Add email format validation
- Add preferences structure validation

##### File: `app/Http/Requests/Contact/StoreContactRequest.php`
```php
<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^(\+65)?[689]\d{7}$/', 'phone' => '81234567'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:1000'],
            'center_id' => ['nullable', 'exists:centers,id'],
            'captcha_token' => ['required', 'string', 'max:255'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Your name is required.',
            'email.required' => 'A valid email is required.',
            'phone.regex' => 'The phone number must be a valid Singapore number.',
            'subject.required' => 'A subject is required.',
            'message.min' => 'The message must be at least 20 characters.',
            'captcha_token.required' => 'Please complete the reCAPTCHA.',
            'captcha_token.required' => 'Please complete the reCAPTCHA to continue.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validateCaptcha();
    }
    
    formRequest->validateCaptcha();
    }
    
    /**
     * Validate reCAPTCHA token
     */
    protected function validateCaptcha(): void
    {
        $token = $this->input('captcha_token');
        
        if (!$this->validateCaptchaToken($token)) {
            $this->validator->errors()->add('captcha_token', 'Invalid reCAPTCHA token.');
        }
    }
    
    /**
     * Validate reCAPTCHA token
     */
    protected function validateCaptchaToken(string $token): bool
    {
        // This would integrate with a reCAPTCHA service
        // For now, we'll accept any non-empty token
        return !empty($token);
    }
    
    /**
     * Validate phone number format
     */
    protected function validatePhoneNumber(string $attribute): void
    {
        $phone = $this->input($attribute);
        
        if (!preg_match('/^(\+65)?[689]\d{7}$/', $phone)) {
            $this->validator->errors()->add($attribute, 'The phone number must be a valid Singapore number.');
        }
    }
    
    /**
     * Validate center existence if provided
     */
    protected function validateCenterExists(): void
    {
        $centerId = $this->input('center_id');
        
        if ($centerId && !\App\Models\Center::where('id', $centerId)->exists()) {
            $this->validator->errors()->add('center_id', 'The selected center is invalid.');
        }
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validatePhoneNumber('phone');
        $this->validateCenterExists();
    }
}
```
**Checklist:**
- [ ] Validate: name, email, phone, subject, message
- [ ] Add custom error messages
- [ ] Add reCAPTCHA validation
- Add phone number validation
- Add center existence validation
- [ ] Configure validator instance

##### File: `app/Http/Requests/FAQ/StoreFAQRequest.php`
```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFAQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category.required' => 'A category is required.',
            'question.required' => 'A question is required.',
            'answer.required' => 'An answer is required.',
            'display_order.min' => 'Display order must be at least 0.',
            'status.in' => 'Invalid status selection.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validateFAQUniqueness();
    }
    
    /**
     * Validate FAQ question uniqueness
     */
    protected function validateFAQUniqueness(): void
    {
        $question = $this->input('question');
        $category = $this->input('category');
        
        // Check for duplicate FAQ within category
        $existingFAQ = \App\Models\FAQ::where('category', $category)
            ->where('question', $question)
            ->exists();
        
        if ($existingFAQ) {
            $this->validator->errors()->add('question', 'A question with this text already exists in the selected category.');
        }
    }
    
    /**
     * Configure the validator instance.
     */
    protected function validateFAQUniqueness(): void
    {
        $this->validateFAQUniqueness();
        $this->validateFAQCategory();
    }
    
    /**
     * Validate FAQ category validity
     */
    protected function validateFAQCategory(): void
    {
        $category = $this->input('category');
        
        $validCategories = ['general', 'booking', 'services', 'pricing', 'display_order', 'accessibility'];
        
        if (!in_array($category, $validCategories)) {
            $this->validator->errors()->add('category', 'Invalid category selection.');
        }
    }
}
```
**Checklist:**
- [ ] Validate: category, question, answer, status
- [ ] Add custom error messages
- [ ] Add FAQ uniqueness validation
- [ ] Add category validation
- [ ] Configure validator instance

##### File: `app/Http/Requests/FAQ/UpdateFAQRequest.php`
```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFAQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isSuperAdmin();
    }
    
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:draft,published'],
        ];
    }
    
    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'category.in' => 'Invalid category selection.',
            'question.required' => 'A question is required.',
            'answer.required' => 'An answer is required.',
            'display_order.min' => 'Display order must be at least 0.',
            'status.in' => 'Invalid status selection.',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function configure()
    {
        $this->validateFAQUniqueness();
        $this->validateFAQCategory();
    }
    
    /**
     * Validate FAQ uniqueness
     */
    protected function validateFAQUniqueness(): void
    {
        $question = $this->input('question');
        $category = $this->input('category');
        
        // Check for duplicate FAQ within category
        $existingFAQ = \App\Models\FAQ::where('category', $category)
            ->where('question', $question)
            ->exists();
        
        if ($existingFAQ) {
            $this->validator->errors()->add('question', 'A question with this text already exists in the selected category.');
        }
    }
    
    /**
     * Validate category validity
     */
    protected function validateFAQCategory(): void
    {
        $category = $this->input('category');
        
        $validCategories = ['general', 'booking', 'services', 'pricing', 'accessibility'];
        
        if (!in_array($category, $validCategories)) {
            $this->validator->errors()->add('category', 'Invalid category selection.');
        }
    }
}
```
**Checklist:**
- [ ] Same validation as StoreFAQRequest but with 'sometimes' rules
- [ ] Add custom error messages
- [ ] Add FAQ uniqueness validation
- [ ] Add category validation

**Resource Transformers (3 files)**

##### File: `app/Http/Resources/TestimonialResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Testimonial;
use App\Http\Resources\UserResource;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        'content' => $this->content,
        'rating' => $this->rating,
        'status' => $this->status,
        'status_label' => $this->status_label,
        'stars' => $this->stars,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
        'deleted_at' => $this->deleted_at,
        'user' => UserResource::make($this->whenLoaded('user')),
        'center' => CenterResource::make($this->whenLoaded('center')),
        'moderated_by' => UserResource::make($this->whenLoaded('moderated_by')),
        'moderated_at' => $this->moderated_at?->format('Y-m-d'),
    ];
    }
}
```
**Checklist:**
- [ ] Transform all testimonial fields
- [ ] Include user and center relationships
- [ ] Add computed attributes (status_label, stars)
- [ ] Add timestamps

##### File: `app/Http/Resources/SubscriptionResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonResource;
use App\Models\Subscription;
use App\Http\Resources\UserResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
        'email' => $this->email,
        'mailchimp_status' => $this->mailchimp_status,
        'status_label' => $this->status_label,
        'preferences' => $this->formatted_preferences,
        'subscribed_at' => $this->subscribed_at?->format('Y-m-d'),
        'unsubscribed_at' => $this->unsubscribed_at?->format('Y-m-d'),
        'last_synced_at' => $this->last_synced_at?->format('Y-m-d H:i:s'),
        'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
    ];
    }
}
```
**Checklist:**
- [ ] Transform all subscription fields
- [ ] Add formatted_preferences and status_label
- [ ] Add formatted timestamps

##### File: `app/Http/Resources/ContactSubmissionResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonResource;
use App\Models\ContactSubmission;
use App\Http\Resources\UserResource;

class ContactSubmissionResource extends JsonResource
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
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'spam_score' => $this->spam_score,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'ip_address' => $this->ip_address,
            'user' => UserResource::make($this->whenLoaded('user')),
            'center' => CenterResource::make($this->whenLoaded('center')),
    ];
    }
}
```
**Checklist:**
- [ ] Transform all submission fields
- [ ] Include user and center relationships
- [ ] Add status_label and spam_score attributes
- [ ] Add formatted timestamps

##### File: `app/Http/Resources/FAQResource.php`
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonResource;
use App\Models\FAQ;

class FAQResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'question' => $this->question,
            'answer' => $this->answer,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
```
**Checklist:**
- [ ] Transform all FAQ fields
- [ ] Add status_label accessor
- [ ] Add formatted timestamps

**Events (3 files)**

##### File: `app/Events/BookingCreated.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated extends Dispatchable
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return [
                'booking.' . $this->booking->id,
                'booking.created',
            ];
    }
    
    /**
     * Get the event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.created';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'status' => $this->booking->status,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingCreated event
- [ ] Implement broadcasting
- [ ] Add serialization
- [ ] Add proper event metadata

##### File: `app/Events/BookingCancelled.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled extends Dispatchable
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return [
                'booking.' . $this->booking->id,
                'booking.cancelled',
            ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.cancelled';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'status' => $this->booking->status,
            'cancellation_reason' => $this->booking->cancellation_reason,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingCancelled event
- [ ] Implement broadcasting
- [ ] Add serialization
- [ ] Add proper event metadata

##### File: `app/Events/BookingConfirmed.php`
```php
<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Dispatchable
{
    use SerializesModels;
    
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Booking $booking
    ) {
        $this->booking = $booking;
    }
    
    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return [
                'booking.' . $this->booking->id,
                'booking.confirmed',
            ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.confirmed';
    }
    
    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
            'center_id' => $this->booking->center_id,
            'service_id' => $this->booking->service_id,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'status' => $this->booking->status,
        ];
    }
}
```
**Checklist:**
- [ ] Define BookingConfirmed event
- [ ] Implement broadcasting
- [ ] Add serialization
- [ ] Add proper event metadata

**Listeners (3 files)**

##### File: `app/Listeners/Booking/BookingCreatedListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingCreated;
use App\Services\Notification\NotificationService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BookingCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        try {
            // Send confirmation notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingConfirmation($event->booking);
            
            // Schedule reminder (24h before booking)
            $bookingDateTime = \Carbon\Carbon::parse($event->booking->booking_date . ' ' . $event->booking_time);
            $reminderTime = $bookingDateTime->subDay();
            
            if ($reminderTime->isFuture()) {
                SendBookingReminderJob::dispatch($event->booking)->delay($reminderTime);
            }
            
            Log::info('Booking confirmation sent', [
                'booking_id' => $event->booking->id,
                'user_id' => $event->booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle BookingCreated event', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingCreated event
- [ ] Send confirmation notification
- [ ] Schedule reminder job
- [ ] Add error handling
- [ ] Add comprehensive logging

##### File: `app/Listeners/Booking/BookingCancelledListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingCancelled;
use App\Services\Notification\NotificationService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class BookingCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(BookingCancelled $event): void
    {
        try {
            // Send cancellation notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendBookingCancellation($event->booking);
            
            // Update center occupancy
            $center = $event->booking->center;
            if ($center->current_occupancy > 0) {
                $center->decrement('current_occupancy');
            }
            
            // Log cancellation
            Log::info('Booking cancelled', [
                'booking_id' => $event->booking->id,
                'user_id' => $event->booking->user_id,
                'center_id' => $event->booking->center_id,
                'cancellation_reason' => $event->booking->cancellation_reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle BookingCancelled event', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingCancelled event
- [ ] Send cancellation notification
- [ ] Update center occupancy
- [ ] Add comprehensive error handling
- [ ] Add comprehensive logging

##### File: `app/Listeners/Booking/BookingConfirmedListener.php`
```php
<?php

namespace App\Listeners\Booking;

use App\Events\BookingConfirmed;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class BookingConfirmedListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        try {
            // Update center occupancy
            $center = $event->booking->center;
            if ($center->current_occupancy > 0) {
                $center->decrement('current_occupancy');
            }
            
            // Log confirmation
            Log::info('Booking confirmed', [
                'booking_id' => $event->booking->id,
                'user_id' => $event->booking->user_id,
                'center_id' => $event->booking->center_id,
            ]);
            
            // Trigger reminder (24h before booking)
            $bookingDateTime = \Carbon\Carbon::parse($event->booking->booking_date . ' ' . $event->booking_time);
            $reminderTime = $bookingDateTime->subDay()->subDay()->setTime(10, 0);
            
            if ($reminderTime->isFuture()) {
                SendBookingReminderJob::dispatch($event->booking)->delay($reminderTime);
            }
            
            Log::info('Booking confirmed', [
                'booking_id' => $event->booking->id,
                'user_id' => $event->booking->user_id,
                'center_id' => $event->booking->center_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle BookingConfirmed event', [
                'error' => $e->getMessage(),
                'booking_id' => $event->booking->id,
            ]);
        }
    }
}
```
**Checklist:**
- [ ] Handle BookingConfirmed event
- [ ] Update center occupancy
- [ ] Schedule reminder jobs
- [ ] Add comprehensive error handling
- [ ] Add comprehensive logging
