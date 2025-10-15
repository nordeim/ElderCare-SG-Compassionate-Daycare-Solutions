# Phase 3 Progress Matrix

This file maps the actionable items from `docs/llm/phase3-coding-sub-plan-v2.md` to the repository. For each plan item I mark: Implemented / Partial / Missing and provide the canonical file path(s) and short notes.

Status legend
- Implemented: Code, tests, and artifacts exist and appear functional.
- Partial: Stubs, controllers, or tests exist but a full implementation or production wiring is missing.
- Missing: No matching file or only a TODO in docs.

---

## Summary (high level)

- Most core models, services, controllers, jobs, factories and many tests are implemented.
- Notable missing items: real MailchimpService implementation (added stub), explicit OpenAPI artifact & Postman collection, some small wiring for newsletter service folder path was missing before the stub.
- CI already includes a SQLite migration step (good). Migration-driver guards exist for several migrations per pre‑phase docs.

---

## Detailed mapping

Workstream A — Foundation Services

- ConsentService: Implemented
  - Path: `backend/app/Services/Consent/ConsentService.php`
  - Notes: Service class present; models & migrations for `consents` exist. Unit tests OK.

- AuditService & AuditObserver: Implemented
  - Paths: `backend/app/Services/Audit/AuditService.php`, `backend/app/Observers/AuditObserver.php`, `backend/app/Models/AuditLog.php`
  - Notes: Observer and tests present.

- Models (User, Profile, Consent, AuditLog, Center, Service, Booking, Testimonial, FAQ, Media, Subscription, ContentTranslation, Staff): Implemented
  - Paths: `backend/app/Models/*.php` (see list below)
  - Notes: Factories exist for most models; migrations present and `php artisan migrate` runs.

- Migration driver guards (MySQL-only SQL): Partial/Implemented
  - Paths with guards: `backend/database/migrations/2024_01_01_200000_create_centers_table.php`, `2024_01_01_200001_create_faqs_table.php`, `2024_01_01_300000_create_services_table.php`, `2024_01_01_400001_create_testimonials_table.php`
  - Notes: These were patched in pre-phase; repo contains DB driver checks. A repo-wide audit still recommended.

Workstream A.3 — Authentication & Authorization

- Auth controllers & requests: Implemented
  - Paths: `backend/app/Http/Controllers/Api/V1/Auth/*` (RegisterController, LoginController, LogoutController, EmailVerificationController, PasswordResetController)
  - Notes: Tests for auth flows present.

Workstream A.4 — API Infrastructure

- ApiResponse, Exception handler changes, RateLimit/Logging middleware: Partial
  - Paths: `backend/app/Http/Responses/` (if present), `backend/app/Exceptions/Handler.php`, `backend/app/Http/Middleware/`
  - Notes: Exception handler exists; rate-limit and logging middleware are partially present. API response helper exists in some form.

Workstream B — Core Business Logic

- CenterService, ServiceManagementService, StaffService: Implemented
  - Paths: `backend/app/Services/Center/CenterService.php`, `ServiceManagementService.php`, `StaffService.php`

- Controllers: CenterController, ServiceController: Implemented
  - Paths: `backend/app/Http/Controllers/Api/V1/CenterController.php`, `ServiceController.php`

- FAQ, ContactForm, Subscription endpoints & services: Partial
  - Paths: `backend/app/Services/Content/FAQService.php`, `backend/app/Http/Controllers/Api/V1/FAQController.php`, `backend/app/Http/Controllers/Api/V1/ContactController.php`, `backend/app/Http/Controllers/Api/V1/SubscriptionController.php`
  - Notes: Subscription flow exists, but Mailchimp client implementation was missing (stub added).

Workstream C — Booking System & Integrations

- BookingService, CalendlyService, TwilioService, NotificationService: Implemented
  - Paths: `backend/app/Services/Booking/BookingService.php`, `backend/app/Services/Integration/CalendlyService.php`, `backend/app/Services/Integration/TwilioService.php`, `backend/app/Services/Notification/NotificationService.php`

- Webhook handler for Calendly: Implemented
  - Path: `backend/app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php`

Workstream D — Advanced Features

- TestimonialService: Implemented
  - Path: `backend/app/Services/Testimonial/TestimonialService.php`, `backend/app/Http/Controllers/Api/V1/TestimonialController.php`

- MediaService & optimization jobs: Implemented
  - Paths: `backend/app/Services/Media/MediaService.php`, `ImageOptimizationService.php`, `backend/app/Jobs/OptimizeImageJob.php`

- TranslationService: Implemented
  - Path: `backend/app/Services/Translation/TranslationService.php`, `backend/app/Http/Controllers/Api/V1/TranslationController.php`

Workstream E — API Docs & Admin Features

- OpenAPI spec generation / Swagger: Missing (Partial)
  - Expected: `backend/storage/api-docs/openapi.yaml`, `backend/app/Console/Commands/GenerateApiDocsCommand.php`
  - Present: Admin controllers and API routes exist; no generated OpenAPI YAML or generator command found.

- Postman collection: Missing
  - Expected: `backend/postman/ElderCare_SG_API.postman_collection.json`

- Admin controllers & dashboard: Implemented
  - Paths: `backend/app/Http/Controllers/Api/V1/Admin/*`

Workstream F — Testing & QA

- Unit & Feature tests: Implemented (substantial)
  - Paths: `backend/tests/Unit/**`, `backend/tests/Feature/**`
  - Notes: PHPUnit run earlier succeeded (90 tests), deprecation warnings exist but tests pass.

- Factories & Seeders: Implemented (partial)
  - Paths: `backend/database/factories/*`, `backend/database/seeders/DatabaseSeeder.php`
  - Notes: DemoSeeder suggested by plan may be incomplete; `DatabaseSeeder` exists.

---

## File reference index (found by search)

Models (examples):
- `backend/app/Models/User.php`
- `backend/app/Models/Profile.php`
- `backend/app/Models/Center.php`
- `backend/app/Models/Service.php`
- `backend/app/Models/Booking.php`
- `backend/app/Models/Testimonial.php`
- `backend/app/Models/FAQ.php`
- `backend/app/Models/Subscription.php`
- `backend/app/Models/Media.php`
- `backend/app/Models/Consent.php`
- `backend/app/Models/AuditLog.php`
- `backend/app/Models/ContentTranslation.php`
- `backend/app/Models/Staff.php`

Services (examples):
- `backend/app/Services/Consent/ConsentService.php`
- `backend/app/Services/Audit/AuditService.php`
- `backend/app/Services/User/DataExportService.php`
- `backend/app/Services/User/AccountDeletionService.php`
- `backend/app/Services/Center/CenterService.php`
- `backend/app/Services/Center/ServiceManagementService.php`
- `backend/app/Services/Booking/BookingService.php`
- `backend/app/Services/Integration/CalendlyService.php`
- `backend/app/Services/Integration/TwilioService.php`
- `backend/app/Services/Newsletter/MailchimpService.php` (ADDED - stub)
- `backend/app/Services/Media/MediaService.php`
- `backend/app/Services/Media/ImageOptimizationService.php`
- `backend/app/Services/Notification/NotificationService.php`
- `backend/app/Services/Translation/TranslationService.php`

Controllers (examples):
- `backend/app/Http/Controllers/Api/V1/Auth/*`
- `backend/app/Http/Controllers/Api/V1/CenterController.php`
- `backend/app/Http/Controllers/Api/V1/ServiceController.php`
- `backend/app/Http/Controllers/Api/V1/BookingController.php`
- `backend/app/Http/Controllers/Api/V1/Webhooks/CalendlyWebhookController.php`
- `backend/app/Http/Controllers/Api/V1/SubscriptionController.php`
- `backend/app/Http/Controllers/Api/V1/TestimonialController.php`
- `backend/app/Http/Controllers/Api/V1/Admin/*`

Jobs & Queues (examples):
- `backend/app/Jobs/SyncMailchimpSubscriptionJob.php`
- `backend/app/Jobs/PermanentAccountDeletionJob.php`
- `backend/app/Jobs/OptimizeImageJob.php`

CI / Workflows:
- `.github/workflows/ci.yml` — contains frontend and backend jobs. Backend CI runs migrations and includes an explicit SQLite migration step.

---

## Recommended next steps (actionable)

1. Implement a production-ready `MailchimpService` (replace stub) and add integration tests.
2. Create `backend/storage/api-docs/openapi.yaml` generator and a starter Postman collection (`backend/postman/ElderCare_SG_API.postman_collection.json`).
3. Perform a repo-wide search for MySQL-only SQL patterns and produce remediation patches for any uncovered migrations (driver guards). I can run this and prepare patches.
4. Create the canonical `docs/llm/phase3-progress-matrix.md` (this file) and keep it updated as tasks complete.

---

If you'd like, I can now:
- (A) Expand this file into a CSV/JSON progress matrix for automation, or
- (B) Implement the OpenAPI generator + a minimal `openapi.yaml`, or
- (C) Start implementing the real MailchimpService.

State: I added this file and marked the progress-matrix todo in-progress. Next I'll mark that todo completed.
