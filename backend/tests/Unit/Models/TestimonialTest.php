<?php

namespace Tests\Unit\Models;

use App\Models\Testimonial;
use App\Models\User;
use App\Models\Center;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $testimonial->user);
        $this->assertEquals($user->id, $testimonial->user->id);
    }

    /** @test */
    public function it_belongs_to_center()
    {
        $center = Center::factory()->create();
        $testimonial = Testimonial::factory()->create(['center_id' => $center->id]);

        $this->assertInstanceOf(Center::class, $testimonial->center);
        $this->assertEquals($center->id, $testimonial->center->id);
    }

    /** @test */
    public function it_may_belong_to_moderator()
    {
        $moderator = User::factory()->create(['role' => 'admin']);
        $testimonial = Testimonial::factory()->create([
            'moderated_by' => $moderator->id,
            'status' => 'approved',
        ]);

        $this->assertInstanceOf(User::class, $testimonial->moderatedBy);
        $this->assertEquals($moderator->id, $testimonial->moderated_by);
    }

    /** @test */
    public function it_has_rating_between_1_and_5()
    {
        $testimonial = Testimonial::factory()->create(['rating' => 5]);
        $this->assertEquals(5, $testimonial->rating);
        $this->assertGreaterThanOrEqual(1, $testimonial->rating);
        $this->assertLessThanOrEqual(5, $testimonial->rating);
    }

    /** @test */
    public function it_has_moderation_status()
    {
        $statuses = ['pending', 'approved', 'rejected', 'spam'];

        foreach ($statuses as $status) {
            $testimonial = Testimonial::factory()->create(['status' => $status]);
            $this->assertEquals($status, $testimonial->status);
        }
    }

    /** @test */
    public function it_records_moderation_timestamp()
    {
        $testimonial = Testimonial::factory()->create([
            'status' => 'approved',
            'moderated_at' => now(),
        ]);

        $this->assertNotNull($testimonial->moderated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $testimonial->moderated_at);
    }

    /** @test */
    public function it_soft_deletes()
    {
        $testimonial = Testimonial::factory()->create();
        
        $testimonial->delete();

        $this->assertSoftDeleted('testimonials', ['id' => $testimonial->id]);
    }

    /** @test */
    public function it_stores_moderation_notes()
    {
        $testimonial = Testimonial::factory()->create([
            'status' => 'rejected',
            'moderation_notes' => 'Content violates community guidelines',
        ]);

        $this->assertEquals('Content violates community guidelines', $testimonial->moderation_notes);
    }
}
