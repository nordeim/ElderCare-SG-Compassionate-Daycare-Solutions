<?php

namespace Tests\Unit\Services;

use App\Jobs\OptimizeImageJob;
use App\Models\Media;
use App\Models\Center;
use App\Services\Media\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(MediaService::class);
    }

    public function test_upload_image_creates_media_and_dispatches_job()
    {
        Storage::fake('s3');
        Queue::fake();

        $center = Center::factory()->create();

    // Use a fake uploaded file without requiring GD extension (create raw file)
    $file = UploadedFile::fake()->create('photo.jpg', 150, 'image/jpeg');

        $media = $this->service->upload($file, Center::class, $center->id, 'image', [
            'caption' => 'A caption',
            'alt_text' => 'Alt text',
        ]);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertEquals('A caption', $media->caption);
        $this->assertEquals('Alt text', $media->alt_text);

    // File put to storage — normalize path
        $path = ltrim(parse_url($media->url, PHP_URL_PATH), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        Storage::disk('s3')->assertExists($path);

        // Job dispatched
        Queue::assertPushed(OptimizeImageJob::class, function ($job) use ($media) {
            return $job->mediaId === $media->id;
        });
    }

    public function test_delete_deletes_record_and_files()
    {
        Storage::fake('s3');

        $center = Center::factory()->create();

    // Simulate an uploaded file
    $path = 'center/' . $center->id . '/image/photo.jpg';
    Storage::disk('s3')->put($path, 'contents');

        $media = Media::factory()->create([
            'mediable_type' => Center::class,
            'mediable_id' => $center->id,
            'url' => Storage::disk('s3')->url($path),
            'thumbnail_url' => null,
        ]);

        $result = $this->service->delete($media->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    // Assert the normalized path is missing
    Storage::disk('s3')->assertMissing($path);
    }

    public function test_reorder_updates_display_order()
    {
        $center = Center::factory()->create();

        $m1 = Media::factory()->create(['mediable_type' => Center::class, 'mediable_id' => $center->id, 'display_order' => 1]);
        $m2 = Media::factory()->create(['mediable_type' => Center::class, 'mediable_id' => $center->id, 'display_order' => 2]);
        $m3 = Media::factory()->create(['mediable_type' => Center::class, 'mediable_id' => $center->id, 'display_order' => 3]);

        $order = [$m3->id, $m1->id, $m2->id];

        $this->service->reorder(Center::class, $center->id, $order);

        $this->assertDatabaseHas('media', ['id' => $m3->id, 'display_order' => 1]);
        $this->assertDatabaseHas('media', ['id' => $m1->id, 'display_order' => 2]);
        $this->assertDatabaseHas('media', ['id' => $m2->id, 'display_order' => 3]);
    }

    public function test_updateMetadata_updates_fields()
    {
        $media = Media::factory()->create(['caption' => null, 'alt_text' => null, 'display_order' => 5]);

        $updated = $this->service->updateMetadata($media->id, [
            'caption' => 'New caption',
            'alt_text' => 'New alt',
            'display_order' => 2,
        ]);

        $this->assertEquals('New caption', $updated->caption);
        $this->assertEquals('New alt', $updated->alt_text);
        $this->assertEquals(2, $updated->display_order);
    }

    public function test_getMediaFor_returns_media_with_and_without_type()
    {
        $center = Center::factory()->create();

        $img = Media::factory()->create(['mediable_type' => Center::class, 'mediable_id' => $center->id, 'type' => 'image']);
        $vid = Media::factory()->create(['mediable_type' => Center::class, 'mediable_id' => $center->id, 'type' => 'video']);

        $all = $this->service->getMediaFor(Center::class, $center->id);
        $this->assertCount(2, $all);

        $images = $this->service->getMediaFor(Center::class, $center->id, 'image');
        $this->assertCount(1, $images);
        $this->assertEquals('image', $images->first()->type);
    }
}
