Phase 3 Backend Execution Sub-Plan — Core Services & PDPA Compliance
Strategic Priority: Complete backend implementation to enable production demo for stakeholders.

Alignment:

codebase_completion_master_plan.md — Phase 3 objectives
docs/AGENT.md — Backend blueprint (§4), PDPA compliance (§7), testing standards (§10)
Project_Architecture_Document.md — Service-layer architecture, PDPA workflows, API design
Database schema — 18 tables, 15 foreign keys, PDPA/MOH compliance requirements
1. Executive Summary
1.1 Objectives
Implement complete backend API for ElderCare SG platform covering authentication, center management, booking system, PDPA compliance, and external integrations.
Deliver production-ready codebase with >90% test coverage, comprehensive documentation, and stakeholder demo readiness.
Establish service-oriented architecture with thin controllers, dedicated service classes, repository pattern, and robust error handling.
1.2 Success Criteria
✅ Authentication: Users can register (with consent), login/logout, verify email, reset password via API
✅ PDPA Compliance: Consent tracking, audit logging, data export, account deletion with 30-day grace period
✅ Center Management: Admins can CRUD centers/services/staff via API with MOH validation
✅ Booking System: Users can create bookings, receive confirmations (email/SMS), view history
✅ Content Management: FAQs, testimonials (with moderation), contact submissions, newsletter subscriptions
✅ API Quality: OpenAPI documentation, rate limiting, consistent error handling, versioned endpoints (/api/v1/)
✅ Testing: >90% backend coverage (PHPUnit), all endpoints integration tested, external service mocks
1.3 Dependencies & Assumptions
Phase 1 complete: Docker environment, database migrations exist, CI/CD pipeline operational
Phase 2 complete: Frontend design system ready to consume APIs (parallel development acceptable)
External service accounts: Calendly, Twilio, Mailchimp, AWS S3 credentials available (or mocked for testing)
Team: 2 backend developers (Backend Dev 1 + Backend Dev 2) can parallelize workstreams
Timeline: 12-14 days with parallelization, 19-26 days sequential
2. Architecture Blueprint
2.1 Service-Layer Pattern
text

HTTP Request → Route → Controller → Request (validation) → Service (business logic) → Repository (data access) → Model → Database
                ↓                                              ↓
              Middleware                                   Events/Jobs (audit, notifications)
                                                              ↓
                                                         Resource (response transform)
2.2 Directory Structure
text

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
2.3 Key Design Decisions
Thin Controllers: Max 5-7 lines per method (delegate to services)
Service Layer: All business logic, exception handling, transaction management
Repository Pattern: Database abstraction, testable data access
Event-Driven Audit: Model changes trigger events → listeners log to audit_logs
Queue-Based Notifications: Email/SMS sent via jobs (retry 3x, exponential backoff)
Polymorphic Relationships: media and content_translations use Eloquent polymorphism
API Versioning: All routes prefixed /api/v1/, future versions /api/v2/
Response Format: Consistent JSON structure (data/meta/errors)
3. Workstream Breakdown
Workstream A: Foundation & Authentication (3-4 days)
Owner: Backend Dev 1
Dependencies: None (Phase 1 migrations exist)
Priority: CRITICAL (blocks other workstreams)

Deliverables:

User authentication system (register, login, logout, verify email, password reset)
Laravel Sanctum API token authentication
Role-based authorization (user, admin, super_admin)
Consent capture during registration
Profile management API
File Count: 28 files (models, controllers, requests, services, tests)

Workstream B: PDPA Compliance Core (2-3 days)
Owner: Backend Dev 2
Dependencies: Workstream A (user authentication)
Priority: CRITICAL (legal requirement)

Deliverables:

Consent management system (capture, versioning, tracking)
Audit logging middleware (automatic tracking of all model changes)
Data export API (user downloads all their data as JSON)
Account deletion workflow (soft delete → 30-day grace → hard delete job)
PDPA middleware (consent gates for sensitive operations)
File Count: 18 files (models, services, jobs, middleware, tests)

Workstream C: Center & Service Management (3-4 days)
Owner: Backend Dev 1
Dependencies: Workstream A (authentication for admin access)
Priority: HIGH (demo requirement)

Deliverables:

Center CRUD API with MOH validation
Service CRUD API (linked to centers)
Staff CRUD API (linked to centers)
Media upload & management (AWS S3 integration)
Content translation API (multilingual support)
Search API (MeiliSearch integration)
File Count: 35 files (models, controllers, services, repositories, tests)

Workstream D: Booking System (4-5 days)
Owner: Backend Dev 2
Dependencies: Workstream C (centers/services must exist)
Priority: HIGH (demo requirement)

Deliverables:

Booking creation API with Calendly integration
Booking workflow (pending → confirmed → completed/cancelled)
Notification system (email + SMS via Twilio)
Reminder job (24h before booking)
Booking history API
Calendly webhook handler (event updates)
File Count: 32 files (models, controllers, services, jobs, listeners, tests)

Workstream E: Content & Community (2-3 days)
Owner: Backend Dev 1
Dependencies: Workstream A (authentication)
Priority: MEDIUM

Deliverables:

FAQ CRUD API
Testimonial submission & moderation API
Contact form submission API (spam detection)
Newsletter subscription API (Mailchimp integration)
File Count: 24 files (models, controllers, services, tests)

Workstream F: API Infrastructure (2-3 days)
Owner: Backend Dev 2
Dependencies: All workstreams (wraps existing APIs)
Priority: MEDIUM

Deliverables:

API versioning structure (/api/v1/)
Response formatting middleware
Global error handling
Rate limiting middleware (60 req/min per user)
OpenAPI/Swagger documentation
API resource transformers
File Count: 15 files (middleware, resources, documentation, tests)

Workstream G: Testing & Quality Assurance (3-4 days)
Owner: Both Backend Devs (parallel)
Dependencies: All workstreams
Priority: CRITICAL (demo readiness)

Deliverables:

Unit tests for all models (relationships, scopes, accessors)
Feature tests for all API endpoints (CRUD operations)
Integration tests for external services (mocked Calendly, Twilio, Mailchimp)
Database factories for all models
Database seeders (demo data)
API documentation review
File Count: 40+ test files

4. Parallelization Strategy (12-14 Days)
Days	Backend Dev 1	Backend Dev 2	Deliverable Milestones
1-3	Workstream A: Auth & User Management	Workstream C: Center Models & Repositories	Auth API functional, Center models ready
4-6	Workstream C: Center CRUD APIs	Workstream B: PDPA Compliance	Center management demo-ready, PDPA systems active
7-10	Workstream E: Content & Community	Workstream D: Booking System	FAQ/Testimonials live, Booking flow complete
11-12	Workstream G: Testing (Auth, Centers, Content)	Workstream F: API Infrastructure	API docs published, rate limiting active
13-14	Workstream G: Final QA & Bug Fixes	Workstream G: Integration Tests & Demo Data	>90% coverage, demo database seeded
5. Comprehensive File Creation Matrix
5.1 Workstream A: Foundation & Authentication
Models (4 files)
File Path	Description	Feature Checklist
app/Models/User.php	User model with authentication, soft deletes, PDPA compliance	- [ ] Sanctum HasApiTokens trait<br>- [ ] Soft deletes<br>- [ ] HasFactory trait<br>- [ ] Relationships: profile(), consents(), bookings(), testimonials()<br>- [ ] Scopes: active(), admins()<br>- [ ] Accessors: getPreferredLanguageNameAttribute()<br>- [ ] Password hashing mutator
app/Models/Profile.php	Extended user profile information	- [ ] Belongs to User<br>- [ ] HasFactory trait<br>- [ ] Fillable attributes<br>- [ ] Date casting for birth_date<br>- [ ] Accessor for full address
app/Models/Consent.php	PDPA consent tracking model	- [ ] Belongs to User<br>- [ ] HasFactory trait<br>- [ ] Fillable attributes<br>- [ ] Scope: active() (consent_given = true)<br>- [ ] Scope: byType(string $type)
app/Models/PasswordResetToken.php	Password reset token model	- [ ] No timestamps<br>- [ ] Primary key: email<br>- [ ] Token hashing
Controllers (6 files)
File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/Auth/RegisterController.php	User registration with consent capture	- [ ] register(RegisterRequest $request): Create user + profile + consent<br>- [ ] Return user + token<br>- [ ] Trigger email verification<br>- [ ] Max 7 lines (delegate to AuthService)
app/Http/Controllers/Api/V1/Auth/LoginController.php	User login with Sanctum token	- [ ] login(LoginRequest $request): Validate credentials<br>- [ ] Create Sanctum token<br>- [ ] Return user + token<br>- [ ] Log audit event
app/Http/Controllers/Api/V1/Auth/LogoutController.php	Revoke current Sanctum token	- [ ] logout(Request $request): Revoke token<br>- [ ] Return success message
app/Http/Controllers/Api/V1/Auth/EmailVerificationController.php	Email verification handling	- [ ] verify(EmailVerificationRequest $request): Mark email as verified<br>- [ ] resend(Request $request): Resend verification email
app/Http/Controllers/Api/V1/Auth/PasswordResetController.php	Password reset workflow	- [ ] forgotPassword(ForgotPasswordRequest $request): Send reset link<br>- [ ] reset(ResetPasswordRequest $request): Update password<br>- [ ] Invalidate all tokens
app/Http/Controllers/Api/V1/ProfileController.php	User profile management	- [ ] show(Request $request): Get authenticated user's profile<br>- [ ] update(UpdateProfileRequest $request): Update profile<br>- [ ] uploadAvatar(UploadAvatarRequest $request): Upload avatar to S3
Requests (Validation) (7 files)
File Path	Description	Feature Checklist
app/Http/Requests/Auth/RegisterRequest.php	Registration validation	- [ ] Validate: name, email (unique), password (min 8 chars), phone (Singapore format)<br>- [ ] Validate consent checkboxes (account, marketing)<br>- [ ] Custom error messages
app/Http/Requests/Auth/LoginRequest.php	Login validation	- [ ] Validate: email (exists), password<br>- [ ] Custom error messages
app/Http/Requests/Auth/EmailVerificationRequest.php	Email verification validation	- [ ] Validate signature, expiration
app/Http/Requests/Auth/ForgotPasswordRequest.php	Password reset request validation	- [ ] Validate: email (exists)
app/Http/Requests/Auth/ResetPasswordRequest.php	Password reset validation	- [ ] Validate: token, email, password (confirmed, min 8 chars)
app/Http/Requests/Profile/UpdateProfileRequest.php	Profile update validation	- [ ] Validate: name, phone, bio, birth_date, address, city, postal_code<br>- [ ] Optional fields<br>- [ ] Singapore postal code format (6 digits)
app/Http/Requests/Profile/UploadAvatarRequest.php	Avatar upload validation	- [ ] Validate: image (jpg/png/webp), max 2MB<br>- [ ] Dimensions: max 2000x2000
Services (3 files)
File Path	Description	Feature Checklist
app/Services/Auth/AuthService.php	Authentication business logic	- [ ] register(array $data): Create user + profile + consent + send verification email<br>- [ ] login(array $credentials): Authenticate + create token<br>- [ ] logout(User $user): Revoke tokens<br>- [ ] verifyEmail(User $user): Mark email verified<br>- [ ] sendPasswordResetLink(string $email): Create reset token + send email<br>- [ ] resetPassword(array $data): Update password + invalidate tokens<br>- [ ] Exception handling
app/Services/Auth/ProfileService.php	Profile management logic	- [ ] getProfile(User $user): Get profile with relationships<br>- [ ] updateProfile(User $user, array $data): Update profile<br>- [ ] uploadAvatar(User $user, UploadedFile $file): Upload to S3 + update profile<br>- [ ] deleteAvatar(User $user): Delete from S3 + clear profile
app/Services/Auth/ConsentService.php	Consent management logic	- [ ] captureConsent(User $user, string $type, bool $given, string $version, array $meta): Create consent record<br>- [ ] getActiveConsents(User $user): Get all active consents<br>- [ ] withdrawConsent(User $user, string $type): Mark consent withdrawn<br>- [ ] hasConsent(User $user, string $type): Check if user has active consent
Resources (API Transformers) (3 files)
File Path	Description	Feature Checklist
app/Http/Resources/UserResource.php	User API response transformer	- [ ] Transform: id, name, email, phone, role, preferred_language, email_verified_at<br>- [ ] Include profile (when loaded)<br>- [ ] Exclude sensitive fields (password, remember_token)
app/Http/Resources/ProfileResource.php	Profile API response transformer	- [ ] Transform: all profile fields<br>- [ ] Include avatar URL (S3 presigned if needed)
app/Http/Resources/ConsentResource.php	Consent API response transformer	- [ ] Transform: id, consent_type, consent_given, consent_version, created_at
Middleware (2 files)
File Path	Description	Feature Checklist
app/Http/Middleware/EnsureEmailIsVerified.php	Require email verification	- [ ] Check if user's email_verified_at is not null<br>- [ ] Return 403 error if not verified
app/Http/Middleware/CheckRole.php	Role-based authorization	- [ ] handle($request, Closure $next, ...$roles): Check user role<br>- [ ] Return 403 if role not allowed
Routes (1 file)
File Path	Description	Feature Checklist
routes/api.php (AUTH section)	Authentication routes	- [ ] POST /api/v1/auth/register<br>- [ ] POST /api/v1/auth/login<br>- [ ] POST /api/v1/auth/logout (protected)<br>- [ ] POST /api/v1/auth/email/verify/{id}/{hash} (signed)<br>- [ ] POST /api/v1/auth/email/resend (protected)<br>- [ ] POST /api/v1/auth/password/forgot<br>- [ ] POST /api/v1/auth/password/reset<br>- [ ] GET /api/v1/profile (protected)<br>- [ ] PUT /api/v1/profile (protected)<br>- [ ] POST /api/v1/profile/avatar (protected)<br>- [ ] DELETE /api/v1/profile/avatar (protected)
Tests (2 files minimum)
File Path	Description	Feature Checklist
tests/Feature/Auth/AuthenticationTest.php	Authentication flow tests	- [ ] Test: user can register with valid data<br>- [ ] Test: registration requires consent<br>- [ ] Test: user can login with correct credentials<br>- [ ] Test: user cannot login with wrong password<br>- [ ] Test: user can logout<br>- [ ] Test: user can verify email<br>- [ ] Test: user can request password reset<br>- [ ] Test: user can reset password
tests/Feature/Profile/ProfileTest.php	Profile management tests	- [ ] Test: user can view own profile<br>- [ ] Test: user can update profile<br>- [ ] Test: user can upload avatar<br>- [ ] Test: user can delete avatar<br>- [ ] Test: guest cannot access profile
5.2 Workstream B: PDPA Compliance Core
Models (2 files)
File Path	Description	Feature Checklist
app/Models/AuditLog.php	Audit log model for PDPA compliance	- [ ] Belongs to User (nullable)<br>- [ ] HasFactory trait<br>- [ ] Cast old_values, new_values as JSON<br>- [ ] Scope: byUser(User $user)<br>- [ ] Scope: byModel(string $type, int $id)<br>- [ ] Read-only (no updates/deletes after creation)
app/Models/Consent.php	Already created in Workstream A	(Reference only)
Services (4 files)
File Path	Description	Feature Checklist
app/Services/PDPA/AuditService.php	Audit logging service	- [ ] logCreate(Model $model, User $user = null): Log creation<br>- [ ] logUpdate(Model $model, array $oldValues, array $newValues, User $user = null): Log update<br>- [ ] logDelete(Model $model, User $user = null): Log deletion<br>- [ ] getUserAuditLogs(User $user): Get all audit logs for user<br>- [ ] Capture IP, user agent, URL from request
app/Services/PDPA/DataExportService.php	Data export service	- [ ] exportUserData(User $user): Export all user data as JSON<br>- [ ] Include: user, profile, consents, bookings, testimonials, contact_submissions<br>- [ ] Exclude: password, remember_token, tokens<br>- [ ] Return downloadable JSON file<br>- [ ] Log export event in audit_logs
app/Services/PDPA/AccountDeletionService.php	Account deletion service	- [ ] requestDeletion(User $user): Soft delete user (30-day grace)<br>- [ ] cancelDeletion(User $user): Restore soft-deleted user<br>- [ ] permanentlyDelete(User $user): Hard delete after 30 days<br>- [ ] Cascade: delete profile, consents, audit_logs<br>- [ ] Anonymize: bookings (keep records but remove PII)<br>- [ ] Log all deletion actions
app/Services/Auth/ConsentService.php	Already created in Workstream A	(Reference only)
Jobs (2 files)
File Path	Description	Feature Checklist
app/Jobs/PDPA/PermanentlyDeleteUserJob.php	Hard delete user after 30-day grace period	- [ ] Check if deleted_at > 30 days ago<br>- [ ] Call AccountDeletionService::permanentlyDelete()<br>- [ ] Handle failure gracefully<br>- [ ] Retry 3 times
app/Jobs/PDPA/AnonymizeUserDataJob.php	Anonymize user data in related tables	- [ ] Replace PII with anonymized placeholders<br>- [ ] Update: bookings (questionnaire_responses), contact_submissions<br>- [ ] Preserve statistical data<br>- [ ] Log anonymization
Middleware (2 files)
File Path	Description	Feature Checklist
app/Http/Middleware/LogAuditTrail.php	Automatic audit logging for model changes	- [ ] Listen to model events: created, updated, deleted<br>- [ ] Call AuditService to log changes<br>- [ ] Capture old/new values from model dirty attributes<br>- [ ] Skip for AuditLog model (prevent recursion)
app/Http/Middleware/RequireConsent.php	Require specific consent before proceeding	- [ ] handle($request, Closure $next, string $consentType): Check if user has active consent<br>- [ ] Return 403 if consent not given<br>- [ ] Skip for admins
Listeners (3 files)
File Path	Description	Feature Checklist
app/Listeners/LogModelCreated.php	Log model creation events	- [ ] Listen to Illuminate\Database\Events\ModelCreated<br>- [ ] Call AuditService::logCreate()
app/Listeners/LogModelUpdated.php	Log model update events	- [ ] Listen to Illuminate\Database\Events\ModelUpdated<br>- [ ] Call AuditService::logUpdate()
app/Listeners/LogModelDeleted.php	Log model deletion events	- [ ] Listen to Illuminate\Database\Events\ModelDeleted<br>- [ ] Call AuditService::logDelete()
Controllers (2 files)
File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/PDPA/DataExportController.php	Data export API	- [ ] export(Request $request): Download user's data as JSON<br>- [ ] Authenticate user<br>- [ ] Call DataExportService::exportUserData()<br>- [ ] Return JSON download response
app/Http/Controllers/Api/V1/PDPA/AccountDeletionController.php	Account deletion API	- [ ] requestDeletion(Request $request): Soft delete user<br>- [ ] cancelDeletion(Request $request): Cancel deletion (restore user)<br>- [ ] Authenticate user<br>- [ ] Call AccountDeletionService
Routes (1 file)
File Path	Description	Feature Checklist
routes/api.php (PDPA section)	PDPA compliance routes	- [ ] GET /api/v1/pdpa/data/export (protected)<br>- [ ] POST /api/v1/pdpa/account/delete (protected)<br>- [ ] POST /api/v1/pdpa/account/cancel-deletion (protected)<br>- [ ] GET /api/v1/pdpa/consents (protected)<br>- [ ] PUT /api/v1/pdpa/consents/{type} (protected)
Tests (2 files)
File Path	Description	Feature Checklist
tests/Feature/PDPA/DataExportTest.php	Data export tests	- [ ] Test: user can export their data<br>- [ ] Test: exported data includes all user records<br>- [ ] Test: exported data excludes sensitive fields<br>- [ ] Test: export is logged in audit_logs
tests/Feature/PDPA/AccountDeletionTest.php	Account deletion tests	- [ ] Test: user can request account deletion<br>- [ ] Test: deleted user is soft deleted<br>- [ ] Test: user can cancel deletion within 30 days<br>- [ ] Test: user is permanently deleted after 30 days<br>- [ ] Test: related data is anonymized
5.3 Workstream C: Center & Service Management
Models (6 files)
File Path	Description	Feature Checklist
app/Models/Center.php	Elderly care center model	- [ ] HasFactory, soft deletes<br>- [ ] Relationships: services(), staff(), media(), translations(), bookings(), testimonials()<br>- [ ] Casts: operating_hours, medical_facilities, amenities, transport_info, languages_supported, government_subsidies as JSON<br>- [ ] Scopes: published(), byCity(string $city), search(string $query)<br>- [ ] Accessors: getOccupancyRateAttribute(), getAverageRatingAttribute()<br>- [ ] Mutators: setSlugAttribute()<br>- [ ] Validation: MOH license format
app/Models/Service.php	Center service/program model	- [ ] Belongs to Center<br>- [ ] HasFactory, soft deletes<br>- [ ] Relationships: center(), bookings(), media(), translations()<br>- [ ] Casts: features as JSON<br>- [ ] Scopes: published(), byCenter(Center $center)<br>- [ ] Mutators: setSlugAttribute()
app/Models/Staff.php	Center staff member model	- [ ] Belongs to Center<br>- [ ] HasFactory<br>- [ ] Casts: qualifications as JSON<br>- [ ] Scopes: active(), byCenter(Center $center)<br>- [ ] Accessors: getDisplayNameAttribute()
app/Models/Media.php	Polymorphic media model	- [ ] MorphTo relationship: mediable()<br>- [ ] HasFactory<br>- [ ] Scopes: images(), videos(), byType(string $type)<br>- [ ] Accessors: getUrlAttribute() (S3 presigned if needed)<br>- [ ] Mutators: Generate thumbnails for videos
app/Models/ContentTranslation.php	Polymorphic translation model	- [ ] MorphTo relationship: translatable()<br>- [ ] Belongs to User (translator, reviewer)<br>- [ ] HasFactory<br>- [ ] Scopes: byLocale(string $locale), published(), pending()<br>- [ ] Unique index: (translatable_type, translatable_id, locale, field)
app/Models/FAQ.php	FAQ model	- [ ] HasFactory<br>- [ ] Relationships: translations()<br>- [ ] Scopes: published(), byCategory(string $category), ordered()<br>- [ ] Accessors: getTranslatedQuestion(string $locale), getTranslatedAnswer(string $locale)
Repositories (4 files)
File Path	Description	Feature Checklist
app/Repositories/CenterRepository.php	Center data access layer	- [ ] all(array $filters = []): Paginated list with filters (city, status, search)<br>- [ ] findById(int $id): Find by ID with relationships<br>- [ ] findBySlug(string $slug): Find by slug<br>- [ ] create(array $data): Create center<br>- [ ] update(Center $center, array $data): Update center<br>- [ ] delete(Center $center): Soft delete center<br>- [ ] search(string $query): Full-text search + MeiliSearch integration
app/Repositories/ServiceRepository.php	Service data access layer	- [ ] all(array $filters = []): List services (filter by center)<br>- [ ] findById(int $id): Find by ID<br>- [ ] create(array $data): Create service<br>- [ ] update(Service $service, array $data): Update service<br>- [ ] delete(Service $service): Soft delete service
app/Repositories/StaffRepository.php	Staff data access layer	- [ ] all(array $filters = []): List staff (filter by center)<br>- [ ] findById(int $id): Find by ID<br>- [ ] create(array $data): Create staff member<br>- [ ] update(Staff $staff, array $data): Update staff<br>- [ ] delete(Staff $staff): Delete staff
app/Repositories/MediaRepository.php	Media data access layer	- [ ] all(array $filters = []): List media (filter by mediable)<br>- [ ] findById(int $id): Find by ID<br>- [ ] create(array $data): Create media record<br>- [ ] update(Media $media, array $data): Update media<br>- [ ] delete(Media $media): Delete media + S3 file
Services (5 files)
File Path	Description	Feature Checklist
app/Services/Center/CenterService.php	Center management logic	- [ ] getAllCenters(array $filters): Get paginated centers<br>- [ ] getCenterById(int $id): Get center with services/staff/media<br>- [ ] getCenterBySlug(string $slug): Get center for public view<br>- [ ] createCenter(array $data): Create center with MOH validation<br>- [ ] updateCenter(Center $center, array $data): Update center<br>- [ ] deleteCenter(Center $center): Soft delete center<br>- [ ] searchCenters(string $query): Search with MeiliSearch<br>- [ ] syncToSearchIndex(Center $center): Sync to MeiliSearch
app/Services/Center/ServiceManagementService.php	Service CRUD logic	- [ ] getServicesByCenterId(int $centerId): Get all services for a center<br>- [ ] createService(Center $center, array $data): Create service<br>- [ ] updateService(Service $service, array $data): Update service<br>- [ ] deleteService(Service $service): Soft delete service
app/Services/Center/StaffService.php	Staff management logic	- [ ] getStaffByCenterId(int $centerId): Get all staff for a center<br>- [ ] createStaff(Center $center, array $data): Create staff with photo upload<br>- [ ] updateStaff(Staff $staff, array $data): Update staff<br>- [ ] deleteStaff(Staff $staff): Delete staff
app/Services/Media/MediaService.php	Media upload & management	- [ ] uploadMedia(Model $model, UploadedFile $file, array $meta): Upload to S3 + create record<br>- [ ] getMediaForModel(Model $model, string $type = null): Get all media for a model<br>- [ ] updateMedia(Media $media, array $data): Update caption/alt_text<br>- [ ] deleteMedia(Media $media): Delete from S3 + database<br>- [ ] reorderMedia(Model $model, array $order): Update display_order
app/Services/Translation/TranslationService.php	Multilingual content management	- [ ] getTranslation(Model $model, string $field, string $locale): Get translated content<br>- [ ] createTranslation(Model $model, string $field, string $locale, string $value): Create translation<br>- [ ] updateTranslation(ContentTranslation $translation, array $data): Update translation<br>- [ ] publishTranslation(ContentTranslation $translation): Mark as published<br>- [ ] getPendingTranslations(): Get all pending translations for review
Controllers (6 files)
File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/CenterController.php	Center CRUD API	- [ ] index(Request $request): List centers (filters: city, status, search)<br>- [ ] show(string $slugOrId): Get center details<br>- [ ] store(StoreCenterRequest $request): Create center (admin only)<br>- [ ] update(UpdateCenterRequest $request, Center $center): Update center (admin only)<br>- [ ] destroy(Center $center): Delete center (admin only)
app/Http/Controllers/Api/V1/Center/ServiceController.php	Service CRUD API	- [ ] index(Center $center): List services for a center<br>- [ ] show(Center $center, Service $service): Get service details<br>- [ ] store(StoreServiceRequest $request, Center $center): Create service (admin only)<br>- [ ] update(UpdateServiceRequest $request, Service $service): Update service (admin only)<br>- [ ] destroy(Service $service): Delete service (admin only)
app/Http/Controllers/Api/V1/Center/StaffController.php	Staff CRUD API	- [ ] index(Center $center): List staff for a center<br>- [ ] show(Center $center, Staff $staff): Get staff details<br>- [ ] store(StoreStaffRequest $request, Center $center): Create staff (admin only)<br>- [ ] update(UpdateStaffRequest $request, Staff $staff): Update staff (admin only)<br>- [ ] destroy(Staff $staff): Delete staff (admin only)
app/Http/Controllers/Api/V1/MediaController.php	Media upload & management API	- [ ] index(Request $request): List media for a model (polymorphic)<br>- [ ] store(UploadMediaRequest $request): Upload media<br>- [ ] update(UpdateMediaRequest $request, Media $media): Update caption/alt<br>- [ ] destroy(Media $media): Delete media<br>- [ ] reorder(ReorderMediaRequest $request): Reorder media gallery
app/Http/Controllers/Api/V1/FAQController.php	FAQ API	- [ ] index(Request $request): List FAQs (filter by category)<br>- [ ] show(FAQ $faq): Get FAQ details<br>- [ ] store(StoreFAQRequest $request): Create FAQ (admin only)<br>- [ ] update(UpdateFAQRequest $request, FAQ $faq): Update FAQ (admin only)<br>- [ ] destroy(FAQ $faq): Delete FAQ (admin only)
app/Http/Controllers/Api/V1/SearchController.php	Search API (MeiliSearch)	- [ ] search(SearchRequest $request): Search centers/services<br>- [ ] Support filters: city, rating, amenities, price range<br>- [ ] Return faceted results
Requests (12 files - validation)
File Path	Description	Feature Checklist
app/Http/Requests/Center/StoreCenterRequest.php	Create center validation	- [ ] Validate: name, address, city, postal_code, phone, email, moh_license_number, license_expiry_date, capacity<br>- [ ] MOH license unique, format validation<br>- [ ] JSON fields validation: operating_hours, amenities, transport_info
app/Http/Requests/Center/UpdateCenterRequest.php	Update center validation	(Similar to StoreCenterRequest)
app/Http/Requests/Service/StoreServiceRequest.php	Create service validation	- [ ] Validate: center_id, name, description, price, price_unit, duration, features (JSON)
app/Http/Requests/Service/UpdateServiceRequest.php	Update service validation	(Similar to StoreServiceRequest)
app/Http/Requests/Staff/StoreStaffRequest.php	Create staff validation	- [ ] Validate: center_id, name, position, qualifications (JSON), years_of_experience, photo (optional file)
app/Http/Requests/Staff/UpdateStaffRequest.php	Update staff validation	(Similar to StoreStaffRequest)
app/Http/Requests/Media/UploadMediaRequest.php	Upload media validation	- [ ] Validate: mediable_type, mediable_id, file (image/video), caption, alt_text<br>- [ ] File type/size limits (images: max 5MB, videos: max 100MB)
app/Http/Requests/Media/UpdateMediaRequest.php	Update media validation	- [ ] Validate: caption, alt_text, display_order
app/Http/Requests/Media/ReorderMediaRequest.php	Reorder media validation	- [ ] Validate: media_ids (array of IDs with order)
app/Http/Requests/FAQ/StoreFAQRequest.php	Create FAQ validation	- [ ] Validate: category, question, answer, display_order
app/Http/Requests/FAQ/UpdateFAQRequest.php	Update FAQ validation	(Similar to StoreFAQRequest)
app/Http/Requests/SearchRequest.php	Search validation	- [ ] Validate: query (min 2 chars), filters (city, rating, amenities, price_min, price_max)
Resources (6 files)
File Path	Description	Feature Checklist
app/Http/Resources/CenterResource.php	Center API response	- [ ] Transform: all center fields<br>- [ ] Include: services (when loaded), staff (when loaded), media (when loaded)<br>- [ ] Calculate: occupancy_rate, average_rating
app/Http/Resources/ServiceResource.php	Service API response	- [ ] Transform: all service fields<br>- [ ] Include: center (when loaded), media (when loaded)
app/Http/Resources/StaffResource.php	Staff API response	- [ ] Transform: all staff fields<br>- [ ] Include: photo URL
app/Http/Resources/MediaResource.php	Media API response	- [ ] Transform: url, thumbnail_url, caption, alt_text, type, size, duration
app/Http/Resources/FAQResource.php	FAQ API response	- [ ] Transform: category, question, answer, display_order
app/Http/Resources/ContentTranslationResource.php	Translation API response	- [ ] Transform: locale, field, value, translation_status, translated_by, reviewed_by
Tests (4 files minimum)
File Path	Description	Feature Checklist
tests/Feature/Center/CenterManagementTest.php	Center CRUD tests	- [ ] Test: admin can create center<br>- [ ] Test: admin can update center<br>- [ ] Test: admin can delete center<br>- [ ] Test: user cannot create center<br>- [ ] Test: MOH license validation works<br>- [ ] Test: search centers works
tests/Feature/Center/ServiceManagementTest.php	Service CRUD tests	- [ ] Test: admin can create service for center<br>- [ ] Test: admin can update service<br>- [ ] Test: admin can delete service<br>- [ ] Test: services listed by center
tests/Feature/Center/StaffManagementTest.php	Staff CRUD tests	- [ ] Test: admin can add staff to center<br>- [ ] Test: staff photo upload works<br>- [ ] Test: staff can be updated/deleted
tests/Feature/Media/MediaUploadTest.php	Media upload tests	- [ ] Test: admin can upload image<br>- [ ] Test: file size validation works<br>- [ ] Test: media can be reordered<br>- [ ] Test: media deletion removes S3 file
5.4 Workstream D: Booking System
Models (1 file)
File Path	Description	Feature Checklist
app/Models/Booking.php	Booking model with Calendly integration	- [ ] Belongs to User, Center, Service (nullable)<br>- [ ] HasFactory, soft deletes<br>- [ ] Casts: questionnaire_responses as JSON, booking_date as date, booking_time as time<br>- [ ] Scopes: upcoming(), past(), byUser(User $user), byCenter(Center $center), byStatus(string $status)<br>- [ ] Accessors: getFormattedBookingDateTimeAttribute(), getIsUpcomingAttribute()<br>- [ ] Mutators: setBookingNumberAttribute() (auto-generate: BK-20240101-0001)
Repositories (1 file)
File Path	Description	Feature Checklist
app/Repositories/BookingRepository.php	Booking data access layer	- [ ] all(array $filters = []): Paginated bookings (filter by user, center, status, date range)<br>- [ ] findById(int $id): Find by ID with relationships<br>- [ ] findByBookingNumber(string $bookingNumber): Find by booking number<br>- [ ] create(array $data): Create booking<br>- [ ] update(Booking $booking, array $data): Update booking<br>- [ ] delete(Booking $booking): Soft delete booking<br>- [ ] getUpcomingBookings(User $user): Get user's upcoming bookings<br>- [ ] getBookingHistory(User $user): Get user's past bookings
Services (5 files)
File Path	Description	Feature Checklist
app/Services/Booking/BookingService.php	Booking workflow orchestration	- [ ] createBooking(User $user, array $data): Create booking + Calendly event + send confirmation<br>- [ ] getBookingById(int $id): Get booking details<br>- [ ] getUserBookings(User $user, array $filters): Get user's bookings<br>- [ ] cancelBooking(Booking $booking, string $reason): Cancel booking + Calendly event + send notification<br>- [ ] rescheduleBooking(Booking $booking, array $data): Reschedule via Calendly<br>- [ ] confirmBooking(Booking $booking): Mark as confirmed<br>- [ ] completeBooking(Booking $booking): Mark as completed<br>- [ ] Transaction management (rollback on failure)
app/Services/Integration/CalendlyService.php	Calendly API integration	- [ ] createEvent(array $data): Create Calendly event<br>- [ ] cancelEvent(string $eventId): Cancel Calendly event<br>- [ ] rescheduleEvent(string $eventId, array $data): Reschedule event<br>- [ ] getEvent(string $eventId): Get event details<br>- [ ] handleWebhook(array $payload): Process Calendly webhook<br>- [ ] Exception handling + retry logic<br>- [ ] Mock-able for testing
app/Services/Notification/NotificationService.php	Email & SMS notification orchestration	- [ ] sendBookingConfirmation(Booking $booking): Send email + SMS<br>- [ ] sendBookingReminder(Booking $booking): Send 24h reminder<br>- [ ] sendBookingCancellation(Booking $booking): Send cancellation notice<br>- [ ] Queue email/SMS jobs<br>- [ ] Track notification status (confirmation_sent_at, reminder_sent_at)
app/Services/Integration/TwilioService.php	Twilio SMS integration	- [ ] sendSMS(string $to, string $message): Send SMS via Twilio<br>- [ ] sendBookingConfirmationSMS(Booking $booking): Template for confirmation<br>- [ ] sendReminderSMS(Booking $booking): Template for reminder<br>- [ ] Exception handling + retry<br>- [ ] Mock-able for testing
app/Services/Integration/EmailService.php	Email sending service	- [ ] sendEmail(string $to, string $subject, string $template, array $data): Send email via Laravel Mail<br>- [ ] sendBookingConfirmationEmail(Booking $booking): Template for confirmation<br>- [ ] sendReminderEmail(Booking $booking): Template for reminder<br>- [ ] sendCancellationEmail(Booking $booking): Template for cancellation<br>- [ ] Queue emails
Jobs (4 files)
File Path	Description	Feature Checklist
app/Jobs/Booking/SendBookingConfirmationJob.php	Send booking confirmation (email + SMS)	- [ ] Dispatch: after booking creation<br>- [ ] Call NotificationService::sendBookingConfirmation()<br>- [ ] Update confirmation_sent_at timestamp<br>- [ ] Retry 3 times on failure (exponential backoff)
app/Jobs/Booking/SendBookingReminderJob.php	Send 24h reminder before booking	- [ ] Dispatch: scheduled (24h before booking_date + booking_time)<br>- [ ] Call NotificationService::sendBookingReminder()<br>- [ ] Update reminder_sent_at timestamp<br>- [ ] Skip if booking cancelled
app/Jobs/Booking/SyncCalendlyEventJob.php	Sync booking with Calendly	- [ ] Create Calendly event after booking creation<br>- [ ] Update booking with calendly_event_id, calendly_event_uri, calendly_cancel_url, calendly_reschedule_url<br>- [ ] Retry 3 times on failure
app/Jobs/Booking/ProcessBookingRemindersBatchJob.php	Batch process all upcoming bookings (scheduled daily)	- [ ] Query bookings: booking_date = tomorrow, status = confirmed, reminder_sent_at is null<br>- [ ] Dispatch SendBookingReminderJob for each booking<br>- [ ] Run daily via Laravel scheduler
Controllers (2 files)
File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/BookingController.php	Booking CRUD API	- [ ] index(Request $request): List user's bookings (filter: status, date range)<br>- [ ] show(Booking $booking): Get booking details (authorize: user owns booking)<br>- [ ] store(StoreBookingRequest $request): Create booking (authenticated)<br>- [ ] cancel(CancelBookingRequest $request, Booking $booking): Cancel booking<br>- [ ] reschedule(RescheduleBookingRequest $request, Booking $booking): Reschedule booking
app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php	Calendly webhook handler	- [ ] handle(Request $request): Process Calendly webhook<br>- [ ] Validate webhook signature<br>- [ ] Handle events: invitee.created, invitee.canceled<br>- [ ] Update booking status accordingly
Requests (3 files)
File Path	Description	Feature Checklist
app/Http/Requests/Booking/StoreBookingRequest.php	Create booking validation	- [ ] Validate: center_id (exists), service_id (optional, exists), booking_date (future date), booking_time, booking_type, questionnaire_responses (JSON)<br>- [ ] Ensure booking_date is not in the past<br>- [ ] Check center capacity (optional)
app/Http/Requests/Booking/CancelBookingRequest.php	Cancel booking validation	- [ ] Validate: cancellation_reason (required, min 10 chars)<br>- [ ] Ensure booking is not already cancelled/completed
app/Http/Requests/Booking/RescheduleBookingRequest.php	Reschedule booking validation	- [ ] Validate: booking_date (future), booking_time<br>- [ ] Ensure new date is different from current date
Resources (1 file)
File Path	Description	Feature Checklist
app/Http/Resources/BookingResource.php	Booking API response	- [ ] Transform: booking_number, booking_date, booking_time, booking_type, status, calendly URLs<br>- [ ] Include: center (with basic details), service (with basic details)<br>- [ ] Exclude: internal notes (for user requests)<br>- [ ] Include: questionnaire_responses (for user's own bookings)
Listeners (3 files)
File Path	Description	Feature Checklist
app/Listeners/Booking/BookingCreatedListener.php	Handle booking creation	- [ ] Listen to App\Events\BookingCreated<br>- [ ] Dispatch: SyncCalendlyEventJob, SendBookingConfirmationJob
app/Listeners/Booking/BookingCancelledListener.php	Handle booking cancellation	- [ ] Listen to App\Events\BookingCancelled<br>- [ ] Dispatch: SendBookingCancellationJob<br>- [ ] Cancel Calendly event
app/Listeners/Booking/BookingReminderListener.php	Handle reminder scheduling	- [ ] Automatically schedule reminder job when booking confirmed
Events (3 files)
File Path	Description	Feature Checklist
app/Events/BookingCreated.php	Booking created event	- [ ] Public property: Booking $booking<br>- [ ] Implements ShouldBroadcast (optional for real-time)
app/Events/BookingCancelled.php	Booking cancelled event	- [ ] Public property: Booking $booking
app/Events/BookingConfirmed.php	Booking confirmed event	- [ ] Public property: Booking $booking
Mail (3 files - email templates)
File Path	Description	Feature Checklist
app/Mail/BookingConfirmationMail.php	Booking confirmation email	- [ ] Mailable class<br>- [ ] Pass: booking, center, service, user<br>- [ ] View: emails.booking.confirmation<br>- [ ] Subject: "Booking Confirmation - {center_name}"<br>- [ ] Include: booking details, center contact, Calendly links
app/Mail/BookingReminderMail.php	Booking reminder email	- [ ] Similar to BookingConfirmationMail<br>- [ ] Subject: "Reminder: Your visit to {center_name} tomorrow"
app/Mail/BookingCancellationMail.php	Booking cancellation email	- [ ] Subject: "Booking Cancelled - {center_name}"<br>- [ ] Include: cancellation reason, rebooking link
Routes (1 file)
File Path	Description	Feature Checklist
routes/api.php (BOOKING section)	Booking routes	- [ ] GET /api/v1/bookings (protected)<br>- [ ] POST /api/v1/bookings (protected)<br>- [ ] GET /api/v1/bookings/{booking} (protected)<br>- [ ] POST /api/v1/bookings/{booking}/cancel (protected)<br>- [ ] POST /api/v1/bookings/{booking}/reschedule (protected)<br>- [ ] POST /api/v1/webhooks/calendly (public, validate signature)
Tests (3 files minimum)
File Path	Description	Feature Checklist
tests/Feature/Booking/BookingWorkflowTest.php	Booking workflow tests	- [ ] Test: user can create booking<br>- [ ] Test: booking creates Calendly event (mocked)<br>- [ ] Test: confirmation email sent<br>- [ ] Test: confirmation SMS sent (mocked)<br>- [ ] Test: user can cancel booking<br>- [ ] Test: user can reschedule booking<br>- [ ] Test: reminder sent 24h before
tests/Feature/Booking/CalendlyWebhookTest.php	Calendly webhook tests	- [ ] Test: webhook signature validation<br>- [ ] Test: invitee.created updates booking status<br>- [ ] Test: invitee.canceled cancels booking<br>- [ ] Test: invalid signature rejected
tests/Unit/Services/CalendlyServiceTest.php	Calendly service unit tests	- [ ] Test: createEvent returns event data<br>- [ ] Test: cancelEvent sends correct API request<br>- [ ] Test: exception handling on API failure
5.5 Workstream E: Content & Community
Models (3 files)
File Path	Description	Feature Checklist
app/Models/Testimonial.php	Testimonial model with moderation	- [ ] Belongs to User (reviewer), Center, User (moderator)<br>- [ ] HasFactory, soft deletes<br>- [ ] Scopes: approved(), pending(), byCenter(Center $center), byRating(int $rating)<br>- [ ] Accessors: getStatusBadgeAttribute()<br>- [ ] Validation: rating 1-5
app/Models/Subscription.php	Newsletter subscription model	- [ ] HasFactory<br>- [ ] Casts: preferences as JSON<br>- [ ] Scopes: subscribed(), unsubscribed(), pending()<br>- [ ] Accessors: getMailchimpStatusAttribute()
app/Models/ContactSubmission.php	Contact form submission model	- [ ] Belongs to User (nullable), Center (nullable)<br>- [ ] HasFactory<br>- [ ] Scopes: new(), spam(), resolved()<br>- [ ] Spam detection: Check IP, user_agent patterns
Services (4 files)
File Path	Description	Feature Checklist
app/Services/Testimonial/TestimonialService.php	Testimonial management	- [ ] createTestimonial(User $user, Center $center, array $data): Create testimonial (status = pending)<br>- [ ] getTestimonialsByCenterId(int $centerId): Get approved testimonials<br>- [ ] moderateTestimonial(Testimonial $testimonial, string $status, User $moderator): Approve/reject<br>- [ ] deleteTestimonial(Testimonial $testimonial): Soft delete<br>- [ ] Spam detection: Check content for spam patterns
app/Services/Newsletter/NewsletterService.php	Newsletter subscription logic	- [ ] subscribe(string $email, array $preferences): Create subscription + sync to Mailchimp<br>- [ ] unsubscribe(string $email): Mark unsubscribed + sync to Mailchimp<br>- [ ] updatePreferences(Subscription $subscription, array $preferences): Update + sync<br>- [ ] syncToMailchimp(Subscription $subscription): Sync subscriber to Mailchimp<br>- [ ] Double opt-in workflow
app/Services/Integration/MailchimpService.php	Mailchimp API integration	- [ ] addSubscriber(string $email, array $mergeFields, array $tags): Add to Mailchimp<br>- [ ] updateSubscriber(string $email, array $data): Update subscriber<br>- [ ] removeSubscriber(string $email): Unsubscribe<br>- [ ] getSubscriber(string $email): Get subscriber details<br>- [ ] Exception handling + retry<br>- [ ] Mock-able for testing
app/Services/Contact/ContactService.php	Contact form handling	- [ ] submitContactForm(array $data): Create contact submission<br>- [ ] detectSpam(array $data): Spam detection logic (IP, patterns)<br>- [ ] markAsSpam(ContactSubmission $submission): Mark as spam<br>- [ ] markAsResolved(ContactSubmission $submission): Mark as resolved<br>- [ ] Send admin notification email
Jobs (2 files)
File Path	Description	Feature Checklist
app/Jobs/Newsletter/SyncSubscriberToMailchimpJob.php	Sync subscriber to Mailchimp	- [ ] Dispatch: after subscription created/updated<br>- [ ] Call MailchimpService::addSubscriber() or updateSubscriber()<br>- [ ] Update mailchimp_subscriber_id, mailchimp_status, last_synced_at<br>- [ ] Retry 3 times on failure
app/Jobs/Contact/SendContactSubmissionNotificationJob.php	Notify admin of new contact submission	- [ ] Dispatch: after contact submission created (if not spam)<br>- [ ] Send email to admin<br>- [ ] Include: submitter info, message, submission time
Controllers (4 files)
File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/TestimonialController.php	Testimonial API	- [ ] index(Request $request): List testimonials (filter by center, rating)<br>- [ ] show(Testimonial $testimonial): Get testimonial details<br>- [ ] store(StoreTestimonialRequest $request): Create testimonial (authenticated)<br>- [ ] update(UpdateTestimonialRequest $request, Testimonial $testimonial): Update own testimonial<br>- [ ] destroy(Testimonial $testimonial): Delete own testimonial<br>- [ ] moderate(ModerateTestimonialRequest $request, Testimonial $testimonial): Approve/reject (admin only)
app/Http/Controllers/Api/V1/SubscriptionController.php	Newsletter subscription API	- [ ] subscribe(SubscribeRequest $request): Subscribe to newsletter<br>- [ ] unsubscribe(UnsubscribeRequest $request): Unsubscribe<br>- [ ] updatePreferences(UpdatePreferencesRequest $request): Update preferences (authenticated)
app/Http/Controllers/Api/V1/ContactController.php	Contact form API	- [ ] store(StoreContactRequest $request): Submit contact form<br>- [ ] Spam detection before creating submission<br>- [ ] Return success message
app/Http/Controllers/Api/V1/Webhooks/MailchimpWebhookController.php	Mailchimp webhook handler	- [ ] handle(Request $request): Process Mailchimp webhook<br>- [ ] Handle events: subscribe, unsubscribe, profile, cleaned<br>- [ ] Update local subscription records
Requests (8 files)
File Path	Description	Feature Checklist
app/Http/Requests/Testimonial/StoreTestimonialRequest.php	Create testimonial validation	- [ ] Validate: center_id (exists), title, content (min 50 chars), rating (1-5)<br>- [ ] Ensure user hasn't already reviewed this center (optional)
app/Http/Requests/Testimonial/UpdateTestimonialRequest.php	Update testimonial validation	(Similar to StoreTestimonialRequest)
app/Http/Requests/Testimonial/ModerateTestimonialRequest.php	Moderate testimonial validation	- [ ] Validate: status (approved/rejected/spam), moderation_notes (optional)
app/Http/Requests/Subscription/SubscribeRequest.php	Subscribe validation	- [ ] Validate: email (unique in subscriptions), preferences (JSON, optional)
app/Http/Requests/Subscription/UnsubscribeRequest.php	Unsubscribe validation	- [ ] Validate: email (exists in subscriptions)
app/Http/Requests/Subscription/UpdatePreferencesRequest.php	Update preferences validation	- [ ] Validate: preferences (JSON)
app/Http/Requests/Contact/StoreContactRequest.php	Contact form validation	- [ ] Validate: name, email, phone (optional), subject, message (min 20 chars)<br>- [ ] Optional: center_id (if inquiry about specific center)<br>- [ ] reCAPTCHA validation (optional)
app/Http/Requests/FAQ/StoreFAQRequest.php	Already covered in Workstream C	(Reference only)
Resources (3 files)
File Path	Description	Feature Checklist
app/Http/Resources/TestimonialResource.php	Testimonial API response	- [ ] Transform: title, content, rating, created_at<br>- [ ] Include: user (name only), center (name, city)<br>- [ ] Exclude: moderation_notes (for public view)<br>- [ ] Include: status, moderation_notes (for admin view)
app/Http/Resources/SubscriptionResource.php	Subscription API response	- [ ] Transform: email, mailchimp_status, preferences, subscribed_at
app/Http/Resources/ContactSubmissionResource.php	Contact submission API response (admin only)	- [ ] Transform: all fields including IP, user_agent
Routes (1 file)
File Path	Description	Feature Checklist
routes/api.php (CONTENT section)	Content & community routes	- [ ] GET /api/v1/testimonials (public)<br>- [ ] POST /api/v1/testimonials (protected)<br>- [ ] PUT /api/v1/testimonials/{testimonial} (protected, own only)<br>- [ ] DELETE /api/v1/testimonials/{testimonial} (protected, own only)<br>- [ ] POST /api/v1/testimonials/{testimonial}/moderate (admin only)<br>- [ ] POST /api/v1/newsletter/subscribe (public)<br>- [ ] POST /api/v1/newsletter/unsubscribe (public)<br>- [ ] PUT /api/v1/newsletter/preferences (protected)<br>- [ ] POST /api/v1/contact (public)<br>- [ ] POST /api/v1/webhooks/mailchimp (public, validate signature)
Tests (3 files minimum)
File Path	Description	Feature Checklist
tests/Feature/Testimonial/TestimonialWorkflowTest.php	Testimonial tests	- [ ] Test: user can submit testimonial<br>- [ ] Test: testimonial status is pending by default<br>- [ ] Test: admin can approve testimonial<br>- [ ] Test: admin can reject testimonial<br>- [ ] Test: only approved testimonials shown publicly<br>- [ ] Test: spam detection works
tests/Feature/Newsletter/NewsletterTest.php	Newsletter tests	- [ ] Test: user can subscribe<br>- [ ] Test: subscription syncs to Mailchimp (mocked)<br>- [ ] Test: user can unsubscribe<br>- [ ] Test: duplicate subscription prevented
tests/Feature/Contact/ContactFormTest.php	Contact form tests	- [ ] Test: user can submit contact form<br>- [ ] Test: spam detection flags suspicious submissions<br>- [ ] Test: admin receives notification
5.6 Workstream F: API Infrastructure
Middleware (4 files)
File Path	Description	Feature Checklist
app/Http/Middleware/ApiVersioning.php	API version header handling	- [ ] Extract API version from Accept: application/vnd.api+json; version=1<br>- [ ] Set default version to 1<br>- [ ] Store version in request attributes
app/Http/Middleware/FormatJsonResponse.php	Consistent JSON response formatting	- [ ] Wrap all responses in standard structure: {data, meta, errors}<br>- [ ] Add metadata: version, timestamp, request_id<br>- [ ] Handle pagination metadata
app/Http/Middleware/RateLimiting.php	Rate limiting middleware	- [ ] Limit: 60 requests per minute per user<br>- [ ] Limit: 20 requests per minute per IP (unauthenticated)<br>- [ ] Return 429 with Retry-After header<br>- [ ] Exclude admin users from rate limiting
app/Http/Middleware/LogApiRequests.php	API request logging for analytics	- [ ] Log: endpoint, method, user_id, IP, response_time, status_code<br>- [ ] Store in logs or database (optional)<br>- [ ] Exclude sensitive data (passwords, tokens)
Exception Handling (3 files)
File Path	Description	Feature Checklist
app/Exceptions/Handler.php (update)	Global exception handler	- [ ] Handle ModelNotFoundException: Return 404 JSON<br>- [ ] Handle ValidationException: Return 422 JSON with errors<br>- [ ] Handle AuthenticationException: Return 401 JSON<br>- [ ] Handle AuthorizationException: Return 403 JSON<br>- [ ] Handle ThrottleRequestsException: Return 429 JSON<br>- [ ] Handle generic exceptions: Return 500 JSON (hide details in production)<br>- [ ] Report errors to Sentry
app/Exceptions/ApiException.php	Custom API exception base class	- [ ] Properties: message, statusCode, errorCode, meta<br>- [ ] Render as JSON response
app/Exceptions/ExternalServiceException.php	External service failure exception	- [ ] Extend ApiException<br>- [ ] Used when Calendly, Twilio, Mailchimp fail<br>- [ ] Log to Sentry with service context
Resources (Base) (2 files)
File Path	Description	Feature Checklist
app/Http/Resources/BaseResource.php	Base resource with common methods	- [ ] Method: withMeta(array $meta): Add custom metadata<br>- [ ] Method: withLinks(array $links): Add HATEOAS links<br>- [ ] Consistent timestamp formatting
app/Http/Resources/PaginatedResourceCollection.php	Paginated collection resource	- [ ] Wrap paginated data<br>- [ ] Add pagination metadata: current_page, last_page, per_page, total, links
OpenAPI Documentation (2 files)
File Path	Description	Feature Checklist
storage/api-docs/openapi.yaml	OpenAPI 3.0 specification	- [ ] Info: title, description, version, contact<br>- [ ] Servers: staging, production URLs<br>- [ ] Security: Sanctum bearer token<br>- [ ] Paths: All API endpoints with request/response schemas<br>- [ ] Components: Reusable schemas (User, Center, Booking, etc.)<br>- [ ] Generated via L5-Swagger or manually
config/l5-swagger.php	L5-Swagger configuration	- [ ] Configure Swagger UI path: /api/documentation<br>- [ ] Set API base path<br>- [ ] Security definitions
Configuration (2 files)
File Path	Description	Feature Checklist
config/api.php (new)	API configuration	- [ ] version: Current API version<br>- [ ] rate_limits: Authenticated/guest limits<br>- [ ] pagination: Default per_page, max_per_page<br>- [ ] response_format: Data envelope structure
config/cors.php (update)	CORS configuration	- [ ] allowed_origins: Frontend URLs (localhost:3000, staging, production)<br>- [ ] allowed_methods: GET, POST, PUT, PATCH, DELETE<br>- [ ] allowed_headers: Content-Type, Authorization, Accept<br>- [ ] exposed_headers: X-RateLimit-*, X-Request-ID
Tests (2 files)
File Path	Description	Feature Checklist
tests/Feature/Api/RateLimitingTest.php	Rate limiting tests	- [ ] Test: rate limit enforced for authenticated users<br>- [ ] Test: rate limit enforced for guests<br>- [ ] Test: 429 response after limit exceeded<br>- [ ] Test: Retry-After header present
tests/Feature/Api/ErrorHandlingTest.php	Error handling tests	- [ ] Test: 404 for non-existent resource<br>- [ ] Test: 422 for validation errors<br>- [ ] Test: 401 for unauthenticated requests<br>- [ ] Test: 403 for unauthorized requests<br>- [ ] Test: 500 for server errors (hide details)
5.7 Workstream G: Testing & Quality Assurance
Database Factories (18 files - one per model)
File Path	Description	Feature Checklist
database/factories/UserFactory.php	User factory	- [ ] Generate: name, email (unique), password (bcrypt), role, preferred_language<br>- [ ] State: admin(), super_admin(), verified()
database/factories/ProfileFactory.php	Profile factory	- [ ] Generate: bio, birth_date, address, city, postal_code<br>- [ ] Associate with User
database/factories/CenterFactory.php	Center factory	- [ ] Generate: name, slug, description, address, city, postal_code, phone, email, moh_license_number (unique), capacity<br>- [ ] JSON fields: operating_hours, amenities, transport_info<br>- [ ] State: published(), draft()
database/factories/ServiceFactory.php	Service factory	- [ ] Generate: name, slug, description, price, features (JSON)<br>- [ ] Associate with Center
database/factories/StaffFactory.php	Staff factory	- [ ] Generate: name, position, qualifications (JSON), years_of_experience<br>- [ ] Associate with Center
database/factories/BookingFactory.php	Booking factory	- [ ] Generate: booking_number (unique), booking_date (future), booking_time, status<br>- [ ] Associate with User, Center, Service
database/factories/TestimonialFactory.php	Testimonial factory	- [ ] Generate: title, content, rating (1-5), status<br>- [ ] Associate with User, Center
database/factories/FAQFactory.php	FAQ factory	- [ ] Generate: category, question, answer, display_order
database/factories/SubscriptionFactory.php	Subscription factory	- [ ] Generate: email (unique), mailchimp_status, preferences (JSON)
database/factories/ContactSubmissionFactory.php	Contact submission factory	- [ ] Generate: name, email, subject, message, status
database/factories/ConsentFactory.php	Consent factory	- [ ] Generate: consent_type, consent_given, consent_text, consent_version, ip_address<br>- [ ] Associate with User
database/factories/AuditLogFactory.php	Audit log factory	- [ ] Generate: auditable_type, auditable_id, action, old_values (JSON), new_values (JSON)
database/factories/MediaFactory.php	Media factory	- [ ] Generate: mediable_type, mediable_id, type, url, filename, mime_type, size<br>- [ ] State: image(), video()
database/factories/ContentTranslationFactory.php	Content translation factory	- [ ] Generate: translatable_type, translatable_id, locale, field, value, translation_status
database/factories/PasswordResetTokenFactory.php	Password reset token factory	(Optional, usually not needed)
database/factories/PersonalAccessTokenFactory.php	Personal access token factory	(Optional, Sanctum handles this)
database/factories/FailedJobFactory.php	Failed job factory	(Optional, usually not needed)
database/factories/JobFactory.php	Job factory	(Optional, usually not needed)
Database Seeders (5 files)
File Path	Description	Feature Checklist
database/seeders/DatabaseSeeder.php	Master seeder	- [ ] Call all seeders in correct order<br>- [ ] Truncate tables before seeding (optional)<br>- [ ] Environment check (only run on local/staging)
database/seeders/UserSeeder.php	User seeder	- [ ] Create: 1 super admin, 2 admins, 10 regular users<br>- [ ] Create profiles for each user<br>- [ ] Create consents for each user
database/seeders/CenterSeeder.php	Center seeder	- [ ] Create: 10 published centers across different cities<br>- [ ] For each center: create 3-5 services, 3-5 staff members, 5-10 photos<br>- [ ] Add operating_hours, amenities, transport_info
database/seeders/BookingSeeder.php	Booking seeder	- [ ] Create: 50 bookings (mix of statuses: confirmed, completed, cancelled)<br>- [ ] Distribute across centers and users<br>- [ ] Some with future dates, some past
database/seeders/ContentSeeder.php	Content seeder	- [ ] Create: 20 FAQs across categories<br>- [ ] Create: 30 testimonials (mix of statuses: approved, pending)<br>- [ ] Create: 10 contact submissions
Unit Tests (10 files minimum - models & services)
File Path	Description	Feature Checklist
tests/Unit/Models/UserTest.php	User model tests	- [ ] Test: relationships (profile, consents, bookings)<br>- [ ] Test: scopes (active, admins)<br>- [ ] Test: password hashing<br>- [ ] Test: preferred language accessor
tests/Unit/Models/CenterTest.php	Center model tests	- [ ] Test: relationships (services, staff, media)<br>- [ ] Test: scopes (published, byCity)<br>- [ ] Test: occupancy rate accessor<br>- [ ] Test: slug generation
tests/Unit/Models/BookingTest.php	Booking model tests	- [ ] Test: relationships (user, center, service)<br>- [ ] Test: scopes (upcoming, past, byUser)<br>- [ ] Test: booking number generation<br>- [ ] Test: formatted datetime accessor
tests/Unit/Services/AuthServiceTest.php	Auth service tests	- [ ] Test: register creates user + profile + consent<br>- [ ] Test: login validates credentials<br>- [ ] Test: password reset sends email
tests/Unit/Services/BookingServiceTest.php	Booking service tests	- [ ] Test: createBooking calls Calendly service<br>- [ ] Test: cancelBooking updates status<br>- [ ] Test: transaction rollback on failure
tests/Unit/Services/CalendlyServiceTest.php	Calendly service tests	- [ ] Test: createEvent sends correct API request<br>- [ ] Test: exception handling on API failure<br>- [ ] Test: retry logic
tests/Unit/Services/TwilioServiceTest.php	Twilio service tests	- [ ] Test: sendSMS sends correct API request<br>- [ ] Test: exception handling
tests/Unit/Services/MailchimpServiceTest.php	Mailchimp service tests	- [ ] Test: addSubscriber sends correct API request<br>- [ ] Test: updateSubscriber works
tests/Unit/Services/AuditServiceTest.php	Audit service tests	- [ ] Test: logCreate stores audit log<br>- [ ] Test: logUpdate captures old/new values<br>- [ ] Test: IP and user agent captured
tests/Unit/Services/DataExportServiceTest.php	Data export service tests	- [ ] Test: exportUserData includes all user records<br>- [ ] Test: sensitive fields excluded
Feature Tests (Already covered in workstreams above)
Auth tests
Profile tests
Center management tests
Booking workflow tests
Testimonial tests
Newsletter tests
Contact form tests
PDPA tests
Rate limiting tests
Error handling tests
Integration Tests (3 files - external services mocked)
File Path	Description	Feature Checklist
tests/Integration/CalendlyIntegrationTest.php	Calendly integration tests	- [ ] Mock Calendly API responses<br>- [ ] Test: full booking workflow with Calendly event creation<br>- [ ] Test: webhook processing
tests/Integration/TwilioIntegrationTest.php	Twilio integration tests	- [ ] Mock Twilio API responses<br>- [ ] Test: SMS sending for booking confirmation<br>- [ ] Test: retry on failure
tests/Integration/MailchimpIntegrationTest.php	Mailchimp integration tests	- [ ] Mock Mailchimp API responses<br>- [ ] Test: subscriber sync workflow<br>- [ ] Test: webhook processing
6. Configuration Files & Environment Setup
6.1 Configuration Files to Create/Update
File Path	Description	Changes Required
config/sanctum.php	Sanctum configuration	- [ ] Update stateful domains for frontend<br>- [ ] Set token expiration<br>- [ ] Configure middleware
config/queue.php	Queue configuration	- [ ] Configure SQS connection (staging/production)<br>- [ ] Set retry attempts, backoff strategy
config/mail.php	Mail configuration	- [ ] Configure SMTP/SES for production<br>- [ ] Set from address
config/services.php	External services configuration	- [ ] Add Calendly credentials (API key, webhook secret)<br>- [ ] Add Twilio credentials (SID, token, from number)<br>- [ ] Add Mailchimp credentials (API key, list ID)
config/filesystems.php	Filesystem configuration	- [ ] Configure S3 disk (bucket, region, credentials)<br>- [ ] Set public/private disk settings
config/logging.php	Logging configuration	- [ ] Configure Sentry DSN<br>- [ ] Set log channels (daily, slack, sentry)
config/app.php	App configuration	- [ ] Set timezone to Asia/Singapore<br>- [ ] Set locale to 'en'<br>- [ ] Register service providers
6.2 Environment Variables (.env)
Bash

# Application
APP_NAME="ElderCare SG"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eldercare.sg
APP_TIMEZONE=Asia/Singapore

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldercare_db
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eldercare.sg
MAIL_FROM_NAME="${APP_NAME}"

# AWS
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=eldercare-sg-media
AWS_USE_PATH_STYLE_ENDPOINT=false

# Calendly
CALENDLY_API_KEY=
CALENDLY_WEBHOOK_SECRET=
CALENDLY_EVENT_TYPE_URI=

# Twilio
TWILIO_SID=
TWILIO_TOKEN=
TWILIO_FROM=

# Mailchimp
MAILCHIMP_API_KEY=
MAILCHIMP_LIST_ID=
MAILCHIMP_SERVER_PREFIX=

# Queue
QUEUE_CONNECTION=sqs
SQS_KEY=
SQS_SECRET=
SQS_PREFIX=https://sqs.ap-southeast-1.amazonaws.com/your-account-id
SQS_QUEUE=eldercare-queue

# Logging & Monitoring
SENTRY_LARAVEL_DSN=
NEW_RELIC_LICENSE_KEY=

# API
API_VERSION=1
API_RATE_LIMIT_AUTHENTICATED=60
API_RATE_LIMIT_GUEST=20

# MeiliSearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=
7. Service Provider Registration
7.1 Service Providers to Create
File Path	Description	Feature Checklist
app/Providers/RepositoryServiceProvider.php	Bind repository interfaces to implementations	- [ ] Register all repository bindings<br>- [ ] Support dependency injection
app/Providers/EventServiceProvider.php (update)	Register event listeners	- [ ] Register: BookingCreated → BookingCreatedListener<br>- [ ] Register: BookingCancelled → BookingCancelledListener<br>- [ ] Register: Model events → Audit listeners
app/Providers/RouteServiceProvider.php (update)	API route configuration	- [ ] Configure API rate limiting<br>- [ ] Set API prefix to /api/v1
8. Validation & Completion Checklist
8.1 Per-Workstream Validation
After each workstream completion, verify:

 All files created as per matrix
 All features/functions implemented per checklist
 Unit tests written and passing (>90% coverage)
 Feature tests written and passing
 API endpoints documented in OpenAPI
 No lint errors (./vendor/bin/phpstan analyse)
 Code reviewed by peer
 Documentation updated (docs/backend/)
8.2 Final Integration Validation
Before stakeholder demo:

 All workstreams complete
 Database seeded with demo data (10 centers, 50 bookings, 30 testimonials)
 All API endpoints tested via Postman/Insomnia
 OpenAPI documentation generated and accessible
 Rate limiting tested (confirm 429 responses)
 External service integrations tested (mocked)
 PDPA flows tested (consent, audit, data export, account deletion)
 Email templates reviewed (booking confirmation, reminder, cancellation)
 SMS templates reviewed
 Error responses consistent and informative
 Performance testing: all endpoints respond <500ms (P95)
 Security audit: no SQL injection, XSS, CSRF vulnerabilities
 Staging deployment successful
 Frontend integration smoke test (if Phase 2 complete)
9. Stakeholder Demo Script
9.1 Demo Flow (15-20 minutes)
Authentication & PDPA Compliance (3 min)

Register new user with consent capture
Show consent tracking in database
Export user data (JSON download)
Delete account (show soft delete + 30-day grace)
Center Management (3 min)

Show admin panel (Laravel Nova or API)
Create new center with MOH validation
Upload center photos
Create service for center
Add staff member
Booking System (5 min)

User browses centers
User creates booking (show questionnaire)
Show Calendly event creation (mocked or real)
Show confirmation email + SMS (mocked)
Show booking in user dashboard
Cancel booking (show cancellation flow)
Content & Community (3 min)

User submits testimonial
Admin moderates testimonial (approve)
User subscribes to newsletter (show Mailchimp sync)
User submits contact form
API & Infrastructure (3 min)

Show OpenAPI documentation (Swagger UI)
Demonstrate rate limiting (trigger 429 response)
Show error handling (404, 422, 500 responses)
Show audit logs for all actions
Q&A (3 min)

9.2 Demo Preparation
 Seed database with realistic demo data
 Prepare Postman collection with all endpoints
 Set up staging environment with SSL
 Configure external services (or use mocks)
 Prepare slides showing architecture diagram
 Record demo video as backup
10. Risks & Mitigations
Risk	Impact	Probability	Mitigation	Owner
External service API changes (Calendly, Twilio, Mailchimp)	High	Medium	- Abstraction layer via services<br>- Mock all external calls in tests<br>- Version pin SDKs	Backend Dev 2
Database migration errors during deployment	High	Low	- Test migrations on staging<br>- Backup production before deploy<br>- Rollback plan documented	Backend Dev 1
PDPA compliance gaps	Critical	Low	- Legal review of consent flows<br>- Audit all data export/deletion logic<br>- Penetration testing	Backend Dev 1 + Legal
Performance issues under load	Medium	Medium	- Load testing with k6 (1000 concurrent users)<br>- Query optimization (indexes, eager loading)<br>- Redis caching for expensive queries	Backend Dev 2
Incomplete test coverage	Medium	Medium	- Enforce >90% coverage in CI<br>- Code review checklist includes tests<br>- Automated coverage reports	Both Devs
Timeline slippage due to scope creep	Medium	High	- Strict adherence to file matrix<br>- Daily stand-ups to track progress<br>- Defer non-critical features to Phase 4	Project Manager
11. Handoff to Independent Coding Agent
11.1 Prerequisites
Before starting, ensure:

 Phase 1 complete (Docker, migrations, CI/CD)
 Phase 2 complete (frontend design system, i18n)
 Database schema reviewed and understood
 External service credentials available (or mock strategy confirmed)
 Development environment running (docker-compose up -d)
 Laravel installed (composer install)
 Database migrated (php artisan migrate)
11.2 Execution Workflow
For each workstream:

Read workstream description (Section 5.X)
Review file matrix for that workstream
Create files in order:
Models first (establish data layer)
Repositories next (data access abstraction)
Services next (business logic)
Controllers next (API endpoints)
Requests (validation)
Resources (response formatting)
Jobs/Events/Listeners (async workflows)
Middleware (cross-cutting concerns)
Routes (expose endpoints)
Tests last (validate everything)
Check off each feature in the checklist as implemented
Run tests after each file: php artisan test
Commit frequently with descriptive messages: git commit -m "feat: implement UserService with registration logic"
Validate workstream completion using Section 8.1 checklist
Move to next workstream
11.3 Key Commands
Bash

# Create model with migration, factory, seeder
php artisan make:model Center -mfs

# Create controller (API resource)
php artisan make:controller Api/V1/CenterController --api --resource

# Create request (validation)
php artisan make:request Center/StoreCenterRequest

# Create resource (API transformer)
php artisan make:resource CenterResource

# Create service (custom class)
mkdir -p app/Services/Center
touch app/Services/Center/CenterService.php

# Create repository (custom class)
mkdir -p app/Repositories
touch app/Repositories/CenterRepository.php

# Create job
php artisan make:job Booking/SendBookingConfirmationJob

# Create event
php artisan make:event BookingCreated

# Create listener
php artisan make:listener Booking/BookingCreatedListener --event=BookingCreated

# Create middleware
php artisan make:middleware LogAuditTrail

# Create policy
php artisan make:policy CenterPolicy --model=Center

# Run tests
php artisan test --coverage --min=90

# Run linting
./vendor/bin/phpstan analyse

# Generate API documentation (L5-Swagger)
php artisan l5-swagger:generate
11.4 Testing Strategy
For each feature:

Write failing test first (TDD approach)
Implement feature
Run test to verify it passes
Refactor if needed
