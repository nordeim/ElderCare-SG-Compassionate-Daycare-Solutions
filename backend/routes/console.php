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

    $ok = true;

    // 1) Database connectivity (simple query)
    try {
        $start = microtime(true);
        \DB::connection()->getPdo();
        $this->info('Database: OK');
    } catch (\Throwable $e) {
        $this->error('Database: FAILED - ' . $e->getMessage());
        $ok = false;
    }

    // 2) Cache/Redis connectivity (if configured)
    try {
        if (config('cache.default') === 'redis' || config('database.redis.client')) {
            \Cache::store(config('cache.default'))->put('health_check', 'ok', 5);
            $this->info('Cache/Redis: OK');
        } else {
            $this->info('Cache: not redis (skipped)');
        }
    } catch (\Throwable $e) {
        $this->error('Cache/Redis: FAILED - ' . $e->getMessage());
        $ok = false;
    }

    if ($ok) {
        $this->info('Health checks passed');
        return 0;
    }

    $this->error('Health checks failed');
    return 1;
})->purpose('Run quick application health checks (DB + cache)');
