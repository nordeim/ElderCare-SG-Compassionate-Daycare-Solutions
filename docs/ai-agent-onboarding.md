## AI Agent Onboarding — ElderCare SG

Purpose
- This document is a single-source onboarding guide for an autonomous AI coding agent (or human engineer acting as an agent) that must begin work on the Pre‑Phase‑3 remediation and Phase‑3 backend tasks.
- The guide equips the agent with the project snapshot, environment steps, high‑priority tasks, coding conventions, testing and CI requirements, and a PR-ready checklist so the agent can open a clean, reviewable PR.

Audience and assumptions
- Audience: automated coding agents and engineers responsible for Phase‑3 enablement and remediation.
- Assumptions: You have a working development environment (Linux), Docker & Docker Compose, Node 18+, PHP 8.2+, Composer, and access to the repository. Secrets such as `PERCY_TOKEN`, `SENTRY_DSN`, `NEWRELIC_LICENSE_KEY` may be missing — telemetry must be gated by env flags.

Repository snapshot (quick)
- Root: `/Home1/project/web-platform`
- Frontend: `frontend/` (Next.js 14, React 18, TypeScript, Tailwind)
- Backend: `backend/` (Laravel 12, PHP 8.2)
- Database: MySQL 8.0 (production); CI/tests use SQLite in some jobs
- Key docs: `codebase_completion_master_plan.md`, `docs/llm/pre_phase3_remediation_execution_plan_2025-10-10.md`, `docs/llm/phase3-coding-sub-plan-v2.md`, `database_schema.sql`

Current state & context
- Phase 1 & 2 are completed. The project is in Pre‑Phase‑3 Remediation: close migration compatibility issues, bootstrapping analytics/monitoring, improving docs, and scaffolding Phase‑3 service skeletons (Auth/Consent) so Phase‑3 work can begin.
- Critical blockers:
  - DB driver incompatibility in some migrations (MySQL-specific ALTER/STATEMENTs that fail on SQLite)
  - Telemetry scaffolding and missing secrets
  - Cloudflare/Terraform docs not yet complete

Top priorities for a new agent (ordered)
1. Migration hardening — make migrations safe for non-MySQL drivers used in CI/tests.
2. Add CI job or workflow step to run sqlite migrations and tests so CI catches migration regressions.
3. Telemetry & monitoring bootstrap — add guarded stubs for GA4/Hotjar (frontend) and Sentry/NewRelic (backend) plus `.env.example` updates.
4. Docs: add `docs/deployment/cloudflare.md` and augment `terraform/README.md`.
5. Phase‑3 scaffolds: AuthService, ConsentService stubs, route placeholders, and OpenAPI skeletons.

First actionable task (detailed)
- Branch name: `feature/remediation/migration-hardening`
- Goal: make the three identified migrations DB-driver safe and ensure tests run on CI.
- Files to edit:
  - `backend/database/migrations/2025_10_08_133000_create_services_table.php`
  - `backend/database/migrations/2025_10_08_140000_create_bookings_table.php`
  - `backend/database/migrations/2025_10_08_141000_create_testimonials_table.php`
- Change pattern (apply to each file):
  - Create the table schema via `Schema::create(...)` for all drivers.
  - Wrap any MySQL-specific `DB::statement(...)` calls (FULLTEXT, CHECK, ALTER) in a guard that only runs when `DB::getDriverName() === 'mysql'`.
  - For example:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

public function up()
{
    Schema::create('services', function (Blueprint $table) {
        // columns ...
    });

    // MySQL-specific statements
    if (DB::getDriverName() === 'mysql') {
        DB::statement("ALTER TABLE `services` ADD FULLTEXT INDEX `idx_search` (`name`, `description`)");
    }
}
```

- Local verification commands (Linux shell):

```bash
# start containers
docker-compose up -d

# run sqlite migrations for tests
docker-compose exec backend php artisan migrate --database=sqlite --force

# run backend tests
docker-compose exec backend php artisan test
```

CI change (recommended)
- Add a CI step that prepares a sqlite database file and runs migrations before tests. Example (high-level):

```yaml
- name: Prepare sqlite DB
  run: |
    cd backend
    touch database/database.sqlite
    php artisan migrate --database=sqlite --force
```

Telemetry & secrets guidance
- Guard telemetry using env flags. Example in PHP:

```php
if (env('SENTRY_DSN')) {
    // initialize Sentry
}
```

Keep telemetry optional in CI and local runs. Do not fail tests if telemetry secrets are missing.

Phase‑3 scaffolds (minimal files to create)
- `backend/app/Services/AuthService.php` (skeleton with register/login/logout signatures)
- `backend/app/Services/ConsentService.php` (capture/withdraw/version stubs)
- `backend/routes/api.php` — add `/api/v1/auth` and `/api/v1/consent` route placeholders returning a standardized JSON structure
- `docs/backend/phase3-kickoff.md` — OpenAPI snippets for auth & consent

Coding conventions & best practices (must follow)
- Thin controllers, heavy service layer in `app/Services` or `app/Domain/*`.
- Use FormRequest validation classes for API inputs.
- Use Observers (e.g., `AuditObserver`) to record audit logs for model changes.
- No live external API calls in tests. Mock HTTP clients or use Laravel HTTP Client fakes.
- Write unit and feature tests for new logic; target >90% backend coverage for Phase‑3.

PR template & checklist (agent must fill)
- Title format: `feature|bugfix/<short>-<task>` e.g., `feature/remediation/migration-hardening`
- Description must include: intent, files changed, test steps, and risk notes.
- Checklist to complete in PR description:
  - [ ] Branch name follows convention
  - [ ] Local tests pass (php artisan test)
  - [ ] Migration guarded for non-mysql drivers
  - [ ] CI updated (sqlite migration) or unaffected
  - [ ] No external API calls in tests
  - [ ] Doc updates included (if applicable)

Testing guidance
- Use factories & seeders for fixtures.
- For migration hardening, add a small feature test that runs `artisan migrate --database=sqlite` to verify no schema errors.
- Mock external services with `Http::fake()` and assert request shapes.

Risk register (top 3)
1. Missing secrets — telemetry should be gated. Create an ISSUE to request tokens from ops if needed.
2. Schema drift — add sqlite run in CI and follow the migration guard pattern.
3. PDPA non-compliance — ensure AuditService/ConsentService remains wired where data-modifying endpoints are added.

Useful files & docs (read first)
- `codebase_completion_master_plan.md`
- `docs/llm/pre_phase3_remediation_execution_plan_2025-10-10.md`
- `docs/llm/phase3-coding-sub-plan-v2.md`
- `database_schema.sql`
- `backend/database/migrations/`

Handoff & escalation guidance
- If you need CI tokens or Percy / Sentry keys, create an ISSUE and assign to `DevOps` + mention `Compliance` for PDPA approvals.
- If tests fail with unexplained SQL errors, run `php artisan migrate:fresh --database=sqlite` and attach output to the PR.

Acceptance criteria for the onboarding agent's first PR
- All backend tests pass locally and in CI (including sqlite migrations).
- Migration files are safe for sqlite & mysql.
- PR description includes test steps and risk notes.

Short-term recommended PRs (priority order)
1. `feature/remediation/migration-hardening` (migrations + CI sqlite step)
2. `chore/telemetry/bootstrap` (telemetry stubs and `.env.example` updates)
3. `docs/deployment/cloudflare.md` + `terraform/README.md` enhancements
4. `feature/phase3-auth-scaffold` (AuthService/ConsentService skeletons + route stubs)

Contact & ownership
- Repo maintainers: ElderCare Dev Team (tag in PR reviewers)
- DevOps: assign in ISSUE when secrets are needed
- Legal/Compliance: assign in PR when PDPA-sensitive work is introduced

Appendix: example PHP guard snippet

```php
use Illuminate\Support\Facades\DB;

if (DB::getDriverName() === 'mysql') {
    DB::statement("ALTER TABLE `services` ADD FULLTEXT INDEX `idx_search` (`name`, `description`)");
}
```

---

Document version: 2025-10-13
Author: AI Agent (onboarding guide)
