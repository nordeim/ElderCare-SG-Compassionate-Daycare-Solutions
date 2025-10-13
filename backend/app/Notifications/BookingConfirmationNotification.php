<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Confirmation - ' . $this->booking->booking_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your booking has been confirmed!')
            ->line('**Booking Details:**')
            ->line('Booking Number: **' . $this->booking->booking_number . '**')
            ->line('Center: **' . $this->booking->center->name . '**')
            ->line('Date: **' . $date . '**')
            ->line('Time: **' . $time . '**')
            ->when($this->booking->service, function ($mail) {
                return $mail->line('Service: **' . $this->booking->service->name . '**');
            })
            ->line('')
            ->line('**Center Address:**')
            ->line($this->booking->center->address)
            ->line($this->booking->center->city . ' ' . $this->booking->center->postal_code)
            ->line('Phone: ' . $this->booking->center->phone)
            ->when($this->booking->calendly_reschedule_url, function ($mail) {
                return $mail->action('Reschedule Booking', $this->booking->calendly_reschedule_url);
            })
            ->when($this->booking->calendly_cancel_url, function ($mail) {
                return $mail->line('Need to cancel? [Click here](' . $this->booking->calendly_cancel_url . ')');
            })
            ->line('')
            ->line('We look forward to welcoming you!')
            ->line('')
            ->line('If you have any questions, please contact the center directly or reply to this email.')
            ->salutation('Best regards, The ElderCare SG Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'center_name' => $this->booking->center->name,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'booking_time' => $this->booking->booking_time->format('H:i'),
        ];
    }
}
