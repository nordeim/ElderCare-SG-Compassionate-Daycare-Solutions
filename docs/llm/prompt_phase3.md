# ElderCare SG — Pre-Phase 3 Remediation Execution Plan

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

---

# Phase 2 Execution Sub-Plan — Design System, UI Components & i18n

**Source Alignment:**
- `codebase_completion_master_plan.md` — Phase 2 objectives (Design System, UI Components & i18n).
- `docs/AGENT.md` — Sections 3, 5, 9, 10: frontend blueprint, data/integration guidance, accessibility & i18n standards, QA guardrails.
- `Project_Architecture_Document.md` — §§5.2, 5.6, 10.0, 11.0, 16.0, 17.0: frontend architecture, progressive enhancement, localization, content management, accessibility, testing strategy.

---

## 1. Strategic Overview
- **Objective:** Deliver the reusable design system, component library, and multilingual foundation described in Phase 2 of the master plan to unblock downstream phases (frontend pages, content management, booking flows).
- **Success Drivers:** Accessibility-first UI (WCAG 2.1 AA), scalable design tokens, internationalization for English & Mandarin with extensibility toward Malay/Tamil, comprehensive component documentation/testing, analytics instrumentation hooks.
- **Dependencies:** Completed Phase 1 infrastructure & analytics, established Docker-based development workflow, Laravel backend API scaffolding (for Storybook mocks & integration tests).

## 2. Current State Assessment (2025-10-09)
- `frontend/tailwind.config.ts` currently exposes only minimal color tokens; lacks semantic palettes, typography scales, spacing ramps, and Radix token mapping demanded by `docs/design-system/`.
- No `frontend/src/locales/` directory or `next-intl` configuration present; `middleware` and routing do not yet enforce locale prefixes referenced in `Project_Architecture_Document.md` (§5.4, §10.0).
- Component directories (`frontend/src/components/`) contain only starter layout sections; base UI primitives, form controls, and layout scaffolding listed in the master plan are absent.
- Storybook configuration (`frontend/.storybook/`) not present; storytelling/testing infrastructure pending.
- Accessibility and visual regression suites (axe-core snapshots, Percy baselines) have not been connected to component-level workflows.

## 3. Guiding Principles
1. **Design Tokens First:** Translate the palettes & typography defined in `docs/design-system/` into Tailwind + CSS custom properties so all components inherit accessible defaults (per `docs/AGENT.md` §3 and `Project_Architecture_Document.md` §5.5, §16.0).
2. **i18n as Structural Concern:** Locale routing, translation loading, and content negotiation must be completed before component proliferation to avoid retrofitting (per master plan success criteria & PAD §10.0).
3. **Composable Component Architecture:** Adopt atom→molecule→organism layering to align with PAD §5.2 directory expectations and support Storybook documentation.
4. **Accessibility & QA Embedded:** Every component story/test must include axe assertions, Jest snapshot/interaction tests, and Percy diff coverage (per `docs/AGENT.md` §10, PAD §16.0, §17.0).
5. **Analytics Hooks:** Components expose analytics-ready data attributes for GA4/Hotjar instrumentation (per AGENT §3 and §9).

## 4. Execution Workstreams & Milestones

### Workstream A — Design Tokens & Theming (Days 1-2)
- Define global CSS variables and Tailwind extensions (colors, typography, spacing, radii, shadows) according to `docs/design-system/` and PAD §5.5.
- Introduce dark/high-contrast variants (feature-flagged) per accessibility roadmap.
- Deliver theme documentation page in Storybook referencing tokens.
- **Remediation Plan (2025-10-09 21:17 SGT):**
  1. Implement `frontend/src/styles/design-tokens.css` with semantic color/typography/spacing variables mapped to Tailwind via `tailwind.config.ts`.
  2. Add high-contrast custom properties and opt-in selectors aligned with accessibility roadmap.
  3. Create `src/stories/Foundation/Tokens.stories.tsx` documenting palette and typography usage.
  4. Validate via `npm run build-storybook` and update documentation once tokens propagate through components.
- **Status Log (2025-10-09 21:24 SGT):**
  - High-contrast variables added to `design-tokens.css`; Tailwind extensions already consume semantic tokens in `tailwind.config.ts`.
  - `src/lib/theme/tokens.ts` exports typed token maps for runtime utilities.
  - `src/stories/Tokens.stories.tsx` ships a design-token reference page; `npm run build-storybook` succeeds, confirming Storybook integration.
  - Next functional focus: expand Workstream C coverage (Card/FormField stories) and align remaining layout components with tokens.

### Workstream B — Internationalization Layer (Days 2-3)
- Configure `next-intl` (routing middleware, locale detection, provider wrapper).
- Create translation namespaces (`common`, `navigation`, `forms`, `errors`) for English (`en`), Mandarin (`zh`); provision scaffolds for Malay (`ms`) and Tamil (`ta`) per PAD §10.0.
- Build language switcher component with keyboard navigation and analytics events.
- **Status Log (2025-10-09 11:23 SGT):**
  - Root config (`next-intl.config.ts`, `src/lib/i18n/request.ts`) integrated with `createNextIntlPlugin` in `next.config.mjs`; `middleware` & locale-aware layouts redirect correctly.
  - All homepage sections (`header`, `hero`, `program-highlights`, `footer`) consume translations; language switcher operational across desktop/mobile.
  - Build completes successfully but emits `ENVIRONMENT_FALLBACK` warnings. Suspected cause: at least one server component still calls `useTranslations()` without being client-only (investigate remaining layout/section imports). TODO captured in Workstream B follow-ups.
  - Follow-up actions:
    1. Audit any server components invoking `useTranslations()`; either mark `'use client'` or refactor to fetch translations server-side.
    2. Re-run `npm run build` post-audit to confirm warnings cleared; document outcome here.
    3. Update QA checklist once warning-free build achieved.
- **Status Log (2025-10-09 12:02 SGT):**
  - Translation lookup moved server-side: `app/[locale]/page.tsx` now gathers localized copy via `getTranslations()` and passes typed props to `Header`, `Hero`, `ProgramHighlights`, `Footer`, and `LanguageSwitcher`.
  - Client components no longer call `useTranslations()`/`useLocale()`, eliminating `ENVIRONMENT_FALLBACK` warnings during static generation.
  - `npm run build` (see run at 12:01 SGT) completes cleanly with all locale pages prerendered; update recorded in QA checklist.

### Workstream C — Component Library (Days 3-6)
- **Atoms:** Button, Icon, Badge, Input, Label, Toggle, Checkbox, Radio, Select, Textarea.
- **Molecules:** FormField, Fieldset, Dropdown, ModalTrigger, Alert, Toast, Breadcrumbs.
- **Organisms/Layout:** Header, Footer, NavigationBar, PageLayout, HeroBanner, SectionContainer.
- Embed accessibility patterns (focus rings, ARIA attributes, reduced motion) as mandated by PAD §16.0 and AGENT §3.

### Workstream D — Documentation & Tooling (Days 6-7)
- Setup Storybook (`.storybook/main.ts`, `preview.ts`, `tsconfig.storybook.json`) with dark/light & locale toggles.
- Write component stories with localization controls and design notes referencing tokens.
- Integrate design linting (e.g., Tailwind plugin for design tokens) and Chromatic/Percy pipeline hooks.
- **Status Log (2025-10-09 12:44 SGT):**
  - Storybook bootstrapped with Next.js framework (`.storybook/main.ts`, `.storybook/preview.ts`, `.storybook/tsconfig.json`); global theme and locale toolbars configured.
  - Addons installed (`essentials`, `interactions`, `a11y`) and npm scripts (`storybook`, `build-storybook`) available.
  - `build-storybook` currently fails with Webpack hook error `SB_BUILDER-WEBPACK5_0002` (Preview build cannot read `compiler.cache.hooks.shutdown.tap`). Suspected Next.js 14.2.33 × Storybook 8.6 compatibility issue.
  - Follow-ups:
    1. Investigate Storybook builder compatibility (test `@storybook/nextjs` downgrade or Vite builder).
    2. Capture repro logs and open upstream issue if Next.js integration patch not available.
    3. Block Chromatic/Percy pipeline work until preview build is stable.
- **Status Log (2025-10-09 18:58 SGT):**
  - Migrated Storybook to Vite builder with updated configuration (`.storybook/main.ts`, `.storybook/preview.tsx`, `.storybook/tsconfig.json`).
  - Alias `@` resolved to `src/` for story imports; Tailwind design token import order fixed in `src/app/globals.css` to satisfy Vite.
  - `npm run build-storybook` now succeeds; bundle artifacts generated under `frontend/storybook-static/` with minor warnings noted for large chunks.
- **Coverage Audit (2025-10-09 19:02 SGT):**
  - **Stories present:** `Button`, `Checkbox`, `Input`, `Radio`, `Toggle` (`src/stories/`).
  - **UI gaps:** `Card`, `Label`, `FormField`-based controls (`forms/checkbox-field.tsx`, `forms/radio-field.tsx`), `Section` components.
  - **Enhancement opportunities:** add locale-aware copy, surface theme variations via `context.globals.theme`, and document accessibility interactions per component.
- **Status Log (2025-10-09 21:28 SGT):**
  - Added `src/stories/FormField.stories.tsx` demonstrating helper/error messaging propagation with localized copy.
  - Token showcase `src/stories/Tokens.stories.tsx` (from Workstream A) referenced to validate color/spacing usage across molecules.
  - `npm run build-storybook` passes post-update; downstream stories to tackle next include layout/section components.
- **Status Log (2025-10-09 21:34 SGT):**
  - Created `src/stories/Hero.stories.tsx` to cover the hero section with locale-aware CTAs; validated high-contrast token usage.
  - Rebuilt Storybook successfully, confirming new section story packaging. Next targets: `Footer` and `ProgramHighlights` stories.
- **Status Log (2025-10-09 21:41 SGT):**
  - Added `src/stories/Footer.stories.tsx` and `src/stories/ProgramHighlights.stories.tsx` for layout/section coverage with localized copy.
  - `npm run build-storybook` remains green after additions; remaining gap for Workstream C is navigation/header refinement.
- **Status Log (2025-10-09 21:47 SGT):**
  - Shipped `src/stories/Header.stories.tsx` with overview/mobile variants and toolbar-driven locale switching.
  - `npm run build-storybook` passes; Workstream C component coverage now complete pending lint cleanup.
- **Testing Audit (2025-10-09 19:28 SGT):**
  - **Existing suites:** `src/components/ui/__tests__/atoms.test.tsx` (Buttons, Inputs, Toggles basic rendering).
  - **Gaps:** no dedicated tests for `Card` molecule, `CheckboxField`/`RadioField` accessibility props, or locale-driven copy.
  - **Next actions:** add RTL tests covering required/error messaging, design token class assertions, and theme toggling through context providers.
- **Testing Update (2025-10-09 19:58 SGT):**
  - Added `src/components/ui/__tests__/molecules.test.tsx` to cover `Card`, `CheckboxField`, `RadioField` behaviors with Radix state assertions.
  - Installed Storybook Test Runner (`@storybook/test-runner`, `start-server-and-test`) and verified `npm run storybook:test` passes locally after Playwright browser install.
  - CI workflow (`.github/workflows/ci.yml`) now installs Playwright chromium build and executes Storybook interaction tests with telemetry disabled.
- **Testing & Visual Regression Update (2025-10-09 20:33 SGT):**
  - Integrated `jest-axe` into Jest setup, extending molecule tests with `expect(await axe(container)).toHaveNoViolations()` assertions.
  - Storybook radio controls expose `visuallyHiddenLabel` to resolve axe button-name violations; `RadioField` forwards option labels.
  - Added Percy Storybook workflow (`.percy.yml`, `npm run percy:storybook`) and conditional CI step to capture visual baselines when `PERCY_TOKEN` is configured.

### Workstream E — Testing & Quality Gates (Days 7-8)
- Implement Jest + Testing Library unit tests (coverage targets per master plan).
- Add axe-core automated accessibility tests for every component story.
- Configure Percy snapshots (baseline run) and Playwright visual regression for dynamic states.
- Update CI (`.github/workflows/ci.yml`) to run Storybook build, component tests, axe sweeps, Percy.
- **Status Log (2025-10-09 22:08 SGT):**
  - Storybook now enforces axe violations via `preview.tsx` (`parameters.a11y.test = 'error'` with WCAG runOnly tags).
  - `npm run percy:storybook` builds static assets and executes `percy snapshot`; awaiting `PERCY_TOKEN` to generate the initial baseline.

### Buffer & Polish (Day 9)
- Resolve QA feedback, update documentation references, ensure coverage metrics meet thresholds.
- Capture analytics screenshots demonstrating instrumentation readiness.

## 5. Deliverable Validation Checklist
- **Design Tokens:** Tokens exported, Storybook theme page published, Tailwind config aligned with token map, lint rules enforced.
- **i18n:** Locale-specific builds succeed, middleware enforces default locale redirect, translation coverage 100% for enumerated namespaces, fallback strategy documented.
- **Component Library:** Stories for all components (atoms→organisms) with knob controls, accessibility review logs captured, analytics data attributes included where applicable.
- **Testing:** Jest coverage ≥95% for `frontend/src/components/**`, axe tests green, Percy baseline stored, CI pipeline updated and passing.
- **Documentation:** README references Storybook usage, `docs/design-system/` updated with component linkage, AGENT changelog includes Phase 2 entry.

## 6. File Creation Matrix

| File / Directory | Description | Feature Checklist |
| --- | --- | --- |
| `frontend/src/styles/design-tokens.css` | Root CSS variables for colors, spacing, typography, shadows, motion tokens. | - [ ] WCAG-compliant contrast ratios<br>- [ ] Light/dark/high-contrast sets<br>- [ ] Comments linking to ADA references |
| `frontend/src/lib/theme/tokens.ts` | TypeScript token exports for runtime consumption (e.g., charts, animations). | - [ ] Typed token categories (`ColorToken`, `SpaceToken`)<br>- [ ] Default + variant maps<br>- [ ] Unit tests verifying token integrity |
| `frontend/tailwind.config.ts` (extended) | Tailwind config referencing design tokens and component paths. | - [ ] `theme.extend` maps to CSS vars<br>- [ ] Plugin setup for radix/typography<br>- [ ] Safelist for dynamic classes |
| `frontend/src/lib/i18n/config.ts` | `next-intl` configuration (locales, defaultLocale, messages loader). | - [ ] Locale metadata structure<br>- [ ] Fallback chain across en→zh→ms→ta<br>- [ ] Unit tests for locale resolution |
| `frontend/src/middleware.ts` | Locale negotiation middleware per PAD §5.4. | - [ ] Redirect bare domain to `/en`<br>- [ ] Cookie + `Accept-Language` negotiation<br>- [ ] Test coverage via integration tests |
| `frontend/src/locales/en/{common,navigation,forms,errors}.json` | English namespace translations. | - [ ] Complete key coverage for Phase 2 components<br>- [ ] Comments for string ownership<br>- [ ] Lint via JSON schema |
| `frontend/src/locales/zh/{common,navigation,forms,errors}.json` | Mandarin namespace translations (initial). | - [ ] Human-reviewed translations<br>- [ ] Placeholder for translator metadata<br>- [ ] Fallback keys flagged |
| `frontend/src/locales/{ms,ta}/...` (scaffolds) | Malay & Tamil placeholder files for future phases. | - [ ] Marked with TODO metadata<br>- [ ] Programmatic fallback to English |
| `frontend/src/providers/I18nProvider.tsx` | Wraps Next App with `next-intl` provider. | - [ ] Client/server compatibility<br>- [ ] Error boundary for missing messages |
| `frontend/src/components/ui/Button.tsx` | Accessible button primitive with size/variant tokens. | - [ ] Keyboard + focus styles<br>- [ ] Loading/disabled states<br>- [ ] Analytics `data-attr` support |
| `frontend/src/components/ui/{Input,Select,Textarea}.tsx` | Form input primitives following design tokens. | - [ ] Associate labels/ids automatically<br>- [ ] Inline error display<br>- [ ] Assistive text slots |
| `frontend/src/components/ui/{Checkbox,Radio,Toggle}.tsx` | Radix-based form controls. | - [ ] ARIA compliance<br>- [ ] Controlled/uncontrolled support<br>- [ ] Reduced-motion animations |
| `frontend/src/components/ui/Icon.tsx` | Lucide icon wrapper enforcing size/color presets. | - [ ] Token-based sizing<br>- [ ] Accessible title/desc props |
| `frontend/src/components/forms/FormField.tsx` | Composition helper connecting label, input, validation. | - [ ] Generic over input types<br>- [ ] Error + hint messaging |
| `frontend/src/components/layout/{Header,Footer,NavigationBar,PageLayout}.tsx` | Layout components matching PAD §5.2 map. | - [ ] Locale-aware links<br>- [ ] Skip-link & breadcrumbs<br>- [ ] Responsive breakpoints |
| `frontend/src/components/sections/HeroBanner.tsx` | Hero section with motion controls. | - [ ] Prefers-reduced-motion friendly<br>- [ ] CTA analytics hooks |
| `frontend/src/components/ui/LanguageSwitcher.tsx` | Locale selector (menu + analytics). | - [ ] Keyboard accessible menu<br>- [ ] Persists choice (cookie/local storage)<br>- [ ] Fires GA4 event |
| `frontend/.storybook/main.ts` | Storybook entry configuration. | - [ ] CSF3 stories<br>- [ ] Addon suite (a11y, viewport, interactions, i18n toggles) |
| `frontend/.storybook/preview.ts` | Global decorators (i18n, theme, layout). | - [ ] Theme toggle toolbar<br>- [ ] Locale toolbar<br>- [ ] Global CSS imports |
| `frontend/.storybook/tsconfig.json` | TypeScript path overrides for Storybook. | - [ ] Matches app `paths`<br>- [ ] Enables absolute imports |
| `frontend/src/stories/**/*.stories.tsx` | Component stories (atoms→organisms). | - [ ] Controls for props/variants<br>- [ ] Docs tab with usage guidelines<br>- [ ] Play functions for interaction tests |
| `frontend/src/tests/unit/components/*.test.tsx` | Jest unit tests per component. | - [ ] Interaction + snapshot coverage<br>- [ ] i18n string assertions |
| `frontend/src/tests/accessibility/*.axe.test.ts` | axe-core tests for Storybook stories. | - [ ] Each component stubbed<br>- [ ] Fails on contrast/ARIA issues |
| `frontend/src/tests/visual/percy.config.ts` | Percy snapshot configuration. | - [ ] Storybook static build hooking<br>- [ ] Baseline thresholds |
| `frontend/.percy.yml` | Percy project settings. | - [ ] Width breakpoints (mobile/tablet/desktop)<br>- [ ] Minimum height overrides |
| `frontend/src/tests/utils/testWrapper.tsx` | Provider wrapper (theme, i18n) for Jest/RTL. | - [ ] Configurable locale/theme<br>- [ ] Re-export render helpers |
| `frontend/src/styles/storybook.css` | Storybook-specific global styles (grid, canvas). | - [ ] Mirror design tokens<br>- [ ] Documented in Storybook docs |
| `docs/design-system/component-library.md` | Documentation linking components to tokens & usage. | - [ ] Updated component catalog<br>- [ ] Accessibility notes per component |
| `docs/phase2-status-checklist.md` | Running checklist for QA/PM sign-off. | - [ ] Mirrors deliverable validation<br>- [ ] Includes test artifacts references |

## 7. Risk & Mitigation Notes
- **Risk:** Translation debt if Malay/Tamil scaffolds not populated early.  
  **Mitigation:** Tag TODOs and integrate with Phase 7.5 content plan; add lint rule to surface untranslated keys.
- **Risk:** Storybook and Next build divergence.  
  **Mitigation:** Share webpack configuration via `nextjs-storybook` builder, include CI smoke run.
- **Risk:** Token drift between CSS and TypeScript exports.  
  **Mitigation:** Jest snapshot tests (Workstream A) confirm parity; incorporate lint plugin.
- **Risk:** Accessibility regressions.  
  **Mitigation:** axe tests + manual NVDA/VoiceOver sessions logged in QA doc.

## 8. Validation Against Source Documents
- Confirms `codebase_completion_master_plan.md` Phase 2 deliverables (design tokens, i18n, component suite, Storybook, testing) and schedules them across nine workdays.
- Satisfies `docs/AGENT.md` directives for accessibility-first components (§3, §9, §10) and documentation rigor (§13, §15).
- Aligns with `Project_Architecture_Document.md` by reinforcing directory structure (§5.2), localization workflow (§10.0), accessibility compliance (§16.0), and testing strategy (§17.0).

## 9. Next Steps
1. Secure design token palette & typography approval from UX lead (ref `docs/design-system/`).
2. Kick off Workstream A with paired token + Tailwind implementation.
3. Schedule Storybook/QA workflow review with frontend & QA leads at end of Day 6.
4. Update `docs/AGENT.md` change log after initial implementation to signal Phase 2 activation.

