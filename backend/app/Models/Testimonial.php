<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'center_id',
        'title',
        'content',
        'rating',
        'status',
        'moderation_notes',
        'moderated_by',
        'moderated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    /**
     * Get the user that wrote the testimonial.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the center for the testimonial.
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * Get the user that moderated the testimonial.
     */
    public function moderatedBy()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope a query to only include approved testimonials.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending testimonials.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include testimonials for a specific center.
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
     * Approve the testimonial.
     *
     * @param int $moderatorId
     */
    public function approve(int $moderatorId)
    {
        $this->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);
    }

    /**
     * Reject the testimonial.
     *
     * @param int $moderatorId
     * @param string|null $reason
     */
    public function reject(int $moderatorId, ?string $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => $reason,
        ]);
    }

    /**
     * Mark the testimonial as spam.
     *
     * @param int $moderatorId
     */
    public function markAsSpam(int $moderatorId)
    {
        $this->update([
            'status' => 'spam',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);
    }
}