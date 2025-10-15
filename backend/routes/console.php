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
