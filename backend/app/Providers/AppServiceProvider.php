<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

// If Sentry package is available we'll reference it conditionally
// to avoid hard dependency during development unless configured.

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register optional MailchimpServiceProvider if present. We keep this
        // conditional to avoid introducing a hard dependency in environments
        // where the provider or its dependencies may not be available.
        try {
            if (class_exists(\App\Providers\MailchimpServiceProvider::class)) {
                $this->app->register(\App\Providers\MailchimpServiceProvider::class);
            }
        } catch (\Throwable $e) {
            // Do not block the application if provider registration fails during tests.
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Telemetry bootstrap (guarded by environment variables)

        // Sentry (Laravel integration) — register only when DSN is present
        try {
            if (env('SENTRY_LARAVEL_DSN')) {
                if (class_exists('\Sentry\Laravel\ServiceProvider')) {
                    \Sentry\Laravel\Integration::register();
                } else {
                    Log::info('SENTRY_LARAVEL_DSN is set but Sentry SDK not installed.');
                }
            }
        } catch (\Throwable $e) {
            // Do not block the app if telemetry initialization fails
            Log::warning('Sentry init failed: ' . $e->getMessage());
        }

        // New Relic agent (if PHP extension loaded and enabled)
        try {
            if (extension_loaded('newrelic') && env('NEWRELIC_ENABLED', false)) {
                @newrelic_set_appname(env('NEWRELIC_APP_NAME', env('APP_NAME', 'ElderCare SG')));
            }
        } catch (\Throwable $e) {
            Log::info('NewRelic init skipped or failed: ' . $e->getMessage());
        }

        // Register audit observers for core models (PDPA audit trail)
        try {
            if (class_exists(\App\Observers\AuditObserver::class)) {
                if (class_exists(\App\Models\User::class)) {
                    \App\Models\User::observe(\App\Observers\AuditObserver::class);
                }
                if (class_exists(\App\Models\Center::class)) {
                    \App\Models\Center::observe(\App\Observers\AuditObserver::class);
                }
                if (class_exists(\App\Models\Booking::class)) {
                    \App\Models\Booking::observe(\App\Observers\AuditObserver::class);
                }
                if (class_exists(\App\Models\Consent::class)) {
                    \App\Models\Consent::observe(\App\Observers\AuditObserver::class);
                }
                if (class_exists(\App\Models\Testimonial::class)) {
                    \App\Models\Testimonial::observe(\App\Observers\AuditObserver::class);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to register audit observers: ' . $e->getMessage());
        }
    }
}
