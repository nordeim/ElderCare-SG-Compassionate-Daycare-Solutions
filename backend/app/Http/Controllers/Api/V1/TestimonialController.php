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
