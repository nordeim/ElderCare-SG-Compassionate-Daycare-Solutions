<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Center extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'moh_license_number',
        'license_expiry_date',
        'accreditation_status',
        'capacity',
        'current_occupancy',
        'staff_count',
        'staff_patient_ratio',
        'operating_hours',
        'medical_facilities',
        'amenities',
        'transport_info',
        'languages_supported',
        'government_subsidies',
        'latitude',
        'longitude',
        'status',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'license_expiry_date' => 'date',
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'staff_count' => 'integer',
        'staff_patient_ratio' => 'decimal:1',
        'operating_hours' => 'array',
        'medical_facilities' => 'array',
        'amenities' => 'array',
        'transport_info' => 'array',
        'languages_supported' => 'array',
        'government_subsidies' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the services for the center.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the staff for the center.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the bookings for the center.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the testimonials for the center.
     */
    public function testimonials()
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Get the media for the center.
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the translations for the center.
     */
    public function translations()
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    /**
     * Get the occupancy rate.
     *
     * @return float
     */
    public function getOccupancyRateAttribute()
    {
        if ($this->capacity > 0) {
            return round(($this->current_occupancy / $this->capacity) * 100, 2);
        }
        return 0;
    }

    /**
     * Get the average rating.
     *
     * @return float
     */
    public function getAverageRatingAttribute()
    {
        return round($this->testimonials()->where('status', 'approved')->avg('rating'), 2);
    }

    /**
     * Check if the license is valid.
     *
     * @return bool
     */
    public function isLicenseValid()
    {
        return $this->license_expiry_date->isFuture();
    }

    /**
     * Scope a query to only include published centers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include centers with a valid license.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidLicense($query)
    {
        return $query->where('license_expiry_date', '>', now());
    }

    /**
     * Scope a query to only include centers in a specific city.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $city
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }
}