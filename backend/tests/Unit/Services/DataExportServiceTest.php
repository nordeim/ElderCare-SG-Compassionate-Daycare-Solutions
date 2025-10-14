<?php

namespace Tests\Unit\Services;

use App\Services\User\DataExportService;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DataExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(DataExportService::class);
    }

    public function test_exportUserData_writes_json_and_returns_url()
    {
        Storage::fake('private');

        // Create a user with related data via factories
        $user = User::factory()->hasProfile()->hasBookings(2)->hasTestimonials(1)->create();

        // Also create consents and audit logs using factories when available
        if (class_exists(\App\Models\Consent::class)) {
            \App\Models\Consent::factory()->for($user)->create();
        }

        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::factory()->for($user)->create();
        }

        $result = $this->service->exportUserData($user->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('expires_at', $result);

        Storage::disk('private')->assertExists($result['path']);

        // Read stored content and assert JSON structure
        $content = Storage::disk('private')->get($result['path']);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('bookings', $data);
        $this->assertArrayHasKey('testimonials', $data);

    // URL is generated via temporaryUrl; assert it contains a scheme (http/https)
    $this->assertStringContainsString('http', $result['url']);
    }
}
