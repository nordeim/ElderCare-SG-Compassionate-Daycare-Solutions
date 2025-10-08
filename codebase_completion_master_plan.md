## EXECUTION PLAN 

### **Phase 1: Foundation, Infrastructure & Analytics (5-6 days)**

**Objective**: Establish project structure, deployment infrastructure, and measurement systems.

**Dependencies**: None

**File Checklist**:
- [x] Root configuration (package.json, composer.json, docker-compose.yml)
- [x] Environment configuration (.env.example, staging .env, production .env)
- [x] Directory structure (frontend/, backend/, docs/, docker/)
- [x] Git configuration (.gitignore, .gitattributes, branch protection)
- [x] Database migrations (users, profiles, consents, audit_logs)
- [x] AWS infrastructure setup (VPC, RDS, ElastiCache, S3, ECS staging)
- [x] Cloudflare CDN configuration
- [x] CI/CD pipeline (GitHub Actions for staging auto-deploy)
- [x] Google Analytics 4 setup and configuration
- [x] Hotjar integration
- [x] Sentry error tracking setup
- [x] New Relic APM setup
- [x] Test infrastructure (Jest, PHPUnit, Playwright, Lighthouse CI, Percy, axe-core)
- [x] Documentation README.md, CONTRIBUTING.md

**Day-by-Day**:
- **Day 1**: Project structure, Docker setup, Git configuration
- **Day 2**: AWS infrastructure provisioning (staging environment)
- **Day 3**: Database migrations (users, PDPA tables), environment config
- **Day 4**: CI/CD pipeline, analytics (GA4, Hotjar), monitoring (Sentry, New Relic)
- **Day 5**: Test infrastructure setup, documentation
- **Day 6**: Buffer/testing

**Acceptance Criteria**:
- ✅ Development environment runs locally via Docker
- ✅ Staging environment accessible via URL
- ✅ CI/CD deploys to staging on merge to main
- ✅ GA4 tracks page views correctly
- ✅ Sentry captures errors
- ✅ All test runners work (Jest, PHPUnit, Playwright)
- ✅ Database migrations run successfully

---

### **Phase 2: Design System, UI Components & i18n (8-9 days)**

**Objective**: Create reusable components, design tokens, and internationalization foundation.

**Dependencies**: Phase 1

**File Checklist**:
- [x] Tailwind configuration with design tokens
- [x] next-intl setup and configuration
- [x] Locale routing (/en, /zh)
- [x] Translation files (common.json, navigation.json, forms.json, errors.json) × 2 languages
- [x] Language switcher component
- [x] Base components (Button, Card, Input, Label, Icon, Modal, Dialog)
- [x] Form components (FormField, Select, Checkbox, Radio, Textarea)
- [x] Layout components (Header, Footer, Navigation, PageLayout)
- [x] Storybook setup
- [x] Component tests (Jest + Testing Library)
- [x] Accessibility tests (axe-core for each component)
- [x] Visual regression tests (Percy snapshots)

**Day-by-Day**:
- **Day 1**: Tailwind config, design tokens, next-intl setup
- **Day 2**: Locale routing, translation files (English + Mandarin), language switcher
- **Day 3**: Base components (Button, Card, Input, Icon)
- **Day 4**: Form components (FormField, Select, Checkbox, etc.)
- **Day 5**: Layout components (Header, Footer, Navigation)
- **Day 6**: Storybook setup, component documentation
- **Day 7**: Component unit tests
- **Day 8**: Accessibility tests (axe-core), visual regression (Percy)
- **Day 9**: Buffer/polish

**Acceptance Criteria**:
- ✅ All components render in English and Mandarin
- ✅ Language switcher works smoothly
- ✅ Components are responsive (mobile, tablet, desktop)
- ✅ All components pass axe-core accessibility tests
- ✅ Storybook displays all components with variants
- ✅ 100% component test coverage
- ✅ Percy baselines captured

---

### **Phase 3: Core Backend Services & PDPA Compliance (10-11 days)**

**Objective**: Implement authentication, user management, PDPA compliance, and API foundation.

**Dependencies**: Phase 1

**File Checklist**:
- [x] User model enhancements (role, preferences)
- [x] Profile model
- [x] Consent model
- [x] AuditLog model
- [x] Authentication controllers (register, login, logout, verify email)
- [x] Password reset functionality
- [x] Laravel Sanctum configuration
- [x] Role-based middleware (admin, user)
- [x] Consent management service
- [x] Data export service (JSON export of all user data)
- [x] Account deletion service (soft delete, hard delete job)
- [x] Audit logging service (tracks all data changes)
- [x] Cookie consent backend
- [x] API routes structure (/api/v1/)
- [x] API response formatting (consistent JSON structure)
- [x] API error handling (global exception handler)
- [x] Rate limiting middleware
- [x] OpenAPI/Swagger documentation
- [x] Backend unit tests (PHPUnit)
- [x] API integration tests

**Day-by-Day**:
- **Day 1**: User/Profile models, migrations, factories
- **Day 2**: Authentication controllers (register, login, logout)
- **Day 3**: Password reset, email verification, Sanctum config
- **Day 4**: Role-based permissions, middleware
- **Day 5**: Consent management (capture, versioning, storage)
- **Day 6**: Data export endpoint, account deletion workflow
- **Day 7**: Audit logging service, IP tracking middleware
- **Day 8**: API structure, response formatting, error handling
- **Day 9**: Rate limiting, OpenAPI documentation
- **Day 10**: Backend tests (unit + integration)
- **Day 11**: Buffer/polish

**Acceptance Criteria**:
- ✅ Users can register with explicit consent
- ✅ Users can login/logout securely
- ✅ Password reset via email works
- ✅ Email verification required for account activation
- ✅ PDPA: Users can download their data (JSON)
- ✅ PDPA: Users can delete their account (30-day grace period)
- ✅ All data changes logged in audit_logs
- ✅ API documentation complete and accurate
- ✅ >90% backend test coverage
- ✅ Rate limiting prevents abuse (60 req/min)

---

### **Phase 4: Frontend Pages, State Management & PDPA UI (6-7 days)**

**Objective**: Implement page structure, routing, navigation, and user-facing PDPA features.

**Dependencies**: Phases 1, 2, 3

**File Checklist**:
- [x] Page components (Home, About, Services, Contact, Privacy, Terms)
- [x] User dashboard page
- [x] Login/Register pages
- [x] Password reset page
- [x] "My Data & Privacy" page
- [x] Next.js App Router configuration
- [x] Zustand store setup (user session, language preference, UI state)
- [x] React Query configuration (API client, caching)
- [x] API client service (Axios with interceptors)
- [x] Consent UI components (checkboxes, privacy policy links)
- [x] Cookie consent banner
- [x] Data download button (triggers API call)
- [x] Account deletion modal (with confirmation)
- [x] Contact form component
- [x] Newsletter signup form
- [x] SEO metadata (next/head for each page)
- [x] Page-level tests (integration + E2E)

**Day-by-Day**:
- **Day 1**: Page structure, routing, navigation implementation
- **Day 2**: Zustand setup, React Query configuration, API client
- **Day 3**: Login/Register pages, authentication flow
- **Day 4**: Home, About, Services pages with content
- **Day 5**: Contact page, contact form, newsletter signup
- **Day 6**: "My Data & Privacy" page, cookie consent banner, PDPA UI
- **Day 7**: E2E tests (critical paths), SEO metadata, buffer

**Acceptance Criteria**:
- ✅ All pages render correctly in English and Mandarin
- ✅ Navigation works smoothly
- ✅ Authentication flow complete (login → dashboard → logout)
- ✅ Contact form submits successfully
- ✅ Newsletter signup integrates with backend
- ✅ Cookie consent banner blocks analytics until consent
- ✅ Users can download their data from dashboard
- ✅ Account deletion requires confirmation
- ✅ Pages are responsive and accessible
- ✅ E2E tests cover login, registration, contact form
- ✅ Lighthouse performance >90, accessibility >90

---

### **Phase 5: Content Management, MOH Compliance & Media (11-12 days)**

**Objective**: Implement center/service management, Laravel Nova admin, MOH compliance, FAQs, photo galleries.

**Dependencies**: Phases 1, 3

**File Checklist**:
- [x] Center model and migration (with MOH fields, transport_info, amenities)
- [x] Service model and migration
- [x] FAQ model and migration
- [x] Media model and migration (polymorphic)
- [x] ContentTranslation model and migration (polymorphic)
- [x] Center-Service relationship
- [x] Center repository
- [x] Service repository
- [x] Content management service
- [x] Media upload service (AWS S3)
- [x] Image optimization service (WebP conversion, thumbnails)
- [x] Laravel Nova installation and configuration
- [x] Nova resources (Center, Service, FAQ, Media)
- [x] Nova custom fields (MOH license, transport info, amenities)
- [x] Nova role-based permissions (Super Admin, Content Manager, Translator)
- [x] Translation management interface in Nova
- [x] MOH license validation rules
- [x] Content API endpoints (GET /api/v1/centers, /api/v1/services, /api/v1/faqs)
- [x] Search endpoint (MeiliSearch integration)
- [x] Photo gallery component
- [x] FAQ display component
- [x] Content tests (backend + admin panel)

**Day-by-Day**:
- **Day 1**: Center/Service/FAQ/Media models, migrations, relationships
- **Day 2**: ContentTranslation model, polymorphic setup
- **Day 3**: Laravel Nova installation, basic resources, MOH fields
- **Day 4**: Translation management in Nova, workflow setup
- **Day 5**: Media upload service, S3 integration, image optimization
- **Day 6**: FAQ management, photo gallery component
- **Day 7**: MeiliSearch setup, indexing, search service
- **Day 8**: Content API endpoints, localization support
- **Day 9**: Nova permissions, role-based access
- **Day 10**: Frontend integration (center listings, service details, FAQs)
- **Day 11**: Content tests, admin panel tests
- **Day 12**: Buffer/polish

**Acceptance Criteria**:
- ✅ Admins can create/edit/delete centers in Nova
- ✅ Centers display MOH license number
- ✅ Centers show transport information (MRT/bus)
- ✅ Admins can manage services per center
- ✅ Content can be translated (English → Mandarin) in Nova
- ✅ Translation workflow works (Draft → Translated → Published)
- ✅ Admins can upload photos, photos optimize automatically (WebP)
- ✅ Photo galleries display correctly
- ✅ FAQs categorized and searchable
- ✅ MeiliSearch returns relevant results
- ✅ Content API respects locale (?lang=en or ?lang=zh)
- ✅ All content management tested

---

### **Phase 5.5: External Integrations (2-3 days)** 🆕

**Objective**: Integrate Mailchimp (newsletter) and Twilio (SMS notifications).

**Dependencies**: Phases 3, 5

**File Checklist**:
- [x] Subscription model and migration
- [x] Mailchimp service (SDK integration)
- [x] Newsletter subscription API endpoint
- [x] Double opt-in workflow
- [x] Mailchimp sync job (queue)
- [x] Unsubscribe webhook handler
- [x] Twilio service (SDK integration)
- [x] SMS notification service
- [x] SMS templates (confirmation, reminder, cancellation)
- [x] SMS queue jobs
- [x] SMS preferences in user settings
- [x] Newsletter signup form component
- [x] Integration tests

**Day-by-Day**:
- **Day 1**: Mailchimp integration, subscription API, double opt-in
- **Day 2**: Twilio integration, SMS service, templates, queue jobs
- **Day 3**: Integration testing, error handling, buffer

**Acceptance Criteria**:
- ✅ Users can subscribe to newsletter
- ✅ Double opt-in email sent
- ✅ Mailchimp syncs new subscribers
- ✅ Unsubscribe updates local database
- ✅ SMS sends for test bookings (confirmation template)
- ✅ SMS queue retries on failure (3 attempts)
- ✅ Users can opt-out of SMS in preferences

---

### **Phase 6: Booking System & Notifications (10-12 days)**

**Objective**: Implement complete booking workflow with Calendly, email, and SMS notifications.

**Dependencies**: Phases 3, 4, 5, 5.5

**File Checklist**:
- [x] Booking model and migration
- [x] Pre-booking questionnaire schema (JSON)
- [x] Booking service
- [x] Calendly service (API integration)
- [x] Booking controllers
- [x] Booking API endpoints (create, update, cancel)
- [x] Calendly webhook handler (invitee.created, invitee.canceled)
- [x] Email notification service
- [x] Email templates (confirmation, reminder, cancellation)
- [x] SMS notification integration (using Twilio from Phase 5.5)
- [x] Booking status management (pending, confirmed, completed, canceled)
- [x] Booking queue jobs (send confirmations, send reminders)
- [x] Booking form component (multi-step)
- [x] Questionnaire component
- [x] Calendar availability component
- [x] Booking confirmation page
- [x] User booking history (dashboard)
- [x] Admin booking management (Nova)
- [x] Booking E2E tests (critical path)

**Day-by-Day**:
- **Day 1**: Booking model, migration, relationships
- **Day 2**: Booking service, validation logic
- **Day 3**: Calendly API integration, event creation
- **Day 4**: Calendly webhook handler, status updates
- **Day 5**: Email notification service, templates
- **Day 6**: SMS integration for bookings (confirmation + reminder)
- **Day 7**: Booking form UI (multi-step), questionnaire
- **Day 8**: Calendar availability component, booking flow
- **Day 9**: Booking confirmation page, user booking history
- **Day 10**: Admin booking management in Nova
- **Day 11**: Booking E2E tests (full workflow)
- **Day 12**: Buffer/error handling/edge cases

**Acceptance Criteria**:
- ✅ Users can complete booking flow (questionnaire → calendar → confirm)
- ✅ Booking creates Calendly event
- ✅ Confirmation email sent immediately
- ✅ Confirmation SMS sent immediately
- ✅ Reminder SMS sent 24h before booking
- ✅ Users can cancel bookings
- ✅ Cancellation triggers email + SMS
- ✅ Admin can view/manage all bookings in Nova
- ✅ Booking status updates correctly (pending → confirmed)
- ✅ Webhooks from Calendly processed correctly
- ✅ Full booking workflow has E2E test coverage
- ✅ Error handling for API failures (fallback to contact form)

---

### **Phase 7: Advanced Features (5-6 days)**

**Objective**: Implement testimonials and advanced search (MeiliSearch).

**Dependencies**: Phases 4, 5, 6

**File Checklist**:
- [x] Testimonial model and migration
- [x] Testimonial moderation workflow (pending, approved, rejected)
- [x] Testimonial submission form (frontend)
- [x] Testimonial API endpoints
- [x] Testimonial moderation in Nova
- [x] Testimonial display component
- [x] Spam protection (reCAPTCHA v3)
- [x] MeiliSearch advanced filters (by service type, rating, location)
- [x] Search UI component (filters, results)
- [x] Search analytics (track search queries)
- [x] Feature tests

**Day-by-Day**:
- **Day 1**: Testimonial model, API endpoints, validation
- **Day 2**: Testimonial submission form, spam protection (reCAPTCHA)
- **Day 3**: Testimonial moderation in Nova, approval workflow
- **Day 4**: Testimonial display component, rating system
- **Day 5**: Advanced search filters (MeiliSearch), search UI
- **Day 6**: Search analytics, testing, buffer

**Acceptance Criteria**:
- ✅ Users can submit testimonials
- ✅ Testimonials require moderation before display
- ✅ Admin can approve/reject testimonials in Nova
- ✅ Testimonials display with ratings
- ✅ reCAPTCHA prevents spam submissions
- ✅ Search supports filters (service type, location, rating)
- ✅ Search results are relevant and fast (<100ms)
- ✅ Search queries tracked in analytics

**Note**: Virtual tours deferred to Phase 2 (post-MVP, weeks 13-16)

---

### **Phase 7.5: Content Population & Translation (3-4 days)** 🆕

**Objective**: Populate database with real content and translate to Mandarin.

**Dependencies**: Phases 5, 7

**Tasks**:
- [x] Research 5-10 real eldercare centers in Singapore
- [x] Write center descriptions (English)
- [x] Source/create photos for each center
- [x] Create 3-5 services per center
- [x] Write 20+ FAQs (categorized)
- [x] Write About page content
- [x] Create sample testimonials (3-5 per center)
- [x] Professional translation to Mandarin (all content)
- [x] Review translation quality (native speaker)
- [x] Publish content in both languages
- [x] Verify content displays correctly

**Day-by-Day**:
- **Day 1**: Research centers, write English content (centers, services)
- **Day 2**: FAQs, About page, testimonials, source photos
- **Day 3**: Professional translation service (or native speaker translates)
- **Day 4**: Review translations, publish content, verification

**Acceptance Criteria**:
- ✅ At least 5 centers fully populated with descriptions, photos, services
- ✅ All content available in English and Mandarin
- ✅ 20+ FAQs covering common questions
- ✅ About page tells compelling story
- ✅ Sample testimonials provide social proof
- ✅ Translation quality verified by native Mandarin speaker
- ✅ All content displays correctly on frontend

---

### **Phase 8: Quality Assurance & Optimization (4-5 days)**

**Objective**: Comprehensive testing, performance optimization, accessibility audit, security hardening.

**Dependencies**: All previous phases

**Tasks**:
- [x] Cross-browser testing (Chrome, Safari, Firefox, Edge) via BrowserStack
- [x] Device testing (iPhone 12, Samsung Galaxy S21, iPad)
- [x] Screen reader testing (NVDA on Windows, VoiceOver on Mac/iOS)
- [x] Lighthouse CI review (ensure all pages >90 performance & accessibility)
- [x] Performance optimization:
  - Image lazy loading
  - Code splitting optimization
  - Remove unused CSS/JS
  - CDN cache configuration
- [x] Load testing with k6:
  - 1000 concurrent users browsing
  - 100 simultaneous booking submissions
  - Spike test (2x expected load)
- [x] Accessibility audit (external certified auditor)
- [x] Security audit:
  - `npm audit` and fix vulnerabilities
  - `composer audit` and fix vulnerabilities
  - Penetration testing (external firm)
- [x] Fix all critical and high-priority issues
- [x] Create bug tracker for low-priority issues (post-launch backlog)

**Day-by-Day**:
- **Day 1**: Cross-browser testing, device testing, fix compatibility issues
- **Day 2**: Screen reader testing (NVDA, VoiceOver), fix accessibility issues
- **Day 3**: Performance optimization, Lighthouse CI fixes
- **Day 4**: Load testing (k6), security audit, fix vulnerabilities
- **Day 5**: Final fixes, external accessibility audit, penetration test results review

**Acceptance Criteria**:
- ✅ Works correctly on all target browsers (Chrome, Safari, Firefox, Edge)
- ✅ Works on iPhone, Samsung, iPad
- ✅ Fully navigable with keyboard only
- ✅ NVDA and VoiceOver can navigate entire site
- ✅ All pages Lighthouse performance >90
- ✅ All pages Lighthouse accessibility >90
- ✅ Load tests pass with <5% error rate at 1000 concurrent users
- ✅ No critical or high security vulnerabilities
- ✅ External accessibility audit approves WCAG 2.1 AA compliance
- ✅ Penetration test finds no critical issues

---

### **Phase 9: Production Launch & Hardening (3-4 days)**

**Objective**: Deploy to production, configure monitoring, execute launch.

**Dependencies**: All previous phases

**File Checklist**:
- [x] Production environment setup (AWS ECS, RDS, ElastiCache)
- [x] Production environment variables (.env.production)
- [x] Production database setup (with encryption at rest)
- [x] Production secrets management (AWS Secrets Manager)
- [x] Production SSL certificates (Cloudflare)
- [x] Production monitoring (New Relic, Sentry)
- [x] Production alerting rules
- [x] Production backup configuration (automated daily)
- [x] Disaster recovery procedures documentation
- [x] Deployment runbooks
- [x] Rollback procedures
- [x] Launch checklist
- [x] Post-launch monitoring dashboard

**Day-by-Day**:
- **Day 1**: Production environment provisioning (replicate staging)
- **Day 2**: Production configuration, secrets management, SSL
- **Day 3**: Production deployment, smoke tests, monitoring verification
- **Day 4**: Final launch checklist, go-live, post-launch monitoring

**Acceptance Criteria**:
- ✅ Production environment live at final domain
- ✅ SSL certificates valid
- ✅ Database encrypted at rest
- ✅ All secrets in AWS Secrets Manager (not in code)
- ✅ New Relic monitoring active
- ✅ Sentry error tracking active
- ✅ Alerts configured and tested
- ✅ Automated backups running
- ✅ Disaster recovery procedures tested
- ✅ Deployment runbook complete
- ✅ Rollback procedure tested
- ✅ Smoke tests pass in production
- ✅ Launch checklist 100% complete
- ✅ Stakeholders notified of successful launch

---

## 📊 Final Timeline Summary

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 1: Foundation, Infrastructure & Analytics | 5-6 days | 5-6 days |
| Phase 2: Design System, UI Components & i18n | 8-9 days | 13-15 days |
| Phase 3: Backend Services & PDPA Compliance | 10-11 days | 23-26 days |
| Phase 4: Frontend Pages & PDPA UI | 6-7 days | 29-33 days |
| Phase 5: Content Management & MOH Compliance | 11-12 days | 40-45 days |
| Phase 5.5: External Integrations | 2-3 days | 42-48 days |
| Phase 6: Booking System & Notifications | 10-12 days | 52-60 days |
| Phase 7: Advanced Features | 5-6 days | 57-66 days |
| Phase 7.5: Content Population & Translation | 3-4 days | 60-70 days |
| Phase 8: QA & Optimization | 4-5 days | 64-75 days |
| Phase 9: Production Launch | 3-4 days | 67-79 days |

**Total: 67-79 days (13.4-15.8 weeks)**

### To Achieve 12-Week (60-day) Timeline:

**Recommended Approach: Parallelization with 3-4 Developer Team**

| Workstream | Developer | Phases | Timeline |
|------------|-----------|--------|----------|
| **Workstream 1: Infrastructure & Backend** | Backend Dev 1 | Phases 1, 3, 5, 5.5 (backend), 6 (backend), 9 | ~40 days |
| **Workstream 2: Frontend & Design** | Frontend Dev 1 | Phases 2, 4, 7 (frontend) | ~30 days |
| **Workstream 3: Integrations & Content** | Backend Dev 2 | Phases 5.5, 6 (integrations), 7.5 (content) | ~25 days |
| **Workstream 4: QA & Testing** | QA Engineer | Continuous testing, Phase 8 | Parallel |

**With parallelization: Estimated 55-60 days (11-12 weeks)** ✅

---

## 🎯 Critical Success Factors

1. **Start Analytics Day 1**: Cannot measure success without data from launch
2. **i18n from Start**: Retrofitting is exponentially harder
3. **PDPA Non-Negotiable**: Legal requirement, cannot launch without it
4. **Testing Continuous**: Not a separate phase
5. **Staging Environment Always**: Deploy continuously to catch issues early
6. **Content Ready**: No content = no launch
7. **Team Size Matters**: 3-4 developers needed for 12-week timeline

---

## ✅ Final Approval Status

**Revised Execution Plan: APPROVED FOR IMPLEMENTATION**

**With Conditions**:
1. ✅ Team size: Minimum 3 developers (2 backend, 1 frontend) + 1 QA engineer
2. ✅ Clarify technology choices: Laravel Nova (yes), MeiliSearch (yes), AWS ECS (yes)
3. ✅ Stakeholder sign-off on timeline: 12 weeks with parallelization OR 14-16 weeks sequential
4. ✅ Budget approved for: Laravel Nova license, professional translation, external audits

