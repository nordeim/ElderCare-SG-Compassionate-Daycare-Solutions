<?php

namespace Tests\Feature\Booking;

use App\Jobs\SendBookingConfirmationJob;
use App\Models\Center;
use App\Models\Service as CenterServiceModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingHappyPathTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_happy_path_creates_booking_and_dispatches_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $center = Center::factory()->create();
        $svc = CenterServiceModel::factory()->create(['center_id' => $center->id]);

        // Mock CalendlyService in container so BookingService receives it
        $this->mock(\App\Services\Integration\CalendlyService::class, function ($m) {
            $m->shouldReceive('isConfigured')->andReturn(true);
            $m->shouldReceive('createEvent')->andReturn([
                'event_id' => 'evt_999',
                'event_uri' => 'https://calendly.com/events/evt_999',
                'cancel_url' => 'https://calendly.com/cancel/evt_999',
                'reschedule_url' => 'https://calendly.com/reschedule/evt_999',
            ]);
        });

        $payload = [
            'center_id' => $center->id,
            'service_id' => $svc->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'booking_time' => '10:00:00',
        ];

        $resp = $this->postJson('/api/v1/bookings', $payload);

        $resp->assertStatus(201);
        $resp->assertJsonPath('booking_number', fn($v) => is_string($v));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'center_id' => $center->id,
        ]);

        Queue::assertPushed(SendBookingConfirmationJob::class);
    }
}
