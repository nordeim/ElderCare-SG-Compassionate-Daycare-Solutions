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

    public function uploadVideo(
        UploadedFile $file,
        string $mediableType,
        int $mediableId,
        array $metadata = []
    ): Media {
        return $this->upload($file, $mediableType, $mediableId, 'video', $metadata);
    }

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

    public function getMediaFor(string $mediableType, int $mediableId, ?string $type = null)
    {
        $query = Media::where('mediable_type', $mediableType)
            ->where('mediable_id', $mediableId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('display_order')->get();
    }

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

    protected function generateFilename(UploadedFile $file): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    protected function getStoragePath(string $mediableType, int $mediableId, string $type): string
    {
        $modelName = class_basename($mediableType);
        return strtolower($modelName) . '/' . $mediableId . '/' . $type;
    }

    protected function getPathFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        return $parsed['path'] ?? null;
    }
}
