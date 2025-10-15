# MailchimpService implementation plan

Goal
----
Implement a production-ready `MailchimpService` to replace the current stub at `backend/app/Services/Newsletter/MailchimpService.php`. The service will provide robust API client wiring, idempotent sync logic, webhook handling, retries, test coverage, and safe rollout with feature flags and CI safeguards.

Why this plan
----------------
- The repository already references Mailchimp (jobs, controllers, subscription fields, composer dependency). A live client must be implemented carefully to avoid accidental production writes, and to keep CI hermetic.
- We will implement incrementally, with a feature flag and exhaustive tests, and a clear staging rollout path.

Contract (short)
-----------------
- Inputs: Subscription id (int) or email + preferences payload. The `Subscription` model has fields: `email`, `mailchimp_subscriber_id`, `mailchimp_status` (enum: subscribed, unsubscribed, pending, cleaned), `preferences` (json), `last_synced_at`.
- Outputs: Mailchimp API calls that add/update/remove subscribers; update local subscription fields and `last_synced_at` when successful.
- Error modes: network errors, Mailchimp API errors (rate limits, 4xx/5xx), invalid payloads, missing credentials. All failures should be logged, retried (exponential backoff), and surfaced to the job failure hooks.
- Success criteria: Sync job returns true and updates DB fields; controller flows queue jobs; unit tests cover happy path and failure modes; CI remains hermetic (no live API calls) unless explicitly enabled with secrets.

High-level design
------------------
1. Use `mailchimp/marketing` SDK (already declared in composer.json) as the client. Wrap it inside our `MailchimpService` and inject via the constructor. Create a small `MailchimpClientFactory` or bind a typed client in the service container so tests can swap it with a mock.
2. Add config and env flags:
   - `MAILCHIMP_API_KEY` (required for production)
   - `MAILCHIMP_LIST_ID` (target list)
   - `MAILCHIMP_SERVER` (e.g., us19) — or parse from key
   - `MAILCHIMP_ENABLED` (boolean, default=false) — if false, service falls back to no-op mode (or stub behavior)
3. Methods to implement (public surface):
   - `subscribe(string $email, array $preferences = []): bool`
   - `unsubscribe(string $email): bool`
   - `updatePreferences(string $email, array $preferences = []): bool`
   - `syncSubscription(int $subscriptionId): bool` — idempotent; fetches `Subscription` model and syncs to Mailchimp
   - `handleWebhook(array $payload): bool` — process Mailchimp list/webhook events
4. Job integration: `SyncMailchimpSubscriptionJob` already exists and should call the service. Ensure the job continues to function with the new service and that transient failures throw to trigger retries.
5. Webhook security: Mailchimp webhooks are not signed by default. Use a secret token approach (configure `MAILCHIMP_WEBHOOK_SECRET`) and require `?token=` on webhook URLs, or verify `list_id` in payload + source IP whitelist if desired.

Edge cases and considerations
-----------------------------
- Idempotency: Use `mailchimp_subscriber_id` (Mailchimp's `id`) to update subscribers. If it's missing, perform an upsert (create or lookup by email). Prefer `PUT /lists/{list_id}/members/{subscriber_hash}` (Mailchimp uses MD5(email) hashed lowercase) for idempotency.
- Rate limits: implement retry/backoff and capture 429 headers to respect `Retry-After` where applicable.
- Consent and PDPA: Ensure `subscribe` only called for users with consent; controller-level checks exist. When `unsubscribe` is requested, reflect that in DB and call Mailchimp.
- Partial failures: If remote sync fails, leave local record `mailchimp_status` unchanged (or set to `pending_sync`) until success, but log clearly.

Implementation checklist (detailed tasks)
---------------------------------------
Phase 0 — Prep (safe, no code changes to core behavior)
- [ ] Add env var placeholders to `.env.example`: `MAILCHIMP_ENABLED=false`, `MAILCHIMP_API_KEY=`, `MAILCHIMP_LIST_ID=`, `MAILCHIMP_SERVER=`, `MAILCHIMP_WEBHOOK_SECRET=`.
- [ ] Add docs note (this plan file is the authoritative doc).

Phase 1 — Client wiring + config
- [ ] Create `backend/app/Services/Newsletter/MailchimpClientFactory.php` or bind a typed client in a service provider.
    - Create a factory that returns an instance of `\MailchimpMarketing\ApiClient` configured with server & apiKey. Keep this internal; do not call network unless `MAILCHIMP_ENABLED=true`.
- [ ] Update `backend/app/Services/Newsletter/MailchimpService.php` to accept the SDK client via constructor injection and implement the public methods using Mailchimp SDK calls.
- [ ] Add `MAILCHIMP_ENABLED` flag checks: when disabled, methods should log and return `true` for subscribe/unsubscribe (or maintain previous stub behavior) but `syncSubscription` should act as a no-op that updates `last_synced_at` only in local tests (optionally false). Prefer no-op to avoid surprising writes.

Phase 2 — Unit tests and mocks
- [ ] Add unit tests for `MailchimpService` using mocking of the SDK client.
    - Test cases: subscribe success, subscribe failure (HTTP 400/500), unsubscribe success, updatePreferences success, syncSubscription with missing subscription, syncSubscription with subscriber found and updated.
    - Use PHPUnit + Mockery to mock the SDK methods (e.g., `$client->lists->setListMember(...)` or the SDK's method names).
- [ ] Add unit tests for `SyncMailchimpSubscriptionJob` to confirm job delegates to service and handles exceptions.

Phase 3 — Integration test harness (optional & gated)
- [ ] Create an `integration` PHPUnit group that runs against real Mailchimp (only when `MAILCHIMP_ENABLED=true` and secrets injected). These tests should be skipped by default in CI.
- [ ] Add a small `tests/Integration/MailchimpIntegrationTest.php` that can be executed manually with real credentials to smoke test subscribe/unsubscribe workflows.

Phase 4 — Webhook handler + security
- [ ] Implement `handleWebhook(array $payload): bool` to process events `unsubscribe`, `cleaned`, `profile` etc. Map event types to local subscription status updates.
- [ ] Add a route and controller method `POST /api/v1/webhooks/mailchimp` that calls the service after validating `MAILCHIMP_WEBHOOK_SECRET` or query token.
- [ ] Add unit tests for webhook parsing and for the controller to reject invalid tokens.

Phase 5 — CI & deployment changes
- [ ] Ensure no tests perform live network calls by default. Add `@group integration` or similar to integration tests, and configure CI to skip integration tests unless a secret is provided.
- [ ] Add a local healthcheck script `php artisan mailchimp:health` (optional) that attempts a no-op `GET /lists/{id}` but only when enabled and secrets present.

Phase 6 — Rollout & monitoring
- [ ] Deploy to staging with `MAILCHIMP_ENABLED=true` and test account credentials. Run integration smoke tests.
- [ ] Monitor logs and queue failures for 24–48 hours. Specifically watch for jobs failing with 429 or 5xx errors.
- [ ] When stable, enable in production during a low-traffic window.
- [ ] Add Sentry log tags to MailchimpService so errors are easy to filter.

Validation steps & commands
---------------------------
- Run unit tests (all):

```bash
cd backend
./vendor/bin/phpunit --testsuite=Unit
```

- Run Sync job locally (manual smoke):

```bash
cd backend
php artisan tinker
# then in tinker:
>>> \App\Jobs\SyncMailchimpSubscriptionJob::dispatchNow(1);
```

- Run webhook post (during staging test):

```bash
curl -X POST "https://staging.example.com/api/v1/webhooks/mailchimp?token=$MAILCHIMP_WEBHOOK_SECRET" \
  -H "Content-Type: application/json" \
  -d '{"type":"unsubscribe","fired_at":..., "data": {"id":"...","email":"user@example.com","list_id":"$MAILCHIMP_LIST_ID"}}'
```

Safety checklist (must pass before enabling in production)
- [ ] Add env variables and set `MAILCHIMP_ENABLED=false` by default in `.env.example`.
- [ ] Unit tests cover error and success cases for the service. CI passes with Mailchimp calls mocked.
- [ ] Integration tests exist but are gated and not run in CI.
- [ ] Webhook endpoint protected with `MAILCHIMP_WEBHOOK_SECRET` and denies unauthorized requests.
- [ ] Monitoring and Sentry tags added for Mailchimp errors.
- [ ] Rollback plan documented: set `MAILCHIMP_ENABLED=false` to stop outgoing calls; job retries will clear.

Risk analysis & mitigations
--------------------------
- Risk: Accidental writes to production Mailchimp list during development or CI.
  - Mitigation: Default `MAILCHIMP_ENABLED=false`, require explicit env in staging/production; CI never sets secrets unless configured for e2e integration test runs.

- Risk: Rate limits cause job failures or throttling.
  - Mitigation: Implement exponential backoff and pay attention to `Retry-After` headers; queue failed jobs to dead-letter queue; add metrics on retry counts.

- Risk: Missing consent causes PDPA issues.
  - Mitigation: Ensure `subscribe()` is invoked only by controller flows that have validated consent; add assertions in service to refuse to subscribe when consent flagged false (defensive guard).

Acceptance criteria
-------------------
- All unit tests pass with new service mocked.
- `SyncMailchimpSubscriptionJob` succeeds in unit tests and updates `Subscription` as expected.
- Webhook endpoint validates token and updates DB correctly in a staging trial.
- No CI job makes live outgoing calls by default.

Estimated effort and timebox
---------------------------
- Phase 1 (client wiring + config): 2–4 hours
- Phase 2 (unit tests & job tests): 2–3 hours
- Phase 3 (integration harness & webhook): 2–4 hours (manual staging validation)
- Total (to staging-ready): ~1 day of focused work

What I'll do next (if you approve)
----------------------------------
1. Implement the Mailchimp client binding and replace the stub implementation with the real, tested service (Phase 1 + 2). I will add env var placeholders to `.env.example` and a short README note.
2. Add unit tests that mock the SDK and run `phpunit` to ensure no external calls are made.
3. Add a protected webhook route and unit test for it.

If you want me to proceed now, confirm and indicate whether to:
- (A) Implement using the `mailchimp/marketing` SDK (recommended, already in composer.json), or
- (B) Implement a custom HTTP client (Guzzle) wrapper (more work, not necessary).

Tags: mailchimp, integration, CI-safety, PDPA
