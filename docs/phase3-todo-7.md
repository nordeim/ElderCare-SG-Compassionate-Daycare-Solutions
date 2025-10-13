# Day 5: Advanced Features — Complete Implementation (18 Files)

**Branch**: `feature/phase3-advanced-features`

**Objective**: Implement testimonial moderation, media management with S3 upload and image optimization, translation workflow, and admin-specific endpoints.

---

## Table of Contents

1. [Services (4 files)](#services-4-files)
2. [Controllers (6 files)](#controllers-6-files)
3. [Request Validators (3 files)](#request-validators-3-files)
4. [API Resources (3 files)](#api-resources-3-files)
5. [Queue Jobs (1 file)](#queue-jobs-1-file)
6. [Policies (1 file)](#policies-1-file)
7. [Configuration & Validation](#configuration--validation)

---

## Services (4 files)

### 1. `backend/app/Services/Testimonial/TestimonialService.php`

```php
<?php

namespace App\Services\Testimonial;

use App\Models\Testimonial;
use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TestimonialService
{
    /**
     * Get approved testimonials for a center
     *
     * @param int $centerId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getApprovedForCenter(int $centerId, array $filters = []): LengthAwarePaginator
    {
        $query = Testimonial::with('user')
            ->where('center_id', $centerId)
            ->where('status', 'approved');

        // Filter by rating
        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Get all pending testimonials (admin)
     *
     * @return Collection
     */
    public function getPendingTestimonials(): Collection
    {
        return Testimonial::with(['user', 'center'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Submit testimonial (user)
     *
     * @param int $userId
     * @param int $centerId
     * @param array $data
     * @return Testimonial
     * @throws \RuntimeException
     */
    public function submit(int $userId, int $centerId, array $data): Testimonial
    {
        // Check if center exists
        $center = Center::findOrFail($centerId);

        // Check if user has already submitted testimonial for this center
        $existing = Testimonial::where('user_id', $userId)
            ->where('center_id', $centerId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            throw new \RuntimeException(
                'You have already submitted a testimonial for this center'
            );
        }

        // Optionally: Check if user has a completed booking at this center
        // (Ensures only genuine customers can review)
        // $hasBooking = Booking::where('user_id', $userId)
        //     ->where('center_id', $centerId)
        //     ->where('status', 'completed')
        //     ->exists();
        //
        // if (!$hasBooking) {
        //     throw new \RuntimeException(
        //         'You must complete a visit before submitting a testimonial'
        //     );
        // }

        return Testimonial::create([
            'user_id' => $userId,
            'center_id' => $centerId,
            'title' => $data['title'],
            'content' => $data['content'],
            'rating' => $data['rating'],
            'status' => 'pending', // Requires moderation
        ]);
    }

    /**
     * Approve testimonial (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @return Testimonial
     */
    public function approve(int $testimonialId, int $moderatorId): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        if ($testimonial->status !== 'pending') {
            throw new \RuntimeException(
                'Can only approve pending testimonials'
            );
        }

        $testimonial->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);

        \Log::info('Testimonial approved', [
            'testimonial_id' => $testimonialId,
            'moderator_id' => $moderatorId,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Reject testimonial (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @param string $reason
     * @return Testimonial
     */
    public function reject(int $testimonialId, int $moderatorId, string $reason): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        if ($testimonial->status !== 'pending') {
            throw new \RuntimeException(
                'Can only reject pending testimonials'
            );
        }

        $testimonial->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => $reason,
        ]);

        \Log::info('Testimonial rejected', [
            'testimonial_id' => $testimonialId,
            'moderator_id' => $moderatorId,
            'reason' => $reason,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Mark testimonial as spam (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @return Testimonial
     */
    public function markAsSpam(int $testimonialId, int $moderatorId): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        $testimonial->update([
            'status' => 'spam',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => 'Marked as spam',
        ]);

        \Log::warning('Testimonial marked as spam', [
            'testimonial_id' => $testimonialId,
            'user_id' => $testimonial->user_id,
            'moderator_id' => $moderatorId,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Calculate average rating for center
     *
     * @param int $centerId
     * @return float|null
     */
    public function calculateAverageRating(int $centerId): ?float
    {
        $average = Testimonial::where('center_id', $centerId)
            ->where('status', 'approved')
            ->avg('rating');

        return $average ? round($average, 2) : null;
    }

    /**
     * Get rating distribution for center
     *
     * @param int $centerId
     * @return array
     */
    public function getRatingDistribution(int $centerId): array
    {
        $distribution = Testimonial::where('center_id', $centerId)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings (1-5) are represented
        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Get testimonials for moderation queue
     *
     * @param string $status
     * @return LengthAwarePaginator
     */
    public function getModerationQueue(string $status = 'pending'): LengthAwarePaginator
    {
        return Testimonial::with(['user', 'center', 'moderatedBy'])
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->paginate(20);
    }

    /**
     * Delete testimonial (user can delete own pending testimonials)
     *
     * @param int $testimonialId
     * @return bool
     */
    public function delete(int $testimonialId): bool
    {
        $testimonial = Testimonial::findOrFail($testimonialId);
        return $testimonial->delete();
    }

    /**
     * Get user's testimonials
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserTestimonials(int $userId): Collection
    {
        return Testimonial::with('center')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

---

### 2. `backend/app/Services/Media/MediaService.php`

```php
<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Jobs\OptimizeImageJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    protected string $disk = 's3'; // Use S3 for production, 'public' for local

    /**
     * Upload file and create media record
     *
     * @param UploadedFile $file
     * @param string $mediableType
     * @param int $mediableId
     * @param string $type
     * @param array $metadata
     * @return Media
     */
    public function upload(
        UploadedFile $file,
        string $mediableType,
        int $mediableId,
        string $type = 'image',
        array $metadata = []
    ): Media {
        // Validate file type
        $this->validateFileType($file, $type);

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Determine storage path
        $path = $this->getStoragePath($mediableType, $mediableId, $type);

        // Upload to S3 (or local storage)
        $fullPath = Storage::disk($this->disk)->putFileAs(
            $path,
            $file,
            $filename,
            'public' // Make file publicly accessible
        );

        // Get public URL
        $url = Storage::disk($this->disk)->url($fullPath);

        // Create media record
        $media = Media::create([
            'mediable_type' => $mediableType,
            'mediable_id' => $mediableId,
            'type' => $type,
            'url' => $url,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'caption' => $metadata['caption'] ?? null,
            'alt_text' => $metadata['alt_text'] ?? null,
            'display_order' => $metadata['display_order'] ?? 0,
        ]);

        // Queue optimization job for images
        if ($type === 'image') {
            OptimizeImageJob::dispatch($media->id);
        }

        \Log::info('Media uploaded', [
            'media_id' => $media->id,
            'type' => $type,
            'size' => $file->getSize(),
        ]);

        return $media;
    }

    /**
     * Upload video to Cloudflare Stream (placeholder for Phase 6)
     *
     * @param UploadedFile $file
     * @param string $mediableType
     * @param int $mediableId
     * @param array $metadata
     * @return Media
     */
    public function uploadVideo(
        UploadedFile $file,
        string $mediableType,
        int $mediableId,
        array $metadata = []
    ): Media {
        // For now, upload to S3
        // TODO: Integrate Cloudflare Stream in Phase 6
        return $this->upload($file, $mediableType, $mediableId, 'video', $metadata);
    }

    /**
     * Delete media and file from storage
     *
     * @param int $mediaId
     * @return bool
     */
    public function delete(int $mediaId): bool
    {
        $media = Media::findOrFail($mediaId);

        // Extract path from URL
        $path = $this->getPathFromUrl($media->url);

        // Delete file from storage
        if ($path) {
            Storage::disk($this->disk)->delete($path);

            // Delete thumbnail if exists
            if ($media->thumbnail_url) {
                $thumbnailPath = $this->getPathFromUrl($media->thumbnail_url);
                if ($thumbnailPath) {
                    Storage::disk($this->disk)->delete($thumbnailPath);
                }
            }
        }

        // Delete media record
        $deleted = $media->delete();

        \Log::info('Media deleted', [
            'media_id' => $mediaId,
            'path' => $path,
        ]);

        return $deleted;
    }

    /**
     * Reorder media for a model
     *
     * @param string $mediableType
     * @param int $mediableId
     * @param array $orderArray
     * @return bool
     */
    public function reorder(string $mediableType, int $mediableId, array $orderArray): bool
    {
        foreach ($orderArray as $order => $mediaId) {
            Media::where('id', $mediaId)
                ->where('mediable_type', $mediableType)
                ->where('mediable_id', $mediableId)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    /**
     * Update media metadata
     *
     * @param int $mediaId
     * @param array $metadata
     * @return Media
     */
    public function updateMetadata(int $mediaId, array $metadata): Media
    {
        $media = Media::findOrFail($mediaId);

        $media->update([
            'caption' => $metadata['caption'] ?? $media->caption,
            'alt_text' => $metadata['alt_text'] ?? $media->alt_text,
            'display_order' => $metadata['display_order'] ?? $media->display_order,
        ]);

        return $media->fresh();
    }

    /**
     * Get media for a model
     *
     * @param string $mediableType
     * @param int $mediableId
     * @param string|null $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMediaFor(string $mediableType, int $mediableId, ?string $type = null)
    {
        $query = Media::where('mediable_type', $mediableType)
            ->where('mediable_id', $mediableId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('display_order')->get();
    }

    /**
     * Validate file type
     *
     * @param UploadedFile $file
     * @param string $type
     * @throws \InvalidArgumentException
     */
    protected function validateFileType(UploadedFile $file, string $type): void
    {
        $allowedMimes = [
            'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
            'document' => ['application/pdf', 'application/msword'],
        ];

        if (!isset($allowedMimes[$type])) {
            throw new \InvalidArgumentException('Invalid media type');
        }

        if (!in_array($file->getMimeType(), $allowedMimes[$type])) {
            throw new \InvalidArgumentException(
                "File type {$file->getMimeType()} not allowed for {$type}"
            );
        }
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateFilename(UploadedFile $file): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Get storage path for media
     *
     * @param string $mediableType
     * @param int $mediableId
     * @param string $type
     * @return string
     */
    protected function getStoragePath(string $mediableType, int $mediableId, string $type): string
    {
        $modelName = class_basename($mediableType);
        return strtolower($modelName) . '/' . $mediableId . '/' . $type;
    }

    /**
     * Extract path from S3 URL
     *
     * @param string $url
     * @return string|null
     */
    protected function getPathFromUrl(string $url): ?string
    {
        // Extract path from URL (e.g., https://bucket.s3.region.amazonaws.com/path/to/file.jpg)
        $parsed = parse_url($url);
        return $parsed['path'] ?? null;
    }
}
```

---

### 3. `backend/app/Services/Media/ImageOptimizationService.php`

```php
<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    protected string $disk = 's3';

    /**
     * Optimize image (compress and generate thumbnails)
     *
     * @param int $mediaId
     * @return Media
     */
    public function optimize(int $mediaId): Media
    {
        $media = Media::findOrFail($mediaId);

        if ($media->type !== 'image') {
            throw new \InvalidArgumentException('Can only optimize images');
        }

        // Get original image from S3
        $path = $this->getPathFromUrl($media->url);
        
        if (!$path) {
            \Log::error('Could not extract path from URL', ['url' => $media->url]);
            return $media;
        }

        try {
            // Download image from S3
            $imageContent = Storage::disk($this->disk)->get($path);

            // Create Intervention Image instance
            $image = Image::make($imageContent);

            // Generate thumbnails
            $thumbnailUrl = $this->generateThumbnail($image, $path, $media);

            // Optimize original (compress)
            $this->compressOriginal($image, $path);

            // Update media record
            $media->update([
                'thumbnail_url' => $thumbnailUrl,
            ]);

            \Log::info('Image optimized', [
                'media_id' => $mediaId,
                'original_size' => strlen($imageContent),
            ]);

            return $media->fresh();

        } catch (\Exception $e) {
            \Log::error('Image optimization failed', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate thumbnail
     *
     * @param \Intervention\Image\Image $image
     * @param string $originalPath
     * @param Media $media
     * @return string Thumbnail URL
     */
    protected function generateThumbnail($image, string $originalPath, Media $media): string
    {
        $thumbnailSizes = [
            'small' => 300,   // 300x300
            'medium' => 600,  // 600x600
            'large' => 1200,  // 1200x1200
        ];

        $thumbnailPath = str_replace(
            '.' . pathinfo($originalPath, PATHINFO_EXTENSION),
            '_thumb.webp',
            $originalPath
        );

        // Create thumbnail (300x300 for primary thumbnail)
        $thumbnail = clone $image;
        $thumbnail->fit($thumbnailSizes['small'], $thumbnailSizes['small']);

        // Convert to WebP for better compression
        $thumbnailContent = $thumbnail->encode('webp', 85)->__toString();

        // Upload thumbnail to S3
        Storage::disk($this->disk)->put(
            $thumbnailPath,
            $thumbnailContent,
            'public'
        );

        return Storage::disk($this->disk)->url($thumbnailPath);
    }

    /**
     * Compress original image
     *
     * @param \Intervention\Image\Image $image
     * @param string $path
     */
    protected function compressOriginal($image, string $path): void
    {
        // Compress image (85% quality is good balance)
        $compressed = $image->encode(null, 85)->__toString();

        // Re-upload compressed version
        Storage::disk($this->disk)->put($path, $compressed, 'public');
    }

    /**
     * Convert image to WebP format
     *
     * @param int $mediaId
     * @return Media
     */
    public function convertToWebP(int $mediaId): Media
    {
        $media = Media::findOrFail($mediaId);

        $path = $this->getPathFromUrl($media->url);
        
        if (!$path) {
            return $media;
        }

        try {
            $imageContent = Storage::disk($this->disk)->get($path);
            $image = Image::make($imageContent);

            // Convert to WebP
            $webpContent = $image->encode('webp', 85)->__toString();

            // Create new path with .webp extension
            $webpPath = preg_replace('/\.\w+$/', '.webp', $path);

            // Upload WebP version
            Storage::disk($this->disk)->put($webpPath, $webpContent, 'public');

            // Update media record
            $media->update([
                'url' => Storage::disk($this->disk)->url($webpPath),
                'mime_type' => 'image/webp',
            ]);

            // Delete old format
            Storage::disk($this->disk)->delete($path);

            \Log::info('Image converted to WebP', [
                'media_id' => $mediaId,
                'original_path' => $path,
                'webp_path' => $webpPath,
            ]);

            return $media->fresh();

        } catch (\Exception $e) {
            \Log::error('WebP conversion failed', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate responsive image sizes
     *
     * @param int $mediaId
     * @return array URLs of different sizes
     */
    public function generateResponsiveSizes(int $mediaId): array
    {
        $media = Media::findOrFail($mediaId);
        $path = $this->getPathFromUrl($media->url);

        if (!$path) {
            return [];
        }

        $sizes = [
            'xs' => 300,
            'sm' => 600,
            'md' => 1200,
            'lg' => 1920,
        ];

        $urls = [];

        try {
            $imageContent = Storage::disk($this->disk)->get($path);
            $image = Image::make($imageContent);

            foreach ($sizes as $label => $width) {
                $resized = clone $image;
                $resized->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $resizedPath = str_replace(
                    '.' . pathinfo($path, PATHINFO_EXTENSION),
                    "_{$label}.webp",
                    $path
                );

                $resizedContent = $resized->encode('webp', 85)->__toString();

                Storage::disk($this->disk)->put($resizedPath, $resizedContent, 'public');

                $urls[$label] = Storage::disk($this->disk)->url($resizedPath);
            }

            return $urls;

        } catch (\Exception $e) {
            \Log::error('Responsive sizes generation failed', [
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract path from URL
     *
     * @param string $url
     * @return string|null
     */
    protected function getPathFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        return isset($parsed['path']) ? ltrim($parsed['path'], '/') : null;
    }
}
```

---

### 4. `backend/app/Services/Translation/TranslationService.php`

```php
<?php

namespace App\Services\Translation;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Collection;

class TranslationService
{
    /**
     * Create translation
     *
     * @param string $translatableType
     * @param int $translatableId
     * @param string $locale
     * @param string $field
     * @param string $value
     * @param int|null $translatorId
     * @return ContentTranslation
     */
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

    /**
     * Update translation
     *
     * @param int $translationId
     * @param string $value
     * @return ContentTranslation
     */
    public function updateTranslation(int $translationId, string $value): ContentTranslation
    {
        $translation = ContentTranslation::findOrFail($translationId);

        $translation->update([
            'value' => $value,
            'updated_at' => now(),
        ]);

        return $translation->fresh();
    }

    /**
     * Mark translation as translated
     *
     * @param int $translationId
     * @param int $translatorId
     * @return ContentTranslation
     */
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

    /**
     * Mark translation as reviewed
     *
     * @param int $translationId
     * @param int $reviewerId
     * @return ContentTranslation
     */
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

    /**
     * Publish translation
     *
     * @param int $translationId
     * @return ContentTranslation
     */
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

    /**
     * Get translations for a model
     *
     * @param string $translatableType
     * @param int $translatableId
     * @param string|null $locale
     * @return Collection
     */
    public function getTranslations(
        string $translatableType,
        int $translatableId,
        ?string $locale = null
    ): Collection {
        $query = ContentTranslation::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId);

        if ($locale) {
            $query->where('locale', $locale);
        }

        return $query->get();
    }

    /**
     * Get pending translations for a locale
     *
     * @param string $locale
     * @param string|null $status
     * @return Collection
     */
    public function getPendingTranslations(string $locale, ?string $status = 'draft'): Collection
    {
        return ContentTranslation::with(['translator', 'reviewer'])
            ->where('locale', $locale)
            ->where('translation_status', $status)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get translation by unique key
     *
     * @param string $translatableType
     * @param int $translatableId
     * @param string $locale
     * @param string $field
     * @return ContentTranslation|null
     */
    public function getTranslation(
        string $translatableType,
        int $translatableId,
        string $locale,
        string $field
    ): ?ContentTranslation {
        return ContentTranslation::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();
    }

    /**
     * Delete translation
     *
     * @param int $translationId
     * @return bool
     */
    public function delete(int $translationId): bool
    {
        $translation = ContentTranslation::findOrFail($translationId);
        return $translation->delete();
    }

    /**
     * Get translation coverage for a model
     *
     * @param string $translatableType
     * @param int $translatableId
     * @return array
     */
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

    /**
     * Get translatable fields for a model type
     *
     * @param string $translatableType
     * @return array
     */
    protected function getTranslatableFields(string $translatableType): array
    {
        // Define translatable fields per model
        $fields = [
            'App\\Models\\Center' => ['name', 'short_description', 'description'],
            'App\\Models\\Service' => ['name', 'description'],
            'App\\Models\\FAQ' => ['question', 'answer'],
        ];

        return $fields[$translatableType] ?? [];
    }
}
```

---

## Controllers (6 files)

### 5. `backend/app/Http/Controllers/Api/V1/TestimonialController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\StoreTestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Models\Center;
use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService
    ) {
        $this->middleware('auth:sanctum')->only(['store']);
    }

    /**
     * Get approved testimonials for a center
     *
     * @param Center $center
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Center $center, Request $request): JsonResponse
    {
        $filters = $request->only(['min_rating', 'sort_by', 'sort_order', 'per_page']);

        $testimonials = $this->testimonialService->getApprovedForCenter(
            $center->id,
            $filters
        );

        return ApiResponse::paginated(
            $testimonials,
            TestimonialResource::class,
            'Testimonials retrieved successfully'
        );
    }

    /**
     * Submit testimonial (authenticated users)
     *
     * @param Center $center
     * @param StoreTestimonialRequest $request
     * @return JsonResponse
     */
    public function store(Center $center, StoreTestimonialRequest $request): JsonResponse
    {
        try {
            $testimonial = $this->testimonialService->submit(
                $request->user()->id,
                $center->id,
                $request->validated()
            );

            return ApiResponse::created(
                new TestimonialResource($testimonial),
                'Thank you for your testimonial! It will be reviewed before publication.'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Get user's testimonials
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userTestimonials(Request $request): JsonResponse
    {
        $testimonials = $this->testimonialService->getUserTestimonials(
            $request->user()->id
        );

        return ApiResponse::success(
            TestimonialResource::collection($testimonials),
            'Your testimonials retrieved successfully'
        );
    }
}
```

---

### 6. `backend/app/Http/Controllers/Api/V1/MediaController.php`

```php
<?php

namespace App\Http\Controllers\Api/V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Http\Resources\MediaResource;
use App\Http\Responses\ApiResponse;
use App\Models\Media;
use App\Services\Media\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Upload media (admin only)
     *
     * @param UploadMediaRequest $request
     * @return JsonResponse
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        try {
            $media = $this->mediaService->upload(
                file: $request->file('file'),
                mediableType: $request->mediable_type,
                mediableId: $request->mediable_id,
                type: $request->type,
                metadata: [
                    'caption' => $request->caption,
                    'alt_text' => $request->alt_text,
                    'display_order' => $request->display_order ?? 0,
                ]
            );

            return ApiResponse::created(
                new MediaResource($media),
                'Media uploaded successfully. Optimization is in progress.'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            \Log::error('Media upload failed', [
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                'Media upload failed. Please try again.',
                null,
                500
            );
        }
    }

    /**
     * Delete media (admin only)
     *
     * @param Media $media
     * @return JsonResponse
     */
    public function destroy(Media $media): JsonResponse
    {
        try {
            $this->mediaService->delete($media->id);

            return ApiResponse::success(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Media deletion failed', null, 500);
        }
    }

    /**
     * Reorder media (admin only)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'mediable_type' => ['required', 'string'],
            'mediable_id' => ['required', 'integer'],
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'exists:media,id'],
        ]);

        $this->mediaService->reorder(
            $request->mediable_type,
            $request->mediable_id,
            $request->order
        );

        return ApiResponse::success(null, 'Media reordered successfully');
    }

    /**
     * Update media metadata (admin only)
     *
     * @param Request $request
     * @param Media $media
     * @return JsonResponse
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'caption' => ['sometimes', 'string', 'max:500'],
            'alt_text' => ['sometimes', 'string', 'max:255'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $updated = $this->mediaService->updateMetadata(
            $media->id,
            $request->only(['caption', 'alt_text', 'display_order'])
        );

        return ApiResponse::success(
            new MediaResource($updated),
            'Media metadata updated successfully'
        );
    }
}
```

---

### 7. `backend/app/Http/Controllers/Api/V1/TranslationController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translation\StoreTranslationRequest;
use App\Http\Requests\Translation\UpdateTranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContentTranslation;
use App\Services\Translation\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function __construct(
        protected TranslationService $translationService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    /**
     * Get translations for a model
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'translatable_type' => ['sometimes', 'string'],
            'translatable_id' => ['sometimes', 'integer'],
            'locale' => ['sometimes', 'in:en,zh,ms,ta'],
            'status' => ['sometimes', 'in:draft,translated,reviewed,published'],
        ]);

        if ($request->has('translatable_type') && $request->has('translatable_id')) {
            $translations = $this->translationService->getTranslations(
                $request->translatable_type,
                $request->translatable_id,
                $request->locale
            );
        } elseif ($request->has('locale')) {
            $translations = $this->translationService->getPendingTranslations(
                $request->locale,
                $request->status ?? 'draft'
            );
        } else {
            return ApiResponse::error('Missing required parameters', null, 400);
        }

        return ApiResponse::success(
            TranslationResource::collection($translations),
            'Translations retrieved successfully'
        );
    }

    /**
     * Create translation
     *
     * @param StoreTranslationRequest $request
     * @return JsonResponse
     */
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->createTranslation(
            translatableType: $request->translatable_type,
            translatableId: $request->translatable_id,
            locale: $request->locale,
            field: $request->field,
            value: $request->value,
            translatorId: $request->user()->id
        );

        return ApiResponse::created(
            new TranslationResource($translation),
            'Translation created successfully'
        );
    }

    /**
     * Update translation
     *
     * @param UpdateTranslationRequest $request
     * @param ContentTranslation $translation
     * @return JsonResponse
     */
    public function update(UpdateTranslationRequest $request, ContentTranslation $translation): JsonResponse
    {
        $updated = $this->translationService->updateTranslation(
            $translation->id,
            $request->value
        );

        return ApiResponse::success(
            new TranslationResource($updated),
            'Translation updated successfully'
        );
    }

    /**
     * Mark translation as translated
     *
     * @param ContentTranslation $translation
     * @param Request $request
     * @return JsonResponse
     */
    public function markTranslated(ContentTranslation $translation, Request $request): JsonResponse
    {
        try {
            $updated = $this->translationService->markTranslated(
                $translation->id,
                $request->user()->id
            );

            return ApiResponse::success(
                new TranslationResource($updated),
                'Translation marked as translated'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Mark translation as reviewed
     *
     * @param ContentTranslation $translation
     * @param Request $request
     * @return JsonResponse
     */
    public function markReviewed(ContentTranslation $translation, Request $request): JsonResponse
    {
        try {
            $updated = $this->translationService->markReviewed(
                $translation->id,
                $request->user()->id
            );

            return ApiResponse::success(
                new TranslationResource($updated),
                'Translation marked as reviewed'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Publish translation
     *
     * @param ContentTranslation $translation
     * @return JsonResponse
     */
    public function publish(ContentTranslation $translation): JsonResponse
    {
        try {
            $updated = $this->translationService->publish($translation->id);

            return ApiResponse::success(
                new TranslationResource($updated),
                'Translation published successfully'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Delete translation
     *
     * @param ContentTranslation $translation
     * @return JsonResponse
     */
    public function destroy(ContentTranslation $translation): JsonResponse
    {
        $this->translationService->delete($translation->id);

        return ApiResponse::success(null, 'Translation deleted successfully');
    }

    /**
     * Get translation coverage for a model
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function coverage(Request $request): JsonResponse
    {
        $request->validate([
            'translatable_type' => ['required', 'string'],
            'translatable_id' => ['required', 'integer'],
        ]);

        $coverage = $this->translationService->getTranslationCoverage(
            $request->translatable_type,
            $request->translatable_id
        );

        return ApiResponse::success($coverage, 'Translation coverage retrieved');
    }
}
```

---

Due to character limits, I'll continue with the remaining files (Admin Controllers, Requests, Resources, Jobs, Policies) in the next response.
