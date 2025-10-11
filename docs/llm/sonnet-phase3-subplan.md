Phase 3 Execution Sub-Plan — Core Backend Services & PDPA Compliance
Strategic Context:

Objective: Build production-ready Laravel 12 backend API to power the ElderCare SG platform, enabling stakeholder demo with full authentication, PDPA compliance, center/service management, booking workflows, and testimonial moderation.
Timeline: 10-11 days (per master plan), optimized for single backend developer or 2-developer parallel execution.
Demo Readiness Criteria: Secure authentication, at least 5 seeded centers with services/staff, functional booking flow (Calendly integration mocked/stubbed), testimonial submission + moderation, PDPA consent + data export working, OpenAPI documentation live.
Source Alignment:

codebase_completion_master_plan.md — Phase 3 deliverables
docs/AGENT.md — Sections 4, 5, 6, 7: backend blueprint, data/integrations, operational maturity, security/compliance
Project_Architecture_Document.md — Service-layer architecture, PDPA requirements, MOH compliance
Database schema (reviewed above) — 18 tables, relationships, constraints, JSON fields
1. Architecture Deep-Dive & Design Decisions
1.1. Layered Architecture Pattern
text

┌─────────────────────────────────────────────────────────────┐
│  HTTP Layer (Controllers)                                   │
│  • Thin controllers                                         │
│  • Validate via Form Requests                               │
│  • Delegate to Services                                     │
│  • Return API Resources                                     │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│  Service Layer (Business Logic)                             │
│  • AuthenticationService                                    │
│  • ConsentService                                           │
│  • BookingService                                           │
│  • TestimonialService                                       │
│  • DataExportService                                        │
│  • AuditLogService                                          │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│  Repository Layer (Data Access)                             │
│  • UserRepository                                           │
│  • CenterRepository                                         │
│  • BookingRepository                                        │
│  • Query abstraction                                        │
│  • Cache integration                                        │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│  Model Layer (Eloquent ORM)                                 │
│  • Relationships                                            │
│  • Accessors/Mutators                                       │
│  • Scopes                                                   │
│  • Observers (audit logging)                                │
└─────────────────────────────────────────────────────────────┘
1.2. Key Design Decisions
Decision	Rationale	Implementation
Service Layer Mandatory	Keeps controllers thin, business logic testable, enables reuse	All controllers delegate to services; no DB queries in controllers
Repository Pattern	Abstracts data access, enables caching, simplifies testing	Interface + concrete implementation; injected into services
Observers for Audit Logging	Automatic, transparent tracking of model changes	Model observers fire events → AuditLogListener captures
Events & Listeners	Decouples concerns, enables async processing	UserRegistered → SendVerificationEmail, LogUserCreation
Form Requests	Validates early, keeps validation rules organized	RegisterRequest, BookingRequest with custom rules
API Resources	Consistent JSON transformation, hides internal structure	UserResource, CenterResource with conditional fields
Sanctum SPA Authentication	Stateless API auth with CSRF protection for SPA	Cookie-based sessions + token for mobile
Rate Limiting by Route Group	Prevents abuse, protects external APIs	throttle:60,1 for general, throttle:5,1 for auth
Queue Jobs for Async	Email, SMS, Mailchimp sync non-blocking	SendBookingConfirmationJob, SyncMailchimpJob
Soft Deletes Everywhere	PDPA 30-day grace period, audit trail integrity	All models except pivots use SoftDeletes trait
JSON Columns for Flexibility	Dynamic schema (operating hours, amenities, questionnaires)	Cast as array in models, validated via custom rules
1.3. PDPA Compliance Architecture
mermaid

graph TB
    A[User Action] --> B{Requires Consent?}
    B -->|Yes| C[Check Consent Table]
    C -->|Missing/Withdrawn| D[Block Action, Request Consent]
    C -->|Given| E[Proceed with Action]
    B -->|No| E
    E --> F[Execute Business Logic]
    F --> G[Observer Fires Event]
    G --> H[AuditLogListener]
    H --> I[Write to audit_logs]
    G --> J[Other Listeners]
    J --> K[Send Notifications, etc.]
Key Components:

Consent Middleware: Checks consent before allowing actions (e.g., marketing emails)
Consent Service: Manages consent CRUD, versioning, IP tracking
Audit Log Observer: Automatically logs all created, updated, deleted events
Data Export Service: Generates JSON export of all user data (PDPA right to access)
Account Deletion Service: Soft delete → 30-day grace → hard delete job
2. File Creation Matrix & Workstream Breakdown
Workstream A — Foundation (Models, Migrations, Factories) — Days 1-2
Objective: Establish Eloquent models with relationships, review/enhance migrations, create factories for testing.

File Path	Description	Feature Checklist
app/Models/User.php	User model with authentication, roles, PDPA	☐ HasFactory, Notifiable, SoftDeletes, HasApiTokens (Sanctum)<br>☐ Relationships: hasOne(Profile), hasMany(Booking), hasMany(Consent), hasMany(Testimonial)<br>☐ Accessors: getIsAdminAttribute(), getFullNameAttribute()<br>☐ Scopes: scopeActive(), scopeByRole()<br>☐ Casts: email_verified_at → datetime<br>☐ Hidden: password, remember_token<br>☐ Fillable: name, email, phone, role, preferred_language
app/Models/Profile.php	User profile (one-to-one with User)	☐ Relationships: belongsTo(User)<br>☐ Casts: birth_date → date<br>☐ Fillable: bio, birth_date, address, city, postal_code
app/Models/Center.php	Elderly care center (core entity)	☐ SoftDeletes, HasFactory<br>☐ Relationships: hasMany(Service), hasMany(Staff), hasMany(Booking), hasMany(Testimonial), morphMany(Media), morphMany(ContentTranslation)<br>☐ Casts: JSON fields (operating_hours, medical_facilities, amenities, transport_info, languages_supported, government_subsidies) → array; license_expiry_date → date; latitude, longitude → float<br>☐ Scopes: scopePublished(), scopeByCity(), scopeWithAvailableCapacity()<br>☐ Accessors: getOccupancyRateAttribute(), getIsLicenseValidAttribute()<br>☐ Fillable: all except id, created_at, updated_at, deleted_at
app/Models/Service.php	Services offered by centers	☐ SoftDeletes, HasFactory<br>☐ Relationships: belongsTo(Center), hasMany(Booking), morphMany(Media), morphMany(ContentTranslation)<br>☐ Casts: features → array, price → decimal:2<br>☐ Scopes: scopePublished(), scopeByCenter()<br>☐ Fillable: all except id, timestamps, deleted_at
app/Models/Staff.php	Center staff members	☐ HasFactory<br>☐ Relationships: belongsTo(Center)<br>☐ Casts: qualifications → array<br>☐ Scopes: scopeActive(), scopeByCenter()<br>☐ Fillable: all except id, timestamps
app/Models/Booking.php	Visit/service bookings	☐ SoftDeletes, HasFactory<br>☐ Relationships: belongsTo(User), belongsTo(Center), belongsTo(Service)<br>☐ Casts: questionnaire_responses → array, booking_date → date, booking_time → datetime, confirmation_sent_at/reminder_sent_at → datetime<br>☐ Scopes: scopeUpcoming(), scopeByStatus(), scopeByUser()<br>☐ Accessors: getIsUpcomingAttribute(), getCanCancelAttribute()<br>☐ Mutators: setBookingNumberAttribute() (auto-generate)<br>☐ Fillable: all user-input fields
app/Models/Testimonial.php	User testimonials/reviews	☐ SoftDeletes, HasFactory<br>☐ Relationships: belongsTo(User, 'user_id'), belongsTo(Center), belongsTo(User, 'moderated_by')<br>☐ Scopes: scopeApproved(), scopePending(), scopeByCenter()<br>☐ Accessors: getIsApprovedAttribute()<br>☐ Fillable: title, content, rating
app/Models/Faq.php	FAQs with categorization	☐ HasFactory<br>☐ Relationships: morphMany(ContentTranslation)<br>☐ Scopes: scopePublished(), scopeByCategory(), scopeOrdered()<br>☐ Fillable: category, question, answer, display_order, status
app/Models/Subscription.php	Newsletter subscriptions	☐ HasFactory<br>☐ Casts: preferences → array, subscribed_at/unsubscribed_at/last_synced_at → datetime<br>☐ Scopes: scopeSubscribed(), scopeUnsubscribed()<br>☐ Fillable: email, preferences, mailchimp_status
app/Models/ContactSubmission.php	Contact form submissions	☐ HasFactory<br>☐ Relationships: belongsTo(User), belongsTo(Center)<br>☐ Scopes: scopeByStatus(), scopeNotSpam()<br>☐ Fillable: name, email, phone, subject, message
app/Models/Consent.php	PDPA consent tracking	☐ HasFactory<br>☐ Relationships: belongsTo(User)<br>☐ Scopes: scopeActive(), scopeByType()<br>☐ Accessors: getIsActiveAttribute() (consent_given === true)<br>☐ Fillable: consent_type, consent_given, consent_text, consent_version, ip_address, user_agent
app/Models/AuditLog.php	PDPA audit trail	☐ HasFactory<br>☐ Relationships: belongsTo(User), morphTo('auditable')<br>☐ Casts: old_values/new_values → array<br>☐ Fillable: none (created by observer only)
app/Models/Media.php	Polymorphic media storage	☐ HasFactory<br>☐ Relationships: morphTo('mediable')<br>☐ Scopes: scopeByType(), scopeOrdered()<br>☐ Accessors: getUrlAttribute() (prepend CDN if needed)<br>☐ Fillable: type, url, thumbnail_url, filename, mime_type, size, caption, alt_text, display_order
app/Models/ContentTranslation.php	Polymorphic translations	☐ HasFactory<br>☐ Relationships: morphTo('translatable'), belongsTo(User, 'translated_by'), belongsTo(User, 'reviewed_by')<br>☐ Scopes: scopeByLocale(), scopePublished()<br>☐ Fillable: locale, field, value, translation_status
database/factories/UserFactory.php	User model factory	☐ Default user, admin, super_admin states<br>☐ Verified/unverified email states<br>☐ Generates realistic SG phone numbers (+65)
database/factories/CenterFactory.php	Center model factory	☐ Generates valid MOH license numbers<br>☐ Realistic SG addresses, postal codes<br>☐ JSON fields populated (operating hours, amenities, transport)<br>☐ States: published, draft, with_full_capacity
database/factories/ServiceFactory.php	Service model factory	☐ Belongs to center<br>☐ Realistic pricing, features JSON
database/factories/BookingFactory.php	Booking model factory	☐ States: upcoming, past, confirmed, cancelled<br>☐ Generates unique booking numbers
database/factories/*.php	Factories for all other models	☐ Testimonial, FAQ, Subscription, ContactSubmission, Consent, Staff
database/migrations/review	Review existing migrations	☐ Verify all constraints match schema SQL<br>☐ Add missing indexes if any<br>☐ Ensure SQLite compatibility (driver checks for MySQL-specific syntax)
Workstream B — Authentication & Authorization — Days 2-4
Objective: Implement secure authentication (register, login, logout, email verification, password reset), role-based authorization, rate limiting.

File Path	Description	Feature Checklist
app/Http/Controllers/Api/V1/AuthController.php	Authentication endpoints	☐ register(RegisterRequest) → create user, send verification email, return token<br>☐ login(LoginRequest) → validate credentials, return token<br>☐ logout() → revoke current token<br>☐ verifyEmail(Request) → mark email verified, auto-login<br>☐ resendVerification() → re-send verification email (rate limited)<br>☐ forgotPassword(ForgotPasswordRequest) → send reset link<br>☐ resetPassword(ResetPasswordRequest) → validate token, update password<br>☐ user() → return authenticated user with profile
app/Http/Requests/Auth/RegisterRequest.php	Registration validation	☐ Rules: name required, email unique in users, password min:8/confirmed, phone SG format, preferred_language in enum<br>☐ Custom messages<br>☐ authorize() returns true (public endpoint)
app/Http/Requests/Auth/LoginRequest.php	Login validation	☐ Rules: email required/email, password required<br>☐ Custom authenticate() method with rate limiting<br>☐ Throws ValidationException on failure
app/Http/Requests/Auth/ForgotPasswordRequest.php	Forgot password validation	☐ Rules: email required/email/exists:users<br>☐ Rate limiting (max 3 per hour per email)
app/Http/Requests/Auth/ResetPasswordRequest.php	Reset password validation	☐ Rules: token required, email required/email, password required/min:8/confirmed<br>☐ Validate token via Password::tokenExists()
app/Services/AuthenticationService.php	Authentication business logic	☐ register(array $data): User → create user + profile, dispatch UserRegistered event<br>☐ login(string $email, string $password): string → validate, create token, log audit<br>☐ logout(User $user): void → revoke tokens<br>☐ sendVerificationEmail(User $user): void → queue email job<br>☐ verifyEmail(User $user, string $hash): bool → validate hash, mark verified<br>☐ sendPasswordResetLink(string $email): void → create token, queue email<br>☐ resetPassword(array $data): bool → validate token, update password, invalidate token
app/Repositories/UserRepository.php	User data access layer	☐ create(array $data): User<br>☐ findByEmail(string $email): ?User<br>☐ findById(int $id): ?User<br>☐ update(User $user, array $data): bool<br>☐ delete(User $user): bool (soft delete)<br>☐ forceDelete(User $user): bool (hard delete for PDPA)
app/Http/Middleware/EnsureUserHasRole.php	Role-based access control	☐ Constructor accepts role(s)<br>☐ handle() checks auth()->user()->role against allowed roles<br>☐ Return 403 if unauthorized<br>☐ Usage: Route::middleware(['auth:sanctum', 'role:admin'])
app/Http/Middleware/CheckConsent.php	PDPA consent gate	☐ Constructor accepts consent type<br>☐ handle() queries consents table for active consent<br>☐ Return 451 (Unavailable For Legal Reasons) if missing<br>☐ Include consent_url in error response
app/Policies/UserPolicy.php	User authorization policy	☐ viewAny() → admin only<br>☐ view(User $auth, User $user) → self or admin<br>☐ update(User $auth, User $user) → self or admin<br>☐ delete(User $auth, User $user) → self or super_admin<br>☐ forceDelete() → super_admin only
app/Events/UserRegistered.php	User registration event	☐ Properties: public User $user<br>☐ Implements ShouldQueue (async listeners)
app/Listeners/SendEmailVerification.php	Send verification email listener	☐ handle(UserRegistered $event) → dispatch SendEmailVerificationJob
app/Listeners/LogUserCreation.php	Audit log user creation	☐ handle(UserRegistered $event) → call AuditLogService::log()
app/Jobs/SendEmailVerificationJob.php	Email verification job	☐ handle() → use Laravel Notification or Mail to send verification link<br>☐ Include user name, verification URL with signed hash<br>☐ Retry 3 times with exponential backoff
app/Notifications/EmailVerificationNotification.php	Verification email notification	☐ via() returns ['mail']<br>☐ toMail() builds email with signed URL (valid 60 min)<br>☐ Subject: "Verify Your ElderCare SG Account"<br>☐ Multilingual support via __() helper
config/sanctum.php	Sanctum configuration	☐ stateful domains: localhost:3000, staging/prod frontend URLs<br>☐ Token expiration: null (indefinite) or 60 days<br>☐ Middleware: encrypt_cookies, validate_csrf_token
routes/api.php	API route definitions	☐ Public routes (no auth):<br>  POST /api/v1/auth/register<br>  POST /api/v1/auth/login<br>  POST /api/v1/auth/forgot-password<br>  POST /api/v1/auth/reset-password<br>  GET /api/v1/auth/verify-email/{id}/{hash} (signed)<br>☐ Protected routes (auth:sanctum):<br>  POST /api/v1/auth/logout<br>  POST /api/v1/auth/resend-verification<br>  GET /api/v1/auth/user<br>☐ Rate limiting: throttle:5,1 for login/register, throttle:3,60 for password reset
Workstream C — PDPA Compliance Services — Days 3-4
Objective: Implement consent management, audit logging, data export, account deletion per Singapore PDPA requirements.

File Path	Description	Feature Checklist
app/Services/ConsentService.php	Consent management service	☐ recordConsent(User $user, string $type, bool $given, string $text, string $version): Consent → create consent record with IP/user agent<br>☐ hasConsent(User $user, string $type): bool → check active consent<br>☐ withdrawConsent(User $user, string $type): void → create withdrawal record<br>☐ getConsentHistory(User $user): Collection → all consent records<br>☐ getCurrentVersion(): string → return current privacy policy version (from config)
app/Services/AuditLogService.php	Audit logging service	☐ log(string $action, Model $model, ?User $user, ?array $oldValues, ?array $newValues): void → create audit_logs entry<br>☐ getLogsForUser(User $user): Collection → all actions by user<br>☐ getLogsForModel(Model $model): Collection → all changes to model<br>☐ pruneOldLogs(int $years = 7): int → delete logs older than retention period
app/Services/DataExportService.php	PDPA data export (right to access)	☐ exportUserData(User $user): array → compile all user data:<br>  • User + profile<br>  • Bookings with center/service details<br>  • Testimonials<br>  • Consents<br>  • Contact submissions<br>  • Audit logs<br>☐ generateExportFile(User $user): string → create JSON file, store in S3, return URL<br>☐ scheduleExportDeletion(string $url, int $hours = 24): void → delete file after download window
app/Services/AccountDeletionService.php	PDPA account deletion (right to erasure)	☐ requestDeletion(User $user, string $reason): void → soft delete user, schedule hard delete job (30 days)<br>☐ cancelDeletion(User $user): void → restore soft-deleted user within grace period<br>☐ executeHardDeletion(User $user): void → anonymize/delete all user data:<br>  • Hard delete user + profile<br>  • Anonymize bookings (keep center stats, remove PII)<br>  • Anonymize testimonials (keep content, remove name)<br>  • Delete consents<br>  • Keep audit logs (legal requirement, anonymize user_id)<br>☐ isWithinGracePeriod(User $user): bool → check if <30 days since soft delete
app/Http/Controllers/Api/V1/ConsentController.php	Consent management endpoints	☐ index() → get all user consents<br>☐ store(ConsentRequest) → record new consent<br>☐ update(ConsentRequest, string $type) → update/withdraw consent<br>☐ show(string $type) → get specific consent status
app/Http/Controllers/Api/V1/UserDataController.php	PDPA data export/deletion endpoints	☐ export() → generate data export, return download URL<br>☐ requestDeletion(DeletionRequest) → request account deletion<br>☐ cancelDeletion() → cancel pending deletion
app/Http/Requests/ConsentRequest.php	Consent validation	☐ Rules: consent_type required/in enum, consent_given required/boolean, consent_text required, consent_version required<br>☐ Automatically inject IP/user agent in controller
app/Http/Requests/DeletionRequest.php	Account deletion validation	☐ Rules: reason required/string/max:500, confirm required/accepted<br>☐ Custom message explaining 30-day grace period
app/Observers/AuditLogObserver.php	Global audit observer (attach to all models)	☐ created(Model $model) → call AuditLogService::log('created', ...)<br>☐ updated(Model $model) → log old vs new values<br>☐ deleted(Model $model) → log deletion<br>☐ restored(Model $model) → log restoration<br>☐ Exclude: AuditLog itself, PasswordResetToken, Job
app/Jobs/HardDeleteUserJob.php	Hard delete user after grace period	☐ handle() → check grace period expired, call AccountDeletionService::executeHardDeletion()<br>☐ Dispatch delay: 30 days from soft delete<br>☐ Log completion to audit_logs
routes/api.php (PDPA routes)	PDPA-specific routes	☐ GET /api/v1/consents (auth:sanctum)<br>☐ POST /api/v1/consents (auth:sanctum)<br>☐ PATCH /api/v1/consents/{type} (auth:sanctum)<br>☐ GET /api/v1/user/data/export (auth:sanctum, throttle:1,60)<br>☐ POST /api/v1/user/data/delete-request (auth:sanctum)<br>☐ POST /api/v1/user/data/cancel-deletion (auth:sanctum)
app/Console/Commands/PruneAuditLogs.php	Prune old audit logs (7 years)	☐ Schedule: daily<br>☐ Call AuditLogService::pruneOldLogs()<br>☐ Log count of deleted records
Workstream D — Core Business Logic (Centers, Services, Bookings, Testimonials) — Days 4-7
Objective: Implement center/service management, booking workflow with Calendly integration (stubbed for demo), testimonial submission + moderation.

File Path	Description	Feature Checklist
app/Repositories/CenterRepository.php	Center data access	☐ getPublished(array $filters = []): Collection → supports city, capacity, accreditation filters<br>☐ findBySlug(string $slug): ?Center<br>☐ search(string $query): Collection → fulltext search on name/description<br>☐ getNearby(float $lat, float $lng, int $radius): Collection → geolocation query<br>☐ getWithAvailableCapacity(): Collection<br>☐ create(array $data): Center<br>☐ update(Center $center, array $data): bool
app/Repositories/ServiceRepository.php	Service data access	☐ getByCenter(Center $center, bool $publishedOnly = true): Collection<br>☐ findBySlug(Center $center, string $slug): ?Service<br>☐ create(array $data): Service<br>☐ update(Service $service, array $data): bool
app/Repositories/BookingRepository.php	Booking data access	☐ getUserBookings(User $user, ?string $status = null): Collection<br>☐ getCenterBookings(Center $center, ?Carbon $date = null): Collection<br>☐ getUpcoming(User $user): Collection<br>☐ findByBookingNumber(string $number): ?Booking<br>☐ create(array $data): Booking<br>☐ updateStatus(Booking $booking, string $status): bool
app/Repositories/TestimonialRepository.php	Testimonial data access	☐ getApprovedByCenter(Center $center): Collection<br>☐ getPendingModeration(): Collection<br>☐ getUserTestimonials(User $user): Collection<br>☐ create(array $data): Testimonial<br>☐ moderate(Testimonial $testimonial, string $status, ?string $notes): bool
app/Services/CenterService.php	Center business logic	☐ getAllCenters(array $filters): Collection → delegate to repository, apply cache<br>☐ getCenterBySlug(string $slug): Center → with services, staff, media, avg rating<br>☐ searchCenters(string $query): Collection<br>☐ createCenter(array $data): Center → admin only, validate MOH license<br>☐ updateCenter(Center $center, array $data): Center<br>☐ deleteCenter(Center $center): bool → soft delete
app/Services/BookingService.php	Booking business logic	☐ createBooking(User $user, array $data): Booking → validate capacity, generate booking number, create Calendly event (stubbed), dispatch confirmation job<br>☐ getUserBookings(User $user): Collection<br>☐ cancelBooking(Booking $booking, string $reason): bool → cancel Calendly event (stubbed), update status, dispatch notification<br>☐ rescheduleBooking(Booking $booking, Carbon $newDate, Carbon $newTime): bool → Calendly reschedule (stubbed)<br>☐ sendReminders(): int → query bookings 24h ahead, dispatch reminder jobs<br>☐ handleCalendlyWebhook(array $payload): void → process invitee.created, invitee.canceled events
app/Services/TestimonialService.php	Testimonial business logic	☐ submitTestimonial(User $user, array $data): Testimonial → validate user has booking at center, create with pending status<br>☐ moderateTestimonial(Testimonial $testimonial, string $status, ?string $notes, User $moderator): bool → update status, record moderator<br>☐ getCenterTestimonials(Center $center): Collection → approved only, ordered by created_at<br>☐ flagAsSpam(Testimonial $testimonial): bool
app/Services/CalendlyService.php	Calendly integration service (STUBBED for demo)	☐ createEvent(Booking $booking): array → return mock event data ['event_id' => 'STUB-'.uniqid(), 'event_uri' => '#', 'cancel_url' => '#', 'reschedule_url' => '#']<br>☐ cancelEvent(string $eventId, string $reason): bool → return true<br>☐ rescheduleEvent(string $eventId, Carbon $newDate): bool → return true<br>☐ verifyWebhookSignature(Request $request): bool → return true for demo<br>☐ TODO: Replace stubs with real Calendly API client in post-demo phase
app/Http/Controllers/Api/V1/CenterController.php	Center endpoints	☐ index(Request) → filter by city, status, capacity; return CenterResource::collection()<br>☐ show(string $slug) → return CenterResource with services/staff/media/testimonials<br>☐ store(CenterRequest) → admin only, create center<br>☐ update(CenterRequest, Center) → admin only, update center<br>☐ destroy(Center) → admin only, soft delete
app/Http/Controllers/Api/V1/ServiceController.php	Service endpoints	☐ index(Center) → services for a center<br>☐ show(Center, Service) → service details<br>☐ store(ServiceRequest, Center) → admin only, create service<br>☐ update(ServiceRequest, Center, Service) → admin only, update<br>☐ destroy(Center, Service) → admin only, soft delete
app/Http/Controllers/Api/V1/BookingController.php	Booking endpoints	☐ index() → user's bookings (with filters)<br>☐ store(BookingRequest) → create booking<br>☐ show(Booking) → booking details (authorize: own booking or admin)<br>☐ cancel(Booking, CancelBookingRequest) → cancel booking<br>☐ reschedule(Booking, RescheduleBookingRequest) → reschedule booking
app/Http/Controllers/Api/V1/TestimonialController.php	Testimonial endpoints	☐ index(Request) → filter by center, status (approved only for non-admins)<br>☐ store(TestimonialRequest) → submit testimonial<br>☐ moderate(Testimonial, ModerateTestimonialRequest) → admin only, approve/reject
app/Http/Controllers/Api/V1/WebhookController.php	External webhook receiver	☐ calendly(Request) → verify signature (stubbed), call BookingService::handleCalendlyWebhook()<br>☐ mailchimp(Request) → handle unsubscribe events (Phase 5.5)<br>☐ Rate limiting: throttle:60,1
app/Http/Requests/CenterRequest.php	Center create/update validation	☐ Rules: name required, slug unique, moh_license_number unique/format, license_expiry_date after today, capacity integer/min:1, current_occupancy ≤ capacity, JSON fields valid arrays<br>☐ Custom validation: validate_moh_license(), validate_postal_code() (SG 6-digit)
app/Http/Requests/ServiceRequest.php	Service validation	☐ Rules: name required, slug unique per center, price nullable/numeric/min:0, features array
app/Http/Requests/BookingRequest.php	Booking validation	☐ Rules: center_id exists, service_id exists/belongs to center, booking_date after today, booking_time valid, questionnaire_responses array<br>☐ Custom: validate center has capacity, no double-booking same timeslot
app/Http/Requests/TestimonialRequest.php	Testimonial validation	☐ Rules: center_id exists, title required, content required/min:20, rating integer/between:1,5<br>☐ Custom: validate user has confirmed booking at center
app/Http/Resources/CenterResource.php	Center API resource	☐ Fields: all center fields, computed occupancy_rate, is_license_valid, average_rating<br>☐ Relationships (conditional): services, staff, media, testimonials (when included)<br>☐ Localization: translate via ContentTranslation if locale present
app/Http/Resources/ServiceResource.php	Service API resource	☐ Fields: service fields, center name/slug<br>☐ Conditional: media, translations
app/Http/Resources/BookingResource.php	Booking API resource	☐ Fields: booking data, center summary, service summary, user summary (name/email only for admins)<br>☐ Hide: questionnaire_responses (sensitive), internal notes for non-admins<br>☐ Computed: can_cancel, is_upcoming
app/Http/Resources/TestimonialResource.php	Testimonial API resource	☐ Fields: testimonial data, user name (if approved), center name<br>☐ Hide: moderation_notes for non-admins
app/Policies/CenterPolicy.php	Center authorization	☐ viewAny() → public<br>☐ view() → public if published, admin if draft<br>☐ create/update/delete() → admin only
app/Policies/BookingPolicy.php	Booking authorization	☐ view(User, Booking) → own booking or admin<br>☐ create() → authenticated<br>☐ cancel(User, Booking) → own booking (if not completed) or admin<br>☐ reschedule(User, Booking) → own booking (if upcoming) or admin
app/Policies/TestimonialPolicy.php	Testimonial authorization	☐ viewAny() → public (approved only)<br>☐ create() → authenticated + has booking<br>☐ moderate() → admin only
app/Events/BookingCreated.php	Booking created event	☐ Properties: public Booking $booking<br>☐ Implements ShouldQueue
app/Events/BookingCancelled.php	Booking cancelled event	☐ Properties: public Booking $booking, public string $reason
app/Listeners/SendBookingConfirmation.php	Send confirmation email/SMS	☐ handle(BookingCreated) → dispatch SendBookingConfirmationJob
app/Listeners/SendBookingCancellation.php	Send cancellation email/SMS	☐ handle(BookingCancelled) → dispatch SendBookingCancellationJob
app/Jobs/SendBookingConfirmationJob.php	Email/SMS confirmation job	☐ Send email with booking details, calendar link<br>☐ Stub SMS send (Twilio integration Phase 5.5)<br>☐ Mark confirmation_sent_at
app/Jobs/SendBookingReminderJob.php	24h reminder job	☐ Query bookings 24h ahead, send reminders<br>☐ Mark reminder_sent_at
app/Jobs/SendBookingCancellationJob.php	Cancellation notification job	☐ Send email/SMS with cancellation reason
routes/api.php (business routes)	Core business routes	☐ Centers: GET /api/v1/centers (public), GET /api/v1/centers/{slug} (public), POST /api/v1/centers (admin), PATCH /api/v1/centers/{id} (admin), DELETE /api/v1/centers/{id} (admin)<br>☐ Services: GET /api/v1/centers/{center}/services, GET /api/v1/centers/{center}/services/{service}, admin CRUD<br>☐ Bookings: GET /api/v1/bookings (auth), POST /api/v1/bookings (auth), GET /api/v1/bookings/{id} (auth), POST /api/v1/bookings/{id}/cancel (auth), POST /api/v1/bookings/{id}/reschedule (auth)<br>☐ Testimonials: GET /api/v1/testimonials (public), POST /api/v1/testimonials (auth), POST /api/v1/testimonials/{id}/moderate (admin)<br>☐ Webhooks: POST /api/v1/webhooks/calendly, POST /api/v1/webhooks/mailchimp
Workstream E — External Integrations (Stubs + Foundation) — Day 7
Objective: Create service interfaces for Mailchimp, Twilio, AWS S3 with stub implementations for demo; real implementations post-Phase 3.

File Path	Description	Feature Checklist
app/Services/MailchimpService.php	Mailchimp integration (STUBBED)	☐ subscribe(string $email, array $preferences): bool → return true<br>☐ unsubscribe(string $email): bool → return true<br>☐ updatePreferences(string $email, array $preferences): bool → return true<br>☐ syncSubscriber(Subscription $sub): bool → return true<br>☐ TODO: Implement real Mailchimp API client post-demo
app/Services/TwilioService.php	Twilio SMS integration (STUBBED)	☐ sendSms(string $to, string $message): bool → log to file, return true<br>☐ sendBookingConfirmation(Booking $booking): bool<br>☐ sendBookingReminder(Booking $booking): bool<br>☐ TODO: Implement real Twilio SDK post-demo
app/Services/StorageService.php	AWS S3 file upload service	☐ uploadFile(UploadedFile $file, string $path): string → upload to S3, return URL<br>☐ deleteFile(string $url): bool<br>☐ generatePresignedUrl(string $path, int $expiresIn = 3600): string → for data exports<br>☐ Uses Laravel Storage facade with s3 disk
app/Http/Controllers/Api/V1/SubscriptionController.php	Newsletter subscription endpoints	☐ store(SubscriptionRequest) → create subscription, call MailchimpService::subscribe() (stubbed)<br>☐ unsubscribe(Request) → validate email, call MailchimpService::unsubscribe()<br>☐ updatePreferences(Request) → update preferences
app/Http/Requests/SubscriptionRequest.php	Subscription validation	☐ Rules: email required/email/unique:subscriptions, preferences array<br>☐ Validate preferences structure
routes/api.php (integration routes)	Integration routes	☐ POST /api/v1/subscribe (public, throttle:5,60)<br>☐ POST /api/v1/unsubscribe (public)<br>☐ PATCH /api/v1/subscriptions/preferences (auth)
config/services.php	External service configs	☐ Mailchimp: api_key, list_id, server_prefix<br>☐ Twilio: account_sid, auth_token, from_number<br>☐ Calendly: api_key, webhook_secret, event_type_uri<br>☐ Cloudflare Stream: account_id, api_token (Phase 2 video)
Workstream F — API Layer & Documentation — Days 8-9
Objective: API error handling, response formatting, rate limiting, OpenAPI documentation generation.

File Path	Description	Feature Checklist
app/Exceptions/Handler.php	Global exception handler	☐ Override register() to handle:<br>  • ModelNotFoundException → 404 JSON response<br>  • ValidationException → 422 with error details<br>  • AuthenticationException → 401 JSON<br>  • AuthorizationException → 403 JSON<br>  • ThrottleException → 429 with retry-after header<br>  • Generic exceptions → 500 with error ID (for Sentry correlation)<br>☐ Format all API errors consistently:<br>json<br>{<br> "error": {<br> "message": "...",<br> "code": "VALIDATION_ERROR",<br> "errors": {...},<br> "trace_id": "..."<br> }<br>}<br><br>☐ Include trace_id in logs/Sentry
app/Http/Middleware/ForceJsonResponse.php	Force JSON responses for API	☐ handle() sets Accept: application/json header<br>☐ Apply to all /api/* routes
app/Http/Middleware/AddApiResponseHeaders.php	Add API response headers	☐ X-Request-ID (UUID for request tracing)<br>☐ X-RateLimit-* headers<br>☐ Content-Language (based on locale)<br>☐ CORS headers (if not handled by middleware)
app/Http/Resources/ErrorResource.php	Error response resource	☐ Standardized error formatting<br>☐ Include validation errors, trace_id, timestamp
app/Traits/ApiResponse.php	API response helper trait	☐ success(mixed $data, string $message = null, int $code = 200)<br>☐ error(string $message, int $code = 400, array $errors = [])<br>☐ paginated(LengthAwarePaginator $paginator, callable $transformer)<br>☐ Use in all controllers
config/cors.php	CORS configuration	☐ allowed_origins: frontend URLs (localhost:3000, staging, prod)<br>☐ allowed_methods: GET, POST, PATCH, DELETE, OPTIONS<br>☐ allowed_headers: Content-Type, Authorization, X-Requested-With, Accept, Origin<br>☐ exposed_headers: X-Request-ID, X-RateLimit-*<br>☐ supports_credentials: true (for Sanctum cookies)
routes/api.php (middleware)	API middleware stack	☐ Global middleware: ForceJsonResponse, AddApiResponseHeaders<br>☐ Route groups:<br>  • throttle:60,1 (general API)<br>  • throttle:5,1 (auth endpoints)<br>  • auth:sanctum (protected routes)
storage/api-docs/openapi.yaml	OpenAPI 3.0 specification	☐ Info: title, description, version, contact<br>☐ Servers: localhost, staging, production<br>☐ Security: sanctumAuth (bearer token)<br>☐ Paths (all endpoints):<br>  • /api/v1/auth/* (register, login, logout, etc.)<br>  • /api/v1/centers (list, show, create, update, delete)<br>  • /api/v1/bookings (list, create, show, cancel, reschedule)<br>  • /api/v1/testimonials (list, create, moderate)<br>  • /api/v1/consents (list, create, update)<br>  • /api/v1/user/data/* (export, delete-request)<br>☐ Components:<br>  • Schemas: User, Center, Booking, Testimonial, Consent, Error<br>  • Responses: Success, ValidationError, Unauthorized, Forbidden, NotFound<br>  • Security schemes: sanctumAuth<br>☐ Examples for all endpoints
public/api-docs.html	Swagger UI static page	☐ Loads openapi.yaml<br>☐ Interactive API explorer<br>☐ Accessible at /api-docs
composer.json (packages)	Add OpenAPI tooling	☐ "darkaonline/l5-swagger": "^8.5" (or manual YAML)<br>☐ "spatie/laravel-query-builder": "^5.0" (for filtering/sorting)
Workstream G — Testing & Quality Assurance — Days 9-11
Objective: Comprehensive backend test coverage (unit, feature, integration), ensure >90% coverage per master plan.

File Path	Description	Feature Checklist
tests/Unit/Services/AuthenticationServiceTest.php	Auth service unit tests	☐ test_register_creates_user_and_profile()<br>☐ test_register_dispatches_event()<br>☐ test_login_validates_credentials()<br>☐ test_login_returns_token()<br>☐ test_send_verification_email_queues_job()<br>☐ test_verify_email_marks_verified()<br>☐ test_reset_password_validates_token()
tests/Unit/Services/ConsentServiceTest.php	Consent service tests	☐ test_record_consent_creates_record()<br>☐ test_has_consent_returns_true_if_given()<br>☐ test_withdraw_consent_creates_withdrawal_record()<br>☐ test_get_consent_history_returns_all_records()
tests/Unit/Services/BookingServiceTest.php	Booking service tests	☐ test_create_booking_generates_booking_number()<br>☐ test_create_booking_calls_calendly_service() (mock)<br>☐ test_cancel_booking_updates_status()<br>☐ test_send_reminders_queries_upcoming_bookings()
tests/Unit/Services/DataExportServiceTest.php	Data export tests	☐ test_export_user_data_includes_all_relations()<br>☐ test_generate_export_file_returns_url()
tests/Unit/Repositories/UserRepositoryTest.php	User repo tests	☐ test_create_user()<br>☐ test_find_by_email()<br>☐ test_update_user()<br>☐ test_soft_delete_user()
tests/Feature/Auth/RegistrationTest.php	Registration API tests	☐ test_user_can_register_with_valid_data()<br>☐ test_registration_validates_required_fields()<br>☐ test_registration_validates_email_format()<br>☐ test_registration_validates_unique_email()<br>☐ test_registration_creates_profile()<br>☐ test_registration_sends_verification_email()
tests/Feature/Auth/LoginTest.php	Login API tests	☐ test_user_can_login_with_valid_credentials()<br>☐ test_login_returns_token()<br>☐ test_login_fails_with_invalid_credentials()<br>☐ test_login_rate_limits_attempts()
tests/Feature/Auth/EmailVerificationTest.php	Email verification tests	☐ test_user_can_verify_email_with_valid_hash()<br>☐ test_verification_fails_with_invalid_hash()<br>☐ test_resend_verification_queues_job()
tests/Feature/Auth/PasswordResetTest.php	Password reset tests	☐ test_user_can_request_password_reset()<br>☐ test_user_can_reset_password_with_valid_token()<br>☐ test_reset_fails_with_invalid_token()<br>☐ test_reset_invalidates_token_after_use()
tests/Feature/Centers/CenterListTest.php	Center listing tests	☐ test_can_list_published_centers()<br>☐ test_can_filter_centers_by_city()<br>☐ test_can_search_centers_by_name()<br>☐ test_draft_centers_hidden_from_public()
tests/Feature/Centers/CenterDetailTest.php	Center detail tests	☐ test_can_view_center_by_slug()<br>☐ test_includes_services_and_staff()<br>☐ test_includes_average_rating()<br>☐ test_404_for_non_existent_center()
tests/Feature/Bookings/CreateBookingTest.php	Booking creation tests	☐ test_authenticated_user_can_create_booking()<br>☐ test_booking_generates_unique_number()<br>☐ test_booking_validates_capacity()<br>☐ test_booking_creates_calendly_event() (mock)<br>☐ test_booking_sends_confirmation_email() (queue assertion)<br>☐ test_unauthenticated_user_cannot_create_booking()
tests/Feature/Bookings/CancelBookingTest.php	Booking cancellation tests	☐ test_user_can_cancel_own_booking()<br>☐ test_user_cannot_cancel_other_users_booking()<br>☐ test_cancellation_updates_calendly() (mock)<br>☐ test_cancellation_sends_notification()
tests/Feature/Testimonials/SubmitTestimonialTest.php	Testimonial submission tests	☐ test_user_can_submit_testimonial()<br>☐ test_testimonial_defaults_to_pending()<br>☐ test_validates_user_has_booking_at_center()<br>☐ test_validates_rating_range()
tests/Feature/Testimonials/ModerateTestimonialTest.php	Testimonial moderation tests	☐ test_admin_can_approve_testimonial()<br>☐ test_admin_can_reject_testimonial()<br>☐ test_non_admin_cannot_moderate()<br>☐ test_moderation_records_moderator()
tests/Feature/PDPA/ConsentTest.php	Consent management tests	☐ test_user_can_give_consent()<br>☐ test_user_can_withdraw_consent()<br>☐ test_consent_records_ip_and_user_agent()<br>☐ test_consent_history_includes_all_changes()
tests/Feature/PDPA/DataExportTest.php	Data export tests	☐ test_user_can_export_data()<br>☐ test_export_includes_all_user_data()<br>☐ test_export_rate_limited()
tests/Feature/PDPA/AccountDeletionTest.php	Account deletion tests	☐ test_user_can_request_deletion()<br>☐ test_deletion_soft_deletes_user()<br>☐ test_deletion_schedules_hard_delete_job()<br>☐ test_user_can_cancel_deletion_within_grace_period()<br>☐ test_hard_delete_anonymizes_data()
tests/Feature/Middleware/RoleMiddlewareTest.php	Authorization middleware tests	☐ test_admin_middleware_allows_admin()<br>☐ test_admin_middleware_blocks_user()<br>☐ test_super_admin_middleware_allows_super_admin()
tests/Feature/Api/RateLimitingTest.php	Rate limiting tests	☐ test_api_rate_limits_general_requests()<br>☐ test_auth_endpoints_rate_limit_stricter()<br>☐ test_rate_limit_returns_429()
tests/Feature/Api/ErrorHandlingTest.php	API error handling tests	☐ test_404_returns_json_error()<br>☐ test_validation_error_returns_422_with_details()<br>☐ test_unauthorized_returns_401()<br>☐ test_forbidden_returns_403()<br>☐ test_server_error_includes_trace_id()
tests/TestCase.php	Base test class	☐ setUp() initializes database, seeds if needed<br>☐ Helper: actingAsUser(?string $role = 'user'): User<br>☐ Helper: actingAsAdmin(): User<br>☐ Helper: createCenter(array $attrs = []): Center<br>☐ Helper: createBooking(User $user, array $attrs = []): Booking<br>☐ Uses RefreshDatabase, WithFaker
phpunit.xml	PHPUnit configuration	☐ testsuites: Unit, Feature<br>☐ coverage: exclude bootstrap, config, routes, resources<br>☐ php: DB_CONNECTION=sqlite, DB_DATABASE=:memory:, QUEUE_CONNECTION=sync, MAIL_MAILER=array<br>☐ stopOnFailure=false, colors=true
composer.json (dev packages)	Testing dependencies	☐ "phpunit/phpunit": "^10.0"<br>☐ "mockery/mockery": "^1.6"<br>☐ "fakerphp/faker": "^1.23"<br>☐ "laravel/pint": "^1.0" (code style)
Workstream H — Database Seeding & Demo Data — Day 10
Objective: Create realistic demo data for stakeholder presentation: 5-10 centers with services/staff, sample users, bookings, testimonials.

File Path	Description	Feature Checklist
database/seeders/DatabaseSeeder.php	Master seeder	☐ Calls all seeders in order:<br>  1. UserSeeder<br>  2. CenterSeeder<br>  3. ServiceSeeder<br>  4. StaffSeeder<br>  5. FaqSeeder<br>  6. BookingSeeder<br>  7. TestimonialSeeder<br>  8. ConsentSeeder<br>☐ Truncates tables before seeding (optional flag)
database/seeders/UserSeeder.php	User seeding	☐ Create super admin: admin@eldercare.sg, password: password<br>☐ Create 2 admins<br>☐ Create 20 regular users (verified emails)<br>☐ Each user gets profile with realistic SG addresses
database/seeders/CenterSeeder.php	Center seeding	☐ Create 8-10 centers across Singapore:<br>  • Mix of cities: Ang Mo Kio, Bedok, Clementi, Jurong West, Tampines, Woodlands<br>  • All published status<br>  • Valid MOH license numbers<br>  • Realistic operating hours, amenities, transport info<br>  • Some at capacity, some with availability<br>☐ Attach 3-5 photos per center (placeholder URLs or real stock photos)<br>☐ Create English descriptions (Mandarin translations via ContentTranslation seeder if time permits)
database/seeders/ServiceSeeder.php	Service seeding	☐ Create 3-5 services per center:<br>  • Day care<br>  • Dementia care<br>  • Physiotherapy<br>  • Meal programs<br>  • Social activities<br>☐ Realistic pricing: $50-200/day<br>☐ All published status
database/seeders/StaffSeeder.php	Staff seeding	☐ Create 4-8 staff per center:<br>  • Registered nurses<br>  • Caregivers<br>  • Physiotherapists<br>  • Activity coordinators<br>☐ Qualifications JSON: certifications, years of experience<br>☐ Placeholder bio
database/seeders/FaqSeeder.php	FAQ seeding	☐ Create 20-30 FAQs across categories:<br>  • General (10)<br>  • Booking (5)<br>  • Services (5)<br>  • Pricing (5)<br>  • Accessibility (5)<br>☐ Realistic questions/answers<br>☐ All published
database/seeders/BookingSeeder.php	Booking seeding	☐ Create 30-50 bookings:<br>  • 10 upcoming confirmed<br>  • 20 past completed<br>  • 5 cancelled<br>  • 5 pending<br>☐ Distribute across centers and users<br>☐ Realistic booking numbers<br>☐ Stub Calendly event IDs
database/seeders/TestimonialSeeder.php	Testimonial seeding	☐ Create 30-40 testimonials:<br>  • 25 approved (visible)<br>  • 5 pending<br>  • 5 rejected<br>☐ Rating distribution: mostly 4-5 stars, some 3<br>☐ Realistic content<br>☐ Link to users with bookings at the center
database/seeders/ConsentSeeder.php	Consent seeding	☐ Create consent records for all users:<br>  • account consent (all users, given)<br>  • marketing_email (70% given)<br>  •
