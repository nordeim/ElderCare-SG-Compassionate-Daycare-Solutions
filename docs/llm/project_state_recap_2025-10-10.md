# ElderCare SG — Project State Recap (2025-10-10)

## Mission & Vision Alignment
- **Product Goal**: Deliver a compassionate, accessibility-first bridge connecting Singaporean families with trusted elderly daycare services, prioritising trust, regulatory compliance, and empathetic UX (`README.md`, `docs/ai-coding-agent-brief.md`).
- **Success Metrics**: +30% bookings in 3 months, Lighthouse >90, <40% mobile bounce, >5 min sessions, 3G load <3 s, >60% video completion (sources: `Project_Architecture_Document.md`, `codebase_completion_master_plan.md`).

## System Architecture Understanding
### Frontend (`frontend/`)
- Next.js 14 App Router w/ React Server Components default; Tailwind CSS tokens, Radix UI, Framer Motion with reduced-motion awareness (`docs/AGENT.md` §3, `Project_Architecture_Document.md` §5).
- State: React Query (server), Zustand (client), locale-prefixed routing with `next-intl`. Storybook + Percy integrated for design system QA (`docs/phase2-execution-subplan.md`).
- Accessibility: WCAG 2.1 AA baked into tokens, components, and CI (axe-core, Lighthouse) with manual audits planned (`docs/AGENT.md` §§9, 10, 16).

### Backend (`backend/`)
- Laravel 12 service-oriented monolith with Sanctum auth, domain services (`Services`, `Repositories`, `Events`, `Jobs`). API contract JSON:API-adjacent under `/api/v1/` (`Project_Architecture_Document.md` §6, `docs/AGENT.md` §4).
- Async stack: Redis + Horizon, SQS/Fargate in production; job + fail queues provisioned by migrations (`0001_01_01_000002_create_jobs_table.php`).
- Compliance: PDPA consent ledger, audit trail, data export & deletion flows mandated in Phase 3 plan (`codebase_completion_master_plan.md` Phase 3).

### Data & Integrations
- **Primary Store**: MySQL 8.0 (utf8mb4) with strict FK + JSON columns for flexible structures (`database_schema.sql`).
- **Caching & Queues**: Redis, `cache` tables for fallback/local testing.
- **Search**: MeiliSearch target (phase 5/7), full-text fallbacks for MySQL.
- **Integrations**: Calendly, Mailchimp, Twilio, Cloudflare Stream, GA4, Hotjar, Sentry, New Relic (per `docs/AGENT.md` §§5, 8).

## Current Implementation Snapshot
| Area | Status | Evidence |
| --- | --- | --- |
| Phase 1 Foundation | ✅ Completed with follow-up tasks noted (env templates, Terraform doc, analytics stubs) | `docs/phase1-execution-plan.md`
| Phase 2 Design System & i18n | ✅ Components, Storybook (Vite builder), jest-axe, Percy gating implemented with remaining coverage tickets | `docs/phase2-execution-subplan.md`
| Database Migrations | ✅ All core entities, PDPA tables, polymorphic resources, and reporting views implemented | `database/migrations/*.php`
| Backend Services | 🚧 Planned for Phase 3 — authentication, consent services, rate limiting upcoming | `codebase_completion_master_plan.md`
| Booking Workflow | 🚧 Scheduled Phase 6; schema ready (`bookings`, `services`) awaiting service layer & Calendly integration |
| Advanced Search/Testimonial Moderation | 🚧 Phase 7 roadmap |

## Database State & Alignment
- Schema parity between `database_schema.sql` and Laravel migrations confirmed for 18 tables, 2 views, >80 indexes.
- Soft deletes, JSON questionnaire, PDPA consent enums, polymorphic media/translations all present.
- Reporting views `active_centers_summary` & `user_booking_history` implemented via migration `2025_10_08_160000_create_reporting_views.php`.
- Remaining action: ensure DB-specific check constraints guarded for SQLite (see remediation below).

## Compliance & QA Posture
- PDPA-compliant entities (`consents`, `audit_logs`), consent ledger snapshots, audit metadata captured.
- Accessibility-first approach guided by design tokens and Storybook a11y runs; manual NVDA/VoiceOver sweeps scheduled in Phase 8.
- Monitoring/analytics scaffolding partially stubbed; GA4/Hotjar/Sentry modules pending final wiring (`docs/phase1-execution-plan.md`).

## Recommended Remediation & Next Actions
- **Guard MySQL-specific constraints**: Wrap raw `ALTER TABLE ... ADD CONSTRAINT` statements in `services`, `bookings`, `testimonials` migrations with driver checks (`Schema::getConnection()->getDriverName() === 'mysql'`) to prevent SQLite migration failures.
- **Finalize analytics & monitoring bootstrap**: Complete GA4, Hotjar, Sentry, New Relic helper modules, env guidance, and CI validation per Phase 1 follow-up checklist (`docs/phase1-execution-plan.md` §3.4).
- **Document Cloudflare & Terraform gaps**: Produce `docs/deployment/cloudflare.md` and enhance Terraform README per outstanding Phase 1 actions.
- **Extend Storybook/test coverage**: Finish remaining component stories/tests (Card, Form composites, navigation) and capture Percy baselines (pending `PERCY_TOKEN`) as listed in `docs/phase2-execution-subplan.md` workstream audits.
- **Phase 3 readiness**: Prioritise authentication, consent management service, rate limiting middleware, and OpenAPI docs to unlock downstream booking workflows.
- **Booking service instrumentation**: Prepare Calendly/Twilio integration stubs, queue retry strategies, and fallback UX paths ahead of Phase 6 to satisfy compliance + resilience requirements (`Project_Architecture_Document.md` §§4.2, 8).
- **Search & translation governance**: Draft lint/check rules for untranslated keys, plan MeiliSearch index bootstrap, and ensure content moderation workflow readiness before Phase 7.

## Open Alignment Questions / Flags
1. **Cloudflare Terraform coverage**: confirm whether infra automation or manual runbook is preferred for CDN/WAF as noted missing in Phase 1 audit.
2. **Observer tooling secrets**: validate availability of GA4, Hotjar, Sentry, New Relic credentials for staging before Phase 3.
3. **Content translation pipeline**: align on translator/reviewer roles in Nova for `content_translations` workflow to prevent Phase 7.5 bottlenecks.
4. **Analytics data layer ownership**: determine cross-functional owner for GA4 Measurement Protocol + dashboards (marketing vs engineering).

---
Prepared by Cascade AI Assistant • 2025-10-10 06:19 SGT
