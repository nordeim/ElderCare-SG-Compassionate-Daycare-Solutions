<?php

namespace Tests\Unit\Models;

use App\Models\Media;
use App\Models\Center;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_polymorphic_mediable_relationship()
    {
        $center = Center::factory()->create();
        $media = Media::factory()->create([
            'mediable_type' => Center::class,
            'mediable_id' => $center->id,
        ]);

        $this->assertInstanceOf(Center::class, $media->mediable);
        $this->assertEquals($center->id, $media->mediable->id);
    }

    /** @test */
    public function it_works_with_different_mediable_types()
    {
        $service = Service::factory()->create();
        $media = Media::factory()->create([
            'mediable_type' => Service::class,
            'mediable_id' => $service->id,
        ]);

        $this->assertInstanceOf(Service::class, $media->mediable);
    }

    /** @test */
    public function it_has_media_types()
    {
        $image = Media::factory()->create(['type' => 'image']);
        $video = Media::factory()->create(['type' => 'video']);
        $document = Media::factory()->create(['type' => 'document']);

        $this->assertEquals('image', $image->type);
        $this->assertEquals('video', $video->type);
        $this->assertEquals('document', $document->type);
    }

    /** @test */
    public function it_stores_file_metadata()
    {
        $media = Media::factory()->create([
            'filename' => 'center-photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024000,
            'url' => 'https://s3.amazonaws.com/bucket/center-photo.jpg',
        ]);

        $this->assertEquals('center-photo.jpg', $media->filename);
        $this->assertEquals('image/jpeg', $media->mime_type);
        $this->assertEquals(1024000, $media->size);
        $this->assertStringContainsString('s3.amazonaws.com', $media->url);
    }

    /** @test */
    public function it_has_accessibility_fields()
    {
        $media = Media::factory()->create([
            'alt_text' => 'Front view of the care center building',
            'caption' => 'Main entrance with wheelchair ramp',
        ]);

        $this->assertEquals('Front view of the care center building', $media->alt_text);
        $this->assertEquals('Main entrance with wheelchair ramp', $media->caption);
    }

    /** @test */
    public function it_has_display_order()
    {
        $media1 = Media::factory()->create(['display_order' => 1]);
        $media2 = Media::factory()->create(['display_order' => 2]);

        $this->assertEquals(1, $media1->display_order);
        $this->assertEquals(2, $media2->display_order);
    }

    /** @test */
    public function it_stores_video_duration()
    {
        $video = Media::factory()->create([
            'type' => 'video',
            'duration' => 180,
        ]);

        $this->assertEquals(180, $video->duration);
    }

    /** @test */
    public function it_stores_cloudflare_stream_id()
    {
        $video = Media::factory()->create([
            'type' => 'video',
            'cloudflare_stream_id' => 'abc123def456',
        ]);

        $this->assertEquals('abc123def456', $video->cloudflare_stream_id);
    }
}
