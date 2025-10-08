# CI/CD Overview

## Pipelines
- **CI workflow** (`.github/workflows/ci.yml`)
  - Runs on pushes to `main`/`develop` and PRs into `main`.
  - Jobs: frontend lint/tests (`npm run lint`, `npm run type-check`, `npm run test:coverage`, Playwright), backend PHPUnit with migrations, Lighthouse CI, accessibility (Pa11y).
- **Deploy workflow** (`.github/workflows/deploy.yml`)
  - `deploy-staging`: Executes on `main` branch pushes (auto-deploy requirement for Phase 1). Add deployment commands when infrastructure release pipeline is ready.
  - `deploy-production`: Executes on `v*` tags; reserved for manual release promotion.

## Required GitHub Secrets
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`: Terraform/Deploy credentials.
- `STAGING_API_URL`, `PRODUCTION_API_URL`: Injected to frontend build steps.
- `LHCI_GITHUB_APP_TOKEN`: Lighthouse CI auth (already referenced in CI workflow).
- `SENTRY_AUTH_TOKEN`, `SENTRY_ORG`, `SENTRY_PROJECT`: For release sourcemaps (queued for Phase 1.5 once monitoring is live).
- `PERCY_TOKEN`: Required for visual regression captures (`npm run test:percy`).
- `NEWRELIC_LICENSE_KEY`: Agent activation during deploy.
- `HOTJAR_SITE_ID`, `GA_MEASUREMENT_ID`: Provided to frontend build as env vars.
- `MAILCHIMP_API_KEY`, `TWILIO_AUTH_TOKEN`, `CALENDLY_API_KEY`: Used by backend tests/deployments.

## Deployment Checklist
1. Confirm `ci.yml` passes for target commit.
2. Ensure `.env` templates for target environment are populated with secrets (see `docs/deployment/README.md`).
3. Provide Cloudflare cache purge token (if required) via GitHub Secret `CLOUDFLARE_API_TOKEN`.
4. Update Terraform state prior to deployment (`terraform plan` clean).
5. Tag releases with semantic versioning (`vX.Y.Z`) after staging approval.
6. Monitor Sentry/New Relic dashboards post-deploy; update incident log if anomalies detected.

## Next Steps
- Populate secret placeholders in repository settings.
- Implement deployment scripts within `deploy.yml` (Terraform apply + ECS service update).
- Add status badges to `README.md` referencing CI and staging deploy once validated.
