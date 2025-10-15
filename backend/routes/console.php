<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Health check command
 *
 * Usage: php artisan health
 * Returns exit code 0 when DB and cache are reachable; non-zero otherwise.
 */
Artisan::command('health', function () {
    $this->comment('Running application health checks...');

    $service = app(\App\Services\Health\HealthService::class);
    $result = $service->check(['detailed' => false]);

    // Print a small summary
    foreach ($result['checks'] as $key => $check) {
        if (! empty($check['ok'])) {
            $this->info(ucfirst($key) . ': OK');
        } else {
            $this->error(ucfirst($key) . ': FAILED');
        }
    }

    if ($result['ok']) {
        $this->info('Health checks passed');
        return 0;
    }

    $this->error('Health checks failed');
    return 1;
})->purpose('Run quick application health checks (DB + cache)');


/**
 * Mailchimp health check (integration/gated)
 * Usage: php artisan mailchimp:health [--list=LIST_ID]
 * Exit codes:
 *   0 = success (Mailchimp reachable and list found)
 *   2 = MAILCHIMP_ENABLED is false (integration disabled)
 *   3 = missing environment variables (API key/server/list)
 *   4 = Mailchimp SDK missing or factory returned noop
 *   5 = API/network error or list not found
 */
Artisan::command('mailchimp:health {--list=}', function () {
    $this->comment('Running Mailchimp health check...');

    $enabled = filter_var(env('MAILCHIMP_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    if (! $enabled) {
        $this->info('Mailchimp integration disabled (MAILCHIMP_ENABLED=false)');
        return 2;
    }

    $apiKey = env('MAILCHIMP_API_KEY');
    $server = env('MAILCHIMP_SERVER');
    $listId = $this->option('list') ?? env('MAILCHIMP_LIST_ID');

    if (! $apiKey || ! $server || ! $listId) {
        $this->error('Missing Mailchimp configuration. Ensure MAILCHIMP_API_KEY, MAILCHIMP_SERVER and MAILCHIMP_LIST_ID (or --list) are set.');
        return 3;
    }

    // Create client via factory
    try {
        $client = \App\Services\Newsletter\MailchimpClientFactory::create();
    } catch (\Throwable $e) {
        $this->error('Failed to create Mailchimp client: ' . $e->getMessage());
        return 4;
    }

    // If factory returned a noop object without getList, treat as missing SDK
    if (! isset($client->lists) || ! is_callable([$client->lists, 'getList'])) {
        $this->error('Mailchimp SDK not available or factory returned noop client.');
        return 4;
    }

    // Perform a safe read-only API call
    try {
        $resp = $client->lists->getList($listId);
        $this->info('Mailchimp OK: list found');
        return 0;
    } catch (\Throwable $e) {
        $this->error('Mailchimp API error: ' . $e->getMessage());
        return 5;
    }
})->purpose('Run a Mailchimp integration health check (integration-only, gated)');
