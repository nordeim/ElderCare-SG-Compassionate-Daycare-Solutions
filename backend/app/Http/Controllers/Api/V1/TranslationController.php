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

    public function destroy(ContentTranslation $translation): JsonResponse
    {
        $this->translationService->delete($translation->id);

        return ApiResponse::success(null, 'Translation deleted successfully');
    }

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
