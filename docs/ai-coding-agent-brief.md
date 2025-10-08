# ElderCare SG — AI Coding Agent Brief

## Purpose
- Capture the essential WHAT, WHY, and HOW of the `ElderCare SG` web platform so that any AI coding agent can operate with full context.
- Reinforce the elevated operating framework: deep analysis, systematic planning, technical excellence, strategic partnership, and transparent communication.

## Product Vision & Value Proposition
- **Mission**: Deliver a compassionate, accessibility-first digital bridge between Singaporean families and trusted elderly daycare services.
- **Primary Outcomes**: Empower informed decisions, build trust, and enhance quality of life for seniors and caregivers.
- **Target Audiences**: Adult children (30–55), family caregivers, healthcare professionals, and digitally literate seniors across Singapore’s multicultural landscape.

## Core Features & Journeys
- **Service Discovery**: Rich profiles of facilities, staff qualifications, certifications, and amenities.
- **Virtual Tours**: Video.js-powered, accessible tours with adaptive streaming and WebXR options.
- **Booking System**: Calendly-backed scheduling with pre-visit questionnaires, confirmations, reminders, and rescheduling flows.
- **Testimonials & Stories**: Moderated, multilingual user testimonials with media consent tracking.
- **Multilingual Experience**: English, Mandarin, Malay, and Tamil content with respectful tone and cultural sensitivity.
- **Newsletter & Engagement**: Mailchimp integration (double opt-in) plus analytics for open/click performance.
- **Government Support Guidance**: Subsidy calculators and guidance for Pioneer/Merdeka programs.

## Technical Architecture Snapshot
- **Frontend (`frontend/`)**: Next.js 14, React 18, TypeScript 5, Tailwind CSS, Radix UI, Zustand (client state), React Query (server state), Framer Motion (animation with reduced-motion support).
- **Backend (`backend/`)**: Laravel 12 (PHP 8.2), service-oriented API, Sanctum authentication, MariaDB/MySQL primary store, Redis caching/queues, Elasticsearch search services, S3 (ap-southeast-1) for media.
- **Infrastructure**: Dockerized services orchestrated by Kubernetes on AWS Singapore region; Cloudflare CDN and security; GitHub Actions CI/CD; staging via ECS; observability via Sentry, New Relic, Lighthouse CI, GA4, Hotjar.
- **Security & Compliance**: OAuth 2.0, RBAC, secure session cookies, MFA for admins, CSP, CSRF, SQLi/XSS hardening, PDPA-compliant consent and data retention.

## Compliance, Accessibility & Localization
- **Regulatory**: PDPA adherence (consent, residency, retention, right-to-be-forgotten), MOH eldercare display requirements.
- **Accessibility**: WCAG 2.1 AA baseline, keyboard navigation, captions/audio descriptions, adjustable typography, screen reader validation (NVDA/VoiceOver).
- **Cultural Fit**: Multicultural imagery, respectful language, holiday-aware content, transit and parking guidance aligned with Singapore norms.

## Quality Standards & Definition of Done
- **Automated Testing**: Jest unit tests, Testing Library integration tests, Playwright E2E, Percy visual, Lighthouse CI performance audits, axe-core accessibility checks.
- **Manual Verification**: BrowserStack cross-browser/device runs, screen-reader passes, usability feedback loops, security penetration tests.
- **Definition of Done**: Code review, 100% automated coverage, manual QA pass, accessibility and performance targets (>90 Lighthouse), documentation updates, stakeholder approval, monitored production deployment.

## Analytics & Success Metrics
| Metric | Target | Review Cadence | Owner |
| --- | --- | --- | --- |
| Visit bookings increase | +30% in 3 months | Monthly | Marketing |
| Mobile bounce rate | <40% | Bi-weekly | UX |
| Lighthouse Perf/Accessibility | >90 | Each deploy | DevOps/QA |
| Avg session duration | >5 minutes | Monthly | Marketing |
| Form completion | >75% | Monthly | UX |
| 3G load time | <3 seconds | Weekly | DevOps |
| Video engagement | >60% completion | Monthly | Marketing |

## Delivery Workflow
1. **Branching**: Feature branches per task (`feature/*`) with peer review prior to merge.
2. **Environment Setup**: Docker Compose local environment, `.env` configuration mirrored across stages.
3. **CI/CD**: GitHub Actions execute tests, audits, and deploy to staging on `main` merges; production via controlled release.
4. **Monitoring & Alerting**: Sentry for errors, New Relic for APM, UptimeRobot for uptime, ELK for logs.
5. **Documentation Expectations**: Update `README.md`, `docs/`, and relevant runbooks with each feature.

## Execution Roadmap Context
- Refer to `codebase_completion_master_plan.md` for phased plan. Current phase emphasizes foundation (infrastructure, analytics, test scaffolding). Acceptance criteria include Dockerized local dev, accessible staging URL, and automated staging deploys from `main`.

## Key References for Further Detail
- `README.md` — project overview, stack, setup, roadmap.
- `Project_Requirements_Document.md` — detailed requirements across clarity, compliance, technical precision, validation.
- `codebase_completion_master_plan.md` — phased delivery plan and acceptance criteria.
- `docs/architecture.md`, `docs/design-system.md`, `docs/accessibility.md` — deeper dives into specific domains (consult as available).

---
**Operational Reminder**: All AI coding agents must apply the elevated operating framework—prioritize deep analysis, structured planning, technical excellence, strategic partnership, and transparent communication on every task.
