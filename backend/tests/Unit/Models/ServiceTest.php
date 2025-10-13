<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_center()
    {
        $center = Center::factory()->create();
        $service = Service::factory()->create(['center_id' => $center->id]);

        $this->assertInstanceOf(Center::class, $service->center);
    }

    /** @test */
    public function it_casts_features_to_array()
    {
        $service = Service::factory()->create(['features' => ['wifi', 'projector']]);

        $this->assertIsArray($service->features);
        $this->assertContains('wifi', $service->features);
    }

    /** @test */
    public function it_supports_soft_deletes()
    {
        $service = Service::factory()->create();
        $service->delete();

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }
}
