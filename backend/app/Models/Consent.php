<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_given',
        'consent_text',
        'consent_version',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consent_given' => 'boolean',
    ];

    /**
     * Get the user that owns the consent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include consents of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope a query to only include active consents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('consent_given', true);
    }

    /**
     * Scope a query to only include withdrawn consents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('consent_given', false);
    }

    /**
     * Check if the consent is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->consent_given;
    }

    /**
     * Withdraw the consent.
     *
     * @return bool
     */
    public function withdraw()
    {
        return $this->update(['consent_given' => false]);
    }
}