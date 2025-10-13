<?php

namespace App\Jobs;

use App\Services\Media\ImageOptimizationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $mediaId;

    public function __construct(int $mediaId)
    {
        $this->mediaId = $mediaId;
        $this->onQueue('media-optimization');
    }

    public function handle(ImageOptimizationService $service): void
    {
        try {
            $service->optimize($this->mediaId);
        } catch (Exception $e) {
            \Log::error('OptimizeImageJob failed', ['media_id' => $this->mediaId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
