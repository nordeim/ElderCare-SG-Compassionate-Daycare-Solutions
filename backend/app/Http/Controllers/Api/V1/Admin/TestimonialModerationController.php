<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Testimonial;
use App\Services\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialModerationController extends Controller
{
    public function __construct(protected TestimonialService $service)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    public function index(Request $request): JsonResponse
    {
        $status = $request->get('status', 'pending');
        $results = $this->service->getModerationQueue($status);

        return ApiResponse::paginated($results, \App\Http\Resources\TestimonialResource::class, 'Moderation queue');
    }

    public function approve(Testimonial $testimonial, Request $request): JsonResponse
    {
        // Authorize against the Testimonial model (role-based check inside policy)
        $this->authorize('moderate', \App\Models\Testimonial::class);

        $updated = $this->service->approve($testimonial->id, $request->user()->id);

        return ApiResponse::success(new \App\Http\Resources\TestimonialResource($updated), 'Testimonial approved');
    }

    public function reject(Testimonial $testimonial, Request $request): JsonResponse
    {
        $this->authorize('moderate', \App\Models\Testimonial::class);

        $reason = $request->input('reason', 'No reason provided');
        $updated = $this->service->reject($testimonial->id, $request->user()->id, $reason);

        return ApiResponse::success(new \App\Http\Resources\TestimonialResource($updated), 'Testimonial rejected');
    }

    public function markAsSpam(Testimonial $testimonial, Request $request): JsonResponse
    {
        $this->authorize('moderate', \App\Models\Testimonial::class);

        $updated = $this->service->markAsSpam($testimonial->id, $request->user()->id);

        return ApiResponse::success(new \App\Http\Resources\TestimonialResource($updated), 'Testimonial marked as spam');
    }

    public function destroy(Testimonial $testimonial, Request $request): JsonResponse
    {
        // Admins can delete
        $this->authorize('moderate', \App\Models\Testimonial::class);

        $testimonial->delete();

        return ApiResponse::success(null, 'Testimonial deleted');
    }
}
