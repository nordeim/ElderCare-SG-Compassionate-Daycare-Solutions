<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals($user->id, $booking->user->id);
    }

    /** @test */
    public function it_belongs_to_center()
    {
        $center = Center::factory()->create();
        $booking = Booking::factory()->create(['center_id' => $center->id]);

        $this->assertInstanceOf(Center::class, $booking->center);
        $this->assertEquals($center->id, $booking->center->id);
    }

    /** @test */
    public function it_may_belong_to_service()
    {
        $service = Service::factory()->create();
        $booking = Booking::factory()->create(['service_id' => $service->id]);

        $this->assertInstanceOf(Service::class, $booking->service);

        $bookingNoService = Booking::factory()->create(['service_id' => null]);
        $this->assertNull($bookingNoService->service);
    }

    /** @test */
    public function it_casts_questionnaire_responses_to_array()
    {
        $responses = [
            'elderly_age' => 75,
            'medical_conditions' => ['diabetes', 'hypertension'],
            'mobility' => 'walker',
        ];

        $booking = Booking::factory()->create([
            'questionnaire_responses' => $responses,
        ]);

        $this->assertIsArray($booking->questionnaire_responses);
        $this->assertEquals(75, $booking->questionnaire_responses['elderly_age']);
        $this->assertContains('diabetes', $booking->questionnaire_responses['medical_conditions']);
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $booking = Booking::factory()->create([
            'booking_date' => '2025-02-15',
            'booking_time' => '14:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $booking->booking_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $booking->booking_time);
        $this->assertEquals('2025-02-15', $booking->booking_date->toDateString());
    }

    /** @test */
    public function it_generates_unique_booking_number()
    {
        $booking1 = Booking::factory()->create([
            'booking_number' => 'BK-20250115-0001',
        ]);
        $booking2 = Booking::factory()->create([
            'booking_number' => 'BK-20250115-0002',
        ]);

        $this->assertNotEquals($booking1->booking_number, $booking2->booking_number);
        $this->assertStringStartsWith('BK-', $booking1->booking_number);
    }

    /** @test */
    public function it_tracks_notification_timestamps()
    {
        $booking = Booking::factory()->create([
            'confirmation_sent_at' => null,
            'reminder_sent_at' => null,
        ]);

        $booking->update(['confirmation_sent_at' => now()]);
        $this->assertNotNull($booking->confirmation_sent_at);

        $booking->update(['reminder_sent_at' => now()]);
        $this->assertNotNull($booking->reminder_sent_at);
    }

    /** @test */
    public function it_has_status_enum()
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

        foreach ($statuses as $status) {
            $booking = Booking::factory()->create(['status' => $status]);
            $this->assertEquals($status, $booking->status);
        }
    }

    /** @test */
    public function it_soft_deletes()
    {
        $booking = Booking::factory()->create();
        
        $booking->delete();

        $this->assertSoftDeleted('bookings', ['id' => $booking->id]);
    }

    /** @test */
    public function it_stores_calendly_integration_data()
    {
        $booking = Booking::factory()->create([
            'calendly_event_id' => 'evt_123',
            'calendly_event_uri' => 'https://api.calendly.com/events/evt_123',
            'calendly_cancel_url' => 'https://calendly.com/cancellations/evt_123',
            'calendly_reschedule_url' => 'https://calendly.com/reschedulings/evt_123',
        ]);

        $this->assertEquals('evt_123', $booking->calendly_event_id);
        $this->assertNotNull($booking->calendly_cancel_url);
    }
}
