<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Reminder: Your Visit Tomorrow - ' . $this->booking->booking_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a friendly reminder about your upcoming visit tomorrow.')
            ->line('')
            ->line('**Booking Details:**')
            ->line('Booking Number: **' . $this->booking->booking_number . '**')
            ->line('Center: **' . $this->booking->center->name . '**')
            ->line('Date: **' . $date . '**')
            ->line('Time: **' . $time . '**')
            ->line('')
            ->line('**Getting There:**')
            ->line($this->booking->center->address)
            ->line($this->booking->center->city . ' ' . $this->booking->center->postal_code)
            ->when($this->booking->center->transport_info, function ($mail) {
                $transport = $this->booking->center->transport_info;
                if (isset($transport['mrt']) && !empty($transport['mrt'])) {
                    return $mail->line('Nearest MRT: ' . implode(', ', $transport['mrt']));
                }
                return $mail;
            })
            ->line('')
            ->line('**What to Bring:**')
            ->line('• Identification (NRIC or Passport)')
            ->line('• Medical records (if applicable)')
            ->line('• List of current medications')
            ->line('')
            ->line('Please arrive 10 minutes early for registration.')
            ->line('Contact the center at **' . $this->booking->center->phone . '** if you need directions or have questions.')
            ->when($this->booking->calendly_reschedule_url, function ($mail) {
                return $mail->line('Need to reschedule? [Click here](' . $this->booking->calendly_reschedule_url . ')');
            })
            ->line('')
            ->salutation('We look forward to seeing you tomorrow! The ElderCare SG Team');
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
