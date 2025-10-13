<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = fake()->dateTimeBetween('now', '+3 months');

        return [
            'booking_number' => $this->generateBookingNumber(),
            'user_id' => User::factory(),
            'center_id' => Center::factory(),
            'service_id' => fake()->boolean(70) ? Service::factory() : null,
            'booking_date' => $bookingDate->format('Y-m-d'),
            'booking_time' => fake()->randomElement(['09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00']),
            'booking_type' => fake()->randomElement(['visit', 'consultation', 'trial_day']),
            'questionnaire_responses' => $this->generateQuestionnaire(),
            'status' => 'pending',
            'calendly_event_id' => null,
            'calendly_event_uri' => null,
            'calendly_cancel_url' => null,
            'calendly_reschedule_url' => null,
            'cancellation_reason' => null,
            'notes' => fake()->boolean(20) ? fake()->sentence() : null,
            'confirmation_sent_at' => null,
            'reminder_sent_at' => null,
            'sms_sent' => false,
        ];
    }

    /**
     * Indicate a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmation_sent_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }

    /**
     * Indicate a completed booking.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_date' => fake()->dateTimeBetween('-3 months', '-1 day')->format('Y-m-d'),
            'status' => 'completed',
            'confirmation_sent_at' => now()->subDays(fake()->numberBetween(7, 90)),
            'reminder_sent_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ]);
    }

    /**
     * Indicate a cancelled booking.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => fake()->randomElement([
                'Schedule conflict - need to reschedule',
                'Found alternative care arrangement',
                'Family member available to provide care',
                'Medical appointment conflict',
            ]),
        ]);
    }

    /**
     * Indicate booking with Calendly integration.
     */
    public function withCalendly(): static
    {
        return $this->state(function (array $attributes) {
            $eventId = 'evt_' . fake()->uuid();
            
            return [
                'calendly_event_id' => $eventId,
                'calendly_event_uri' => "https://api.calendly.com/scheduled_events/{$eventId}",
                'calendly_cancel_url' => "https://calendly.com/cancellations/{$eventId}",
                'calendly_reschedule_url' => "https://calendly.com/reschedulings/{$eventId}",
            ];
        });
    }

    /**
     * Generate unique booking number.
     */
    protected function generateBookingNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "BK-{$date}-{$sequence}";
    }

    /**
     * Generate realistic questionnaire responses.
     */
    protected function generateQuestionnaire(): array
    {
        return [
            'elderly_age' => fake()->numberBetween(65, 95),
            'medical_conditions' => fake()->randomElements([
                'diabetes',
                'hypertension',
                'heart_disease',
                'dementia',
                'arthritis',
                'osteoporosis',
                'stroke_history',
            ], fake()->numberBetween(0, 3)),
            'mobility' => fake()->randomElement(['independent', 'walker', 'wheelchair', 'bedridden']),
            'special_requirements' => fake()->boolean(60) ? fake()->sentence(10) : null,
            'dietary_restrictions' => fake()->boolean(40) ? fake()->randomElements([
                'halal', 'vegetarian', 'diabetic', 'low_sodium', 'texture_modified'
            ], fake()->numberBetween(1, 2)) : [],
        ];
    }
}
