<?php

namespace App\Services\Newsletter;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

class MailchimpService
{
    private $client;
    private ?string $listId;

    public function __construct($client = null)
    {
        // If no client provided, create one via factory (uses env vars)
        $this->client = $client ?? MailchimpClientFactory::create();
        $this->listId = env('MAILCHIMP_LIST_ID');
    }

    private function enabled(): bool
    {
        return filter_var(env('MAILCHIMP_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Subscribe or update a subscriber (idempotent).
     */
    public function subscribe(string $email, array $preferences = []): bool
    {
        Log::info('MailchimpService::subscribe called', ['email' => $email]);

        if (! $this->enabled()) {
            Log::info('Mailchimp disabled; subscribe no-op', ['email' => $email]);
            return true;
        }

        if (! $this->listId) {
            Log::error('Mailchimp list id not configured');
            return false;
        }

        try {
            $subscriberHash = md5(strtolower($email));

            $body = array_merge([
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'status' => 'subscribed',
                'merge_fields' => $preferences,
            ], []);

            // Mailchimp Marketing SDK uses: $client->lists->setListMember(listId, subscriberHash, body)
            $this->client->lists->setListMember($this->listId, $subscriberHash, $body);

            return true;
        } catch (RequestException $e) {
            Log::error('Mailchimp subscribe request failed', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Mailchimp subscribe failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function unsubscribe(string $email): bool
    {
        Log::info('MailchimpService::unsubscribe called', ['email' => $email]);

        if (! $this->enabled()) {
            Log::info('Mailchimp disabled; unsubscribe no-op', ['email' => $email]);
            return true;
        }

        if (! $this->listId) {
            Log::error('Mailchimp list id not configured');
            return false;
        }

        try {
            $subscriberHash = md5(strtolower($email));
            $body = ['status' => 'unsubscribed'];
            $this->client->lists->setListMember($this->listId, $subscriberHash, $body);

            return true;
        } catch (RequestException $e) {
            Log::error('Mailchimp unsubscribe request failed', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Mailchimp unsubscribe failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function updatePreferences(string $email, array $preferences = []): bool
    {
        Log::info('MailchimpService::updatePreferences called', ['email' => $email, 'prefs' => $preferences]);
        return $this->subscribe($email, $preferences);
    }

    /**
     * Sync a local subscription to Mailchimp. Idempotent.
     */
    public function syncSubscription(int $subscriptionId): bool
    {
        $sub = Subscription::find($subscriptionId);

        if (! $sub) {
            Log::warning('MailchimpService::syncSubscription - subscription not found', ['id' => $subscriptionId]);
            return false;
        }

        if (! $this->enabled()) {
            Log::info('Mailchimp disabled; syncSubscription no-op', ['id' => $subscriptionId]);
            // Optionally set last_synced_at when in no-op mode
            $sub->last_synced_at = now();
            $sub->save();
            return true;
        }

        if (! $this->listId) {
            Log::error('Mailchimp list id not configured');
            return false;
        }

        try {
            $email = $sub->email;
            $subscriberHash = md5(strtolower($email));

            $body = [
                'email_address' => $email,
                'status_if_new' => $sub->mailchimp_status === 'subscribed' ? 'subscribed' : 'pending',
                'status' => $sub->mailchimp_status === 'subscribed' ? 'subscribed' : 'pending',
                'merge_fields' => (array) $sub->preferences,
            ];

            $resp = $this->client->lists->setListMember($this->listId, $subscriberHash, $body);

            // Mailchimp returns an object with id for the member
            $mcId = $resp['id'] ?? null;
            if ($mcId) {
                $sub->mailchimp_subscriber_id = $mcId;
            }

            $sub->mailchimp_status = 'subscribed';
            $sub->last_synced_at = now();
            $sub->save();

            return true;
        } catch (RequestException $e) {
            Log::error('Mailchimp sync request failed', ['id' => $subscriptionId, 'error' => $e->getMessage()]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Mailchimp sync failed', ['id' => $subscriptionId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Handle incoming webhook payloads from Mailchimp.
     */
    public function handleWebhook(array $payload): bool
    {
        Log::info('MailchimpService::handleWebhook', ['payload' => $payload]);

        // Basic mapping: handle unsubscribe/cleaned/profile updates
        try {
            $type = $payload['type'] ?? null;
            $data = $payload['data'] ?? [];

            if (! $type || ! isset($data['email'])) {
                Log::warning('Mailchimp webhook missing fields', ['payload' => $payload]);
                return false;
            }

            $email = $data['email'];
            $sub = Subscription::where('email', $email)->first();

            if (! $sub) {
                // not a local subscriber, ignore
                return true;
            }

            if ($type === 'unsubscribe' || $type === 'cleaned') {
                $sub->mailchimp_status = 'unsubscribed';
                $sub->last_synced_at = now();
                $sub->save();
                return true;
            }

            // profile or other events: update merge fields if present
            if (isset($data['merges']) && is_array($data['merges'])) {
                $sub->preferences = array_merge((array) $sub->preferences, $data['merges']);
                $sub->last_synced_at = now();
                $sub->save();
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Mailchimp webhook processing failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

