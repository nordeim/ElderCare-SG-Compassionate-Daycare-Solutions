# ElderCare SG — Pre-Phase 3 Remediation Execution Plan (2025-10-10)

## 1. Objectives
- Close outstanding technical and documentation gaps before commencing Phase 3 of the `codebase_completion_master_plan.md` (backend services & PDPA compliance).
- Ensure foundational telemetry, infrastructure, and frontend QA guardrails are production-ready to support upcoming authentication and consent workflows.
- Mitigate migration/runtime risks identified during schema review to allow frictionless local, CI, and staging deployments.

## 2. Gap Summary (Source Trace)
- **DB driver compatibility**: Raw MySQL `ALTER TABLE ... CHECK` statements in `database/migrations/2025_10_08_133000_create_services_table.php`, `2025_10_08_140000_create_bookings_table.php`, `2025_10_08_141000_create_testimonials_table.php` lack driver guards; will fail under SQLite used by PHPUnit (`project_state_recap_2025-10-10.md`).
- **Analytics & monitoring bootstrap**: GA4, Hotjar, Sentry, New Relic helper modules plus env/secrets guidance remain TODO per `docs/phase1-execution-plan.md` §3.4.
- **Cloudflare & Terraform documentation gaps**: `docs/phase1-execution-plan.md` §3.2 calls for `docs/deployment/cloudflare.md` and enhanced Terraform README/apply runbook.
- **Design system QA completion**: `docs/phase2-execution-subplan.md` highlights pending Storybook story coverage (Card/Form composites, navigation polish), Percy baseline capture (awaiting `PERCY_TOKEN`), and localized testing improvements.
- **Phase 3 readiness gaps**: Need auth/consent service scaffolds, rate limiting plan, API contract alignment, plus secrets availability to avoid delays when Phase 3 starts (`codebase_completion_master_plan.md` Phase 3 acceptance criteria).

## 3. Scope & Assumptions
- Focus limited to remediation enabling Phase 3 start; does not implement Phase 3 features themselves.
- Team composition per master plan: Backend Dev 1, Backend Dev 2, Frontend Dev, QA Engineer; availability assumed in parallel blocks.
- Environment targets: Local (Docker, SQLite for tests), Staging (MySQL, Redis), Production parity via Terraform.
- Percy token and analytics secrets to be provided by stakeholders before execution; pending items highlighted in risks.

## 4. Workstreams Overview
| Workstream | Goal | Primary Owner | Key Dependencies | Estimated Duration |
| --- | --- | --- | --- | --- |
| **A. Migration Hardening** | Ensure migrations run under MySQL & SQLite | Backend Dev 1 | `database/migrations/*.php` | 0.5 day |
| **B. Analytics & Monitoring Bootstrap** | Deliver GA4, Hotjar, Sentry, New Relic scaffolding | Frontend Dev + Backend Dev 2 | Secrets provision, `frontend/src/lib`, `backend/config` | 1.5 days |
| **C. Infrastructure Documentation & IaC Parity** | Document Cloudflare workflow & Terraform usage | Backend Dev 2 | `terraform/`, `docs/deployment/` | 1 day |
| **D. Design System QA Closure** | Finish Storybook coverage, Percy baseline, axe automation | Frontend Dev + QA | `frontend/src/stories`, `.percy.yml`, CI config | 1.5 days |
| **E. Phase 3 Enablement Prep** | Stage auth/consent groundwork & rate limiting guardrails | Backend Dev 1 + Backend Dev 2 | Workstreams A–D outputs, secrets readiness | 2 days |

## 5. Workstream Detail

### Workstream A — Migration Hardening
- **Goal**: Cross-database safe migrations.
- **Tasks**:
  - **[A1]** Introduce driver checks before running `DB::statement` constraint additions (MySQL only) in services/bookings/testimonials migrations.
  - **[A2]** Add regression PHPUnit test: `php artisan migrate:fresh --database=sqlite` executed in CI matrix.
  - **[A3]** Update `docs/database-migration-execution-plan.md` with note on driver checks.
- **Deliverables**: Updated migrations with conditional guards; CI job verifying SQLite migrations.
- **Validation**: Local `php artisan test --database=sqlite`, GitHub Actions matrix green.
- **Risks**: Constraint coverage drift—mitigate by documenting parity in `project_state_recap_2025-10-10.md` addendum.

### Workstream B — Analytics & Monitoring Bootstrap
- **Goal**: Ready-to-configure telemetry stack.
- **Tasks**:
  - **[B1]** Build `frontend/src/lib/analytics/{ga,hotjar}.ts` and toggled initialization per env.
  - **[B2]** Create backend configs `backend/config/{sentry.php,newrelic.php}` + service providers.
  - **[B3]** Add env templates (`.env.staging`, `.env.production`, `frontend/.env.local.staging`) with placeholder keys.
  - **[B4]** Document setup in `docs/deployment/monitoring.md` (per Phase 1 plan) with activation checklist.
  - **[B5]** Update CI to fail if telemetry toggles missing (lint/test step verifying env keys when flags enabled).
- **Deliverables**: Modules, config files, env templates, documentation, CI guard.
- **Validation**: `npm run lint && npm run build` verifying dead code elimination, `php artisan config:cache`, manual smoke verifying toggles.
- **Risks**: Secrets not provisioned—flag to stakeholders, allow feature-flag fallback.

### Workstream C — Infrastructure Documentation & IaC Parity
- **Goal**: Repeatable staging/prod provisioning instructions.
- **Tasks**:
  - **[C1]** Draft `docs/deployment/cloudflare.md` covering DNS, CDN, WAF, terraform integration decision.
  - **[C2]** Enhance `terraform/README.md` with module descriptions, apply/destroy runbooks, state management instructions.
  - **[C3]** Audit Terraform modules for Phase 1 parity; log missing resources (e.g., CloudFront interop) in backlog.
  - **[C4]** Add validation checklist (run `terraform validate`, `terraform plan -var-file=staging.tfvars`).
- **Deliverables**: Updated docs, backlog issues for missing modules, CI/manual validation log.
- **Validation**: `terraform validate` output appended to documentation; peer review sign-off.
- **Risks**: Terraform state access; coordinate with DevOps lead.

### Workstream D — Design System QA Closure
- **Goal**: Complete frontend QA guardrails before backend integration work.
- **Tasks**:
  - **[D1]** Add missing stories (`Card`, `FormField` composites, `NavigationBar`, `LanguageSwitcher` analytics events).
  - **[D2]** Extend Jest RTL tests for localized copy & reduced-motion variants.
  - **[D3]** Configure Percy baseline run once `PERCY_TOKEN` supplied; document fallback.
  - **[D4]** Ensure Storybook a11y checks cover new stories; update `docs/phase2-status-checklist.md`.
  - **[D5]** Update CI to conditionally run Percy + Storybook test runner.
- **Deliverables**: Stories/tests committed, Percy baseline snapshot, updated checklist.
- **Validation**: `npm run test`, `npm run storybook:test`, `npm run percy:storybook` (conditional) with QA sign-off.
- **Risks**: Token absence—document deferred baseline and schedule once available.

### Workstream E — Phase 3 Enablement Prep
- **Goal**: Remove blockers for authentication/PDPA build-out.
- **Tasks**:
  - **[E1]** Define authentication & consent service architecture notes in `docs/backend/phase3-kickoff.md` (new) referencing PAD.
  - **[E2]** Prepare Laravel Sanctum setup skeleton, rate limiting middleware configuration (`app/Http/Kernel.php`), and queue retry policies.
  - **[E3]** Draft OpenAPI skeleton for `/api/v1` endpoints covering auth + consent flows.
  - **[E4]** Confirm secrets inventory (mail, SMS, Calendly, Twilio) and create `docs/deployment/secrets-checklist.md` update.
  - **[E5]** Conduct stakeholder review meeting; log decisions in doc.
- **Deliverables**: Architecture doc, code scaffolds (feature branches), OpenAPI draft, secrets checklist update, meeting notes.
- **Validation**: Peer review of doc/UI, `php artisan test` for scaffolds, alignment sign-off captured in `docs/backend/phase3-kickoff.md`.
- **Risks**: Scope creep into implementation—enforce boundary (scaffolds only).

## 6. Cross-Cutting Governance
- **Daily Stand-up**: 15 min to track workstream blockers (owner-led).
- **Mid-Remediation Review**: After Workstreams A–C complete, confirm readiness to proceed (target T+2 days).
- **Final Readiness Review**: Present validation evidence, secure go/no-go for Phase 3 start.
- **Documentation Updates**: Every deliverable includes doc link in `project_state_recap_2025-10-10.md` addendum.

## 7. Timeline & Sequencing (Calendar Days)
| Day | Activities |
| --- | --- |
| Day 0 (Today) | Kickoff, assign owners, confirm secrets availability.
| Day 1 | Workstream A + Workstream C1/C2 progress; start Workstream B1.
| Day 2 | Complete Workstream B (modules/docs), run telemetry validation; finish Workstream C.
| Day 3 | Execute Workstream D tasks, run Percy baseline (if tokens ready); start Workstream E scaffolds.
| Day 4 | Finalize Workstream E documentation, stakeholder review, consolidate validation logs.
| Day 5 | Buffer for fixes, final readiness review, update recap.

## 8. Success Metrics & Exit Criteria
- **Migration Safety**: CI passes with MySQL + SQLite runs; zero migration regressions in smoke tests.
- **Telemetry Readiness**: Feature flags default off, toggled on with secrets -> builds succeed; docs complete.
- **Infrastructure Docs**: Terraform + Cloudflare runbooks peer-approved; `terraform validate` logs stored.
- **Design System QA**: 100% targeted components covered in Storybook with axe clean run; Percy baseline captured or documented.
- **Phase 3 Prep**: Auth/consent skeleton + rate limiting configs merged; OpenAPI draft reviewed; secrets checklist confirmed.

## 9. Next Steps
1. Share plan with stakeholders for sign-off.
2. Create workstream-specific tickets referencing this plan.
3. Begin execution following timeline; update plan as tasks close.
4. Maintain evidence in `docs/llm/` and update `project_state_recap_2025-10-10.md` under "Next Steps" after completion.

Prepared by Cascade AI Assistant • 2025-10-10 06:27 SGT
