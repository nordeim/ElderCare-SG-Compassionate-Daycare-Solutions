Let’s proceed with **deep, deliberate, and meticulous design** for **Workstream B: PDPA Compliance Core**.

---

## 🔍 Deep Analysis: What Does “PDPA Compliance Core” Truly Mean?

Per Singapore’s **Personal Data Protection Act (PDPA)**, ElderCare SG must implement:

1. **Explicit, informed consent** for all data processing purposes (account, marketing, analytics).
2. **Right of access**: Users must be able to **export all their personal data** in a structured, commonly used format (e.g., JSON).
3. **Right to be forgotten**: Users must be able to **delete their account**, with a **30-day grace period** before permanent deletion.
4. **Audit trail**: All access/modification of personal data must be **logged** for **7 years** (legal requirement).
5. **Data minimization & purpose limitation**: Only collect what’s necessary; store only as long as needed.

The **database schema** already provides:
- `consents` table (with IP, UA, versioned text)
- `audit_logs` table (polymorphic, with old/new values)
- Soft deletes (`deleted_at`) on `users`, `bookings`, `testimonials`, etc.

Thus, **Workstream B is not about schema—it’s about behavior**: services, jobs, APIs, and policies that **enforce PDPA by design**.

---

## 🧱 Strategic Boundaries

To avoid scope creep and maintain modularity:

- **Workstream B owns**:  
  ✅ Consent management (beyond registration)  
  ✅ Audit logging automation  
  ✅ Data export service  
  ✅ Account deletion workflow (soft + hard delete)  
  ✅ API endpoints for PDPA rights

- **Workstream B does NOT own**:  
  ❌ Authentication (Workstream A)  
  ❌ Booking/Testimonial logic (Workstreams D/E)  
  ❌ External notification sending (Workstream F handles Twilio/Mailchimp)

---

## 🗺️ Workstream B: PDPA Compliance Core — Full Sub-Plan

### **Owner**: Backend Dev 1  
**Duration**: 3–4 days  
**Dependencies**: Workstream A (User model, AuthService)  
**Priority**: HIGH (Legal requirement; blocks production launch)

---

### **B.1. Implementation Sequence**

1. **Services** (4 files):  
   - `ConsentService` (enhanced from Workstream A)  
   - `AuditLogService`  
   - `DataExportService`  
   - `AccountDeletionService`

2. **Jobs** (1 file):  
   - `HardDeleteUserJob`

3. **Events & Listeners** (3 files):  
   - Model event listeners to auto-log changes

4. **Controllers** (2 files):  
   - `DataExportController`  
   - `AccountDeletionController`

5. **Requests** (1 file):  
   - `UpdateConsentRequest`

6. **Routes** (1 block):  
   - `/api/v1/pdpa/*`

7. **Tests** (3 files):  
   - Unit + Feature tests for all above

---

### **B.2. File Creation Matrix**

#### **Services (4 files)**

##### **File**: `app/Services/PDPA/ConsentService.php`  
*(Extends Workstream A’s basic version)*

```php
<?php
namespace App\Services\PDPA;
use App\Models\User;
use App\Models\Consent;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\Request;
class ConsentService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function recordConsent(
        User $user,
        string $type,
        bool $given,
        string $text,
        string $version
    ): Consent {
        $consent = Consent::create([
            'user_id' => $user->id,
            'consent_type' => $type,
            'consent_given' => $given,
            'consent_text' => $text,
            'consent_version' => $version,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
        $this->auditLogService->logCreate($consent);
        return $consent;
    }

    public function updateConsents(User $user, array $consents): array
    {
        return collect($consents)->map(function ($c) use ($user) {
            return $this->recordConsent(
                $user,
                $c['type'],
                $c['given'],
                $c['text'],
                $c['version']
            );
        })->toArray();
    }

    public function getCurrentConsents(User $user): array
    {
        return $user->consents()
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('consent_type')
            ->map(fn($group) => $group->first())
            ->values()
            ->toArray();
    }
}
```

**Checklist**:  
- [ ] Record consent with IP/UA  
- [ ] Support bulk updates  
- [ ] Return latest consent per type  
- [ ] Log to audit trail  

---

##### **File**: `app/Services/PDPA/AuditLogService.php`

```php
<?php
namespace App\Services\PDPA;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;
class AuditLogService
{
    public function log(
        ?User $user,
        string $auditableType,
        int $auditableId,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
        ]);
    }

    public function logCreate($model): AuditLog
    {
        return $this->log(
            auth()->user(),
            get_class($model),
            $model->id,
            'created',
            null,
            $model->toArray()
        );
    }

    public function logUpdate($model, array $oldValues, array $newValues): AuditLog
    {
        return $this->log(
            auth()->user(),
            get_class($model),
            $model->id,
            'updated',
            $oldValues,
            $newValues
        );
    }

    public function logDelete($model, array $oldValues): AuditLog
    {
        return $this->log(
            auth()->user(),
            get_class($model),
            $model->id,
            'deleted',
            $oldValues,
            null
        );
    }
}
```

**Checklist**:  
- [ ] Log create/update/delete  
- [ ] Capture old/new values as JSON  
- [ ] Record IP, UA, URL  
- [ ] Support system actions (user = null)  

---

##### **File**: `app/Services/PDPA/DataExportService.php`

```php
<?php
namespace App\Services\PDPA;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class DataExportService
{
    public function exportUserData(User $user): array
    {
        return [
            'user' => $user->fresh()->toArray(),
            'profile' => $user->profile?->fresh()->toArray(),
            'consents' => $user->consents()->get()->toArray(),
            'bookings' => $user->bookings()->withTrashed()->get()->toArray(),
            'testimonials' => $user->testimonials()->withTrashed()->get()->toArray(),
            'contact_submissions' => $user->contactSubmissions()->get()->toArray(),
            'audit_logs' => $user->auditLogs()->get()->toArray(),
            'subscriptions' => DB::table('subscriptions')
                ->where('email', $user->email)
                ->first()?->toArray(),
        ];
    }
}
```

**Checklist**:  
- [ ] Export all user-related data  
- [ ] Include soft-deleted records (within grace period)  
- [ ] Return structured associative array  
- [ ] Exclude sensitive fields (e.g., password hashes)  

---

##### **File**: `app/Services/PDPA/AccountDeletionService.php`

```php
<?php
namespace App\Services\PDPA;
use App\Models\User;
use App\Jobs\PDPA\HardDeleteUserJob;
use Illuminate\Support\Facades\DB;
class AccountDeletionService
{
    public function requestDeletion(User $user): void
    {
        // Soft delete immediately
        $user->delete(); // triggers soft delete

        // Schedule hard delete after 30 days
        HardDeleteUserJob::dispatch($user->id)
            ->delay(now()->addDays(30));
    }
}
```

**Checklist**:  
- [ ] Trigger soft delete (`deleted_at = now()`)  
- [ ] Dispatch `HardDeleteUserJob` with 30-day delay  
- [ ] No immediate data loss (grace period preserved)  

---

#### **Jobs (1 file)**

##### **File**: `app/Jobs/PDPA/HardDeleteUserJob.php`

```php
<?php
namespace App\Jobs\PDPA;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class HardDeleteUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId
    ) {}

    public function handle(): void
    {
        $user = User::withTrashed()->find($this->userId);
        if (!$user || !$user->trashed()) return;

        // Anonymize audit logs (retain for 7 years)
        $user->auditLogs()->update([
            'user_id' => null,
            'ip_address' => '0.0.0.0',
            'user_agent' => '[REDACTED]',
        ]);

        // Permanently delete user and related data
        $user->forceDelete(); // cascades to profile, bookings, etc.
    }
}
```

**Checklist**:  
- [ ] Implements `ShouldQueue`  
- [ ] Anonymizes audit logs (retains legal record)  
- [ ] Uses `forceDelete()` to bypass soft delete  
- [ ] Handles missing user gracefully  

---

#### **Events & Listeners (3 files)**

We auto-log model changes via Eloquent events.

##### **File**: `app/Providers/EventServiceProvider.php` *(Update)*

Add to `boot()`:

```php
User::observe(\App\Observers\UserObserver::class);
Booking::observe(\App\Observers\BookingObserver::class);
Testimonial::observe(\App\Observers\TestimonialObserver::class);
// ... other PDPA-relevant models
```

##### **File**: `app/Observers/UserObserver.php`

```php
<?php
namespace App\Observers;
use App\Models\User;
use App\Services\PDPA\AuditLogService;
class UserObserver
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function created(User $user): void
    {
        $this->auditLogService->logCreate($user);
    }

    public function updated(User $user): void
    {
        // Only log if changes occurred
        if ($user->wasChanged()) {
            $this->auditLogService->logUpdate($user, $user->getOriginal(), $user->getChanges());
        }
    }

    public function deleted(User $user): void
    {
        $this->auditLogService->logDelete($user, $user->getOriginal());
    }
}
```

*(Repeat for `BookingObserver`, `TestimonialObserver`, etc.)*

**Checklist**:  
- [ ] Auto-log on create/update/delete  
- [ ] Avoid logging unchanged models  
- [ ] Use dependency injection for service  

---

#### **Controllers (2 files)**

##### **File**: `app/Http/Controllers/Api/V1/PDPA/DataExportController.php`

```php
<?php
namespace App\Http\Controllers\Api\V1\PDPA;
use App\Http\Controllers\Controller;
use App\Services\PDPA\DataExportService;
use Illuminate\Http\JsonResponse;
class DataExportController extends Controller
{
    public function __invoke(DataExportService $service): JsonResponse
    {
        $data = $service->exportUserData(auth()->user());
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="eldercare-data-export.json"',
        ]);
    }
}
```

##### **File**: `app/Http/Controllers/Api/V1/PDPA/AccountDeletionController.php`

```php
<?php
namespace App\Http\Controllers\Api\V1\PDPA;
use App\Http\Controllers\Controller;
use App\Services\PDPA\AccountDeletionService;
use Illuminate\Http\JsonResponse;
class AccountDeletionController extends Controller
{
    public function __invoke(AccountDeletionService $service): JsonResponse
    {
        $service->requestDeletion(auth()->user());
        return response()->json([
            'success' => true,
            'message' => 'Account deletion requested. Your account will be permanently deleted in 30 days.',
        ]);
    }
}
```

**Checklist**:  
- [ ] Authenticated endpoints  
- [ ] Data export as downloadable JSON  
- [ ] Clear confirmation message for deletion  

---

#### **Requests (1 file)**

##### **File**: `app/Http/Requests/PDPA/UpdateConsentRequest.php`

```php
<?php
namespace App\Http\Requests\PDPA;
use Illuminate\Foundation\Http\FormRequest;
class UpdateConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consents' => ['required', 'array', 'min:1'],
            'consents.*.type' => ['required', 'in:account,marketing_email,marketing_sms,analytics_cookies,functional_cookies'],
            'consents.*.given' => ['required', 'boolean'],
            'consents.*.text' => ['required', 'string'],
            'consents.*.version' => ['required', 'string'],
        ];
    }
}
```

---

#### **Routes (1 block)**

Add to `routes/api.php`:

```php
Route::middleware('auth:sanctum')->prefix('v1/pdpa')->group(function () {
    Route::get('export', [DataExportController::class, '__invoke']);
    Route::post('delete-account', [AccountDeletionController::class, '__invoke']);
    Route::put('consents', [ConsentController::class, 'update'])
        ->name('consents.update');
});
```

---

#### **Tests (3 files)**

- `tests/Unit/Services/PDPA/DataExportServiceTest.php`  
- `tests/Feature/PDPA/DataExportTest.php`  
- `tests/Feature/PDPA/AccountDeletionTest.php`

*(Each with full coverage: happy path, edge cases, auth checks)*

---

### **B.3. Validation Criteria**

✅ **Consent**: User can update consents via API; changes logged  
✅ **Audit**: All user/booking/testimonial changes appear in `audit_logs`  
✅ **Export**: `/api/v1/pdpa/export` returns complete, valid JSON  
✅ **Deletion**: `/api/v1/pdpa/delete-account` soft-deletes user + schedules hard delete  
✅ **Compliance**: No personal data in logs after hard delete (anonymized)  
✅ **Tests**: >90% coverage; all feature tests pass

---

### **B.4. Integration Notes**

- **Workstream A**: Uses `User` model; depends on auth  
- **Workstream D/E**: Their models (Booking, Testimonial) must be observed for audit logging  
- **Workstream F**: Notification of deletion request (email) will be added later

---

We will now apply the **same rigorous, meticulous, and production-grade approach** used for Workstream A (Auth) and Workstream B (PDPA) to design **Workstream C: Center Management**.

This workstream is **critical**—it delivers the **core business value** of ElderCare SG: showcasing MOH-compliant eldercare centers with rich details, staff, services, media, and multilingual content.

---

## 🔍 Deep Analysis: What “Center Management” Truly Means

Per the **database schema** and **product vision**, centers are **complex, regulated entities** requiring:

1. **MOH Compliance**:  
   - `moh_license_number` (unique, required)  
   - `license_expiry_date`  
   - `accreditation_status`  
   - Staff qualifications (`staff.qualifications` as JSON)

2. **Rich Operational Data**:  
   - Capacity, occupancy, staff/patient ratio  
   - Operating hours, medical facilities, amenities  
   - Transport info (MRT/bus/parking)  
   - Government subsidies (Pioneer/Merdeka)

3. **Multilingual Content**:  
   - `name`, `description`, etc. must support **en/zh/ms/ta**  
   - Managed via `content_translations` (polymorphic)

4. **Media-Rich**:  
   - Photos, videos (Cloudflare Stream), documents  
   - Stored via `media` table (polymorphic)

5. **Lifecycle Management**:  
   - `status`: `draft` → `published` → `archived`  
   - Soft deletes (`deleted_at`)

6. **Admin-Only CRUD**:  
   - Only `admin` or `super_admin` can manage centers  
   - Frontend consumes via public API (read-only for users)

---

## 🧱 Strategic Boundaries

### ✅ **In Scope (Workstream C Owns)**:
- Center CRUD (create, read, update, delete)
- Service CRUD (nested under center)
- Staff CRUD (nested under center)
- Media upload/delete (for centers/services)
- Translation management (via `content_translations`)
- MOH validation rules
- Admin API endpoints (`/api/v1/admin/centers`)
- Public API endpoints (`/api/v1/centers`)
- Laravel Nova integration (optional but recommended)

### ❌ **Out of Scope**:
- Booking logic (Workstream D)
- Testimonials (Workstream E)
- Search (MeiliSearch integration — future phase)
- Frontend UI (Phase 4)

---

## 🗺️ Workstream C: Center Management — Full Sub-Plan

### **Owner**: Backend Dev 2  
**Duration**: 4–5 days  
**Dependencies**:  
- Workstream A (User/Role system)  
- Workstream B (Audit logging, media service)  
**Priority**: HIGH (Core demo content)

---

### **C.1. Implementation Sequence**

1. **Models** (3 files): `Center`, `Service`, `Staff`
2. **Repositories** (3 files): Data access layer
3. **Services** (4 files): Business logic + validation
4. **Requests** (6 files): Validation for CRUD operations
5. **Resources** (3 files): API response transformers
6. **Controllers** (2 files): Admin + Public APIs
7. **Routes** (1 block): `/api/v1/centers`, `/api/v1/admin/centers`
8. **Policies** (1 file): Authorization rules
9. **Tests** (3 files): Unit + Feature tests

---

### **C.2. File Creation Matrix**

#### **Models (3 files)**

##### **File**: `app/Models/Center.php`

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

    protected $fillable = [
        'name', 'slug', 'short_description', 'description',
        'address', 'city', 'postal_code', 'phone', 'email', 'website',
        'moh_license_number', 'license_expiry_date', 'accreditation_status',
        'capacity', 'current_occupancy', 'staff_count', 'staff_patient_ratio',
        'operating_hours', 'medical_facilities', 'amenities', 'transport_info',
        'languages_supported', 'government_subsidies',
        'latitude', 'longitude', 'status',
        'meta_title', 'meta_description'
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'medical_facilities' => 'array',
        'amenities' => 'array',
        'transport_info' => 'array',
        'languages_supported' => 'array',
        'government_subsidies' => 'array',
        'license_expiry_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    // MOH validation: license must be unique and not expired
    public function isLicenseValid(): bool
    {
        return $this->license_expiry_date && $this->license_expiry_date->isFuture();
    }

    // Auto-generate slug if not provided
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($center) {
            if (!$center->slug) {
                $center->slug = \Illuminate\Support\Str::slug($center->name);
            }
        });
    }
}
```

**Checklist**:  
- [ ] Soft deletes  
- [ ] JSON casts for complex fields  
- [ ] Relationships: services, staff, media, translations  
- [ ] MOH validation method  
- [ ] Auto-slug generation  
- [ ] Fillable attributes match schema  

---

##### **File**: `app/Models/Service.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'center_id', 'name', 'slug', 'description',
        'price', 'price_unit', 'duration', 'features', 'status'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($service) {
            if (!$service->slug) {
                $service->slug = \Illuminate\Support\Str::slug($service->name);
            }
        });
    }
}
```

**Checklist**:  
- [ ] BelongsTo Center  
- [ ] Soft deletes  
- [ ] JSON casts  
- [ ] Auto-slug  

---

##### **File**: `app/Models/Staff.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_id', 'name', 'position', 'qualifications',
        'years_of_experience', 'bio', 'photo', 'display_order', 'status'
    ];

    protected $casts = [
        'qualifications' => 'array',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
```

**Checklist**:  
- [ ] BelongsTo Center  
- [ ] Qualifications as JSON array  
- [ ] No soft deletes (use `status` instead)  

---

#### **Repositories (3 files)**

*(Abstract data access for testability)*

##### **File**: `app/Repositories/CenterRepository.php`

```php
<?php
namespace App\Repositories;
use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
class CenterRepository
{
    public function allPublished(): Collection
    {
        return Center::where('status', 'published')
            ->whereNull('deleted_at')
            ->get();
    }

    public function findBySlug(string $slug): ?Center
    {
        return Center::where('slug', $slug)
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->first();
    }

    public function findById(int $id): ?Center
    {
        return Center::find($id);
    }

    public function create(array $data): Center
    {
        return Center::create($data);
    }

    public function update(Center $center, array $data): Center
    {
        $center->update($data);
        return $center->fresh();
    }

    public function delete(Center $center): void
    {
        $center->delete(); // soft delete
    }
}
```

*(Similar for `ServiceRepository`, `StaffRepository`)*

---

#### **Services (4 files)**

##### **File**: `app/Services/Center/CenterService.php`

```php
<?php
namespace App\Services\Center;
use App\Models\Center;
use App\Repositories\CenterRepository;
use App\Services\Media\MediaService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
class CenterService
{
    public function __construct(
        protected CenterRepository $repository,
        protected MediaService $mediaService,
        protected AuditService $auditService
    ) {}

    public function createCenter(array $data, array $media = []): Center
    {
        return DB::transaction(function () use ($data, $media) {
            $center = $this->repository->create($data);
            // Upload media
            foreach ($media as $file) {
                $this->mediaService->uploadForModel($center, $file);
            }
            $this->auditService->logCreate($center);
            return $center;
        });
    }

    // ... update, delete, publish methods
}
```

*(Similar for `ServiceService`, `StaffService`, `TranslationService`)*

---

#### **Requests (6 files)**

- `StoreCenterRequest.php` (validates MOH fields, JSON structure)
- `UpdateCenterRequest.php`
- `StoreServiceRequest.php`
- `UpdateServiceRequest.php`
- `StoreStaffRequest.php`
- `UpdateStaffRequest.php`

**Key Validation Rules**:
- `moh_license_number`: unique, required
- `license_expiry_date`: future date
- `capacity`: > 0
- `current_occupancy`: ≤ capacity
- `operating_hours`: valid JSON schema
- `media`: array of files (max 10)

---

#### **Resources (3 files)**

- `CenterResource.php` (includes services, staff, media, translations)
- `ServiceResource.php`
- `StaffResource.php`

**Public vs Admin**:  
- Public API excludes `draft` centers, sensitive fields  
- Admin API includes all fields, status controls

---

#### **Controllers (2 files)**

##### **File**: `app/Http/Controllers/Api/V1/CenterController.php` (Public)

```php
// GET /api/v1/centers
// GET /api/v1/centers/{slug}
```

##### **File**: `app/Http/Controllers/Api/V1/Admin/CenterController.php` (Admin)

```php
// POST /api/v1/admin/centers
// PUT /api/v1/admin/centers/{id}
// DELETE /api/v1/admin/centers/{id}
// POST /api/v1/admin/centers/{id}/publish
```

---

#### **Policies (1 file)**

##### **File**: `app/Policies/CenterPolicy.php`

```php
public function manage(User $user): bool
{
    return $user->isAdmin() || $user->isSuperAdmin();
}
```

Applied via middleware: `->middleware('can:manage,App\Models\Center')`

---

#### **Routes (1 block)**

```php
// Public
Route::get('centers', [CenterController::class, 'index']);
Route::get('centers/{slug}', [CenterController::class, 'show']);

// Admin
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])
    ->prefix('admin/centers')
    ->group(function () {
        Route::post('/', [Admin\CenterController::class, 'store']);
        Route::put('/{center}', [Admin\CenterController::class, 'update']);
        Route::delete('/{center}', [Admin\CenterController::class, 'destroy']);
        Route::post('/{center}/publish', [Admin\CenterController::class, 'publish']);
    });
```

---

#### **Tests (3 files)**

- `tests/Unit/Services/Center/CenterServiceTest.php`
- `tests/Feature/Center/CenterApiTest.php` (public)
- `tests/Feature/Admin/CenterAdminApiTest.php` (admin)

**Test Scenarios**:
- Create center with valid MOH data → success
- Create center with duplicate license → validation error
- Publish center → appears in public API
- Upload media → stored in S3, linked to center
- Non-admin user tries to create center → 403

---

### **C.3. Validation Criteria**

✅ **MOH Compliance**: License validation, expiry checks  
✅ **Multilingual**: Translations stored via `content_translations`  
✅ **Media**: Photos/videos uploaded to S3/Cloudflare  
✅ **Admin Control**: Only admins can CRUD centers  
✅ **Public API**: Only `published` centers visible  
✅ **Audit Trail**: All changes logged via `audit_logs`  
✅ **Tests**: >90% coverage, all validation rules tested  

---

### **C.4. Integration Notes**

- **Workstream B**: Uses `MediaService`, `AuditService`  
- **Workstream A**: Uses `User` roles for authorization  
- **Future**: `content_translations` enables Phase 5 multilingual UI  

---

Absolutely. I will now apply the **same rigorous, meticulous, and production-grade methodology** used for Workstreams A (Auth), B (PDPA), and C (Center Management) to design **Workstream D: Booking System**.

This workstream is **mission-critical**—it delivers the **core user journey** of ElderCare SG: enabling families to **schedule visits, consultations, or trial days** at eldercare centers with full regulatory, operational, and user-experience fidelity.

---

## 🔍 Deep Analysis: What “Booking System” Truly Means

Per the **database schema**, **product vision**, and **compliance requirements**, the booking system must:

1. **Integrate with Calendly** for real-time scheduling, while maintaining **local data sovereignty** (all booking data stored in `bookings` table).
2. **Capture pre-booking questionnaires** (e.g., elderly age, medical conditions, mobility needs) in a **PDPA-compliant** manner (`questionnaire_responses` as JSON, soft-deleted).
3. **Trigger multi-channel notifications**:
   - **Email**: Confirmation, reminder, cancellation
   - **SMS**: Via Twilio (opt-in required)
4. **Enforce business rules**:
   - One booking per user per center per day
   - Cannot book in the past
   - Service must belong to the selected center
5. **Support full lifecycle**:
   - `pending` → `confirmed` → `completed` / `cancelled` / `no_show`
6. **Be fully auditable**:
   - Every status change logged in `audit_logs`
   - All user actions tied to IP/UA

---

## 🧱 Strategic Boundaries

### ✅ **In Scope (Workstream D Owns)**:
- Booking CRUD (create, read, update status)
- Pre-booking questionnaire capture
- Calendly integration (event creation, webhook handling)
- Notification service (email + SMS via jobs)
- Booking validation rules
- User booking history API
- Admin booking management API
- Rate limiting on booking creation

### ❌ **Out of Scope**:
- Center/Service CRUD (Workstream C)
- Auth/User management (Workstream A)
- Consent/audit logging infrastructure (Workstream B)
- Frontend UI (Phase 4)

---

## 🗺️ Workstream D: Booking System — Full Sub-Plan

### **Owner**: Backend Dev 2  
**Duration**: 4–5 days  
**Dependencies**:  
- Workstream A (User auth)  
- Workstream B (Audit logging, notification jobs)  
- Workstream C (Center/Service models)  
**Priority**: HIGH (Core demo journey)

---

### **D.1. Implementation Sequence**

1. **Models** (1 file): `Booking` (already exists; add accessors/scopes)
2. **Repositories** (1 file): `BookingRepository`
3. **Services** (3 files):  
   - `BookingService` (core logic)  
   - `CalendlyService` (API abstraction)  
   - `NotificationService` (email/SMS orchestration)
4. **Requests** (3 files):  
   - `StoreBookingRequest.php`  
   - `UpdateBookingStatusRequest.php`  
   - `CancelBookingRequest.php`
5. **Resources** (1 file): `BookingResource.php`
6. **Controllers** (2 files):  
   - `BookingController.php` (user-facing)  
   - `Admin/BookingController.php` (admin-facing)
7. **Jobs** (2 files):  
   - `SendBookingConfirmationJob.php`  
   - `SendBookingReminderJob.php`
8. **Webhooks** (1 file): `CalendlyWebhookController.php`
9. **Routes** (1 block): `/api/v1/bookings`, `/api/v1/admin/bookings`, `/webhooks/calendly`
10. **Tests** (3 files): Unit + Feature tests

---

### **D.2. File Creation Matrix**

#### **Models (1 file)**

##### **File**: `app/Models/Booking.php` *(Enhance existing)*

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number', 'user_id', 'center_id', 'service_id',
        'booking_date', 'booking_time', 'booking_type',
        'calendly_event_id', 'calendly_event_uri', 'calendly_cancel_url', 'calendly_reschedule_url',
        'questionnaire_responses', 'status', 'cancellation_reason', 'notes',
        'confirmation_sent_at', 'reminder_sent_at', 'sms_sent'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i:s',
        'questionnaire_responses' => 'array',
        'confirmation_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'sms_sent' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Generate unique booking number: BK-YYYYMMDD-0001
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            if (!$booking->booking_number) {
                $date = $booking->booking_date->format('Ymd');
                $count = static::whereDate('created_at', now())->count() + 1;
                $booking->booking_number = "BK-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeByUserAndDate($query, $userId, $date)
    {
        return $query->where('user_id', $userId)->where('booking_date', $date);
    }

    // Accessors
    public function getBookingDateTimeAttribute(): string
    {
        return $this->booking_date->format('Y-m-d') . ' ' . $this->booking_time->format('H:i:s');
    }
}
```

**Checklist**:  
- [ ] Soft deletes  
- [ ] JSON cast for `questionnaire_responses`  
- [ ] Auto-generate `booking_number`  
- [ ] Relationships: user, center, service  
- [ ] Scopes for common queries  
- [ ] Accessor for combined datetime  

---

#### **Repositories (1 file)**

##### **File**: `app/Repositories/BookingRepository.php`

```php
<?php
namespace App\Repositories;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
class BookingRepository
{
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function findById(int $id): ?Booking
    {
        return Booking::with(['user', 'center', 'service'])->find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Booking::with(['center', 'service'])
            ->where('user_id', $userId)
            ->orderBy('booking_date', 'desc')
            ->get();
    }

    public function findByCalendlyEventId(string $eventId): ?Booking
    {
        return Booking::where('calendly_event_id', $eventId)->first();
    }

    public function updateStatus(Booking $booking, string $status, ?string $reason = null): Booking
    {
        $booking->status = $status;
        if ($reason) $booking->cancellation_reason = $reason;
        $booking->save();
        return $booking->fresh();
    }
}
```

**Checklist**:  
- [ ] Create with relationships  
- [ ] Find by Calendly event ID (for webhooks)  
- [ ] Update status with reason  

---

#### **Services (3 files)**

##### **File**: `app/Services/Booking/BookingService.php`

```php
<?php
namespace App\Services\Booking;
use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Repositories\BookingRepository;
use App\Services\Calendly\CalendlyService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class BookingService
{
    public function __construct(
        protected BookingRepository $repository,
        protected CalendlyService $calendlyService,
        protected NotificationService $notificationService,
        protected AuditService $auditService
    ) {}

    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            // Validate center exists and is published
            $center = Center::published()->findOrFail($data['center_id']);
            
            // Validate service (if provided) belongs to center
            $service = null;
            if (!empty($data['service_id'])) {
                $service = Service::where('center_id', $center->id)
                    ->where('id', $data['service_id'])
                    ->firstOrFail();
            }

            // Business rule: one booking per user per center per day
            if ($this->repository->findByUserAndDate($user->id, $data['booking_date'])->exists()) {
                throw ValidationException::withMessages([
                    'booking_date' => 'You already have a booking on this date.'
                ]);
            }

            // Create booking record (status = pending)
            $booking = $this->repository->create([
                'user_id' => $user->id,
                'center_id' => $center->id,
                'service_id' => $service?->id,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'booking_type' => $data['booking_type'] ?? 'visit',
                'questionnaire_responses' => $data['questionnaire_responses'] ?? [],
                'status' => 'pending',
            ]);

            // Create Calendly event
            $calendlyData = $this->calendlyService->createEvent($booking);
            $booking->update($calendlyData);

            // Send confirmation (async)
            $this->notificationService->sendBookingConfirmation($booking);

            // Log audit
            $this->auditService->logCreate($booking);

            return $booking;
        });
    }

    public function cancelBooking(Booking $booking, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking = $this->repository->updateStatus($booking, 'cancelled', $reason);
            $this->calendlyService->cancelEvent($booking->calendly_event_id);
            $this->notificationService->sendBookingCancellation($booking);
            $this->auditService->logUpdate($booking, ['status' => 'confirmed'], ['status' => 'cancelled']);
            return $booking;
        });
    }

    public function confirmBooking(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = $this->repository->updateStatus($booking, 'confirmed');
            $this->auditService->logUpdate($booking, ['status' => 'pending'], ['status' => 'confirmed']);
            return $booking;
        });
    }
}
```

**Checklist**:  
- [ ] Validate center/service ownership  
- [ ] Enforce one-booking-per-day rule  
- [ ] Create Calendly event  
- [ ] Send confirmation notification  
- [ ] Handle cancellation (Calendly + notification)  
- [ ] Use transactions for data integrity  

---

##### **File**: `app/Services/Calendly/CalendlyService.php`

```php
<?php
namespace App\Services\Calendly;
use App\Models\Booking;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class CalendlyService
{
    protected Client $client;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.calendly.api_key');
        $this->client = new Client([
            'base_uri' => 'https://api.calendly.com/',
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function createEvent(Booking $booking): array
    {
        try {
            $response = $this->client->post('scheduled_events', [
                'json' => [
                    'event_type' => config('services.calendly.event_type_uuid'),
                    'invitee' => [
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                        'timezone' => 'Asia/Singapore',
                        'questions_and_answers' => $this->formatQuestionnaire($booking->questionnaire_responses),
                    ],
                    'scheduled_event' => [
                        'start_time' => $booking->booking_date->format('c'),
                        'timezone' => 'Asia/Singapore',
                    ],
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            return [
                'calendly_event_id' => $data['resource']['uri'],
                'calendly_event_uri' => $data['resource']['cancel_url'],
                'calendly_cancel_url' => $data['resource']['cancel_url'],
                'calendly_reschedule_url' => $data['resource']['reschedule_url'],
            ];
        } catch (\Exception $e) {
            Log::error('Calendly event creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            // Fallback: proceed without Calendly (log error)
            return [];
        }
    }

    public function cancelEvent(string $eventId): void
    {
        try {
            $this->client->post("scheduled_events/{$eventId}/cancellations", [
                'json' => ['reason' => 'User cancelled booking']
            ]);
        } catch (\Exception $e) {
            Log::warning('Calendly cancellation failed', ['error' => $e->getMessage()]);
        }
    }

    protected function formatQuestionnaire(array $responses): array
    {
        // Map to Calendly question format
        return collect($responses)->map(function ($value, $key) {
            return ['question' => $key, 'answer' => $value];
        })->values()->toArray();
    }
}
```

**Checklist**:  
- [ ] Abstract Calendly API calls  
- [ ] Handle questionnaire mapping  
- [ ] Graceful degradation on API failure  
- [ ] Secure API key via config  

---

##### **File**: `app/Services/Notification/NotificationService.php`

```php
<?php
namespace App\Services\Notification;
use App\Models\Booking;
use App\Jobs\Notification\SendBookingConfirmationJob;
use App\Jobs\Notification\SendBookingReminderJob;
class NotificationService
{
    public function sendBookingConfirmation(Booking $booking): void
    {
        SendBookingConfirmationJob::dispatch($booking->id)->onQueue('notifications');
    }

    public function sendBookingReminder(Booking $booking): void
    {
        SendBookingReminderJob::dispatch($booking->id)
            ->delay(now()->addHours(24))
            ->onQueue('notifications');
    }

    public function sendBookingCancellation(Booking $booking): void
    {
        // Similar job dispatch
    }
}
```

**Checklist**:  
- [ ] Dispatch jobs to notification queue  
- [ ] Schedule reminders for 24h before booking  

---

#### **Requests (3 files)**

##### **File**: `app/Http/Requests/Booking/StoreBookingRequest.php`

```php
<?php
namespace App\Http\Requests\Booking;
use Illuminate\Foundation\Http\FormRequest;
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'center_id' => ['required', 'exists:centers,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'booking_type' => ['required', 'in:visit,consultation,trial_day'],
            'questionnaire_responses' => ['nullable', 'array'],
            'questionnaire_responses.elderly_age' => ['nullable', 'integer', 'min:50', 'max:120'],
            'questionnaire_responses.medical_conditions' => ['nullable', 'array'],
            'questionnaire_responses.mobility' => ['nullable', 'in:independent,walking_aid,wheelchair'],
        ];
    }
}
```

**Checklist**:  
- [ ] Validate center/service existence  
- [ ] Future date only  
- [ ] Questionnaire structure validation  

---

*(Similar for `UpdateBookingStatusRequest.php`, `CancelBookingRequest.php`)*

---

#### **Resources (1 file)**

##### **File**: `app/Http/Resources/BookingResource.php`

```php
<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'booking_type' => $this->booking_type,
            'status' => $this->status,
            'questionnaire_responses' => $this->when(auth()->user()->isAdmin(), $this->questionnaire_responses),
            'center' => CenterResource::make($this->whenLoaded('center')),
            'service' => ServiceResource::make($this->whenLoaded('service')),
            'calendly_cancel_url' => $this->calendly_cancel_url,
            'calendly_reschedule_url' => $this->calendly_reschedule_url,
            'created_at' => $this->created_at,
        ];
    }
}
```

**Checklist**:  
- [ ] Hide sensitive questionnaire data from non-admins  
- [ ] Include Calendly action URLs  
- [ ] Load center/service relationships  

---

#### **Controllers (2 files)**

##### **File**: `app/Http/Controllers/Api/V1/BookingController.php`

```php
// POST /api/v1/bookings → createBooking
// GET /api/v1/bookings → user booking history
// POST /api/v1/bookings/{id}/cancel → cancelBooking
```

##### **File**: `app/Http/Controllers/Api/V1/Admin/BookingController.php`

```php
// GET /api/v1/admin/bookings → all bookings
// PUT /api/v1/admin/bookings/{id}/confirm → confirmBooking
// PUT /api/v1/admin/bookings/{id}/cancel → cancelBooking (with reason)
```

---

#### **Jobs (2 files)**

- `SendBookingConfirmationJob.php`: Sends email + SMS (if consented)
- `SendBookingReminderJob.php`: Sends reminder 24h before

---

#### **Webhooks (1 file)**

##### **File**: `app/Http/Controllers/Webhook/CalendlyWebhookController.php`

```php
// Handle Calendly webhooks (invitee.created, invitee.canceled)
// Update local booking status accordingly
// Verify webhook signature for security
```

---

#### **Routes (1 block)**

```php
// User bookings
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::apiResource('bookings', BookingController::class)->only(['store', 'index']);
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});

// Admin bookings
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('v1/admin')->group(function () {
    Route::get('bookings', [Admin\BookingController::class, 'index']);
    Route::put('bookings/{booking}/confirm', [Admin\BookingController::class, 'confirm']);
    Route::put('bookings/{booking}/cancel', [Admin\BookingController::class, 'cancel']);
});

// Webhooks
Route::post('webhooks/calendly', [CalendlyWebhookController::class, 'handle']);
```

---

#### **Tests (3 files)**

- `tests/Unit/Services/Booking/BookingServiceTest.php`
- `tests/Feature/Booking/BookingApiTest.php`
- `tests/Feature/Webhook/CalendlyWebhookTest.php`

**Test Scenarios**:  
- Create booking with valid data → success  
- Create duplicate booking → validation error  
- Cancel booking → status updated, Calendly notified  
- Webhook updates local status → audit logged  

---

### **D.3. Validation Criteria**

✅ **Booking Creation**: Valid bookings created with unique numbers  
✅ **Calendly Sync**: Events created/cancelled in Calendly  
✅ **Notifications**: Confirmation/reminder sent via email/SMS  
✅ **Questionnaire**: Captured securely, hidden from non-admins  
✅ **Business Rules**: One booking per day enforced  
✅ **Audit Trail**: All changes logged  
✅ **Webhooks**: Calendly events update local status  
✅ **Tests**: >90% coverage, all validation rules tested  

---

### **D.4. Integration Notes**

- **Workstream A**: Uses `User` model for auth  
- **Workstream B**: Uses `AuditService`, `NotificationService`  
- **Workstream C**: Depends on `Center`/`Service` models  
- **External**: Calendly API, Twilio SMS, Mailchimp (for email)

---

We will now apply the **same rigorous, meticulous, and production-grade methodology** used for Workstreams A–D to design **Workstream E: Content & Community (FAQs, Testimonials, Contact Submissions)**.

This workstream is **essential for trust-building and user engagement**—it enables families to access authoritative information (FAQs), read authentic experiences (Testimonials), and reach out for support (Contact)—all while maintaining **PDPA compliance, moderation workflows, and multilingual readiness**.

---

## 🔍 Deep Analysis: What “Content & Community” Truly Means

Per the **database schema**, **product vision**, and **compliance requirements**, this workstream must deliver:

### 1. **FAQs (Frequently Asked Questions)**
- **Categorized** (`general`, `booking`, `services`, `pricing`, `accessibility`)
- **Ordered** by `display_order` for editorial control
- **Multilingual**: Content stored via `content_translations` (polymorphic)
- **Searchable**: Full-text index on `question`/`answer`
- **Admin-managed**: Only `admin`/`super_admin` can create/edit

### 2. **Testimonials**
- **User-submitted**: Tied to authenticated user + specific center
- **Moderated workflow**: `pending` → `approved`/`rejected`/`spam`
- **Rating system**: 1–5 stars
- **PDPA-compliant**: Soft deletes, audit logs
- **Multilingual**: Title/content translatable
- **Spam protection**: reCAPTCHA v3 (frontend), but backend must support moderation

### 3. **Contact Submissions**
- **Guest or authenticated**: `user_id` nullable
- **Center-specific**: Optional `center_id` for targeted inquiries
- **Spam detection**: Store `ip_address`, `user_agent`
- **Status tracking**: `new` → `in_progress` → `resolved`/`spam`
- **Notification**: Triggers internal alert (not user-facing)

All three entities:
- Must be **audited** (via `audit_logs`)
- Must support **soft deletes** (where applicable)
- Must be **secure** (no XSS, SQLi)
- Must be **testable** (>90% coverage)

---

## 🧱 Strategic Boundaries

### ✅ **In Scope (Workstream E Owns)**:
- FAQ CRUD (admin-only)
- Testimonial submission + moderation (user + admin)
- Contact form submission + status management (admin-only status updates)
- Multilingual content via `content_translations`
- API endpoints for public consumption (FAQs, approved testimonials)
- Admin API endpoints for management
- Moderation workflows (approve/reject/spam)
- Audit logging for all mutations

### ❌ **Out of Scope**:
- reCAPTCHA integration (frontend responsibility)
- Email/SMS notifications (Workstream F)
- Search (MeiliSearch — future phase)
- Frontend UI (Phase 4)

---

## 🗺️ Workstream E: Content & Community — Full Sub-Plan

### **Owner**: Backend Dev 1  
**Duration**: 3–4 days  
**Dependencies**:  
- Workstream A (User auth)  
- Workstream B (Audit logging, `content_translations` model)  
- Workstream C (Center model)  
**Priority**: MEDIUM-HIGH (Trust & engagement)

---

### **E.1. Implementation Sequence**

1. **Models** (3 files): `Faq`, `Testimonial`, `ContactSubmission`
2. **Repositories** (3 files): Data access layer
3. **Services** (3 files):  
   - `FaqService`  
   - `TestimonialService`  
   - `ContactSubmissionService`
4. **Requests** (5 files):  
   - `StoreFaqRequest.php`  
   - `UpdateFaqRequest.php`  
   - `StoreTestimonialRequest.php`  
   - `ModerateTestimonialRequest.php`  
   - `StoreContactSubmissionRequest.php`
5. **Resources** (3 files):  
   - `FaqResource.php`  
   - `TestimonialResource.php`  
   - `ContactSubmissionResource.php`
6. **Controllers** (4 files):  
   - `FaqController.php` (public)  
   - `TestimonialController.php` (public + user)  
   - `ContactSubmissionController.php` (public)  
   - `Admin/ContentController.php` (admin-only)
7. **Policies** (1 file): `ContentPolicy.php`
8. **Routes** (1 block): `/api/v1/faqs`, `/api/v1/testimonials`, etc.
9. **Tests** (3 files): Unit + Feature tests

---

### **E.2. File Creation Matrix**

#### **Models (3 files)**

##### **File**: `app/Models/Faq.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', 'question', 'answer', 'display_order', 'status'
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
```

**Checklist**:  
- [ ] MorphMany `translations`  
- [ ] Scopes: `published()`, `byCategory()`  
- [ ] Fillable attributes match schema  

---

##### **File**: `app/Models/Testimonial.php`

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

    protected $fillable = [
        'user_id', 'center_id', 'title', 'content', 'rating',
        'status', 'moderation_notes', 'moderated_by'
    ];

    protected $casts = [
        'rating' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCenter($query, int $centerId)
    {
        return $query->where('center_id', $centerId);
    }
}
```

**Checklist**:  
- [ ] Soft deletes  
- [ ] Relationships: user, center, moderatedBy  
- [ ] Scopes: `approved()`, `byCenter()`  
- [ ] Rating validation (1–5) enforced at DB level  

---

##### **File**: `app/Models/ContactSubmission.php`

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'center_id', 'name', 'email', 'phone', 'subject', 'message',
        'status', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'ip_address' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
}
```

**Checklist**:  
- [ ] Nullable `user_id`, `center_id`  
- [ ] Store `ip_address`, `user_agent` for spam detection  
- [ ] Scope: `new()`  

---

#### **Repositories (3 files)**

*(Standard CRUD operations with relationships)*

##### **File**: `app/Repositories/FaqRepository.php`

```php
public function allPublished(): Collection
{
    return Faq::published()->orderBy('display_order')->get();
}

public function findByCategory(string $category): Collection
{
    return Faq::published()->byCategory($category)->orderBy('display_order')->get();
}
```

*(Similar for `TestimonialRepository`, `ContactSubmissionRepository`)*

---

#### **Services (3 files)**

##### **File**: `app/Services/Content/TestimonialService.php`

```php
<?php
namespace App\Services\Content;
use App\Models\User;
use App\Models\Testimonial;
use App\Repositories\TestimonialRepository;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\Request;
class TestimonialService
{
    public function __construct(
        protected TestimonialRepository $repository,
        protected AuditService $auditService
    ) {}

    public function submitTestimonial(User $user, array $data): Testimonial
    {
        $testimonial = $this->repository->create([
            'user_id' => $user->id,
            'center_id' => $data['center_id'],
            'title' => $data['title'],
            'content' => $data['content'],
            'rating' => $data['rating'],
            'status' => 'pending',
        ]);
        $this->auditService->logCreate($testimonial);
        return $testimonial;
    }

    public function moderateTestimonial(Testimonial $testimonial, string $status, ?string $notes = null, ?User $moderator = null): Testimonial
    {
        $oldStatus = $testimonial->status;
        $testimonial->status = $status;
        $testimonial->moderation_notes = $notes;
        $testimonial->moderated_by = $moderator?->id;
        $testimonial->moderated_at = now();
        $testimonial->save();

        $this->auditService->logUpdate(
            $moderator,
            $testimonial,
            ['status' => $oldStatus],
            ['status' => $status]
        );
        return $testimonial;
    }
}
```

**Checklist**:  
- [ ] Submit testimonial (status = pending)  
- [ ] Moderate (approve/reject/spam) with notes  
- [ ] Log audit with moderator context  

---

*(Similar for `FaqService`, `ContactSubmissionService`)*

---

#### **Requests (5 files)**

##### **File**: `app/Http/Requests/Content/StoreTestimonialRequest.php`

```php
public function rules(): array
{
    return [
        'center_id' => ['required', 'exists:centers,id'],
        'title' => ['required', 'string', 'max:255'],
        'content' => ['required', 'string', 'max:2000'],
        'rating' => ['required', 'integer', 'min:1', 'max:5'],
    ];
}
```

##### **File**: `app/Http/Requests/Content/ModerateTestimonialRequest.php`

```php
public function rules(): array
{
    return [
        'status' => ['required', 'in:approved,rejected,spam'],
        'moderation_notes' => ['nullable', 'string', 'max:500'],
    ];
}
```

*(Similar for FAQ and Contact requests)*

---

#### **Resources (3 files)**

##### **File**: `app/Http/Resources/TestimonialResource.php`

```php
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'content' => $this->content,
        'rating' => $this->rating,
        'user' => UserResource::make($this->whenLoaded('user')),
        'center' => CenterResource::make($this->whenLoaded('center')),
        'created_at' => $this->created_at,
    ];
}
```

**Note**: Only **approved** testimonials shown in public API.

---

#### **Controllers (4 files)**

##### **Public APIs**:
- `FaqController@index` → all published FAQs
- `TestimonialController@index` → approved testimonials (optionally by center)
- `ContactSubmissionController@store` → create submission

##### **User APIs**:
- `TestimonialController@store` → submit testimonial (auth required)

##### **Admin APIs** (`Admin/ContentController.php`):
- `POST /faqs` → create FAQ
- `PUT /faqs/{id}` → update FAQ
- `PUT /testimonials/{id}/moderate` → moderate testimonial
- `PUT /contact-submissions/{id}` → update status

---

#### **Policies (1 file)**

##### **File**: `app/Policies/ContentPolicy.php`

```php
public function manage(User $user): bool
{
    return $user->isAdmin() || $user->isSuperAdmin();
}

public function moderateTestimonial(User $user): bool
{
    return $this->manage($user);
}
```

Applied via middleware: `->middleware('can:manage,App\Models\Faq')`

---

#### **Routes (1 block)**

```php
// Public content
Route::get('faqs', [FaqController::class, 'index']);
Route::get('testimonials', [TestimonialController::class, 'index']);

// User content
Route::middleware('auth:sanctum')->group(function () {
    Route::post('testimonials', [TestimonialController::class, 'store']);
});

// Contact
Route::post('contact', [ContactSubmissionController::class, 'store']);

// Admin content
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->group(function () {
    Route::apiResource('faqs', Admin\FaqController::class);
    Route::put('testimonials/{testimonial}/moderate', [Admin\TestimonialController::class, 'moderate']);
    Route::apiResource('contact-submissions', Admin\ContactSubmissionController::class)->only(['index', 'update']);
});
```

---

#### **Tests (3 files)**

- `tests/Feature/Content/FaqTest.php`
- `tests/Feature/Content/TestimonialTest.php`
- `tests/Feature/Content/ContactSubmissionTest.php`

**Test Scenarios**:  
- Submit testimonial → status = pending  
- Approve testimonial → appears in public API  
- Submit contact form → stored with IP  
- Non-admin tries to create FAQ → 403  
- FAQ ordering respected in response  

---

### **E.3. Validation Criteria**

✅ **FAQs**: Published FAQs returned in order, by category  
✅ **Testimonials**: Only approved shown publicly; moderation workflow functional  
✅ **Contact**: Submissions stored with IP/user agent; status updatable by admin  
✅ **Multilingual**: `content_translations` linked (frontend will consume)  
✅ **Audit Trail**: All changes logged in `audit_logs`  
✅ **Security**: No XSS (content escaped in resource), validation enforced  
✅ **Tests**: >90% coverage, all validation rules tested  

---

### **E.4. Integration Notes**

- **Workstream B**: Uses `AuditService`, `ContentTranslation` model  
- **Workstream A**: Uses `User` auth and roles  
- **Workstream C**: Depends on `Center` model for testimonials  
- **Future**: Frontend will use `content_translations` for multilingual display

---

This **Workstream E** is now **fully specified, production-ready, and autonomously implementable**.

Shall we proceed to **Workstream F: API Infrastructure & External Integrations** next?
