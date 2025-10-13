<?php

namespace App\Services\Translation;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Collection;

class TranslationService
{
    public function createTranslation(
        string $translatableType,
        int $translatableId,
        string $locale,
        string $field,
        string $value,
        ?int $translatorId = null
    ): ContentTranslation {
        return ContentTranslation::create([
            'translatable_type' => $translatableType,
            'translatable_id' => $translatableId,
            'locale' => $locale,
            'field' => $field,
            'value' => $value,
            'translation_status' => 'draft',
            'translated_by' => $translatorId,
        ]);
    }

    public function updateTranslation(int $translationId, string $value): ContentTranslation
    {
        $translation = ContentTranslation::findOrFail($translationId);

        $translation->update([
            'value' => $value,
            'updated_at' => now(),
        ]);

        return $translation->fresh();
    }

    public function markTranslated(int $translationId, int $translatorId): ContentTranslation
    {
        $translation = ContentTranslation::findOrFail($translationId);

        if ($translation->translation_status !== 'draft') {
            throw new \RuntimeException(
                'Can only mark draft translations as translated'
            );
        }

        $translation->update([
            'translation_status' => 'translated',
            'translated_by' => $translatorId,
        ]);

        return $translation->fresh();
    }

    public function markReviewed(int $translationId, int $reviewerId): ContentTranslation
    {
        $translation = ContentTranslation::findOrFail($translationId);

        if ($translation->translation_status !== 'translated') {
            throw new \RuntimeException(
                'Can only review translations that have been translated'
            );
        }

        $translation->update([
            'translation_status' => 'reviewed',
            'reviewed_by' => $reviewerId,
        ]);

        return $translation->fresh();
    }

    public function publish(int $translationId): ContentTranslation
    {
        $translation = ContentTranslation::findOrFail($translationId);

        if (!in_array($translation->translation_status, ['translated', 'reviewed'])) {
            throw new \RuntimeException(
                'Can only publish reviewed or translated content'
            );
        }

        $translation->update([
            'translation_status' => 'published',
        ]);

        return $translation->fresh();
    }

    public function getTranslations(string $translatableType, int $translatableId, ?string $locale = null): Collection
    {
        $query = ContentTranslation::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId);

        if ($locale) {
            $query->where('locale', $locale);
        }

        return $query->get();
    }

    public function getPendingTranslations(string $locale, ?string $status = 'draft'): Collection
    {
        return ContentTranslation::with(['translator', 'reviewer'])
            ->where('locale', $locale)
            ->where('translation_status', $status)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getTranslation(string $translatableType, int $translatableId, string $locale, string $field): ?ContentTranslation
    {
        return ContentTranslation::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();
    }

    public function delete(int $translationId): bool
    {
        $translation = ContentTranslation::findOrFail($translationId);
        return $translation->delete();
    }

    public function getTranslationCoverage(string $translatableType, int $translatableId): array
    {
        $locales = ['en', 'zh', 'ms', 'ta'];
        $fields = $this->getTranslatableFields($translatableType);

        $coverage = [];

        foreach ($locales as $locale) {
            $translations = $this->getTranslations($translatableType, $translatableId, $locale);
            $translatedFields = $translations->pluck('field')->toArray();

            $coverage[$locale] = [
                'total_fields' => count($fields),
                'translated_fields' => count($translatedFields),
                'percentage' => count($fields) > 0 
                    ? round((count($translatedFields) / count($fields)) * 100, 2)
                    : 0,
            ];
        }

        return $coverage;
    }

    protected function getTranslatableFields(string $translatableType): array
    {
        $fields = [
            'App\\Models\\Center' => ['name', 'short_description', 'description'],
            'App\\Models\\Service' => ['name', 'description'],
            'App\\Models\\FAQ' => ['question', 'answer'],
        ];

        return $fields[$translatableType] ?? [];
    }
}
