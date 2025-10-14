<?php

namespace App\Services\Integration;

use App\Exceptions\CalendlyNotConfiguredException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CalendlyService
{
    protected ?string $apiToken;
    protected ?string $organizationUri;
    protected ?string $webhookSecret;
    protected string $baseUrl = 'https://api.calendly.com';

    public function __construct()
    {
        $this->apiToken = config('services.calendly.api_token');
        $this->organizationUri = config('services.calendly.organization_uri');
        $this->webhookSecret = config('services.calendly.webhook_secret');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiToken);
    }

    public function createEvent(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new CalendlyNotConfiguredException();
        }

        $payload = [
            'event_type' => $data['event_type'] ?? 'One-on-one',
            'invitee' => [
                'name' => $data['user_name'] ?? null,
                'email' => $data['user_email'] ?? null,
            ],
            'start_time' => isset($data['booking_date']) && (is_string($data['booking_date']) || method_exists($data['booking_date'], 'toIso8601String'))
                ? (is_string($data['booking_date']) ? $data['booking_date'] : $data['booking_date']->toIso8601String())
                : null,
        ];

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->retry(2, 500)
            ->post($this->baseUrl . '/scheduled_events', $payload);

        if ($response->failed()) {
            $this->logHttpError('createEvent', $response);
            throw new \RuntimeException('Calendly createEvent failed: ' . $response->status());
        }

        $body = $response->json();

        return [
            'event_id' => $body['data']['id'] ?? ($body['resource']['uri'] ?? null),
            'event_uri' => $body['data']['uri'] ?? $body['resource']['uri'] ?? null,
            'cancel_url' => $body['data']['cancel']['uri'] ?? null,
            'reschedule_url' => $body['data']['reschedule']['uri'] ?? null,
            'raw' => $body,
        ];
    }

    public function cancelEvent(string $eventUriOrId): bool
    {
        if (!$this->isConfigured()) {
            throw new CalendlyNotConfiguredException();
        }

        $endpoint = Str::startsWith($eventUriOrId, 'http') ? $eventUriOrId . '/cancellations' : $this->baseUrl . '/scheduled_events/' . $eventUriOrId . '/cancellations';

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->retry(2, 500)
            ->post($endpoint, []);

        if ($response->failed()) {
            $this->logHttpError('cancelEvent', $response);
            return false;
        }

        return true;
    }

    public function rescheduleEvent(string $eventUriOrId, $newDateTime): array|null
    {
        if (!$this->isConfigured()) {
            throw new CalendlyNotConfiguredException();
        }

        $payload = [
            'start_time' => is_string($newDateTime) ? $newDateTime : $newDateTime->toIso8601String(),
        ];

        $endpoint = Str::startsWith($eventUriOrId, 'http') ? $eventUriOrId . '/reschedule' : $this->baseUrl . '/scheduled_events/' . $eventUriOrId . '/reschedule';

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->retry(2, 500)
            ->post($endpoint, $payload);

        if ($response->failed()) {
            $this->logHttpError('rescheduleEvent', $response);
            return null;
        }

        return $response->json();
    }

    public function getEvent(string $eventUriOrId): array|null
    {
        if (!$this->isConfigured()) {
            throw new CalendlyNotConfiguredException();
        }

        $endpoint = Str::startsWith($eventUriOrId, 'http') ? $eventUriOrId : $this->baseUrl . '/scheduled_events/' . $eventUriOrId;

        $response = Http::withToken($this->apiToken)
            ->acceptJson()
            ->retry(2, 500)
            ->get($endpoint);

        if ($response->failed()) {
            $this->logHttpError('getEvent', $response);
            return null;
        }

        return $response->json();
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            return false;
        }

        $computed = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);
        $header = preg_replace('/^sha256=/', '', $signature);

        return hash_equals($computed, $header);
    }

    protected function logHttpError(string $context, $response): void
    {
        try {
            Log::warning('Calendly API error', [
                'context' => $context,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            // swallow logging failures
        }
    }

    public function createScheduledEvent(array $data): array
    {
        return $this->createEvent($data);
    }

    public function cancelScheduledEvent(string $uri): bool
    {
        return $this->cancelEvent($uri);
    }
}
