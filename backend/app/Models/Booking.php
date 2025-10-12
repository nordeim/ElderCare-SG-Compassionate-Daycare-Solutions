<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_number',
        'user_id',
        'center_id',
        'service_id',
        'booking_date',
        'booking_time',
        'booking_type',
        'calendly_event_id',
        'calendly_event_uri',
        'calendly_cancel_url',
        'calendly_reschedule_url',
        'questionnaire_responses',
        'status',
        'cancellation_reason',
        'notes',
        'confirmation_sent_at',
        'reminder_sent_at',
        'sms_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime',
        'questionnaire_responses' => 'array',
        'sms_sent' => 'boolean',
        'confirmation_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Get the user that made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the center for the booking.
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * Get the service for the booking.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope a query to only include upcoming bookings.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString());
    }

    /**
     * Scope a query to only include bookings with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include bookings for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include bookings for a specific center.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $centerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    /**
     * Confirm the booking.
     */
    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
    }

    /**
     * Cancel the booking.
     *
     * @param  string  $reason
     */
    public function cancel($reason)
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Mark the booking as completed.
     */
    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }
}