<?php

namespace Tests\Unit;

use App\Services\Health\HealthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthServiceTest extends TestCase
{
    public function test_check_returns_structure()
    {
        $service = new HealthService();

        $result = $service->check(['detailed' => false]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ok', $result);
        $this->assertArrayHasKey('checks', $result);
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function test_db_failure_marked_unhealthy()
    {
        // Simulate DB failure by mocking DB facade
        DB::shouldReceive('connection->getPdo')->andThrow(new \Exception('simulated db fail'));

        $service = new HealthService();
        $result = $service->check(['detailed' => false]);

        $this->assertFalse($result['ok']);
        $this->assertArrayHasKey('db', $result['checks']);
        $this->assertFalse($result['checks']['db']['ok']);
    }
}
