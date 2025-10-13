<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'booking_number' => $this->booking_number,
            'status' => $this->status,
            'booking_type' => $this->booking_type,
            'booking_date' => $this->booking_date->toDateString(),
            'booking_time' => $this->booking_time->format('H:i'),
            'booking_datetime_display' => $this->booking_date->format('d M Y') . ' at ' . 
                                          \Carbon\Carbon::parse($this->booking_time)->format('g:i A'),
            'center' => [
                'id' => $this->center->id,
                'name' => $this->center->name,
                'slug' => $this->center->slug,
                'address' => $this->center->address,
                'city' => $this->center->city,
                'phone' => $this->center->phone,
            ],
            'service' => $this->when($this->service, function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'price' => $this->service->price,
                    'price_display' => $this->service->price 
                        ? '$' . number_format($this->service->price, 2)
                        : 'POA',
                ];
            }),
            'questionnaire_responses' => $this->when(
                $request->user()?->id === $this->user_id || 
                in_array($request->user()?->role, ['admin', 'super_admin']),
                $this->questionnaire_responses
            ),
            'calendly_cancel_url' => $this->when(
                $request->user()?->id === $this->user_id && $this->calendly_cancel_url,
                $this->calendly_cancel_url
            ),
            'calendly_reschedule_url' => $this->when(
                $request->user()?->id === $this->user_id && $this->calendly_reschedule_url,
                $this->calendly_reschedule_url
            ),
            'cancellation_reason' => $this->when(
                $this->status === 'cancelled',
                $this->cancellation_reason
            ),
            'confirmation_sent' => !is_null($this->confirmation_sent_at),
            'reminder_sent' => !is_null($this->reminder_sent_at),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'user' => $this->when(
                in_array($request->user()?->role, ['admin', 'super_admin']),
                function () {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone,
                    ];
                }
            ),
            'notes' => $this->when(
                in_array($request->user()?->role, ['admin', 'super_admin']),
                $this->notes
            ),
        ];
    }
}
