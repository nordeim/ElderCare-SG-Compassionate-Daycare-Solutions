<?php

namespace Tests\Integration\Services;

use App\Services\Integration\CalendlyService;

class CalendlyServiceIntegrationTest extends \Tests\TestCase
{
    /**
     * @group integration
     */
    public function test_live_api_calls_are_skipped_if_no_token()
    {
        if (empty(config('services.calendly.api_token'))) {
            $this->markTestSkipped('No CALENDLY_API_TOKEN configured for integration test');
        }

        $svc = new CalendlyService();

        $this->assertTrue($svc->isConfigured());

        // Basic live connectivity check: get a non-existent event should not throw but may return null/404 wrapper
        $res = $svc->getEvent('non-existent-id-please-ignore');

        $this->assertTrue(is_array($res) || is_null($res));
    }
}
