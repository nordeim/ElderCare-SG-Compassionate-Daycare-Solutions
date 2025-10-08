# Git Workflow & Branch Protection

## Branch Strategy
- `main`: production-ready code; protected branch.
- `develop`: integration branch for upcoming release work.
- `feature/*`, `bugfix/*`, `chore/*`: short-lived branches for scoped changes; rebase before merge.

## Branch Protection Guidelines
- Require pull requests for `main` and `develop`.
- Require status checks: CI pipeline (`CI` workflow) must pass before merge.
- Enforce at least one approving review from code owners.
- Disallow force pushes to protected branches.
- Require up-to-date branches (merge/rebase) before merging PR.

## Pull Request Checklist
- Reference related issues or roadmap sections.
- Provide context and implementation summary.
- Include risk assessment + mitigation.
- Attach screenshots or logs for UI or infra changes.
- Confirm automated tests & linting pass (`npm run test`, `php artisan test`, Playwright/Lighthouse as applicable).
- Note any database migrations or config changes.
- Update relevant documentation (`README.md`, `docs/`, runbooks) when behavior changes.

## CI/CD Expectations
- CI lint/test jobs must pass before review.
- Staging deploy job auto-triggers on `main` merges; verify deployment status.
- Releases tagged `v*` trigger production deploy workflow; ensure release notes accompany tags.

## Release Process
1. Merge approved features into `develop`.
2. Create release branch `release/x.y.z` when ready; run regression checks.
3. Tag release (`git tag vX.Y.Z`) after staging sign-off.
4. Push tag to trigger production deploy workflow (`deploy.yml`).
5. Monitor Sentry/New Relic post-deploy and update stakeholders.

## Emergency Fixes
- Branch from `main` using `hotfix/*` naming.
- After deploying fix, merge `hotfix/*` back into `main` and `develop`.

## Secrets & Environment Handling
- Store secrets in GitHub Actions Secrets or AWS Secrets Manager.
- Never commit populated `.env` files.
- Coordinate with DevOps for Terraform state locking and Cloudflare updates.
