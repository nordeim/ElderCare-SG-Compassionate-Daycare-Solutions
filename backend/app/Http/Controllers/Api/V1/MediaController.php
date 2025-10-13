<?php

namespace App\Http\Controllers\Api\V1;

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

    public function destroy(Media $media): JsonResponse
    {
        try {
            $this->mediaService->delete($media->id);

            return ApiResponse::success(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Media deletion failed', null, 500);
        }
    }

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
