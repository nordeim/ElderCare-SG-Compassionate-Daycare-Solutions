<?php

namespace App\Console\Commands;

use App\Jobs\SendBookingReminderJob;
use App\Services\Booking\BookingService;
use Illuminate\Console\Command;

class SendBookingRemindersCommand extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Send reminders for bookings happening in 24 hours';

    public function handle(BookingService $bookingService): int
    {
        $this->info('Checking for bookings needing reminders...');

        $bookings = $bookingService->getBookingsNeedingReminders();

        if ($bookings->isEmpty()) {
            $this->info('No bookings need reminders at this time.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} booking(s) needing reminders.");

        foreach ($bookings as $booking) {
            $this->line("Queueing reminder for booking #{$booking->booking_number}");
            SendBookingReminderJob::dispatch($booking->id);
        }

        $this->info('All reminders queued successfully.');

        return self::SUCCESS;
    }
}
