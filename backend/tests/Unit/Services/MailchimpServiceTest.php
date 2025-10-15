<?php

namespace Tests\Unit\Services;

use App\Jobs\SyncMailchimpSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailchimpServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_sync_subscription_job_updates_subscription()
    {
        // Create a subscription record using factory
        $sub = Subscription::factory()->create([
            'email' => 'test+mc@example.com',
            'mailchimp_status' => 'pending',
        ]);

        // Dispatch the job synchronously
        (new SyncMailchimpSubscriptionJob($sub->id))->handle(app(\App\Services\Newsletter\MailchimpService::class));

        $sub->refresh();

    $this->assertEquals('subscribed', $sub->mailchimp_status);
    // When Mailchimp is disabled the service will either preserve an existing
    // mailchimp_subscriber_id or set a noop placeholder (mc_noop). Accept any
    // non-empty string that begins with the project's mc_ prefix.
    $this->assertNotNull($sub->mailchimp_subscriber_id);
    $this->assertStringStartsWith('mc_', $sub->mailchimp_subscriber_id);
    $this->assertNotNull($sub->last_synced_at);
    }
}
