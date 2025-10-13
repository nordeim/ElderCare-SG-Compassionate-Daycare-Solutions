<?php

namespace App\Jobs;

use App\Services\Newsletter\MailchimpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMailchimpSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $subscriptionId;
    public $tries = 3;
    public $backoff = 60;

    public function __construct(int $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function handle(MailchimpService $mailchimpService): void
    {
        Log::info('Syncing subscription to Mailchimp', ['subscription_id' => $this->subscriptionId]);

        try {
            $success = $mailchimpService->syncSubscription($this->subscriptionId);

            if ($success) {
                Log::info('Mailchimp sync successful', ['subscription_id' => $this->subscriptionId]);
            } else {
                Log::error('Mailchimp sync failed', ['subscription_id' => $this->subscriptionId]);
                throw new \Exception("Mailchimp sync failed for subscription {$this->subscriptionId}");
            }
        } catch (\Throwable $e) {
            Log::error('Mailchimp sync exception', [
                'subscription_id' => $this->subscriptionId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Mailchimp sync job failed permanently', [
            'subscription_id' => $this->subscriptionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
