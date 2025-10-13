<?php

namespace App\Services\Integration;

use App\Models\Center;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalendlyService
{
    protected ?string $apiToken;
    protected ?string $organizationUri;
    protected string $baseUrl = 'https://api.calendly.com';

    public function __construct()
    {
        $this->apiToken = config('services.calendly.api_token');
        $this->organizationUri = config('services.calendly.organization_uri');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->organizationUri);
    }

    public function createEvent(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Calendly is not configured');
        }

        $center = $data['center'];
        $service = $data['service'] ?? null;
        $bookingDate = $data['booking_date'];
        $userName = $data['user_name'];
        $userEmail = $data['user_email'];

        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/scheduled_events", [
                    'event' => [
                        'name' => $service ? $service->name : 'Center Visit',
                        'location' => [
                            'kind' => 'physical',
                            'location' => $center->address . ', ' . $center->city,
                        ],
                        'start_time' => $bookingDate->toIso8601String(),
                        'duration' => 60,
                        'invitees' => [
                            [
                                'name' => $userName,
                                'email' => $userEmail,
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $eventData = $response->json();

                return [
                    'event_id' => $eventData['resource']['id'] ?? null,
                    'event_uri' => $eventData['resource']['uri'] ?? null,
                    'cancel_url' => $eventData['resource']['cancel_url'] ?? null,
                    'reschedule_url' => $eventData['resource']['reschedule_url'] ?? null,
                ];
            }

            Log::error('Calendly event creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create Calendly event: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Calendly API exception', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function cancelEvent(string $eventUri): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Calendly not configured, skipping cancellation');
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->delete("{$this->baseUrl}{$eventUri}");

            if ($response->successful()) {
                return true;
            }

            Log::error('Calendly event cancellation failed', [
                'status' => $response->status(),
                'uri' => $eventUri,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Calendly cancel exception', [
                'error' => $e->getMessage(),
                'uri' => $eventUri,
            ]);

            return false;
        }
    }

    public function rescheduleEvent(string $eventUri, Carbon $newDateTime): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Calendly not configured, skipping reschedule');
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->patch("{$this->baseUrl}{$eventUri}", [
                    'start_time' => $newDateTime->toIso8601String(),
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Calendly event reschedule failed', [
                'status' => $response->status(),
                'uri' => $eventUri,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Calendly reschedule exception', [
                'error' => $e->getMessage(),
                'uri' => $eventUri,
            ]);

            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.calendly.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Calendly webhook secret not configured');
            return true;
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($calculatedSignature, $signature);
    }
}
