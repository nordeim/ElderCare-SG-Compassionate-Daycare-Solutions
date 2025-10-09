# Phase 2 Execution Sub-Plan — Design System, UI Components & i18n

**Prepared:** 2025-10-09 06:54 SGT  
**Author:** Cascade AI Agent  
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

---

> Standing by to translate this sub-plan into execution tickets or sprint boards upon stakeholder confirmation.
