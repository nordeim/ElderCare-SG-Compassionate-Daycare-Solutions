<?php

namespace Tests\Unit\Services;

use App\Services\Integration\CalendlyService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class CalendlyServiceTest extends \Tests\TestCase
{
    public function test_is_configured_false_when_no_env()
    {
        config(['services.calendly.api_token' => null]);
        $svc = new CalendlyService();

        $this->assertFalse($svc->isConfigured());
    }

    public function test_verify_webhook_signature_with_secret()
    {
        config(['services.calendly.webhook_secret' => 'secret-123']);
        $svc = new CalendlyService();

        $payload = ['foo' => 'bar'];
        $sig = 'sha256=' . hash_hmac('sha256', json_encode($payload), 'secret-123');

        $this->assertTrue($svc->verifyWebhookSignature($payload, $sig));
    }

    public function test_create_event_uses_http_and_returns_normalized_shape()
    {
        config(['services.calendly.api_token' => 'abc']);

        Http::fake([
            'https://api.calendly.com/scheduled_events' => Http::response([
                'data' => [
                    'id' => 'evt_123',
                    'uri' => 'https://api.calendly.com/scheduled_events/evt_123',
                    'cancel' => ['uri' => 'https://cancel'],
                    'reschedule' => ['uri' => 'https://reschedule'],
                ],
            ], 201),
        ]);

        $svc = new CalendlyService();

        $resp = $svc->createEvent([
            'user_name' => 'Alice',
            'user_email' => 'alice@example.com',
            'booking_date' => now()->toIso8601String(),
        ]);

        $this->assertArrayHasKey('event_id', $resp);
        $this->assertEquals('evt_123', $resp['event_id']);
        $this->assertArrayHasKey('raw', $resp);
    }
}
