# ElderCare SG — Database Migration Execution Plan

## 1. Purpose & Scope
- **Goal**: Align Laravel migration scripts under `database/migrations/` with the canonical schema defined in `database_schema.sql`.
- **Scope**: Covers core entities, compliance tables, polymorphic content, queue infrastructure, and derived views required for Phase 1–6 deliverables in `codebase_completion_master_plan.md`.
- **References**: `database_schema.sql`, `Project_Architecture_Document.md`, `docs/AGENT.md` sections 5–7, and ADRs related to data architecture.

## 2. Current State Assessment
- **Existing migrations**:
  - `0001_01_01_000000_create_users_table.php` — baseline `users`, `password_reset_tokens`, `sessions` tables.
  - `0001_01_01_000001_create_cache_table.php` — cache infrastructure (retain for Laravel operations).
  - `0001_01_01_000002_create_jobs_table.php` — review required to confirm parity with desired `jobs` definition.
- **Gap summary**:
  - Missing columns/constraints for `users` (roles, PDPA fields, soft deletes, indexes).
  - Absent tables for PDPA compliance, content localization, bookings workflow, media, integrations.
  - Absent views (`active_centers_summary`, `user_booking_history`).

## 3. Target Schema Overview
| Category | Tables |
| --- | --- |
| Foundation | `users`, `password_reset_tokens`, `failed_jobs`, `personal_access_tokens` |
| Core Entities | `profiles`, `centers`, `faqs`, `subscriptions`, `contact_submissions` |
| Dependents | `services`, `staff` |
| Relationships | `bookings`, `testimonials` |
| Compliance | `consents`, `audit_logs` |
| Polymorphic | `media`, `content_translations` |
| Queue | `jobs` |
| Derived Views | `active_centers_summary`, `user_booking_history` |

## 4. Migration Workstream Breakdown
1. **Update Core Auth Migration**
   - Expand `users` table to match schema (phone, role enum, language, soft deletes, indices).
   - Ensure `password_reset_tokens` matches SQL definition.
   - Decide on `sessions` table retention (document decision).
   - Create dedicated migration for `personal_access_tokens` if absent.

2. **Foundation Entities**
   - `profiles` (one-to-one users, PDPA-ready fields).
   - `failed_jobs` (Laravel default but ensure fields per SQL).

3. **Centers & Related Data**
   - `centers` with JSON metadata, geo fields, check constraints, full-text index.
   - `services`, `staff` with foreign keys & soft deletes (where required).

4. **Support Tables**
   - `faqs`, `subscriptions`, `contact_submissions` including JSON preference columns and moderation statuses.

5. **Relationship-Heavy Tables**
   - `bookings` with Calendly integration fields, questionnaire JSON, status enums, soft deletes, composite indexes.
   - `testimonials` with moderation workflow and rating constraint.

6. **Compliance Tables**
   - `consents` capturing versioning/IP/user agent.
   - `audit_logs` polymorphic tracking with JSON snapshots.

7. **Polymorphic Content**
   - `media` (Cloudflare Stream IDs, alt text, ordering).
   - `content_translations` (locale enum, workflow statuses, translator references).

8. **Queue Infrastructure**
   - `jobs` table (available/reserved timestamps) aligned with SQL.
   - Add composite indexes via dedicated migration (`ALTER TABLE` statements executed via Blueprint or raw SQL).

9. **Views Creation**
   - `active_centers_summary` and `user_booking_history` created in migrations using `DB::statement`.
   - Ensure `down()` drops views.

## 5. Implementation Order & File Naming Guidance
- Maintain chronological order matching dependencies (users → profiles → centers → services → bookings → testimonials → compliance → polymorphic → indexes/views).
- Suggested naming convention:
  - `2024_10_XX_010000_update_users_and_tokens_tables.php`
  - `2024_10_XX_011000_create_profiles_table.php`
  - ... through to `2024_10_XX_020500_create_reporting_views.php`
- Use timestamps reflecting execution order to avoid foreign key conflicts.

## 6. Technical Considerations
- **Enums**: Implement via `enum()` helper or `string` + check constraint; prefer `enum()` for readability (MySQL 8.0 supports).
- **Check constraints**: Apply using `DB::statement` post table creation for conditions like `capacity > 0` and rating bounds.
- **JSON columns**: Utilize `$table->json()`; ensure corresponding Laravel models cast attributes.
- **Full-text indexes**: `$table->fullText(['name', 'description'])` where supported.
- **Views**: Define via raw SQL ensuring idempotency and compatibility with MySQL 8.0.

## 7. Verification Strategy
- Run `php artisan migrate:fresh` to ensure clean schema creation.
- Use MySQL `SHOW CREATE TABLE` / `information_schema` queries to spot-check structure.
- Compare resulting schema against `database_schema.sql` (fields, constraints, indexes, views).
- Update/extend feature tests to cover CRUD operations on new entities.

## 8. Documentation & Follow-Up Actions
- Update `docs/AGENT.md` change log after migrations land.
- Create ADR if deviations from SQL (e.g., retention of `sessions` table) are necessary.
- Extend `DatabaseSeeder.php` and add factories once schema stabilized (tracked separately).
- Record risk/mitigation updates in `Project_Architecture_Document.md` if schema changes impact compliance/performance.

## 9. Open Decisions & Dependencies
- **Sessions Table** (`sessions`): confirm requirement with architecture team; default Laravel table may remain for session driver support.
- **Cache tables**: keep existing migration; not part of SQL but operationally required.
- **Timeline Alignment**: Migration completion is prerequisite for Phases 3, 5, 6 in `codebase_completion_master_plan.md`.

---
Prepared by Cascade AI Agent — 2025-10-08 (SGT).
