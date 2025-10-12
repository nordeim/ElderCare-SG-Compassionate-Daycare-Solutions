<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'url',
        'thumbnail_url',
        'filename',
        'mime_type',
        'size',
        'duration',
        'caption',
        'alt_text',
        'cloudflare_stream_id',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'duration' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the parent mediable model (center, service, etc.).
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include images.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope a query to only include videos.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope a query to order media by display order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Get the file size in megabytes.
     *
     * @return string
     */
    public function getSizeInMbAttribute()
    {
        return round($this->size / 1024 / 1024, 2) . ' MB';
    }

    /**
     * Get the formatted duration for videos.
     *
     * @return string|null
     */
    public function getFormattedDurationAttribute()
    {
        if ($this->duration) {
            return gmdate('H:i:s', $this->duration);
        }
        return null;
    }
}