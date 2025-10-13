<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\Notification\NotificationService;
use App\Services\Booking\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;
    public $tries = 2;

    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(
        NotificationService $notificationService,
        BookingService $bookingService
    ): void {
        $booking = Booking::with(['user', 'center', 'service'])->find($this->bookingId);

        if (!$booking) {
            Log::error('Booking not found for reminder', ['booking_id' => $this->bookingId]);
            return;
        }

        if ($booking->reminder_sent_at) {
            Log::info('Reminder already sent', ['booking_id' => $this->bookingId]);
            return;
        }

        Log::info('Sending booking reminder', [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
        ]);

        try {
            $notificationService->sendBookingReminder($booking);

            $bookingService->markReminderSent($booking->id);

        } catch (\Exception $e) {
            Log::error('Booking reminder job failed', [
                'booking_id' => $this->bookingId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Booking reminder job failed permanently', [
            'booking_id' => $this->bookingId,
            'error' => $exception->getMessage(),
        ]);
    }
}
