<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Center;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $isImage = fake()->boolean(80);
        $size = $isImage ? fake()->numberBetween(50_000, 2_000_000) : fake()->numberBetween(1_000_000, 50_000_000);
        $duration = $isImage ? null : fake()->numberBetween(10, 600);

        return [
            'mediable_type' => Center::class,
            'mediable_id' => Center::factory(),
            'type' => $isImage ? 'image' : 'video',
            'url' => $isImage ? fake()->imageUrl(1200, 800, 'nature') : 'https://cdn.example.com/videos/' . fake()->uuid() . '.mp4',
            'thumbnail_url' => $isImage ? null : 'https://cdn.example.com/videos/' . fake()->uuid() . '_thumb.jpg',
            'filename' => $isImage ? 'image_' . fake()->uuid() . '.jpg' : 'video_' . fake()->uuid() . '.mp4',
            'mime_type' => $isImage ? 'image/jpeg' : 'video/mp4',
            'size' => $size,
            'duration' => $duration,
            'caption' => fake()->boolean(50) ? fake()->sentence(8) : null,
            'alt_text' => $isImage ? fake()->sentence(6) : null,
            'cloudflare_stream_id' => $isImage ? null : 'cf_' . fake()->uuid(),
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'mime_type' => 'image/jpeg',
            'thumbnail_url' => null,
            'cloudflare_stream_id' => null,
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'mime_type' => 'video/mp4',
            'thumbnail_url' => 'https://cdn.example.com/videos/' . fake()->uuid() . '_thumb.jpg',
            'cloudflare_stream_id' => 'cf_' . fake()->uuid(),
            'duration' => fake()->numberBetween(10, 1200),
        ]);
    }

    public function withThumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'thumbnail_url' => $attributes['thumbnail_url'] ?? ('https://cdn.example.com/thumbs/' . fake()->uuid() . '.jpg'),
        ]);
    }

    public function withCloudflareStream(): static
    {
        return $this->state(fn (array $attributes) => [
            'cloudflare_stream_id' => $attributes['cloudflare_stream_id'] ?? ('cf_' . fake()->uuid()),
        ]);
    }

    public function largeSize(): static
    {
        return $this->state(fn (array $attributes) => [
            'size' => fake()->numberBetween(10_000_000, 200_000_000),
        ]);
    }
}
