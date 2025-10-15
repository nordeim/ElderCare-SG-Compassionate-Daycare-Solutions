<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_200()
    {
        // Ensure DB and Cache facades behave in the test environment
        \Illuminate\Support\Facades\DB::shouldReceive('connection->getPdo')->andReturnTrue();
        \Illuminate\Support\Facades\Cache::shouldReceive('put')->andReturnTrue();

        $resp = $this->get('/health');
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['ok', 'checks', 'timestamp']);
    }

    public function test_health_endpoint_shows_503_on_db_failure()
    {
        // simulate db failure
        \Illuminate\Support\Facades\DB::shouldReceive('connection->getPdo')->andThrow(new \Exception('boom'));

        $resp = $this->get('/health');
        $resp->assertStatus(503);
    }
}
