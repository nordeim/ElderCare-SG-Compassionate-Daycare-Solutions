<?php

namespace Tests\Unit\Services;

use App\Jobs\SendBookingConfirmationJob;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Service as CenterServiceModel;
use App\Models\User;
use App\Services\Booking\BookingService;
use App\Services\Integration\CalendlyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Do not resolve BookingService here - resolve in each test after mock bindings
    }

    public function test_create_booking_happy_path_dispatches_job()
    {
        Queue::fake();

        // Make Calendly configured and return event data
        $this->mock(CalendlyService::class, function (MockInterface $m) {
            $m->shouldReceive('isConfigured')->andReturn(true);
            $m->shouldReceive('createEvent')->andReturn([
                'event_id' => 'evt_123',
                'event_uri' => 'https://calendly.com/events/evt_123',
                'cancel_url' => 'https://calendly.com/cancel/evt_123',
                'reschedule_url' => 'https://calendly.com/reschedule/evt_123',
            ]);
        });

        $user = User::factory()->create();
        $this->service = $this->app->make(BookingService::class);
        $center = Center::factory()->create();
        $svc = CenterServiceModel::factory()->create(['center_id' => $center->id]);

        $data = [
            'center_id' => $center->id,
            'service_id' => $svc->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '10:00:00',
            'booking_type' => 'visit',
        ];

        $booking = $this->service->create($user->id, $data);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals('pending', $booking->status);

        Queue::assertPushed(SendBookingConfirmationJob::class, function ($job) use ($booking) {
            return $job->bookingId === $booking->id;
        });
    }

    public function test_create_rejects_past_date()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $this->service = $this->app->make(BookingService::class);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->create($user->id, [
            'center_id' => $center->id,
            'booking_date' => now()->subDay()->toDateString(),
            'booking_time' => '09:00:00',
        ]);
    }

    public function test_create_rejects_duplicate_booking()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        // Ensure calendly won't try external calls during this test
        $this->mock(CalendlyService::class, function (MockInterface $m) {
            $m->shouldReceive('isConfigured')->andReturn(false);
        });

        $this->service = $this->app->make(BookingService::class);

        $data = [
            'center_id' => $center->id,
            'booking_date' => now()->addDays(1)->toDateString(),
            'booking_time' => '09:00:00',
        ];

        // Mock Calendly to be not configured so no external call
        $this->mock(CalendlyService::class, function (MockInterface $m) {
            $m->shouldReceive('isConfigured')->andReturn(false);
        });

        $this->service = $this->app->make(BookingService::class);

        // Create the first booking via the service
        $created = $this->service->create($user->id, $data);

        // Normalize booking_time to the stored format (Booking model may return DateTime)
        $data['booking_time'] = $created->booking_time instanceof \DateTimeInterface
            ? $created->booking_time->toTimeString()
            : $created->booking_time;

        // Replicate BookingService's existing booking lookup to ensure precondition
        $found = Booking::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->whereDate('booking_date', $data['booking_date'])
            ->where('booking_time', $data['booking_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        $this->assertNotNull($found, 'Pre-existing booking lookup (service query) did not find the created booking.');

    $this->expectException(\RuntimeException::class);

    // Attempt to create a duplicate booking via the service
    $this->service->create($user->id, $data);
    }

    public function test_update_reschedules_calendly_event_if_present()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'center_id' => $center->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'booking_time' => '11:00:00',
            'status' => 'pending',
            'calendly_event_uri' => 'https://calendly.com/events/evt_1',
        ]);

        $this->mock(CalendlyService::class, function (MockInterface $m) {
            $m->shouldReceive('isConfigured')->andReturn(true);
            $m->shouldReceive('rescheduleEvent')->once()->andReturnTrue();
        });

        $this->service = $this->app->make(BookingService::class);

        $newDate = now()->addDays(4)->toDateString();

        $updated = $this->service->update($booking->id, ['booking_date' => $newDate, 'booking_time' => '12:00:00']);

        $this->assertEquals($newDate, $updated->booking_date->toDateString());
    }

    public function test_cancel_calls_calendly_and_sets_cancelled()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'center_id' => $center->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'booking_time' => '11:00:00',
            'status' => 'confirmed',
            'calendly_event_uri' => 'https://calendly.com/events/evt_2',
        ]);

        $this->mock(CalendlyService::class, function (MockInterface $m) {
            $m->shouldReceive('cancelEvent')->once()->andReturnTrue();
        });

        $this->service = $this->app->make(BookingService::class);

        $cancelled = $this->service->cancel($booking->id, 'Client requested');

        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Client requested', $cancelled->cancellation_reason);
    }

    public function test_confirm_and_complete_transitions()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $b = Booking::factory()->create(['user_id' => $user->id, 'center_id' => $center->id, 'status' => 'pending']);
        $this->service = $this->app->make(BookingService::class);

        $confirmed = $this->service->confirm($b->id);
        $this->assertEquals('confirmed', $confirmed->status);

        $completed = $this->service->complete($b->id);
        $this->assertEquals('completed', $completed->status);
    }

    public function test_get_bookings_needing_reminders_and_mark()
    {
        $user = User::factory()->create();
        $center = Center::factory()->create();

        $target = now()->addDay();

        $b = Booking::factory()->create([
            'user_id' => $user->id,
            'center_id' => $center->id,
            'booking_date' => $target->toDateString(),
            'status' => 'pending',
            'reminder_sent_at' => null,
        ]);
        $this->service = $this->app->make(BookingService::class);

        $list = $this->service->getBookingsNeedingReminders();
        $this->assertTrue($list->contains('id', $b->id));

        $marked = $this->service->markReminderSent($b->id);
        $this->assertNotNull($marked->reminder_sent_at);
        $this->assertTrue((bool) $marked->sms_sent);
    }
}
