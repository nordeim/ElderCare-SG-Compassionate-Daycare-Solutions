<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Newsletter\MailchimpClientFactory;
use App\Services\Newsletter\MailchimpService;

class MailchimpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind MailchimpService. The factory will create an SDK client if available.
        $this->app->bind(MailchimpService::class, function ($app) {
            return new MailchimpService(MailchimpClientFactory::create());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // no-op
    }
}
