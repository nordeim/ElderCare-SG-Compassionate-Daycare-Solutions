# Agent Briefing — Phase 3 Initialization (Backend)

Purpose
- Provide a single, authoritative briefing for an independent AI coding agent to start Phase 3 backend work.
- Capture WHAT (scope), WHY (business & compliance drivers), and HOW (architecture, conventions, immediate steps, verification) based on the master plan and repository scan.
- Surface current codebase state, high‑priority gaps, exact next tasks, commands, CI guidance, and PR expectations so the agent can begin work autonomously.

Audience
- New AI coding agent or engineer onboarding to implement Phase 3 (Core Backend Services & PDPA compliance) in `h:\project\web-platform\backend`.

Summary — WHAT, WHY, HOW
- WHAT: Implement API v1 backend services required for authentication, PDPA compliance (consent + audit + data export), centers/services, booking orchestration (Calendly stubs), media (S3) handling, testimonials, and OpenAPI docs + tests.
- WHY: Deliver a production-ready backend foundation enabling a stakeholder demo, legal PDPA requirements, and a stable staging environment. Phase 1/2 are complete; Phase 3 unlocks user flows (registration, bookings, data export) and compliance.
- HOW (principles):
  - Service-oriented: thin controllers, fat services under `app/Services/*`.
  - Repositories optional but preferred for DB encapsulation.
  - PDPA-first: every data mutation must be logged (audit_logs) and consent checked where applicable.
  - TDD: write failing PHPUnit tests (sqlite) first, make them pass.
  - CI must run migrations on SQLite before tests to catch DB-specific SQL issues.
  - Use mocks/fakes for external APIs (Calendly/Twilio/Mailchimp) in CI.

Current codebase state (high-level)
- Models & migrations: Core Eloquent models present (User, Profile, Consent, AuditLog, Center, Service, Staff, Booking, Testimonial, FAQ, Subscription, ContactSubmission, Media, ContentTranslation). 18 migration files exist. Some migrations were guarded for MySQL-only SQL (already applied).
- Implemented services: `ConsentService`, `AuditService`.
- Missing / high-value gaps (from repo scan):
  - Controllers under `app/Http/Controllers/Api/V1` (Auth + resources) — missing.
  - Services: `User/DataExportService`, `CenterService`, `BookingService`, `MediaService`, `TestimonialService` — missing.
  - Observer: `app/Observers/AuditObserver.php` — missing.
  - API response wrapper: `app/Http/Responses/ApiResponse.php` — missing.
  - OpenAPI: `storage/api-docs/openapi.yaml` and Postman collection — missing.
  - Tests: Most unit/feature tests are placeholders.
  - CI: No sqlite migration step before tests.

Immediate priorities (safe, high-value order)
1. CI safety: add sqlite DB creation + run `php artisan migrate --database=sqlite --force` before tests.
2. Core API response helper: add `app/Http/Responses/ApiResponse.php` for consistent envelopes.
3. PDPA-critical services:
   - Implement `app/Services/User/DataExportService.php` (JSON export of user data).
   - Implement `app/Observers/AuditObserver.php` to hook model events to `AuditService`.
4. Minimal Auth controllers (API):
   - `app/Http/Controllers/Api/V1/Auth/RegisterController.php`
   - `app/Http/Controllers/Api/V1/Auth/LoginController.php`
   - `app/Http/Controllers/Api/V1/Auth/LogoutController.php`
   - Use FormRequests for validation and return ApiResponse.
5. Service skeletons (implement interfaces / methods and unit tests):
   - `app/Services/Center/CenterService.php`
   - `app/Services/Booking/BookingService.php` (Calendly stubs)
   - `app/Services/Media/MediaService.php` (S3 abstraction stub)
   - `app/Services/Testimonial/TestimonialService.php`
6. OpenAPI + Postman placeholders:
   - Add `backend/storage/api-docs/openapi.yaml` with minimal endpoints (auth, centers, booking).
   - Create `backend/postman/ElderCare_SG_API.postman_collection.json` as a minimal collection.

Exact next file to create (recommended)
- `app/Http/Responses/ApiResponse.php` (small, low-risk, enables controller scaffolds).
- After that: `app/Observers/AuditObserver.php` and `app/Services/User/DataExportService.php`.

Commands & environment (Windows / Docker)
- Start local stack:
  - Open PowerShell, cd to repo root:
    - docker-compose up -d
  - Exec into backend container:
    - docker-compose exec backend bash
- Prepare sqlite DB (local or CI):
  - mkdir -p backend/database
  - touch backend/database/database.sqlite
  - php artisan config:clear
  - php artisan migrate --database=sqlite --force
- Run tests:
  - php artisan test
  - vendor/bin/phpunit --testsuite=Unit
- Linting/format:
  - composer check-style (if configured) / php-cs-fixer
- Generate auth tokens (local testing):
  - php artisan tinker
  - -> create user factory or use `php artisan db:seed` if seeders exist.

CI patch (insert before test step)
- Create sqlite file and run migrations before tests. Example snippet (GitHub Actions):
  - mkdir -p backend/database
  - touch backend/database/database.sqlite
  - php artisan migrate --database=sqlite --force

Testing guidance
- Use sqlite in CI for fast test runs; ensure migrations guarded for MySQL-only SQL.
- Mock external APIs via Guzzle mock handlers or local fake adapters; never call live services in CI.
- Prioritize unit tests for services, feature tests for critical API endpoints (registration, login, center listing, booking create).
- Aim for deterministic tests: seed predictable data via factories.

Branching & PR conventions
- Branch naming: feature/phase3-<short-desc> (e.g., feature/phase3-auth-controllers)
- Create small PRs (< 300 LOC) with:
  - Summary: WHAT changed, WHY, HOW implemented.
  - Tests added and passing locally.
  - Files changed list and any infra impact (CI changes).
  - PDPA impact statement if relevant.
- Tag reviewers: backend leads and compliance owner.

Checklist for each PR (automated gates + manual)
- [ ] New code covered by unit tests.
- [ ] Integration/feature tests for API endpoints.
- [ ] Linting passes (php-cs-fixer / styleci).
- [ ] No secrets in code.
- [ ] PDPA criteria satisfied (consents/audit where applicable).
- [ ] Update `docs/phase3-progress-matrix.csv` row statuses.

File inventory (quick map)
- Implemented (verified): backend/app/Models/* (User, Profile, Consent, AuditLog, Center, Service, Staff, Booking, Testimonial, FAQ, Subscription, ContactSubmission, Media, ContentTranslation); migrations present.
- Present services: backend/app/Services/Consent/ConsentService.php; backend/app/Services/Audit/AuditService.php.
- Missing (high priority):
  - backend/app/Services/User/DataExportService.php
  - backend/app/Observers/AuditObserver.php
  - backend/app/Http/Controllers/Api/V1/* (Auth + resource controllers)
  - backend/app/Http/Responses/ApiResponse.php
  - backend/app/Services/Center/CenterService.php
  - backend/app/Services/Booking/BookingService.php
  - backend/app/Services/Media/MediaService.php
  - backend/app/Services/Testimonial/TestimonialService.php
  - backend/storage/api-docs/openapi.yaml
  - backend/postman/ElderCare_SG_API.postman_collection.json
  - Tests: unit + feature under backend/tests/*

PDPA & compliance notes (must read before changing persistence)
- All personal data changes must be recorded in `audit_logs`. Ensure `AuditObserver` triggers on model events (created/updated/deleted) and calls `AuditService`.
- Consent versions: Use `consents` table; log consent acceptance with version id/time.
- Data export: `DataExportService` must gather all user-related data (profiles, consents, bookings, messages, media links) and return a downloadable JSON bundle. Include metadata (timestamp, version).
- Account deletion: follow soft-delete + 30-day grace; queue a hard-delete job; record audit entries for deletion requests.

Risks & mitigations
- Risk: MySQL-specific SQL in migrations breaks sqlite — Mitigation: ensure guards; run sqlite migrations in CI early.
- Risk: External API credential leaks — Mitigation: use secrets, mock in tests.
- Risk: Incomplete audit trail — Mitigation: implement AuditObserver early; add tests asserting audit entries created.

Acceptance criteria for Phase 3 MVP demo
- Auth: register/login/logout working via `/api/v1`.
- PDPA: users can give consent; audit logs created for profile update; user data export endpoint exists.
- Centers: list endpoint works (localized if available).
- Booking: booking create flow creates Booking model and enqueues/schedules notification jobs (external API calls mocked).
- Tests: core unit + feature tests pass in CI (sqlite migrations run before tests).
- OpenAPI: minimal spec present under `backend/storage/api-docs/openapi.yaml`.

Notes & references
- Consult: `docs/phase3-progress-matrix.*` for the current gap list.
- Do not re-apply migration guards already listed in on-boarding doc for guarded migrations.
- If uncertain about a PDPA decision, halt and request compliance clarification rather than guessing.

Next actionable step (recommended)
- Create branch `feature/phase3-api-init` and implement `app/Http/Responses/ApiResponse.php`, `app/Observers/AuditObserver.php`, and `app/Services/User/DataExportService.php` with unit tests. Then update CI to run sqlite migrations and run the test suite.

End of briefing.