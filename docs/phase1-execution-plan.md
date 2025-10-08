# Phase 1 Execution Plan — Foundation, Infrastructure & Analytics

## 1. Objective
Deliver the Phase 1 outcomes defined in `codebase_completion_master_plan.md`, ensuring ElderCare SG has a production-grade foundation across configuration, infrastructure, analytics, and documentation.

## 2. Audit Summary (2025-10-08)
| Workstream | Current State | Required Action |
| --- | --- | --- |
| **Root configuration** (`package.json`, `composer.json`, `docker-compose.yml`) | Present and aligned with Phase 1 target stack (Next.js + Laravel + Docker) | Spot-check dependency versions against PAD before release. |
| **Environment templates** (`.env.example`, `frontend/.env.local.example`, `backend/.env.example`) | Base templates exist; no `staging`/`production` variants or secrets guidance | Create `.env.staging`, `.env.production`, `frontend/.env.local.staging`, `frontend/.env.local.production`; add environment guidance in `docs/deployment/README.md`. |
| **Directory structure** (`frontend/`, `backend/`, `docker/`, `docs/`, `terraform/`, `.github/`) | Present and populated | Document directory purpose in `README.md` appendix. |
| **Git configuration** (`.gitignore`, `.gitattributes`) | Present | Add branch protection policy doc (`docs/git-workflow.md`) referenced in master plan. |
| **Database migrations** | Comprehensive migrations delivered (see `database/migrations/2025_*`) | Pending validation: run `php artisan migrate:fresh` smoke test. |
| **Terraform / AWS infra** (`terraform/`) | Core modules (`main.tf`, `variables.tf`, `outputs.tf`, `staging.tfvars`) exist | Review for Phase 1 parity: document missing modules (e.g., CloudFront/Cloudflare interop), add README with apply instructions. |
| **Cloudflare CDN configuration** | No explicit IaC or documentation detected | Create `docs/deployment/cloudflare.md` outlining DNS, CDN, WAF steps; evaluate Terraform integration feasibility. |
| **CI/CD pipeline** (`.github/workflows/ci.yml`, `deploy.yml`) | Comprehensive CI pipeline; staging deploy workflow exists | Confirm staging deploy job matches Phase 1 acceptance (auto deploy on `main`); update secrets checklist. |
| **Analytics & monitoring** | No GA4/Hotjar script stubs or config files found; Sentry/New Relic not wired | Create `frontend/src/lib/analytics/ga.ts`, `frontend/src/lib/analytics/hotjar.ts`; add backend `config/sentry.php` stub; document DSN/env requirements. |
| **Test infrastructure** | Jest/Playwright configs present in frontend; PHPUnit in backend | Add Playwright/Lighthouse CI badges to README; ensure axe-core integration (verify `package.json` scripts). |
| **Documentation** (`README.md`, `CONTRIBUTING.md`, `docs/`) | README rich; CONTRIBUTING missing; new plan docs exist | Draft `CONTRIBUTING.md`; update README Phase 1 checklist; add runbook references. |

## 3. Revised Task Plan
1. **Environment & Secrets Hardening**
   - Create environment templates for staging/production (root, backend, frontend, Docker).
   - Update `docs/deployment/README.md` with environment variable catalog and secrets sourcing.

2. **Infrastructure Artifacts**
   - Enhance Terraform: document module responsibilities, ensure ECS/RDS/ElastiCache outputs align with PAD.
   - Produce `docs/deployment/cloudflare.md` covering DNS, caching, WAF, and Terraform integration notes.

3. **CI/CD & Git Workflow**
   - Review `.github/workflows/deploy.yml` to ensure staging deploy triggers on `main` merges; update secret requirements list.
   - Add `docs/git-workflow.md` describing branch protection, PR checklist, and CI expectations.

4. **Analytics & Monitoring Bootstrap**
   - Implement GA4/Hotjar helper modules in `frontend/src/lib/analytics/` with feature flag toggles.
   - Add Sentry and New Relic configuration placeholders: `frontend/src/lib/monitoring/sentry.ts`, backend `config/sentry.php`, `config/newrelic.php` (if required), and `.env` keys.
   - Document integration steps in `docs/deployment/monitoring.md` (new file).

5. **Testing Infrastructure Enhancements**
   - Verify Playwright, Lighthouse CI, Percy, and axe-core scripts exist; add missing npm scripts or configs.
   - Update README badges and testing section to reflect available tooling.

6. **Documentation Refresh**
   - Author `CONTRIBUTING.md` aligning with collaboration protocol.
   - Append README Phase 1 summary and quickstart instructions (Docker + migrations).
   - Reference new docs in `docs/AGENT.md` change log.

## 4. Validation Strategy
- **Environment**: Run `cp` commands to ensure new templates copy cleanly; perform `.env` diff audit.
- **Infrastructure**: Execute `terraform validate` for updated modules; dry-run `terraform plan -var-file=staging.tfvars`.
- **CI/CD**: Trigger GitHub Actions workflow via branch to confirm lint/test/deploy sequences; check secret resolution.
- **Analytics/Monitoring**: Run frontend build verifying GA4/Sentry stubs tree-shake when disabled; confirm backend config passes `php artisan config:cache`.
- **Testing**: Execute `npm run test`, `npm run test:e2e`, `npm run lighthouse`, and backend `php artisan test` to ensure scripts succeed.
- **Documentation**: Peer review new docs for clarity; ensure cross-links added to `README.md` and `docs/AGENT.md`.

## 5. Next Steps
1. Execute tasks listed in Section 3, capturing updates via TODO tracker.
2. After implementation, rerun validation steps and record outcomes in this file.
3. Provide summary update to stakeholders referencing this plan.
