# Phase 3 Gaps & Prioritized Next Steps

This document distills critical gaps discovered during the Phase‑3 plan vs code review and ranks next steps by priority, impact, and estimated effort. It includes exact commands and recommended PR titles so an incoming agent can act quickly.

## Executive summary (top 5 gaps)
1. CI does not prepare/run sqlite migrations before tests — high priority. Without this, migration incompatibilities slip through CI.
2. Service layer mostly missing — CenterService, BookingService, MediaService, TestimonialService, etc. These are essential for Phase‑3 deliverables.
3. API controllers and request/resource classes are missing under `app/Http/Controllers/Api/V1` and `app/Http/Requests` — blocks API completion and docs generation.
4. Tests are placeholders — unit & feature tests required to reach coverage targets and prevent regressions.
5. OpenAPI spec and Postman collection not generated — blocks API docs and demos.

## Prioritized action list

1) Add CI sqlite migration step (HIGH)
- Why: Catch migration incompatibilities early; enforce migrations run on CI before tests.
- Estimated time: 15–45 minutes to implement and verify in CI workflow.
- Owner: DevOps / Agent
- PR title: `chore(ci): run sqlite migrations before tests`
- Steps to implement:

```yaml
# In .github/workflows/ci.yml (or equivalent)
- name: Prepare sqlite DB
  run: |
    cd backend
    mkdir -p database
    touch database/database.sqlite
    composer install --no-interaction --prefer-dist
    php artisan migrate --database=sqlite --force
```

- Verification:
  - Run GitHub Action or run locally in same runner image; ensure `php artisan migrate --database=sqlite` completes.

2) Run repo-wide audit for MySQL-specific SQL (HIGH)
- Why: Ensure no remaining `DB::statement`, `FULLTEXT`, `CHECK`, or ALTER statements will fail on SQLite.
- Estimated time: 10–30 minutes to search + triage.
- Command:

```bash
grep -R "DB::statement\|FULLTEXT\|fullText\|CHECK\|ALTER TABLE" -n || true
```

- If finds exist, apply guard:

```php
if (DB::getDriverName() === 'mysql') {
    DB::statement("...mysql sql...");
}
```

3) Implement missing Service layer stubs (MEDIUM)
- Why: Services are needed for business logic, testing, and separation of concerns.
- Scope: create minimal method signatures for CenterService, BookingService, MediaService, TestimonialService, ConsentService already exists.
- Estimated time: 2–6 hours (create stubs + minimal tests)
- PR title: `feat(services): add service stubs for centers bookings media testimonials`
- Verification: ensure `php artisan test` + `php artisan migrate` run; write one unit test per service verifying method exists.

4) Add API controllers & requests (MEDIUM)
- Why: Controllers are required for API routes, docs, and feature tests.
- Estimated time: 3–8 hours (create controllers, form requests, and resources for core endpoints)
- PR title: `feat(api): implement core API controllers and requests`
- Verification: run feature tests for auth/center endpoints (to be added)

5) Write tests & factories (HIGH for coverage)
- Why: Required to reach ≥90% coverage and protect future changes.
- Estimated time: 1–3 days depending on depth.
- PR title: `test: add unit and feature tests for core models and services`
- Verification: `php artisan test --coverage --min=90`

6) Generate OpenAPI & Postman (LOW-MED)
- Why: API docs needed for demos and integration with frontend.
- Steps: implement `GenerateApiDocsCommand`, use route inspection or annotations to build `openapi.yaml`, and export Postman collection.
- Estimated time: 4–8 hours
- PR title: `chore(docs): generate OpenAPI and Postman collection`

## Short-term quick wins (can be done now)
- Create GitHub Action step to run sqlite migrations before tests (1)
- Run grep audit for DB-specific SQL and patch simple cases (2)
- Create minimal service stubs for BookingService and CenterService with no-op methods and tests (3)

## Verification commands (copy/paste)

```bash
# Create sqlite db and run migrations locally (backend)
mkdir -p backend/database
touch backend/database/database.sqlite
cd backend
composer install --no-interaction
php artisan migrate --database=sqlite --force
php artisan test

# Run grep audit
grep -R "DB::statement\|FULLTEXT\|fullText\|CHECK\|ALTER TABLE" -n backend || true
```

## File owners & recommended PR reviewers
- CI changes: DevOps + Backend Maintainers
- Migration fixes: Backend Maintainers
- Services & Controllers: Backend Devs (assign to `@backend-team`)
- Tests & Docs: QA + Backend Devs

## Follow-ups & longer-term work (Phase 4+)
- Implement full test coverage and CI gating for coverage
- Add staging deployment, smoke tests, and scheduled cron jobs
- Integrate frontend with API and finalize demo data

---

Generated: 2025-10-13
