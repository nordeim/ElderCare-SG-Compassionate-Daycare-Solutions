<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancellationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->booking->booking_date->format('l, d M Y');
        $time = \Carbon\Carbon::parse($this->booking->booking_time)->format('g:i A');

        return (new MailMessage)
            ->subject('Booking Cancelled - ' . $this->booking->booking_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your booking has been cancelled as requested.')
            ->line('')
            ->line('**Cancelled Booking Details:**')
            ->line('Booking Number: **' . $this->booking->booking_number . '**')
            ->line('Center: **' . $this->booking->center->name . '**')
            ->line('Original Date: **' . $date . '**')
            ->line('Original Time: **' . $time . '**')
            ->when($this->booking->cancellation_reason, function ($mail) {
                return $mail->line('Reason: ' . $this->booking->cancellation_reason);
            })
            ->line('')
            ->line('We\'re sorry we won\'t be seeing you on this occasion.')
            ->action('Book Another Visit', url('/centers/' . $this->booking->center->slug))
            ->line('')
            ->line('If you cancelled by mistake or would like to reschedule, please visit our website or contact the center directly at **' . $this->booking->center->phone . '**.')
            ->line('')
            ->salutation('Thank you for your interest in our services. The ElderCare SG Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'center_name' => $this->booking->center->name,
            'status' => 'cancelled',
        ];
    }
}
