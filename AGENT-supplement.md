# AGENT-supplement.md - Supplemental Details for CLAUDE.md

**Purpose**: This document contains specific implementation details, configuration examples, and operational guidance found in AGENT.md that are missing or less detailed in CLAUDE.md. It is designed to be used as a supplement to CLAUDE.md to provide AI coding agents with complete information for implementation.

## 1. Enhanced Backend Service Layer Architecture

### Service Layer Patterns
```
Controllers: Thin HTTP handlers; delegate to services
Services: Core business logic with transactional integrity (e.g., BookingService creates booking + Calendly event atomically)
Repositories: Data access abstraction with Redis caching (5-min TTL, tag-based invalidation)
Observers: AuditObserver auto-logs changes for opt-in models (User, Center, Booking, Consent, Testimonial)
Events/Listeners: Domain event orchestration (notifications, analytics, cache invalidation)
Jobs: Queued tasks (emails, SMS, analytics sync, data exports) with exponential backoff (1m, 5m, 15m)
```

### Implementation Guidance
- Controllers should only handle HTTP concerns and delegate to services
- Services should contain all business logic and maintain transactional integrity
- Repositories should abstract data access and implement caching strategies
- Use observers for automatic audit logging on model changes
- Leverage events/listeners for cross-cutting concerns
- Implement jobs for long-running tasks with proper retry strategies

## 2. Testing Environment Configuration

### Integration Test Environment Variables
```bash
# Required for Calendly integration tests
CALENDLY_API_TOKEN=your_token
CALENDLY_ORGANIZATION_URI=https://api.calendly.com/organizations/YOUR_ORG
CALENDLY_WEBHOOK_SECRET=your_webhook_secret
```

### Database Testing Configuration
- **Default**: In-memory SQLite (fast, isolated, uses RefreshDatabase trait)
- **Override**: Set DB_CONNECTION=mysql in .env.testing for MySQL-specific tests

### CI Behavior
Integration tests run only in staging/production pipelines with secrets configured.

## 3. Queue Configuration Details

### Worker Pool Configuration
```
Worker Pools: Sized per environment (local: 1 worker, staging: 2, production: 4+)
```

### Priority Queue Structure
```
Priority Queues:
high: SMS confirmations, critical alerts
default: Transactional emails, booking notifications
low: Analytics syncs, batch processing
```

### Delayed Jobs Configuration
```
Delayed Jobs: 72-hour and 24-hour booking reminders, post-visit follow-ups
```

### Retry Strategy
```
Retry Strategy: Exponential backoff (1m, 5m, 15m); failures logged to Sentry and failed_jobs table; replayable via Nova
```

## 4. API Design Examples

### Filtering and Sorting Patterns
```
Filtering & Sorting: filter[city]=Singapore, sort=-created_at
```

### Rate Limiting Configuration
```
Rate Limits: 60 req/min per IP (public), 1000/hour per authenticated user, configurable throttles for partner integrations
```

### Pagination Structure
```
Pagination: ?page=1&per_page=20 with meta and links blocks
```

## 5. Frontend State Management Details

### State Management Breakdown
```
Server State (React Query): Booking data, center listings, testimonials. 5-minute stale time, background revalidation.
Global Client State (Zustand + localStorage): Auth session, locale preference, UI toggles, feature flags.
Local State: Form inputs, modal toggles; managed via React hooks.
```

### Implementation Guidance
- Use React Query for server data with 5-minute stale time
- Store global client state in Zustand with persistence
- Keep local component state in React hooks
- Ensure state degrades gracefully when JavaScript is disabled

## 6. Accessibility Testing Tools

### Visual Regression Testing
```
Tool: Percy integrated with Storybook
Execution: npm run percy:storybook
Baselines: Stored in .percy.yml
CI Integration: Gated by PERCY_TOKEN environment variable
Review: Visual diffs reviewed in Percy dashboard before merge
```

### Testing Cadence
```
Manual testing: Sprint review (major UI changes)
QA checklist: Accessibility, keyboard-only navigation, 200% zoom test
```

## 7. E2E Testing Information

### Execution Cadence
```
Smoke Tests: Every PR (basic auth + homepage)
Full Suite: Nightly + on release candidates
Cross-browser: Chrome, Firefox, Safari (via Playwright)
```

### Critical User Journeys
```
Authentication Flow: Register → Email verification → Login → MFA (admin)
Booking Flow: Search centers → View details → Fill questionnaire → Schedule via Calendly → Receive confirmation
Testimonial Submission: Login → Submit testimonial → Moderation → Approval → Display
Language Switching: Change locale → Verify content translation → Verify URL update
Accessibility: Keyboard-only navigation of booking flow → Screen reader announcements
```

## 8. Performance Testing Details

### Load Testing Configuration
```
k6 (backend API): Baseline 1,000 concurrent users, stress test 2x (2,000 concurrent)
Artillery (alternative for complex scenarios)
WebPageTest (frontend): Weekly runs from Singapore, 3G profile
Lighthouse CI: Every deployment (staging + production)
```

### Stress Testing
```
k6 Stress Test: Breaking point identification, 2,000 concurrent users (2x baseline)
Pre-launch + quarterly: Manual review
```

## 9. CI/CD Pipeline Information

### Deployment Strategies
```
Staging: Automatic on main merges (ECS Fargate, blue-green deployment)
Production: Manual approval + change ticket required (ECS Fargate multi-AZ, canary rollout)
```

### Pipeline Stages
```
1. Lint & Static Analysis
2. Automated Testing
3. Security Checks
4. Build Artifacts
5. Deployment
```

## 10. Monitoring Configuration

### Analytics Configuration
```
GA4, Hotjar, Sentry initialized via AnalyticsProvider in Next.js
Configure environment variables per docs/deployment/monitoring.md
Enhanced measurement events: booking_started, booking_completed, virtual_tour_started, newsletter_subscribed, testimonial_submitted, language_switched
IP anonymization enforced for PDPA compliance
```

### Monitoring Tools
```
Tool: Purpose: Key Metrics: Alert Threshold
Sentry: Error tracking: Error rate, unique issues, release health: >10 errors/min sustained
New Relic: APM & RUM: Response time, throughput, Apdex, Core Web Vitals: Response time >500ms (p95)
CloudWatch: Infrastructure metrics: CPU, memory, disk, network: CPU >70% sustained 5min
UptimeRobot: Synthetic uptime: HTTP 200 checks (1-min interval): 2 consecutive failures
Lighthouse CI: Performance budgets: Lighthouse scores, page weight: Performance <90, weight >280KB
GA4: User analytics: Sessions, conversions, bounce rate: Custom dashboards
Hotjar: UX insights: Heatmaps, session recordings (consent-gated): Manual review
```

## 11. Infrastructure Information

### Managed Resources
```
VPC (public/private subnets, NAT gateways, security groups)
ECS Fargate (task definitions, services, auto-scaling policies)
RDS MySQL 8.0 (multi-AZ, automated backups, read replica in production)
ElastiCache Redis 7 (cluster mode in production, single node in staging)
S3 buckets (media, backups, Terraform state)
IAM roles/policies (least privilege)
CloudWatch metrics, alarms, log groups
AWS Secrets Manager (API keys, DB credentials)
```

### Operational Rules
```
❌ No manual AWS console edits — all changes via Terraform
✅ State stored in S3 with DynamoDB locking
✅ Separate workspaces for staging/production
✅ Terraform plan required in PR descriptions for infra changes
```

## 12. Security Configuration

### Authentication Configuration
```
Authentication: Sanctum tokens, MFA for admin/moderator accounts, password strength enforcement
```

### Authorization Configuration
```
Authorization: RBAC via policies/gates; granular abilities (view PII, approve content, manage translations)
```

### Security Architecture
```
Layer: Controls: Implementation
Transport: TLS 1.3, HSTS preload, certificate pinning (mobile future): Cloudflare + AWS ALB
Application: CSP, X-Frame-Options: DENY, X-Content-Type-Options: nosniff, CSRF middleware: Laravel security headers + Next.js config
Authentication: OAuth 2.0 (Sanctum), MFA for admin/moderator, secure session cookies (httpOnly, secure, sameSite): Laravel Sanctum + custom MFA flow
Authorization: RBAC via Policies/Gates (roles: user, admin, moderator, translator); granular abilities (view PII, approve content, manage translations): Laravel policies + Nova permissions
Data: AES-256 encryption at rest (RDS, S3), TLS 1.3 in transit, bcrypt password hashing (cost 12): AWS KMS + Laravel encryption
Secrets: AWS Secrets Manager with 90-day rotation, no secrets in repo, .env excluded via .gitignore: Terraform-managed rotation
Network: VPC isolation, private subnets for DB/Redis, security groups (least privilege), WAF rules (DDoS, SQLi, XSS): Cloudflare WAF + AWS security groups
Dependencies: Dependabot (weekly), npm audit, composer audit, container image scanning (Trivy/Snyk): GitHub Actions + automated PRs
Monitoring: AWS GuardDuty (threat detection), Sentry (error tracking), CloudWatch anomaly detection, 24-hour incident notification SLA: Multi-tool observability stack
```

## 13. Disaster Recovery Information

### Backup Strategies and Recovery Objectives
```
Component: Backup Strategy: Retention: Recovery Objective
RDS MySQL: Automated daily snapshots + point-in-time recovery: 35 days: RPO: <1 hour, RTO: <4 hours
Redis: Daily snapshots (ElastiCache): 7 days: RTO: <1 hour (rebuild cache from DB)
S3 Media: Versioning enabled + lifecycle archival to Glacier: Indefinite (compliance): RTO: <30 min (restore from versioning)
Terraform State: S3 versioning + cross-region replication: Indefinite: RTO: <15 min (restore from backup)
Application Code: Git repository (GitHub) + container registry (ECR): Indefinite: RTO: <15 min (redeploy from tag)
```

### DR Runbook
```
DR Runbook: docs/runbooks/disaster-recovery.md outlines failover procedures, stakeholder contacts, and rollback strategies.
```

## 14. Testing Standards and Thresholds

### Testing Requirements
```
Standard: Requirement: Enforcement: Exemptions
Coverage: ≥90% critical modules, ≥80% overall: CI/CD warning if below; blocker for critical paths: Trivial getters/setters, third-party adapters
Accessibility: Lighthouse ≥90, zero axe-core violations: CI/CD gate: None
Performance: Lighthouse Performance ≥90: CI/CD gate: None
Security: No high/critical vulnerabilities in dependencies: CI/CD gate (Dependabot, npm audit): 
```

## 15. Phase 3 Test Highlights

### Current Test Status
```
Tests: 90 tests
Assertions: 216
Status: ✅ All passing
Warnings: 59 PHPUnit deprecations (to be addressed in Phase 4)
```

### Key Test Implementations
```
✅ RegisterTest: POST /api/v1/register returns token + user
✅ BookingHappyPathTest: Full booking lifecycle with Calendly mock, job dispatch assertions
✅ CalendlyServiceTest: Unit tests with Http::fake() for API mocking
✅ AuditObserverTest: Validates audit log creation on model changes
```

---

**Note**: This supplement should be used in conjunction with CLAUDE.md (v4.1) to provide complete implementation guidance for AI coding agents working on the ElderCare SG project.


This supplemental document captures all the valuable implementation details from AGENT.md that are missing or less detailed in CLAUDE.md. It's organized by topic to make it easy for AI agents to find specific information they need for implementation tasks.
