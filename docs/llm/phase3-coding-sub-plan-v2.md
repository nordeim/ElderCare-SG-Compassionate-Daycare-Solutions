# Phase 3 Backend Execution Sub-Plan — Core Services, PDPA Compliance & API Foundation

<think>
This is a critical planning phase. The user needs:
1. Complete backend implementation to enable a production demo
2. Comprehensive file-by-file roadmap with feature checklists
3. A plan that an independent AI agent can execute without ambiguity
4. Deep integration with the existing schema (18 tables, complex relationships)
5. PDPA compliance as a non-negotiable requirement
6. External integrations (Calendly, Mailchimp, Twilio, S3, Cloudflare Stream)

I need to think strategically about:
- Layered architecture (Models → Repositories → Services → Controllers)
- Service-oriented design (thin controllers, fat services)
- PDPA compliance embedded at every layer (audit logging, consent checks)
- API-first design with consistent response formats
- Queue-based async operations (emails, SMS, Mailchimp sync)
- Comprehensive testing strategy
- File organization that scales

Let me structure this as a multi-workstream plan with:
- Clear dependencies between workstreams
- File creation matrix with descriptions and checklists
- Validation criteria for each deliverable
- Risk mitigation strategies
</think>

---

## 1. Executive Summary

**Objective**: Deliver production-ready Laravel backend API (v1) with complete PDPA compliance, MOH regulatory support, and external integrations to enable an impressive stakeholder demo.

**Scope**: 
- ✅ Foundation: Authentication (Sanctum), authorization (RBAC), PDPA compliance (consent + audit)
- ✅ Core Business Logic: Centers, services, staff, FAQs, contact forms, newsletter subscriptions
- ✅ Booking System: Calendly integration, questionnaires, email/SMS notifications, webhooks
- ✅ Advanced Features: Testimonial moderation, media management (S3), multilingual content
- ✅ API Infrastructure: Consistent response formats, error handling, rate limiting, OpenAPI docs
- ✅ Testing: >90% coverage (unit + feature tests), factories/seeders, integration tests

**Out of Scope** (Deferred to Phase 4+):
- Frontend integration (handled in Phase 4)
- MeiliSearch advanced search (Phase 7)
- Cloudflare Stream video processing (Phase 5/6)
- Laravel Nova admin panel (Phase 5)

**Success Criteria**:
- All API endpoints documented and testable via Postman
- PDPA compliance verified (consent tracking, audit logs, data export, account deletion)
- External integrations functional (Calendly webhook, Mailchimp sync, Twilio SMS)
- Test coverage ≥90% (PHPUnit)
- Staging deployment successful with smoke tests passing

---

## Progress update — remediation performed (2025-10-13)

This section records concrete remediation work that was completed during Pre‑Phase‑3 so that subsequent AI agents or engineers can skip redoing finished tasks.

- Migration hardening (completed)
   - Summary: MySQL-only SQL in several migrations was wrapped in a positive DB driver guard so SQLite-based CI/tests will not fail while preserving MySQL behaviour in production:
      - Guard pattern used (example):

```php
use Illuminate\Support\Facades\DB;

if (DB::getDriverName() === 'mysql') {
      DB::statement("/* mysql-only SQL here */");
}
```

      - Files patched (in-place on `main`):
         - `backend/database/migrations/2024_01_01_200000_create_centers_table.php`
         - `backend/database/migrations/2024_01_01_200001_create_faqs_table.php`
         - `backend/database/migrations/2024_01_01_300000_create_services_table.php`
         - `backend/database/migrations/2024_01_01_400001_create_testimonials_table.php` (also fixed a malformed import line discovered during edits)

      - Effect: Running `php artisan migrate --database=sqlite` will skip MySQL-only ALTER/INDEX/CHECK statements and should complete successfully for these migrations.

- Documentation updates (completed)
   - `docs/ai-agent-onboarding.md` was updated to include a detailed "Migration hardening — completed work" section that lists the guarded files, the guard pattern, and the active tracked todo list. Agents should consult that file for the authoritative short checklist before making migration edits.

- Current tracked todo status (canonical):
   - Review Phase 3 plan vs code — COMPLETED
   - Produce progress matrix — NOT STARTED
   - List gaps and prioritized next steps — NOT STARTED

- Important notes & caveats
   - The CI workflow was NOT changed. The recommended CI change to create a `backend/database/database.sqlite` (or use :memory:) and run `php artisan migrate --database=sqlite --force` before tests remains to be implemented — this is high priority to catch regressions.
   - A repo-wide audit for other MySQL-specific constructs (additional migrations, raw `DB::statement` uses, or package-supplied SQL) has NOT been completed. Search terms to run: `DB::statement`, `FULLTEXT`, `fullText`, `CHECK`, `ALTER TABLE`.
   - Do NOT reapply guards to the files listed above; instead, if you identify new MySQL-only SQL, add it to the todo list and apply the same positive-driver guard pattern.

- Next recommended immediate actions (priority order)
   1. Add CI sqlite migration step so CI runs migrations before tests and fails early on incompatibilities.
   2. Run `php artisan migrate --database=sqlite` locally/in-CI and run the test suite; fix any remaining incompatibilities surfaced.
   3. Run a repo-wide search for MySQL-only SQL and guard or refactor findings.
   4. Produce the progress matrix (file-by-file Implemented/Partial/Missing) and the prioritized gaps list.


## 2. Database Schema Deep Dive & Backend Implications

### 2.1 Schema Complexity Analysis

| Complexity Factor | Impact on Backend Design |
|-------------------|--------------------------|
| **18 interconnected tables** | Requires robust Eloquent relationship mapping, eager loading strategies, N+1 query prevention |
| **Polymorphic relationships** (media, content_translations, audit_logs) | Need trait-based implementations, dynamic relationship resolution |
| **JSON columns** (operating_hours, amenities, questionnaire_responses) | Custom casting, validation rules, query helpers |
| **Soft deletes** (users, centers, services, bookings, testimonials) | Global scopes, trash/restore endpoints, PDPA grace period jobs |
| **PDPA audit trail** | Observer pattern for automatic audit log creation on model events |
| **External service IDs** (calendly_event_id, mailchimp_subscriber_id, cloudflare_stream_id) | API client abstraction, webhook signature verification, retry logic |
| **Multilingual content** | Translation repository pattern, locale-based query scopes |
| **MOH compliance fields** | Validation rules for license numbers, expiry date alerts, accreditation workflows |

### 2.2 Critical Relationships Map

```
users (1) ──→ (1) profiles
  │
  ├──→ (*) bookings ──→ (1) centers ──→ (*) services
  │                      │              │
  ├──→ (*) testimonials ─┘              └──→ (*) media (polymorphic)
  │                                      └──→ (*) content_translations (polymorphic)
  ├──→ (*) consents                      
  │
  ├──→ (*) audit_logs (polymorphic)
  │
  └──→ (*) contact_submissions (optional)

centers (1) ──→ (*) staff

subscriptions (independent, Mailchimp-synced)

faqs (independent, multilingual via content_translations)
```

### 2.3 Service Layer Responsibilities (Derived from Schema)

| Service | Primary Responsibilities | Key Dependencies |
|---------|-------------------------|------------------|
| **AuthService** | Registration, login, logout, email verification, password reset | `users`, `password_reset_tokens`, `consents` |
| **ConsentService** | Consent capture, withdrawal, versioning, export | `consents`, `audit_logs` |
| **AuditService** | Automatic logging of model changes | `audit_logs` (polymorphic) |
| **UserService** | Profile management, data export, account deletion | `users`, `profiles`, `consents`, `audit_logs` |
| **CenterService** | CRUD, MOH compliance checks, capacity management | `centers`, `services`, `staff`, `media`, `content_translations` |
| **ServiceMgmtService** | Center service CRUD, pricing, features | `services`, `media`, `content_translations` |
| **BookingService** | Booking creation, status management, questionnaires | `bookings`, `centers`, `services`, `users`, Calendly API |
| **CalendlyService** | Event creation, cancellation, webhook processing | External Calendly API, `bookings` |
| **NotificationService** | Email/SMS sending, template rendering, queue jobs | Twilio API, Laravel Mail, `bookings` |
| **MailchimpService** | Subscription sync, double opt-in, webhook handling | External Mailchimp API, `subscriptions` |
| **MediaService** | Upload to S3, optimization, polymorphic attachment | AWS S3 SDK, `media` |
| **TranslationService** | Translation CRUD, workflow management | `content_translations` |
| **TestimonialService** | Submission, moderation, rating aggregation | `testimonials`, `users`, `centers` |
| **ContactService** | Form submission, spam detection, status tracking | `contact_submissions` |

---

## 3. Phase 3 Workstream Breakdown (6 Workstreams)

### Timeline Overview (Total: 10-11 days)

```
Day 1-2   │ Workstream A: Foundation (Models, Auth, PDPA)
Day 3-4   │ Workstream B: Core Business Logic
Day 5-6   │ Workstream C: Booking System & Integrations
Day 7-8   │ Workstream D: Advanced Features
Day 9     │ Workstream E: API Layer & Documentation
Day 10-11 │ Workstream F: Testing & Quality Assurance
```

---

## 4. Detailed File Creation Matrix

### 🔹 Workstream A: Foundation Services (Days 1-2)

**Objective**: Establish authentication, authorization, PDPA compliance foundation.

**Dependencies**: Completed Phase 1 migrations, Docker environment.

#### A.1 Eloquent Models & Relationships

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Models/User.php` | User model with authentication, PDPA compliance | - [ ] `HasApiTokens`, `SoftDeletes`, `Notifiable` traits<br>- [ ] Relationships: `profile()`, `bookings()`, `testimonials()`, `consents()`, `auditLogs()`<br>- [ ] Accessors: `preferredLanguage`, `isAdmin()`<br>- [ ] Mutators: `password` (bcrypt)<br>- [ ] Casts: `email_verified_at` (datetime)<br>- [ ] Hidden: `password`, `remember_token` |
| `backend/app/Models/Profile.php` | User profile (1:1 with User) | - [ ] Relationship: `user()`<br>- [ ] Casts: `birth_date` (date)<br>- [ ] Fillable: `avatar`, `bio`, `address`, `city`, `postal_code` |
| `backend/app/Models/Consent.php` | PDPA consent tracking | - [ ] Relationship: `user()`<br>- [ ] Casts: `consent_given` (boolean), `created_at` (datetime)<br>- [ ] Scopes: `ofType()`, `active()`, `withdrawn()`<br>- [ ] Methods: `isActive()`, `withdraw()` |
| `backend/app/Models/AuditLog.php` | Polymorphic audit trail | - [ ] Polymorphic: `auditable()`<br>- [ ] Relationship: `user()`<br>- [ ] Casts: `old_values` (array), `new_values` (array)<br>- [ ] Scopes: `forModel()`, `byAction()` |
| `backend/app/Models/Center.php` | Eldercare centers (core entity) | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `services()`, `staff()`, `bookings()`, `testimonials()`, `media()`, `translations()`<br>- [ ] Casts: `operating_hours` (array), `medical_facilities` (array), `amenities` (array), `transport_info` (array), `languages_supported` (array), `government_subsidies` (array), `license_expiry_date` (date)<br>- [ ] Accessors: `occupancyRate()`, `averageRating()`, `isLicenseValid()`<br>- [ ] Scopes: `published()`, `validLicense()`, `inCity()`<br>- [ ] Sluggable implementation |
| `backend/app/Models/Service.php` | Center services | - [ ] `SoftDeletes` trait<br>- [ ] Relationship: `center()`, `bookings()`, `media()`, `translations()`<br>- [ ] Casts: `price` (decimal:2), `features` (array)<br>- [ ] Scopes: `published()`, `forCenter()` |
| `backend/app/Models/Staff.php` | Center staff (MOH compliance) | - [ ] Relationship: `center()`<br>- [ ] Casts: `qualifications` (array), `years_of_experience` (integer)<br>- [ ] Scopes: `active()`, `forCenter()` |
| `backend/app/Models/Booking.php` | Bookings with Calendly integration | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `user()`, `center()`, `service()`<br>- [ ] Casts: `booking_date` (date), `booking_time` (datetime), `questionnaire_responses` (array), `sms_sent` (boolean)<br>- [ ] Scopes: `upcoming()`, `byStatus()`, `forUser()`, `forCenter()`<br>- [ ] Methods: `confirm()`, `cancel()`, `markCompleted()`, `sendReminder()` |
| `backend/app/Models/Testimonial.php` | User testimonials with moderation | - [ ] `SoftDeletes` trait<br>- [ ] Relationships: `user()`, `center()`, `moderatedBy()`<br>- [ ] Casts: `rating` (integer), `moderated_at` (datetime)<br>- [ ] Scopes: `approved()`, `pending()`, `forCenter()`<br>- [ ] Methods: `approve()`, `reject()`, `markAsSpam()` |
| `backend/app/Models/FAQ.php` | FAQs with multilingual support | - [ ] Relationship: `translations()`<br>- [ ] Scopes: `published()`, `byCategory()`, `ordered()`<br>- [ ] Casts: `display_order` (integer) |
| `backend/app/Models/Subscription.php` | Newsletter subscriptions (Mailchimp) | - [ ] Casts: `preferences` (array), `subscribed_at` (datetime), `unsubscribed_at` (datetime), `last_synced_at` (datetime)<br>- [ ] Scopes: `subscribed()`, `pending()`<br>- [ ] Methods: `subscribe()`, `unsubscribe()`, `syncToMailchimp()` |
| `backend/app/Models/ContactSubmission.php` | Contact form submissions | - [ ] Relationships: `user()`, `center()`<br>- [ ] Scopes: `new()`, `byStatus()`<br>- [ ] Methods: `markAsSpam()`, `resolve()` |
| `backend/app/Models/Media.php` | Polymorphic media storage | - [ ] Polymorphic: `mediable()`<br>- [ ] Casts: `size` (integer), `duration` (integer), `display_order` (integer)<br>- [ ] Scopes: `images()`, `videos()`, `ordered()`<br>- [ ] Accessors: `sizeInMB()`, `formattedDuration()` |
| `backend/app/Models/ContentTranslation.php` | Polymorphic translations | - [ ] Polymorphic: `translatable()`<br>- [ ] Relationships: `translator()`, `reviewer()`<br>- [ ] Scopes: `locale()`, `published()`, `forField()`<br>- [ ] Methods: `markTranslated()`, `markReviewed()`, `publish()` |

**Validation**: All models have unit tests verifying relationships, scopes, accessors, mutators.

---

#### A.2 PDPA Compliance Services

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Consent/ConsentService.php` | Consent management service | - [ ] `captureConsent($userId, $type, $consentText, $version, $ipAddress, $userAgent)`: Record consent<br>- [ ] `withdrawConsent($userId, $type, $ipAddress, $userAgent)`: Withdraw consent<br>- [ ] `checkConsent($userId, $type)`: Check if consent is active<br>- [ ] `getConsentHistory($userId)`: Retrieve full consent history<br>- [ ] `exportConsentData($userId)`: Export for PDPA data request<br>- [ ] Automatic audit logging on consent changes |
| `backend/app/Services/Audit/AuditService.php` | Audit logging service | - [ ] `log($model, $action, $oldValues, $newValues, $userId, $ipAddress, $userAgent, $url)`: Create audit log<br>- [ ] `getAuditTrail($model)`: Retrieve audit history for a model<br>- [ ] `searchAuditLogs($filters)`: Search audit logs by date/user/action<br>- [ ] `exportAuditLogs($startDate, $endDate)`: Export for compliance audits<br>- [ ] Automatic 7-year retention policy enforcement |
| `backend/app/Observers/AuditObserver.php` | Model observer for automatic audit logging | - [ ] `created($model)`: Log creation event<br>- [ ] `updated($model)`: Log update event with old/new values<br>- [ ] `deleted($model)`: Log deletion event<br>- [ ] `restored($model)`: Log restore event<br>- [ ] IP/user agent capture from request context |
| `backend/app/Services/User/DataExportService.php` | PDPA data export service | - [ ] `exportUserData($userId)`: Generate JSON export of all user data<br>- [ ] Include: profile, bookings, testimonials, consents, audit logs<br>- [ ] Format: JSON with nested relationships<br>- [ ] Queue job for large datasets<br>- [ ] Email download link when ready |
| `backend/app/Services/User/AccountDeletionService.php` | PDPA account deletion service | - [ ] `requestDeletion($userId)`: Soft delete with 30-day grace period<br>- [ ] `cancelDeletion($userId)`: Restore account within grace period<br>- [ ] `permanentlyDelete($userId)`: Hard delete (queue job)<br>- [ ] Anonymize related data (bookings, testimonials)<br>- [ ] Notify user of deletion stages |
| `backend/app/Jobs/PermanentAccountDeletionJob.php` | Queue job for permanent deletion | - [ ] Execute after 30-day grace period<br>- [ ] Hard delete user record<br>- [ ] Anonymize bookings (replace user_id with null or anonymized ID)<br>- [ ] Remove PII from audit logs<br>- [ ] Send final deletion confirmation email |

**Validation**: 
- ✅ Consent capture/withdrawal flows tested with different consent types
- ✅ Audit logs automatically created on model changes (verified via observer tests)
- ✅ Data export generates complete JSON with all relationships
- ✅ Account deletion follows 30-day grace period, anonymizes data correctly

---

#### A.3 Authentication & Authorization

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php` | User registration | - [ ] `store(RegisterRequest $request)`: Create user with consent capture<br>- [ ] Validate unique email, strong password<br>- [ ] Send email verification<br>- [ ] Return 201 with user resource + token<br>- [ ] Log registration audit event |
| `backend/app/Http/Controllers/Api/V1/Auth/LoginController.php` | User login | - [ ] `store(LoginRequest $request)`: Authenticate and issue token<br>- [ ] Validate credentials<br>- [ ] Check email verification status<br>- [ ] Return user resource + Sanctum token<br>- [ ] Log login audit event with IP |
| `backend/app/Http/Controllers/Api/V1/Auth/LogoutController.php` | User logout | - [ ] `destroy(Request $request)`: Revoke current token<br>- [ ] Return 204 No Content<br>- [ ] Log logout audit event |
| `backend/app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php` | Email verification | - [ ] `verify(Request $request)`: Mark email as verified<br>- [ ] Signed URL validation<br>- [ ] Return 200 with success message<br>- [ ] Trigger welcome email |
| `backend/app/Http/Controllers/Api/V1/Auth/PasswordResetController.php` | Password reset | - [ ] `requestReset(Request $request)`: Send password reset email<br>- [ ] `reset(Request $request)`: Reset password with token<br>- [ ] Token expiry validation (60 mins)<br>- [ ] Log password change audit event |
| `backend/app/Http/Requests/Auth/RegisterRequest.php` | Registration validation | - [ ] Required: `name`, `email`, `password`, `password_confirmation`, `consent_account`, `consent_terms`<br>- [ ] Email format, unique validation<br>- [ ] Password min 8 chars, complexity rules<br>- [ ] Consent boolean validation |
| `backend/app/Http/Requests/Auth/LoginRequest.php` | Login validation | - [ ] Required: `email`, `password`<br>- [ ] Email format validation<br>- [ ] Optional: `remember` (boolean) |
| `backend/app/Http/Middleware/EnsureEmailIsVerified.php` | Email verification middleware | - [ ] Block unverified users from protected routes<br>- [ ] Return 403 with verification required message |
| `backend/app/Http/Middleware/CheckRole.php` | Role-based authorization middleware | - [ ] `handle($request, $next, ...$roles)`: Check user role<br>- [ ] Support multiple roles: `CheckRole::class.':admin,super_admin'`<br>- [ ] Return 403 if role mismatch |
| `backend/app/Policies/UserPolicy.php` | User authorization policy | - [ ] `update(User $user, User $model)`: User can update own profile, admins can update any<br>- [ ] `delete(User $user, User $model)`: Only super_admin can delete users<br>- [ ] `viewAny(User $user)`: Only admins can list all users |

**Validation**:
- ✅ Registration flow: user created, consent captured, verification email sent
- ✅ Login returns valid Sanctum token, works with `Authorization: Bearer {token}` header
- ✅ Email verification required for protected endpoints
- ✅ Role middleware blocks unauthorized access (tested with admin/user roles)

---

#### A.4 API Infrastructure

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Responses/ApiResponse.php` | Standardized API response formatter | - [ ] `success($data, $message, $statusCode)`: Success response<br>- [ ] `error($message, $errors, $statusCode)`: Error response<br>- [ ] `paginated($paginator, $resourceClass)`: Paginated collection<br>- [ ] Consistent JSON structure: `{success, message, data, errors, meta}`<br>- [ ] Support for Laravel API resources |
| `backend/app/Exceptions/Handler.php` (modify) | Global exception handler | - [ ] Catch `ValidationException`: Return 422 with field errors<br>- [ ] Catch `AuthenticationException`: Return 401<br>- [ ] Catch `AuthorizationException`: Return 403<br>- [ ] Catch `ModelNotFoundException`: Return 404<br>- [ ] Catch `ThrottleRequestsException`: Return 429<br>- [ ] Catch generic exceptions: Return 500 with sanitized message (no stack trace in production)<br>- [ ] Log all exceptions to Sentry |
| `backend/app/Http/Middleware/RateLimitApi.php` | API rate limiting | - [ ] Default: 60 requests per minute per user<br>- [ ] Higher limits for authenticated users: 120/min<br>- [ ] Return 429 with `Retry-After` header<br>- [ ] Different limits for auth endpoints (stricter): 5/min for login |
| `backend/app/Http/Middleware/LogApiRequest.php` | API request logging middleware | - [ ] Log all API requests: method, path, IP, user_id, response status<br>- [ ] Store in `api_request_logs` table (create migration)<br>- [ ] Exclude sensitive data (passwords, tokens) from logs |
| `backend/config/sanctum.php` (configure) | Sanctum configuration | - [ ] Token expiration: 60 days (configurable)<br>- [ ] Stateful domains for SPA: `localhost:3000`, staging/production domains<br>- [ ] Token abilities/permissions support enabled |
| `backend/routes/api.php` | API routing structure | - [ ] Group `/api/v1` with rate limiting, JSON middleware<br>- [ ] Public routes: auth (register, login), password reset, contact form<br>- [ ] Protected routes: profile, bookings, testimonials, data export<br>- [ ] Admin routes: center management, moderation, user management<br>- [ ] Versioning strategy documented |

**Validation**:
- ✅ All API responses follow consistent format (automated test for response structure)
- ✅ Exceptions return proper HTTP status codes with user-friendly messages
- ✅ Rate limiting blocks excessive requests, returns `Retry-After` header
- ✅ API request logs captured for debugging/analytics

---

### 🔹 Workstream B: Core Business Logic (Days 3-4)

**Objective**: Implement center management, services, FAQs, contact forms, newsletter subscriptions.

**Dependencies**: Workstream A models and services.

#### B.1 Center & Service Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Center/CenterService.php` | Center management service | - [ ] `create($data)`: Create center with MOH validation<br>- [ ] `update($centerId, $data)`: Update center, check license expiry<br>- [ ] `delete($centerId)`: Soft delete<br>- [ ] `publish($centerId)`: Change status to published<br>- [ ] `archive($centerId)`: Archive center<br>- [ ] `checkLicenseExpiry()`: Alert if license expires within 30 days<br>- [ ] `updateOccupancy($centerId, $newOccupancy)`: Update current occupancy<br>- [ ] `getWithStatistics($centerId)`: Return center with services count, staff count, avg rating<br>- [ ] Automatic audit logging |
| `backend/app/Services/Center/ServiceManagementService.php` | Service CRUD service | - [ ] `create($centerId, $data)`: Add service to center<br>- [ ] `update($serviceId, $data)`: Update service<br>- [ ] `delete($serviceId)`: Soft delete<br>- [ ] `getServicesForCenter($centerId)`: List all services for center<br>- [ ] `publish($serviceId)`: Publish service |
| `backend/app/Services/Center/StaffService.php` | Staff management service | - [ ] `create($centerId, $data)`: Add staff member<br>- [ ] `update($staffId, $data)`: Update staff<br>- [ ] `delete($staffId)`: Delete staff<br>- [ ] `reorder($centerId, $orderArray)`: Update display order<br>- [ ] `getActiveStaff($centerId)`: List active staff for center |
| `backend/app/Http/Controllers/Api/V1/CenterController.php` | Center API controller | - [ ] `index(Request $request)`: List centers (paginated, filterable by city/status)<br>- [ ] `show($slug)`: Show single center with relationships<br>- [ ] `store(StoreCenterRequest $request)`: Create center (admin only)<br>- [ ] `update($id, UpdateCenterRequest $request)`: Update center (admin only)<br>- [ ] `destroy($id)`: Soft delete center (admin only)<br>- [ ] Use `CenterResource` for response transformation |
| `backend/app/Http/Controllers/Api/V1/ServiceController.php` | Service API controller | - [ ] `index($centerId)`: List services for center<br>- [ ] `show($centerId, $serviceSlug)`: Show single service<br>- [ ] `store($centerId, StoreServiceRequest $request)`: Create service (admin only)<br>- [ ] `update($id, UpdateServiceRequest $request)`: Update service (admin only)<br>- [ ] `destroy($id)`: Soft delete service (admin only) |
| `backend/app/Http/Requests/StoreCenterRequest.php` | Center creation validation | - [ ] Required: `name`, `address`, `city`, `postal_code`, `phone`, `email`, `moh_license_number`, `license_expiry_date`, `capacity`, `description`<br>- [ ] MOH license format validation (Singapore format)<br>- [ ] License expiry date must be future<br>- [ ] Capacity must be positive integer<br>- [ ] JSON validation for `operating_hours`, `amenities`, `transport_info` |
| `backend/app/Http/Requests/UpdateCenterRequest.php` | Center update validation | - [ ] Same as create, but all fields optional<br>- [ ] Current occupancy cannot exceed capacity |
| `backend/app/Http/Requests/StoreServiceRequest.php` | Service creation validation | - [ ] Required: `name`, `description`<br>- [ ] Optional: `price` (decimal, min 0), `price_unit`, `duration`<br>- [ ] JSON validation for `features` array |
| `backend/app/Http/Resources/CenterResource.php` | Center API resource transformer | - [ ] Transform: id, name, slug, description, address, city, postal_code, phone, email, website<br>- [ ] Include: services (count), staff (count), average_rating, occupancy_rate, license status<br>- [ ] Conditional: MOH license details (admin only), internal notes (admin only)<br>- [ ] Nested: services (when requested), staff (when requested), media (when requested) |
| `backend/app/Http/Resources/ServiceResource.php` | Service API resource transformer | - [ ] Transform: id, name, slug, description, price, price_unit, duration, features, status<br>- [ ] Include: center (basic info)<br>- [ ] Nested: media (when requested) |

**Validation**:
- ✅ Center CRUD operations work with proper authorization (admin only)
- ✅ MOH license validation enforced (format, expiry date)
- ✅ Center listing filterable by city, status, and searchable by name
- ✅ Services correctly associated with centers
- ✅ API resources transform data consistently

---

#### B.2 FAQ, Contact Form, Newsletter

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Content/FAQService.php` | FAQ management service | - [ ] `create($data)`: Create FAQ<br>- [ ] `update($faqId, $data)`: Update FAQ<br>- [ ] `delete($faqId)`: Delete FAQ<br>- [ ] `reorder($category, $orderArray)`: Update display order<br>- [ ] `getPublishedByCategory($category)`: Get published FAQs for category<br>- [ ] `search($query)`: Full-text search in questions/answers |
| `backend/app/Services/Contact/ContactService.php` | Contact form service | - [ ] `submit($data)`: Create contact submission<br>- [ ] `markAsSpam($submissionId)`: Flag as spam<br>- [ ] `resolve($submissionId)`: Mark as resolved<br>- [ ] `detectSpam($data)`: Simple spam detection (rate limiting, honeypot field)<br>- [ ] `notifyAdmin($submission)`: Send admin notification email<br>- [ ] IP address logging for spam prevention |
| `backend/app/Services/Newsletter/MailchimpService.php` | Mailchimp integration service | - [ ] `subscribe($email, $preferences)`: Add subscriber to Mailchimp (double opt-in)<br>- [ ] `unsubscribe($email)`: Remove subscriber from Mailchimp<br>- [ ] `updatePreferences($email, $preferences)`: Update subscriber preferences<br>- [ ] `syncSubscription($subscriptionId)`: Sync local subscription with Mailchimp<br>- [ ] `handleWebhook($payload, $signature)`: Process Mailchimp webhooks (unsubscribe, cleaned)<br>- [ ] Retry logic for API failures<br>- [ ] Queue job for async sync |
| `backend/app/Http/Controllers/Api/V1/FAQController.php` | FAQ API controller | - [ ] `index(Request $request)`: List FAQs (filterable by category, status)<br>- [ ] `show($id)`: Show single FAQ<br>- [ ] `store(StoreFAQRequest $request)`: Create FAQ (admin only)<br>- [ ] `update($id, UpdateFAQRequest $request)`: Update FAQ (admin only)<br>- [ ] `destroy($id)`: Delete FAQ (admin only) |
| `backend/app/Http/Controllers/Api/V1/ContactController.php` | Contact form API controller | - [ ] `store(ContactRequest $request)`: Submit contact form<br>- [ ] Spam detection before storing<br>- [ ] Send admin notification<br>- [ ] Return 201 with success message |
| `backend/app/Http/Controllers/Api/V1/SubscriptionController.php` | Newsletter subscription API controller | - [ ] `store(SubscribeRequest $request)`: Subscribe to newsletter<br>- [ ] Queue Mailchimp sync job<br>- [ ] Send double opt-in email<br>- [ ] Return 201 with pending status<br>- [ ] `destroy(Request $request)`: Unsubscribe (email parameter)<br>- [ ] `webhook(Request $request)`: Handle Mailchimp webhooks |
| `backend/app/Http/Requests/ContactRequest.php` | Contact form validation | - [ ] Required: `name`, `email`, `subject`, `message`<br>- [ ] Optional: `phone`, `center_id`<br>- [ ] Email format validation<br>- [ ] Message min 10 characters<br>- [ ] Honeypot field validation |
| `backend/app/Http/Requests/SubscribeRequest.php` | Newsletter subscription validation | - [ ] Required: `email`<br>- [ ] Email format, unique validation<br>- [ ] Optional: `preferences` (JSON) |
| `backend/app/Jobs/SyncMailchimpSubscriptionJob.php` | Queue job for Mailchimp sync | - [ ] Call `MailchimpService::syncSubscription()`<br>- [ ] Retry 3 times on failure<br>- [ ] Update `last_synced_at` timestamp<br>- [ ] Update `mailchimp_status` from API response |

**Validation**:
- ✅ FAQ CRUD operations work, display_order updates correctly
- ✅ Contact form submissions stored with spam detection
- ✅ Newsletter subscription triggers Mailchimp API call (mocked in tests)
- ✅ Mailchimp webhook handler processes unsubscribe events correctly

---

### 🔹 Workstream C: Booking System & Integrations (Days 5-6)

**Objective**: Complete booking workflow with Calendly integration, notifications (email/SMS), webhooks.

**Dependencies**: Workstream A/B services, external API credentials (Calendly, Twilio).

#### C.1 Booking Service & Calendly Integration

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Booking/BookingService.php` | Core booking service | - [ ] `create($userId, $data)`: Create booking with questionnaire<br>- [ ] Generate unique booking_number (BK-YYYYMMDD-####)<br>- [ ] Validate booking date/time (not in past, center availability)<br>- [ ] Call `CalendlyService::createEvent()`<br>- [ ] Store Calendly event ID, URIs<br>- [ ] Queue confirmation email/SMS<br>- [ ] Return booking resource<br>- [ ] `update($bookingId, $data)`: Update booking (reschedule via Calendly)<br>- [ ] `cancel($bookingId, $reason)`: Cancel booking, call Calendly cancel API<br>- [ ] `confirm($bookingId)`: Change status to confirmed<br>- [ ] `complete($bookingId)`: Mark as completed<br>- [ ] `sendReminders()`: Queue reminders for bookings 24h ahead<br>- [ ] Automatic audit logging |
| `backend/app/Services/Integration/CalendlyService.php` | Calendly API integration | - [ ] `createEvent($centerCalendlyUrl, $bookingDetails)`: Create Calendly event<br>- [ ] `cancelEvent($calendlyEventUri)`: Cancel Calendly event<br>- [ ] `rescheduleEvent($calendlyEventUri, $newDateTime)`: Reschedule event<br>- [ ] `verifyWebhookSignature($payload, $signature)`: Verify webhook authenticity<br>- [ ] API client with retry logic (exponential backoff)<br>- [ ] Error handling (Calendly API errors → booking failure)<br>- [ ] Mock interface for testing |
| `backend/app/Services/Notification/NotificationService.php` | Email/SMS notification service | - [ ] `sendBookingConfirmation($booking)`: Send confirmation email + SMS<br>- [ ] `sendBookingReminder($booking)`: Send reminder 24h before<br>- [ ] `sendCancellationNotification($booking)`: Send cancellation email + SMS<br>- [ ] Email templates: `emails.booking.confirmation`, `emails.booking.reminder`, `emails.booking.cancellation`<br>- [ ] SMS templates: `sms.booking.confirmation`, `sms.booking.reminder`<br>- [ ] Call `TwilioService` for SMS<br>- [ ] Queue jobs for async sending |
| `backend/app/Services/Integration/TwilioService.php` | Twilio SMS integration | - [ ] `sendSMS($to, $message)`: Send SMS via Twilio API<br>- [ ] Singapore phone number validation (+65 format)<br>- [ ] API client with retry logic<br>- [ ] Error handling (failed SMS → log, don't block booking)<br>- [ ] Mock interface for testing |
| `backend/app/Http/Controllers/Api/V1/BookingController.php` | Booking API controller | - [ ] `index(Request $request)`: List user's bookings (filterable by status, date range)<br>- [ ] `show($bookingNumber)`: Show single booking with center/service details<br>- [ ] `store(StoreBookingRequest $request)`: Create booking<br>- [ ] `update($id, UpdateBookingRequest $request)`: Update/reschedule booking<br>- [ ] `destroy($id, CancelBookingRequest $request)`: Cancel booking (requires reason)<br>- [ ] Authorization: Users can only access own bookings, admins can access all |
| `backend/app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php` | Calendly webhook handler | - [ ] `handle(Request $request)`: Process Calendly webhooks<br>- [ ] Verify webhook signature<br>- [ ] Handle events: `invitee.created`, `invitee.canceled`, `invitee.rescheduled`<br>- [ ] Update booking status based on webhook event<br>- [ ] Send notifications on status changes<br>- [ ] Return 200 to acknowledge webhook |
| `backend/app/Http/Requests/StoreBookingRequest.php` | Booking creation validation | - [ ] Required: `center_id`, `booking_date`, `booking_time`, `booking_type`<br>- [ ] Optional: `service_id`, `questionnaire_responses` (JSON)<br>- [ ] Date must be future date<br>- [ ] Time must be within center operating hours<br>- [ ] Validate questionnaire against schema |
| `backend/app/Http/Requests/CancelBookingRequest.php` | Booking cancellation validation | - [ ] Required: `cancellation_reason` (min 10 chars) |
| `backend/app/Http/Resources/BookingResource.php` | Booking API resource transformer | - [ ] Transform: id, booking_number, booking_date, booking_time, booking_type, status<br>- [ ] Include: center (basic), service (basic), calendly_cancel_url, calendly_reschedule_url<br>- [ ] Conditional: questionnaire_responses (user only), notes (admin only)<br>- [ ] Nested: center, service (when requested) |
| `backend/app/Jobs/SendBookingConfirmationJob.php` | Queue job for booking confirmation | - [ ] Call `NotificationService::sendBookingConfirmation()`<br>- [ ] Update `confirmation_sent_at` timestamp<br>- [ ] Retry 2 times on failure |
| `backend/app/Jobs/SendBookingReminderJob.php` | Queue job for booking reminders | - [ ] Call `NotificationService::sendBookingReminder()`<br>- [ ] Update `reminder_sent_at` timestamp<br>- [ ] Mark `sms_sent` as true |
| `backend/app/Console/Commands/SendBookingRemindersCommand.php` | Daily cron command for reminders | - [ ] Run daily at 9 AM SGT<br>- [ ] Find bookings 24 hours ahead with `reminder_sent_at` null<br>- [ ] Dispatch `SendBookingReminderJob` for each booking |

**Validation**:
- ✅ Booking creation calls Calendly API and stores event ID (mocked in tests, tested manually with real API)
- ✅ Confirmation email/SMS queued and sent after booking creation
- ✅ Booking cancellation updates status, cancels Calendly event, sends notification
- ✅ Calendly webhook handler processes events correctly and updates booking status
- ✅ Reminders sent 24 hours before bookings (cron command tested)

---

### 🔹 Workstream D: Advanced Features (Days 7-8)

**Objective**: Testimonial moderation, media management, translation workflow.

**Dependencies**: Workstream A/B models and services.

#### D.1 Testimonial Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Testimonial/TestimonialService.php` | Testimonial service | - [ ] `submit($userId, $centerId, $data)`: Create testimonial (pending status)<br>- [ ] `approve($testimonialId, $moderatorId)`: Approve testimonial<br>- [ ] `reject($testimonialId, $moderatorId, $reason)`: Reject testimonial<br>- [ ] `markAsSpam($testimonialId, $moderatorId)`: Flag as spam<br>- [ ] `getApprovedForCenter($centerId)`: Get approved testimonials for center<br>- [ ] `calculateAverageRating($centerId)`: Calculate center average rating<br>- [ ] Automatic audit logging |
| `backend/app/Http/Controllers/Api/V1/TestimonialController.php` | Testimonial API controller | - [ ] `index($centerId)`: List approved testimonials for center<br>- [ ] `store($centerId, StoreTestimonialRequest $request)`: Submit testimonial (authenticated users)<br>- [ ] Admin routes: `pending()`, `approve($id)`, `reject($id)`, `spam($id)` |
| `backend/app/Http/Requests/StoreTestimonialRequest.php` | Testimonial submission validation | - [ ] Required: `title`, `content`, `rating`<br>- [ ] Rating: integer, min 1, max 5<br>- [ ] Content: min 20 characters<br>- [ ] User can only submit one testimonial per center |
| `backend/app/Http/Resources/TestimonialResource.php` | Testimonial API resource transformer | - [ ] Transform: id, title, content, rating, created_at<br>- [ ] Include: user (name only, anonymize option), center (name)<br>- [ ] Conditional: moderation_notes (admin only) |

**Validation**:
- ✅ Users can submit testimonials, status is pending
- ✅ Admins can approve/reject/spam testimonials
- ✅ Only approved testimonials visible in public listing
- ✅ Average rating calculated correctly

---

#### D.2 Media Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Media/MediaService.php` | Media upload service | - [ ] `upload($file, $mediableType, $mediableId, $type)`: Upload to S3, create media record<br>- [ ] S3 path structure: `{mediableType}/{mediableId}/{type}/{filename}`<br>- [ ] Generate thumbnails for images (100x100, 300x300, 800x800)<br>- [ ] Store original + thumbnail URLs<br>- [ ] Extract image dimensions, file size, MIME type<br>- [ ] `delete($mediaId)`: Delete from S3 and database<br>- [ ] `reorder($mediableId, $orderArray)`: Update display order<br>- [ ] Queue job for image optimization (WebP conversion) |
| `backend/app/Services/Media/ImageOptimizationService.php` | Image optimization service | - [ ] Convert images to WebP format<br>- [ ] Compress images (quality 85%)<br>- [ ] Generate responsive sizes (300w, 600w, 1200w, 1920w)<br>- [ ] Store optimized versions in S3<br>- [ ] Update media record with optimized URLs |
| `backend/app/Http/Controllers/Api/V1/MediaController.php` | Media API controller | - [ ] `store(UploadMediaRequest $request)`: Upload media (admin only)<br>- [ ] `destroy($id)`: Delete media (admin only)<br>- [ ] `reorder(Request $request)`: Update display order (admin only) |
| `backend/app/Http/Requests/UploadMediaRequest.php` | Media upload validation | - [ ] Required: `file`, `mediable_type`, `mediable_id`, `type`<br>- [ ] File validation: max 10MB, allowed MIME types (image/jpeg, image/png, image/webp, video/mp4)<br>- [ ] Optional: `caption`, `alt_text` (required for images for accessibility) |
| `backend/app/Jobs/OptimizeImageJob.php` | Queue job for image optimization | - [ ] Call `ImageOptimizationService::optimize()`<br>- [ ] Update media record with optimized URLs<br>- [ ] Run after initial upload completes |

**Validation**:
- ✅ Image upload to S3 successful, media record created
- ✅ Thumbnails generated for images
- ✅ Image optimization job queued and processed (WebP conversion)
- ✅ Media deletion removes files from S3 and database
- ✅ Alt text enforced for images (accessibility)

---

#### D.3 Translation Management

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Services/Translation/TranslationService.php` | Translation management service | - [ ] `createTranslation($translatableType, $translatableId, $locale, $field, $value)`: Create translation<br>- [ ] `updateTranslation($translationId, $value)`: Update translation<br>- [ ] `markTranslated($translationId, $translatorId)`: Mark as translated<br>- [ ] `markReviewed($translationId, $reviewerId)`: Mark as reviewed<br>- [ ] `publish($translationId)`: Publish translation<br>- [ ] `getTranslations($translatableType, $translatableId, $locale)`: Get all translations for model<br>- [ ] `getPendingTranslations($locale)`: Get pending translations for a locale |
| `backend/app/Http/Controllers/Api/V1/TranslationController.php` | Translation API controller (admin only) | - [ ] `index(Request $request)`: List translations (filterable by locale, status)<br>- [ ] `store(StoreTranslationRequest $request)`: Create translation<br>- [ ] `update($id, UpdateTranslationRequest $request)`: Update translation<br>- [ ] `markTranslated($id)`: Mark as translated<br>- [ ] `markReviewed($id)`: Mark as reviewed<br>- [ ] `publish($id)`: Publish translation |
| `backend/app/Http/Requests/StoreTranslationRequest.php` | Translation creation validation | - [ ] Required: `translatable_type`, `translatable_id`, `locale`, `field`, `value`<br>- [ ] Locale: enum validation (en, zh, ms, ta)<br>- [ ] Unique constraint: type + id + locale + field |
| `backend/app/Http/Resources/TranslationResource.php` | Translation API resource transformer | - [ ] Transform: id, locale, field, value, translation_status, created_at<br>- [ ] Include: translator (name), reviewer (name), translatable (polymorphic) |

**Validation**:
- ✅ Translation CRUD operations work
- ✅ Translation workflow (draft → translated → reviewed → published) enforced
- ✅ Translators/reviewers tracked correctly
- ✅ Published translations accessible via API

---

### 🔹 Workstream E: API Documentation & Admin Features (Day 9)

**Objective**: Generate OpenAPI docs, create Postman collection, admin-specific endpoints.

**Dependencies**: All previous workstreams.

#### E.1 API Documentation

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/storage/api-docs/openapi.yaml` | OpenAPI 3.0 specification | - [ ] Complete endpoint documentation for all API routes<br>- [ ] Request/response schemas<br>- [ ] Authentication (Bearer token)<br>- [ ] Error response examples<br>- [ ] Tags: Auth, Users, Centers, Services, Bookings, Testimonials, FAQs, Contact, Subscriptions, Admin |
| `backend/app/Console/Commands/GenerateApiDocsCommand.php` | Command to generate OpenAPI from routes | - [ ] Parse routes, extract controllers/methods<br>- [ ] Generate OpenAPI YAML<br>- [ ] Run: `php artisan api:generate-docs` |
| `backend/postman/ElderCare_SG_API.postman_collection.json` | Postman collection | - [ ] All endpoints organized by folder<br>- [ ] Environment variables: `{{base_url}}`, `{{token}}`<br>- [ ] Example requests with sample data<br>- [ ] Pre-request scripts for token refresh |
| `backend/postman/ElderCare_SG_Local.postman_environment.json` | Postman environment for local dev | - [ ] `base_url`: `http://localhost:8000/api/v1`<br>- [ ] `token`: (to be filled after login) |

**Validation**:
- ✅ OpenAPI spec validates via Swagger Editor
- ✅ Postman collection imports successfully, all requests work
- ✅ Documentation accessible at `/api/documentation` (Swagger UI)

---

#### E.2 Admin-Specific Endpoints

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/app/Http/Controllers/Api/V1/Admin/UserController.php` | Admin user management | - [ ] `index(Request $request)`: List all users (paginated, filterable by role, status)<br>- [ ] `show($id)`: Show user with full details<br>- [ ] `update($id, Request $request)`: Update user (change role, verify email)<br>- [ ] `destroy($id)`: Delete user (soft delete)<br>- [ ] Authorization: super_admin only |
| `backend/app/Http/Controllers/Api/V1/Admin/DashboardController.php` | Admin dashboard statistics | - [ ] `index()`: Return key statistics<br>- [ ] Stats: total users, total centers, total bookings, pending testimonials, avg rating, revenue (if applicable)<br>- [ ] Date range filtering |
| `backend/app/Http/Controllers/Api/V1/Admin/ModerationController.php` | Content moderation | - [ ] `pendingTestimonials()`: List pending testimonials<br>- [ ] `pendingTranslations()`: List pending translations<br>- [ ] `contactSubmissions()`: List contact submissions (filterable by status) |

**Validation**:
- ✅ Admin endpoints accessible only to admin/super_admin roles
- ✅ Dashboard statistics accurate
- ✅ Moderation workflows functional

---

### 🔹 Workstream F: Testing & Quality Assurance (Days 10-11)

**Objective**: Achieve >90% test coverage, create factories/seeders, run integration tests.

**Dependencies**: All previous workstreams.

#### F.1 Unit Tests

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/tests/Unit/Models/UserTest.php` | User model tests | - [ ] Relationships: `profile()`, `bookings()`, `consents()`<br>- [ ] Accessors: `isAdmin()`<br>- [ ] Mutators: `password` bcrypt<br>- [ ] Soft delete behavior |
| `backend/tests/Unit/Models/CenterTest.php` | Center model tests | - [ ] Relationships: `services()`, `staff()`, `bookings()`<br>- [ ] Accessors: `occupancyRate()`, `averageRating()`, `isLicenseValid()`<br>- [ ] Scopes: `published()`, `validLicense()` |
| `backend/tests/Unit/Models/BookingTest.php` | Booking model tests | - [ ] Relationships: `user()`, `center()`, `service()`<br>- [ ] Methods: `confirm()`, `cancel()`<br>- [ ] Scopes: `upcoming()`, `byStatus()` |
| `backend/tests/Unit/Services/ConsentServiceTest.php` | Consent service tests | - [ ] `captureConsent()`: Creates consent record<br>- [ ] `withdrawConsent()`: Updates consent_given to false<br>- [ ] `checkConsent()`: Returns correct status |
| `backend/tests/Unit/Services/BookingServiceTest.php` | Booking service tests | - [ ] `create()`: Generates booking_number, calls Calendly mock<br>- [ ] `cancel()`: Updates status, calls Calendly cancel mock<br>- [ ] `sendReminders()`: Queues reminder jobs |
| *(Continue for all services)* | | |

**Validation**: Run `php artisan test --coverage --min=90`

---

#### F.2 Feature Tests (API Integration)

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/tests/Feature/Auth/RegistrationTest.php` | Registration flow tests | - [ ] Successful registration returns 201 with token<br>- [ ] Validation errors return 422<br>- [ ] Duplicate email returns 422<br>- [ ] Consent captured correctly |
| `backend/tests/Feature/Auth/LoginTest.php` | Login flow tests | - [ ] Successful login returns 200 with token<br>- [ ] Invalid credentials return 401<br>- [ ] Unverified email blocked (if middleware enabled) |
| `backend/tests/Feature/Center/CenterManagementTest.php` | Center CRUD tests | - [ ] Admin can create center<br>- [ ] User cannot create center (403)<br>- [ ] Center listing returns paginated results<br>- [ ] Center filtering by city works<br>- [ ] Center soft delete works |
| `backend/tests/Feature/Booking/BookingFlowTest.php` | Booking flow tests | - [ ] User can create booking<br>- [ ] Calendly API called (mocked)<br>- [ ] Confirmation email queued<br>- [ ] User can cancel booking<br>- [ ] Cancellation reason required |
| `backend/tests/Feature/Testimonial/TestimonialModerationTest.php` | Testimonial moderation tests | - [ ] User can submit testimonial (pending status)<br>- [ ] Admin can approve testimonial<br>- [ ] Approved testimonials visible in listing<br>- [ ] Pending testimonials not visible to users |
| *(Continue for all features)* | | |

**Validation**: Run `php artisan test --testsuite=Feature`

---

#### F.3 Factories & Seeders

| File Path | Description | Feature Checklist |
|-----------|-------------|-------------------|
| `backend/database/factories/UserFactory.php` | User factory | - [ ] Generate realistic user data<br>- [ ] Support states: `admin`, `verified`, `unverified` |
| `backend/database/factories/CenterFactory.php` | Center factory | - [ ] Generate centers with valid MOH license, operating hours JSON<br>- [ ] Support states: `published`, `draft` |
| `backend/database/factories/BookingFactory.php` | Booking factory | - [ ] Generate bookings with future dates<br>- [ ] Support states: `confirmed`, `pending`, `cancelled` |
| *(Continue for all models)* | | |
| `backend/database/seeders/DatabaseSeeder.php` | Master seeder | - [ ] Seed 10 users (1 super_admin, 2 admins, 7 users)<br>- [ ] Seed 5 centers with services, staff, media<br>- [ ] Seed 20 FAQs across categories<br>- [ ] Seed 10 bookings (mix of statuses)<br>- [ ] Seed 15 testimonials (mix of pending/approved) |
| `backend/database/seeders/DemoSeeder.php` | Demo data seeder | - [ ] Comprehensive demo data for stakeholder presentation<br>- [ ] Realistic center descriptions, photos<br>- [ ] Populated bookings for next 2 weeks<br>- [ ] Testimonials with ratings |

**Validation**: 
- ✅ Run `php artisan db:seed` successfully
- ✅ Demo data loads without errors
- ✅ Factories generate valid data (tested with `tinker`)

---

## 5. File Creation Summary Table

### Summary by Category

| Category | Files | Estimated Lines of Code |
|----------|-------|------------------------|
| **Models** | 14 | ~2,800 |
| **Services** | 15 | ~3,000 |
| **Controllers** | 20 | ~2,500 |
| **Requests** | 18 | ~900 |
| **Resources** | 10 | ~800 |
| **Middleware** | 5 | ~300 |
| **Jobs** | 6 | ~600 |
| **Observers** | 1 | ~150 |
| **Policies** | 5 | ~400 |
| **Tests (Unit)** | 20 | ~2,000 |
| **Tests (Feature)** | 15 | ~1,800 |
| **Factories** | 14 | ~1,400 |
| **Seeders** | 3 | ~600 |
| **API Docs** | 3 | ~1,500 |
| **Config/Routes** | 3 | ~400 |
| **Commands** | 2 | ~200 |
| **Total** | **154 files** | **~19,350 LOC** |

---

## 6. Testing Strategy & Coverage Targets

### Coverage Breakdown

| Layer | Target Coverage | Validation Method |
|-------|----------------|-------------------|
| **Models** | ≥95% | Unit tests for relationships, scopes, accessors, mutators |
| **Services** | ≥90% | Unit tests with mocked dependencies |
| **Controllers** | ≥85% | Feature tests with API requests |
| **Jobs/Commands** | ≥90% | Unit tests with queue faking |
| **Middleware** | ≥95% | Feature tests with different roles/permissions |
| **Overall** | ≥90% | PHPUnit coverage report |

### Test Execution Plan

```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# All tests with coverage
php artisan test --coverage --min=90

# Parallel execution (faster)
php artisan test --parallel

# Generate HTML coverage report
php artisan test --coverage-html coverage
```

---

## 7. External Integration Checklist

### API Credentials Required

| Service | Credentials Needed | Environment Variables | Testing Approach |
|---------|-------------------|----------------------|------------------|
| **Calendly** | API Key, Organization URI | `CALENDLY_API_KEY`, `CALENDLY_ORG_URI` | Mock in tests, manual test in staging |
| **Twilio** | Account SID, Auth Token, Phone Number | `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_PHONE` | Mock in tests, manual test with real number |
| **Mailchimp** | API Key, List ID | `MAILCHIMP_API_KEY`, `MAILCHIMP_LIST_ID` | Mock in tests, manual test with test list |
| **AWS S3** | Access Key, Secret, Bucket, Region | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_REGION` | Use local S3 mock (MinIO) for tests |

### Webhook Endpoints to Configure

| Service | Webhook URL | Events to Subscribe |
|---------|------------|---------------------|
| **Calendly** | `https://api.eldercare-sg.com/api/v1/webhooks/calendly` | `invitee.created`, `invitee.canceled` |
| **Mailchimp** | `https://api.eldercare-sg.com/api/v1/webhooks/mailchimp` | `unsubscribe`, `cleaned` |

---

## 8. Deployment Readiness Checklist

### Pre-Deploy Validation

- [ ] All migrations run successfully: `php artisan migrate --force`
- [ ] All tests pass: `php artisan test`
- [ ] Code coverage ≥90%
- [ ] Seeders populate demo data: `php artisan db:seed --class=DemoSeeder`
- [ ] API documentation generated: `php artisan api:generate-docs`
- [ ] Environment variables configured in staging `.env`
- [ ] External API credentials valid (test with health check endpoint)
- [ ] Queue workers configured in ECS
- [ ] Cron jobs scheduled (booking reminders, license expiry alerts)
- [ ] Error tracking active (Sentry DSN configured)
- [ ] Rate limiting tested (429 responses return correctly)
- [ ] CORS configured for frontend domain

### Smoke Test Endpoints (Manual)

```bash
# Health check
GET /api/health

# Public endpoints
GET /api/v1/centers
GET /api/v1/faqs

# Authentication flow
POST /api/v1/auth/register
POST /api/v1/auth/login
GET /api/v1/user (with Bearer token)

# Booking flow
POST /api/v1/bookings (authenticated)
GET /api/v1/bookings/:id

# Admin endpoints
GET /api/v1/admin/dashboard (admin token)
```

---

## 9. Risk Mitigation & Contingencies

| Risk | Probability | Impact | Mitigation | Contingency |
|------|------------|--------|------------|-------------|
| **External API failure** (Calendly, Twilio) | Medium | High | Retry logic, circuit breaker pattern, fallback to manual process | Allow bookings without Calendly sync, queue for retry |
| **S3 upload failure** | Low | Medium | Retry with exponential backoff, store locally temporarily | Use local storage as fallback, sync to S3 later |
| **Queue worker failure** | Low | High | Monitor queue depth, auto-scale workers, dead letter queue | Manual job retry via CLI, alert on-call |
| **Test coverage gap** | Medium | Medium | Enforce coverage checks in CI, block PR if <90% | Identify critical paths, prioritize coverage there |
| **Database migration failure** | Low | High | Test migrations in staging first, backup before deploy | Rollback migration, restore from backup |
| **PDPA compliance gap** | Low | Critical | Legal review of consent flows, audit trail verification | Halt deployment, address gap immediately |

---

## 10. Success Metrics & Demo Preparation

### Stakeholder Demo Script

**Duration**: 15 minutes

**Flow**:
1. **User Journey** (5 mins)
   - Register new account → Email verification → Login
   - Browse centers → View center details with media gallery
   - Create booking → Receive confirmation email (show in Mailtrap)
   - View booking history → Cancel booking
   
2. **Admin Features** (5 mins)
   - Admin dashboard with statistics
   - Moderate pending testimonial → Approve
   - View contact submissions → Mark as resolved
   - Add new center with MOH license validation
   
3. **PDPA Compliance** (3 mins)
   - User consent dashboard → View consent history
   - Download personal data (JSON export)
   - Request account deletion → Show soft delete with grace period
   
4. **API Documentation** (2 mins)
   - Show Swagger UI with interactive API docs
   - Execute live API call from Postman

### Demo Data Requirements

- **5 Centers**: Realistic names, addresses, photos, operating hours
- **15 Services**: Varied pricing, features
- **10 Bookings**: Mix of upcoming, completed, cancelled
- **20 Testimonials**: 15 approved (varied ratings), 5 pending
- **10 Users**: Mix of regular users and admins
- **30 FAQs**: Covering all categories

**Preparation Command**:
```bash
php artisan db:seed --class=DemoSeeder
```

---

## 11. Handoff to Independent Coding Agent

### Execution Instructions

**Prerequisites**:
1. Environment setup complete (Docker, `.env` configured)
2. Database migrations run (`php artisan migrate`)
3. External API credentials available (or mocked)

**Recommended Execution Order**:

#### Week 1 (Days 1-5):
**Day 1**: Workstream A.1 (Models) + A.4 (API Infrastructure)
- Create all 14 Eloquent models with relationships
- Setup API response formatter, exception handler
- **Validation**: Models load in `tinker`, relationships work

**Day 2**: Workstream A.2 (PDPA Services) + A.3 (Auth Controllers)
- Implement consent/audit services
- Build auth controllers (register, login, logout, password reset)
- **Validation**: Registration + login work via Postman, consent captured

**Day 3**: Workstream B.1 (Center/Service Management)
- Implement center/service services and controllers
- **Validation**: Centers CRUD works, MOH license validation enforced

**Day 4**: Workstream B.2 (FAQ, Contact, Newsletter)
- Implement FAQ, contact, Mailchimp services
- **Validation**: Contact form submission works, Mailchimp sync queued

**Day 5**: Workstream C.1 (Booking System part 1)
- Implement booking service, Calendly integration (mocked)
- **Validation**: Booking creation works, Calendly mock called

#### Week 2 (Days 6-11):
**Day 6**: Workstream C.1 (Booking System part 2)
- Complete notification service, Twilio integration
- Implement webhook handlers
- **Validation**: Confirmation email/SMS sent, webhooks processed

**Day 7**: Workstream D.1 (Testimonials) + D.3 (Translations)
- Implement testimonial moderation
- Build translation service
- **Validation**: Testimonial approval works, translations manageable

**Day 8**: Workstream D.2 (Media Management)
- Implement media upload to S3, image optimization
- **Validation**: Image upload works, thumbnails generated

**Day 9**: Workstream E (API Docs + Admin Features)
- Generate OpenAPI spec, Postman collection
- Build admin endpoints
- **Validation**: Swagger UI accessible, admin dashboard returns stats

**Day 10**: Workstream F.1 + F.2 (Unit + Feature Tests)
- Write unit tests for models, services
- Write feature tests for all API endpoints
- **Validation**: `php artisan test` passes with >90% coverage

**Day 11**: Workstream F.3 (Factories/Seeders) + Final QA
- Create factories for all models
- Build comprehensive demo seeder
- Run smoke tests, fix bugs
- **Validation**: Demo data loads successfully, all endpoints work

### Daily Validation Checklist

At the end of each day, run:
```bash
# 1. Code quality
./vendor/bin/phpcs --standard=PSR12 app/
./vendor/bin/phpstan analyse

# 2. Tests
php artisan test

# 3. Build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache

# 4. Manual smoke test
# (Use Postman collection for relevant endpoints)
```

### Troubleshooting Guide

| Issue | Solution |
|-------|----------|
| Migration fails | Check database connection, rollback and re-run |
| Test fails | Check factory data, ensure database is seeded correctly |
| Queue job not processing | Verify queue worker is running: `php artisan queue:work` |
| External API error | Check credentials, verify mock is active in tests |
| Coverage <90% | Identify untested files: `php artisan test --coverage` |

---

## 12. Post-Phase 3 Next Steps

**Immediate** (Phase 4):
- Frontend integration with backend API
- React Query setup for API calls
- Zustand store integration with user session

**Short-term** (Phase 5):
- Laravel Nova installation for admin panel
- Advanced media management (Cloudflare Stream for videos)
- MeiliSearch integration for full-text search

**Medium-term** (Phase 6-7):
- Content population with real data
- Professional translation to Mandarin/Malay/Tamil
- Advanced features (virtual tours, subsidy calculator)

---

## 13. Final Approval & Sign-off

**This plan is ready for execution when**:
- [ ] Stakeholder review completed
- [ ] External API credentials secured (or mock strategy approved)
- [ ] Infrastructure readiness confirmed (staging environment, S3 bucket, queue workers)
- [ ] Independent coding agent has reviewed plan and confirmed understanding
- [ ] Demo date and success criteria agreed upon

**Plan Author**: AI Coding Agent (Cascade)  
**Plan Version**: 1.0  
**Last Updated**: 2025-10-10  
**Status**: ✅ APPROVED FOR EXECUTION

---

**🎯 This comprehensive sub-plan provides everything an independent coding agent needs to complete Phase 3 backend development without ambiguity. Let's build an impressive production demo!** 🚀
