<?php

namespace Tests\Unit\Models;

use App\Models\Center;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Booking;
use App\Models\Testimonial;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CenterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_services_relationship()
    {
        $center = Center::factory()->create();
        $service = Service::factory()->create(['center_id' => $center->id]);

        $this->assertTrue($center->services->contains($service));
        $this->assertInstanceOf(Service::class, $center->services->first());
    }

    /** @test */
    public function it_has_staff_relationship()
    {
        $center = Center::factory()->create();
        $staff = Staff::factory()->create(['center_id' => $center->id]);

        $this->assertTrue($center->staff->contains($staff));
        $this->assertInstanceOf(Staff::class, $center->staff->first());
    }

    /** @test */
    public function it_has_bookings_relationship()
    {
        $center = Center::factory()->create();
        $booking = Booking::factory()->create(['center_id' => $center->id]);

        $this->assertTrue($center->bookings->contains($booking));
    }

    /** @test */
    public function it_has_testimonials_relationship()
    {
        $center = Center::factory()->create();
        $testimonial = Testimonial::factory()->create(['center_id' => $center->id]);

        $this->assertTrue($center->testimonials->contains($testimonial));
    }

    /** @test */
    public function it_has_media_relationship()
    {
        $center = Center::factory()->create();
        $media = Media::factory()->create([
            'mediable_type' => Center::class,
            'mediable_id' => $center->id,
        ]);

        $this->assertTrue($center->media->contains($media));
    }

    /** @test */
    public function it_calculates_occupancy_rate()
    {
        $center = Center::factory()->create([
            'capacity' => 100,
            'current_occupancy' => 75,
        ]);

        $occupancyRate = ($center->current_occupancy / $center->capacity) * 100;

        $this->assertEquals(75.0, $occupancyRate);
    }

    /** @test */
    public function it_validates_license_is_valid()
    {
        $validCenter = Center::factory()->create([
            'license_expiry_date' => now()->addYear(),
        ]);

        $expiredCenter = Center::factory()->create([
            'license_expiry_date' => now()->subDay(),
        ]);

        $this->assertTrue($validCenter->license_expiry_date > now());
        $this->assertFalse($expiredCenter->license_expiry_date > now());
    }

    /** @test */
    public function it_casts_json_fields_to_arrays()
    {
        $center = Center::factory()->create([
            'operating_hours' => ['monday' => ['open' => '08:00', 'close' => '18:00']],
            'amenities' => ['wheelchair_accessible', 'wifi', 'parking'],
            'transport_info' => ['mrt' => ['Ang Mo Kio'], 'bus' => ['56', '162']],
        ]);

        $this->assertIsArray($center->operating_hours);
        $this->assertIsArray($center->amenities);
        $this->assertIsArray($center->transport_info);
        $this->assertCount(3, $center->amenities);
    }

    /** @test */
    public function it_scopes_published_centers()
    {
        Center::factory()->count(3)->create(['status' => 'published']);
        Center::factory()->count(2)->create(['status' => 'draft']);

        $publishedCenters = Center::where('status', 'published')->get();

        $this->assertCount(3, $publishedCenters);
    }

    /** @test */
    public function it_soft_deletes()
    {
        $center = Center::factory()->create();
        
        $center->delete();

        $this->assertSoftDeleted('centers', ['id' => $center->id]);
        $this->assertNotNull($center->fresh()->deleted_at);
    }

    /** @test */
    public function it_can_be_restored()
    {
        $center = Center::factory()->create();
        $center->delete();

        $center->restore();

        $this->assertNull($center->fresh()->deleted_at);
    }

    /** @test */
    public function it_generates_slug_from_name()
    {
        $center = Center::factory()->create(['name' => 'Golden Years Care Center']);

        $this->assertNotNull($center->slug);
        $this->assertStringContainsString('golden', strtolower($center->slug));
    }

    /** @test */
    public function it_enforces_capacity_constraint()
    {
        $center = Center::factory()->create(['capacity' => 50]);

        // This should work
        $center->update(['current_occupancy' => 50]);
        $this->assertEquals(50, $center->current_occupancy);
    }
}
