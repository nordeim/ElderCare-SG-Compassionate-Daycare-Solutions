Absolutely. Below is the **comprehensive, production-grade Phase 3 Backend Code Completion Sub-Plan** for **ElderCare SG**, meticulously designed to align with:

- The **complete database schema** (18 tables, PDPA/MOH compliance, polymorphic relationships)
- The **service-oriented Laravel 12 architecture**
- The **PDPA and MOH regulatory requirements**
- The **execution roadmap** (Phase 3 = Core Backend Services & PDPA Compliance)
- The goal of delivering a **stakeholder-ready production demo**

This plan is structured to be **fully actionable by an independent coding agent**, with explicit file deliverables, feature checklists, and architectural guardrails.

---

# 🧱 ElderCare SG — Phase 3 Backend Code Completion Sub-Plan  
## **Objective**: Implement a production-ready, PDPA-compliant backend service layer with authentication, consent, audit logging, data export, and API contracts—**ready for frontend integration and stakeholder demo**.

---

## 📌 Core Principles Applied
1. **Service Layer Architecture**: Zero business logic in controllers; all logic in `Domain/*` services.
2. **PDPA by Design**: Consent ledger, soft deletes, audit trail, data export, and right-to-be-forgotten workflows are **non-optional**.
3. **MOH Alignment**: License validation, staff credential visibility, and center accreditation are modeled explicitly.
4. **Testability**: Every service has PHPUnit unit tests; every API endpoint has feature tests.
5. **Observability**: All mutations logged via `audit_logs`; all consent changes captured in `consents`.
6. **Security**: Sanctum for API auth, rate limiting, input validation via Form Requests, RBAC via Policies.

---

## 🗂️ File Creation Matrix (Backend Only)

### **I. Authentication & User Management**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/app/Http/Controllers/Auth/AuthController.php` | Thin controller for register/login/logout | - [ ] Delegates to `AuthService`<br>- [ ] Returns consistent JSON responses |
| `backend/app/Domain/Auth/AuthService.php` | Core auth logic: registration, login, session | - [ ] Validates email uniqueness<br>- [ ] Hashes password<br>- [ ] Creates user + profile atomically<br>- [ ] Records initial consent (`account`, `functional_cookies`)<br>- [ ] Triggers email verification |
| `backend/app/Http/Requests/Auth/RegisterRequest.php` | Validation for registration | - [ ] Validates email, password strength, phone format<br>- [ ] Requires explicit consent checkboxes |
| `backend/app/Http/Requests/Auth/LoginRequest.php` | Validation for login | - [ ] Validates credentials<br>- [ ] Rate-limited via middleware |
| `backend/app/Domain/Auth/ConsentService.php` | Manages PDPA consents | - [ ] Records consent with IP, UA, version<br>- [ ] Supports withdrawal<br>- [ ] Snapshots consent text |
| `backend/database/factories/UserFactory.php` | Test data factory | - [ ] Creates user + profile<br>- [ ] Attaches sample consents |
| `backend/tests/Feature/Auth/AuthTest.php` | End-to-end auth flow tests | - [ ] Register → verify email → login<br>- [ ] Consent recorded in DB<br>- [ ] Invalid credentials rejected |
| `backend/tests/Unit/Domain/Auth/AuthServiceTest.php` | Unit tests for auth logic | - [ ] Password hashing verified<br>- [ ] Duplicate email blocked |

---

### **II. PDPA Compliance Core**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/app/Domain/PDPA/AuditLogService.php` | Central audit logging service | - [ ] Logs `created`, `updated`, `deleted`, `restored`<br>- [ ] Captures `old_values`/`new_values` as JSON<br>- [ ] Records IP, UA, URL<br>- [ ] Polymorphic (`auditable_type`, `auditable_id`) |
| `backend/app/Domain/PDPA/DataExportService.php` | PDPA-mandated data export (JSON) | - [ ] Exports all user data: user, profile, bookings, testimonials, consents<br>- [ ] Excludes soft-deleted records unless within 30-day grace<br>- [ ] Returns downloadable JSON via API |
| `backend/app/Domain/PDPA/AccountDeletionService.php` | Right-to-be-forgotten workflow | - [ ] Soft-deletes user (`deleted_at = now()`)<br>- [ ] Schedules hard-delete job after 30 days<br>- [ ] Anonymizes audit logs after retention period (7 years) |
| `backend/app/Jobs/PDPA/HardDeleteUserJob.php` | Background job for permanent deletion | - [ ] Runs after 30-day grace period<br>- [ ] Deletes user, profile, bookings (cascaded)<br>- [ ] Preserves anonymized audit logs |
| `backend/app/Http/Controllers/PDPA/DataExportController.php` | API endpoint for data export | - [ ] Requires auth<br>- [ ] Returns JSON blob<br>- [ ] Logs access in `audit_logs` |
| `backend/app/Http/Controllers/PDPA/AccountDeletionController.php` | API endpoint for deletion request | - [ ] Requires auth<br>- [ ] Triggers soft-delete + job<br>- [ ] Confirms via email |
| `backend/tests/Feature/PDPA/DataExportTest.php` | Tests data export integrity | - [ ] All user data included<br>- [ ] Format matches schema |
| `backend/tests/Feature/PDPA/AccountDeletionTest.php` | Tests deletion workflow | - [ ] User soft-deleted<br>- [ ] Job queued<br>- [ ] Subsequent login fails |

---

### **III. Consent & Cookie Management**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/app/Http/Controllers/Consent/ConsentController.php` | API for updating consents | - [ ] Accepts `consent_type`, `consent_given`<br>- [ ] Delegates to `ConsentService` |
| `backend/app/Domain/Auth/ConsentService.php` *(see above)* | Already defined | - [ ] Handles all consent types: `marketing_email`, `analytics_cookies`, etc.<br>- [ ] Enforces versioned privacy policy |
| `backend/database/migrations/2025_10_15_000000_add_consent_version_to_users.php` | Tracks current policy version | - [ ] Adds `privacy_policy_version` to `users` |

---

### **IV. Role-Based Access Control (RBAC)**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/app/Policies/UserPolicy.php` | Authorization for user actions | - [ ] `viewAny`, `view`, `update`, `delete`<br>- [ ] Admins can manage all users |
| `backend/app/Http/Middleware/EnsureEmailIsVerified.php` | Blocks unverified users | - [ ] Redirects API to `/verify-email` |
| `backend/app/Http/Middleware/RoleMiddleware.php` | Enforces role checks | - [ ] `role:admin` gate |

---

### **V. API Foundation & Observability**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/routes/api.php` | API routes (v1) | - [ ] `/auth/register`, `/auth/login`<br>- [ ] `/pdpa/export`, `/pdpa/delete`<br>- [ ] `/consents`<br>- [ ] All under `/api/v1` |
| `backend/app/Http/Middleware/RateLimitMiddleware.php` | 60 req/min per IP | - [ ] Uses Laravel’s built-in rate limiter |
| `backend/app/Exceptions/Handler.php` | Global exception handler | - [ ] Returns consistent JSON error format<br>- [ ] Logs to Sentry |
| `backend/config/sanctum.php` | Sanctum config | - [ ] Stateful for SPA<br>- [ ] Token expiration = 1 year |
| `backend/app/Providers/DomainServiceProvider.php` | Binds services to container | - [ ] `AuditLogService`, `ConsentService`, etc. |

---

### **VI. Documentation & Contracts**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/docs/openapi/auth.yaml` | OpenAPI spec for auth | - [ ] `/register`, `/login` schemas<br>- [ ] Error responses |
| `backend/docs/openapi/pdpa.yaml` | OpenAPI spec for PDPA endpoints | - [ ] `/export`, `/delete` |
| `docs/backend/phase3-kickoff.md` | Architecture decision record | - [ ] Explains service boundaries<br>- [ ] PDPA/MOH compliance strategy<br>- [ ] Links to OpenAPI |

---

### **VII. Testing & Quality**

| File | Description | Feature Checklist |
|------|-------------|-------------------|
| `backend/tests/CreatesApplication.php` | Test bootstrap | - [ ] Uses SQLite in memory |
| `backend/phpunit.xml` | Test config | - [ ] Excludes `vendor`<br>- [ ] Coverage >90% |
| `backend/tests/Feature/Consent/ConsentTest.php` | Consent flow tests | - [ ] Consent recorded with IP/UA<br>- [ ] Withdrawal works |

---

## 🔐 PDPA & MOH Compliance Mapping

| Requirement | Implementation |
|------------|----------------|
| **Explicit consent** | `consents` table + `ConsentService` |
| **30-day grace period** | Soft delete (`deleted_at`) + `HardDeleteUserJob` |
| **7-year audit retention** | `audit_logs` never hard-deleted; anonymized after 7 years (future job) |
| **Data export** | `DataExportService` returns full JSON |
| **MOH license tracking** | `centers.moh_license_number`, `license_expiry_date` |
| **Staff credentials** | `staff.qualifications` (JSON array) |

---

## ⏱️ Execution Timeline (Backend Focus)

| Day | Focus |
|-----|-------|
| Day 1 | Auth service, models, factories |
| Day 2 | Consent + AuditLog services |
| Day 3 | Data export + account deletion |
| Day 4 | API controllers, routes, middleware |
| Day 5 | OpenAPI docs, RBAC, rate limiting |
| Day 6 | Full test suite (unit + feature) |
| Day 7 | Polish, demo prep, documentation |

---

## ✅ Definition of Done (Phase 3 Backend)

- [ ] Users can register/login with explicit consent
- [ ] All data changes logged in `audit_logs`
- [ ] Users can download full data export (JSON)
- [ ] Account deletion triggers 30-day soft delete + hard delete job
- [ ] API documented in OpenAPI
- [ ] >90% backend test coverage
- [ ] Rate limiting active (60 req/min)
- [ ] All PDPA/MOH fields populated in sample data
- [ ] Stakeholder demo: register → book → export data → delete account

---

This sub-plan is **complete, precise, and production-ready**. It can be handed to an independent Laravel developer or AI coding agent to implement **without ambiguity**.

Would you like me to generate any of the actual code files next (e.g., `AuthService.php`, `DataExportService.php`, or OpenAPI specs)?
