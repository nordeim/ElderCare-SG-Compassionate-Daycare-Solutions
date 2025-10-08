# Monitoring & Observability Setup

## Frontend Monitoring
- **Google Analytics 4**: Configure `NEXT_PUBLIC_GA_MEASUREMENT_ID` in `.env` files. Instrumentation handled via `frontend/src/lib/analytics/ga.ts` and `AnalyticsProvider`.
- **Hotjar**: Supply `NEXT_PUBLIC_HOTJAR_ID` and optional `NEXT_PUBLIC_HOTJAR_VERSION`. Initialization handled client-side (`frontend/src/lib/analytics/hotjar.ts`).
- **Sentry**: Provide `NEXT_PUBLIC_SENTRY_DSN`, `NEXT_PUBLIC_SENTRY_ENVIRONMENT`, and optional `NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE`. Bootstrapped in `frontend/src/lib/monitoring/sentry.ts` and included in `AnalyticsProvider`.

## Backend Monitoring
- **Sentry Laravel**: Set `SENTRY_LARAVEL_DSN`, `SENTRY_TRACES_SAMPLE_RATE`, and optional `SENTRY_RELEASE`. Configuration stored in `backend/config/sentry.php`.
- **New Relic**: Ensure `NEWRELIC_LICENSE_KEY` and `NEWRELIC_APPNAME` defined in `.env`. Install New Relic PHP agent on ECS/EKS instances during provisioning.

## Logging & Metrics
- Application logs configured via `backend/config/logging.php` (stack driver). Forward logs to CloudWatch using Firelens or native ECS log drivers.
- For metrics and APM, rely on New Relic dashboards. Configure alerts for response time, error rate, and Apdex.

## Alerting
- **Sentry Alerts**: Configure issue alerts for high-severity errors, with escalation to on-call via Slack/email.
- **New Relic Alerts**: Create policies for CPU, memory, error rate, and latency thresholds.
- **Uptime Monitoring**: Use UptimeRobot (or StatusCake) for HTTP + API endpoints; integrate notifications with incident management channel.

## Deployment Checklist
1. Populate monitoring env vars (`SENTRY_*`, `NEWRELIC_*`, GA, Hotjar) via AWS Secrets Manager or GitHub Secrets.
2. Confirm Terraform modules output required endpoints (RDS, Redis, ECS service) to monitoring dashboards.
3. After deploy, verify Sentry traces and GA/Hotjar sessions appear within dashboard within 10 minutes.
4. Update incident runbook (`docs/runbooks/incident-response.md`) with Sentry and New Relic URLs.
