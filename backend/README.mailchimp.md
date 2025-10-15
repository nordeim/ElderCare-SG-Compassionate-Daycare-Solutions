# Mailchimp Integration (staging / production)

This file explains how to enable Mailchimp for staging or production and how to run the (optional) integration tests.

1) Environment variables

- Set these in your environment or secrets manager for staging/production:

```
MAILCHIMP_ENABLED=true
MAILCHIMP_API_KEY=your-mailchimp-api-key
MAILCHIMP_LIST_ID=your-mailchimp-list-id
MAILCHIMP_SERVER=us19  # or the server prefix in your Mailchimp API key
MAILCHIMP_WEBHOOK_SECRET=long-random-token-for-webhook
```

2) Enabling Mailchimp

- By default `MAILCHIMP_ENABLED` is `false` in `.env.example` to prevent accidental writes during development and CI.
- To enable in staging, set `MAILCHIMP_ENABLED=true` in your staging environment variables and provide valid `MAILCHIMP_API_KEY` and `MAILCHIMP_LIST_ID` in the secret store.

3) Webhook configuration

- Configure a Mailchimp webhook in your Mailchimp audience settings to POST to:

```
https://staging.example.com/api/v1/webhooks/mailchimp?token=${MAILCHIMP_WEBHOOK_SECRET}
```

- The webhook handler expects `type` and `data.email` in the payload and updates local `subscriptions` by email.

4) Running integration tests (manual, gated)

- Integration tests are not enabled in CI. Run them locally or in staging only when `MAILCHIMP_ENABLED=true` and secrets are available.

Example (manual):

```bash
# Ensure environment variables are set (do not commit secrets)
export MAILCHIMP_ENABLED=true
export MAILCHIMP_API_KEY=...
export MAILCHIMP_LIST_ID=...
export MAILCHIMP_SERVER=us19
export MAILCHIMP_WEBHOOK_SECRET=...

# Run the integration test group (if present)
cd backend
./vendor/bin/phpunit --group integration
```

5) Safety & rollback

- If you observe unexpected behavior, disable Mailchimp by setting `MAILCHIMP_ENABLED=false` in your environment; this prevents further outgoing calls.
- `SyncMailchimpSubscriptionJob` will be retried per job config; to stop retries, remove jobs from the queue or set `MAILCHIMP_ENABLED=false`.

6) Notes

- Unit tests mock the Mailchimp SDK; the real SDK is only invoked when `MAILCHIMP_ENABLED` is true and valid credentials exist.
- For troubleshooting, check application logs (Sentry/NewRelic) and queue worker logs.

## Mailchimp health command

Use the artisan command to verify Mailchimp connectivity and credentials. This command is gated and intended for staging/ops use only.

```bash
# From backend folder
php artisan mailchimp:health
# Or check a specific list id
php artisan mailchimp:health --list=YOUR_LIST_ID
```

Exit codes:

- 0 = success (Mailchimp reachable and list found)
- 2 = MAILCHIMP_ENABLED=false (integration disabled)
- 3 = missing environment variables (API key/server/list)
- 4 = Mailchimp SDK missing or factory returned noop
- 5 = API/network error or list not found

Note: Do not run this in CI unless you intentionally inject real Mailchimp secrets for a gated integration step.
