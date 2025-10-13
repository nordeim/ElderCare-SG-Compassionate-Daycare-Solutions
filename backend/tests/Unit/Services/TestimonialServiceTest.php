<?php

namespace Tests\Unit\Services;

use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialService;
use App\Models\User;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TestimonialService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(TestimonialService::class);
    }

    /** @test */
    public function it_can_submit_a_testimonial_and_prevent_duplicates()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'center_id' => $center->id,
            'rating' => 5,
            'comment' => 'Great service',
        ];

        $t1 = $this->service->submit($payload);
        $this->assertInstanceOf(Testimonial::class, $t1);

        // Attempt duplicate
        $t2 = $this->service->submit($payload);
        $this->assertNull($t2, 'Duplicate testimonial should be prevented');
    }

    /** @test */
    public function it_can_approve_and_reject_testimonials()
    {
        $testimonial = Testimonial::factory()->create(['status' => 'pending']);

        $approved = $this->service->approve($testimonial->id);
        $this->assertTrue($approved);
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'status' => 'approved']);

        $rejected = $this->service->reject($testimonial->id, 'Inappropriate');
        $this->assertTrue($rejected);
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'status' => 'rejected']);
    }

    /** @test */
    public function it_calculates_average_rating_and_distribution()
    {
        $center = Center::factory()->create();

        Testimonial::factory()->create(['center_id' => $center->id, 'rating' => 5, 'status' => 'approved']);
        Testimonial::factory()->create(['center_id' => $center->id, 'rating' => 4, 'status' => 'approved']);
        Testimonial::factory()->create(['center_id' => $center->id, 'rating' => 3, 'status' => 'approved']);

        $avg = $this->service->calculateAverageRating($center->id);
        $this->assertEqualsWithDelta(4.0, $avg, 0.01);

        $dist = $this->service->getRatingDistribution($center->id);
        $this->assertArrayHasKey(5, $dist);
        $this->assertEquals(1, $dist[5]);
    }
}
