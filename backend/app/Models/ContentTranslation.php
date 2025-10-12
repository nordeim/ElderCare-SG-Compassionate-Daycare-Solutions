<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'value',
        'translation_status',
        'translated_by',
        'reviewed_by',
    ];

    /**
     * Get the parent translatable model (center, service, etc.).
     */
    public function translatable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that translated the content.
     */
    public function translator()
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    /**
     * Get the user that reviewed the content.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include translations for a specific locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope a query to only include published translations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('translation_status', 'published');
    }

    /**
     * Scope a query to only include translations for a specific field.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForField($query, $field)
    {
        return $query->where('field', $field);
    }

    /**
     * Mark the translation as translated.
     *
     * @param int $translatorId
     */
    public function markTranslated(int $translatorId)
    {
        $this->update([
            'translation_status' => 'translated',
            'translated_by' => $translatorId,
        ]);
    }

    /**
     * Mark the translation as reviewed.
     *
     * @param int $reviewerId
     */
    public function markReviewed(int $reviewerId)
    {
        $this->update([
            'translation_status' => 'reviewed',
            'reviewed_by' => $reviewerId,
        ]);
    }

    /**
     * Publish the translation.
     */
    public function publish()
    {
        $this->update(['translation_status' => 'published']);
    }
}