<?php

namespace App\Services\Newsletter;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

/**
 * Minimal MailchimpService stub used for syncing subscriptions.
 * This provides method signatures expected by SyncMailchimpSubscriptionJob and controllers.
 * Implement real API calls when ready; for now methods return boolean or null as appropriate.
 */
class MailchimpService
{
    public function subscribe(string $email, array $preferences = []): bool
    {
        // TODO: integrate with Mailchimp API. For now, return true as a stub.
        Log::info('MailchimpService::subscribe (stub)', ['email' => $email]);
        return true;
    }

    public function unsubscribe(string $email): bool
    {
        Log::info('MailchimpService::unsubscribe (stub)', ['email' => $email]);
        return true;
    }

    public function updatePreferences(string $email, array $preferences = []): bool
    {
        Log::info('MailchimpService::updatePreferences (stub)', ['email' => $email, 'prefs' => $preferences]);
        return true;
    }

    public function syncSubscription(int $subscriptionId): bool
    {
        // Attempt to find the subscription record and pretend to sync it.
        $sub = Subscription::find($subscriptionId);

        if (! $sub) {
            Log::warning('MailchimpService::syncSubscription - subscription not found', ['id' => $subscriptionId]);
            return false;
        }

        // In a real implementation, call Mailchimp API and update subscription status fields.
        Log::info('MailchimpService::syncSubscription (stub) - synced', ['id' => $subscriptionId]);
        $sub->mailchimp_status = 'subscribed';
        $sub->mailchimp_subscriber_id = 'mc_stub_' . substr((string) $subscriptionId, 0, 8);
        $sub->last_synced_at = now();
        $sub->save();

        return true;
    }

    public function handleWebhook(array $payload, ?string $signature = null): bool
    {
        // Validate signature if configured, then process payload.
        Log::info('MailchimpService::handleWebhook (stub)', ['payload' => $payload]);
        return true;
    }
}
