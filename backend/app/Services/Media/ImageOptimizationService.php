<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageOptimizationService
{
    protected string $disk = 's3';

    public function optimize(int $mediaId): Media
    {
        $media = Media::findOrFail($mediaId);

        if ($media->type !== 'image') {
            throw new \InvalidArgumentException('Can only optimize images');
        }

        $path = $this->getPathFromUrl($media->url);
        
        if (!$path) {
            \Log::error('Could not extract path from URL', ['url' => $media->url]);
            return $media;
        }

        try {
            $imageContent = Storage::disk($this->disk)->get($path);

            $image = Image::make($imageContent);

            $thumbnailUrl = $this->generateThumbnail($image, $path, $media);

            $this->compressOriginal($image, $path);

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

    protected function generateThumbnail($image, string $originalPath, Media $media): string
    {
        $thumbnailSizes = [
            'small' => 300,
            'medium' => 600,
            'large' => 1200,
        ];

        $thumbnailPath = str_replace(
            '.' . pathinfo($originalPath, PATHINFO_EXTENSION),
            '_thumb.webp',
            $originalPath
        );

        $thumbnail = clone $image;
        $thumbnail->fit($thumbnailSizes['small'], $thumbnailSizes['small']);

        $thumbnailContent = $thumbnail->encode('webp', 85)->__toString();

        Storage::disk($this->disk)->put(
            $thumbnailPath,
            $thumbnailContent,
            'public'
        );

        return Storage::disk($this->disk)->url($thumbnailPath);
    }

    protected function compressOriginal($image, string $path): void
    {
        $compressed = $image->encode(null, 85)->__toString();

        Storage::disk($this->disk)->put($path, $compressed, 'public');
    }

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

            $webpContent = $image->encode('webp', 85)->__toString();

            $webpPath = preg_replace('/\.\w+$/', '.webp', $path);

            Storage::disk($this->disk)->put($webpPath, $webpContent, 'public');

            $media->update([
                'url' => Storage::disk($this->disk)->url($webpPath),
                'mime_type' => 'image/webp',
            ]);

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

    protected function getPathFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        return isset($parsed['path']) ? ltrim($parsed['path'], '/') : null;
    }
}
