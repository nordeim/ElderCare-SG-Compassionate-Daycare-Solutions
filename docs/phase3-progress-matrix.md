# Phase 3 Progress Matrix — Summary

This document is generated from the Phase‑3 coding sub‑plan and a repository scan to show what is implemented, partially implemented, or missing so that incoming agents can skip completed work.

## Quick summary
- Files/areas implemented (models, migrations, two services): Core Eloquent models exist (User, Profile, Consent, AuditLog, Center, Service, Staff, Booking, Testimonial, FAQ, Subscription, ContactSubmission, Media, ContentTranslation). Migrations present (18) and four migrations were guarded for MySQL-specific SQL.
- Services: `ConsentService` and `AuditService` implemented. Many other services (CenterService, BookingService, MediaService, TestimonialService, etc.) are missing.
- Controllers: API controllers under `app/Http/Controllers/Api/V1` are not present in the repository scan (missing).
- Tests: Only example tests exist; the planned unit/feature tests are absent.
- CI: No workflow changes yet to run sqlite migrations before tests.

## Matrix (selected entries)

| Workstream | Planned Path | Status | Evidence | Notes |
|-----------:|:-------------|:------:|:--------:|:-----|
| A.1 | `backend/app/Models/User.php` | Implemented | File exists | - |
| A.1 | `backend/app/Models/Center.php` | Implemented | File exists | - |
| A.2 | `backend/app/Services/Consent/ConsentService.php` | Implemented | File exists | - |
| A.2 | `backend/app/Services/Audit/AuditService.php` | Implemented | File exists | - |
| A.2 | `backend/app/Services/User/DataExportService.php` | Missing | Not found | PDPA export service missing |
| A.3 | `backend/app/Http/Controllers/Api/V1/Auth/RegisterController.php` | Missing | Not found | No Api/V1 controllers present in repo scan |
| B.1 | `backend/app/Services/Center/CenterService.php` | Missing | Not found | Center model exists; service missing |
| C.1 | `backend/app/Services/Booking/BookingService.php` | Missing | Not found | Booking workflows unimplemented in service layer |
| D.2 | `backend/app/Services/Media/MediaService.php` | Missing | Not found | Media model exists but S3/service code missing |
| E.1 | `backend/storage/api-docs/openapi.yaml` | Missing | Not found | OpenAPI spec not generated |
| F.1 | `backend/tests/Unit/Models/UserTest.php` | Missing | Not found | Tests are placeholders only |

> Full CSV-export of this matrix is available at `docs/phase3-progress-matrix.csv`.

## Notes for incoming agents
- Do not re-apply migration guards already listed in `docs/ai-agent-onboarding.md` and the Phase‑3 progress update — they were applied to these migrations:
  - `2024_01_01_200000_create_centers_table.php`
  - `2024_01_01_200001_create_faqs_table.php`
  - `2024_01_01_300000_create_services_table.php`
  - `2024_01_01_400001_create_testimonials_table.php`
- High-value missing items: API controllers, service layer implementations (CenterService, BookingService, MediaService), tests (unit + feature), CI sqlite migration step, OpenAPI generation and Postman collection.

## Suggested immediate verification commands

```bash
# Search for raw SQL usage that may be MySQL-specific
grep -R "DB::statement\|FULLTEXT\|fullText\|CHECK\|ALTER TABLE" -n backend || true

# Run sqlite migrations locally (after creating sqlite file)
# (Run inside container or locally with composer/php installed)
php artisan migrate --database=sqlite --force || true

# Run existing tests (placeholders only)
php artisan test || true
```


---

Generated: 2025-10-13
