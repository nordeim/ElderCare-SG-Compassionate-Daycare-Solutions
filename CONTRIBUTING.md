# Contributing to ElderCare SG

Thank you for your interest in contributing! We aim to provide a compassionate, accessible experience for caregivers and seniors alike. This guide outlines expectations for contributing code, documentation, design assets, and operational improvements.

## Table of Contents
- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Branching Model](#branching-model)
- [Commit Standards](#commit-standards)
- [Pull Request Checklist](#pull-request-checklist)
- [Testing Expectations](#testing-expectations)
- [Documentation Updates](#documentation-updates)
- [Security & Compliance](#security--compliance)
- [Release Management](#release-management)
- [Community Channels](#community-channels)

## Code of Conduct
We adhere to an inclusive, respectful environment. Review the `CODE_OF_CONDUCT.md` before contributing. Harassment or discriminatory behaviour is not tolerated.

## Getting Started
1. Fork the repository and clone your fork.
2. Install dependencies (`npm install`, `composer install`) or bootstrap with Docker (`docker-compose up`).
3. Copy environment templates (`.env.example`, `frontend/.env.local.example`) and populate secrets per `docs/deployment/README.md`.
4. Verify the baseline test suite: `npm run test`, `npm run test:e2e`, `php artisan test`.

## Branching Model
- `main`: production-ready (protected)
- `develop`: integration branch for upcoming release
- Short-lived branches: `feature/*`, `bugfix/*`, `chore/*`, `hotfix/*`
- Rebase onto `develop` before opening a pull request

Detailed workflow guidance lives in `docs/git-workflow.md`.

## Commit Standards
- Use [Conventional Commits](https://www.conventionalcommits.org/) when possible (`feat:`, `fix:`, `chore:`).
- Keep commits scoped; avoid mixing unrelated changes.
- Reference issue IDs in commit messages or PR descriptions (`refs #123`).

## Pull Request Checklist
- Follow the PR template (if enabled).
- Provide context, screenshots, and risk assessment.
- Confirm automated checks pass locally (`npm run lint`, `npm run test`, `php artisan test`).
- Highlight migrations, env var changes, or infra updates.
- Update relevant docs (`README.md`, `docs/`, runbooks) and link them in the PR.
- Tag reviewers per ownership matrix and request review only when ready.

## Testing Expectations
- **Frontend**: `npm run lint`, `npm run test`, `npm run test:e2e`, `npm run lighthouse` for relevant UI changes.
- **Backend**: `php artisan test`, plus targeted Pest/PHPUnit tests.
- **Accessibility**: Run axe or Pa11y sweeps for UI changes.
- **Performance**: Capture Lighthouse before/after for significant UI updates.
- Provide test evidence in PR description (logs or screenshots).

## Documentation Updates
- Keep architectural references synchronized: `docs/AGENT.md`, `Project_Architecture_Document.md`, `docs/deployment/`.
- Update change logs or ADRs when modifying architecture or infrastructure decisions.
- Describe onboarding impacts in relevant docs (e.g., `docs/phase1-execution-plan.md`).

## Security & Compliance
- Do not commit secrets. Use GitHub Secrets, AWS Secrets Manager, or SSM Parameter Store.
- Adhere to PDPA requirements: log consent changes, avoid exporting PII in dev logs.
- Review changes touching authentication, authorization, or PII with a lead engineer.

## Release Management
- Feature complete & tested before merging into `main`.
- Tag releases using semantic versioning (`vX.Y.Z`).
- Coordinate staging and production deploys using `deploy.yml` pipelines.

## Community Channels
- Slack: `#eldercare-dev`
- Architecture sync: Weekly, every Tuesday 10:00 SGT
- Incident bridge: See runbook `docs/runbooks/incident-response.md`

We appreciate your collaboration in building a dignified eldercare experience for Singaporean families. Thank you for contributing! 
