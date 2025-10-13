<?php

namespace App\Services\Notification;

use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use App\Notifications\BookingReminderNotification;
use App\Notifications\BookingCancellationNotification;
use App\Services\Integration\TwilioService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected TwilioService $twilioService
    ) {}

    public function sendBookingConfirmation(Booking $booking): void
    {
        try {
            $booking->user->notify(new BookingConfirmationNotification($booking));

            Log::info('Booking confirmation email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking confirmation email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($booking->user->phone) {
            $this->sendConfirmationSMS($booking);
        }

        $booking->update(['confirmation_sent_at' => now()]);
    }

    public function sendBookingReminder(Booking $booking): void
    {
        try {
            $booking->user->notify(new BookingReminderNotification($booking));

            Log::info('Booking reminder email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking reminder email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($booking->user->phone) {
            $this->sendReminderSMS($booking);
        }
    }

    public function sendCancellationNotification(Booking $booking): void
    {
        try {
            $booking->user->notify(new BookingCancellationNotification($booking));

            Log::info('Booking cancellation email sent', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking cancellation email failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        if ($booking->user->phone) {
            $this->sendCancellationSMS($booking);
        }
    }

    protected function sendConfirmationSMS(Booking $booking): void
    {
        $message = $this->getConfirmationSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    protected function sendReminderSMS(Booking $booking): void
    {
        $message = $this->getReminderSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    protected function sendCancellationSMS(Booking $booking): void
    {
        $message = $this->getCancellationSMSMessage($booking);

        $this->twilioService->sendSMS(
            to: $booking->user->phone,
            message: $message
        );
    }

    protected function getConfirmationSMSMessage(Booking $booking): string
    {
        $date = $booking->booking_date->format('d M Y');
        $time = Carbon::parse($booking->booking_time)->format('g:i A');

        return "ElderCare SG: Your booking #{$booking->booking_number} at {$booking->center->name} is confirmed for {$date} at {$time}. See you soon!";
    }

    protected function getReminderSMSMessage(Booking $booking): string
    {
        $date = $booking->booking_date->format('d M Y');
        $time = Carbon::parse($booking->booking_time)->format('g:i A');

        return "ElderCare SG: Reminder - Your visit to {$booking->center->name} is tomorrow ({$date}) at {$time}. Booking #{$booking->booking_number}";
    }

    protected function getCancellationSMSMessage(Booking $booking): string
    {
        return "ElderCare SG: Your booking #{$booking->booking_number} at {$booking->center->name} has been cancelled. Contact us if you have questions.";
    }
}
